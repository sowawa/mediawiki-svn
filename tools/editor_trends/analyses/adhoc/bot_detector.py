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
__date__ = '2010-11-25'
__version__ = '0.1'


import os
import cStringIO
import multiprocessing
import xml.etree.cElementTree as cElementTree
import sys
from Queue import Empty

if '..' not in sys.path:
    sys.path.append('..')

from classes import settings
settings = settings.Settings()


from classes import storage
from utils import file_utils
from utils import messages

from classes import consumers
from classes import bots


def read_bots_csv_file(location, filename, encoding, manager=False):
    '''
    Constructs a dictionary from Bots.csv
    key is language
    value is a list of bot names
    '''
    if manager:
        bot_dict = manager.dict()
    else:
        bot_dict = dict()
    for line in file_utils.read_data_from_csv(location, filename, encoding):
        line = line.strip()
        language, bots = line.split(',')
        bots = bots.split('|')
        for bot in bots:
            if bot not in bot_dict:
                b = botconsumers.Bot(bot)
                b.id = None
            else:
                b = bot_dict[bot]
            b.projects.append(language)
            bot_dict[bot] = b
    return bot_dict


def retrieve_bots(db_type, language_code):
    '''
    Loader function to retrieve list of id's of known Wikipedia bots.
    '''
    bots = []
    db = storage.init_database(db_type, 'bots', 'ids')
    cursor = db.find()
    for bot in cursor:
        if bot['verified'] == 'True' and language_code in bot['projects']:
            bots.append(bot['name'])
    return bots


def store_bots():
    '''
    This file reads the results from the lookup_bot_userid function and stores
    it in a MongoDB collection.
    '''
    keys = ['name', 'verified', 'projects']
    bots = file_utils.create_dict_from_csv_file(settings.csv_location,
                                                'bots_ids.csv',
                                                'utf-8',
                                                keys)
    db = storage.init_database(rts.storage, 'wikilytics', 'bots')
    db.drop_collection()
    for id in bots:
        bot = bots[id]
        data = dict([(k, bot[k]) for k in keys])
        data['id'] = id
        db.insert(data)

    print 'Stored %s bots' % db.count()


def convert_object_to_dict(obj, exclude=[]):
    '''
    @obj is an arbitray object where the properties need to be translated to
    keys and values to ease writing to a csv file.
    '''
    d = {}
    for kw in obj.__dict__.keys():
        if kw not in exclude:
            d[kw] = getattr(obj, kw)
    return d


def write_bot_list_to_csv(bots, keys):
    fh = file_utils.create_txt_filehandle(settings.csv_location, 'bots_ids.csv',
                                          'w', 'utf-8')
    bot_dict = convert_object_to_dict(bots, exclude=['time', 'written'])
    for bot in bot_dict:
        bot = bot_dict[bot]
        file_utils.write_dict_to_csv(bot, fh, keys, write_key=False,
                                     newline=True)
    fh.close()


def lookup_bot_userid(data, fh, bots, keys):
    '''
    This function is used to find the id's belonging to the different bots that
    are patrolling the Wikipedia sites.
    @xml_nodes is a list of xml elements that need to be parsed
    @bots is a dictionary containing the names of the bots to lookup
    '''
    username = data[3]
    if username in bots:
        bot = bots.pop(username)
        setattr(bot, 'id', data[0])
        setattr(bot, 'verified', True)
        bot = convert_object_to_dict(bot, exclude=['time'])
        file_utils.write_dict_to_csv(bot, fh, keys, write_key=False, newline=True)
    return bots


