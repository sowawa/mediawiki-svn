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

__author__ = '''\n'''.join(['Diederik van Liere (dvanliere@gmail.com)'])
__email__ = 'dvanliere at gmail dot com'
__date__ = '2011-04-05'
__version__ = '0.1'

import sys
import subprocess
from abc import ABCMeta, abstractmethod
if '..' not in sys.path:
    sys.path.append('..')

import settings
settings = settings.Settings()

import exceptions
import queue
from utils import file_utils

import_error = 0
try:
    import pymongo
    import bson
    from bson.code import Code
    from pymongo.errors import OperationFailure
except ImportError:
    import_error += 1
try:
    import pycassa
except ImportError:
    import_error += 1
if import_error == 2:
    raise exceptions.NoDatabaseProviderInstalled()


class AbstractDatabase:
    '''
    Abstract Database class that shows which functions at a minimum should be
    provided by the actual Database classes.  
    '''
    __metaclass__ = ABCMeta

    def __init__(self, dbname, collection):
        self.dbname = dbname
        self.collection = collection
        self.db = self.connect()

    def __repr__(self):
        return '%s:%s' % (self.dbname, self.collection)

    @abstractmethod
    def connect(self):
        '''Initializes a connection to a specific collection in a database'''

    @abstractmethod
    def drop_collection(self):
        '''Deletes a collection from a database'''

    @abstractmethod
    def add_index(self, key):
        '''Add index to a collection'''

    @abstractmethod
    def insert(self, data, qualifier):
        '''Insert observation to a collection'''

    @abstractmethod
    def update(self, key, value, data):
        '''Update an observation in a collection'''

    @abstractmethod
    def find(self, key, qualifier):
        '''Find multiple observations in a collection'''

    @abstractmethod
    def find_one(self, key, value, var=False):
        '''Find a single observation in a collection'''

    @abstractmethod
    def save(self, data):
        '''Saves an observation and returns the id from the db'''

    @abstractmethod
    def count(self):
        '''Counts the number of observations in a collection'''

class Mongo(AbstractDatabase):
    '''
    This class provides the functionality to talk to a MongoDB backend including
    inserting, finding, and updating data.
    '''
    def __init__(self, dbname, collection, master=None, slaves=[]):
        if master == None:
            self.master = 'localhost'
        else:
            self.master = master
        self.slaves = slaves
        self.port = 27017
        super(Mongo, self).__init__(dbname, collection)

    @classmethod
    def is_registrar_for(cls, storage):
        '''
        Register this class for the Mongo backend
        '''
        return storage == 'mongo'

    def connect(self):
        master = pymongo.Connection(host=self.master, port=self.port)
        if self.master == 'localhost':
            return master[self.dbname]
        else:
            slave_connections = []
            for slave in self.slaves:
                slave = pymongo.Connection(host=slave, port=self.port)
                slave_connections.append(slave)
            master_slave_connection = pymongo.MasterSlaveConnection(master, slave_connections)
            return master_slave_connection[self.dbname]

    def save(self, data):
        assert isinstance(data, dict), 'You need to feed me dictionaries.'
        return self.db[self.collection].save(data)

    def insert(self, data, qualifiers=None, safe=False):
        assert isinstance(data, dict), 'You need to feed me dictionaries.'
        data = self.stringify_keys(data)
        try:
            if qualifiers:
                self.db[self.collection].insert(data, qualifiers, safe=safe)
            else:
                self.db[self.collection].insert(data, safe=safe)
        except bson.errors.InvalidDocument, error:
            print error
            print 'BSON document too large, unable to store %s' % \
                (data.keys()[0])
        except OperationFailure, error:
            print 'It seems that you are running out of disk space. \
            Error message: %s' % error
            sys.exit(-1)
        except OverflowError, error:
            print '''It seems that you are trying to store an integer that is 
            too long. Error message: %s''' % error
            print 'Offending data: %s' % data
            sys.exit(-1)

    def update(self, key, value, data):
        assert isinstance(data, dict), 'You need to feed me dictionaries.'
        self.db[self.collection].update({key: value}, {'$set': data})

    def find(self, conditions, vars=None):
        if conditions:
            return self.db[self.collection].find(conditions, fields=vars)
        else:
            return self.db[self.collection].find()

    def find_one(self, conditions, vars=None):
        if vars:
            #if you only want to retrieve a specific variable(s) then you need to
            #specify vars, if vars is None then you will get the entire BSON object
            vars = vars.split(',')
            vars = dict([(var, 1) for var in vars])
            return self.db[self.collection].find_one(conditions, vars)
        else:
            #conditions should be a dictionary
            return self.db[self.collection].find_one(conditions)


    def drop_collection(self):
        self.db.drop_collection(self.collection)

    def add_index(self, key):
        self.db[self.collection].create_index(key)
        self.db[self.collection].ensure_index(key)

    def count(self):
        return self.db[self.collection].count()

    def retrieve_editors(self):
        q = queue.JoinableRetryQueue()
        cursor = self.find('editor')
        print 'Loading editors...'
        for editor in cursor:
            q.put(editor['editor'])
        print 'Finished loading editors...'
        return q

    def retrieve_distinct_keys(self, key, force_new=False):
        '''
        TODO: figure out how big the index is and then take appropriate action, 
        index < 4mb just do a distinct query, index > 4mb do a map reduce.
        '''
        print 'Check if distinct keys have previously been saved...'
        if force_new == False and \
            file_utils.check_file_exists(settings.binary_location, '%s_%s_%s.bin'
                                         % (self.dbname, self.collection, key)):
            print 'Loading distinct keys from previous session...'
            ids = file_utils.load_object(settings.binary_location, '%s_%s_%s.bin'
                                         % (self.dbname, self.collection, key))
        else:
            #TODO this is a bit arbitrary, should check if index > 4Mb.
            print 'Determining distinct keys...'
            if self.db[self.collection].count() < 200000:
                ids = self.db[self.collection].distinct(key)
            else:
                ids = self.retrieve_distinct_keys_mapreduce(key)
            file_utils.store_object(ids, settings.binary_location, \
                                    '%s_%s_%s.bin' % (self.dbname,
                                                      self.collection, key))
        return ids

    def retrieve_distinct_keys_mapreduce(self, key):
        '''
        This is to work around a Mongo limitation, if the index is too large
        then the distinct() function does not work. You need to do a map/reduce.
        '''
        emit = 'function () { emit(this.%s, 1)};' % key
        mapper = Code(emit)
        reducer = Code("function()")

        ids = []
        collection = '%s_%s' % (self.dbname, 'mapreduce_editors')
        cursor = self.db[self.collection].map_reduce(mapper, reducer, collection)
        for c in cursor.find():
            ids.append(c['_id'])
        return ids

    def stringify_keys(self, data):
        '''
        @data should be a dictionary where the keys are not yet strings. This 
        function is called just prior any insert / update query in mongo 
        because mongo only accepts strings as keys.
        '''
        new_data = {}
        for key, value in data.iteritems():
            if isinstance(value, dict):
                new_data[str(key)] = self.stringify_keys(value)
            else:
                new_data[str(key)] = value
        return new_data

    def start_server(self, path):
        '''
        Helper function to start MongoDB daemon.
        '''
        if settings.platform == 'Windows':
            subprocess.Popen([path, '--port %s', self.port, '--dbpath',
                                  'c:\data\db', '--logpath', 'c:\mongodb\logs'])
        elif settings.platform == 'Linux':
            subprocess.Popen([path, '--port %s' % self.port])
        elif settings.platform == 'OSX':
            raise exceptions.NotYetImplementedError
        else:
            raise exceptions.PlatformNotSupportedError(settings.platform)


