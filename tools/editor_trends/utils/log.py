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
__date__ = '2011-01-11'
__version__ = '0.1'

import datetime
import sys
import progressbar
if '..' not in sys.path:
    sys.path.append('..')

from classes import settings
settings = settings.Settings()

from classes import storage

def to_db(rts, jobtype, task, timer, event='start'):
    db = storage.init_database(rts.storage, rts.dbname, 'jobs')
    created = datetime.datetime.now()
    job = db.find_one({'hash': rts.id})
    #print job
    data = {'hash': rts.id,
          'created': created,
          'jobtype': jobtype,
          'in_progress': True,
          'language_code': rts.language.code,
          'project': rts.project.name,
          'tasks': {},
          }

    if job == None:
        if jobtype == 'dataset':
            data['finished'] = False
            _id = db.save(data)
        elif jobtype == 'chart':
            data['finished'] = True
            _id = db.save(data)

        job = db.find_one({'_id': _id})

    tasks = job['tasks']
    t = tasks.get(task, {})
    if event == 'start':
        t['start'] = timer.t0
        t['in_progress'] = True
        tasks[task] = t
        db.update('hash', rts.id, {'$set': {'tasks': tasks}})
        #coll.update({'hash': hash}, {'$set': {'tasks': tasks}})
    elif event == 'finish':
        t['finish'] = timer.t1
        t['in_progress'] = False
        tasks[task] = t
        if task == 'transform' or jobtype == 'chart':
            #final task, set entire task to finished
            db.update('hash', rts.id, {'$set': {'tasks': tasks,
                                                 'in_progress': False,
                                                 'finished': True}})
        else:
            db.update('hash', rts.id, {'$set': {'tasks': tasks}})


def to_csv(logger, settings, message, verb, function, **kwargs):
    '''
    Writes detailed log information to logs / projectname_date.csv
    '''
    logger.debug('%s\tStarting %s' \
        % (datetime.datetime.now(), function.func_name))
    if message:
        logger.debug('%s\t%s' % (datetime.datetime.now(), message))

    try:
        max_length = max([len(prop) for prop in kwargs if type(prop) != type(True)])
    except ValueError:
        max_length = 0
    max_tabs = max_length // settings.tab_width
    res = max_length % settings.tab_width
    if res > 0:
        max_tabs += 1
    pos = max_tabs * settings.tab_width
    for prop in kwargs:
        if verb:
            logger.debug('%s\tAction: %s\tSetting: %s' \
                % (datetime.datetime.now(), verb, kwargs[prop]))
        else:
            tabs = (pos - len(prop)) // settings.tab_width
            res = len(prop) % settings.tab_width
            if res > 0 or tabs == 0:
                tabs += 1
            tabs = ''.join(['\t' for t in xrange(tabs)])
            logger.debug('%s\t\tKey: %s%sSetting: %s' \
                % (datetime.datetime.now(),
                   prop,
                   tabs,
                   kwargs[prop]))


def init_progressbar_widgets(description):
    widgets = ['%s: ' % description, progressbar.Percentage(), ' ',
               progressbar.Bar(marker=progressbar.RotatingMarker()), ' ',
               progressbar.ETA(), ' ', progressbar.FileTransferSpeed()]
    return widgets


# error tracking related functions
def track_errors(xml_buffer, error, file, messages):
    text = extract_offending_string(xml_buffer.getvalue(), error)

    vars = {}
    vars['file'] = file
    vars['error'] = error
    vars['text'] = text
    #print file, error, text
    key = remove_error_specific_information(error)
    if key not in messages:
        messages[key] = {}
    if messages[key] == {}:
        c = 0
    else:
        counters = messages[key].keys()
        counters.sort()
        counters.reverse()
        c = counters[-1]

    messages[key][c] = {}
    for var in vars:
        messages[key][c][var] = vars[var]

    return messages


def report_error_messages(messages, function):
    store_object(messages, settings.log_location, function.func_name)
    errors = messages.keys()
    for error in errors:
        for key, value in messages[error].iteritems():
            print error, key, value


def remove_error_specific_information(e):
    pos = e.args[0].find('line')
    if pos > -1:
        return e.args[0][:pos]
    else:
        return e.args[0]


def extract_offending_string(text, error):
    '''
    This function determines the string that causes an error when feeding it to
    the XML parser. This is only useful for debugging purposes.
    '''
    location = re.findall(RE_ERROR_LOCATION, error.args[0])
    if location != []:
        location = int(location[0]) - 1
        text = text.split('\n')[location]
        text = text.decode('utf-8')
        return text
    else:
        return ''
