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
from itertools import izip_longest
from datetime import datetime
from dateutil.relativedelta import *
sys.path.append('../')
import resource

random.seed(1024)
from classes import storage

headers = ['user_id', 'article_id', 'revision_id', 'namespace', 'timestamp',
        'md5', 'revert', 'reverted_user', 'reverted_rev_id', 'delta', 'cur_size']
keys = ['user_id', 'article_id', 'rev_id', 'ns', 'date',
        'hash', 'revert', 'reverted_user', 'reverted_rev_id', 'delta', 'cur_size']

size = 0 #current size of file
#max_size = 2147483648
max_size = 5000000
editors_seen = {}
cnt_obs = 0         #count of number of edits
revs = {}
titles = {}
predictions = {}

t0 = datetime.now()
location = '/home/diederik/wikimedia/xml/en/wiki/txt/'
txt_files = '/home/diederik/wikimedia/xml/en/wiki/sorted/'
files = os.listdir(location)
max_file_handles = resource.getrlimit(resource.RLIMIT_NOFILE)[0] - 100
#files.sort()
#files.reverse()

cutoff_date = datetime(2010, 8, 31) #operator is >
end_date = datetime(2011, 2, 1) #operator is <
cutoff_date_training = datetime(2010, 1, 31) #operator is >
end_date_training = datetime(2010, 9, 1) # operator is <


class IDGenerator:
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
    namespaces = ['0', '1', '2', '3', '4', '5']
    if start_date == datetime(2009, 9, 1):
        if '2009' not in edits and '2010' not in edits:
            return active
    elif start_date == datetime(2010, 9, 1):
        if '2010' not in edits and '2011' not in edits:
            return active

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
    fh = open('set_b.bin', 'rb')
    obj = cPickle.load(fh)
    fh.close()
    return obj


def convert_tz_to_mysql_tz(tz):
    iso = tz.__str__()
    tz = iso[0:4] + '-' + iso[4:6] + '-' + iso[6:]
    return tz


def check_reverter(idg, reverter):
    try:
        reverter = int(reverter)
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
    #cursor = db.find({'date': {'$gte': start_date_pre, '$lt': end_date_pre}}, 'first_edit,edit_count,user_id,username')
    cursor = db.find({}, 'first_edit,edit_count,user_id,username')
    x, y, z = 0, 0, 0
    for editor in cursor:
        x += 1
        if 'first_edit' not in editor:
            continue
        if editor['first_edit'] >= end_date_pre:
            continue
        if check_username(editor['username']) == False:
            continue
        if check_user_id(editor['editor']) == False:
            continue

        #print editor['edit_count']
        active = determine_active(editor['edit_count'], start_date_pre, end_date_pre)
        if active > 0:
            pre_editors.add(editor['editor'])
            y += 1
        active = determine_active(editor['edit_count'], end_date_pre, end_date)
        if active > 0:
            post_editors.add(editor['editor'])
            z += 1
        if x % 100000 == 0:
            print 'Retrieved %s pre_editors / %s post_editors / %s total editors...' % (y, z, x)

    #set_a = pre_editors.difference(post_editors)
    post_editors = pre_editors.intersection(post_editors)

    return pre_editors, post_editors


def write_headers(fh, headers):
    for i, key in enumerate(headers):
        fh.write('%s' % key)
        if (i + 1) != len(keys):
            fh.write('\t')
        else:
            fh.write('\n')

def write_revision(dataset, revision):
    size = 0
    for i, key in enumerate(keys):
        #print key, revision[key]
#        if key == 'reverted_user' or key == 'reverted_rev_id':
#            revision[key] = revision[key][0]
        if type(revision[key]) == type(0):
            revision[key] = str(revision[key])

        dataset.write('%s' % revision[key].decode('utf-8'))
        size += len(revision[key])
        if (i + 1) != len(keys):
            dataset.write('\t')
        else:
            dataset.write('\n')
    return size


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
idg = IDGenerator()



