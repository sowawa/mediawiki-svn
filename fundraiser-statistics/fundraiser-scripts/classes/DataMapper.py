
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
        
        copy_logs         - copies logs from source to destination for processing for the current hour
        log_exists        - determines if a log exists in the squid local folder based on name
        get_list_of_logs  - returns a list of logs in the squid local folder 

"""
class DataMapper(object):
    
    """
        Copies mining logs from remote site for a given hour
    """
    def copy_logs(self, type, **kwargs):
        
        if type == FDH._TESTTYPE_BANNER_:
            # prefix = 'bannerImpressions-2011-06-01-11PM*'
            prefix = 'bannerImpressions'
        elif type == FDH._TESTTYPE_LP_:
            # prefix = 'landingpages-2011-06-01-11PM*'
            prefix = 'landingpages'
        
        filename = prefix
        
        now = datetime.datetime.now()
        year = str(now.year)
        month = str(now.month)
        day = str(now.day)
        hour = now.hour
        
        """ If specified change the timestamp  Assume each arg is a string """
        for key in kwargs:
            
            if key == 'year':
                year = kwargs[key]
            elif key == 'month':                
                month = kwargs[key]
            elif key == 'day':
                day = kwargs[key]
            elif key == 'hour':
                hour = int(kwargs[key])
        
        if int(month) < 10:
            month = '0' + str(int(month))
        if int(day) < 10:
            day = '0' + str(int(day))
            
        """ adjust the hour based on time of day """ 
        if hour > 12:
            hour = str(hour-12)
            day_part = 'PM*'
        else:
            hour = str(hour)
            day_part = 'AM*'
        
        if int(hour) < 10:
            hour = '0' + str(int(hour))
            
        filename = filename + '-' + year + '-' + month + '-' + day + '-' + hour + day_part
        #filename = 'bannerImpressions-2011-05-27-11PM--25*'
        # filename = prefix + '-2011-06-01-11PM*' 
        cmd = 'sftp ' + projSet.__user__ + '@' + projSet.__squid_log_server__ + ':' + projSet.__squid_log_home__ + filename  + ' ' + projSet.__squid_log_local_home__
        
        os.system(cmd)
        
        return filename

    """
        Return a listing of all of the squid logs
    """
    def get_list_of_logs(self):
        
        files = os.listdir(projSet.__squid_log_local_home__)
        files.sort()
        
        new_files = list()
        for f in files:
            new_files.append(f.split('.')[0])
            
        return new_files[1:]
        
    """
        Determine if a squid log exists
    """
    def log_exists(self, log_name):
        
        files = os.listdir(projSet.__squid_log_local_home__)
        files.sort()
        
        new_files = list()
        for f in files:
            if f == log_name:
                return True
            
        return False
"""

    CLASS :: FundraiserDataMapper
    
    Data mapper specific to the Wikimedia Fundraiser
    
    METHODS:
        
        mine_squid_impression_requests        - mining banner impressions from squid logs
        mine_squid_landing_page_requests      - mining landing page views from squid logs

