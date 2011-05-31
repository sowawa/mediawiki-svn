from django.shortcuts import render_to_response
from django.http import Http404
from django.shortcuts import render_to_response, get_object_or_404
from django.template import RequestContext
from django.http import HttpResponseRedirect, HttpResponse
from django.core.urlresolvers import reverse

import sys
import os
import datetime

import Fundraiser_Tools.classes.Helper as Hlp
import Fundraiser_Tools.classes.DataReporting as DR
import Fundraiser_Tools.classes.DataLoader as DL
import Fundraiser_Tools.classes.FundraiserDataHandler as FDH
import Fundraiser_Tools.classes.TimestampProcessor as TP
import Fundraiser_Tools.settings as projSet
import operator


"""
    Index page for finding the latest camapigns.  Displays a list of recent campaigns with more than k donations over the last n hours. 
    
"""
def index(request):
    
    sltl = DL.SquidLogTableLoader()
    
    # Show the squid log table
    squid_table = sltl.get_all_rows_unique_start_time()
    
    # Show the latest log that has been or is loading and its progress
    completion_rate = sltl.get_completion_rate_of_latest_log()
    
    return render_to_response('LML/index.html', {'squid_table' : squid_table, 'completion_rate' : completion_rate},  context_instance=RequestContext(request))
    

    
    
    