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

def time_to_new_wikipedian(var, editor, **kwargs):
    '''
    This plugin calculates how long it takes for an editor to become a 
    new wikipedian. A new wikipedian is defined as someone who has made 10 
    edits
    '''
    new_wikipedian = editor['new_wikipedian']
    if new_wikipedian != False:
        first_edit = editor['first_edit']
        dt = new_wikipedian - first_edit
        var.add(new_wikipedian, dt.days)
    return var
