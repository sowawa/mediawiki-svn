#!/usr/bin/python
# -*- coding: utf-8 -*-
'''
Copyright (C) 2010 by Diederik van Liere (dvanliere@gmail.com)
This program is free software; you can redistribute it and/or
modify it under the terms of the GNU General Public License version 2
as published by the Free Software Foundation.
This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
See the GNU General Public License for more details, at
http://www.fsf.org/licenses/gpl.html
'''

__author__ = '''\n'''.join(['Diederik van Liere (dvanliere@gmail.com)', ])
__email__ = 'dvanliere at gmail dot com'
__date__ = '2011-01-14'
__version__ = '0.1'

import gc
import random
import calendar
import datetime
import time
import math
import sys
import cPickle
from pymongo.son_manipulator import SONManipulator
from multiprocessing import RLock
from texttable import Texttable
from datetime import timedelta
import cProfile
if '..' not in sys.path:
    sys.path.append('..')

from classes import settings
settings = settings.Settings()

from utils import file_utils
from utils import data_converter
from classes import storage
from analyses import json_encoders
from classes import exceptions

class Transform(SONManipulator):
    '''
    This encoder transforms a Dataset to a MongoDB bson document. 
    To use this encoder initalize a mongo database instance and then add:
    mongo.add_son_manipulator(Transform())    
    '''
    def __init__(self):
        super(Transform).__init__(self)

    def transform_incoming(self, son):
        for key, ds in son.items():
            son[key] = {}
            for x, var in enumerate(ds):
                if isinstance(var, Variable):
                    son[key][var.name] = var.encode()
        for prop in ds.props:
            son[prop] = getattr(ds, prop)
        return son

    def transform_outgoing(self, son, collection):
        for (key, value) in son.items():
            if isinstance(value, dict):
                names = value.keys()
                for name in names:
                    var = Variable(name, None)
                    var.decode(value)
                    son['variables'][name] = var
            else: # Again, make sure to recurse into sub-docs
                son[key] = value
        name = son.pop('name', None)
        project = son.pop('project', None)
        collection = son.pop('collection', None)
        language_code = son.pop('language_code', None)
        variables = son.pop('variables', [])
        ds = Dataset(name, project, collection, language_code, **son)
        for var in variables:
            var = variables[var]
            ds.add_variable(var)
        return ds


class Data:
    '''
    Some generic functions that are required by the Observation, Variable, and
    Dataset classes. 
    '''
    def __init__(self):
        pass

    def generate_key(self, variables):
        '''
        This is a simple hash function that expects a list of variables, used
        to lookup an Observation or Variable. 
        '''
        keys = []
        for variable in variables:
            try:
                variable = variable.encode('utf-8')
            except AttributeError:
                variable = str(variable)
            keys.append(variable)
        return '_'.join([key for key in keys])
        #return id
        #m = hashlib.md5()
        #m.update(id)
        #print id, m.hexdigest()
        #return m.hexdigest()

    def encode_to_bson(self, data=None):
        '''
        This function converts a Variable or Observation to a dictionary that 
        can be stored in Mongo.
        '''
        if data:
            kwargs = dict([(str(key), value) for key, value in data.__dict__.iteritems()])
        else:
            kwargs = dict([(str(key), value) for key, value in self.__dict__.iteritems()])
        for key, value in kwargs.iteritems():
            if isinstance(value, dict):
                d = {}
                for k, v in value.iteritems():
                    if isinstance(v, Observation):
                        v = self.encode_to_bson(v)
                    d[str(k)] = v
                kwargs[key] = d
        return kwargs

    def convert_date_to_epoch(self, date):
        '''
        Calculate the number of seconds since epoch depending on the time_unit
        of the date
        '''
        assert self.time_unit == 'year' or self.time_unit == 'month' \
        or self.time_unit == 'day', \
            'Time unit should either be year, month or day.'

        if self.time_unit == 'year':
            datum = datetime.datetime(date.year, 1, 1)
            return int(time.mktime(datum.timetuple()))
        elif self.time_unit == 'month':
            datum = datetime.datetime(date.year, date.month, 1)
            return int(time.mktime(datum.timetuple()))
        elif self.time_unit == 'day':
            return int(time.mktime(date.timetuple()))
        else:
            return date

    def set_date_range(self, date):
        '''
        Determine the width of a date range for an observation. 
        '''
        if self.time_unit == 'year':
            return datetime.datetime(date.year, 12, 31), \
                datetime.datetime(date.year, 1, 1)
        elif self.time_unit == 'month':
            day = calendar.monthrange(date.year, date.month)[1]
            return datetime.datetime(date.year, date.month, day), \
                datetime.datetime(date.year, date.month, 1)
        else:
            return datetime.datetime(date.year, date.month, date.day), \
                datetime.datetime(date.year, date.month, date.day)


