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
__date__ = '2010-11-09'
__version__ = '0.1'

import multiprocessing

'''
This needs a cleanup
'''
class BaseConsumer(multiprocessing.Process):

    def __init__(self, task_queue, result_queue):
        multiprocessing.Process.__init__(self)
        self.task_queue = task_queue
        self.result_queue = result_queue


#        for kw in kwargs:
#            setattr(self, kw, kwargs[kw])
#
#    def run(self):
#        proc_name = self.name
#        kwargs = {}
#        IGNORE = ['input_queue', 'result_queue', 'target']
#        for kw in self.__dict__:
#            if kw not in IGNORE and not kw.startswith('_'):
#                kwargs[kw] = getattr(self, kw)
#        self.target(self.input_queue, self.result_queue, **kwargs)


class ProcessResultQueue(multiprocessing.Process):

    def __init__(self, target, result_queue, **kwargs):
        multiprocessing.Process.__init__(self)
        self.result_queue = result_queue
        self.target = target
        for kw in kwargs:
            setattr(self, kw, kwargs[kw])


    def run(self):
        proc_name = self.name
        kwargs = {}
        IGNORE = ['result_queue', 'target']
        for kw in self.__dict__:
            if kw not in IGNORE and not kw.startswith('_'):
                kwargs[kw] = getattr(self, kw)
        self.target(self.result_queue, **kwargs)

class TXTFile(object):

    def __init__(self, file, location, output, output_file, target, **kwargs):
        self.file = file
        self.location = location
        self.target = target
        self.output = output
        self.output_file = output_file
        for kw in kwargs:
            setattr(self, kw, kwargs[kw])

    def __str__(self):
        return '%s' % (self.file)

    def __call__(self, bots):
        self.bots = bots
        self.fr = file_utils.create_txt_filehandle(self.location, self.file, 'r', settings.encoding)
        self.fw = file_utils.create_txt_filehandle(self.output, self.output_file, 'w', settings.encoding)
        for line in self.fr:
            line = line.strip()
            if line == '':
                continue
            line = line.split('\t')
            self.bots = self.target(line, self.fw, self.bots, self.keys)
            if self.bots == {}:
                break
        self.fr.close()
        self.fw.close()
        return self.bots


class XMLFileConsumer(BaseConsumer):

    def run(self):
        while True:
            new_xmlfile = self.task_queue.get()
            self.task_queue.task_done()
            if new_xmlfile == None:
                print 'Swallowed a poison pill'
                break
            print 'Queue is %s files long...' % (messages.show(self.task_queue.qsize) - settings.number_of_processes)
            new_xmlfile()


class XMLFile(object):
    def __init__(self, file, location, output, output_file, target, ** kwargs):
        self.file = file
        self.location = location
        self.output = output
        self.target = target
        self.output_file = output_file
        for kw in kwargs:
            setattr(self, kw, kwargs[kw])

    def create_file_handle(self):
        self.mode = 'a'
        if self.output_file == None:
            self.mode = 'w'
            self.output_file = self.file[:-4] + '.txt'

        self.fh = file_utils.create_txt_filehandle(self.output, self.output_file, self.mode, settings.encoding)

    def __str__(self):
        return '%s' % (self.file)

    def __call__(self, bots=None):
        if bots != {} and bots != None:
            self.bots = bots
        if settings.debug:
            messages = {}
            vars = {}

        data = xml.read_input(utils.create_txt_filehandle(self.location,
                                                      self.file, 'r',
                                                      encoding=settings.encoding))
        self.create_file_handle()
        for raw_data in data:
            xml_buffer = cStringIO.StringIO()
            raw_data.insert(0, '<?xml version="1.0" encoding="UTF-8" ?>\n')
            try:
                raw_data = ''.join(raw_data)
                xml_buffer.write(raw_data)
                elem = cElementTree.XML(xml_buffer.getvalue())
                bots = self.target(elem, fh=self.fh, bots=self.bots)
            except SyntaxError, error:
                print error
                '''
                There are few cases with invalid tokens, they are ignored
                '''
                if settings.debug:
                    file_utils.track_errors(xml_buffer, error, self.file, messages)
            except UnicodeEncodeError, error:
                print error
                if settings.debug:
                    file_utils.track_errors(xml_buffer, error, self.file, messages)
            except MemoryError, error:
                print self.file, error
                print raw_data[:12]
                print 'String was supposed to be %s characters long' % sum([len(raw) for raw in raw_data])
        else:
            self.fh.close()

        if settings.debug:
            file_utils.report_error_messages(messages, self.target)

        return bots
