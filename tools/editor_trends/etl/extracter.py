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

'''
The extracter module takes care of decompressing a Wikipedia XML dumpfile, 
parsing the XML on the fly and extracting & constructing the variables that are
need for subsequent analysis. The extract module is initialized using an 
instance of RunTimeSettings and the most important parameters are:
The name of project
The language of the project
The location where the dump files are stored
'''

from collections import deque
import sys
import os
from datetime import datetime
from xml.etree.cElementTree import iterparse, dump
from multiprocessing import JoinableQueue, Process, cpu_count


if '..' not in sys.path:
    sys.path.append('..')

from etl import variables
from utils import file_utils
from utils.ordered_dict import OrderedDict
from classes import buffer
from analyses.adhoc import bot_detector

def parse_revision(revision, article, xml_namespace, cache, bots, md5hashes, size, reverts, templates):
    '''
    This function has as input a single revision from a Wikipedia dump file, 
    article id it belongs to, the xml_namespace of the Wikipedia dump file, 
    the cache object that collects parsed revisions, a list of md5hashes
    to determine whether an edit was reverted and a size dictionary to determine
    how many characters were added and removed compared to the previous revision. 
    '''
    if revision == None:
    #the entire revision is empty, weird. 
        #dump(revision)
        return md5hashes, size, reverts, templates

    contributor = revision.find('%s%s' % (xml_namespace, 'contributor'))
    contributor = variables.parse_contributor(contributor, bots, xml_namespace)
    if not contributor:
        #editor is anonymous, ignore
        return md5hashes, size, reverts, templates

    revision_id = revision.find('%s%s' % (xml_namespace, 'id'))
    revision_id = variables.extract_revision_id(revision_id)
    if revision_id == None:
        #revision_id is missing, which is weird
        return md5hashes, size, reverts, templates

    article['revision_id'] = revision_id
    text = variables.extract_revision_text(revision, xml_namespace)
    article.update(contributor)

    comment = variables.extract_comment_text(revision, xml_namespace, revision_id)
    if comment != None:
        cache.comments.update(comment)

    timestamp = revision.find('%s%s' % (xml_namespace, 'timestamp')).text
    article['timestamp'] = timestamp

    #if templates == {} and text != None:
    #    templates = variables.detect_speedy_deletion(text, templates)

    hash = variables.create_md5hash(text)
    revert = variables.is_revision_reverted(hash['hash'], md5hashes)
    reverts = variables.store_revert_information(hash, revision_id, contributor, reverts)
    past_revert = variables.determine_past_revert(hash, revert, reverts)
    md5hashes.append(hash['hash'])
    size = variables.calculate_delta_article_size(size, text)

    article.update(hash)
    article.update(size)
    article.update(revert)
    article.update(past_revert)

    cache.add(article)
    return md5hashes, size, reverts, templates


def parse_xml(fh, rts, cache, process_id, file_id):
    '''
    This function initializes the XML parser and calls the appropriate function
    to extract / construct the variables from the XML stream. 
    '''
    bots = bot_detector.retrieve_bots(rts.storage, rts.language.code)
    include_ns = OrderedDict(((3, 'User talk'),
                (5, 'Wikipedia talk'),
                (1, 'Talk'),
                (2, 'User'),
                (4, 'Wikipedia')))

    start = 'start'; end = 'end'
    context = iterparse(fh, events=(start, end))
    context = iter(context)

    article = {}
    size = {}
    templates = {}
    reverts = {}
    rev_count = 0
    md5hashes = deque()
    id = False
    ns = False
    parse = False

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
                title_meta = variables.parse_title_meta_data(title, current_namespace, namespaces)
                title_meta['redirect'] = False
                if current_namespace < 6:
                    parse = True
                    article['namespace'] = current_namespace
                    cache.count_articles += 1
                    if cache.count_articles % 10000 == 0:
                        print 'Worker %s parsed %s articles' % (process_id, cache.count_articles)
                elem.clear()

            elif elem.tag.endswith('redirect'):
                title_meta['redirect'] = True
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
                        md5hashes, size, reverts, templates = parse_revision(elem, article, xml_namespace, cache, bots, md5hashes, size, reverts, templates)
                        if templates != {}:
                            article['templates'] = templates

                        #add the timestamp when the article was created to
                        #the article collection
                        if rev_count == 0:
                            timestamp = elem.find('%s%s' % (xml_namespace, 'timestamp')).text
                            title_meta['date'] = timestamp
                            rev_count += 1
                        if current_namespace < 6:
                            cache.articles[article['article_id']] = title_meta

                        cache.count_revisions += 1
                        clear = True
                    if clear:
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
                #Reset all variables for next article
                article = {}
                size = {}
                reverts = {}
                templates = {}
                title_meta = {}
                md5hashes = deque()
                rev_count = 0
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


def stream_raw_xml(input_queue, process_id, fhd, rts):
    '''
    This function fetches an XML file from the queue and launches the processor. 
    '''
    t0 = datetime.now()
    file_id = 0
    cache = buffer.CSVBuffer(process_id, rts, fhd)

    while True:
        filename = input_queue.get()
        input_queue.task_done()
        file_id += 1
        if filename == None:
            print '%s files left in the queue' % input_queue.qsize()
            break

        print filename
        fh = file_utils.create_streaming_buffer(filename)
        parse_xml(fh, rts, cache, process_id, file_id)
        fh.close()

        t1 = datetime.now()
        print 'Worker %s: Processing of %s took %s' % (process_id, filename, (t1 - t0))
        print 'There are %s files left in the queue' % (input_queue.qsize())
        t0 = t1

    cache.close()
    cache.summary()


def debug():
    bots = bot_detector.retrieve_bots('mongo', 'en')


def launcher(rts):
    '''
    This function initializes the multiprocessor, and loading the queue with
    the compressed XML files. 
    '''
    input_queue = JoinableQueue()

    files = file_utils.retrieve_file_list(rts.input_location)

    if len(files) > cpu_count():
        processors = cpu_count() - 1
    else:
        processors = len(files)

    fhd = buffer.FileHandleDistributor(rts.max_filehandles, processors)

    for filename in files:
        filename = os.path.join(rts.input_location, filename)
        print filename
        input_queue.put(filename)

    for x in xrange(processors):
        print 'Inserting poison pill %s...' % x
        input_queue.put(None)

    extracters = [Process(target=stream_raw_xml, args=[input_queue, process_id,
                                                       fhd, rts])
                  for process_id in xrange(processors)]
    for extracter in extracters:
        extracter.start()

    input_queue.join()



if __name__ == '__main__':
    debug()
