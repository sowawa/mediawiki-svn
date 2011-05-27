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
__date__ = '2010-11-05'
__version__ = '0.1'

import os

class FileNotFoundException(Exception):
    '''Exception when a file is not found'''
    def __init__(self, filename, path):
        super(FileNotFoundException, self).__init__()
        self.filename = filename
        self.path = path

    def __str__(self):
        return  '''The file %s was not found. Please make sure that the file 
            exists and the path is correct.''' \
            % (os.path.join(self.path, self.filename))

class PlatformNotSupportedError(Exception):
    '''Exception when a platform is not supported'''
    def __init__(self, platform):
        super(PlatformNotSupportedError, self).__init__()
        self.platform = platform

    def __str__(self):
        return 'Platform %s is not supported' % self.platform

class CompressionNotSupportedError(Exception):
    '''Exception to notify user that decompression is not supported.'''
    def __init__(self, extension):
        super(CompressionNotSupportedError, self).__init__()
        self.extension = extension

    def __str__(self):
        return 'You have not installed a program to extract %s archives.' \
            % self.extension

class CompressedFileNotSupported(Exception):
    '''Exception to notify that compressed archive is not supported.'''
    def __init__(self, extension):
        super(CompressedFileNotSupported, self).__init__()
        self.extension = extension

    def __str__(self):
        return 'Wikilytics does not support %s files to extract directly from.' \
            % self.extension


class OutDatedPythonVersionError(Exception):
    '''Exception to notify that the user is using an outdated Python version.'''
    def __init__(self, version):
        super(OutDatedPythonVersionError, self).__init__()
        self.version = version

    def __str__(self):
        return 'Please upgrade to Python 2.6 or higher (but not Python 3.x).'

class UnknownJSONEncoderError(Exception):
    '''Exception to notify that the JSON encoder requested is unknown.'''
    def __init__(self, func):
        super(UnknownJSONEncoderError, self).__init__()
        self.func = func

    def __str__(self):
        return 'There is no JSON encoder called %s, please make sure that you \
            entered the right name' % self.func

class NoDatabaseProviderInstalled(Exception):
    '''Exception to notify that the database requested is not supported.'''
    def __init__(self):
        super(NoDatabaseProviderInstalled, self).__init__()


    def __str__(self):
        return '''You need either to install Mongo or Cassandra to use 
        Wikiltyics.'''

class OutputNotSupported(Exception):
    def __init__(self, format):
        super(OutputNotSupported, self).__init_()
        self.format = format

    def __str__(self):
        return '''Output format %s is not supported.''' % format

class UnknownPluginError(Exception):
    '''Exception to notify the user that the requested plugin does not exist.'''
    def __init__(self, plugin, plugins):
        super(UnknownPluginError, self).__init__()
        self.plugin = plugin
        self.plugins = plugins

    def __str__(self):
        return 'Plugin %s is an unknown plugin. Please choose one of the \
            the following plugins: %s' % (self.plugin, self.plugins)


class NotYetImplementedError(Exception):
    '''Exception to notify that the requested functionality has not yet been
    implemented.'''
    def __init__(self, func):
        super(NotYetImplementedError, self).__init__()
        self.func = func

    def __str__(self):
        return '''%s has not yet been implemented, update your installation from
        subversion or contact Diederik van Liere.''' % self.func.func_name


class GenericMessage(Exception):
    '''Exception used for generic message, you need to supply the message 
    yourself.'''
    def __init__(self, caller):
        super(GenericMessage, self).__init__()
        self.caller = caller

    def __str__(self):
        if self.caller == 'only_variables':
            return 'var should be an instance of Variable.'
        elif self.caller == 'corrupted_install':
            return 'I could not determine the location of manage.py, \
                please reinstall Wikilytics.'
        elif self.caller == 'corrupted_config':
            return 'Please delete wiki.cfg and run python manage.py config'
        elif self.caller == 'not_configured':
            return 'Please run first python manage.py config'

