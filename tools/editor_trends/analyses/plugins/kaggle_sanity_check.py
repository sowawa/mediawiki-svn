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
__date__ = '2011-01-28'
__version__ = '0.1'


from datetime import datetime
from dateutil.relativedelta import *


def kaggle_sanity_check(var, editor, **kwargs):
    end_date = datetime(2011, 2, 1)
    cutoff = datetime(2010, 9, 1)
    start_date = datetime(2009, 9, 1)
    edits = editor['edit_count']
    active = 0
    count = 0
    while start_date < cutoff:
        year = str(start_date.year)
        month = str(start_date.month)
        #namespaces = edits.get(year, {}).get(month, {}).keys()
        #for ns in namespaces:
        count += edits.get(year, {}).get(month, {}).get('0', 0)
        start_date = start_date + relativedelta(months= +1)

    if count > 0:
        while start_date < end_date:
            year = str(start_date.year)
            month = str(start_date.month)
            active += edits.get(year, {}).get(month, {}).get('0', 0)
            start_date = start_date + relativedelta(months= +1)
        if active > 0 :
            var.add(cutoff, 1)

    return var