class Observation(Data):
    '''
    The smallest unit, here the actual data is being stored. 
    Time_unit should either be 'year', 'month' or 'day'. 
    '''
    def __init__(self, date, time_unit, id, meta):
        assert isinstance(date, datetime.datetime), '''Date variable should be 
            a datetime.datetime instance.'''
        #self.date = date
        self.data = 0
        self.time_unit = time_unit
        self.date = date
        self.t1, self.t0 = self.set_date_range(date)
        self.id = id
        self.props = []
        self.count = 0
        for mt in meta:
            if isinstance(mt, float):
                raise Exception, '''Mongo does not allow a dot "." in the name 
                    of a key, please use an integer or string as key.'''
            elif not isinstance(mt, list):
                setattr(self, mt, meta[mt])
                self.props.append(mt)
        self._type = 'observation'

    def __repr__(self):
        return '%s' % self.date

    def __str__(self):
        return 'range: %s-%s-%s : %s-%s-%s' % (self.t0.month, self.t0.day, \
                                               self.t0.year, self.t1.month, \
                                               self.t1.day, self.t1.year)

    def __iter__(self):
        for obs in self.data:
            yield self.data[obs]

    def __getitem__(self, key):
        return getattr(self, key, [])

    def serialize(self):
        return cPickle.dumps(self)

    def deserialize(self):
        return cPickle.loads(self)

    def add(self, value):
        '''
        Increment the observation with value, or append value to the list of
        observations. 
        '''
        if isinstance(value, list):
            if self.count == 0:
                self.data = []
            self.data.append(value)
        else:
            self.data += value
        self.count += 1


    def get_date_range(self):
        '''Determine the date range for the observations'''
        return '%s-%s-%s:%s-%s-%s' % (self.t0.month, self.t0.day, self.t0.year, \
                                      self.t1.month, self.t1.day, self.t1.year)

