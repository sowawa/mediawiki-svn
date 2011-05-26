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

import codecs
import os
from datetime import datetime
import json

location = '/home/diederik/wikimedia/en/wiki/kaggle_prediction'
files = os.listdir(location)
files.reverse()
dataset = codecs.open('training.tsv', 'w', 'utf-8')
t0 = datetime.now()
max_size = 2147483648
titles = {}
ids = set()
size = 0
cnt_obs = 0
max_size_reached = False

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
            username = line[3].lower()
            if username.endswith('bot'):
                #line[10] = '1'
                continue
            cnt_obs += 1
            title_id = line[1]
            ids.add(line[2])
            title = line.pop(5)
            titles[title_id] = title
            line.append('\n')
            line = '\t'.join(line)
            size += len(line)
            if size > max_size:
                max_size_reached = True
            dataset.write(line.decode('utf-8'))

dataset.close()

fh = codecs.open('titles.tsv', 'w', 'utf-8')
for id, title in titles.iteritems():
    fh.write('%s\t%s\n' % (id, title.decode('utf-8')))
fh.close()

fh = codecs.open('ids.json', 'w', 'utf-8')
json.dump(ids, fh)
#for id in ids:
#fh.write('%s\n' % (id.decode('utf-8')))
#fh.write('%s\n' % (json.du)
fh.close()

t1 = datetime.now()
print 'Descriptives:\n'
print 'Number of editors: %s' % len(ids)
print 'Number of edits: %s' % cnt_obs
print 'It took %s to construct the Kaggle training set' % (t1 - t0)
