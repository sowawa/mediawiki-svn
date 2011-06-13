
"""

This module effectively functions as a Singleton class.

TimestampProcesser facilitates the processing of timestamps used in the CiviCRM and "faulkner" mySQL 
databases.  This includes mapping among timestamp formats and converting those formats to indexed
lists and dictionaries.

Examples of format definitions:

    format 1 - 20080101000606
    format 2 - 2008-01-01 00:06:06   
    
Examples of resolution definitions:

    resolution 0 - xxxx-xx-xx 00:00:00
    resolution 1 - xxxx-xx-xx xx:00:00
    resolution 2 - xxxx-xx-xx xx:xx:00
    resolution 3 - xxxx-xx-xx xx:xx:xx

METHODS:

    normalize_timestamps          - Takes a list of timestamps as input and converts it to a set of days, hours, or minutes counting back from 0
    timestamps_to_dict            - Convert lists into dictionaries before processing it is assumed that lists are composed of only simple types
    find_latest_date_in_list      - Find the latest time stamp in a list
    find_earliest_date_in_list    - Find the earliest time stamp in a list
    gen_date_strings              - Given a datetime object produce a timestamp a number of hours in the past and according to a particular format 
    timestamp_from_obj            - Convert datetime objects to a timestamp of a given format. 
    timestamp_to_obj              - Convert timestamp to a datetime object of a given format 
    normalize_intervals           - Inserts missing interval points into the time and metric lists
    timestamp_convert_format      - Converts from one timestamp format to another timestamp format 

"""

__author__ = "Ryan Faulkner"
__revision__ = "$Rev$"
__date__ = "April 8th, 2011"


import sys
import datetime
import calendar as cal
import math
import Fundraiser_Tools.classes.Helper as mh

"""
    Get timestamps for interval
    
    INPUT:
         start_time_obj       - a datetime object indicating the start of the interval
"""
def timestamps_for_interval(start_time_obj, timestamp_format, **kwargs):
   
   for key in kwargs:
       
       if key == 'minutes':
           end_time_obj = start_time_obj + datetime.timedelta(minutes=kwargs[key])
       elif key == 'hours':
            end_time_obj = start_time_obj + datetime.timedelta(hours=kwargs[key])
    
       start_timestamp = timestamp_from_obj(start_time_obj, timestamp_format, 3)
       end_timestamp = timestamp_from_obj(end_time_obj, timestamp_format, 3)
        
       return [start_timestamp, end_timestamp]
   
   
"""
 
     Takes a list of timestamps as input and converts it to a set of days, hours, or minutes counting back from 0
     
     INPUT:
         time_lists       - a list of datetime objects
         count_back       - indicate whether the list counts back from the end
         time_unit        - an integer indicating what unit to measure time in (0 = day, 1 = hour, 2 = minute)
     
     RETURN: 
         time_norm        - a dictionary of normalized times
         
"""
def normalize_timestamps(time_lists, count_back, time_unit):
     
     time_lists, isList = timestamps_to_dict(time_lists)
     
     """ Depending on args set the start date """
     if count_back:
         start_date_obj = find_latest_date_in_list(time_lists)
     else:
         start_date_obj = find_earliest_date_in_list(time_lists)
     
     start_month = start_date_obj.month
     start_day = start_date_obj.day
     start_hr  = start_date_obj.hour
     start_mte = start_date_obj.minute
     
     length_of_month = cal.mdays[start_month]
     
     # Normalize dates
     time_norm = mh.AutoVivification()
     for key in time_lists.keys():
         for date_obj in time_lists[key]:
             
             month = date_obj.month
             day = date_obj.day
             hr = date_obj.hour
             mte = date_obj.minute
             
             if time_unit == 0:
                 elem = (day - start_day)
             elif time_unit == 1:
                 elem = (day - start_day) * 24 + (hr - start_hr)
             elif time_unit == 2:
                 elem = (day - start_day) * 24 * 60 + (hr - start_hr) * 60 + (mte - start_mte)
             elif time_unit == 3:
                 elem = (month - start_month) * 24 * 60 * length_of_month + (day - start_day) * 24 * 60 + (hr - start_hr) * 60 + (mte - start_mte)
             try: 
                 time_norm[key].append(elem)
             except:
                 time_norm[key] = list()
                 time_norm[key].append(elem)
     
     """ If the original argument was a list put it back in that form """
     if isList:
         time_norm = time_norm[key]
         
     return time_norm
     
 
