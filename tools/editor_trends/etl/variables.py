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

import hashlib
import re
from xml.etree.cElementTree import dump

RE_DEL_ARTICLE = re.compile('/GA[\d]{1,2}')
RE_SPEEDY_DELETION = re.compile('\{\{db\-[a-z\d]*\}\}') #http://en.wikipedia.org/wiki/Wikipedia:Criteria_for_speedy_deletion

def validate_hostname(address):
    '''
    This is not a foolproof solution at all. The problem is that it's really 
    hard to determine whether a string is a hostname or not **reliably**. This 
    is a very fast rule of thumb. Will lead to false positives, 
    but that's life :)
    '''
    parts = address.split(".")
    if len(parts) > 2:
        return True
    else:
        return False


def validate_ip(address):
    '''
    Determine whether a username is an IP4 address. 
    '''
    parts = address.split(".")
    if len(parts) != 4:
        return False
    parts = parts[:3]
    for item in parts:
        try:
            if not 0 <= int(item) <= 255:
                return False
        except ValueError:
            return False
    return True


def extract_revision_text(revision, xml_namespace):
    '''
    Extract the actual text from a revision. 
    '''
    rev_text = revision.find('%s%s' % (xml_namespace, 'text'))
    if rev_text.text == None:
        rev_text.text = fix_revision_text(revision)
    return rev_text.text


def parse_title(title):
    ''' Extract the text of a title of an article'''
    return title.text


def detect_speedy_deletion(revision_text, templates):
    spds = re.findall(RE_SPEEDY_DELETION, revision_text)
    for spd in spds:
        templates[spd] = 1
    return templates


def parse_title_meta_data(title, ns, namespaces):
    '''
    This function categorizes an article to assist the Wikimedia Taxonomy
    project. See 
    http://meta.wikimedia.org/wiki/Contribution_Taxonomy_Project/Research_Questions
    '''
    title_meta = {}
    namespace = '%s:' % namespaces[ns]
    title = title.replace(namespace, '')

    title_meta['title'] = title
    title_meta['ns'] = ns
    title_meta['category'] = None

    if title.startswith('List of'):
        title_meta['category'] = 'List'
    elif ns == 1:
        if re.search(RE_DEL_ARTICLE, title):
            title_meta['category'] = 'Good Article'
    elif ns == 4 or ns == 5:
        if title.find('Articles for deletion') > -1:
            if title.find('Articles for deletion/Log/') == -1:
                title_meta['category'] = 'Deletion'
        elif title.find('Arbitration') > -1:
            title_meta['category'] = 'Arbitration'
        elif title.find('Good Article') > -1:
            title_meta['category'] = 'Good Article'
        elif title.find('Mediation') > -1:
            if title.find('Mediation Committee') > -1:
                title_meta['category'] = 'Mediation'
            elif title.find('Mediation Cabal') > -1:
                title_meta['category'] = 'Mediation'
        elif title.find('Featured') > -1:
            if title.find('Featured Articles') > -1:
                title_meta['category'] = 'Featured Article'
            elif title.find('Featured picture candidates') > -1:
                title_meta['category'] = 'Featured Pictures'
            elif title.find('Featured sound candidates') > -1:
                title_meta['category'] = 'Featured Sounds'
            elif title.find('Featured list candidates') > -1:
                title_meta['category'] = 'Featured Lists'
            elif title.find('Featured portal candidates') > -1:
                title_meta['category'] = 'Featured Portal'
            elif title.find('Featured topic candidates') > -1:
                title_meta['category'] = 'Featured Topic'


    #print title_meta
    return title_meta


def extract_username(contributor, xml_namespace):
    '''Extract the username of the contributor'''
    contributor = contributor.find('%s%s' % (xml_namespace, 'username'))
    if contributor != None:
        return contributor.text
    else:
        return None


def determine_username_is_bot(contributor, bots, xml_namespace):
    '''
    #contributor is an xml element containing the id of the contributor
    @bots should have a dict with all the bot ids and bot names
    @Return False if username id is not in bot dict id or True if username id
    is a bot id.
    '''
    username = contributor.find('%s%s' % (xml_namespace, 'username'))
    if username == None:
        return 0
    else:
        if username.text in bots:
            return 1
        else:
            return 0


def extract_contributor_id(revision, xml_namespace):
    '''
    @contributor is the xml contributor node containing a number of attributes
    Currently, we are only interested in registered contributors, hence we
    ignore anonymous editors. 
    '''
    if revision.get('deleted'):
        # ASK: Not sure if this is the best way to code deleted contributors.
        return None
    elem = revision.find('%s%s' % (xml_namespace, 'id'))
    if elem != None:
        return {'id':elem.text}
    else:
        elem = revision.find('%s%s' % (xml_namespace, 'ip'))
        if elem == None or elem.text == None:
            return None
        elif validate_ip(elem.text) == False and validate_hostname(elem.text) == False:
            return {'username':elem.text, 'id': elem.text}
        else:
            return None


