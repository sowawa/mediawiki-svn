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
__date__ = '2010-11-02'
__version__ = '0.1'

import multiprocessing
from operator import itemgetter
from copy import deepcopy
from Queue import Empty

import progressbar
from classes import storage
from utils import data_converter
from classes import consumers
from classes import queue


class EditorDatabase(object):
    def __init__(self, rts, tasks, result):
        self.db_raw = storage.init_database(rts.storage, rts.dbname, rts.editors_raw)
        self.db_dataset = storage.init_database(rts.storage, rts.dbname, rts.editors_dataset)

class EditorConsumer(consumers.BaseConsumer):
    '''
    A simple class takes care of fetching an editor from the queue and start
    processing its edits. 
    '''
    def __init__(self, rts, tasks, result):
        super(EditorConsumer, self).__init__(rts, tasks, result)
        self.db_raw = storage.init_database(rts.storage, rts.dbname, rts.editors_raw)
        self.db_dataset = storage.init_database(rts.storage, rts.dbname, rts.editors_dataset)
        self.rts = rts

    def run(self):
        while True:
            editor_id = self.tasks.get()
            self.tasks.task_done()
            if editor_id == None:
                break
            editor = Editor(self.rts, editor_id, self.db_raw, self.db_dataset)
            editor()
            self.result.put(True)


class Editor:
    def __init__(self, rts, editor_id, db_raw, db_dataset):
        self.editor_id = editor_id
        self.db_raw = db_raw
        self.db_dataset = db_dataset
        self.edits = []
        self.editor = self.db_raw.find({'user_id': self.editor_id})
        self.cutoff = 9
        self.username = None
        self.construct_edit_history()

    def __str__(self):
        return '%s' % (self.editor_id)

    def construct_edit_history(self):
        for x, edit in enumerate(self.editor):
            edit.pop('_id')
            edit.pop('user_id')
            if x == 0:
                self.username = edit['username']
            edit.pop('username')
            self.edits.append(edit)

    def __call__(self):
        if self.edits == []:
            return

        first_year, final_year = determine_year_range(self.edits)

        last_edit_by_year = determine_last_edit_by_year(self.edits, first_year, final_year)
        articles_edited = determine_articles_workedon(self.edits, first_year, final_year)
        article_count = determine_article_count(articles_edited, first_year, final_year)

        namespaces_edited = determine_namespaces_workedon(self.edits, first_year, final_year)
        character_count = determine_edit_volume(self.edits, first_year, final_year)
        revert_count = determine_number_reverts(self.edits, first_year, final_year)

        edit_count = determine_number_edits(self.edits, first_year, final_year)

        totals = {}
        counts = data_converter.create_datacontainer(first_year, final_year)
        totals = calculate_totals(totals, counts, character_count, 'character_count')
        totals = calculate_totals(totals, counts, revert_count, 'revert_count')
        totals = calculate_totals(totals, counts, article_count, 'article_count')
        totals = calculate_totals(totals, counts, edit_count, 'edit_count')

        cum_edit_count_main_ns, cum_edit_count_other_ns = calculate_cum_edits(self.edits)

        edits = sort_edits(self.edits)
        if len(edits) > self.cutoff:
            new_wikipedian = edits[self.cutoff]['date']
        else:
            new_wikipedian = False

        first_edit = edits[0]['date']
        final_edit = edits[-1]['date']

        data = {'user_id': self.editor_id,
                'username': self.username,
                'new_wikipedian': new_wikipedian,
                'cum_edit_count_main_ns': cum_edit_count_main_ns,
                'cum_edit_count_other_ns': cum_edit_count_other_ns,
                'final_edit': final_edit,
                'first_edit': first_edit,
                'last_edit_by_year': last_edit_by_year,
                'articles_edited': articles_edited,
                'edit_count': edit_count,
                'namespaces_edited': namespaces_edited,
                'article_count': article_count,
                'character_count': character_count,
                'revert_count': revert_count,
                'totals': totals,
                }
        self.db_dataset.insert(data)


def cleanup_datacontainer(dc, variable_type):
    '''
    valid variable_type are either a {}, a [] or 0.
    '''
    years = dc.keys()
    for year in years:
        months = dc[year].keys()
        for month in months:
            if dc[year][month] == variable_type:
                del dc[year][month]
    return dc


