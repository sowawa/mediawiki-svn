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
__date__ = '2011-01-21'
__version__ = '0.1'

import datetime
import time
import sys
import re

if '..' not in sys.path:
    sys.path.append('..')

from classes import settings
settings = settings.Settings()


def convert_timestamp_to_date(timestamp):
    return datetime.datetime.strptime(timestamp[:10], settings.date_format)


def convert_timestamp_to_datetime_naive(timestamp, timestamp_format):
    return datetime.datetime.strptime(timestamp, timestamp_format)


def convert_timestamp_to_datetime_utc(timestamp):
    tz = datetime.tzinfo('utc')
    d = convert_timestamp_to_datetime_naive(timestamp, settings.timestamp_format)
    #return d.replace(tzinfo=tz) #enabling this line crashes pymongo
    return d


def invert_dict(dictionary):
    '''
    @dictionary is a simple dictionary containing simple values, ie. no lists,
    or other dictionaries
    output: dictionary where key and value are swapped. 
    '''
    return dict([[v, k] for k, v in dictionary.items()])


def validate_hostname(hostname):
    regex_hostname = re.compile('^(?=.{1,255}$)[0-9A-Za-z](?:(?:[0-9A-Za-z]|\b-){0,61}[0-9A-Za-z])?(?:\.[0-9A-Za-z](?:(?:[0-9A-Za-z]|\b-){0,61}[0-9A-Za-z])?)*\.?$')
    res = re.match(regex_hostname, hostname)
    if res == None:
        return False
    else:
        return True

def get_max_width(table, index):
    '''
    Get the maximum width of the given column index
    Gracefully borrowed from: http://ginstrom.com/scribbles/2007/09/04/pretty-printing-a-table-in-python/
    '''
    return max([len(row[index]) for row in table])




def pprint_table(table):
    '''
    Prints out a table of data, padded for alignment
    @param out: Output stream (file-like object)
    @param table: The table to print. A list of lists.
    Each row must have the same number of columns.
    Gracefully borrowed from: http://ginstrom.com/scribbles/2007/09/04/pretty-printing-a-table-in-python/
    '''

    col_paddings = []
    text = ''
    for i in range(len(table[0])):
        col_paddings.append(get_max_width(table, i))

    for row in table:
        # left col
        #print >> out, row[0].ljust(col_paddings[0] + 1),
        # rest of the cols
        for i in xrange(0, len(row)):
            col = row[i].rjust(col_paddings[i] + 2)
            text = text + col
            if i == len(row) - 1:
                text = text + '\n'
            #print >> out, col,
        #print >> out
    return text
