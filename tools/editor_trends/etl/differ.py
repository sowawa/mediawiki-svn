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

import json
import cStringIO
import codecs
import sys
import os
import difflib
from xml.etree.cElementTree import iterparse, dump

if '..' not in sys.path:
    sys.path.append('../')

from utils import file_utils
from etl import variables
from classes import exceptions


def parse_xml(fh, format, process_id, location):
    '''
    This function initializes the XML parser and calls the appropriate function
    to extract / construct the variables from the XML stream. 
    '''
    include_ns = {3: 'User Talk',
                  5: 'Wikipedia Talk',
                  1: 'Talk',
                  }

    start = 'start'; end = 'end'
    context = iterparse(fh, events=(start, end))
    context = iter(context)

    article = {}
    count_articles = 0
    id = False
    ns = False
    parse = False
    rev1 = None
    rev2 = None
    file_id, fh_output = None, None

    try:
        for event, elem in context:
            if event is end and elem.tag.endswith('siteinfo'):
                '''
                This event happens once for every dump file and is used to
                determine the version of the generator used to generate the XML
                file. 
                '''
                xml_namespace = variables.determine_xml_namespace(elem)
                namespaces = variables.create_namespace_dict(elem, xml_namespace)
                ns = True
                elem.clear()

            elif event is end and elem.tag.endswith('title'):
                '''
                This function determines the title of an article and the
                namespace to which it belongs. Then, if the namespace is one
                which we are interested in set parse to True so that we start
                parsing this article, else it will skip this article. 
                '''
                title = variables.parse_title(elem)
                article['title'] = title
                current_namespace = variables.determine_namespace(title, namespaces, include_ns)
                if current_namespace == 1 or current_namespace == 3 or current_namespace == 5:
                    parse = True
                    article['namespace'] = current_namespace
                    count_articles += 1
                    if count_articles % 10000 == 0:
                        print 'Worker %s parsed %s articles' % (process_id, count_articles)
                elem.clear()

            elif elem.tag.endswith('revision'):
                '''
                This function does the actual analysis of an individual revision,
                calculating size difference between this and previous revision and
                calculating md5 hash to determine whether this edit was reverted.
                '''
                if parse:
                    if event is start:
                        clear = False
                    else:
                        #dump(elem)
                        rev_id = elem.find('%s%s' % (xml_namespace, 'id'))
                        timestamp = elem.find('%s%s' % (xml_namespace, 'timestamp')).text
                        contributor = elem.find('%s%s' % (xml_namespace, 'contributor'))
                        editor = variables.parse_contributor(contributor, None, xml_namespace)
                        if editor:
                            rev_id = variables.extract_revision_id(rev_id)

                            if rev1 == None and rev2 == None:
                                diff = variables.extract_revision_text(elem, xml_namespace)
                                rev1 = elem
                            if rev1 != None and rev2 != None:
                                diff = diff_revision(rev1, rev2, xml_namespace)

                            article[rev_id] = {}
                            article[rev_id].update(editor)
                            article[rev_id]['timestamp'] = timestamp
                            article[rev_id]['diff'] = diff

                        clear = True
                    if clear:
                        rev2 = rev1
                        elem.clear()
                else:
                    elem.clear()

            elif event is end and elem.tag.endswith('id') and id == False:
                '''
                Determine id of article
                '''
                article['article_id'] = elem.text
                id = True
                elem.clear()

            elif event is end and elem.tag.endswith('page'):
                '''
                We have reached end of an article, reset all variables and free
                memory. 
                '''
                elem.clear()
                #write diff of text to file
                if parse:
                    #print article
                    fh_output, file_id = assign_filehandle(fh_output, file_id, location, process_id, format)
                    write_diff(fh_output, article, format)
                #Reset all variables for next article
                article = {}
                if rev1 != None:
                    rev1.clear()
                if rev2 != None:
                    rev2.clear()
                id = False
                parse = False

    except SyntaxError, error:
        print 'Encountered invalid XML tag. Error message: %s' % error
        dump(elem)
        sys.exit(-1)
    except IOError, error:
        print '''Archive file is possibly corrupted. Please delete this archive 
            and retry downloading. Error message: %s''' % error
        sys.exit(-1)
    print 'Finished parsing Wikipedia dump file.'


