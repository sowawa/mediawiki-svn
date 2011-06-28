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
__date__ = '2011-01-10'
__version__ = '0.1'

import sys
if '../../' not in sys.path:
    sys.path.append('../../')

from classes import settings
settings = settings.Settings()
from classes import storage
from utils import file_utils

try:
    import psyco
    psyco.full()
except ImportError:
    pass

def create_articles_set(edits):
    s = set()
    years = edits.keys()
    for year in years:
        for edit in edits[year]:
            s.add(edit['article'])
    return s


def create_edgelist(project, collection):
    db = storage.init_database('mongo', project, collection)
    ids = db.retrieve_distinct_keys('editor')
    ids.sort()
    fh = file_utils.create_txt_filehandle(settings.dataset_location, '%s_edgelist.csv' % project, 'w', 'utf-8')
    for i in ids:
        author_i = db.find_one({'editor': i})
        if author_i != None:
            article_i = create_articles_set(author_i['edits'])
            for j in ids:
                if i > j:
                    author_j = db.find_one({'editor': j})
                    article_j = create_articles_set(author_j['edits'])
                    common = article_i.intersection(article_j)
                    if len(common) > 0:
                        file_utils.write_list_to_csv([i, j, len(common)], fh, recursive=False, newline=True)
    fh.close()

if __name__ == '__main__':
    create_edgelist('wikilytics', 'enwiki_editors_raw')
