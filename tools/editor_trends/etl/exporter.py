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
__author__email = 'dvanliere at gmail dot com'
__date__ = '2010-10-21'
__version__ = '0.1'

import os
import sys
import datetime
import calendar
from dateutil.relativedelta import *
import multiprocessing
from Queue import Empty



sys.path.append('..')
import configuration
settings = configuration.Settings()
from utils import utils
from utils import messages
from database import db
from etl import shaper
from analyses import cohort_charts

try:
    import psyco
    psyco.full()
except ImportError:
    pass


class Variable(object):
    '''
    This class constructs a time-based variable and has some associated simple 
    statistical descriptives
    '''
    def __init__(self, var):
        self.name = var
        self.obs = []
        self.stats = ['n', 'avg', 'sd', 'min', 'max']

    def __repr__(self):
        return self.name

    def descriptives(self):
        self.time = shaper.create_datacontainer()
        self.time = shaper.add_months_to_datacontainer(getattr(self, 'time'), datatype='dict')

        for var in self.stats:
            setattr(self, var, shaper.create_datacontainer())
            setattr(self, var, shaper.add_months_to_datacontainer(getattr(self, var), datatype='list'))

        for year in self.time:
            for month in self.time[year]:
                data = [self.time[year][month][k] for k in self.time[year][month].keys()]
                self.avg[year][month] = shaper.get_mean(data)
                self.sd[year][month] = shaper.get_standard_deviation(data)
                self.min[year][month] = min(data)
                self.max[year][month] = max(data)
                self.n[year][month] = len(data)


class LongDataset(object):
    '''
    This class acts as a container for the Variable class and has some methods
    to output the dataset to a csv file. 
    '''
    def __init__(self, vars, name):
        self.name = name
        self.vars = []
        for var in vars:
            setattr(self, var, Variable(var))
            self.vars.append(var)

    def __repr__(self):
        return 'Dataset containing: %s' % (self.vars)

    def write_headers(self, fh):
        fh.write('_time\t')
        for var in self.vars:
            var = getattr(self, var)
            fh.write('%s\t' % var.name)
            #for stat in var.stats:
            #    fh.write('%s_%s\t' % (var.name, stat))
        fh.write('\n')

    def convert_to_longitudinal_data(self, id, obs, vars):
        for var in vars:
            ds = getattr(self, var)
            years = obs[var].keys()
            for year in years:
                months = obs[var][year].keys()
                for m in months:
                    #d = calendar.monthrange(int(year), int(m))[1] #determines the number of days in a given month/year
                    #date = datetime.date(int(year), int(m), d)
                    if id not in ds.time[year][m] and obs[var][year][m] > 0:
                        ds.time[year][m][id] = obs[var][year][m]

    def write_longitudinal_data(self, write_time=True):
        fh = utils.create_txt_filehandle(settings.dataset_location, self.name, 'w', settings.encoding)
        self.write_headers(fh)
        for var in self.vars:
            var = getattr(self, var)
            for o in var.obs:
                if write_time:
                    fh.write('%s\t%s\n' % (o[0], o[1]))
                else:
                    fh.write('%s\n' % (o[1]))
        fh.close()

#        windows = create_windows()
#        dc = shaper.create_datacontainer()
#        dc = shaper.add_months_to_datacontainer(dc, windows)
#
##        for var in self.vars:
##            var = getattr(self, var)
##            var.descriptives()
#        years = dc.keys()
#        years.sort()
#        for year in years:
#            months = dc[year].keys()
#            months.sort()
#            for month in months:
#                d = calendar.monthrange(int(year), int(month))[1] #determines the number of days in a given month/year
#                date = datetime.date(int(year), int(month), d)
#                fh.write('%s\t' % date)
#                for var in self.vars:
#                    var = getattr(self, var)
#                    #data = ['%s_%s\t' % (var.name, getattr(var, stat)[year][month]) for stat in var.stats]
#                    fh.write(''.join([ % s\t]))
#                    #fh.write(''.join(['%s\t' % (getattr(var, stat)[year][month],) for stat in var.stats]))
#                fh.write('\n')