"""
class FundraiserDataMapper(DataMapper):
    
    _db_ = None
    _cur_ = None
    
    _impression_table_name_ = 'banner_impressions'
    _landing_page_table_name_ = 'landing_page_requests'
    
    _BANNER_REQUEST_ = 0
    _LP_REQUEST_ = 1
    
    _BANNER_FIELDS_ =  ' (start_timestamp, utm_source, referrer, country, lang, counts, on_minute) '
    _LP_FIELDS_ = ' (start_timestamp, utm_source, utm_campaign, utm_medium, landing_page, page_url, referrer_url, browser, lang, country, project, ip, request_time) '
    
    
    """ !! MODIFY -- use dataloaders! """ 
    def _init_db(self):     
        self._db_ = MySQLdb.connect(host='127.0.0.1', user='rfaulk', db='faulkner', port=3307)
        self._cur_ = self._db_.cursor()
        
    """ !! MODIFY -- use dataloaders! """ 
    def _close_db(self):
        self._cur_.close()
        self._db_.close()
        
        
    """
        Remove banner impression or landing page squid records from tables before loading 
    """
    def _clear_squid_records(self, start, request_type):
        
        
        """ Ensure that the range is correct; otherwise abort - critical that outside records are not deleted """
        timestamp = TP.timestamp_convert_format(start,1,2)
        
        if request_type == self._BANNER_REQUEST_:
            deleteStmnt = 'delete from ' + self._impression_table_name_ + ' where start_timestamp = \'' + timestamp + '\';'
        elif request_type == self._LP_REQUEST_:
            deleteStmnt = 'delete from ' + self._landing_page_table_name_ + ' where start_timestamp = \'' + timestamp + '\';'
        
        try:
            self._cur_.execute(deleteStmnt)
            print >> sys.stdout, "Executed delete: " + deleteStmnt
        except:
            print >> sys.stderr, "Could not execute delete:\n" + deleteStmnt + "\nResuming insert ..."
            pass

        
        
    """
        Given the name of a log file extract the squid requests corresponding to banner impressions.
        
        Squid logs can be found under hume:/a/static/uncompressed/udplogs.  A sample request is documented in the source below.
        
        @param logFileName: the full name of the logfile.  The local squid log folder is stored in web_reporting/settings.py
        @type logFileName: string
        
    """
    def mine_squid_impression_requests(self, logFileName):
        
        self._init_db()

        sltl = DL.SquidLogTableLoader()
        itl = DL.ImpressionTableLoader()
        
        """ Retrieve the log timestamp from the filename """
        time_stamps = TP.get_timestamps_with_interval(logFileName, 15)
        
        start = time_stamps[0]
        end = time_stamps[1]
        start_timestamp_in = "convert(\'" + start + "\', datetime)"
        curr_time = TP.timestamp_from_obj(datetime.datetime.now(),1,3)
        
        
        """ retrieve the start time of the log """
        start = self.get_first_timestamp_from_log(logFileName)
        
        """ Initialization - open the file """
        logFile, total_lines_in_file = self.open_logfile(logFileName)
        
        
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
        
        """
            PROCESS LOG FILE
            ================
            
            Sample Request:
            
            line =
            "sq63.wikimedia.org 757675855 2011-06-01T23:00:07.612 0 187.57.227.121 TCP_MEM_HIT/200 1790 GET \
            http://meta.wikimedia.org/w/index.php?title=Special:BannerLoader&banner=B20110601_JWJN001_BR&userlang=pt&db=ptwiki&sitename=Wikip%C3%A9dia&country=BR NONE/- text/javascript http://pt.wikipedia.org/wiki/Modo_e_tempo_verbal \
            - Mozilla/5.0%20(Windows%20NT%206.1)%20AppleWebKit/534.24%20(KHTML,%20like%20Gecko)%20Chrome/11.0.696.71%20Safari/534.24"

        """
        
        line_count = 0
        line = logFile.readline()
        while (line != ''):
    
            lineArgs = line.split()
            
            """ 
                Parse the Timestamp:
                
                Sample timestamp:
                    timestamp = "2011-06-01T23:00:07.612"
            """
            
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
            
            """ Logic used to deal with logs that aren't sequential """
    
            if minute == 0 and not(hr_change) and not(clamp):
                min_log = -1
    
            if minute == 1:
                hr_change = 0
                clamp = 1
    
            """ 
                Parse the URL:
                
                Sample url:
                    url = "http://meta.wikimedia.org/w/index.php?title=Special:BannerLoader&banner=B20110601_JWJN001_BR&userlang=pt&db=ptwiki&sitename=Wikip%C3%A9dia&country=BR"
            """
    
            try:
                url = lineArgs[8]
            except IndexError:
                url = 'Unavailable'
    
            parsedUrl = up.urlparse(url)
            query = parsedUrl[queryIndex]
            queryBits = cgi.parse_qs(query)
    
            """ Extract - project, banner, language, & country data from the url """
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
    
            """ Group banner impression counts based on (banner, country, project, language) """
            try:
                counts[banner][country][project][lang] = counts[banner][country][project][lang] + 1
            except TypeError:
                counts[banner][country][project][lang] = 1
    

            """ 
                Break out impression data by minute.  This conditional detects when a request with a previously unseen minute in the timestamp appears.
                
            """
            if min_log < minute and not(hr_change):
    
                if minute == 0:
                    hr_change = 1
    
                min_log = minute
                time_stamp_in = "convert(\'" + date_fields[0] + date_fields[1] + date_fields[2] + time_fields[0] + time_fields[1]  + "00\', datetime)"

    
                """ Run through the counts dictionary and insert a row into the banner impressions table for each entry """
    
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
                                    #print insertStmt + val
                                    self._cur_.execute(insertStmt + val)
                                except:
                                    self._db_.rollback()
                                    sys.exit("Database Interface Exception - Could not execute statement:\n" + insertStmt + val)
    
                # Re-initialize counts
                counts = Hlp.AutoVivification()

            line = logFile.readline()
            line_count = line_count + 1
            
            """ Log Miner Logging - Update the squid_log_record table """
            if line_count % 10000 == 0 or line_count == total_lines_in_file:
                completion = float(line_count / total_lines_in_file) * 100.0
                sltl.update_table_row(type='banner_impression',log_copy_time=curr_time,start_time=start,end_time=end,log_completion_pct=completion.__str__(),total_rows=line_count.__str__())

        
        logFile.close()
        self._close_db()



    """
        Given the name of a log file Extract the squid requests corresponding to landing page views 
        
        Squid logs can be found under hume:/a/static/uncompressed/udplogs.  A sample request is documented in the source below.
        
        @param logFileName: the full name of the logfile.  The local squid log folder is stored in web_reporting/settings.py
        @type logFileName: string

    """
    def mine_squid_landing_page_requests(self,  logFileName):

        self._init_db()
        
        sltl = DL.SquidLogTableLoader()
        lptl = DL.LandingPageTableLoader()
        
        
        """ Retrieve the log timestamp from the filename """
        #time_stamps = Hlp.get_timestamps(logFileName)
        time_stamps = TP.get_timestamps_with_interval(logFileName, 15)
        
        start = time_stamps[0]
        end = time_stamps[1]
        start_timestamp_in = "convert(\'" + start + "\', datetime)"
        curr_time = TP.timestamp_from_obj(datetime.datetime.now(),1,3)
        
        """ retrieve the start time of the log """
        start = self.get_first_timestamp_from_log(logFileName)
        
        """ Initialization - open the file """
        logFile, total_lines_in_file = self.open_logfile(logFileName)
    
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
        
        """
            PROCESS REQUESTS FROM FILE
            ==========================
            
            Sample request:
            
            line = 
                "sq63.wikimedia.org 757671483 2011-06-01T23:00:01.343 93 98.230.113.246 TCP_MISS/200 10201 GET \
                http://wikimediafoundation.org/w/index.php?title=WMFJA085/en/US&utm_source=donate&utm_medium=sidebar&utm_campaign=20101204SB002&country_code=US&referrer=http%3A%2F%2Fen.wikipedia.org%2Fwiki%2FFile%3AMurphy_High_School.jpg CARP/208.80.152.83 text/html http://en.wikipedia.org/wiki/File:Murphy_High_School.jpg \
                - Mozilla/4.0%20(compatible;%20MSIE%208.0;%20Windows%20NT%206.1;%20WOW64;%20Trident/4.0;%20FunWebProducts;%20GTB6.6;%20SLCC2;%20.NET%20CLR%202.0.50727;%20.NET%20CLR%203.5.30729;%20.NET%20CLR%203.0.30729;%20Media%20Center%20PC%206.0;%20HPDTDF;%20.NET4.0C)"
        """
        line = logFile.readline()
        while (line != ''):
            lineArgs = line.split()
    
            """ Get the IP Address of the donor """
            ip_add = lineArgs[4];
    
    
            #  SELECT CAST('20070529 00:00:00' AS datetime)

            """ 
                Parse the Timestamp:
                
                Sample timestamp:
                    timestamp = "2011-06-01T23:00:07.612"
            """

    
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
    
            """ 
                Process referrer URL
                =================== 
                
                Sample referrer:
                    referrer_url = http://en.wikipedia.org/wiki/File:Murphy_High_School.jpg
            """
                
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
                
                """ If the hostname of the form '<lang>.<project>.org' """
                if ( len( hostname[0] ) <= 2 ) :
                    # referrer_path = parsed_referrer_url[pathIndex].split('/')
                    project = hostname[0]                  # wikimediafoundation.org
                    source_lang = hostname[0]
                else:
                    try:
                        """ species.wikimedia vs en.wikinews """
                        project = hostname[0] if ( hostname[1] == 'wikimedia' ) else hostname[1]    
                        """ pl.wikipedia vs commons.wikimedia """
                        source_lang = hostname[0] if ( len(hostname[1]) < 5 ) else 'en'             
                    except:
                        project = 'wikipedia'   """ default project to 'wikipedia' """
                        source_lang = 'en'      """ default lang to english """
            
            """
                Process User agent string
                ========================
                
                sample user agent string:
                    user_agent_string = Mozilla/4.0%20(compatible;%20MSIE%208.0;%20Windows%20NT%206.1;%20WOW64;%20Trident/4.0;%20FunWebProducts;%20GTB6.6;%20SLCC2;%20.NET%20CLR%202.0.50727;%20.NET%20CLR%203.5.30729;%20.NET%20CLR%203.0.30729;%20Media%20Center%20PC%206.0;%20HPDTDF;%20.NET4.0C)
                
            """
            
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
    
            """
                 Process landing URL
                 ===================
                 
                 sample landing url
                     landing_url = "http://wikimediafoundation.org/w/index.php?title=WMFJA085/en/US&utm_source=donate&utm_medium=sidebar&utm_campaign=20101204SB002&country_code=US&referrer=http%3A%2F%2Fen.wikipedia.org%2Fwiki%2FFile%3AMurphy_High_School.jpg"
                     landing_url = "http://wikimediafoundation.org/wiki/WMFJA1/ru"
            """
            
            try:
                landing_url = lineArgs[8]
            except IndexError:
                landing_url = 'Unavailable'
                
            hostIndex = 1
            queryIndex = 4
            pathIndex = 2
            
            parsed_landing_url = up.urlparse(landing_url)
            query_fields = cgi.parse_qs(parsed_landing_url[queryIndex]) # Get the banner name and lang
            path_pieces = parsed_landing_url[pathIndex].split('/')

            #print ''
            #print landing_url
            include_request, index_str_flag = self.evaluate_landing_url(landing_url, parsed_landing_url, query_fields, path_pieces)
            #print [include_request, index_str_flag]
            
            
            if include_request:
                
                """ Address cases where the query string contains the landing page - ...wikimediafoundation.org/w/index.php?... """
                if index_str_flag:
                    try:
                        
                        """ URLs of the form ...?title=<lp_name> """
                        lp_country = query_fields['title'][0].split('/')
                        landing_page = lp_country[0]
                        
                        """ URLs of the form ...?county_code=<iso_code> """
                        try:
                            country = query_fields['country_code'][0]
                        except:
                            """ URLs of the form ...?title=<lp_name>/<lang>/<iso_code> """
                            if len(lp_country) == 3:
                                country = lp_country[2]
                            else:
                                country = lp_country[1]
                            
                    except:
                        landing_page = 'NONE'
                        country = Hlp.localize_IP(self._cur_, ip_add)
                        
                else: 
                    """ Address cases where the query string does not contain the landing page - ...wikimediafoundation.org/wiki/... """
                    parsed_landing_url = up.urlparse(landing_url)
                    query_fields = cgi.parse_qs(parsed_landing_url[queryIndex]) # Get the banner name and lang
                    
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
                            country =  Hlp.localize_IP(self._cur_, ip_add) 
                
                # If country is confused with the language use the ip
                if country == country.lower():
                    country = Hlp.localize_IP(self._cur_, ip_add) 
                                
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
                    self._cur_.execute(insertStmt_lp + val)
                    
                except:
                    print "Could not insert:\n" + insertStmt_lp + val
                    pass
                    
            line = logFile.readline()
            line_count = line_count + 1 
    
            """ Log Miner Logging - Update the squid_log_record table """
            if (line_count % 1000) == 0 or line_count == total_lines_in_file:
                completion = float(line_count / total_lines_in_file) * 100.0
                sltl.update_table_row(type='lp_view',log_copy_time=curr_time,start_time=start,end_time=end,log_completion_pct=completion.__str__(),total_rows=line_count.__str__())

        self._close_db()
        

    """
        Looks into the logfile and pull the timestamp of the first request
        
        @param logFileName: the full name of the logfile.  The local squid log folder is stored in web_reporting/settings.py
        @type logFileName: string
        
    """
    def get_first_timestamp_from_log(self, logFileName):
        
        logFile = self.open_logfile(logFileName)[0]
        
        """ Scan through the log file """
        line = logFile.readline()
        while (line != ''):
            lineArgs = line.split()
            
            """ Try to extract Timestamp data """
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
                
            """ Break the loop once we have a timestamp """
            first_time_stamp = date_fields[0] + date_fields[1] + date_fields[2] + time_fields[0] + time_fields[1]  + '00'
            break
                
        logFile.close()
        
        return first_time_stamp
    


    
    """
        Opens the logfile and counts the total number of lines
        
        @param logFileName: the full name of the logfile.  The local squid log folder is stored in web_reporting/settings.py
        @type logFileName: string
        
    """
    def open_logfile(self, logFileName):        
        
        if (re.search('\.gz', logFileName)):
            logFile = gzip.open(projSet.__squid_log_local_home__ + logFileName, 'r')
            total_lines_in_file = float(commands.getstatusoutput('zgrep -c "" ' + projSet.__squid_log_local_home__ + logFileName)[1])
        else:
            logFile = open(projSet.__squid_log_local_home__ + logFileName, 'r')
            total_lines_in_file = float(commands.getstatusoutput('grep -c "" ' + projSet.__squid_log_local_home__ + logFileName)[1])

        return logFile, total_lines_in_file



    """
        Parses the landing url and determines if its valid
    """
    def evaluate_landing_url(self, landing_url, parsed_landing_url, query_fields, path_pieces):        
        
        hostIndex = 1
        queryIndex = 4
        pathIndex = 2

        """ 
            Filter the landing URLs
        
             /wikimediafoundation.org/wiki/WMF/
             /wikimediafoundation.org/w/index.php?title=WMF/ 

            Evaluate conditions which determine acceptance of request based on the landing url 
        """
        try: 
            c1 = re.search('WMF', path_pieces[2] ) != None or re.search('Junetesting001', path_pieces[2] ) != None 
            c2 = re.search('Hear_from_Kartika', path_pieces[2]) != None
            
            cond1 = parsed_landing_url[hostIndex] == 'wikimediafoundation.org' and path_pieces[1] == 'wiki' and (c1 or c2)

            c1 = re.search('index.php', path_pieces[2] )  != None
            index_str_flag = c1
            
            try:
                c2 = re.search('WMF', query_fields['title'][0] ) != None or re.search('L2011', query_fields['title'][0] ) != None  or re.search('L11', query_fields['title'][0] ) != None 
            except KeyError:
                c2 = 0
            cond2 = (parsed_landing_url[hostIndex] == 'wikimediafoundation.org' and path_pieces[1] == 'w' and c1 and c2)
                            
            regexp_res = re.search('Special:LandingCheck',landing_url)
            cond3 = (regexp_res == None)
            
            return [(cond1 or cond2) and cond3, index_str_flag]
             
        except Exception as e: 
            #print type(e)     # the exception instance
            #print e.args      # arguments stored in .args
            #print e           # __str__ allows args to printed directly
            
            return [False, False]

    