def create_bot_validation_dataset(xml_nodes, fh, bots):
    revisions = xml_nodes.findall('revision')
    for revision in revisions:
        contributor = revision.find('contributor')
        #contributor = xml.retrieve_xml_node(revision, 'contributor')
        username = contributor.find('username')
        if username == None or username.text == None:
            continue
        else:
            username = username.text.lower()

        #print username.encode('utf-8')
        if username.find('bot') > -1 or username.find('script') > -1:
            bot = bots.get(username, botconsumers.Bot(username, verified=False))
            bot.id = contributor.find('id').text
            timestamp = revision.find('timestamp').text
            if timestamp != None:
                timestamp = file_utils.convert_timestamp_to_datetime_naive(timestamp, settings.timestamp_format)
                bot.time[str(timestamp.year)].append(timestamp)
            bots[username] = bot

    return bots

    #bot = bots.get('PseudoBot')
    #bot.hours_active()
    #bot.avg_lag_between_edits()


def bot_launcher(language_code, project, target, action, single=False, manager=False):
    '''
    This function sets the stage to launch bot id detection and collecting data
    to discover new bots.
    '''
    file_utils.delete_file(settings.csv_location, 'bots_ids.csv')
    location = os.path.join(settings.input_location, language_code, project)
    input_xml = os.path.join(location, 'chunks')
    input_txt = os.path.join(location, 'txt')

    tasks = multiprocessing.JoinableQueue()
    mgr = multiprocessing.Manager()
    keys = ['id', 'name', 'verified', 'projects']

    if action == 'lookup':
        output_file = 'bots_ids.csv'
        files = file_utils.retrieve_file_list(input_txt, 'txt', mask=None)
        input_queue = pc.load_queue(files, poison_pill=True)
        bots = read_bots_csv_file(settings.csv_location, 'Bots.csv', 'utf-8', manager=manager)
        for file in files:
            tasks.put(consumers.TXTFile(file, input_txt, settings.csv_location, output_file, target, bots=bots, keys=keys))

    else:
        output_file = 'bots_predictionset.csv'
        files = file_utils.retrieve_file_list(input_xml, 'xml', mask=None)
        bots = {}
        for file in files:
            tasks.put(consumers.XMLFile(file, input_xml, settings.csv_location, output_file, target, bots=bots, keys=keys))

    #lock = mgr.Lock()
    if manager:
        manager = mgr

    tracker = {}
    if single:
        while True:
            try:
                print '%s files left in the queue...' % messages.show(tasks.qsize)
                task = tasks.get(block=False)
                bots = task(bots)
            except Empty:
                break
    else:
        bot_launcher_multi(tasks)

    file_utils.store_object(bots, settings.binary_location, 'bots.bin')
    if action == 'lookup':
        store_bots()
        if bots != {}:
            print 'The script was unable to retrieve the user id\s for the following %s bots:\n' % len(bots)
            keys = bots.keys()
            for key in keys:
                try:
                    print '%s' % key.encode('utf-8')
                except:
                    pass
    else:
        bot_training_dataset(bots)
        #write_bot_list_to_csv(bots, keys)


def bot_training_dataset(bots):
    fh = file_utils.create_txt_filehandle(settings.csv_location, 'training_bots.csv', 'w', 'utf-8')
    keys = bots.keys()
    for key in keys:
        bot = bots.get(key)
        bot.hours_active()
        bot.avg_lag_between_edits()
        bot.write_training_dataset(fh)

    fh.close()


def bot_launcher_multi(tasks):
    '''
    This is the launcher that uses multiprocesses.
    '''
    consumers = [consumers.XMLFileConsumer(tasks, None) for i in xrange(multiprocessing.cpu_count())]
    for x in xrange(multiprocessing.cpu_count()):
        tasks.put(None)

    for w in consumers:
        w.start()

    tasks.join()


def debug_bots_dict():
    bots = file_utils.load_object(settings.binary_location, 'bots.bin')
    for bot in bots:
        bot = bots[bot]
        edits = sum([len(bot.time[t]) for t in bot.time])
        print bot.name, bot.verified, edits
    print 'done'
    return bots


if __name__ == '__main__':
    language_code = 'en'
    project = 'wiki'
    #store_bots()
    #bots = debug_bots_dict()
    #write_bot_list_to_csv(bots)
    #language_code, project, lookup_bot_userid, single = False, manager = False
    bot_launcher(language_code, project, create_bot_validation_dataset, action='training', single=True, manager=False)