def calculate_totals(totals, counts, dc, var):
    '''
    So far, counting a variable for an editor happens per month per year but
    this makes it cumbersome to determine how many edits an editor has made in 
    a single year (you need to iterate over all the months and that can become
    quite expensive when you have 10000s of editors. Hence, this little helper
    function counts the total number of actions on a yearly basis. 
    '''
    cnts = deepcopy(counts)
    totals.setdefault(var, {})
    for year in dc:
        for month in dc[year]:
            for ns in dc[year][month]:
                if isinstance(dc[year][month][ns], dict):
                    cnts[year].setdefault(ns, {})
                    for key in dc[year][month][ns]:
                        cnts[year][ns].setdefault(key, 0)
                        cnts[year][ns][key] += dc[year][month][ns][key]
                else:
                    cnts[year].setdefault(ns, 0)
                    #print year, ns, type(ns), dc[year][month][ns]
                    cnts[year][ns] += dc[year][month][ns]
    totals[var] = cnts
    return totals


def determine_number_edits(edits, first_year, final_year):
    '''
    This function counts the number of edits per namespace per month per year. 
    '''
    dc = data_converter.create_datacontainer(first_year, final_year)
    dc = data_converter.add_months_to_datacontainer(dc, 'dict')
    for edit in edits:
            ns = str(edit['ns'])
            year = str(edit['date'].year)
            month = str(edit['date'].month)
            dc[year][month].setdefault(ns, 0)
            dc[year][month][ns] += 1
    dc = cleanup_datacontainer(dc, {})
    return dc


def calculate_cum_edits(edits):
    cum_edit_count_main_ns = 0
    cum_edit_count_other_ns = 0
    for edit in edits:
        if edit['ns'] == 0:
            cum_edit_count_main_ns += 1
        else:
            cum_edit_count_other_ns += 1

    return cum_edit_count_main_ns, cum_edit_count_other_ns


def determine_articles_workedon(edits, first_year, final_year):
    '''
    This function creates a list of article_ids that an editor has worked on in 
    a given month/year. 
    '''
    dc = data_converter.create_datacontainer(first_year, final_year)
    dc = data_converter.add_months_to_datacontainer(dc, 'dict')
    for edit in edits:
        year = str(edit['date'].year)
        month = str(edit['date'].month)
        ns = str(edit['ns'])
        dc[year][month].setdefault(ns, set())
        dc[year][month][ns].add(edit['article_id'])

    #convert the set to a list as mongo cannot store sets. 
    for year in dc:
        for month in dc[year]:
            for ns in dc[year][month]:
                dc[year][month][ns] = list(dc[year][month][ns])
    dc = cleanup_datacontainer(dc, {})
    return dc


def determine_namespaces_workedon(edits, first_year, final_year):
    '''
    This function creates a list of namespaces that an editor has worked on in 
    a given month/year.
    '''
    dc = data_converter.create_datacontainer(first_year, final_year)
    dc = data_converter.add_months_to_datacontainer(dc, 'set')
    for edit in edits:
        year = str(edit['date'].year)
        month = str(edit['date'].month)
        dc[year][month].add(edit['ns'])
    for year in dc:
        for month in dc[year]:
            dc[year][month] = list(dc[year][month])
    dc = cleanup_datacontainer(dc, [])
    return dc


def determine_number_reverts(edits, first_year, final_year):
    '''
    This function counts the number of times an edit was reverted in a given
    month/year. 
    '''
    dc = data_converter.create_datacontainer(first_year, final_year)
    dc = data_converter.add_months_to_datacontainer(dc, 'dict')
    for edit in edits:
        year = str(edit['date'].year)
        month = str(edit['date'].month)
        ns = str(edit['ns'])
        if edit['revert']:
            dc[year][month].setdefault(ns, 0)
            dc[year][month][ns] += 1
    dc = cleanup_datacontainer(dc, {})
    return dc