class Variable(Data):
    '''
    This class constructs a time-based variable. 
    '''
    def __init__(self, name, time_unit, lock, obs, **kwargs):
        self.name = name
        self.lock = lock
        self.obs = obs
        self.time_unit = time_unit
        self.groupbys = []
        self._type = 'variable'
        self.props = ['name', 'time_unit', '_type']
        for kw in kwargs:
            setattr(self, kw, kwargs[kw])
            self.props.append(kw)

    def __str__(self):
        return '%s' % self.name

    def __repr__(self):
        return '%s' % self.name

    def __getitem__(self, key):
        return getattr(self, key, [])

    def __iter__(self):
        keys = self.obs.keys()
        for key in keys:
            yield key

    def __len__(self):
        return len(self.obs.keys())

    def items(self):
        for key in self.__dict__.keys():
            yield key, getattr(self, key)

    def itervalues(self):
        for key in self:
            yield self.obs[key].data

    def iteritems(self):
        for key in self:
            yield (key, self.obs[key])

    def get_data(self):
        '''Construct a list with all data points'''
        return [o for o in self.itervalues()]

    def get_observation(self, key, date, meta):
        '''Get a single observation based on a date key and possibly meta data'''
        if key in self.obs:
            return self.obs.get(key)
        else:
            obs = Observation(date, self.time_unit, key, meta)
            return obs

    def add(self, date, value, meta={}):
        '''
        The add function is used to add an observation to a variable. An
        observation is always grouped by the combination of the date and 
        time_unit. Time_unit is a property of a Variable and indicates how 
        granular the observations should be grouped. For example, if 
        time_unit == year then all observations in a given year will be grouped. 
        When calling add you should supply at least two variables:
        1) date: when did the observation happen
        2) value: an integer or float that was observed on that date
        Optionally you can supply a dictionary for extra groupings. The key is
        the name of the extra grouping.
        For example, if you add {'experience': 3} as the meta dict when calling
        add then you will create an extra grouping called experience and all
        future observations who fall in the same date range and the same 
        experience level, in this case 3, will be grouped by that particular 
        observation. You can use as many extra groupings as you want but 
        usually one extra grouping should be enough. 
        '''
        assert isinstance(meta, dict), '''The meta variable should be a dict 
            (either empty) or with variables to group by.'''
        start, end = self.set_date_range(date)
        values = meta.values()
        values.insert(0, end)
        values.insert(0, start)
        id = self.generate_key(values)

        self.lock.acquire()
        try:
            obs = self.get_observation(id, date, meta)
            #obs = cPickle.loads(obs)
            obs.add(value)
            #obs = obs.serialize()
            self.obs[id] = obs
        finally:
            self.lock.release()
        #print date, id, meta.values(), obs.count, len(self.obs)

    def number_of_obs(self):
        '''Determine the total number of observations in a Variable'''
        n = 0
        for obs in self.obs:
            n += self.obs[obs].count
        return n

    def encode(self):
        '''
        Convert Variable to a bson object to store in Mongo
        '''
        bson = {}
        for prop in self.props:
            bson[prop] = getattr(self, prop)

        bson['obs'] = {}
        for obs in self:
            data = self.obs[obs]
            obs = str(obs)
            bson['obs'][obs] = data.encode_to_bson()
        return bson

    def decode(self, values):
        '''Decode a BSON object in a Variable'''
        for varname in values:
            for prop in values[varname]:
                if isinstance(values[varname][prop], dict):
                    data = values[varname][prop]
                    for d in data:
                        date = data[d]['date']
                        obs = data[d]['data']
                        self.add(date, obs)
                else:
                    setattr(self, prop, values[varname][prop])
                    self.props.append(prop)

    def get_date_range(self):
        '''Determine the date for the first and last observation'''
        dates = [self.obs[key].date for key in self]
        try:
            first = min(dates)
        except ValueError:
            first = '.'
        try:
            last = max(dates)
        except ValueError:
            last = '.'
        return first, last


