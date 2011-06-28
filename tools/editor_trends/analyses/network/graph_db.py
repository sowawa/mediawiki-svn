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
__date__ = '2010-11-25'
__version__ = '0.1'

import codecs
from neo4jrestclient import GraphDatabase, NotFoundError

neo4jrestclient.request.CACHE = True

class IDGenerator:
    def __init__(self):
        self.n = 0
        self.ids = {}
        self.inverted_ids = {}

    def invert_dict(self):
        return dict((v, k) for k, v in self.ids.iteritems())

    def get_id(self, n):
        if n not in self.ids:
            self.ids[n] = self.n
            self.n += 1
        return self.ids[n]

    def reverse_lookup(self, n):
        if self.inverted_ids == {}:
            self.inverted_ids = self.invert_dict()
        return self.inverted_ids[n]


def read_edgelist():
    fh = codecs.open('C:\\Users\\diederik.vanliere\\Dropbox\\wsor\\diederik\\wikilytics_edgelist.csv', 'r', 'utf-8')
    for line in fh:
        line = line.strip()
        line = line.split('\t')
        actor_a = line[0]
        actor_b = line[1]
        weight = int(line[2])
        yield (actor_a, actor_b, weight)
    fh.close()

def init_db():
    gdb = GraphDatabase("http://localhost:7474/db/data/")
    return gdb

def get_node(gdb, idg, node):
    node = idg.get_id(node)
    try:
        #n = gdb.nodes.get('id', node)
        n = gdb.nodes[node]
    except NotFoundError:
        n = gdb.nodes.create(id=node)
        n['id'] = node

    return n

def load_data():
    idg = IDGenerator()
    gdb = init_db()
    for (actor_a, actor_b, weight) in read_edgelist():
        n1 = get_node(gdb, idg, actor_a)
        n2 = get_node(gdb, idg, actor_b)
        n1.relationships.create("cognitive_distance", n2, weight=weight)

if __name__ == '__main__':
    load_data()

