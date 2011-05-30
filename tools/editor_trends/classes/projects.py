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
__date__ = '2011-01-26'
__version__ = '0.1'

import sys
if '..' not in sys.path:
    sys.path.append('..')

import languages

class Project:
    def __init__(self, name, urlname, full_name):
        self.name = name
        self.full_name = full_name
        self.urlname = urlname

    def __repr__(self):
        return '%s' % self.full_name

    def supported_languages(self, output='parser'):
        '''
        Generate a list of tuples with currently supported languages. 
        '''
        assert output == 'django' or output == 'parser', 'Output should either be parser or django.'
        lnc = languages.LanguageContainer()
        choices = []

        for lang  in lnc.init_languages:
            lang = lnc.init_languages[lang]
            if lang in self.valid_languages:
                if output == 'parser':
                    lang = lnc.languages.get(lang, 'Unknown language')
                    choices.append(lang.name.encode('utf-8'))
                elif output == 'django':
                    lang = lnc.languages.get(lang, languages.Language('Unknown language', lang, None))
                    choices.append((lang.code, lang.name))
        #choices = [choice.decode('utf-8') for choice in choices]
        return choices


class ProjectContainer:
    def __init__(self):
        self.projects = {}
        self.wikis = {'wiki': {'url':'wiki', 'full_name': 'Wikipedia'},
                    'commons': {'url':'wikicommons', 'full_name': 'Wikimedia Commons'},
                    'books': {'url':'wikibooks', 'full_name':'Wikibooks'},
                    'news': {'url':'wikinews', 'full_name': 'Wikinews'},
                    'quote': {'url':'wikiquote', 'full_name': 'Wikiquote'},
                    'source': {'url':'wikisource', 'full_name': 'Wikisource'},
                    'versity': {'url':'wikiversity', 'full_name':'Wikiversity'},
                    'tionary': {'url':'wiktionary', 'full_name': 'Wiktionary'},
                    'meta': {'url':'metawiki', 'full_name': 'Metawiki'},
                    'species': {'url':'wikispecies', 'full_name': 'Wikispecies'},
                    'incubator': {'url':'incubatorwiki', 'full_name': 'Wikimedia Incubator'},
                    'foundation': {'url':'foundationwiki', 'full_name': 'Wikimedia Foundation'},
                    'mediawiki': {'url':'mediawikiwiki', 'full_name': 'Medawiki Wiki'},
                    'outreach': {'url':'outreachwiki', 'full_name': 'Outreach Wiki'},
                    'strategic_planning': {'url':'strategywiki', 'full_name': 'Wikimedia Strategic Planning'},
                    'usability_initiative': {'url':'usabilitywiki', 'full_name': 'Wikimedia Usability Initiative'},
                    }
        for project in self.wikis:
            props = self.wikis[project]
            urlname = props['url']
            full_name = props['full_name']
            proj = self.projects.get(project, Project(project, urlname, full_name))
            proj.valid_languages = self.project_supports_language(urlname)
            self.projects[project] = proj

    def get_project(self, name):
        return self.projects.get(name, None)

    def supported_projects(self):
        return ([(d['full_name'], key) for key, d in self.wikis.iteritems()])

    def project_supports_language(self, urlname):
        valid_languages_wiki = ['ace', 'af', 'als', 'an', 'roa-rup', 'ast', 'gn', 'av', 'ay', 'az', 'id', 'ms', 'bm', 'zh-min-nan', 'jv', 'map-bms', 'su', 'bug', 'bi', 'bar', 'bs', 'br', 'ca', 'cbk-zam', 'ch', 'cs', 'ny', 'sn', 'tum', 've', 'co', 'za', 'cy', 'da', 'pdc', 'de', 'nv', 'na', 'lad', 'et', 'ang', 'en', 'es', 'eo', 'ext', 'eu', 'to', 'fo', 'fr', 'frp', 'fy', 'ff', 'fur', 'ga', 'gv', 'sm', 'gd', 'gl', 'got', 'hak', 'haw', 'hsb', 'hr', 'io', 'ilo', 'ig', 'ia', 'ie', 'ik', 'xh', 'zu', 'is', 'it', 'mh', 'kl', 'pam', 'csb', 'kw', 'kg', 'ki', 'rw', 'ky', 'rn', 'sw', 'ht', 'ku', 'la', 'lv', 'lb', 'lt', 'lij', 'li', 'ln', 'jbo', 'lg', 'lmo', 'hu', 'mg', 'mt', 'mi', 'cdo', 'my', 'nah', 'fj', 'nl', 'cr', 'ne', 'nap', 'frr', 'pih', 'no', 'nn', 'nrm', 'oc', 'om', 'pag', 'pi', 'pap', 'pms', 'nds', 'pl', 'pt', 'ty', 'ksh', 'ro', 'rmy', 'rm', 'qu', 'se', 'sg', 'sc', 'sco', 'st', 'tn', 'sq', 'scn', 'simple', 'ceb', 'ss', 'sk', 'sl', 'so', 'sh', 'fi', 'sv', 'tl', 'tt', 'tet', 'vi', 'tpi', 'chy', 'tr', 'tk', 'tw', 'vec', 'vo', 'fiu-vro', 'wa', 'vls', 'war', 'wo', 'ts', 'yo', 'bat-smg', 'el', 'ab', 'ba', 'be', 'bg', 'bxr', 'cu', 'os', 'kk', 'kv', 'mk', 'mn', 'ce', 'ru', 'sr', 'tg', 'udm', 'uk', 'uz', 'xal', 'cv', 'hy', 'ka', 'he', 'yi', 'ar', 'fa', 'ha', 'ps', 'sd', 'ur', 'ug', 'arc', 'dv', 'as', 'bn', 'bpy', 'gu', 'bh', 'hi', 'ks', 'mr', 'kn', 'ne', 'new', 'sa', 'ml', 'or', 'pa', 'ta', 'te', 'bo', 'dz', 'si', 'km', 'lo', 'th', 'am', 'ti', 'iu', 'chr', 'ko', 'ja', 'zh', 'wuu', 'lzh', 'yue']
        valid_languages_wiktionary = ['af', 'als', 'an', 'roa-rup', 'ast', 'gn', 'ay', 'az', 'id', 'ms', 'zh-min-nan', 'jv', 'su', 'mt', 'bs', 'br', 'ca', 'cs', 'co', 'za', 'cy', 'da', 'de', 'na', 'et', 'ang', 'en', 'es', 'eo', 'eu', 'fo', 'fr', 'fy', 'gd', 'ga', 'gv', 'sm', 'gl', 'hr', 'io', 'ia', 'ie', 'ik', 'zu', 'is', 'it', 'kl', 'csb', 'ku', 'kw', 'rw', 'sw', 'la', 'lv', 'lb', 'lt', 'li', 'ln', 'jbo', 'hu', 'mg', 'mi', 'nah', 'fj', 'nl', 'no', 'nn', 'oc', 'om', 'uz', 'nds', 'pl', 'pt', 'ro', 'qu', 'sg', 'st', 'tn', 'scn', 'simple', 'sk', 'sl', 'sq', 'ss', 'so', 'sh', 'fi', 'sv', 'tl', 'tt', 'vi', 'tpi', 'tr', 'tk', 'vo', 'wa', 'wo', 'ts', 'el', 'tsd', 'be', 'bg', 'kk', 'ky', 'mk', 'mn', 'ru', 'sr', 'tg', 'uk', 'hy', 'ka', 'he', 'yi', 'ar', 'fa', 'ha', 'ps', 'sd', 'ug', 'ur', 'dv', 'bn', 'gu', 'hi', 'ks', 'ne', 'sa', 'mr', 'kn', 'ml', 'pa', 'ta', 'te', 'km', 'lo', 'my', 'si', 'th', 'am', 'ti', 'iu', 'chr', 'ko', 'ja', 'zh']
        valid_languages_wikiquote = ['af', 'als', 'id', 'bs', 'ca', 'cs', 'da', 'de', 'en', 'es', 'eo', 'eu', 'fr', 'is', 'it', 'ku', 'la', 'lb', 'lt', 'hu', 'nl', 'no', 'pl', 'pt', 'ro', 'sk', 'fi', 'sv', 'tr', 'el', 'bg', 'ru', 'sr', 'ka', 'he', 'ar', 'fa', 'gu', 'mr', 'ta', 'th', 'ko', 'ja', 'zh']
        valid_languages_wikinews = ['als', 'bs', 'ca', 'cs', 'de', 'en', 'es', 'fa', 'fr', 'it', 'hu', 'nl', 'no', 'nds', 'pl', 'pt', 'ro', 'fi', 'sv', 'tr', 'bg', 'ru', 'sr', 'uk', 'he', 'ar', 'sd', 'ta', 'th', 'ko', 'ja', 'zh']
        valid_languages_wikisource = ['als', 'id', 'bs', 'cs', 'cy', 'da', 'de', 'en', 'es', 'fr', 'gl', 'hr', 'is', 'it', 'la', 'lt', 'li', 'nl', 'pl', 'pt', 'ro', 'sk', 'fi', 'sv', 'vi', 'tr', 'el', 'ru', 'sr', 'he', 'yi', 'ar', 'fa', 'bn', 'ml', 'th', 'ko', 'ja', 'zh']
        valid_languages_wikibooks = ['af', 'als', 'ang', 'als', 'az', 'ms', 'su', 'bs', 'cs', 'co', 'cy', 'da', 'de', 'na', 'et', 'en', 'es', 'eo', 'eu', 'fr', 'fy', 'gl', 'hr', 'ia', 'ie', 'is', 'it', 'ku', 'la', 'lt', 'mg', 'nl', 'no', 'oc', 'uz', 'nds', 'pl', 'pt', 'ro', 'qu', 'sq', 'simple', 'sk', 'sl', 'fi', 'sv', 'vi', 'tl', 'tt', 'tr', 'tk', 'vo', 'el', 'bg', 'be', 'kk', 'ky', 'mk', 'ru', 'sr', 'tg', 'uk', 'cv', 'hy', 'ka', 'he', 'ar', 'fa', 'ps', 'ur', 'bn', 'hi', 'mr', 'sa', 'kn', 'ml', 'pa', 'ta', 'te', 'km', 'ne', 'th', 'ko', 'ja', 'zh']
        valid_languages_wikiversity = ['cs', 'de', 'en', 'es', 'fr', 'it', 'pt', 'fi', 'el', 'ru', 'ja']
        valid_languages_wikicommons = ['en']
        valid_languages_wikispecies = ['en']
        try:
            languages = locals()['valid_languages_%s' % urlname]
            return languages
        except KeyError:
            return []

def debug():
    pc = ProjectContainer()
    pc.supported_projects()

def init(project=None):
    pc = ProjectContainer()
    if project:
        return pc.get_project(project)
    else:
        return pc.get_project('wiki')

if __name__ == '__main__':
    debug()