def expand_edits(edits):
    data = []
    for edit in edits:
        data.append(edit['date'])
    return data


def expand_observations(obs, vars_to_expand):
    for var in vars_to_expand:
        if var == 'edits':
            obs[var] = expand_edits(obs[var])
        elif var == 'edits_by_year':
            keys = obs[var].keys()
            keys.sort()
            edits = []
            for key in keys:
                edits.append(obs[var][key])
            obs[var] = edits
    return obs



def expand_headers(headers, vars_to_expand, obs):
    for var in vars_to_expand:
        l = len(obs[var])
        pos = headers.index(var)
        for i in xrange(l):
            if var.endswith('year'):
                suffix = 2001 + i
            elif var.endswith('edits'):
                suffix = 1 + i
            headers.insert(pos + i, '%s_%s' % (var, suffix))
        headers.remove(var)
    return headers


def generate_long_editor_dataset(tasks, dbname, collection, **kwargs):
    mongo = db.init_mongo_db(dbname)
    editors = mongo[collection + '_dataset']
    vars = ['monthly_edits']
    name = dbname + '_long_editors.csv'
    #fh = utils.create_txt_filehandle(settings.dataset_location, name, 'w', settings.encoding)
    vars_to_expand = []
    keys = dict([(var, 1) for var in vars])
    ld = LongDataset(vars)
    while True:
        try:
            id = input_queue.get(block=False)
            if id == None:
                break
            print id
            obs = editors.find_one({'editor': id}, keys)
            ld.convert_to_longitudinal_data(id, obs, vars)
            #utils.write_list_to_csv(data, fh)
        except Empty:
            break
    ld.write_longitudinal_data()


def create_windows(break_down_first_year=True):
    '''
    This function creates a list of months. If break_down_first_year = True then
    the first year will be split in 3, 6, 9 months as well. 
    '''
    years = (datetime.datetime.now().year + 1) - 2001
    windows = [y * 12 for y in xrange(1, years)]
    if break_down_first_year:
        windows = [3, 6, 9] + windows
    return windows



def diff_month(d1, d2):
    return (d1.year - d2.year) * 12 + d1.month - d2.month


def generate_cohort_dataset_raw(tasks, dbname, collection):
    mongo = db.init_mongo_db(dbname)
    editors = mongo['%s%s' % (collection, '_dataset')]
    windows = create_windows()
    data = shaper.create_datacontainer('dict')
    final_year = datetime.datetime.now().year + 1
    ld = LongDataset(['experience'], '%s_forward_cohort.csv' % dbname)
    while True:
        id = tasks.get(block=False)
        tasks.task_done()
        if id == None:
            break
        obs = editors.find_one({'editor': id},
                               {'new_wikipedian': 1,
                                'monthly_edits': 1,
                                'final_edit':1
                                })

        new_wikipedian = obs['new_wikipedian']
        last_edit = obs['final_edit']
        dt = diff_month(last_edit, new_wikipedian)
        day = calendar.monthrange(new_wikipedian.year, new_wikipedian.month)[1]
        tenth_edit = datetime.date(new_wikipedian.year, new_wikipedian.month, day)
        ld.experience.obs.append([tenth_edit, dt])

    ld.write_longitudinal_data()


def generate_cohort_dataset_forward(tasks, dbname, collection):
    mongo = db.init_mongo_db(dbname)
    editors = mongo[collection + '_dataset']
    final_year = datetime.datetime.now().year + 1
    windows = create_windows()
    data = shaper.create_datacontainer('dict')
    while True:
        id = tasks.get(block=False)
        if id == None:
            break

        obs = editors.find_one({'editor': id}, {'new_wikipedian': 1, 'monthly_edits': 1, 'final_edit':1})
        new_wikipedian = obs['new_wikipedian']
        year = new_wikipedian.year

        last_edit = obs['final_edit']
        edits = obs['monthly_edits']

        if new_wikipedian.month not in data[new_wikipedian.year]:
            data[new_wikipedian.year][new_wikipedian.month] = {}
        for i, year in enumerate(xrange(new_wikipedian.year, final_year)):
            min_edits = min(obs['monthly_edits'].values())
            if min_edits < 5:
                continue
            months = edits.get(str(year), [])
