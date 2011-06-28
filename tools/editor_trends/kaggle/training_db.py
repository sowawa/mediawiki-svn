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

__author__ = '''\n'''.join(['Diederik van Liere (dvanliere@gmail.com)'])
__email__ = 'dvanliere at gmail dot com'
__date__ = '2011-04-12'
__version__ = '0.1'

import os
import sys
import cPickle
import codecs
import random
from itertools import izip
from datetime import datetime
from dateutil.relativedelta import *
sys.path.append('../')

random.seed(1024)
from classes import storage

headers = ['user_id', 'article_id', 'revision_id', 'namespace', 'timestamp',
        'md5', 'reverted', 'reverted_user_id', 'reverted_revision_id', 'delta', 'cur_size']
keys = ['user_id', 'article_id', 'rev_id', 'ns', 'date',
        'hash', 'revert', 'reverted_user', 'reverted_rev_id', 'delta', 'cur_size']

max_size = 2147483648
#max_size = 2000000
cnt_obs = 0         #count of number of edits
revs = {}
titles = {}
predictions = {}

t0 = datetime.now()
location = '/home/diederik/wikimedia/xml/en/wiki/txt/'
files = os.listdir(location)
#files.sort()
#files.reverse()
editors_seen = {}
cutoff_date = datetime(2010, 9, 1) #operator is >
end_date = datetime(2011, 2, 1) #operator is <
cutoff_date_training = datetime(2010, 1, 31) #operator is >
end_date_training = datetime(2010, 9, 1) # operator is <

class IDGenerator:
    def __init__(self):
        self.n = 0
        self.ids = {}

    def get_id(self, n):
        if n not in self.ids:
            self.ids[n] = self.n
            self.n += 1
        return str(self.ids[n])

class RandomIDGenerator:
    def __init__(self):
        self.n = 0
        self.ids = {}
        self.rnd_ids = {}
        self.inverted_ids = None

    def invert_dict(self, dictionary):
        return dict((v, k) for k, v in dictionary.iteritems())

    def get_id(self, n):
        if n not in self.ids:
            self.n += 1
            while len(self.rnd_ids) < self.n :
                rnd_id = self.get_random_id()
                if self.rnd_ids.get(rnd_id, False) == False:
                    self.rnd_ids[rnd_id] = True
                    self.ids[n] = rnd_id
        return self.ids[n]

    def get_random_id(self):
        return random.randrange(0, 1000000)

    def reverse_lookup(self, n):
        self.inverted_ids = self.invert_dict(self.ids)
        return self.inverted_ids[n]


def construct_article_meta(fh_articles, files):
    print 'Constructing title dataset...'
    headers = ['article_id', 'category', 'timestamp', 'namespace', 'redirect', 'title', 'related_page']
    write_headers(fh_articles, headers)
    #fh_articles.write('%s\t%s\t%s\t%s\t%s\t%s\t%s\n' % ('article_id', 'category', 'timestamp', 'namespace', 'redirect', 'title', 'related_page'))
    article_meta = {}
    for filename in files:
        if filename.startswith('articles_meta'):
            fh = codecs.open(os.path.join(location, filename))
            for line in fh:
                line = line.strip()
                line = line.split('\t')
                category = line[1]
                if category != 'List':
                    title = line[2]
                    title = title.split('/')
                    article_meta.setdefault(title[-1], {})
                    article_meta[title[-1]]['category'] = category
                    article_meta[title[-1]]['id'] = line[0]
            fh.close()
    return article_meta


def determine_active(edits, start_date, end_date):
    active = 0
    if start_date == datetime(2009, 9, 1):
        if '2009' not in edits and '2010' not in edits:
            return active
#    elif start_date == datetime(2010, 9, 1):
#        if '2010' not in edits and '2011' not in edits:
#            return active


    namespaces = ['0', '1', '2', '3', '4', '5']
    while start_date < end_date:
            year = str(start_date.year)
            month = str(start_date.month)
            for ns in namespaces:
                active += edits.get(year, {}).get(month, {}).get(ns, 0)
                if active > 0: #we don't need to know how many edits,just if active
                    return active
            start_date = start_date + relativedelta(months= +1)
    return active


def load_binary_file(filename):
    fh = open(filename, 'rb')
    obj = cPickle.load(fh)
    fh.close()
    return obj


def convert_tz_to_mysql_tz(tz):
    return tz.__str__()


def check_reverter(idg, reverter):
    try:
        if reverter != -1:
            reverter = idg.get_id(reverter)
            return reverter
    except ValueError:
        pass
    return -1


def check_user_id(user_id):
    try:
        int(user_id)
    except ValueError:
        return False
    return True


