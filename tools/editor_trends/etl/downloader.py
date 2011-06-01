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

import urllib2
import progressbar
import multiprocessing
import sys
import os

from utils import file_utils
from utils import http_utils
from utils import text_utils
from utils import log


def download_wiki_file(task_queue, rts):
    '''
    This is a very simple replacement for wget and curl because Windows does
    not have these tools installed by default
    '''
    success = True
    chunk = 1024 * 4

    while True:
        filename = task_queue.get(block=False)
        task_queue.task_done()
        if filename == None:
            print 'Swallowed a poison pill'
            break
        widgets = log.init_progressbar_widgets(filename)
        extension = os.path.splitext(filename)[1]
        filemode = file_utils.determine_file_mode(extension)
        filesize = http_utils.determine_remote_filesize(rts.wp_dump_location,
                                                        rts.dump_relative_path,
                                                        filename)

        mod_date = http_utils.determine_modified_date(rts.wp_dump_location,
                                                rts.dump_relative_path,
                                                filename)
        mod_date = text_utils.convert_timestamp_to_datetime_naive(mod_date, rts.timestamp_server)
        if file_utils.check_file_exists(rts.input_location, filename):
            mod_loc = file_utils.get_modified_date(rts.input_location, filename)
            if mod_loc == mod_date and (rts.force == False or rts.force == None):
                print 'You already have downloaded the most recent %s%s dumpfile.' % (rts.language.code, rts.project.name)
                continue

        if filemode == 'w':
            fh = file_utils.create_txt_filehandle(rts.input_location,
                                                  filename,
                                                  filemode,
                                                  rts.encoding)
        else:
            fh = file_utils.create_binary_filehandle(rts.input_location, filename, 'wb')

        if filesize != -1:
            pbar = progressbar.ProgressBar(widgets=widgets, maxval=filesize).start()
        else:
            pbar = progressbar.ProgressBar(widgets=widgets).start()
        try:
            path = '%s%s' % (rts.dump_absolute_path, filename)
            req = urllib2.Request(path)
            response = urllib2.urlopen(req)
            while True:
                data = response.read(chunk)
                if not data:
                    print 'Finished downloading %s.' % (path)
                    break
                fh.write(data)

                filesize -= chunk
                if filesize < 0:
                    chunk = chunk + filesize
                pbar.update(pbar.currval + chunk)

        except urllib2.URLError, error:
            print 'Reason: %s' % error
        except urllib2.HTTPError, error:
            print 'Error: %s' % error
        finally:
            fh.close()
            file_utils.set_modified_data(mod_date, rts.input_location, filename)



def launcher(rts, logger):
    print 'Creating list of files to be downloaded...'
    tasks = http_utils.create_list_dumpfiles(rts.wp_dump_location,
                                  rts.dump_relative_path,
                                  rts.dump_filename)
    #print tasks.qsize()
    #if tasks.qsize() < rts.settings.number_of_processes:
    #    rts..number_of_processes = tasks.qsize()
    if tasks.qsize() > 2:
        consumers = [multiprocessing.Process(target=download_wiki_file,
                    args=(tasks, rts))
                    for i in xrange(rts.number_of_processes)]
    else: consumers = [multiprocessing.Process(target=download_wiki_file,
                    args=(tasks, rts))
                    for i in xrange(1)]
    print 'Starting consumers to download files...'
    for w in consumers:
        w.start()

    tasks.join()