#            if i == 0:
#                months = months.keys()
#                months = [int(m) for m in months]
#                months.sort()
#                months = months[new_wikipedian.month - 1:]
#                months = [str(m) for m in months]
            for month in months:
                experience = str(i * 12 + (int(month) - 1))
                if experience not in data[new_wikipedian.year][new_wikipedian.month]:
                    data[new_wikipedian.year][new_wikipedian.month][experience] = 0
                data[new_wikipedian.year][new_wikipedian.month][experience] += 1 if edits[str(year)][month] > 0 else 0

    filename = 'cohort_data_forward.bin'
    print 'Storing data as %s' % os.path.join(settings.binary_location, '%s_%s' % (dbname, filename))
    utils.store_object(data, settings.binary_location, '%s_%s' % (dbname, filename))
    cohort_charts.prepare_cohort_dataset(dbname, filename)

    filename = '_cohort_data_forward_histogram.csv'
    fh = utils.create_txt_filehandle(settings.dataset_location, '%s_%s' % (dbname, filename), 'w', settings.encoding)
    for year in data:
        for month in data[year]:
            obs = data[year][month].keys()
            obs.sort()
            for o in obs:
                utils.write_list_to_csv(['%s-%s' % (month, year), o, data[year][month][o]], fh, recursive=False, format='long')
    fh.close()

def generate_cohort_dataset_backward(tasks, dbname, collection, **kwargs):
    mongo = db.init_mongo_db(dbname)
    editors = mongo[collection + '_dataset']
    windows = create_windows(break_down_first_year=False)
    data = shaper.create_datacontainer('dict')
    data = shaper.add_windows_to_datacontainer(data, windows)

    while True:
        id = tasks.get(block=False)
        tasks.task_done()
        if id == None:
            break
        obs = editors.find_one({'editor': id}, {'first_edit': 1, 'final_edit': 1, 'edits_by_year': 1, 'last_edit_by_year': 1})
        first_edit = obs['first_edit']
        for year in xrange(2001, datetime.datetime.now().year + 1):
            year = str(year)
            if obs['edits_by_year'][year] > 0:
                last_edit = obs['last_edit_by_year'][year]
                editor_dt = relativedelta(last_edit, first_edit)
                editor_dt = (editor_dt.years * 12) + editor_dt.months
                for w in windows:
                    if w >= editor_dt:
                        data[int(year)][w] += 1
                        break
    filename = 'cohort_data_backward.bin'
    print 'Storing data as %s' % os.path.join(settings.binary_location, '%s_%s' % (dbname, filename))
    utils.store_object(data, settings.binary_location, '%s_%s' % (dbname, filename))
    cohort_charts.prepare_cohort_dataset(dbname, filename)



def generate_cohort_dataset_backward_custom(tasks, dbname, collection):
    mongo = db.init_mongo_db(dbname)
    editors = mongo[collection + '_dataset']
    windows = create_windows()
    data = shaper.create_datacontainer('dict')
    data = shaper.add_windows_to_datacontainer(data, windows)

    while True:
        id = tasks.get(block=False)
        tasks.task_done()
        if id == None:
            break
        obs = editors.find_one({'editor': id}, {'first_edit': 1, 'final_edit': 1, 'monthly_edits':1, 'edits_by_year': 1, 'last_edit_by_year': 1})
        first_edit = obs['first_edit']

        if obs['monthly_edits']['2010']['8'] > 4:
            for year in xrange(2001, datetime.datetime.now().year + 1):
                if obs['edits_by_year'].get(str(year), 0) > 0:
                    last_edit = obs['last_edit_by_year'][str(year)]
                    editor_dt = relativedelta(last_edit, first_edit)
                    editor_dt = (editor_dt.years * 12) + editor_dt.months
                    for w in windows:
                        if w >= editor_dt:
                            data[int(year)][w] += 1
                            break
        filename = 'august_2010_cohort_data_.bin'
        utils.store_object(data, settings.binary_location, '%s_%s' % (dbname, filename))
        cohort_charts.prepare_cohort_dataset(dbname, filename)





