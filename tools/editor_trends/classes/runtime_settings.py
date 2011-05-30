#!/usr/bin/python
# coding=utf-8
'''
Copyright (C) 2010 by Diederik van Liere (dvanliere@gmail.com)
This program is free software; you can redistribute it and/or
modify it under the terms of the GNU General Public License version 2
as published by the Free Software Foundation.
This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
See the GNU General Public License for more details, at
http,//www.fsf.org/licenses/gpl.html
'''

__author__ = '''\n'''.join(['Diederik van Liere (dvanliere@gmail.com)', ])
__email__ = 'dvanliere at gmail dot com'
__date__ = '2010-10-21'
__version__ = '0.1'

'''
This file provides mapper between language name and locale language name and
Wikipedia acronym.
'''

import os
import sys
import datetime
import time

if '..' not in sys.path:
    sys.path.append('../')

from settings import Settings
from analyses import inventory
from classes import exceptions
import languages
import projects


class RunTimeSettings(Settings):
    '''
    This class keeps track of the commands issued by the user and is used to 
    feed the different etl functions. Difference with configuration class is
    that the configuration class are read-only settings that are always the 
    same for a user while these settings can change depending on the kind of
    analysis requested. 
    '''
    def __init__(self, project, language, args=None):
        Settings.__init__(self)
        self.project = project
        self.language = language
        self.dbname = 'wikilytics'

        #if args:
        self.args = args
        self.id = '%s%s_%s' % (self.language.code, self.project.name, 'current_month')
        #print self.settings.input_location
        #print self.get_value('location')
        self.project = self.update_project_settings()
        self.language = self.update_language_settings()

        self.input_location = self.set_input_location()
        self.output_location = self.set_output_location()

        self.plugins = self.set_plugin()
        self.keywords = self.split_keywords()
        self.namespaces = self.get_namespaces()

        #self.kaggle = self.get_value('kaggle')
        self.function = self.get_value('func')
        self.ignore = self.get_value('except')
        self.force = self.get_value('force')
        self.analyzer_collection = self.get_value('collection')

        self.dataset = os.path.join(self.dataset_location, self.project.name)
        self.txt = os.path.join(self.output_location, 'txt')
        self.sorted = os.path.join(self.output_location, 'sorted')
        self.diffs = os.path.join(self.output_location, 'diffs')

        self.directories = [self.output_location,
                            self.txt,
                            self.sorted,
                            self.dataset,
                            self.diffs]
        self.verify_environment(self.directories)

        #Wikidump file related variables
        self.dump_filename = self.generate_wikidump_filename()
        self.dump_relative_path = self.set_dump_path()
        self.dump_absolute_path = self.set_dump_path(absolute=True)

        #Collection names
        self.editors_raw = '%s%s_editors_raw' % (self.language.code, self.project.name)
        self.editors_dataset = '%s%s_editors_dataset' % (self.language.code, self.project.name)
        self.articles_raw = '%s%s_articles_raw' % (self.language.code, self.project.name)
        self.diffs_dataset = '%s%s_diffs_dataset' % (self.language.code, self.project.name)



    def __str__(self):
        return 'Runtime Settings for project %s %s' % (self.language.name,
                                                      self.project.full_name)

    def __iter__(self):
        for item in self.__dict__:
            yield item

    def dict(self):
        '''
        Return a dictionary with all properties and their values
        '''
        props = {}
        for prop in self:
            props[prop] = getattr(self, prop)
        return props

    def split_keywords(self):
        '''
        Parse keywords from command line (only supplied for -dataset)
        '''
        keywords = self.get_value('keywords')
        d = {}
        if keywords != None:
            keywords = keywords.split(',')
            if [True for kw in keywords if kw.find('=') > -1] != []:
                for kw in keywords:
                    key, value = kw.split('=')
                    if value.find(';') > -1:
                        value = value.split(';')
                    else:
                        try:
                            value = int(value)
                        except ValueError:
                            pass
                    d[key] = value
        return d

    def set_plugin(self):
        '''
        Determine whether the plugin exists.
        '''
        plugin = self.get_value('charts')
        requested_plugins = []
        if plugin != None and isinstance(plugin, type('module')) == False:
            plugins = plugin.split(',')
            available_plugins = inventory.available_analyses()
            for plugin in plugins:
                if plugin not in available_plugins:
                    raise exceptions.UnknownPluginError(plugin, available_plugins)
                else:
                    requested_plugins.append(plugin)
        elif isinstance(plugin, type('module')) != None:
            requested_plugins.append(plugin)
        return requested_plugins

    def set_input_location(self):
        '''
        Determine the location where the XML dump files are stored. 
        '''
        files = os.listdir(self.input_location)
        extensions = ['.gz', '.7z', '.bz2']
        project = '%s%s' % (self.language.code, self.project.name)
        for filename in files:
            basename, ext = os.path.splitext(filename)
            if ext in extensions and filename.startswith(project):
                #ABS path case: check if files are stored here
                return self.input_location
        return os.path.join(self.base_location, self.language.code,
                            self.project.name)

    def set_output_location(self):
        '''
        Construct the full project location
        '''
        return os.path.join(self.base_location, self.language.code,
                            self.project.name)

    def show_settings(self):
        '''
        Prints some very high level configuration settings.
        '''
        about = {}
        about['Project'] = '%s' % self.project.full_name.title()
        about['Language'] = '%s / %s / %s' % (self.language.name,
                                              self.language.locale,
                                              self.language.code)
        about['Input directory'] = '%s' % self.input_location
        about['Output directory'] = '%s and subdirectories' % self.output_location

        max_length_key = max([len(key) for key in about.keys()])

        print ''
        print '''Wikilytics is (c) 2010-2011 by the Wikimedia Foundation.
        Written by Diederik van Liere (dvanliere@gmail.com).
        This software comes with ABSOLUTELY NO WARRANTY. This is free 
        software, and you are welcome to distribute it under certain conditions.
        See the README.1ST file for more information.'''
        print '\nPlatform: %s:%s\n' % (self.platform, self.architecture)

        print 'Final settings after parsing command line arguments:'
        for ab in about:
            print '%s: %s' % (ab.rjust(max_length_key), about[ab].encode('utf-8'))


    def get_value(self, key):
        '''
        Returns key from argument if present else None
        '''
        return getattr(self.args, key, None)

    def set_dump_path(self, absolute=False):
        if absolute:
            return '%s/%s%s/latest/' % (self.wp_dump_location,
                                        self.language.code,
                                        self.project.name)
        else:
            return '/%s%s/latest/' % (self.language.code, self.project.name)

    def generate_wikidump_filename(self):
        '''
        Generate the main name of the wikidump file to be downloaded.
        '''
        return '%s%s-latest-%s' % (self.language.code, self.project.name,
                                   self.get_value('file'))

    def update_language_settings(self):
        '''
        Determine the language to be used, default is the system language
        '''
        lang = self.get_value('language')
        lnc = languages.LanguageContainer()
        default = lnc.languages[lnc.default]
        if lang != default.name:
            lang = lnc.get_language(lang, code=False)
            return lang
        else:
            return default

    def update_project_settings(self):
        '''
        Determine the project to be analyzed, default is Wikipedia
        '''
        default = self.project
        proj = self.get_value('project')
        if proj != default:
            pc = projects.ProjectContainer()
            proj = pc.get_project(proj)
            return proj
        else:
            return default

    def get_projectname(self):
        '''
        Determine the full project name based on the project acronym 
        and language.
        '''
        #language_code = self.get_language()
        print self.language.code, self.project.name
        if self.language.code == None:
            print 'Entered language: %s is not a valid Wikimedia language' \
            % self.get_value('language')
            sys.exit(-1)

        if self.project.full_name == None:
            print 'Entered project: %s is not valid Wikimedia Foundation project.' \
            % self.get_value('project')
            sys.exit(-1)
        else:
            return '%s%s' % (self.language.code, self.project.name)

    def secs_since_epoch(self):
        dt = datetime.datetime.now()
        return time.mktime(dt.timetuple())

    def get_namespaces(self):
        '''
        Get the list of namespaces that should be included for analysis. Default
        is namespace 0 (the main namespace)
        '''
        namespaces = self.get_value('namespace')
        if namespaces != None:
            return namespaces.split(',')
        else:
            return ['0']  #Assume that the mainspace is of interest


def init_environment(project, language_code):
    '''
    Initialize an instance of RuntimeSettings. 
    '''
    pjc = projects.ProjectContainer()
    project = pjc.get_project(project)
    lnc = languages.LanguageContainer()
    language = lnc.get_language(language_code)
    parser = init_args_parser(language_code, project)
    args = parser.parse_args(['django'])
    #args.language = language.name
    #args.project = project.name
    rts = RunTimeSettings(project, language, args)
    return rts