"""

    HELPER METHOD for normalize_timestamps.  Convert lists into dictionaries before processing it is assumed that lists 
    are composed of only simple types
    
    INPUT:
        time_lists    - a list of datetime objects
        
    RETURN: 
        time_lists    - dictionary with a single key 'key' that stores the list
        isList        - a dictionary of normalized times
        
"""
def timestamps_to_dict(time_lists):
    
    isList = 0
    if type(time_lists) is list:
        isList = 1
        
        old_list = time_lists
        time_lists = mh.AutoVivification()
        
        key = 'key'
        time_lists[key] = list()
        
        for i in range(len(old_list)):
            time_lists[key].append(old_list[i])

    return [time_lists, isList]

       
"""

    HELPER METHOD for normalize_timestamps.  Find the latest time stamp in a list
    
    INPUT:
        time_lists       - a list of datetime objects
        
    RETURN: 
        date_max        - datetime object of the latest date in the list
        
"""
def find_latest_date_in_list(time_lists):
    
    date_max = datetime.datetime(1000,1,1,0,0,0)
    
    for key in time_lists.keys():
        for date_obj in time_lists[key]:
            if date_int > date_min:
                date_min = date_obj
                
    return date_max

"""

    HELPER METHOD for normalize_timestamps.  Find the earliest timestamp in a list
    
    INPUT:
        time_lists       - a list of datetime objects
        
    RETURN: 
        date_min        - datetime object of the earliest date in the list
        
"""
def find_earliest_date_in_list(time_lists):
    
    date_min = datetime.datetime(3000,1,1,0,0,0)
    
    for key in time_lists.keys():
        for date_obj in time_lists[key]:
            if date_obj < date_min:
                date_min = date_obj
                
    return date_min


"""

    Given a datetime object produce a timestamp a number of hours in the past and according to a particular format
    
    format 1 - 20080101000606
    format 2 - 2008-01-01 00:06:06
    
    INPUT:
    
        now              - datetime object
        hours_back       - the amount of time the 
        format           - the format of the returned timestamp strings 
        resolution       - the resolution detail of the timestamp (e.g. down to the minute, down to the hour, ...)
    
    
    RETURN:
         start_time     - formatted datetime string
         end_time       - formatted datetime string
    
"""
def gen_date_strings(time_ref, hours_back, format, resolution):
    
    delta = datetime.timedelta(hours=-hours_back)

    time_obj = time_ref + delta
    time_ref = time_ref + datetime.timedelta(hours=-1) # Move an hour back to terminate at 55 minute
    
    # Cast the start and end time strings in the proper format
    start_time = timestamp_from_obj(time_obj, format, resolution)
    end_time = timestamp_from_obj(time_ref, format, resolution)

    return [start_time, end_time]



"""

    Convert datetime objects to a timestamp of a given format.  HELPER METHOD for gen_date_strings.
        
    INPUT:
    
        time_obj         - datetime object
        format           - the format of the returned timestamp strings 
        resolution       - the resolution detail of the timestamp (e.g. down to the minute, down to the hour, ...)
    
    
    RETURN:
         start_time     - formatted datetime string
         end_time       - formatted datetime string

"""
def timestamp_from_obj(time_obj, format, resolution):
    
    if time_obj.month < 10:
        month = '0' + str(time_obj.month)
    else:
        month = str(time_obj.month)

    if time_obj.day < 10:
        day = '0' + str(time_obj.day)
    else:
        day = str(time_obj.day)

    if time_obj.hour < 10:
        hour = '0' + str(time_obj.hour)
    else:
        hour = str(time_obj.hour)
        
    if time_obj.minute < 10:
        minute = '0' + str(time_obj.minute)
    else:
        minute = str(time_obj.minute)
        
    if time_obj.second < 10:
        second = '0' + str(time_obj.second)
    else:
        second = str(time_obj.second)
        
    # Cast the start and end time strings in the proper format
    if format == 1:
        
        if resolution == 0:
            timestamp = str(time_obj.year) + month + day + '000000'
        elif resolution == 1:
            timestamp = str(time_obj.year) + month + day + hour + '0000'
        elif resolution == 2:
            timestamp = str(time_obj.year) + month + day + hour + minute + '00'
        elif resolution == 3:
            timestamp = str(time_obj.year) + month + day + hour + minute + second
    
    elif format == 2:
        
        if resolution == 0:
            timestamp = str(time_obj.year) + '-' +  month + '-' +  day + ' ' +  '00:00:00'
        elif resolution == 1:
            timestamp = str(time_obj.year) + '-' +  month + '-' +  day + ' ' +  hour + ':00:00'
        elif resolution == 2:
            timestamp = str(time_obj.year) + '-' +  month + '-' +  day + ' ' +  hour + ':' + minute + ':00'
        elif resolution == 3:
            timestamp = str(time_obj.year) + '-' +  month + '-' +  day + ' ' +  hour + ':' + minute + ':' + second
            
    return timestamp


