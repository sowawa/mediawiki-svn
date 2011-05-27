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
__date__ = '2011-04-10'
__version__ = '0.1'

import json
import cStringIO
import codecs
import sys
import os
import difflib
from xml.etree.cElementTree import iterparse, dump
from multiprocessing import JoinableQueue, Process, cpu_count
from datetime import datetime


if '..' not in sys.path:
    sys.path.append('../')

from utils import file_utils
from etl import variables
from classes import exceptions


def parse_xml(fh, format, process_id, location):
    '''
    This function initializes the XML parser and calls the appropriate function
    to extract / construct the variables from the XML stream. 
    '''
    include_ns = {3: 'User Talk',
                  5: 'Wikipedia Talk',
                  1: 'Talk',
                  }

    start = 'start'; end = 'end'
    context = iterparse(fh, events=(start, end))
    context = iter(context)

    article = {}
    count_articles = 0
    id = False
    ns = False
    parse = False
    rev1 = None
    rev2 = None
    file_id, fh_output = None, None

    try:
        for event, elem in context:
            if event is end and elem.tag.endswith('siteinfo'):
                '''
                This event happens once for every dump file and is used to
                determine the version of the generator used to generate the XML
                file. 
                '''
                xml_namespace = variables.determine_xml_namespace(elem)
                namespaces = variables.create_namespace_dict(elem, xml_namespace)
                ns = True
                elem.clear()

            elif event is end and elem.tag.endswith('title'):
                '''
                This function determines the title of an article and the
                namespace to which it belongs. Then, if the namespace is one
                which we are interested in set parse to True so that we start
                parsing this article, else it will skip this article. 
                '''
                title = variables.parse_title(elem)
                article['title'] = title
                current_namespace = variables.determine_namespace(title, namespaces, include_ns)
                if current_namespace == 1 or current_namespace == 3 or current_namespace == 5:
                    parse = True
                    article['namespace'] = current_namespace
                    count_articles += 1
                    if count_articles % 10000 == 0:
                        print 'Worker %s parsed %s articles' % (process_id, count_articles)
                elem.clear()

            elif elem.tag.endswith('revision'):
                '''
                This function does the actual analysis of an individual revision,
                calculating size difference between this and previous revision and
                calculating md5 hash to determine whether this edit was reverted.
                '''
                if parse:
                    if event is start:
                        clear = False
                    else:
                        #dump(elem)
                        rev_id = elem.find('%s%s' % (xml_namespace, 'id'))
                        timestamp = elem.find('%s%s' % (xml_namespace, 'timestamp')).text
                        contributor = elem.find('%s%s' % (xml_namespace, 'contributor'))
                        editor = variables.parse_contributor(contributor, None, xml_namespace)
                        if editor:
                            rev_id = variables.extract_revision_id(rev_id)

                            if rev1 == None and rev2 == None:
                                diff = variables.extract_revision_text(elem, xml_namespace)
                                rev1 = elem
                            if rev1 != None and rev2 != None:
                                diff = diff_revision(rev1, rev2, xml_namespace)

                            article[rev_id] = {}
                            article[rev_id].update(editor)
                            article[rev_id]['timestamp'] = timestamp
                            article[rev_id]['diff'] = diff

                        clear = True
                    if clear:
                        rev2 = rev1
                        elem.clear()
                else:
                    elem.clear()

            elif event is end and elem.tag.endswith('id') and id == False:
                '''
                Determine id of article
                '''
                article['article_id'] = elem.text
                id = True
                elem.clear()

            elif event is end and elem.tag.endswith('page'):
                '''
                We have reached end of an article, reset all variables and free
                memory. 
                '''
                elem.clear()
                #write diff of text to file
                if parse:
                    #print article
                    fh_output, file_id = assign_filehandle(fh_output, file_id, location, process_id, format)
                    write_diff(fh_output, article, format)
                #Reset all variables for next article
                article = {}
                if rev1 != None:
                    rev1.clear()
                if rev2 != None:
                    rev2.clear()
                id = False
                parse = False

    except SyntaxError, error:
        print 'Encountered invalid XML tag. Error message: %s' % error
        dump(elem)
        sys.exit(-1)
    except IOError, error:
        print '''Archive file is possibly corrupted. Please delete this archive 
            and retry downloading. Error message: %s''' % error
        sys.exit(-1)
    print 'Finished parsing Wikipedia dump file.'


