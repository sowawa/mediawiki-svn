"""
    DJANGO VIEW DEFINITIONS:
    ========================
    
    Defines the views for Log Miner Logging (LML) application.  The LML is meant to provide functionality for observing log mining activity and to
    copy and mine logs at the users request.

    Views:
    
    index             -- the index page shows a listing of loaded/mined logs by start time and and the time of log mining  
    copy_logs_form    -- this renders a form where the user can request logs to be copied over for a certain hour
    copy_logs_process -- the target of the copy form that executes the copying using a DataMapper
    log_list          -- Shows a listing of all copied logs.  The links allow mining to be initiated.  
    mine_logs_form    -- this renders a form where the user can request logs of a certain hour timestamop to be mined
    mine_logs_process -- the target of the mine form that executes the mining using a DataMapper
    mine_logs_process_file    -- target of the link from the log_list page ... mines log associated to link via DataMapper
"""

__author__ = "Ryan Faulkner"
__revision__ = "$Rev$"
__date__ = "June 20th, 2011"


from django.shortcuts import render_to_response
from django.http import Http404
from django.shortcuts import render_to_response, get_object_or_404
from django.template import RequestContext
from django.http import HttpResponseRedirect, HttpResponse
from django.core.urlresolvers import reverse

import sys
import os
import re
import datetime

import Fundraiser_Tools.classes.Helper as Hlp
import Fundraiser_Tools.classes.DataReporting as DR
import Fundraiser_Tools.classes.DataLoader as DL
import Fundraiser_Tools.classes.DataMapper as DM
import Fundraiser_Tools.classes.FundraiserDataThreading as FDT
import Fundraiser_Tools.classes.FundraiserDataHandler as FDH
import Fundraiser_Tools.classes.TimestampProcessor as TP
import Fundraiser_Tools.settings as projSet
import operator


"""
    Index page for finding the latest camapigns.  Displays a list of recent campaigns with more than k donations over the last n hours. 
    
"""
def index(request):
    
    sltl = DL.SquidLogTableLoader()
    
    """ Show the squid log table """
    squid_table = sltl.get_all_rows_unique_start_time()
    
    """ Show the latest log that has been or is loading and its progress """
    completion_rate = sltl.get_completion_rate_of_latest_log()
    
    return render_to_response('LML/index.html', {'squid_table' : squid_table, 'completion_rate' : completion_rate},  context_instance=RequestContext(request))


"""
    Display log on squid archive server with the option to copy
    
"""
def copy_logs_form(request):
    now = datetime.datetime.now() + datetime.timedelta(hours=7)
    return render_to_response('LML/copy_logs.html', {'year': now.year, 'month': now.month, 'day': now.day, 'hour': now.hour},  context_instance=RequestContext(request))

""" 
    !! MODIFY -- integrate this into a new thread !!
    
    1 Show a full file listing of the logs on the log server
    2 Enable selection of logs to copy over
"""
def copy_logs_process(request):

    try:
        
        year_var = request.POST['year']
        month_var = request.POST['month']
        day_var = request.POST['day']
        hour_var = request.POST['hour']
                
    except KeyError as e:
        """ flag an error here for the user """
        return HttpResponseRedirect(reverse('LML.views.index'))
        # pass
    
    """ Initialize the datamapper and then copy the banner and lp logs """
    dm = DM.DataMapper()
    dm.copy_logs('banner', year=year_var, month=month_var, day=day_var, hour=hour_var)
    dm.copy_logs('lp', year=year_var, month=month_var, day=day_var, hour=hour_var)
    
    
    return render_to_response('LML/log_list.html', {'log_file_list' : dm.get_list_of_logs()},  context_instance=RequestContext(request))

def log_list(request):
    dm = DM.DataMapper()
    return render_to_response('LML/log_list.html', {'log_file_list' : dm.get_list_of_logs()},  context_instance=RequestContext(request))


"""
    Form view that enables user to enter a squid log hour
    *All* available logs from that hour are loaded
"""
def mine_logs_form(request):
    now = datetime.datetime.now() + datetime.timedelta(hours=7)
    return render_to_response('LML/mine_logs.html', {'year': now.year, 'month': now.month, 'day': now.day, 'hour': now.hour},  context_instance=RequestContext(request))

""" 
    !! FIXME -- this needs to be tested !!
    Process mining logs for a given hour - linked from the mine logs form
"""
def mine_logs_process(request):

    try:
        
        year_var = request.POST['year']
        month_var = request.POST['month']
        day_var = request.POST['day']
        hour_var = request.POST['hour']
                
    except KeyError as e:
        """ flag an error here for the user """
        return HttpResponseRedirect(reverse('LML.views.index'))
        # pass
    
    """ Initialize the datamapper and then copy the banner and lp logs """
    fdm = DM.FundraiserDataMapper()
    log_files_list = fdm.get_list_of_logs()
    
    log_names = list()
        
    date_string = year_var + '-' + month_var + '-' + day_var + '-' + hour_var
    
    for lf in log_files_list:
        if re.search(date_string, lf):
             FDT.MinerThread(lf).start()
            
    return HttpResponseRedirect(reverse('LML.views.index'))

    
"""
    Executed when the user selects a log file to mine
    
    Kicks off a thread to load a squid log into the db

"""
def mine_logs_process_file(request, log_name):

    mt = FDT.MinerThread(log_name)
    mt.start()
            
    return HttpResponseRedirect(reverse('LML.views.index'))


    