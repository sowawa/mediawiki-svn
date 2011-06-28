"""

This module provides access to the threading classes for Fundraiser Analytics related tasks.  
This is configured as a set of classes that extend the python threading classes to build
containers for multi-threading where needed

"""

__author__ = "Ryan Faulkner"
__revision__ = "$Rev$"
__date__ = "June 10th, 2011"

import threading
import re
import Fundraiser_Tools.classes.DataMapper as DM

"""
    This class handles executing a log mining process in a new thread
"""
class MinerThread ( threading.Thread ):

    _fdm_ = None
    _log_name_ = None
    
    def __init__(self, log_name):
        threading.Thread.__init__(self)
        self._fdm_ = DM.FundraiserDataMapper()
        self._log_name_ = log_name
    
    def run( self ):
       self.call_mine_log()
    
    
    def call_copy_log(self, type, **kwargs):
        
        return
    
    def call_mine_log(self):
        
        dm = DM.DataMapper()
        
        """ Determine whether logs are for banner impressions or landing pages """
        
        if re.search('bannerImpressions', self._log_name_):
            print 'New Thread:  Mining banner impressions from ' + self._log_name_
            
            if dm.log_exists(self._log_name_ + '.log.gz'):
                
                try:
                    self._fdm_.mine_squid_impression_requests(self._log_name_ + '.log.gz')
                
                except Exception as e:
                    print type(e)     # the exception instance
                    print e.args      # arguments stored in .args
                    print e           # __str__ allows args to printed directly
                

            elif dm.log_exists(self._log_name_ + '.log'):
                
                try:
                    self._fdm_.mine_squid_impression_requests(self._log_name_ + '.log')
                    
                except Exception as e:
                    print type(e)     # the exception instance
                    print e.args      # arguments stored in .args
                    print e           # __str__ allows args to printed directly

                
        elif re.search('landingpages', self._log_name_):
            print 'New Thread:  Mining landing page views from ' + self._log_name_
            
            if dm.log_exists(self._log_name_ + '.log.gz'):
                
                try:
                    self._fdm_.mine_squid_landing_page_requests(self._log_name_ + '.log.gz')
                
                except Exception as e:
                        print type(e)     # the exception instance
                        print e.args      # arguments stored in .args
                        print e           # __str__ allows args to printed directly
            
            elif dm.log_exists(self._log_name_ + '.log'):
                
                try:
                    self._fdm_.mine_squid_landing_page_requests(self._log_name_ + '.log')
                
                except Exception as e:
                            print type(e)     # the exception instance
                            print e.args      # arguments stored in .args
                            print e           # __str__ allows args to printed directly
        
           