class Dataset:
    '''
    This class acts as a container for the Variable class and has some methods
    to output the dataset to a csv file, mongodb and display statistics.
    '''

    def __init__(self, chart, rts, variables=None, **kwargs):
        self.encoder, chart_type, charts = json_encoders.get_json_encoder(chart)
        if self.encoder == None:
            raise exceptions.UnknownPluginError(chart_type, charts)
        self.chart = chart
        self.name = 'Dataset to construct %s' % self.chart
        self.project = rts.project.name
        self.collection = rts.editors_dataset
        self.language_code = rts.language.code
        self.hash = self.name
        self._type = 'dataset'
        self.created = datetime.datetime.today()
        self.format = 'long'
        for kw in kwargs:
            setattr(self, kw, kwargs[kw])
        self.props = self.__dict__.keys()

        self.variables = []
        if variables != None:
            for kwargs in variables:
                name = kwargs.pop('name')
                setattr(self, name, Variable(name, **kwargs))
                self.variables.append(name)

    def __repr__(self):
        return 'Dataset contains %s variables' % (len(self.variables))

    def __iter__(self):
        for variable in self.variables:
            yield getattr(self, variable)

    def details(self):
        print 'Project: %s%s' % (self.language_code, self.project)
        print 'JSON encoder: %s' % self.encoder
        print 'Raw data was retrieved from: %s%s/%s' % (self.language_code,
                                                        self.project,
                                                        self.collection)

    def create_filename(self):
        '''
        This function creates a filename for the dataset by searching for shared
        properties among the different variables in the dataset. All shared
        properties will be used in the filename to make sure that one analysis
        that's run with different parameters gets stored in separate files.
        '''
        common = {}
        props = set()
        for variable in self.variables:
            s = set()
            variable = getattr(self, variable)
            for prop in variable.props:
                if prop not in ['name', 'time_unit', '_type']:
                    s.add(prop)
                    props.add(prop)
            common[variable.name] = s

        keys = []
        for prop in props:
            attrs = []
            for s in common.values():
                attrs.append(prop)
            if len(attrs) == len(common.values()):
                keys.append(prop)
        keys.sort()
        attrs = '_'.join(['%s=%s' % (key, getattr(self, key)) for key in keys])
        filename = '%s%s_%s_%s.csv' % (self.language_code,
                                       self.project,
                                       self.chart,
                                       attrs)
        return filename

    def add_variable(self, variables):
        '''
        Call this function to add a Variable to a dataset. 
        '''
        if not isinstance(variables, list):
            variables = [variables]
        for variable in variables:
            if isinstance(variable, Variable):
                self.variables.append(variable.name)
                setattr(self, variable.name, variable)
            else:
                raise TypeError('You can only add an instance of Variable to a dataset.')

    def write(self, format='csv'):
        '''
        This is the entry point for outputting data, either to csv or mongo.
        '''
        if format == 'csv':
            filename = self.create_filename()
            self.to_csv(filename)
        elif format == 'mongo':
            self.to_mongo()

    def to_mongo(self):
        dbname = '%s%s' % (self.language_code, self.project)
        db = storage.init_database(self.rts.storage, dbname, 'charts')
        db.add_son_manipulator(Transform())
        db.remove({'hash':self.hash, 'project':self.project,
                    'language_code':self.language_code})
        db.insert({'variables': self})

    def to_csv(self, filename):
        data = data_converter.convert_dataset_to_lists(self, 'manage')
        headers = data_converter.add_headers(self)
        lock = RLock()
        fh = file_utils.create_txt_filehandle(settings.dataset_location,
                                              filename,
                                              'w',
                                              'utf-8')
        file_utils.write_list_to_csv(headers, fh, recursive=False, newline=True)
        file_utils.write_list_to_csv(data, fh, recursive=False, newline=True,
                                     format=self.format,
                                     lock=lock)
        fh.close()

    def encode(self):
        props = {}
        for prop in self.props:
            props[prop] = getattr(self, prop)
        return props

    def descriptives(self):
        '''
        Calculate some simple descriptive statistics to describe the Dataset.
        '''
        for variable in self:
            data = variable.get_data()
            variable.mean = get_mean(data)
            variable.median = get_median(data)
            variable.sds = get_standard_deviation(data)
            variable.min = get_min(data)
            variable.max = get_max(data)
            variable.num_obs = variable.number_of_obs()
            variable.num_dates = len(variable)
            variable.first_obs, variable.last_obs = variable.get_date_range()

    def summary(self):
        '''
        Summarize the contents of the Dataset by calculating some simple
        descriptives statistics. 
        '''
        self.descriptives()
        table = Texttable(max_width=0)
        variables = ['Variable', 'Mean', 'Median', 'SD', 'Minimum', 'Maximum',
                'Num Obs', 'Num of\nUnique Groups', 'First Obs', 'Final Obs']
        table.add_row([variable for variable in variables])
        table.set_cols_align(['r' for variable in variables])
        table.set_cols_valign(['m' for variable in variables])

        for x, variable in enumerate(self):
            table.add_row([variable.name, variable.mean, variable.median,
                            variable.sds, variable.min, variable.max,
                            variable.num_obs, variable.num_dates,
                            variable.first_obs, variable.last_obs])
        print table.draw()
        print self
        print self.details()