print 'Parsing revisions...'
db_raw = storage.init_database('mongo', 'wikilytics', 'enwiki_editors_raw')
seen_editors = {}
for editors in izip_longest(post_editors, pre_editors, fillvalue=None):
    for editor in editors:
        go = editors_seen.get(editor, True)
        if go:
        #if editor:
            editors_seen[editor] = False
            print 'Parsing editor %s...' % editor
            #revisions = db_raw.find({'user_id': editor})
            file_id = int(editor) % max_file_handles
            fh = codecs.open(os.path.join(txt_files, '%s.csv' % file_id), 'r', 'utf-8')
            for line in fh:
                line = line.strip()
                line = line.split('\t')
                if line[0] != editor:
                    continue
                revision = {}
                revision['user_id'] = int(line[0])
                revision['article_id'] = int(line[1])
                revision['rev_id'] = int(line[2])
                revision['ns'] = line[4]
                revision['date'] = datetime.strptime(line[6], '%Y-%m-%dT%H:%M:%SZ')
                revision['hash'] = line[7]
                revision['revert'] = line[8]
                revision['reverted_user'] = line[9]
                revision['reverted_rev_id'] = line[10]
                revision['cur_size'] = line[12]
                revision['delta'] = line[13]
                #print line
                #print revision

                #'user_id', 'article_id', 'rev_id', 'ns', 'date',
                #'hash', 'revert', 'reverted_user', 'reverted_rev_id', 'delta', 'cur_size'
                #print 'Editor %s made % edits' % (editor, len(revisions))
                #for revision in revisions:
                user_id = idg.get_id(revision['user_id'])
                revision['user_id'] = user_id #recode id to make it harder to look up answers
                if revision['ns'] < 0:
                    continue
                timestamp = revision['date']
                #revision['date'] = convert_tz_to_mysql_tz(timestamp)

                predictions.setdefault(user_id, {})
                predictions[user_id].setdefault('solution', 0)
                predictions[user_id].setdefault('training', 0)

                if timestamp > cutoff_date and timestamp < end_date:
                    predictions[user_id]['solution'] += 1
                elif timestamp > cutoff_date_training and timestamp < end_date_training:
                    predictions[user_id]['training'] += 1
                if timestamp > cutoff_date: #exclude edits after cut off date
                    continue

                revision['reverted_user'] = check_reverter(idg, revision.get('reverted_user', -1))
                #revision.pop('_id')
                #revision.pop('username')
                revision['date'] = revision['date'].__str__()
                titles[revision['article_id']] = True
                revs[revision['rev_id']] = True
                size += write_revision(dataset, revision)
                cnt_obs += 1
                if cnt_obs % 10000 == 0:
                    print 'Parsed %s revisions...' % cnt_obs
            fh.close()
    if size > max_size:
        break
if size > max_size:
    print 'Reached maximum filesize...'
else:
    print 'Parsed all available editors in post set...'
dataset.close()



print 'Constructing solution dataset...'
fh = codecs.open('solutions.csv', 'w', 'utf-8')
keys = predictions.keys()
keys.sort()
fh.write('%s,%s\n' % ('editor_id', 'solution'))
for key in keys:
    fh.write('%s,%s\n' % (key, predictions[key]['solution']))
fh.close()


print 'Constructing test dataset...'
fh = codecs.open('test.csv', 'w', 'utf-8')
fh.write('%s,%s\n' % ('editor_id', 'test'))
for key, value in predictions.iteritems():
    fh.write('%s,%s\n' % (key, value['training']))
fh.close()


print 'Constructing article file...'
fh_articles = codecs.open('titles.tsv', 'w', 'utf-8')
article_meta = construct_article_meta(fh_articles, files)
for filename in files:
    if filename.startswith('articles') and not filename.startswith('articles_meta'):
        fh = codecs.open(os.path.join(location, filename))
        for line in fh:
            line = line.strip()
            line = line.split('\t')
            if len(line) == 6:
                article_id = int(line[0])
                title = titles.get(article_id, None)
                if title:
                    title = line[-1]
                    meta = article_meta.get(title, None)
                    parent_id = -1
                    category = 'Null'
                    if meta:
                        parent_id = meta['id']
                        category = meta['category']

                    line[1] = category
                    line[2] = convert_tz_to_mysql_tz(line[2])
                    line[-1] = line[-1].decode('utf-8')
                    line.append(str(parent_id))
                    line.append('\n')
                    fh_articles.write('\t'.join(line))
        fh.close()
fh_articles.close()


print 'Constructing comment dataset...'
fh_comments = codecs.open('comments.tsv', 'w', 'utf-8')
fh_comments.write('%s\t%s\n' % ('rev_id', 'text'))
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
