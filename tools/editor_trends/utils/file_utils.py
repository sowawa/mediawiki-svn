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
__date__ = '2010-10-21'
__version__ = '0.1'

'''
The utils module contains helper functions that will be needed throughout.
It provides functions to read / write data to text and binary files, fix markup
and track error messages.
'''

import bz2
import gzip
import re
import time
import datetime
import cPickle
import codecs
import os
import sys
import subprocess
import shutil

if '..' not in sys.path:
    sys.path.append('..')

from classes import settings
settings = settings.Settings()

from classes import exceptions

def read_unicode_text(fh):
    '''
    @fh should be a file object
    '''
    data = []
    try:
        for line in fh:
            line = line.strip()
            data.append(line)
    except UnicodeDecodeError, e:
        print e

    return data


def read_raw_data(fh):
    '''
    @fh should be a file object
    '''
    for line in fh:
        line = line.strip()
        line = line.split('\t')
        yield line


# read / write data related functions
def read_data_from_csv(location, filename, encoding):
    '''
    @filename is the path (either absolute or relative) including the name of
    of the file
    @encoding is usually utf-8 
    '''
    fh = create_txt_filehandle(location, filename, 'r', encoding)
    for line in fh:
        yield line

    fh.close()


def create_directory(path):
    '''
    @path is the absolute path
    '''
    try:
        os.mkdir(path)
        return True
    except IOError:
        return False


def determine_file_mode(extension):
    '''
    Checks if a given extension is an ASCII extension or not. 
    '''
    if extension in ['.txt', '.csv', '.json', '.xml']:
        return 'w'
    else:
        return 'wb'


def write_list_to_csv(data, fh, recursive=False, newline=True, format='long', lock=None):
    '''
    @data is a list which can contain other lists that will be written as a
    single line to a textfile
    @fh is a handle to an open text
    
    The calling function is responsible for:
        1) closing the filehandle
    '''
    tab = False
    wrote_newline = None
    if recursive:
        recursive = False
    if lock:
        lock.acquire()
    for x, d in enumerate(data):
        if tab:
            fh.write('\t')
        if isinstance(d, list):
            recursive = write_list_to_csv(d, fh, recursive=True, newline=False)
            #when there is a list of lists but no other elements in the first list
            #then write a newline. 
            if len(d) == len(data[x]):
                fh.write('\n')
        elif isinstance(d, dict):
            tab = write_dict_to_csv(d, fh, d.keys(), write_key=True, format=format)
        else:
            fh.write('%s' % d)
            tab = True

    if recursive:
        tab = False
        return True
    if newline:
        fh.write('\n')
    if lock:
        lock.release()

def write_dict_to_csv(data, fh, keys, write_key=True, format='long'):
    assert format == 'long' or format == 'wide', 'Format should either be long or wide.'

    if format == 'long':
        for key in keys:
            if write_key:
                fh.write('%s\t' % key)
            if isinstance(data[key], list):
                for d in data[key]:
                    fh.write('%s\t%s\n' % (key, d))
            elif isinstance(data[key], dict):
                write_dict_to_csv(data[key], fh, data[key].keys(), write_key=False, format=format)
            else:
                fh.write('%s\n' % (data[key]))
    elif format == 'wide':
        for key in keys:
            if write_key:
                fh.write('%s\t' % key)
            if isinstance(data[key], list):
                for d in data[key]:
                    fh.write('%s\t')
            elif isinstance(data[key], list):
                write_dict_to_csv(data[key], fh, data[key].keys(), write_key=False, format=format)
            else:
                fh.write('%s\t' % (data[key]))
        fh.write('\n')


def create_txt_filehandle(location, filename, mode, encoding):
    '''Create a filehandle for text file with utf-8 encoding'''
    filename = str(filename)
    if not filename.endswith('.csv'):
        if filename.find('.') == -1:
            filename = construct_filename(filename, '.csv')
    path = os.path.join(location, filename)
    return codecs.open(path, mode, encoding)


def create_streaming_buffer(path):
    extension = os.path.splitext(path)[1]
    if extension == '.gz':
        fh = gzip.GzipFile(path, 'rb')
    elif extension == '.bz2':
        fh = bz2.BZ2File(path, 'rb')
    elif extension == '.7z':
        #TODO: might be too linux specific
        fh = subprocess.Popen('7z e -bd -so %s 2>/dev/null' % path, shell=True,
                              stdout=subprocess.PIPE, bufsize=65535).stdout
    elif extension == '.xml':
        location, filename = os.path.split(path)
        fh = create_txt_filehandle(location, filename, 'r', 'utf-8')
    else:
        raise exceptions.CompressedFileNotSupported(extension)
    return fh


def create_binary_filehandle(location, filename, mode):
    path = os.path.join(location, filename)
    return open(path, mode)


def construct_filename(name, extension):
    if hasattr(name, '__call__'):
        return '%s%s' % (name.func_name, extension)
    else:
        return '%s%s' % (name, extension)