def determine_edit_volume(edits, first_year, final_year):
    '''
    This function counts the number of characters added and remove  by year 
    by month by namespace for a particular editor. 
    '''
    dc = data_converter.create_datacontainer(first_year, final_year)
    dc = data_converter.add_months_to_datacontainer(dc, 'dict')
    for edit in edits:
        year = str(edit['date'].year)
        month = str(edit['date'].month)
        ns = str(edit['ns'])
        dc[year][month].setdefault(ns, {})
        if edit['delta'] < 0:
            dc[year][month][ns].setdefault('removed', 0)
            dc[year][month][ns]['removed'] += edit['delta']
        elif edit['delta'] > 0:
            dc[year][month][ns].setdefault('added', 0)
            dc[year][month][ns]['added'] += edit['delta']
    dc = cleanup_datacontainer(dc, {})
    return dc


def determine_year_range(edits):
    '''
    This function determines the first and final year that an editor was active.
    '''
    #[x for b in a for x in b]
    years = [edit['date'].year for edit in edits]
    first_year = int(min(years))
    final_year = int(max(years)) + 1
    return first_year, final_year


def determine_last_edit_by_year(edits, first_year, final_year):
    '''
    This function determines the date of the last edit in a given year for a
    given editor. 
    '''
    dc = data_converter.create_datacontainer(first_year, final_year, 0)
    for edit in edits:
        date = str(edit['date'].year)
        if dc[date] == 0:
            dc[date] = edit['date']
        elif dc[date] < edit['date']:
            dc[date] = edit['date']
    return dc


def determine_article_count(articles_edited, first_year, final_year):
    '''
    This function counts the number of unique articles by year edited by a
    particular editor.
    '''
    dc = data_converter.create_datacontainer(first_year, final_year)
    dc = data_converter.add_months_to_datacontainer(dc, 'dict')
    for year in articles_edited:
        for month in articles_edited[year]:
            for ns in articles_edited[year][month]:
                dc[year][month][ns] = len(articles_edited[year][month][ns])
    dc = cleanup_datacontainer(dc, {})
    return dc


def sort_edits(edits):
    return sorted(edits, key=itemgetter('date'))


def add_indexes(rts):
    db_dataset = storage.init_database(rts.storage, rts.dbname, rts.editors_dataset)
    print '\nCreating indexes...'
    db_dataset.add_index('user_id')
    db_dataset.add_index('new_wikipedian')
    db_dataset.add_index('username')
    db_dataset.add_index('cum_edit_count_main_ns')
    db_dataset.add_index('cum_edit_count_other_ns')
    db_dataset.add_index('first_edit')
    db_dataset.add_index('final_edit')

    print 'Finished creating indexes...'


def setup_database(rts):
    '''
    Initialize the database, including setting indexes and dropping the older
    version of the collection.
    '''
    db_raw = storage.init_database(rts.storage, rts.dbname, rts.editors_raw)
    db_dataset = storage.init_database(rts.storage, rts.dbname, rts.editors_dataset)
    db_dataset.drop_collection()
    editors = db_raw.retrieve_distinct_keys('user_id', force_new=True)
    return editors, db_raw, db_dataset


def transform_editors_multi_launcher(rts):
    editors, db_raw, db_dataset = setup_database(rts)
    n = editors.size()
    result = queue.JoinableRetryQueue()
    pbar = progressbar.ProgressBar(maxval=n).start()
    transformers = [EditorConsumer(rts, editors, result) \
                    for i in xrange(rts.number_of_processes)]


    for x in xrange(rts.number_of_processes):
        editors.put(None)

    for transformer in transformers:
        transformer.start()

    while n > 0:
        try:
            res = result.get(block=False)
            if res == True:
                pbar.update(pbar.currval + 1)
                n -= 1
        except Empty:
            pass

    editors.join()
    add_indexes(rts)



def transform_editors_single_launcher(rts):
    print rts.dbname, rts.editors_raw
    editors, db_raw, db_dataset = setup_database(rts)
    n = editors.size()
    pbar = progressbar.ProgressBar(maxval=n).start()

    for x in xrange(rts.number_of_processes):
        editors.put(None)

    while True:
        editor = editors.get()
        editors.task_done()
        if editor == None:
            break
        editor = Editor(rts, editor, db_raw, db_dataset)
        editor()

        pbar.update(pbar.currval + 1)

    add_indexes(rts)


if __name__ == '__main__':
    rts = None
    transform_editors_single_launcher(rts)
    #transform_editors_multi_launcher(rts)
