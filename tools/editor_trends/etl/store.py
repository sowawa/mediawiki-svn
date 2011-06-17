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
    This function is called by multiple consumers who each read a file and store
    the contents into the database.
    '''
    def run(self):
        db = storage.init_database(self.rts.storage, self.rts.dbname,
                                   self.rts.editors_raw)
        editor_cache = cache.EditorCache(db)
        while True:
            try:
                filename = self.tasks.get(block=False)
                self.tasks.task_done()
                if filename == None:
                    self.result.put(None)
                    break

                fh = file_utils.create_txt_filehandle(self.rts.sorted, filename,
                                                      'r', 'utf-8')
                data = []
                for line in file_utils.read_raw_data(fh):
                    if len(line) == 1: # or len(line) == 4:
                        continue
                    obs = prepare_data(line)
                    if obs != {}:
                        data.append(obs)
                    if len(data) == 10000:
                        db.insert(data, safe=False)
                        data = []

                if data != []:
                    db.insert(data, safe=False)
                fh.close()
                self.result.put(True)
            except Empty:
                pass


def prepare_data(line):
    '''
    Prepare a single line to store in the database, this entails converting
    to proper variable and taking care of the encoding.
    '''
    user_id = line[0]
    article_id = int(line[1])
    rev_id = int(line[2])
    username = line[3].encode('utf-8')
    ns = int(line[4])
    date = text_utils.convert_timestamp_to_datetime_utc(line[6])
    md5 = line[7]
    revert = int(line[8])
    reverted_user = int(line[9])
    reverted_rev_id = int(line[10])
    bot = int(line[11])
    cur_size = int(line[12])
    delta = int(line[13])

    data = {'date': date,
            'user_id': user_id,
            'article_id': article_id,
            'rev_id': rev_id,
            'username': username,
            'ns': ns,
            'hash': md5,
            'revert':revert,
            'cur_size':cur_size,
            'delta':delta,
            'bot':bot,
    }

    if reverted_user > -1:
        data['reverted_user'] = reverted_user,
        data['reverted_rev_id'] = reverted_rev_id

    return data


def store_articles(tasks, rts):
    db = storage.init_database(rts.storage, rts.dbname, rts.articles_raw)
    filename = None
    while True:
        try:
            filename = tasks.get(block=False)
            tasks.task_done()
            if filename == None:
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
    print 'Finished processing %s...' % filename


def launcher_articles(rts):
    '''
    This function reads articles.csv and stores it in a separate collection.
    Besides containing the title of an article, it also includes:
    * namespace
    * category (if any)
    * article id
    * redirect (true / false)
    * timestamp article created
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
    db.add_index('user_id')
    db.add_index('username')
    db.add_index('article_id')
    db.add_index('reverted_by')
    db.add_index('revert')
    db.add_index('bot')
    db.add_index('date')
    db.add_index('ns')
    db.add_index('delta')


if __name__ == '__main__':
    pass
