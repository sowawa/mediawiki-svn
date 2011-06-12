

"""

This module effectively functions as a Singleton class.

Helper is a bucket for miscellaneous general methods that are needed and have yet to be grouped with other functionality. 

"""

__author__ = "Ryan Faulkner"
__revision__ = "$Rev$"
__date__ = "May 3rd, 2011"


import sys
import math
import calendar as cal
import csv
import MySQLdb


def precede_with_backslash(string, char):
    
    new_string = ''
    
    for i in string:
        if i == char:
            new_string = new_string + '\\'
            
        new_string = new_string + i
        
    return new_string



"""
    
            
"""
def convert_Decimal_list_to_float(lst):
    new_lst = list()
    
    for i in lst:
        if i == None or i == 'NULL':
            new_lst.append(0.0)
        else:
            new_lst.append(float(i))
            
    return new_lst

     
     
     
"""
    Return a specific query name given a query type
            
"""
def stringify(str_to_stringify):
        
        if str_to_stringify is None:
            return 'NULL'
        
        return '"' + str_to_stringify + '"'
    
    


""" 

!! FROM miner_help.py !!

"""



class AutoVivification(dict):
    """Implementation of perl's autovivification feature."""
    def __getitem__(self, item):
        try:
            return dict.__getitem__(self, item)
        except KeyError:
            value = self[item] = type(self)()
            return value

def read_sql(filename):

    sql_file = open(filename, 'r')

    sql_stmnt = ''
    line = sql_file.readline()
    while (line != ''):
        sql_stmnt = sql_stmnt + line
        line = sql_file.readline()
    
    sql_file.close()
    
    return sql_stmnt
    
def drange(start, stop, step):
    
    if step < 1:
        gain = math.floor(1 / step)
        lst = range(0, ((stop-start) * gain), 1)
        return [start + x * step for x in lst]
    else:
        return range(start, stop, step)
    

def mod_list(lst, modulus):
    return [x % modulus for x in lst]

    

""" Compute the difference among two timestamps """
def get_timestamps_diff(timestamp_start, timestamp_end):
    
    year_1 = int(timestamp_start[0:4])
    month_1 = int(timestamp_start[4:6])
    day_1 = int(timestamp_start[6:8])
    hr_1 = int(timestamp_start[8:10])
    min_1 = int(timestamp_start[10:12])
    
    year_2 = int(timestamp_end[0:4])
    month_2 = int(timestamp_end[4:6])
    day_2 = int(timestamp_end[6:8])
    hr_2 = int(timestamp_end[8:10])
    min_2 = int(timestamp_end[10:12])
    
    t1 = cal.datetime.datetime(year=year_1, month=month_1, day=day_1, hour=hr_1, minute=min_1,second=0)
    t2 = cal.datetime.datetime(year=year_2, month=month_2, day=day_2, hour=hr_2, minute=min_2,second=0)
    
    diff = t2 - t1
    diff = float(diff.seconds) / 3600
    
    return diff
    
""" Converts a list to a dictionary or vice versa -- INCOMPLETE MAY BE USEFUL AT SOME FUTURE POINT """
def convert_list_dict(collection):
    
    if type(collection) is dict:
        new_collection = list()
        
    elif type(collection) is list:
        new_collection = dict()
        
    else:
        print "miner_help::convert_list_dict:  Invalid type, must be a list or a dictionary."
        return 0;

    return new_collection
    
""" Given an IP localizes the country """
def localize_IP(cur, ip_string):
    
    # compute ip number
    ip_fields = ip_string.split('.')
    w = int(ip_fields[0])
    x = int(ip_fields[1])
    y = int(ip_fields[2])
    z = int(ip_fields[3])
    
    ip_num = 16777216 * w + 65536 * x + 256 * y + z;
    
    sql_stmnt = 'select country_ISO_1 from ip_country where ' + str(ip_num) + ' >= ip_from and ' + str(ip_num) + ' <= ip_to'
    
    try:
        cur.execute(sql_stmnt)
        row = cur.fetchone()
    except:
        db.rollback()
        sys.exit("Could not execute: " + sql_stmnt)
    
    try:
        country = row[0]
    except:
        country = ''
    
    return country
    
""" Load  data into the IP localization table to associate IPs with countries """
def load_IP_localization_table():
    
    """ Get db object / Create cursor  """
    # db = MySQLdb.connect(host='127.0.0.1', user='rfaulk', db='faulkner', port=3307)
    db = MySQLdb.connect(host='storage3.pmtpa.wmnet', user='rfaulk', db='faulkner')
    cur = db.cursor()
    
    # Parse CSV file 
    ipReader = csv.reader(open('./csv/IpToCountry.csv', 'rb'))
    insert_stmnt = 'INSERT INTO ip_country VALUES '
    # (ip_from,ip_to,registry,assigned,country_ISO_1,country_ISO_2,country_name) 
    header = 1
    for row in ipReader:
        # skip the csv comments
        if row[0][0] != '#':
            header = 0
        
        if not(header):
            
            for i in range(len(row)):
                pieces = row[i].split('\'')
                
                if len(pieces) > 1:
                    new_str = pieces[0]
                    
                    # remove single quotes from fields
                    for j in range(1,len(pieces)):
                        new_str = new_str + ' ' + pieces[j]
                
                    row[i] = new_str
                        
            vals = '\',\''.join(row)
            sql_stmnt = insert_stmnt + '(\'' + vals + '\')'
            
            print vals
            #cur.execute(sql_stmnt)
            try:
                cur.execute(sql_stmnt)
            except:
                db.rollback()
                sys.exit("Could not insert: " + sql_stmnt)
    
    
    # Commit to the db
    db.commit()

    # Close connection
    cur.close()
    db.close()