def check_username(username):
    username = username.lower()
    if username.endswith('bot') or username.find('script') > -1:
        return False #exclude more bots and scripts
    return True


def determine_editors(db):
    start_date_pre = datetime(2009, 9, 1)
    end_date_pre = datetime(2010, 9, 1)
    end_date = datetime(2011, 2, 1)
    pre_editors = set()
    post_editors = set()
    cursor = db.find({}, 'first_edit,edit_count,user_id,username')
    x, y, z = 0, 0, 0
    for editor in cursor:
        x += 1
        if 'first_edit' not in editor:
            continue
        if editor['first_edit'] > end_date_pre:
            continue
        if check_username(editor['username']) == False:
            continue
        if check_user_id(editor['user_id']) == False:
            continue

        active_pre = determine_active(editor['edit_count'], start_date_pre, end_date_pre)
        if x % 100000 == 0:
            print 'Retrieved %s pre_editors / %s post_editors / %s total editors...' % (y, z, x)

        if active_pre == 0:
            continue #exclude editors who are not active in the year before the cutoff date
        else:
            active_post = determine_active(editor['edit_count'], end_date_pre, end_date)
            if active_post == 0:
                pre_editors.add(user_id)
                y += 1
            else:
                post_editors.add(user_id)
                z += 1
    print 'Retrieved %s pre_editors / %s post_editors / %s total editors...' % (y, z, x)
    return pre_editors, post_editors


def write_headers(fh, headers):
    for i, key in enumerate(headers):
        fh.write('%s' % key)
        if (i + 1) != len(headers):
            fh.write('\t')
        else:
            fh.write('\n')

def write_revision(dataset, revision):
    for i, key in enumerate(keys):
        if type(revision[key]) == type(0):
            revision[key] = str(revision[key])
        dataset.write('%s' % revision[key].decode('utf-8'))
        if (i + 1) != len(keys):
            dataset.write('\t')
        else:
            dataset.write('\n')


print 'Constructing training dataset...'
db_dataset = storage.init_database('mongo', 'wikilytics', 'enwiki_editors_dataset')
print 'Loading editors...'
if not os.path.exists('set_a.bin'):
    pre_editors, post_editors = determine_editors(db_dataset)
    fh = open('set_a.bin', 'wb')
    cPickle.dump(pre_editors, fh)
    fh.close()

    fh = open('set_b.bin', 'wb')
    cPickle.dump(post_editors, fh)
    fh.close()
else:
    pre_editors = load_binary_file('set_a.bin')
    post_editors = load_binary_file('set_b.bin')


dataset = codecs.open('training.tsv', 'w', 'utf-8')
write_headers(dataset, headers)
idg = RandomIDGenerator()

namespaces = IDGenerator()
print 'Parsing revisions...'
db_raw = storage.init_database('mongo', 'wikilytics', 'enwiki_editors_raw')
seen_editors = {}
editors = {}
x = 1
for editor in post_editors:
    #print editor
    editors[x] = editor
    x += 2
x = 0
z = len(post_editors)
for y, editor in enumerate(pre_editors):
    #print editor
    editors[x] = editor
    x += 2
    if z == y:
        break

editor_keys = editors.keys()
editor_keys.sort()
for key in editor_keys:
    #print editors
    #for editor in editors:
    editor = editors[key]
    #print editor
    go = editors_seen.get(editor, True)
    if go:
        editors_seen[editor] = False
        user_id = idg.get_id(editor)
        print 'Parsing editor %s (%s) ...' % (editor, user_id)
        revisions = db_raw.find({'user_id': str(editor)})

        predictions.setdefault(user_id, {})
        predictions[user_id].setdefault('solution', 0)
        predictions[user_id].setdefault('training', 0)

        for revision in revisions:
            revision['user_id'] = user_id #recode id to make it harder to look up answers
            if revision['ns'] < 0 or revision['ns'] > 5:
                continue
            #revision['ns'] = namespaces.get_id(revision['ns'])
            timestamp = revision['date']
            revision['date'] = convert_tz_to_mysql_tz(timestamp)



            if timestamp > cutoff_date:
                #print editor, user_id, timestamp, revision['date']
                if timestamp < end_date:
                    predictions[user_id]['solution'] += 1
            elif timestamp > cutoff_date_training:
                if timestamp < end_date_training:
                    predictions[user_id]['training'] += 1

            if timestamp > cutoff_date: #exclude edits after cut off date
                continue
            if revision['revert'] == 1:
                revision['reverted_user'] = check_reverter(idg, revision.get('reverted_user', -1))
            revision.pop('_id')
            revision.pop('username')
            titles[revision['article_id']] = True
            revs[revision['rev_id']] = True
            write_revision(dataset, revision)
            cnt_obs += 1
            if cnt_obs % 10000 == 0:
                print 'Parsed %s revisions...' % cnt_obs
    if dataset.tell() > max_size:
        break
