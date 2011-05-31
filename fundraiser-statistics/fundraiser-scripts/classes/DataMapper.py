
"""

This module is used to define the mapping between data sources.  The primary intention is to convert Squid log representations into 
MySQL database representations however this task may grow over time.

The DataMapper class decouples the data mapping function of from the loading and reporting using Adapter structural pattern.

"""

__author__ = "Ryan Faulkner"
__revision__ = "$Rev$"
__date__ = "April 25th, 2011"


import sys
import urlparse as up
import httpagentparser
import math
import commands

import cgi    
import re        
import gzip   
import os

import MySQLdb 

import datetime
import Fundraiser_Tools.classes.DataLoader as DL
import Fundraiser_Tools.classes.Helper as Hlp
import Fundraiser_Tools.settings as projSet
import Fundraiser_Tools.classes.TimestampProcessor as TP
import Fundraiser_Tools.classes.FundraiserDataHandler as FDH

"""

    BASE CLASS :: DataMapper
    
    Base class for interacting with DataSource.  Methods that are intended to be extended in derived classes include:
    
    METHODS:
        
        copy_logs                             - copies logs from source to destination for processing 

"""
class DataMapper(object):
    
    """
        Copies mining logs from remote site
    """
    def copy_logs(self, type):
        
        # '/archive/udplogs'
        
        now = datetime.datetime.now()
        
    
        if type == FDH._TESTTYPE_BANNER_:
            prefix = 'bannerImpressions-'
        elif type == FDH._TESTTYPE_LP_:
            prefix = 'landingpages-'
        
        if now.hour > 12:
            filename = prefix + str(now.year) + '-' + str(now.month) + '-' + str(now.day) + '-' + str(now.hour - 12) + 'PM*'
        else:
            filename = prefix + str(now.year) + '-' + str(now.month) + '-' + str(now.day) + '-' + str(now.hour) + 'AM*'    
        
        filename = 'bannerImpressions-2011-05-27-11PM--25*'
        
        cmd = 'sftp ' + projSet.__user__ + '@' + projSet.__squid_log_server__ + ':' + projSet.__squid_log_home__ + filename  + ' ' + projSet.__squid_log_local_home__
        
        os.system(cmd)

        return filename

