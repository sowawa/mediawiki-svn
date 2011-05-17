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
import os
from datetime import datetime
if '..' not in sys.path:
    sys.path.append('..%s..%s' % (os.sep, os.sep))

from classes import storage
from classes import settings

rts = settings.Settings()
db = storage.init_database('mongo', 'wikilytics', 'enwiki_editors_dataset')
location = os.path.join(rts.csv_location, 'd_20110502.tsv')

fh = open(location, 'r')
for i, line in enumerate(fh):
    if i == 0:
        continue
    line = line.strip()
    line = line.replace("'", '')
    line = line.split('\t')
    id = line[0]
    id = id[:-1]
    if line[1] == 'None':
        continue
    date = datetime.strptime(line[1][:8], '%Y%m%d')
    if i % 1000 == 0:
        print 'Updated user %s' % i
    db.update('editor', id, {'reg_date': date})
fh.close()

print 'Adding index'
db_dataset.add_index('reg_date')
print 'Done.'