def get_standard_deviation(number_list):
    '''Given a list of numbers, calculate the standard deviation of the list'''
    mean = get_mean(number_list)
    std = 0
    n = len(number_list)
    for i in number_list:
        std = std + (i - mean) ** 2
    try:
        return math.sqrt(std / float(n - 1))
    except ZeroDivisionError:
        return '.'

def get_median(number_list):
    '''Given a list of numbers, calculate the median of the list'''
    if number_list == []:
        return '.'
    data = sorted(number_list)
    data = [float(x) for x in data]
    if len(data) % 2 == 1:
        return data[(len(data) + 1) / 2 - 1]
    else:
        lower = data[len(data) / 2 - 1]
        upper = data[len(data) / 2]
    return (lower + upper) / 2

def get_mean(number_list):
    '''Given a list of numbers, calculate the average of the list'''
    if number_list == []:
        return '.'
    float_nums = [float(x) for x in number_list]
    return sum(float_nums) / len(number_list)

def get_min(number_list):
    '''Given a list of numbers, return the lowest number'''
    if number_list == []:
        return '.'
    else:
        return min(number_list)

def get_max(number_list):
    '''Given a list of numbers, return the highest number'''
    if number_list == []:
        return '.'
    else:
        return max(number_list)


def debug():
    db = storage.init_database('mongo', 'wikilytics', 'enwiki_charts')
    #db.add_son_manipulator(Transform())

    lock = RLock()
    v = Variable('test', 'year', lock, {})

    for x in xrange(100000):
        year = random.randrange(2005, 2010)
        month = random.randrange(1, 12)
        day = random.randrange(1, 28)
        d = datetime.datetime(year, month, day)
        x = random.randrange(1, 10000)
        v.add(d, x, {'username': 'diederik'})
    gc.collect()

#    d1 = datetime.datetime.today()
#    d2 = datetime.datetime(2007, 6, 7)
#    ds = Dataset('histogram', rts, [{'name': 'count', 'time_unit': 'year'},
#                                   #{'name': 'testest', 'time_unit': 'year'}
#                                   ])
#    ds.count.add(d1, 10, {'exp': 3})
#    ds.count.add(d1, 135, {'exp': 3})
#    ds.count.add(d2, 1, {'exp': 4})
#    #ds.testest.add(d1, 135)
#    #ds.testest.add(d2, 535)
#    ds.summary()
#    ds.write(format='csv')
#
#    ds.encode()
    #name, time_unit, lock, **kwargs


#        v.add(d, 135, {'exp': 3, 'test': 10})
#        v.add(d, 1, {'exp': 4, 'test': 10})
#        v.add(d, 1, {'exp': 4, 'test': 10})
#        v.add(d , 1, {'exp': 3, 'test': 8})
#        v.add(d , 1, {'exp': 2, 'test': 10})
#        v.add(d , 1, {'exp': 4, 'test': 11})
#        v.add(d , 1, {'exp': 8, 'test': 13})
#        v.add(d , 1, {'exp': 9, 'test': 12})
    #mem = get_refcounts()

#    v.add(d2 + timedelta(days=400), 1, {'exp': 4, 'test': 10})
#    v.add(d2 + timedelta(days=900), 1, {'exp': 3, 'test': 8})
#    v.add(d2 + timedelta(days=1200), 1, {'exp': 2, 'test': 10})
#    v.add(d2 + timedelta(days=1600), 1, {'exp': 4, 'test': 11})
#    v.add(d2 + timedelta(days=2000), 1, {'exp': 8, 'test': 13})
#    v.add(d2 + timedelta(days=2400), 1, {'exp': 9, 'test': 12})

#    print len(v), v.number_of_obs()

 #   mongo.test.insert({'variables': ds})
if __name__ == '__main__':
    #cProfile.run('debug()')
    debug()
