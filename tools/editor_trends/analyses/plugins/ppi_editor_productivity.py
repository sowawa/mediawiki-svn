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

from datetime import datetime

def ppi_editor_productivity(var, editor, **kwargs):
    #print editor
    if editor == None:
        return var
    new_wikipedian = editor['new_wikipedian']
    if not new_wikipedian:
        return var

    edits = editor['character_count']
    username = editor['username']
    try:
        added = edits['2010']['11']['0']['added']
    except KeyError:
        added = 0
    try:
        removed = edits['2010']['11']['0']['removed']
    except KeyError:
        removed = 0

    key = datetime(2010, 11, 30)
    if added > 0:
        var.add(key, added, {'username': username, 'added': 'added'})
    if removed > 0:
        var.add(key, removed, {'username': username, 'removed': 'removed'})

    try:
        added = edits['2010']['12']['0']['added']
    except KeyError:
        added = 0
    try:
        removed = edits['2010']['12']['0']['removed']
    except KeyError:
        removed = 0

    key = datetime(2010, 12, 31)
    if added > 0:
        var.add(key, added, {'username': username, 'added': 'added'})
    if removed > 0:
        var.add(key, removed, {'username': username, 'removed': 'removed'})

    return var
