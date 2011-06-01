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
__date__ = '2011-05-17'
__version__ = '0.1'

from datetime import datetime
from math import pow, log, sqrt
import codecs
import time
import sys
import os
sys.path.append('../../')

from classes import settings
from classes import storage

rts = settings.Settings()
db = storage.init_database('mongo', 'wikilytics', 'enwiki_editors_dataset')

def create_sample_a():
    editors = {}
    location = os.path.join(rts.csv_location, 'ppi_editors.csv')
    fh = open(location, 'r')
    for line in fh:
        line = line.strip()
        username, chars, date = line.split('\t')
        chars = int(chars)
        date = datetime.strptime(date, '%Y-%m-%d')
        editors.setdefault(username, [])
        editors[username].append(date)
    fh.close()
    return editors


def create_sample_b():
    editors = []
    start_date = datetime(2010, 9, 1)
    end_date = datetime(2010, 11, 1)
    cursor = db.find('reg_date', {'$gte': start_date, '$lt': end_date})
    for editor in cursor:
        editors.append(editor['username'])
    return editors


def retrieve_variables(obs, username, date):
    data = db.find_one({'username': username})
    year = str(date.year)
    month = str(date.month)
    if data:
        revert_count = data['revert_count'].get(year, {}).get(month, {}).get('0', 0)
        character_count = data['character_count'].get(year, {}).get(month, {}).get('0', {}).get('added', 0)
        reg_date = data.get('reg_date', datetime(2001, 1, 1))
        #epoch = time.mktime(reg_date.timetuple())
        cum_edit_count_main_ns = data.get('cum_edit_count_main_ns', 0)
        cum_edit_count_other_ns = data.get('cum_edit_count_other_ns', 0)
        article_count = data['article_count'].get(year, {}).get(month, {}).get('0', 0)

        if character_count + cum_edit_count_main_ns + cum_edit_count_other_ns + article_count > 0:
            #print '%s\t%s\t%s\t%s\t%s' % (username, date, revert_count, character_count, reg_date)
            obs.setdefault(username, {})
            obs[username]['revert_count'] = float(revert_count)
            obs[username]['character_count'] = float(character_count)
            obs[username]['reg_date'] = reg_date    #epoch / 86400
            obs[username]['cum_edit_count_main_ns'] = float(cum_edit_count_main_ns)
            obs[username]['cum_edit_count_other_ns'] = float(cum_edit_count_other_ns)
            obs[username]['article_count'] = float(article_count)
    return obs


def create_dataset(editors):
    obs = {}
    #print '%s\t%s\t%s\t%s\t%s' % ('username', 'date', 'number of reverts', 'number of characters added', 'registration date')
    dates = [datetime(2010, 11, 30)] #, datetime(2010, 12, 31)]
    for username in editors:
        for date in dates:
            obs = retrieve_variables(obs, username, date)
    return obs


def euclidean_distance(vars, person1, person2):
    #handle the date variable
    #sum_of_squares = sum([pow(person1[item] - person2[item], 2) for item in vars])
    sum_of_squares = 0.0
    for item in vars:
        if item == 'reg_date' or item == 'revert_count':
            pass
#            dt = person1[item] - person2[item]
#            dt = log(dt.days) if dt.days > 0 else 0
#            sum_of_squares += pow(dt, 2)
        else:
            sum_of_squares += pow(person1[item] - person2[item], 2)
    return 1 / (1 + sqrt(sum_of_squares))


def calculate_distance_matrix(vars, obs_a, obs_b):
    print 'Constructing distance matrix...'
    distances = {}
    for person1 in obs_a:
        for person2 in obs_b:
            if person1 != person2:
                d = euclidean_distance(vars, obs_a[person1], obs_b[person2])
                #print obs_a[person1].values(), obs_b[person2].values(), d
                distances.setdefault(person1, {})
                distances[person1][person2] = d
    return distances


def normalize_dataset(vars, obs):
    '''
    This function rescales a dataset by dividing the observation by the standard
    deviation (which results in a Z-score)
    '''
    editors = obs.keys()
    data = []
    for var in vars:
        for editor in editors:
            data.append(obs[editor][var])
        sd = standard_deviation(data)
        for editor in editors:
            try:
                obs[editor][var] = obs[editor][var] / sd
            except ZeroDivisionError:
                obs[editor][var] = 0
    return obs


def standard_deviation(data):
    n = len(data)
    values = sum(data)
    sq_values = values * values
    sd = (1.0 / n) * sq_values - (pow((1.0 / n) * values, 2))
    return sd


def inverse_dictionary(data):
    return dict((v, k) for k, v in data.iteritems())


def find_partner(distances):
    print 'Finding similar partners...'
    matches = []
    ppi_editors = distances.keys()
    for ppi_editor in ppi_editors:
        data = inverse_dictionary(distances[ppi_editor])
        min_d = min(data.keys())
        max_d = max(data.keys())
        match = data[max_d]
        matches.append((ppi_editor, match, max_d))
        #remove match to make sure that every matched pair is unique
        for editor in distances:
            try:
                distances[editor].pop(match)
            except KeyError:
                pass
        print ppi_editor, match, min_d, max_d
    return matches


def write_dataset(vars, matches, obs_a, obs_b):
    print 'Writing dataset to CSV file...'
    fh = codecs.open('ppi_quality.csv', 'w', 'utf-8')
    fh.write('%s\t' % ('editor_a'))
    fh.write('_a\t'.join(vars))
    fh.write('\t%s\t' % ('editor_b'))
    fh.write('_b\t'.join(vars))
    fh.write('\tdelta registration days\tid\teuclid_dist\n')
    for i, match in enumerate(matches):
        line = []
        editor_a = match[0]
        editor_b = match[1]
        dist = match[2]
        line.append(editor_a)
        values_a = [str(obs_a[editor_a][v]) for v in vars]
        values_b = [str(obs_b[editor_b][v]) for v in vars]
        line.extend(values_a)
        line.append(editor_b)
        line.extend(values_b)
        dt = obs_a[editor_a]['reg_date'] - obs_b[editor_b]['reg_date']
        line.append(str(dt.days))
        line.append(str(i))
        line.append(dist)
        line.append('\n')
        print line
        #line = '\t'.join([str(l).decode('utf-8') for l in line])
        line = '\t'.join(line)
        fh.write(line)
    fh.close()


def launcher():
    print 'Retrieving datasets...'
    vars = ['character_count', 'reg_date', 'cum_edit_count_main_ns',
            'cum_edit_count_other_ns', 'article_count', 'revert_count']
    editors_a = create_sample_a()
    obs_a = create_dataset(editors_a)
    #obs_a = normalize_dataset(vars, obs_a)
    editors_b = create_sample_b()
    obs_b = create_dataset(editors_b)
    #obs_b = normalize_dataset(vars, obs_b)
    distances = calculate_distance_matrix(vars, obs_a, obs_b)
    matches = find_partner(distances)
    write_dataset(vars, matches, obs_a, obs_b)


if __name__ == '__main__':
    launcher()
