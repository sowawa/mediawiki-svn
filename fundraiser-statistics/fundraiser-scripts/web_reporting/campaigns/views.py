
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
    
    """ Interface with the DataLoader """
    
    os.chdir(projSet.__project_home__ + '/Fundraiser_Tools/classes')
    
    crl = DL.CampaignReportingLoader()
    campaigns, all_data = crl.run_query('totals',{'metric_name':'donations','start_time':'20101230130400','end_time':'20101230154400'})
    
    sorted_campaigns = sorted(campaigns.iteritems(), key=operator.itemgetter(1))
    sorted_campaigns.reverse()
    
    new_sorted_campaigns = list()
    for campaign in sorted_campaigns:
        key = campaign[0]
        
        if campaign[1] > 50:
            name = all_data[key][0]
            if name  == None:
                name = 'none'
            timestamp = TP.timestamp_from_obj(all_data[key][3], 2, 2)
            new_sorted_campaigns.append([campaign[0], campaign[1], name, timestamp, all_data[key][4]])
    
    sorted_campaigns = new_sorted_campaigns
    
    os.chdir(projSet.__project_home__ + '/Fundraiser_Tools/web_reporting')

    return render_to_response('campaigns/index.html', {'campaigns' : sorted_campaigns})

    


"""

    Use time interval views of the data to display campaign information

"""
def show_campaigns(request, utm_campaign):
    
    """ Look 5 hrs into the past? """
    start_time = '20101230130400'
    end_time = '20101230154400' 
    interval = 2

    os.chdir(projSet.__project_home__ + '/Fundraiser_Tools/classes')
    
    """ Estimate start/end time of campaign """
    """ This generates an image for campaign views """
    ir = DR.IntervalReporting(use_labels=False, font_size=20, plot_type='line', data_loader='campaign', file_path=projSet.__web_home__ + 'campaigns/static/images/')
    ir.run(start_time, end_time, interval, 'campaign', 'views', utm_campaign, [])
    
    """ search for start_time and end_time """
    
    top_view_interval = max(ir._counts_[utm_campaign])

    start_count = 0
    end_count = len(ir._counts_[utm_campaign]) 
    begin_count = False
    count = 0
    
    """ Define the start time as the first interval at least80% of the maximum view interval 
        Define the end time as the first interval following the estimated start time and less than 50% of the maximum view interval """
    for i in ir._counts_[utm_campaign]:
        if i > (0.5 * top_view_interval) and not(begin_count):
            start_count = count
            begin_count = True
            
        if begin_count and i < (0.5 * top_view_interval) and (end_count > count):
            end_count = count - 3
            
        count = count + 1
    
    start_time_est = TP.timestamp_to_obj(start_time, 1) + datetime.timedelta(minutes=interval * start_count)
    end_time_est = TP.timestamp_to_obj(start_time, 1) + datetime.timedelta(minutes=interval * end_count)
    start_time_est = TP.timestamp_from_obj(start_time_est, 1, 2)
    end_time_est = TP.timestamp_from_obj(end_time_est, 1, 2)
    
    
    """ determine the type of test """
    """ Get the banners  """
    test_type, artifact_name_list = FDH.get_test_type(utm_campaign, start_time_est, end_time_est)
        
    os.chdir(projSet.__project_home__ + '/Fundraiser_Tools/web_reporting')

    return render_to_response('campaigns/show_campaigns.html', {'utm_campaign' : utm_campaign, 'start_time' : start_time_est, 'end_time' : end_time_est, 'artifacts' : artifact_name_list, 'test_type' : test_type}, context_instance=RequestContext(request))    
    
    # return HttpResponseRedirect(reverse('campaigns.views.index'))

    """ return a form that displays estimates and allows a test to be generated """


"""
    
    Retrieve a report from the database and display

"""
def show_report(request, utm_campaign):
    
    ttl = DL.TestTableLoader()
    
    row = ttl.get_test_row(utm_campaign)
    
    html = row[7]

    return HttpResponse(html)

    
    