def generate_wide_editor_dataset(tasks, dbname, collection, **kwargs):
    mongo = db.init_mongo_db(dbname)
    editors = mongo[collection + '_dataset']
    name = dbname + '_wide_editors.csv'
    fh = utils.create_txt_filehandle(settings.dataset_location, name, 'a', settings.encoding)
    x = 0
    vars_to_expand = ['edits', 'edits_by_year', 'articles_by_year']
    while True:
        try:
            id = input_queue.get(block=False)
            if id == None:
                break
            print messages.show(input_queue.qsize)
            obs = editors.find_one({'editor': id})
            obs = expand_observations(obs, vars_to_expand)
            if x == 0:
                headers = obs.keys()
                headers.sort()
                headers = expand_headers(headers, vars_to_expand, obs)
                utils.write_list_to_csv(headers, fh)
            data = []
            keys = obs.keys()
            keys.sort()
            for key in keys:
                data.append(obs[key])
            utils.write_list_to_csv(data, fh)

            x += 1
        except Empty:
            break
    fh.close()


def dataset_launcher(dbname, collection, target):
    if type(target) == type('str'):
        target = globals()[target]
    editors = db.retrieve_distinct_keys(dbname, collection, 'editor')
    tasks = multiprocessing.JoinableQueue()
    #consumers = [multiprocessing.Process(target=target, args=(tasks, dbname, collection)) for i in xrange(settings.number_of_processes)]
    for editor in editors:
        tasks.put(editor)
    print 'The queue contains %s editors.' % messages.show(tasks.qsize)
    tasks.put(None)
    target(tasks, dbname, collection)

    #for x in xrange(settings.number_of_processes):
    #    tasks.put(None)

    #for w in consumers:
    #    w.start()

    #tasks.join()


def debug(dbname, collection):
    editors = {
               '1':{'first_edit': datetime.datetime(2009, 10, 1), 'final_edit': datetime.datetime(2009, 11, 30)},
               '2':{'first_edit': datetime.datetime(2009, 12, 1), 'final_edit': datetime.datetime(2010, 2, 27)},
               '3':{'first_edit': datetime.datetime(2009, 3, 1), 'final_edit': datetime.datetime(2009, 11, 30)},
               '4':{'first_edit': datetime.datetime(2007, 1, 1), 'final_edit': datetime.datetime(2008, 4, 30)},
               '5':{'first_edit': datetime.datetime(2006, 5, 1), 'final_edit': datetime.datetime(2009, 7, 30)},
               '6':{'first_edit': datetime.datetime(2008, 11, 1), 'final_edit': datetime.datetime(2009, 6, 30)},
               '7':{'first_edit': datetime.datetime(2009, 1, 1), 'final_edit': datetime.datetime(2009, 10, 30)},
               '8':{'first_edit': datetime.datetime(2009, 7, 1), 'final_edit': datetime.datetime(2009, 7, 30)},
               '9':{'first_edit': datetime.datetime(2009, 12, 1), 'final_edit': datetime.datetime(2010, 11, 30)},
               '10':{'first_edit': datetime.datetime(2008, 5, 1), 'final_edit': datetime.datetime(2010, 11, 30)},
               '11':{'first_edit': datetime.datetime(2007, 2, 1), 'final_edit': datetime.datetime(2010, 3, 30)},
               '12':{'first_edit': datetime.datetime(2007, 2, 1), 'final_edit': datetime.datetime(2008, 2, 27)},
               '13':{'first_edit': datetime.datetime(2007, 2, 1), 'final_edit': datetime.datetime(2009, 4, 30)},
               }
    generate_cohort_dataset(editors, dbname, collection)


if __name__ == '__main__':
    dbname = 'enwiki'
    collection = 'editors'
    #debug(dbname, collection)
    dataset_launcher(dbname, collection, generate_cohort_dataset_backward_custom)
    #dataset_launcher(dbname, collection, generate_long_editor_dataset)
    #dataset_launcher(dbname, collection, generate_wide_editor_dataset)