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
__date__ = '2011-01-04'
__version__ = '0.1'

from Queue import Empty
import multiprocessing
import progressbar

from utils import file_utils
from utils import text_utils
from database import cache
from classes import storage
from classes import consumers


class Storer(consumers.BaseConsumer):
    '''
    This function is called by multiple consumers who each take a sorted 
    file and create a cache object. If the number of edits made by an 
    editor is above the treshold then the cache object stores the data in 
    Mongo, else the data is discarded.
    The treshold is currently more than 9 edits and is not yet configurable. 
    '''
    def run(self):
        db = storage.init_database(self.rts.storage, self.rts.dbname,
                                   self.rts.editors_raw)
        editor_cache = cache.EditorCache(db)
        prev_editor = -1
        while True:
            try:
                filename = self.tasks.get(block=False)
                self.tasks.task_done()
                if filename == None:
                    self.result.put(None)
                    break

                fh = file_utils.create_txt_filehandle(self.rts.sorted, filename,
                                                      'r', 'utf-8')
                for line in file_utils.read_raw_data(fh):
                    if len(line) == 1 or len(line) == 4:
                        continue
                    editor = line[0]
                    #print 'Parsing %s' % editor
                    if prev_editor != editor and prev_editor != -1:
                        editor_cache.add(prev_editor, 'NEXT')

                    data = prepare_data(line)
                    #print editor, data['username']
                    editor_cache.add(editor, data)
                    prev_editor = editor
                fh.close()
                self.result.put(True)
            except Empty:
                pass


def prepare_data(line):
    '''
    Prepare a single line to store in the database, this entails converting
    to proper variable and taking care of the encoding.
    '''
    article_id = int(line[1])
    username = line[3].encode('utf-8')
    ns = int(line[4])
    date = text_utils.convert_timestamp_to_datetime_utc(line[6])
    md5 = line[7]
    revert = int(line[8])
    bot = int(line[9])
    cur_size = int(line[10])
    delta = int(line[11])

    data = {'date': date,
            'article': article_id,
            'username': username,
            'ns': ns,
            'hash': md5,
            'revert':revert,
            'cur_size':cur_size,
            'delta':delta,
            'bot':bot
    }
    return data


def store_articles(tasks, rts):
    db = storage.init_database(rts.storage, rts.dbname, rts.articles_raw)
    while True:
        try:
            filename = tasks.get(block=False)
            if filename == None:
                self.result.put(None)
                break
            print 'Processing %s...' % filename
            fh = file_utils.create_txt_filehandle(rts.txt, filename, 'r', 'utf-8')
            for line in fh:
                line = line.strip()
                line = line.split('\t')
                data = {}
                x, y = 0, 1
                while y < len(line):
                    key, value = line[x], line[y]
                    if key == 'ns' or key == 'id':
                        data[key] = int(value)
                    else:
                        data[key] = value
                    x += 2
                    y += 2
                db.insert(data)
            fh.close()
        except Empty:
            pass
    print 'Done storing articles...'


def launcher_articles(rts):
    '''
    This function reads articles.csv and stores it in a separate collection.
    Besides containing the title of an article, it also includes:
    * namespace
    * category (if any)
    * article id
    '''
    db = storage.init_database(rts.storage, rts.dbname, rts.articles_raw)
    db.drop_collection()


    files = file_utils.retrieve_file_list(rts.txt, extension='csv',
                                          mask='articles')
    tasks = multiprocessing.JoinableQueue()

    print 'Storing articles...'

    for filename in files:
        tasks.put(filename)

    for x in xrange(rts.number_of_processes):
        tasks.put(None)

    storers = [multiprocessing.Process(target=store_articles, args=[tasks, rts])
               for x in xrange(rts.number_of_processes)]

    for storer in storers:
        storer.start()

    tasks.join()

    print '\nCreating indexes...'
    db.add_index('id')
    db.add_index('title')
    db.add_index('ns')
    db.add_index('category')


def launcher(rts):
    '''
    This is the main entry point and creates a number of workers and launches
    them. 
    '''
    print 'Input directory is: %s ' % rts.sorted
    db = storage.init_database(rts.storage, rts.dbname, rts.editors_raw)
    db.drop_collection()

    files = file_utils.retrieve_file_list(rts.sorted, 'csv')
    pbar = progressbar.ProgressBar(maxval=len(files)).start()

    tasks = multiprocessing.JoinableQueue()
    result = multiprocessing.JoinableQueue()

    storers = [Storer(rts, tasks, result) for
                 x in xrange(rts.number_of_processes)]

    for filename in files:
        tasks.put(filename)

    for x in xrange(rts.number_of_processes):
        tasks.put(None)

    for storer in storers:
        storer.start()

    ppills = rts.number_of_processes
    while ppills > 0:
        try:
            res = result.get(block=False)
            if res == True:
                pbar.update(pbar.currval + 1)
            else:
                ppills -= 1
        except Empty:
            pass

    tasks.join()
    print '\nCreating indexes...'
    db.add_index('editor')
    db.add_index('username')


if __name__ == '__main__':
    pass