if dataset.tell() > max_size:
    print 'Reached maximum filesize...'
else:
    print 'Parsed all available editors in post set...'
dataset.close()



print 'Constructing solution dataset...'
fh = codecs.open('solutions.csv', 'w', 'utf-8')
editor_keys = predictions.keys()
editor_keys.sort()
fh.write('%s,%s\n' % ('user_id', 'solution'))
for key in editor_keys:
    fh.write('%s,%s\n' % (key, predictions[key]['solution']))
    print key, predictions[key]['solution']
fh.close()


print 'Constructing test dataset...'
fh = codecs.open('test.csv', 'w', 'utf-8')
fh.write('%s,%s\n' % ('user_id', 'test'))
for key, value in predictions.iteritems():
    fh.write('%s,%s\n' % (key, value['training']))
fh.close()

print 'Constructing article file...'
fh_articles = codecs.open('titles.tsv', 'w', 'utf-8')
article_meta = construct_article_meta(fh_articles, files)
categories = IDGenerator()
for filename in files:
    if filename.startswith('articles') and not filename.startswith('articles_meta'):
        fh = codecs.open(os.path.join(location, filename))
        for line in fh:
            line = line.strip()
            line = line.split('\t')
            if len(line) == 6:
                article_id = int(line[0])
                title = titles.pop(article_id, None)
                if title:
                    title = line[-1]
                    meta = article_meta.get(title, None)
                    parent_id = '-1'
                    category = -1
                    redirect = line[4]
                    if redirect == 'False':
                        redirect = '0'
                    else:
                        redirect = '1'
                    line[4] = redirect
                    if meta:
                        parent_id = meta['id']
                        category = meta['category']


                    line[1] = categories.get_id(category)
                    tz = datetime.strptime(line[2], '%Y-%m-%dT%H:%M:%SZ')
                    line[2] = convert_tz_to_mysql_tz(tz)
                    line[-1] = line[-1].decode('utf-8')
                    line.append(parent_id)
                    line.append('\n')
                    fh_articles.write('\t'.join(line))
        fh.close()
fh_articles.close()


print 'Constructing comment dataset...'
fh_comments = codecs.open('comments.tsv', 'w', 'utf-8')
fh_comments.write('%s\t%s\n' % ('revision_id', 'comment'))
cnt = len(revs.keys())
for filename in files:
    if filename.startswith('comments'):
        fh = codecs.open(os.path.join(location, filename))
        for line in fh:
            if cnt == 0:
                break
            line = line.strip()
            line = line.split('\t')
            if len(line) == 2:  #some lines are missing rev id, not sure why.
                try:
                    rev_id = int(line[0])
                    exists = revs.get(rev_id, None)
                    if exists:
                        fh_comments.write('%s\t%s\n' % (rev_id, line[1].decode('utf-8')))
                        cnt -= 1
                except (ValueError, KeyError), error:
                    print error
        fh.close()
fh_comments.close()

print 'Storing random ids...'
fh = open('random_ids.bin', 'wb')
cPickle.dump(idg, fh)
fh.close()

fh = codecs.open('namespaces.tsv', 'w', 'utf-8')
write_headers(fh, ['key', 'namespace'])
namespaces = {'0':'Main',
              '1':'Talk',
              '2':'User',
              '3':'User Talk',
              '4':'Wikipedia',
              '5':'Wikipedia Talk'
              }
for key, value in namespaces.iteritems():
    fh.write('%s\t%s\n' % (key, value))
fh.close()

fh = codecs.open('categories.tsv', 'w', 'utf-8')
write_headers(fh, ['id', 'name'])
for key, value in categories.ids.iteritems():
    fh.write('%s\t%s\n' % (value, key))
fh.close()

fh = open('descriptives.tsv', 'w')
fh.write('Number of unique editors: %s\n' % idg.n)
fh.write('Number of revisions: %s\n' % cnt_obs)
fh.write('Number of pre-editors: %s\n' % len(pre_editors))
fh.write('Number of post-editors: %s\n' % len(post_editors))
fh.write('Number of editors with zero edits after August 30th. 2010: %s' % (len(pre_editors) - len(post_editors)))
fh.close()


t1 = datetime.now()
print 'Descriptives:'
print 'Number of unique editors: %s' % idg.n
print 'Number of revisions: %s' % cnt_obs
print 'Number of pre-editors: %s' % len(pre_editors)
print 'Number of post-editors: %s' % len(post_editors)
print 'Number of editors with zero edits after August 30th. 2010: %s' % (len(pre_editors) - len(post_editors))
print 'It took %s to construct the Kaggle training set' % (t1 - t0)
