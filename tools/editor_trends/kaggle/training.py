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
__date__ = '2011-04-12'
__version__ = '0.1'

import os
import sys
import cPickle
import codecs
from datetime import datetime
sys.path.append('../')

from classes import storage

location = '/home/diederik/wikimedia/en/wiki/kaggle_prediction_solution'
files = os.listdir(location)
files.reverse()

max_size = 2147483648
max_size_reached = False

t0 = datetime.now()
titles = {}
ids = set()
dates = {}
edits = {}
ignore_ids = set()
size = 0
cnt_obs = 0
cutoff_date = datetime(2010, 8, 31)

print 'Constructing training dataset...'
db = storage.init_database('mongo', 'wikilytics', 'enwiki_editors_dataset')
dataset = codecs.open('training.tsv', 'w', 'utf-8')
for filename in files:
    if not filename.startswith('comments') and not filename.startswith('articles'):
        fh = codecs.open(os.path.join(location, filename))
        if max_size_reached == True:
            break
        for line in fh:
            line = line.strip()
            line = line.split('\t')
            if len(line) != 12:
                continue
            if line[10] == '1':
                continue
            timestamp = datetime.strptime(line[6], '%Y-%m-%dT%H:%M:%SZ')
            if timestamp > cutoff_date:
                continue
            username = line[3].lower()
            if username.endswith('bot') or username.find('script') > -1:
                #line[10] = '1'
                continue
            id = line[2]
            if id not in ids and id not in ignore_ids:
                res = db.find_one('editor', id)
                if res == None:
                    ignore_ids.add(id)
                    continue
            cnt_obs += 1
            title_id = line[1]
            ids.add(id)
            simple_date = '%s-%s' % (timestamp.year, timestamp.month)
            dates.setdefault(simple_date, 0)
            dates[simple_date] += 1
            title = line.pop(5)
            titles[title_id] = title
            line.append('\n')
            line = '\t'.join(line)
            size += len(line)
            if size > max_size:
                max_size_reached = True
            dataset.write(line.decode('utf-8'))

dataset.close()

print 'Constructing title dataset...'
fh = codecs.open('titles.tsv', 'w', 'utf-8')
for id, title in titles.iteritems():
    fh.write('%s\t%s\n' % (id, title.decode('utf-8')))
fh.close()


print 'Constructing solution dataset...'
x = 0
fh = codecs.open('solutions.tsv', 'w', 'utf-8')
for id in ids:
    if id not in ignore_ids:
        obs = db.find_one('editor', str(id), 'cum_edit_count_main_ns')
        if obs != None:
            x += 1
            n = obs['cum_edit_count_main_ns']
            fh.write('%s,%s\n' % (id.decode('utf-8'), n))
            edits.setdefault(n, 0)
            edits[n] += 1
        else:
            print id
fh.close()

print 'Storing date histogram'
fh = open('histogram_dates.bin', 'wb')
cPickle.dump(dates, fh)
fh.close()


fh = open('histogram_dates.tsv', 'w')
for date, n in dates.iteritems():
    fh.write('%s\t%s\n' % (date, n))
fh.close()


print 'Storing edit histogram'
fh = open('histogram_edits.bin', 'wb')
cPickle.dump(edits, fh)
fh.close()

fh = open('histogram_edits.tsv', 'w')
for edit, n in edits.iteritems():
    fh.write('%s\t%s\n' % (edit, n))
fh.close()


t1 = datetime.now()
print 'Descriptives:'
print 'Number of editors: %s' % x
print 'Number of edits: %s' % cnt_obs
print 'It took %s to construct the Kaggle training set' % (t1 - t0)