def fix_revision_text(revision):
    '''
    If revision text is None then replace by empty string so other functions
    still work
    '''
    if revision.text == None:
        return ''


def create_md5hash(text):
    '''
    Calculate md5 hash based on revision text. 
    '''
    hash = {}
    if text != None:
        m = hashlib.md5()
        m.update(text.encode('utf-8'))
        #echo m.digest()
        hash['hash'] = m.hexdigest()
    else:
        hash['hash'] = -1
    return hash


def calculate_delta_article_size(size, text):
    '''
    Determine how many characters were added / removed compared to previous
    version of text. 
    '''
    if text == None:
        text = ''
    if 'prev_size' not in size:
        size['prev_size'] = 0
        size['cur_size'] = len(text)
        size['delta'] = len(text)
    else:
        size['prev_size'] = size['cur_size']
        delta = len(text) - size['prev_size']
        size['cur_size'] = len(text)
        size['delta'] = delta
    return size


def parse_contributor(revision, bots, xml_namespace):
    '''
    Function that takes care of all contributor related variables. 
    '''
    username = extract_username(revision, xml_namespace)
    user_id = extract_contributor_id(revision, xml_namespace)
    editor = {}
    editor['username'] = username

    if bots:
        bot = determine_username_is_bot(revision, bots, xml_namespace)
        editor['bot'] = bot

    if user_id != None:
        editor.update(user_id)
    else:
        editor = False
    return editor


def determine_namespace(title, namespaces, include_ns):
    '''
    You can only determine whether an article belongs to the Main Namespace
    by ruling out that it does not belong to any other namespace
    '''
    if title != None:
        for key in include_ns:
            namespace = namespaces.get(key, None)
            if namespace and title.startswith(namespace):
                return key
        for key, namespace in namespaces.iteritems():
            if namespace and title.startswith(namespace):
                '''article does not belong to any of the include_ns namespaces'''
                return key
        return 0
    else:
        return 999


def store_revert_information(hash, revision_id, contributor, reverts):
    hash = hash['hash']
    if hash not in reverts:
        reverts.setdefault(hash, {})
        reverts[hash]['revision_id'] = revision_id
        reverts[hash]['contributor'] = contributor
    return reverts


def determine_past_revert(hash, revert, reverts):
    past_revert = {}
    if revert['revert'] == 1:
        hash = hash['hash']
        past_revert['reverted_revision_id'] = reverts[hash]['revision_id']
        past_revert['reverted_contributor'] = reverts[hash]['contributor']['id']
    else:
        past_revert['reverted_revision_id'] = -1
        past_revert['reverted_contributor'] = -1
    return past_revert


def is_revision_reverted(hash_cur, hashes):
    '''
    Determine whether an edit was reverted or not based on md5 hashes
    '''
    revert = {}
    if hash_cur in hashes and hash_cur != -1:
        revert['revert'] = 1
    else:
        revert['revert'] = 0
    return revert


def extract_revision_id(revision_id):
    '''
    Determine the id of a revision 
    '''
    if revision_id != None:
        return int(revision_id.text)
    else:
        return None


def extract_comment_text(revision, xml_namespace, revision_id):
    '''
    Extract the comment associated with an edit. 
    '''
    comment_text = revision.find('%s%s' % (xml_namespace, 'comment'))
    comment = {}
    if comment_text != None and comment_text.text != None:
        comment[revision_id] = comment_text.text
        return comment
    else:
        return None


def create_namespace_dict(siteinfo, xml_namespace):
    '''
    This function determines the local names of the different namespaces.
    '''
    namespaces = {}
    print 'Detected xml namespace: %s' % xml_namespace
    print 'Constructing namespace dictionary'
    elements = siteinfo.find('%s%s' % (xml_namespace, 'namespaces'))
    for elem in elements.getchildren():
        key = int(elem.get('key'))
        namespaces[key] = elem.text #extract_text(ns)
        text = elem.text if elem.text != None else ''
        try:
            print key, text.encode('utf-8')
        except UnicodeEncodeError:
            print key
        elem.clear()
    if namespaces == {}:
        print 'Could not determine namespaces. Exiting.'
        sys.exit(-1)
    return namespaces


def determine_xml_namespace(siteinfo):
    '''
    This function determines the xml_namespace version
    '''
    for elem in siteinfo:
        if elem.tag.endswith('sitename'):
            xml_namespace = elem.tag
            pos = xml_namespace.find('sitename')
            xml_namespace = xml_namespace[0:pos]
            elem.clear()
            return xml_namespace
        else:
            elem.clear()