def delete_file(location, filename, directory=False):
    '''
    Delete a file or a directory
    '''
    res = True
    if not directory:
        if check_file_exists(location, filename):
            try:
                path = os.path.join(location, filename)
                os.remove(path)
            except WindowsError, error:
                res = False
                print error
    else:
        try:
            shutil.rmtree(location)
        except Exception, error:
            res = False
            print error
    return res


def determine_filesize(location, filename):
    '''Determine the file size of a local file'''
    path = os.path.join(location, filename)
    return os.path.getsize(path)


def set_modified_data(mod_rem, location, filename):
    '''
    Mod_rem is the modified date of the remote file (the Wikimedia dump file),
    Mon, 15 Mar 2010 07:07:30 GMT Example server timestamp
    '''
    assert isinstance(mod_rem, datetime.datetime), '''The mod_rem variable should 
        be an instane of datetime.datetime.'''
    path = os.path.join(location, filename)
    mod_rem = int(time.mktime(mod_rem.timetuple()))
    os.utime(path, (mod_rem, mod_rem))
    #sraise exceptions.NotYetImplementedError(set_modified_data)


def get_modified_date(location, filename):
    '''determine the date the file was originally created'''
    path = os.path.join(location, filename)
    mod_date = os.stat(path).st_mtime
    mod_date = datetime.datetime.fromtimestamp(mod_date)
    return mod_date


def check_file_exists(location, filename):
    '''check if a file exists in particular location'''
    if hasattr(filename, '__call__'):
        filename = construct_filename(filename, '.bin')
    if os.path.exists(os.path.join(location, filename)):
        return True
    else:
        return False


def which(program):
    '''determine the path where program can be found'''
    def is_exe(fpath):
        return os.path.exists(fpath) and os.access(fpath, os.X_OK)

    fpath, fname = os.path.split(program)
    if fpath:
        if is_exe(program):
            return program
    else:
        for path in os.environ["PATH"].split(os.pathsep):
            exe_file = os.path.join(path, program)
            if is_exe(exe_file):
                return exe_file

    raise exceptions.FileNotFoundException(program)


def store_object(obj, location, filename):
    '''
    Pickle object
    '''
    if hasattr(filename, '__call__'):
        filename = construct_filename(filename, '.bin')
    if not filename.endswith('.bin'):
        filename = filename + '.bin'
    fh = create_binary_filehandle(location, filename, 'wb')
    cPickle.dump(obj, fh)
    fh.close()


def load_object(location, filename):
    '''
    Load pickled object
    '''
    if hasattr(filename, '__call__'):
        filename = construct_filename(filename, '.bin')
    if not filename.endswith('.bin'):
        filename = filename + '.bin'
    fh = create_binary_filehandle(location, filename, 'rb')
    obj = cPickle.load(fh)
    fh.close()
    return obj



def create_dict_from_csv_file(location, filename, encoding, keys=None):
    '''
    Constructs a dictionary from a txtfile
    The first column of the csv file should contain the main key for the dictionary.
    If there are more than one value in the values list, then a @keys variable should
    be supplied and the key sequence should match the value sequence.
    '''
    d = {}
    for line in read_data_from_csv(location, filename, encoding):
        line = line.strip()
        line = line.split('\t')
        key = line[0]
        values = line[1:]
        if len(values) == 1:
            d[key] = values
        else:
            assert keys != None, 'Keys cannot be an instance of None.'
            d[key] = {}
            for k, v in zip(keys, values):
                d[key][k] = v
    return d


def determine_canonical_name(filename):
    '''
    Determine the name of a file by stripping away all extensions.
    '''
    while filename.find('.') > -1:
        filename = os.path.splitext(filename)[0]
    return filename


def retrieve_file_list(location, extension=None, mask=None):
    '''
    Retrieve a list of files from a specified location.
    @location: either an absolute or relative path
    @extension: only include files with extension (optional)
    @mask: only include files that start with mask (optional), this is
    interpreted as a regular expression. 
    
    @return: a list of files matching the criteria
    '''
    if mask:
        mask = re.compile(mask)
    else:
        mask = re.compile('[\w\d*]')
    all_files = os.listdir(location)
    files = []
    for filename in all_files:
        filename = filename.split('.')
        if len(filename) == 1:
            continue
        if extension:
            if re.match(mask, filename[0]) and filename[-1].endswith(extension):
                files.append('.'.join(filename))
        elif re.match(mask, filename[0]):
            files.append('.'.join(filename))
    return files


def merge_list(datalist):
    merged = []
    for d in datalist:
        for x in datalist[d]:
            merged.append(x)
    return merged


def split_list(datalist, maxval):
    chunks = {}
    a = 0
    parts = int(round(float(len(datalist)) / maxval, 0))
    for x in xrange(maxval):
        b = a + parts
        chunks[x] = datalist[a:b]
        a = (x + 1) * parts
        if a >= len(datalist):
            break
    return chunks


def debug():
    pass

if __name__ == '__main__':
    debug()
