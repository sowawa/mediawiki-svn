"""

WSOR dataloader class for the MySQL slave of enwiki

"""


""" Meta """
__author__ = "Ryan Faulkner"
__revision__ = "$Rev$"
__date__ = "June 27th, 2011"


""" Import python base modules """
import sys, getopt, re, datetime, logging, MySQLdb, settings

""" Import Analytics modules """
from Fundraiser_Tools.classes.DataLoader import DataLoader


"""
    Inherits DataLoader
    
    DataLoader class for the WSOR MySQL Slave
    
"""
class WSORSlaveDataLoader(DataLoader):
    
    def __init__(self):
                
        """ Configure the logger """
        LOGGING_STREAM = sys.stderr
        logging.basicConfig(level=logging.DEBUG, stream=LOGGING_STREAM, format='%(asctime)s %(levelname)-8s %(message)s', datefmt='%b-%d %H:%M:%S')
       
        
    """
        Override init_db to connect to the slave
    """
    def init_db(self):
            
        logging.info('Attempting to establish a connection to the database.')
            
        """ Establish connection """
        try:
            self._db_ = MySQLdb.connect(host=settings.__db_server__, user=settings.__user__, db=settings.__db__, port=settings.__db_port__, passwd=settings.__pass__)
            self._db_enwiki_ = MySQLdb.connect(host=settings.__db_server__, user=settings.__user__, db=settings.__db_enwikislave__, port=settings.__db_port__, passwd=settings.__pass__)
            logging.info('Successfully connected.\n')
        except:
            logging.DEBUG('Could not establish a connection to %s @ %s : %s' % (settings.__user__, settings.__db_server__, settings.__db__))
            return
                    
        """ Create cursor """
        self._cur_ = self._db_.cursor()
        self._cur_enwiki_ = self._db_enwiki_.cursor()


    def close_db(self):
        
        self._cur_.close()
        self._db_.close()
        
        self._cur_enwiki_.close()
        self._db_enwiki_.close()


"""
    Inherits WSORSlaveDataLoader
    
    DataLoader class for vandal reversion related queries
    
"""
class VandalLoader(WSORSlaveDataLoader):
    
    
    def __init__(self, query_key):
        
        DataLoader.__init__(self)    
        logging.info('Creating VadalLoader')
    
        """ 
            DEFINE SQL queries
        """
        self._query_names_['query_test'] = 'select count(*) from revert_20110115'
        self._query_names_['query_vandal_count'] = 'select revision_id, username, user_id, sum(is_vandalism) from reverted_20110115 group by 1,2,3'
        self._query_names_['query_total_reverts'] = 'select revision_id, username, user_id, sum(is_vandalism) from reverted_20110115'
    
        """ 
            ASSIGN query 
        """
        try:
            _sql_ = self._query_names_[query_key]
        except KeyError:
            logging.debug('Query does not exist\n')
        
        
    """
        Main execution body and data handling for the loader object
    """
    def run_query(self):
        
        logging.info('Running VandalLoader')
        
        self.init_db()
        
        try:
            self._cur_.execute(self._query_names_['query_test'])
                
            """ GET THE COLUMN NAMES FROM THE QUERY RESULTS """
            self._col_names_ = list()
            for i in self._cur_.description:
                self._col_names_.append(i[0])
                
            self._results_ = self._cur_.fetchall()
            
            logging.info('Execution Complete.')
            
        except Exception as inst:
            
            logging.debug(str(type(inst)))      # the exception instance
            logging.debug(str(inst.args))       # arguments stored in .args
            logging.debug(inst.__str__())       # __str__ allows args to printed directly
            
            # self._db_.rollback()
                        
        return self._results_
    
