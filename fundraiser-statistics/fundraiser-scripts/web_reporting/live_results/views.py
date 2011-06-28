
"""
    DJANGO VIEW DEFINITIONS:
    ========================
    
    Defines the views for ongoing donations.  This view interacts with templates serving embedded AJAX plotting to provide the user with a 
    a running feed of campaign, banner, and landing page performance based on donations received.

    Views:
    
    index             -- Queries the fundraiser database for donations and sends the data to the template to be displayed 
    
    Helpers:
    
    get_data_lists        -- composes the donation query data into a format expected by the template
    combine_data_lists    -- combines the separate data sets from different queries into one in a format expected by the template 
    
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
import types
import re
import datetime
import json
from django.utils import simplejson

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
    Index page for live results. 
"""
def index(request):
    
    """ Get the donations for all campaigns over the last n hours """
    end_time, start_time = TP.timestamps_for_interval(datetime.datetime.now() + datetime.timedelta(hours=5), 1, hours=-6)
    end_time = '20110623220000'
    start_time = '20110623160000'
    
    """ Create a interval loader objects """
    ir_cmpgn = DR.IntervalReporting(query_type=FDH._QTYPE_CAMPAIGN_ + FDH._QTYPE_TIME_)
    ir_banner = DR.IntervalReporting(query_type=FDH._QTYPE_BANNER_ + FDH._QTYPE_TIME_)
    ir_lp = DR.IntervalReporting(query_type=FDH._QTYPE_LP_ + FDH._QTYPE_TIME_)
    
    sampling_interval = 10
    
    """ Execute queries for campaign, banner, and landing page donations """    
    os.chdir(projSet.__project_home__ + '/classes')
    
    #ir.run('20110603120000', '20110604000000', 2, 'donations', '',[])
    ir_cmpgn.run(start_time, end_time, sampling_interval, 'donations', '',[])
    ir_banner.run(start_time, end_time, sampling_interval, 'donations', '',[])
    ir_lp.run(start_time, end_time, sampling_interval, 'donations', '',[])
    os.chdir(projSet.__home__)
    
    """ Extract data from interval reporting objects """
    cmpgn_data_dict = get_data_lists(ir_cmpgn)
    cmpgn_banner_dict = get_data_lists(ir_banner)
    cmpgn_lp_dict = get_data_lists(ir_lp)
    
    """ combine the separate data sets """
    dict_param = combine_data_lists([cmpgn_data_dict, cmpgn_banner_dict, cmpgn_lp_dict])
    
    return render_to_response('live_results/index.html', dict_param,  context_instance=RequestContext(request))


"""

    !! FIXME -- Move to Helper?? !!
    
"""
def get_data_lists(ir):
    
    """ Get metrics """
    data = list()
    labels = '!'
    counts = list()
    max_data = 0
    
    data_index = 0
    for key in ir._counts_.keys():
        
        data.append(list())
        
        if key == None or key == '':
            labels = labels + 'empty?'
        else:
            labels = labels + key + '?'
        
        counts.append(len(ir._counts_[key]))  
        
        for i in range(counts[data_index]):
            data[data_index].append([ir._times_[key][i], ir._counts_[key][i]])
            if ir._counts_[key][i] > max_data:
                max_data = ir._counts_[key][i]
                
        data_index = data_index + 1
    
    labels = labels + '!'
    
    return {'num_elems' : data_index, 'counts' : counts, 'labels' : labels, 'data' : data, 'max_data' : max_data}
    

"""

    !! FIXME -- Move to Helper?? !!
    
"""
def combine_data_lists(dict_list):
    
    try:
        template_keys = dict_list[0].keys()
        num_elems = len(dict_list)
    except:
        print >> sys.stderr, projSet.__web_home__ + '/live_results/views.py: No template data found.'
        return -1

    combined_dict = dict()
    
    for key in template_keys:
        key_list = list()
        for i in range(num_elems):
            key_list.append(dict_list[i][key])
        combined_dict[key] = key_list
        
    return combined_dict

