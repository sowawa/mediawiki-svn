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
__date__ = '2011-01-25'
__version__ = '0.1'

from classes import storage

def sor_newbie_treatment(editor, var, **kwargs):
    rts = kwargs.pop('rts')
    tenth_edit = editor['new_wikipedian']
    title = ':%s' % editor['username']
    collection = '%s%s_diffs_dataset' % (rts.language.code, rts.project.name)
    db = storage.init_database(rts.storage, rts.dbname, collection)

    if tenth_edit != False:
        qualifier = {'ns': 3, 'timestamp': {'$lt':tenth_edit}}
        observations = db.find_one(qualifier)
    else:
        observations = db.find_one('editor', editor)

    if observations != None:
        for obs in observations:
            if obs['ns'] == 3:
                values = obs.values()
                print values



