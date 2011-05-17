#!/usr/bin/python
# -*- coding: utf-8 -*-
'''
Copyright (C) 2011 by Ryan Faulkner (rfaulkner@wikimedia.org)
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
__date__ = '2011-04-20'
__version__ = '0.1'

import sys
from datetime import datetime
if '..' not in sys.path:
    sys.path.append('../../')
    
from classes import storage

location  = '/Users/diederik/Desktop/d_20110502.tsv'
fh = open(location, 'r')
db = storage.init_database('mongo', 'wikilytics', 'enwiki_editors_dataset')

for i, line in enumerate(fh):
    if i ==0:
        continue
    line = line.strip()
    line = line.replace("'",'')
    line = line.split('\t')
    id =line[0]
    id = int(id[:-1])
    #date1=eval(line[1])
    if line[1] == 'None':
        continue
    date = datetime.strptime(line[1][:8], '%Y%m%d')
    db.update('id', id, {'reg_date': date})
    

fh.close()