def assign_filehandle(fh, file_id, location, process_id, format):
    if not fh:
        file_id = 0
        filename = '%s_%s.%s' % (file_id, process_id, format)
        fh = file_utils.create_txt_filehandle(location, filename, 'w', 'utf-8')
    else:
        size = fh.tell()
        max_size = 1024 * 1024 * 64
        if size > max_size:
            fh.close()
            file_id += 1
            filename = '%s_%s.%s' % (file_id, process_id, format)
            fh = file_utils.create_txt_filehandle(location, filename, 'w', 'utf-8')

    return fh, file_id

def write_xml_diff(fh, article):
    pass


def write_json_diff(fh, article):
    json.dump(article, fh)


def write_diff(fh, article, format):
    if format == 'xml':
        write_xml_diff(fh, article)
    elif format == 'json':
        write_json_diff(fh, article)
    else:
        raise exceptions.OutputNotSupported()


def diff_revision(rev1, rev2, xml_namespace):
    buffer = cStringIO.StringIO()
    if rev1.text != None and rev2.text != None:
        diff = difflib.unified_diff(rev1.text, rev2.text, n=0, lineterm='')
        for line in diff:
            if len(line) > 3:
                print line
                buffer.write(line)

        return buffer.getvalue()

def stream_raw_xml(input_queue, process_id, rts, format):
    '''
    This function fetches an XML file from the queue and launches the processor. 
    '''
    t0 = datetime.now()
    file_id = 0

    while True:
        filename = input_queue.get()
        input_queue.task_done()
        if filename == None:
            print '%s files left in the queue' % input_queue.qsize()
            break

        print filename
        fh = file_utils.create_streaming_buffer(filename)
        parse_xml(fh, format, process_id, rts.input_location)
        fh.close()

        t1 = datetime.now()
        print 'Worker %s: Processing of %s took %s' % (process_id, filename, (t1 - t0))
        print 'There are %s files left in the queue' % (input_queue.qsize())
        t0 = t1



def launcher(rts):
    '''
    This function initializes the multiprocessor, and loading the queue with
    the compressed XML files. 
    '''
    input_queue = JoinableQueue()
    format = 'json'
    files = file_utils.retrieve_file_list(rts.input_location)

    if len(files) > cpu_count():
        processors = cpu_count() - 1
    else:
        processors = len(files)

    for filename in files:
        filename = os.path.join(rts.input_location, filename)
        print filename
        input_queue.put(filename)

    for x in xrange(processors):
        print 'Inserting poison pill %s...' % x
        input_queue.put(None)

    extracters = [Process(target=stream_raw_xml, args=[input_queue, process_id,
                                                       rts, format])
                  for process_id in xrange(processors)]
    for extracter in extracters:
        extracter.start()

    input_queue.join()


def launcher_simple():
    location = 'c:\\wikimedia\\nl\\wiki\\'
    output_location = 'c:\\wikimedia\\nl\\wiki\\diffs\\'
    files = file_utils.retrieve_file_list(location)
    process_id = 0
    format = 'json'
    for filename in files:
        fh = file_utils.create_streaming_buffer(os.path.join(location, filename))
        parse_xml(fh, format, process_id, output_location)
        fh.close()


def debug():
    str1 = """
        '''Welcome to Wikilytics !
        '''
        == Background == 
        This package offers a set of tools used to create datasets to analyze Editor 
        Trends. By Editor Trends we refer to the overall pattern of entering and leaving
        a Wikipedia site. The main information source for this package is [[:strategy:Editor Trends Study|Editor Trends Study]]
        
        == High-level Overview Editor Trends Analytics ==
    """.splitlines(1)

    str2 = """
        Welcome to '''Wikilytics''', a free and open source software toolkit for doing analysis of editing trends in Wikipedia and other Wikimedia projects. 
        
        == Background == 
        This package offers a set of tools used to create datasets to analyze editing trends. It was first created expressly for the [[:strategy:Editor Trends Study|Editor Trends Study]], but is well-suited to a variety of research into editing trends. It is thus free to use (as in beer and freedom) if you're interested in expanding on the [[:strategy:Editor Trends Study/Results|results of Editor Trend Study]] or if you'd like to participate in other [[Research/Projects|research projects]].
        
        == High-level Overview Editor Trends Analytics ==
        
        The Python scripts to create the dataset to answer the question '''“Which editors are the ones that are leaving - -are they the new editors or the more tenured ones?”''' consists of three separate phases:
        * Chunk the XML dump file in smaller parts
        ** and discard all non-zero namespace revisions.
    """.splitlines(1)

    diff = difflib.unified_diff(str1, str2, n=0, lineterm='')
    for line in diff:
        if len(line) > 3:
            print line


if __name__ == '__main__':
    launcher_simple()
    #debug()