"""

    Convert timestamp to a datetime object of a given format
    
    INPUT:
    
        timestamp        - timestamp string
        format           - the format of the returned timestamp strings 
    
    
    RETURN:
         time_obj     - datetime conversion of timestamp string
     
"""
def timestamp_to_obj(timestamp, format):
    
    if format == 1:
        time_obj = datetime.datetime(int(timestamp[0:4]), int(timestamp[4:6]), int(timestamp[6:8]), \
                                    int(timestamp[8:10]), int(timestamp[10:12]), int(timestamp[12:14]))
        
    elif format == 2:
        time_obj = datetime.datetime(int(timestamp[0:4]), int(timestamp[5:7]), int(timestamp[8:10]), \
                                    int(timestamp[11:13]), int(timestamp[14:16]), int(timestamp[17:19]))

    return time_obj


"""

    Inserts missing interval points into the time and metric lists
    
    Assumptions: 
        _metrics_ and _times_ are lists of the same length
        there must be a data point at each interval 
        Some data points may be missed
        where there is no metric data the metric takes on the value 0.0
    
    e.g. when _interval_ = 10
    times = [0 10 30 50], metrics = [1 1 1 1] ==> [0 10 30 40 50], [1 1 0 1 0 1]    
    
    INPUT:
    
        times           - 
        metrics         -  
        interval        -
    
    RETURN:
         new_times     - 
         new_metrics   -
    
"""
def normalize_intervals(times, metrics, interval):
    
    current_time = 0.0
    index = 0
    iterations = 0
    max_elems = math.ceil((times[-1] - times[0]) / interval) # there should be no more elements in the list than this
    
    new_times = list()
    new_metrics = list()
    
    """ Iterate through the time list """
    while index < len(times):
        
        """ TEMPORARY SOLUTION: break out of the loop if more than the maximum number of elements is reached """
        if iterations > max_elems:
            break;
        
        new_times.append(current_time)
        
        """ If the current time is not in the current list then add it and a metric value of 0.0
            otherwise add the existing elements to the new lists """
        if current_time != times[index]:
            new_metrics.append(0.0)
        
        else:
            new_metrics.append(metrics[index])
            index = index + 1
        
        current_time = current_time + interval
        
        iterations = iterations + 1
        
    return [new_times, new_metrics]

"""

    Converts from one timestamp format to another timestamp format
        
    format 1 - 20080101000606
    format 2 - 2008-01-01 00:06:06    
        
    INPUT:
    
        ts           - timestamp string
        format_from  - input format
        format_to    - output format
    
    RETURN:
    
         new_timestamp     - new timestamp string
     

"""
def timestamp_convert_format(ts, format_from, format_to):
    
    if format_from == 1:
        
        if format_to == 2:
            new_timestamp = ts[0:4] + '-' + ts[4:6] + '-' + ts[6:8] + ' ' + ts[8:10] + ':' + ts[10:12] + ':' + ts[12:14]
            
    elif format_from == 2:
        if format_to == 1:
            new_timestamp = ts[0:4] + ts[5:7] + ts[8:10] + ts[11:13] + ts[14:16] + ts[15:17]
            
    return new_timestamp
 
"""

    THIS METHOD IS CURRENTLY COUPLED WITH HYPOTHESIS TEST
    
    This method takes a start time and endtime and interval length and produces
    a list of timestamps corresponding to each time spaced by length 'interval'
    between the start time and the end time inclusive
    
    INPUT:
        num_samples      - number of samples per test interval
        interval         - intervals at which samples are drawn within the range, units = minutes
        start_time       - start timestamp 'yyyymmddhhmmss'
        end_time         - end timestamp 'yyyymmddhhmmss'
        format           - !! CURRENTLY UNUSED !! specifies timestamp format
        
    RETURN:
        
        times            - list of timestamps for each sample
        time_indices     - list of indices counting from zero marking the indices for reporting test interval parameters

"""
def get_time_lists(start_time, end_time, interval, num_samples, format):

    """ range must be divisible by interval - convert to hours """
    range = float(interval * num_samples) / 60
    
    """ Compose times """
    start_datetime = datetime.datetime(int(start_time[0:4]), int(start_time[4:6]), int(start_time[6:8]), int(start_time[8:10]), int(start_time[10:12]), int(start_time[12:14]))
    end_datetime = datetime.datetime(int(end_time[0:4]), int(end_time[4:6]), int(end_time[6:8]), int(end_time[8:10]), int(end_time[10:12]), int(end_time[12:14]))

    """ current timestamp and hour index """
    curr_datetime = start_datetime
    curr_timestamp = start_time
    curr_hour_index = 0.0
    
    """ lists to store timestamps and indices """
    times = []
    time_indices = []

    sample_count = 1
    
    """ build a list of timestamps and time indices for plotting increment the time """
    while curr_datetime < end_datetime:
        
        """ for timestamp formatting """
        month_str_fill = ''
        day_str_fill = ''
        hour_str_fill = ''
        minute_str_fill = ''
        if curr_datetime.month < 10:
            month_str_fill = '0'
        if curr_datetime.day < 10:
            day_str_fill = '0'
        if curr_datetime.hour < 10:
            hour_str_fill = '0'
        if curr_datetime.minute < 10:
            minute_str_fill = '0'
        
        curr_timestamp = str(curr_datetime.year) + month_str_fill + str(curr_datetime.month) + day_str_fill + str(curr_datetime.day) + hour_str_fill+ str(curr_datetime.hour) + minute_str_fill+ str(curr_datetime.minute) + '00'
        times.append(curr_timestamp)
        
        """ increment curr_hour_index if the """
        if sample_count == num_samples: 
            
            time_indices.append(curr_hour_index + range / 2)
            curr_hour_index = curr_hour_index + range
            sample_count = 1
        else:
            sample_count = sample_count + 1 
                
            
        """ increment the time by interval minutes """
        td = datetime.timedelta(minutes=interval)
        curr_datetime = curr_datetime + td
    
    """ append the last items onto time lists """
    times.append(end_time)
        
    return [times, time_indices]