"""

    CLASS :: FundraiserDataMapper
    
    Data mapper specific to the Wikimedia Fundraiser
    
    METHODS:
        
        mine_squid_impression_requests        - mining banner impressions from squid logs
        mine_squid_landing_page_requests      - mining landing page views from squid logs

"""
class FundraiserDataMapper(DataMapper):
    
    _db = None
    _cur = None
    
    _squid_log_directory_ = projSet.__project_home__ + 'logs/' 
    _impression_table_name_ = 'banner_impressions'
    _landing_page_table_name_ = 'landing_page_requests'
    
    _BANNER_REQUEST_ = 0
    _LP_REQUEST_ = 1
    
    _BANNER_FIELDS_ =  ' (start_timestamp, utm_source, referrer, country, lang, counts, on_minute) '
    _LP_FIELDS_ = ' (start_timestamp, utm_source, utm_campaign, utm_medium, landing_page, page_url, referrer_url, browser, lang, country, project, ip, request_time) '
    
    
    """ !! MODIFY -- use dataloaders! """ 
    def _init_db(self):     
        self._db = MySQLdb.connect(host='127.0.0.1', user='rfaulk', db='faulkner', port=3307)
        self._cur = self._db.cursor()
        
    """ !! MODIFY -- use dataloaders! """ 
    def _close_db(self):
        self._cur.close()
        self._db.close()
        
        
    
    def _clear_squid_records(self, start, request_type):
        
        
        """ Ensure that the range is correct; otherwise abort - critical that outside records are not deleted """
        timestamp = TP.timestamp_convert_format(start,1,2)
        
        if request_type == self._BANNER_REQUEST_:
            deleteStmnt = 'delete from ' + self._impression_table_name_ + ' where start_timestamp = \'' + timestamp + '\';'
        elif request_type == self._LP_REQUEST_:
            deleteStmnt = 'delete from ' + self._landing_page_table_name_ + ' where start_timestamp = \'' + timestamp + '\';'
        
        try:
            self._cur.execute(deleteStmnt)
            print >> sys.stdout, "Executed delete from impression: " + deleteStmnt
        except:
            print >> sys.stderr, "Could not execute delete:\n" + deleteStmnt + "\nResuming insert ..."
            pass

        
        
    """
        
    """
    def mine_squid_impression_requests(self, logFileName):
    
        self._init_db()

        sltl = DL.SquidLogTableLoader()
        itl = DL.ImpressionTableLoader()
        
        """ Retrieve the log timestamp from the filename """
        time_stamps = Hlp.get_timestamps(logFileName)
        
        start = time_stamps[0]
        end = time_stamps[1]
        start_timestamp_in = "convert(\'" + start + "\', datetime)"
        curr_time = TP.timestamp_from_obj(datetime.datetime.now(),1,3)
                
        """ Initialization - open the file
            logFileName = sys.argv[1]; """
        if (re.search('\.gz', logFileName)):
            logFile = gzip.open(self._squid_log_directory_ + logFileName, 'r')
            total_lines_in_file = float(commands.getstatusoutput('zgrep -c "" ' + self._squid_log_directory_ + logFileName)[1])
        else:
            logFile = open(self._squid_log_directory_ + logFileName, 'r')
            total_lines_in_file = float(commands.getstatusoutput('grep -c "" ' + self._squid_log_directory_ + logFileName)[1])
    
        queryIndex = 4;
    
        counts = Hlp.AutoVivification()
        insertStmt = 'INSERT INTO ' + self._impression_table_name_ + self._BANNER_FIELDS_ + ' values '
    
        min_log = -1
        hr_change = 0
        clamp = 0
    
        """ Clear the old records """
        self._clear_squid_records(start, self._BANNER_REQUEST_)
        
        """ Add a row to the SquidLogTable """
        sltl.insert_row(type='banner_impression',log_copy_time=curr_time,start_time=start,end_time=end,log_completion_pct='0.0',total_rows='0')
        
        # PROCESS LOG FILE
        # ================
    
        line_count = 0
        line = logFile.readline()
        while (line != ''):
    
            lineArgs = line.split()
                
            try:
                time_stamp = lineArgs[2]
                time_bits = time_stamp.split('T')
                date_fields = time_bits[0].split('-')
                time_fields = time_bits[1].split(':')
                minute = int(time_fields[1])
            except (ValueError, IndexError):
                line = logFile.readline()
                total_lines_in_file = total_lines_in_file - 1
                continue
                # pass
            
            # Weird and confusing logic used to deal with logs that aren't sequential
    
            if minute == 0 and not(hr_change) and not(clamp):
                min_log = -1
    
            if minute == 1:
                hr_change = 0
                clamp = 1
    
            # =================
    
            try:
                url = lineArgs[8]
            except IndexError:
                url = 'Unavailable'
    
            parsedUrl = up.urlparse(url)
            query = parsedUrl[queryIndex]
            queryBits = cgi.parse_qs(query)
    
            # Extract - project, banner, language, & country data
            project = ''
            if ('db' in queryBits.keys()):
                project = queryBits['db'][0]
    
            if (project == '' and 'sitename' in queryBits.keys()):
                sitename = queryBits['sitename'][0];
                if sitename:
                    project = sitename
                else:
                    project = 'NONE'
    
            if ('banner' in queryBits.keys()):
                banner = queryBits['banner'][0]
            else:
                banner = 'NONE'
    
            if ('userlang' in queryBits.keys()):
                lang = queryBits['userlang'][0]
            else:
                lang = 'NONE'
    
            if ('country' in queryBits.keys()):
                country = queryBits['country'][0]
            else:
                country = 'NONE'
    
    
            try:
                counts[banner][country][project][lang] = counts[banner][country][project][lang] + 1
            except TypeError:
                counts[banner][country][project][lang] = 1
    
            """
            try:
                counts[att_1][att_2][att_3] = counts[att_1][att_2][att_3] + 1
            except TypeError:
                counts[att_1][att_2][att_3] = 1
            """

            # Break out impression data by minute
            if min_log < minute and not(hr_change):
    
                if minute == 0:
                    hr_change = 1
    
                min_log = minute
                time_stamp_in = "convert(\'" + date_fields[0] + date_fields[1] + date_fields[2] + time_fields[0] + time_fields[1]  + "00\', datetime)"

    
                # print time_stamp_in
    
                bannerKeys = counts.keys()
                for banner_ind in range(len(bannerKeys)):
                    banner = bannerKeys[banner_ind]
                    countryCounts = counts[banner]
                    countryKeys = countryCounts.keys()
    
                    for country_ind in range(len(countryKeys)):
                        country = countryKeys[country_ind]
                        projectCounts = countryCounts[country]
                        projectKeys = projectCounts.keys()
    
                        for project_ind in range(len(projectKeys)):
                            project = projectKeys[project_ind]
                            langCounts = projectCounts[project]
                            langKeys = langCounts.keys()
    
                            for lang_ind in range(len(langKeys)):
                                lang = langKeys[lang_ind]
                                count = langCounts[lang]
                                
                                try:
                                    val = '(' + start_timestamp_in + ',\'' + banner + '\',\'' + project + '\',\'' + country + '\',\'' + lang + '\',' \
                                    + str(count) + ',' + time_stamp_in + ');'

                                    self._cur.execute(insertStmt + val)
                                except:
                                    self._db.rollback()
                                    sys.exit("Database Interface Exception - Could not execute statement:\n" + insertStmt + val)
    
                # Re-initialize counts
                counts = Hlp.AutoVivification()

            line = logFile.readline()
            line_count = line_count + 1
            
            if line_count % 10000 == 0:
                completion = float(line_count / total_lines_in_file) * 100.0
                
                sltl.update_table_row(type='banner_impression',log_copy_time=curr_time,start_time=start,end_time=end,log_completion_pct=completion.__str__(),total_rows=line_count.__str__())

        self._close_db()

    """
        
    """
    def mine_squid_landing_page_requests(self,  logFileName):

        self._init_db()
        
        sltl = DL.SquidLogTableLoader()
        lptl = DL.LandingPageTableLoader()
        
        
        """ Retrieve the log timestamp from the filename """
        time_stamps = Hlp.get_timestamps(logFileName)
        
        start = time_stamps[0]
        end = time_stamps[1]
        start_timestamp_in = "convert(\'" + start + "\', datetime)"
        curr_time = TP.timestamp_from_obj(datetime.datetime.now(),1,3)
        
        count_parse = 0
        
        # open the file
        # logFileName = sys.argv[1]
        if (re.search('\.gz', logFileName)):
            logFile = gzip.open(self._squid_log_directory_ + logFileName, 'r')
            total_lines_in_file = float(commands.getstatusoutput('zgrep -c "" ' + self._squid_log_directory_ + logFileName)[1])        
        else:
            logFile = open(self._squid_log_directory_ + logFileName, 'r')
            total_lines_in_file = float(commands.getstatusoutput('grep -c "" ' + self._squid_log_directory_ + logFileName)[1])
    
        # Initialization
        hostIndex = 1;
        queryIndex = 4;
        pathIndex = 2;
    
        """ SQL Statements """
    
        insertStmt_lp = 'INSERT INTO ' + self._landing_page_table_name_ + self._LP_FIELDS_ + ' values '
    
        """ Clear the old records """
        self._clear_squid_records(start, self._LP_REQUEST_)
        
        """ Add a row to the SquidLogTable """
        sltl.insert_row(type='lp_view',log_copy_time=curr_time,start_time=start,end_time=end,log_completion_pct='0.0',total_rows='0')

        count_correct = 0
        count_total = 0
        line_count = 0
        
        # PROCESS LOG FILE
        # ================
        line = logFile.readline()
        while (line != ''):
            lineArgs = line.split()
    
            # Get the IP Address of the donor
            ip_add = lineArgs[4];
    
    
            # Process Timestamp
            # ================
            # 2010-10-21T23:55:01.431
            #  SELECT CAST('20070529 00:00:00' AS datetime)
    
    
            date_and_time = lineArgs[2];
    
            date_string = date_and_time.split('-')
            time_string = date_and_time.split(':')
    
            # if the date is not logged ignoere the record
            try:
                year = date_string[0]
                month = date_string[1]
                day = date_string[2][:2]
                hour = time_string[0][-2:]
                min = time_string[1]
                sec = time_string[2][:2]
            except:
                line = logFile.readline()
                total_lines_in_file = total_lines_in_file - 1
                continue
    
            timestamp_string = year + '-' + month + '-' + day + " " + hour + ":" + min + ":" + sec
    
    
            # Process referrer URL
            # ================
    
            try:
                referrer_url = lineArgs[11]
            except IndexError:
                referrer_url  = 'Unavailable'
                
            parsed_referrer_url = up.urlparse(referrer_url)
    
            if (parsed_referrer_url[hostIndex] == None):
                project = 'NONE';
                source_lang = 'NONE';
            else:
                hostname = parsed_referrer_url[hostIndex].split('.')
                
                if ( len( hostname[0] ) <= 2 ) :
                    # referrer_path = parsed_referrer_url[pathIndex].split('/')
                    project = hostname[0]                  # wikimediafoundation.org
                    source_lang = hostname[0]
                else:
                    try:
                        project = hostname[0] if ( hostname[1] == 'wikimedia' ) else hostname[1]  # species.wikimedia vs en.wikinews
                        source_lang = hostname[0] if ( len(hostname[1]) < 5 ) else 'en'  # pl.wikipedia vs commons.wikimedia
                    except:
                        project = ''
                        source_lang = 'en'
                
            # Process User agent string
            # =====================
            
            try:
                user_agent_string = lineArgs[13]
            except IndexError:
                user_agent_string = ''
                
            user_agent_fields = httpagentparser.detect(user_agent_string)
            browser = 'NONE'
    
            # Check to make sure fields exist
            if len(user_agent_fields['browser']) != 0:
                if len(user_agent_fields['browser']['name']) != 0:
                    browser = user_agent_fields['browser']['name']
    
    
            # Process landing URL
            # ================
    
            try:
                landing_url = lineArgs[8]
            except IndexError:
                landing_url = 'Unavailable'
    
            
            parsed_landing_url = up.urlparse(landing_url)
            query_fields = cgi.parse_qs(parsed_landing_url[queryIndex]) # Get the banner name and lang
            
            
            # Filter the landing URLs
            #
            # /wikimediafoundation.org/wiki/WMF/
            # /wikimediafoundation.org/w/index.php?title=WMF/
            path_pieces = parsed_landing_url[pathIndex].split('/')
            try:
                
                c1 = re.search('WMF', path_pieces[2] )  != None
                c2 = re.search('Hear_from_Kartika', path_pieces[2])   != None
                cond1 = parsed_landing_url[hostIndex] == 'wikimediafoundation.org' and path_pieces[1] == 'wiki' and (c1 or c2)
                
                c1 = re.search('index.php', path_pieces[2] )  != None
                index_str_flag = c1
                try:
                    c2 = re.search('WMF', query_fields['title'][0] ) != None
                except KeyError:
                    c2 = 0
                cond2 = (parsed_landing_url[hostIndex] == 'wikimediafoundation.org' and path_pieces[1] == 'w' and c1 and c2)
                
                if cond2:
                    count_parse = count_parse + 1
                    
                regexp_res = re.search('Special:LandingCheck',landing_url)
                cond3 = (regexp_res == None)
                
                include_request = (cond1 or cond2) and cond3
                 
    
            except: 
                include_request = 0
            
            if include_request:
                
                # Address cases where the query string contains the landing page - ...wikimediafoundation.org/w/index.php?...
                if index_str_flag:
                    try:
                        # URLs of the form ...?title=<lp_name>
                        lp_country = query_fields['title'][0].split('/')
                        landing_page = lp_country[0]
                        
                        # URLs of the form ...?county_code=<iso_code>
                        try:
                            country = query_fields['country_code'][0]
                            
                        # URLs of the form ...?title=<lp_name>/<lang>/<iso_code>
                        except:
                            if len(lp_country) == 3:
                                country = lp_country[2]
                            else:
                                country = lp_country[1]
                            
                    except:
                        landing_page = 'NONE'
                        country = Hlp.localize_IP(self._cur, ip_add)
                        
                else: # ...wikimediafoundation.org/wiki/...
                    
                    landing_path = parsed_landing_url[pathIndex].split('/')
                    landing_page = landing_path[2];
                    
                    # URLs of the form ...?county_code=<iso_code>
                    try:
                        country = query_fields['country_code'][0]
                    
                    # URLs of the form ...<path>/ <lp_name>/<lang>/<iso_code>
                    except:
                        try:
                            if len(landing_path) == 5:
                                country = landing_path[4] 
                                # source_lang = landing_path[3]                             
                            else:
                                country = landing_path[3]
                                
                        except:
                            country =  Hlp.localize_IP(self._cur, ip_add) 
                
                # If country is confused with the language use the ip
                if country == country.lower():
                    country = Hlp.localize_IP(self._cur, ip_add) 
                                
                # ensure fields exist
                try:
                    utm_source = query_fields['utm_source'][0]
                    utm_campaign = query_fields['utm_campaign'][0]
                    utm_medium = query_fields['utm_medium'][0];
    
                except KeyError:
                    utm_source = 'NONE'
                    utm_campaign = 'NONE'
                    utm_medium = 'NONE'
                    
                # INSERT INTO landing_page ('utm_source', 'utm_campaign', 'utm_medium', 'landing_page', 'page_url', 'lang', 'project', 'ip')  values ()
                try:
                    val = '(' + start_timestamp_in + ',\'' + utm_source + '\',\'' + utm_campaign + '\',\'' + utm_medium + '\',\'' + landing_page + \
                    '\',\'' + landing_url + '\',\'' + referrer_url + '\',\'' + browser + '\',\'' + source_lang + '\',\'' + country + '\',\''  \
                    + project + '\',\'' +  ip_add + '\',' + 'convert(\'' + timestamp_string + '\', datetime)' + ');'
                    
                    #print insertStmt + val
                    self._cur.execute(insertStmt_lp + val)
    
                except:
                    print "Could not insert:\n" + insertStmt_lp + val
                    pass
                    
            line = logFile.readline()
            line_count = line_count + 1 
    
            if (line_count % 1000) == 0:
                completion = float(line_count / total_lines_in_file) * 100.0
                sltl.update_table_row(type='lp_view',log_copy_time=curr_time,start_time=start,end_time=end,log_completion_pct=completion.__str__(),total_rows=line_count.__str__())

        self._close_db()
        