def assign_filehandle(fh, file_id, location, process_id, format):
    if not fh:
        file_id = 0
        filename = '%s_%s.%s' % (file_id, process_id, format)
        fh = file_utils.create_txt_filehandle(location, filename, 'w', 'utf-8')
    else:
        size = fh.tell()
        max_size = 1024 * 1024 * 64
        if size > max_size:
            fh.close()
            file_id += 1
            filename = '%s_%s.%s' % (file_id, process_id, format)
            fh = file_utils.create_txt_filehandle(location, filename, 'w', 'utf-8')

    return fh, file_id

def write_xml_diff(fh, article):
    pass


def write_json_diff(fh, article):
    json.dump(article, fh)


def write_diff(fh, article, format):
    if format == 'xml':
        write_xml_diff(fh, article)
    elif format == 'json':
        write_json_diff(fh, article)
    else:
        raise exceptions.OutputNotSupported()


def diff_revision(rev1, rev2, xml_namespace):
    buffer = cStringIO.StringIO()
    if rev1.text != None and rev2.text != None:
        diff = difflib.unified_diff(rev1.text, rev2.text, n=0, lineterm='')
        for line in diff:
            if len(line) > 3:
                print line
                buffer.write(line)

        return buffer.getvalue()


def launcher(rts):
    '''
    This function initializes the multiprocessor, and loading the queue with
    the compressed XML files. 
    '''
    input_queue = JoinableQueue()

    files = file_utils.retrieve_file_list(rts.input_location)

    if len(files) > cpu_count():
        processors = cpu_count() - 1
    else:
        processors = len(files)

    fhd = buffer.FileHandleDistributor(rts.max_filehandles, processors)

    for filename in files:
        filename = os.path.join(rts.input_location, filename)
        print filename
        input_queue.put(filename)

    for x in xrange(processors):
        print 'Inserting poison pill %s...' % x
        input_queue.put(None)

    extracters = [Process(target=stream_raw_xml, args=[input_queue, process_id,
                                                       fhd, rts])
                  for process_id in xrange(processors)]
    for extracter in extracters:
        extracter.start()

    input_queue.join()


def launcher_simple():
    location = 'c:\\wikimedia\\nl\\wiki\\'
    output_location = 'c:\\wikimedia\\nl\\wiki\\diffs\\'
    files = file_utils.retrieve_file_list(location)
    process_id = 0
    format = 'json'
    for filename in files:
        fh = file_utils.create_streaming_buffer(os.path.join(location, filename))
        #fh = codecs.open(os.path.join(location, filename), 'r', 'utf-8')
        parse_xml(fh, format, process_id, output_location)
        fh.close()


