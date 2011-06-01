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
__date__ = '2011-03-28'
__version__ = '0.1'

import sys
import gc
from Queue import Empty
from multiprocessing import Process

if '..' not in sys.path:
    sys.path.append('..')

from classes import consumers
from classes import storage
from classes import exceptions
from analyses import inventory

class Replicator:
    '''
    The purpose of this class is to make it easier to run the same plugin
    against different projects or with different parameters. 
    '''
    def __init__(self, plugin, time_unit, cutoff=None, cum_cutoff=None, **kwargs):
        #TODO this is an ugly hack to prevent a circular import problem
        #this needs a better fix. 
        import manage

        project, language, parser = manage.init_args_parser()
        self.project = project
        self.language = language
        self.args = parser.parse_args(['django'])
        self.plugin = plugin
        self.time_unit = time_unit
        languages = kwargs.pop('languages', False)
        if languages:
            self.languages = ['de', 'fr', 'es', 'ja', 'ru']
        else:
            self.languages = ['en']
        if cutoff == None:
            self.cutoff = [1, 10, 50]
        else:
            self.cutoff = cutoff

        if cutoff == None:
            self.cum_cutoff = [10]
        else:
            self.cum_cutoff = cum_cutoff
        self.kwargs = kwargs

    def __call__(self):
        project = 'wiki'
        for lang in self.languages:
            self.rts = runtime_settings.init_environment(project, lang, self.args)

            self.rts.dbname = '%s%s' % (lang, project)
            for cum_cutoff in self.cum_cutoff:
                for cutoff in self.cutoff:
                    generate_chart_data(self.rts, self.plugin,
                                        time_unit=self.time_unit,
                                        cutoff=cutoff,
                                        cum_cutoff=cum_cutoff,
                                        **self.kwargs)


class Analyzer(consumers.BaseConsumer):
    def __init__(self, rts, tasks, result, var, data, plugin_module, plugin_name):
        super(Analyzer, self).__init__(rts, tasks, result)
        self.var = var
        self.data = data
        self.plugin = getattr(plugin_module, plugin_name)

    def run(self):
        '''
        Generic loop function that loops over all the editors of a Wikipedia 
        project and then calls the plugin that does the actual mapping.
        '''
        db = storage.init_database(self.rts.storage, self.rts.dbname, self.rts.collection)
        while True:
            try:
                editor_id = self.tasks.get(block=False)
                self.tasks.task_done()
                if editor_id == None:
                    self.result.put(self.var)
                    break
                editor = db.find_one({'editor': editor_id})
                self.plugin(self.var, editor, rts=self.rts, data=self.data)
                self.result.put(True)
            except Empty:
                pass