def get_timestamps_with_interval(logFileName, interval):
    
    log_start, log_end = get_timestamps(logFileName)
    
    end_obj = timestamp_to_obj(log_end, 1)
    start_obj = end_obj + datetime.timedelta(minutes=-interval)
    
    start_timestamp = timestamp_from_obj(start_obj, 1, 2)
    end_timestamp = timestamp_from_obj(end_obj, 1, 2)
    
    return [start_timestamp, end_timestamp]
    
    
""" Extract a timestamp from the filename """
def get_timestamps(logFileName):
    
    fname_parts = logFileName.split('-')

    year = int(fname_parts[1])
    month = int(fname_parts[2])
    day = int(fname_parts[3])
    hour = int(fname_parts[4][0:2])
    minute = int(fname_parts[6][0:2])
    
    # Is this an afternoon log?
    afternoon = (fname_parts[4][2:4] == 'PM') 
     
    # Adjust the hour as necessary if == 12AM or *PM
    if afternoon and hour < 12:
        hour = hour + 12
        
    if not(afternoon) and hour == 12:
        hour = 0

    prev_hr = getPrevHour(year, month, day, hour)
    
    str_month = '0' + str(month) if month < 10 else str(month)
    str_day = '0' + str(day) if day < 10 else str(day)
    str_hour = '0' + str(hour) if hour < 10 else str(hour)
    str_minute = '0' + str(minute) if minute < 10 else str(minute)
    
    prev_month = prev_hr[1] 
    prev_day = prev_hr[2]
    prev_hour = prev_hr[3]
    str_prev_month = '0' + str(prev_month) if prev_month < 10 else str(prev_month)
    str_prev_day = '0' + str(prev_day) if prev_day < 10 else str(prev_day)
    str_prev_hour = '0' + str(prev_hour) if prev_hour < 10 else str(prev_hour)
    
    log_end = str(year) + str_month + str_day + str_hour + str_minute + '00'
    log_start = str(prev_hr[0]) + str_prev_month + str_prev_day + str_prev_hour + '5500' 
    
    #log_start = str(year) + str(month) + str(day) + str(hour) + '5500'
    #log_end = str(prev_hr[0]) + str(prev_hr[1]) + str(prev_hr[2]) + str(prev_hr[3]) + '5500' 

    return [log_start, log_end]


""" Determines the following hour based on the precise date to the hour """
def getNextHour(year, month, day, hour):

    lastDayofMonth = cal.monthrange(year,month)[1]

    next_year = year
    next_month = month
    next_day = day
    next_hour = hour + 1

    if hour == 23:
        next_hour = 0
        if day == lastDayofMonth:
            next_day = 1
            if month == 12:
                next_month = 1
                next_year = year + 1

    return [next_year, next_month, next_day, next_hour]

""" Determines the previous hour based on the precise date to the hour """
def getPrevHour(year, month, day, hour):
    
    if month == 1:
        last_year = year - 1
        last_month = 12
    else:
        last_year = year
        last_month = month - 1
        
    lastDayofPrevMonth = cal.monthrange(year,last_month)[1]
        
    prev_year = year
    prev_month = month
    prev_day = day
    prev_hour = hour - 1

    if prev_hour == -1:
        prev_hour = 23
        if day == 1:
            prev_day = lastDayofPrevMonth
            prev_month = last_month
            prev_year = last_year
        else:
            prev_day = day - 1
            
    return [prev_year, prev_month, prev_day, prev_hour]