def debug():
    str1 = """
    '''Welcome to Wikilytics !
'''
== Background == 
This package offers a set of tools used to create datasets to analyze Editor 
Trends. By Editor Trends we refer to the overall pattern of entering and leaving
a Wikipedia site. The main information source for this package is [[:strategy:Editor Trends Study|Editor Trends Study]]

== High-level Overview Editor Trends Analytics ==

The Python scripts to create the dataset to answer the question '''“Which editors are the ones that are leaving - -are they the new editors or the more tenured ones?”''' consists of three separate phases:
* Chunk the XML dump file in smaller parts
** and discard all non-zero namespace revisions.
* Parse XML chunks by taking the following steps:
** read XML chunk
** construct XML DOM
** iterate over each article in XML DOM
** iterate over each revision in each article
** extract from each revision
*** username id
*** date edit
*** article id
** determine if username belongs to bot, discard information if yes
** store data in MongoDB
* Create dataset from MongoDB database
** Create list with unique username id’s
** Loop over each id
*** determine year of first edit
*** determine year of last edit
*** count total number of edits by year
*** sort edits by date and keep first 10 edits
** Write to CSV file.

== Schema of Editor Trends Database ==
Each person who has contributed to Wikipedia has it's own document in the [http://www.mongodb.org MongoDB]. A document is a bit similar to a row in a [http://en.wikipedia.org/wiki/SQL SQL] database but there are important differences. The document has the following structure:

<source lang='javascript'>
{'editor': id,
 'year_joined': year,
 'new_wikipedian': True,
 'total_edits': n,
 'edits': {
           'date': date,
       'article': article_id,
      }
}
</source>
The edits variable is a sub document containing all the edits made by that person. The edits variable is date sorted, so the first observation is the first edit made by that person while the last observation is the final edit made by that person. This structure allows for quickly querying
the database:

<pre>
use enwiki
editors = enwiki['editors']
enwiki.editors.find_one({'editor': '35252'}, {'edits': 1})[0]
</pre>


Because we know that each editor has their own document, we do not need to scan the entire table to find all relevant matches. Hence, we can use the find_one() function which results in considerable speed improvements.

== Installation ==

=== Step-by-Step Movie Tutorial ===
There is a online tutorial available at [http://vimeo.com/16850312 Vimeo]. You cannot install Editor Trends toolkit on OSX at the moment, I will try to code around some OSX restrictions regarding multiprocessing. 

=== Dependencies ===

Follow the next steps if you would like to replicate the analysis on a Wikipedia of your choice.

# Download and install [http://www.mongodb.com MongoDB], preferably the 64 bit version. 
# Download and install [http://www.python.org/download Python] 2.6 or 2.7 (The code is not Python 3 compliant and it has not been tested using Python < 2.6)
#: Linux users may need to install the packages python-argparse, python-progressbar and pymongo if that functionality is not installed by default with python.
# Download and install [http://www.sliksvn.com/en/download Subversion] client 
# Depending on your platform make sure you have one of the following extraction utilities installed:
:* Windows: [http://www.7zip.com 7zip]
:* Linux: tar (should be installed by default)

To verify that you have installed the required dependencies, do the following:
<pre>
<prompt>:: mongo
MongoDB shell version: 1.6.3
connecting to: test
<prompt> (in mongo shell) exit

<prompt>:: python
Python 2.6.2 (r262:71605, Apr 14 2009, 22:40:02) [MSC v.1500 32 bit (Intel)] on
win32
Type "help", "copyright", "credits" or "license" for more information.
<prompt> (in python) exit()

<prompt>:: 7z or tar (depending on your platform)
7-Zip [64] 4.65  Copyright (c) 1999-2009 Igor Pavlov  2009-02-03

<prompt>:: svn

</pre>
Output on the console might look different depending on your OS and installed version. 

'''For Windows Users, add the following directories to the path'''
<pre>c:\python26;c:\python26\scripts;c:\mongodb\bin;</pre>

To finish the Mongodb configuration, do the following:
<pre>
cd \
mkdir data
mkdir data\db
cd \mongodb\bin
mongod --install --logpath c:\mongodb\logs
net start mongodb
</pre>

Prepare your Python environment by taking the following steps:
1 Check whether easy_install is installed by issuing the command:
<pre>
easy_install
</pre>
If easy_install is not installed then enter the following command:
<pre>
sudo apt-get install python-setuptools
</pre>
2 Check whether virtualenv is installed by the issuing the following command:
<pre>
virtualenv
</pre>
If virtualenv is not installed enter this command:
<pre>
sudo easy_install virtualenv
</pre>
Go to the directory where you want to install your virtual Python, it's okay to go to the parent directory of editor_trends. Then, issue this command:
<pre>
virtualenv editor_trends
</pre>
This will copy the Python executable and libraries to editor_trends/bin and editor_trends/libs
Now, we have to activate our virtual Python:
<pre>
source bin/activate
</pre>
You will see that your command prompt has changed to indicate that you are working with the virtual Python installation instead of working with the systems default installation. 
If you now install dependencies then these dependencies will be installed in your virtual Python installation instead of in the system Python installation. This will keep everybody happy.
Finally, enter the following commands:
<pre>
easy_install progressbar
easy_install pymongo
easy_install argparse
easy_install python-dateutil
easy_install texttable
</pre>
Python is installed and you are ready to go!

If everything is running, then you are ready to go.
==== Important MongoDB Notes ====
If you decide to use MongoDB to store the results then you have to install the 
64-bit version. 32-bit versions of MongoDB are limited to 2GB of data and the 
databases created by this package will definitely be larger than that. For more
background information on this limitation, please read [http://blog.mongodb.org/post/137788967/32-bit-limitations MongoDB 32-bit limitations]

=== Install Editor Trend Analytics ===
First, download Editor Trend Analytics
* Windows: svn checkout http://svn.wikimedia.org/svnroot/mediawiki/trunk/tools/editor_trends/ editor_trends
* Linux: svn checkout http://svn.wikimedia.org/svnroot/mediawiki/trunk/tools/editor_trends/ editor_trends

=== Getting started ===
By now, you should have Editor Trend Analytics up and running. The first thing you need to do is to download a Wikipedia dump file.
<blockquote>From now on, I'll assume that you are locate in the directory where you installed Editor Trend Analytics.</blockquote>

==== Download Wikipedia dump file ====
To download a dump file enter the following command:
<pre>
python manage.py download
</pre>
You can also specify the language (either using the English name or the local name) of the Wikipedia project that you would like to analyze:
<pre>
python manage.py -l Spanish download 
python manage.py -l Español download 
</pre>
Or, if you want to download a non Wikipedia dump file, enter the following command:
<pre>
python manage.py -l Spanish download {commons|wikibooks|wikinews|wikiquote|wikisource|wikiversity|wikitionary}
</pre>

To obtain a list of all supported languages, enter:
<pre>
manage show_languages
</pre>
or to obtain all languages starting with 'x', enter:
<pre>
python manage.py show_languages --first x
</pre>


==== Extract Wikipedia dump file ====
'''WARNING''': This process might take hours to days, depending on the configuration of your system.
The Wikipedia dump file is extracted and split into smaller chunks to speed up the processing. Enter the following command:
<pre>
python manage.py extract (for extracting data from the Wikipedia dump file and storing it in smaller chunks)
</pre>
or, for one of the other Wikimedia projects, enter 
<pre>
python manage.py -l Spanish -p commons extract
</pre>
Valid project choices are: {commons|wikibooks|wikinews|wikiquote|wikisource|wikiversity|wikitionary}

'''Note:''' The extract process may need to be run twice. Once to unzip the dump file, then again to extract the data from the dump file.


==== Sort Wikipedia dump file ====
'''WARNING''': This process might take a few hours.
The chunks must be sorted before being added to the MongoDB. Enter the following command:
<pre>
python manage.py sort (for sorting the chunks as generated by the 'manage extract' step)
</pre>
or, for one of the other Wikimedia projects, enter 
<pre>
python manage.py -l Spanish sort {commons|wikibooks|wikinews|wikiquote|wikisource|wikiversity|wikitionary}
</pre>


==== Store Wikipedia dump file ====
'''WARNING''': This process might take hours to days, depending on the configuration of your system.
Now, we are ready to extract the required information from the Wikipedia dump file chunks and store it in the MongoDB. Enter the following command:
<pre>
python manage.py store
python manage.py -l Spanish store
</pre>
or, for one of the other Wikimedia projects, enter 
<pre>
python manage.py -l Spanish store {commons|wikibooks|wikinews|wikiquote|wikisource|wikiversity|wikitionary}
</pre>

==== Transform dataset ====
'''WARNING''': This process might take a couple of hours.
Finally, the raw data needs to be transformed in useful variables. Issue the following command:
<pre>
python manage.py transform
python manage.py -l Spanish transform
</pre>

==== Create dataset ====
'''WARNING''': This process might take a couple of hours to days depending on the configuration of your computer. 
We are almost there, the data is in the database and now we need to export the data to a [[:en:CSV|CSV]] file so we can import it using a statistical program such as [[:en:R (programming language)]], [[:en:Stata]] or [[:en:SPSS]]. 

Enter the following command:
<pre>
python manage.py dataset 
python manage.py -l Spanish dataset
</pre>
or, for one of the other Wikimedia projects, enter 
<pre>
manage -l Spanish {commons|wikibooks|wikinews|wikiquote|wikisource|wikiversity|wikitionary} dataset
</pre>

==== Everything in one shot ====
'''WARNING''': This process might take a couple of days or even more than a week depending on the configuration of your computer.
If you don't feel like monitoring your computer and you just want to create a dataset from scratch, enter the following command:
<pre>
python manage.py all language
python manage.py -l Spanish all
</pre>
<pre>
python manage.py -p {commons|wikibooks|wikinews|wikiquote|wikisource|wikiversity|wikitionary} all
</pre>


=== Benchmarks ===
{| border=0
  |+ ''Benchmark German Wiki''
|-
  ! Task
  ! Configuration 1
  ! Configuration 2
|-
  | Download 
  | 
  | 1 minute 14 seconds
|-
  | Extract
  | 
  | 4-6 hours
|-
  | Sort
  | 
  | ~30 minutes
|-
  | Store
  | 
  | 4-5 hours
|-
  | Transform
  | 
  | 2-3 hours
|-
  | Total time
  | 
  | 10-14 hours

|}


{| border=0
  |+ ''Benchmark English Wiki''
|-
  ! Task
  ! Configuration 1
  ! Configuration 2
|-
  | Download 
  | 
  | 15 minutes
|-
  | Extract
  | 
  | ~36 hours
|-
  | Sort
  | 
  | 10.5 hours
|-
  | Store
  | 
  | 21 hours
|-
  | Transform
  | 
  | 14.3 hours
|-
  | Total time
  | 
  | 3.4 days

|}


{| width="300" border="1"
  |+ ''Benchmark Hungarian Wiki''
|-
  ! Task
  ! Configuration 3
|-
  | Download 
  | 1-2 minutes
|-
  | Extract
  | 24.5 minutes
|-
  | Sort
  | 1.5 minutes
|-
  | Store
  | 7-8 minutes
|-
  | Transform
  | 11 minutes
|-
  | Total time
  | ~45 minutes
|}


;Configuration 2
''Amazon Web Services Large EC2 Instance''
* Ubuntu 64-bit
* 4 EC2 Compute Units (2 virtual cores)
* 7.5GB memory
* 850GB storage

;Configuration 3
* Win7 64 bit
* Intel i7 CPU (8 virtual core)
* 6GB memory
* 1TB storage
* 100/100Mb/s internet connection


[[Category:Editor Trends Study]]
""".splitlines(1)

    str2 = """
Welcome to '''Wikilytics''', a free and open source software toolkit for doing analysis of editing trends in Wikipedia and other Wikimedia projects. 

== Background == 
This package offers a set of tools used to create datasets to analyze editing trends. It was first created expressly for the [[:strategy:Editor Trends Study|Editor Trends Study]], but is well-suited to a variety of research into editing trends. It is thus free to use (as in beer and freedom) if you're interested in expanding on the [[:strategy:Editor Trends Study/Results|results of Editor Trend Study]] or if you'd like to participate in other [[Research/Projects|research projects]].

== High-level Overview Editor Trends Analytics ==

The Python scripts to create the dataset to answer the question '''“Which editors are the ones that are leaving - -are they the new editors or the more tenured ones?”''' consists of three separate phases:
* Chunk the XML dump file in smaller parts
** and discard all non-zero namespace revisions.
* Parse XML chunks by taking the following steps:
** read XML chunk
** construct XML DOM
** iterate over each article in XML DOM
** iterate over each revision in each article
** extract from each revision
*** username id
*** date edit
*** article id
** determine if username belongs to bot, discard information if yes
** store data in MongoDB
* Create dataset from MongoDB database
** Create list with unique username id’s
** Loop over each id
*** determine year of first edit
*** determine year of last edit
*** count total number of edits by year
*** sort edits by date and keep first 10 edits
** Write to CSV file.

== Schema of Editor Trends Database ==
Each person who has contributed to Wikipedia has it's own document in the [http://www.mongodb.org MongoDB]. A document is a bit similar to a row in a [http://en.wikipedia.org/wiki/SQL SQL] database but there are important differences. The document has the following structure:

<source lang='javascript'>
{'editor': id,
 'year_joined': year,
 'new_wikipedian': True,
 'total_edits': n,
 'edits': {
           'date': date,
       'article': article_id,
      }
}
</source>
The edits variable is a sub document containing all the edits made by that person. The edits variable is date sorted, so the first observation is the first edit made by that person while the last observation is the final edit made by that person. This structure allows for quickly querying
the database:

<pre>
use wikilitycs 
db.editors_dataset.find_one({'editor': '35252'}, {'edits': 1})
</pre>


Because we know that each editor has their own document, we do not need to scan the entire table to find all relevant matches. Hence, we can use the find_one() function which results in considerable speed improvements.

== Installation ==

=== Step-by-Step Movie Tutorial ===
There is a online tutorial available at [http://vimeo.com/16850312 Vimeo]. You cannot install Editor Trends toolkit on OSX at the moment, I will try to code around some OSX restrictions regarding multiprocessing. 

=== Dependencies ===

Follow the next steps if you would like to replicate the analysis on a Wikipedia of your choice.

# Download and install [http://www.mongodb.com MongoDB], preferably the 64 bit version. 
# Download and install [http://www.python.org/download Python] 2.6 or 2.7 (The code is not Python 3 compliant and it has not been tested using Python < 2.6)
#: Linux users may need to install the packages python-argparse, python-progressbar and pymongo if that functionality is not installed by default with python.
# Download and install [http://www.sliksvn.com/en/download Subversion] client 
# Depending on your platform make sure you have one of the following extraction utilities installed:
:* Windows: [http://www.7zip.com 7zip]
:* Linux: tar (should be installed by default)

To verify that you have installed the required dependencies, do the following:
<pre>
<prompt>:: mongo
MongoDB shell version: 1.6.3
connecting to: test
<prompt> (in mongo shell) exit

<prompt>:: python
Python 2.6.2 (r262:71605, Apr 14 2009, 22:40:02) [MSC v.1500 32 bit (Intel)] on
win32
Type "help", "copyright", "credits" or "license" for more information.
<prompt> (in python) exit()

<prompt>:: 7z or tar (depending on your platform)
7-Zip [64] 4.65  Copyright (c) 1999-2009 Igor Pavlov  2009-02-03

<prompt>:: svn

</pre>
Output on the console might look different depending on your OS and installed version. 

'''For Windows Users, add the following directories to the path'''
<pre>c:\python26;c:\python26\scripts;c:\mongodb\bin;</pre>

To finish the Mongodb configuration, do the following:
<pre>
cd \
mkdir data
mkdir data\db
cd \mongodb\bin
mongod --install --logpath c:\mongodb\logs
net start mongodb
</pre>

Prepare your Python environment by taking the following steps:
1 Check whether easy_install is installed by issuing the command:
<pre>
easy_install
</pre>
If easy_install is not installed then enter the following command:
<pre>
sudo apt-get install python-setuptools
</pre>
2 Check whether virtualenv is installed by the issuing the following command:
<pre>
virtualenv
</pre>
If virtualenv is not installed enter this command:
<pre>
sudo easy_install virtualenv
</pre>
Go to the directory where you want to install your virtual Python, it's okay to go to the parent directory of editor_trends. Then, issue this command:
<pre>
virtualenv editor_trends
</pre>
This will copy the Python executable and libraries to editor_trends/bin and editor_trends/libs
Now, we have to activate our virtual Python:
<pre>
source bin/activate
</pre>
You will see that your command prompt has changed to indicate that you are working with the virtual Python installation instead of working with the systems default installation. 
If you now install dependencies then these dependencies will be installed in your virtual Python installation instead of in the system Python installation. This will keep everybody happy.
Finally, enter the following commands:
<pre>
easy_install progressbar
easy_install pymongo
easy_install argparse
easy_install python-dateutil
easy_install texttable
</pre>
Python is installed and you are ready to go!

If everything is running, then you are ready to go.
==== Important MongoDB Notes ====
If you decide to use MongoDB to store the results then you have to install the 
64-bit version. 32-bit versions of MongoDB are limited to 2GB of data and the 
databases created by this package will definitely be larger than that. For more
background information on this limitation, please read [http://blog.mongodb.org/post/137788967/32-bit-limitations MongoDB 32-bit limitations]

=== Install Editor Trend Analytics ===
First, download Editor Trend Analytics
* Windows: svn checkout http://svn.wikimedia.org/svnroot/mediawiki/trunk/tools/editor_trends/ editor_trends
* Linux: svn checkout http://svn.wikimedia.org/svnroot/mediawiki/trunk/tools/editor_trends/ editor_trends

=== Getting started ===
By now, you should have Editor Trend Analytics up and running. The first thing you need to do is to download a Wikipedia dump file.
<blockquote>From now on, I'll assume that you are locate in the directory where you installed Editor Trend Analytics.</blockquote>

==== Download Wikipedia dump file ====
To download a dump file enter the following command:
<pre>
python manage.py download
</pre>
You can also specify the language (either using the English name or the local name) of the Wikipedia project that you would like to analyze:
<pre>
python manage.py -l Spanish download 
python manage.py -l Español download 
</pre>
Or, if you want to download a non Wikipedia dump file, enter the following command:
<pre>
python manage.py -l Spanish download {commons|wikibooks|wikinews|wikiquote|wikisource|wikiversity|wikitionary}
</pre>

To obtain a list of all supported languages, enter:
<pre>
manage show_languages
</pre>
or to obtain all languages starting with 'x', enter:
<pre>
python manage.py show_languages --first x
</pre>


==== Extract Wikipedia dump file ====
'''WARNING''': This process might take hours to days, depending on the configuration of your system.
The Wikipedia dump file is extracted and split into smaller chunks to speed up the processing. Enter the following command:
<pre>
python manage.py extract (for extracting data from the Wikipedia dump file and storing it in smaller chunks)
</pre>
or, for one of the other Wikimedia projects, enter 
<pre>
python manage.py -l Spanish -p commons extract
</pre>
Valid project choices are: {commons|wikibooks|wikinews|wikiquote|wikisource|wikiversity|wikitionary}

'''Note:''' The extract process may need to be run twice. Once to unzip the dump file, then again to extract the data from the dump file.


==== Sort Wikipedia dump file ====
'''WARNING''': This process might take a few hours.
The chunks must be sorted before being added to the MongoDB. Enter the following command:
<pre>
python manage.py sort (for sorting the chunks as generated by the 'manage extract' step)
</pre>
or, for one of the other Wikimedia projects, enter 
<pre>
python manage.py -l Spanish sort {commons|wikibooks|wikinews|wikiquote|wikisource|wikiversity|wikitionary}
</pre>


==== Store Wikipedia dump file ====
'''WARNING''': This process might take hours to days, depending on the configuration of your system.
Now, we are ready to extract the required information from the Wikipedia dump file chunks and store it in the MongoDB. Enter the following command:
<pre>
python manage.py store
python manage.py -l Spanish store
</pre>
or, for one of the other Wikimedia projects, enter 
<pre>
python manage.py -l Spanish store {commons|wikibooks|wikinews|wikiquote|wikisource|wikiversity|wikitionary}
</pre>

==== Transform dataset ====
'''WARNING''': This process might take a couple of hours.
Finally, the raw data needs to be transformed in useful variables. Issue the following command:
<pre>
python manage.py transform
python manage.py -l Spanish transform
</pre>

==== Create dataset ====
'''WARNING''': This process might take a couple of hours to days depending on the configuration of your computer. 
We are almost there, the data is in the database and now we need to export the data to a [[:en:CSV|CSV]] file so we can import it using a statistical program such as [[:en:R (programming language)]], [[:en:Stata]] or [[:en:SPSS]]. 

Enter the following command:
<pre>
python manage.py dataset 
python manage.py -l Spanish dataset
</pre>
or, for one of the other Wikimedia projects, enter 
<pre>
manage -l Spanish {commons|wikibooks|wikinews|wikiquote|wikisource|wikiversity|wikitionary} dataset
</pre>

==== Everything in one shot ====
'''WARNING''': This process might take a couple of days or even more than a week depending on the configuration of your computer.
If you don't feel like monitoring your computer and you just want to create a dataset from scratch, enter the following command:
<pre>
python manage.py all language
python manage.py -l Spanish all
</pre>
<pre>
python manage.py -p {commons|wikibooks|wikinews|wikiquote|wikisource|wikiversity|wikitionary} all
</pre>


=== Benchmarks ===
{| border=0
  |+ ''Benchmark German Wiki''
|-
  ! Task
  ! Configuration 1
  ! Configuration 2
|-
  | Download 
  | 
  | 1 minute 14 seconds
|-
  | Extract
  | 
  | 4-6 hours
|-
  | Sort
  | 
  | ~30 minutes
|-
  | Store
  | 
  | 4-5 hours
|-
  | Transform
  | 
  | 2-3 hours
|-
  | Total time
  | 
  | 10-14 hours

|}


{| border=0
  |+ ''Benchmark English Wiki''
|-
  ! Task
  ! Configuration 1
  ! Configuration 2
|-
  | Download 
  | 
  | 15 minutes
|-
  | Extract
  | 
  | ~36 hours
|-
  | Sort
  | 
  | 10.5 hours
|-
  | Store
  | 
  | 21 hours
|-
  | Transform
  | 
  | 14.3 hours
|-
  | Total time
  | 
  | 3.4 days

|}


{| width="300" border="1"
  |+ ''Benchmark Hungarian Wiki''
|-
  ! Task
  ! Configuration 3
|-
  | Download 
  | 1-2 minutes
|-
  | Extract
  | 24.5 minutes
|-
  | Sort
  | 1.5 minutes
|-
  | Store
  | 7-8 minutes
|-
  | Transform
  | 11 minutes
|-
  | Total time
  | ~45 minutes
|}


;Configuration 2
''Amazon Web Services Large EC2 Instance''
* Ubuntu 64-bit
* 4 EC2 Compute Units (2 virtual cores)
* 7.5GB memory
* 850GB storage

;Configuration 3
* Win7 64 bit
* Intel i7 CPU (8 virtual core)
* 6GB memory
* 1TB storage
* 100/100Mb/s internet connection

==See also==
* [[Wikilytics Dataset]]
* [[Wikilytics Plugins]]

[[Category:Wikilytics]]

""".splitlines(1)

    diff = difflib.unified_diff(str1, str2, n=0, lineterm='')
    for line in diff:
        if len(line) > 3:
            print line
#            print result

if __name__ == '__main__':
    launcher_simple()
    #debug()