class Cassandra(AbstractDatabase):
    '''
    This class provides the functionality to talk to a Cassandra backend 
    including inserting, finding, and updating data.
    '''
    def __init__(self, dbname, collection):
        super(Cassandra, self).__init__(dbname, collection)
        self.port = 9160
        self.host = '127.0.0.1'
        self.keyspace_name = '%s%s' % (self.dbname, self.collection)

    @classmethod
    def is_registrar_for(cls, storage):
        '''
        Register this class for the Cassandra backend
        '''
        return storage == 'cassandra'

    def install_schema(self, drop_first=False):
        '''
        In contrast to Mongo, Cassnadra 
        '''
        sm = pycassa.system_manager.SystemManager('%s:%s' % (self.host, self.port))
        if drop_first:
            sm.drop_keyspace(self.keyspace_name)

        sm.create_keyspace(self.keyspace_name, replication_factor=1)

        sm.create_column_family(self.dbname, self.collection,
                                comparator_type=pycassa.system_manager.UTF8_TYPE,
                                default_validation_class=pycassa.system_manager.UTF8_TYPE)

        sm.create_index(self.dbname, self.collection, 'article', pycassa.system_manager.UTF8_TYPE)
        sm.create_index(self.dbname, self.collection, 'username', pycassa.system_manager.UTF8_TYPE)
        sm.create_index(self.dbname, self.collection, 'user_id', pycassa.system_manager.LONG_TYPE)
        return sm

    def connect(self):
        self.db = pycassa.connect(self.dbname)
        self.collection = pycassa.ColumnFamily(self.dbname, self.collection)
        self.install_schema()

    def drop_collection(self):
        return

    def add_index(self, key):
        return

    def insert(self, data, qualifier):
        return

    def update(self, key, value, data):
        return

    def find(self, key, qualifier=None):
        return

    def save(self, data):
        return

    def count(self):
        return


def init_database(storage, dbname, collection):
    '''
    Calling this function will initialize the appropriate class based on 
    the storage variable. Valid choices are currently Mongo and Cassandra
    '''
    for cls in AbstractDatabase.__subclasses__():
        if cls.is_registrar_for(storage):
            return cls(dbname, collection)
    raise ValueError


if __name__ == '__main__':
    database = init_database('mongo', 'wikilytics', 'zhwiki_editors_raw')
    editors = database.retrieve_distinct_keys('editor', force_new=True)
    #db.insert({'foo':'bar'})
    #db.update('foo', 'bar', {})
    #db.drop_collection()
    print database
