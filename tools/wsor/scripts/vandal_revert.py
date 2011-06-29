
"""

WSOR script that determines how many vandals go on being vandals after there first vandal revert

"""


""" Script meta """
__author__ = "Ryan Faulkner"
__revision__ = "$Rev$"
__date__ = "June 26th, 2011"


""" Import python base modules """
import sys, getopt, re, datetime, logging, argparse
import settings

""" Modify the classpath to include local projects """
sys.path.append(settings.__project_home__)

""" Import Analytics modules """
from WSOR.scripts.classes.WSORSlaveDataLoader import VandalLoader

    
"""
    Define script usage
"""
class Usage(Exception):
    def __init__(self, msg):
        self.msg = msg

"""
    Handles the 'query' argument
"""
def query_name(input):
    
    return input

"""
    Execution body of main
"""
def main(args):
    
    """ Configure the logger """
    LOGGING_STREAM = sys.stderr
    logging.basicConfig(level=logging.DEBUG, stream=LOGGING_STREAM, format='%(asctime)s %(levelname)-8s %(message)s', datefmt='%b-%d %H:%M:%S')
       
    """ Create a DataLoader object and execute """
    vl = VandalLoader(args.query)
    data = vl.run_query()
    
    """ PROCESS the data  -- !! TODO - add data reporting here to build results !! """
    # print data
    
    vl.close_db()
    
    return 0


"""
    Call main, exit when execution is complete
    
    Argument parsing (argparse) and pass to main

""" 
if __name__ == "__main__":
    
    parser = argparse.ArgumentParser(
        description='Extracts revert data in db42.wikimedia.org:halfak and db42.wikimedia.org:enwiki.'
    )
    
    """ Allow specification of the query in CLI arguments """
    parser.add_argument(
        '-q', '--query',
        metavar="<input>",
        type=query_name, 
        help='The name of the query to be executed.',
        default=sys.stdin
    )

    
    args = parser.parse_args()

    sys.exit(main(args))
