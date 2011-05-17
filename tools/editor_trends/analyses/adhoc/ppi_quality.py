__author__ = '''\n'''.join(['Diederik van Liere (dvanliere@gmail.com)'])
__email__ = 'dvanliere at gmail dot com'
__date__ = '2011-05-17'
__version__ = '0.1'

from datetime import datetime
from math import pow
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
    date = datetime(2010, 6, 30)
    cursor = db.find('reg_date', {'$gte': date})
    return cursor


def create_dataset(editors):
    obs = {}
    print '%s\t%s\t%s\t%s\t%s' % ('username', 'date', 'number of reverts', 'number of characters added', 'registration date')
    for username in editors:
        for date in editors[username]:
            month = str(date.month)
            year = str(date.year)
            data = db.find_one('username', username)
            if data:
                revert_count = data['revert_count'].get(year, {}).get(month, {}).get('0', 0)
                character_count = data['character_count'].get(year, {}).get(month, {}).get('0', {}).get('added', 0)
                reg_date = data.get('reg_date', datetime(2001, 1, 1))
                epoch = time.mktime(reg_date.timetuple())
                cum_edit_count_main_ns = data.get('cum_edit_count_main_ns', 0)
                cum_edit_count_other_ns = data.get('cum_edit_count_other_ns', 0)
                article_count = data['article_count'].get(year, {}).get(month, 0)
                print '%s\t%s\t%s\t%s\t%s' % (username, date, revert_count, character_count, reg_date)
                obs.setdefault(username, {})
                obs[username]['revert_count'] = revert_count
                obs[username]['character_count'] = character_count
                obs[username]['reg_date'] = epoch
                obs[username]['cum_edit_count_main_ns'] = cum_edit_count_main_ns
                obs[username]['cum_edit_count_other_ns'] = cum_edit_count_other_ns
                obs[username]['article_count'] = article_count
    return obs

def euclidean_distance(vars, person1, person2):
  sum_of_squares = sum([pow(person1[item] - person2[item], 2) for item in vars])
  return 1 / (1 + sum_of_squares)


def calculate_distance_matrix(obs_a, obs_b):
    vars = ['character_count', 'reg_date', 'cum_edit_count_main_ns', 'cum_edit_count_other_ns', 'article_count']
    matches = {}
    for person1 in obs_a:
        for person2 in obs_b:
            d = euclidean_distance(vars, person1, person2)
            matches.setdefault(person1, {})
            matches[person1][person2] = d
    return matches

def find_partner(matches):
    pass

def launcher():
    editors_a = create_sample_a()
    obs_a = create_dataset(editors_a)
    editors_b = create_sample_b()
    obs_b = create_dataset(editors_b)
    matches = calculate_distance_matrix(obs_a, obs_b)
    find_partner(matches)


if __name__ == '__main__':
    launcher()
