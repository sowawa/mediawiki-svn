
"""

This module is used to define the mapping between data sources.  The primary intention is to convert Squid log representations into 
MySQL database representations however this task may grow over time.

The DataMapper class decouples the data mapping function of from the loading and reporting using Adapter structural pattern.

"""

__author__ = "Ryan Faulkner"
__revision__ = "$Rev$"
__date__ = "April 25th, 2011"


import sys


"""

    BASE CLASS :: DataMapper
    
    Base class for interacting with DataSource.  Methods that are intended to be extended in derived classes include:
    
    METHODS:
    

"""
class DataMapper(object):

    def mine_from_log(self):
        return