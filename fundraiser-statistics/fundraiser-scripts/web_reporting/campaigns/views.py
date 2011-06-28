
"""
    DJANGO VIEW DEFINITIONS:
    ========================
    
    Defines the views for campaigns application.  This is meant to serve as a view into currently running campaigns and 
    generating tests for them.

    Views:
    
    index             -- the index page shows a listing of campaigns and the donations they have received along with a link to a generated report if exists 
    show_campaigns    -- this view shows the view stats for a campaign and allows the user to execute a test and generate a report
    show_report       -- this view simply enables linking to existing reports
    
"""

__author__ = "Ryan Faulkner"
__revision__ = "$Rev$"
__date__ = "June 20th, 2011"


""" Import django modules """
from django.shortcuts import render_to_response
from django.http import Http404
from django.shortcuts import render_to_response, get_object_or_404
from django.template import RequestContext
from django.http import HttpResponseRedirect, HttpResponse
from django.core.urlresolvers import reverse

""" Import python base modules """
import sys, os, datetime, operator

""" Import Analytics modules """
import Fundraiser_Tools.classes.Helper as Hlp
import Fundraiser_Tools.classes.DataReporting as DR
import Fundraiser_Tools.classes.DataLoader as DL
import Fundraiser_Tools.classes.FundraiserDataHandler as FDH
import Fundraiser_Tools.classes.TimestampProcessor as TP
import Fundraiser_Tools.settings as projSet



"""
    Index page for finding the latest camapigns.  Displays a list of recent campaigns with more than k donations over the last n hours. 
    Also if a report exists a link is provided.
    
"""
def index(request):
    
    err_msg = ''

    """ Parse the filter fields """
    filter_data = True
    try:
        min_donations_var = request.POST['min_donations']
        earliest_utc_ts_var = request.POST['utc_ts']

    except KeyError as e:
        filter_data = False
    
    
    """ Interface with the DataLoader """
    
    os.chdir(projSet.__project_home__ + 'classes')
    
    crl = DL.CampaignReportingLoader('totals')
    
    #end_time, start_time = TP.timestamps_for_interval(datetime.datetime.now() + datetime.timedelta(hours=8), 1, hours=-24)
    #start_time = '20101230130400'
    #end_time = '20101230154400'
    start_time = '20110531120000'
    end_time = TP.timestamp_from_obj(datetime.datetime.now() + datetime.timedelta(hours=8),1,3)
    
    campaigns, all_data = crl.run_query({'metric_name':'earliest_timestamp','start_time':start_time,'end_time':end_time})
        
    sorted_campaigns = sorted(campaigns.iteritems(), key=operator.itemgetter(1))
    sorted_campaigns.reverse()
    
    if filter_data:
        try:
            if min_donations_var == '':
                min_donations = 0
            else:
                min_donations = int(min_donations_var)
            
            earliest_utc_ts = int(earliest_utc_ts_var)
        except:
            err_msg = 'Filter fields are incorrect.'

            filter_data = False
    else:
        min_donations_var = ''
        earliest_utc_ts_var = ''


    new_sorted_campaigns = list()
    for campaign in sorted_campaigns:
        key = campaign[0]
        
        if campaign[1] > 0:
            name = all_data[key][0]
            if name  == None:
                name = 'none'
            # timestamp = TP.timestamp_from_obj(all_data[key][3], 2, 2)
            timestamp = TP.timestamp_convert_format(all_data[key][3], 1, 2)
            
            if filter_data: 
                if all_data[key][2] > min_donations and int(all_data[key][3]) > earliest_utc_ts:
                    new_sorted_campaigns.append([campaign[0], campaign[1], name, timestamp, all_data[key][2], all_data[key][4]])
            else:
                new_sorted_campaigns.append([campaign[0], campaign[1], name, timestamp, all_data[key][2], all_data[key][4]])
    
    sorted_campaigns = new_sorted_campaigns
    
    os.chdir(projSet.__project_home__ + 'web_reporting')

    return render_to_response('campaigns/index.html', {'campaigns' : sorted_campaigns, 'err_msg' : err_msg, 'min_donations' : min_donations_var, 'earliest_utc' : earliest_utc_ts_var}, context_instance=RequestContext(request))

    


"""

    Shows view stats over the time range for which the campaign receives landing page hits.  A form is also generated from the associated template
    that allows a test report to be generated.

"""
def show_campaigns(request, utm_campaign):
    
    """ Look 10 hrs into the past? """
    # end_time, start_time = TP.timestamps_for_interval(datetime.datetime.now() + datetime.timedelta(hours=8), 1, hours=-24)
    
    #start_time = '20101230130400'
    #end_time = '20101230154400' 
    """ currently the start time is hard coded to the beginning of FR testing """
    start_time = '20110531120000'  
    end_time = TP.timestamp_from_obj(datetime.datetime.now() + datetime.timedelta(hours=8),1,3)
    
    interval = 2
    
    os.chdir(projSet.__project_home__ + 'classes')
    
    """ Estimate start/end time of campaign """
    """ This generates an image for campaign views """
    ir = DR.IntervalReporting(was_run=False, use_labels=False, font_size=20, plot_type='line', query_type='campaign', file_path=projSet.__web_home__ + 'campaigns/static/images/')
    
    """ 
        Try to produce analysis on the campaign view data  
        If unsuccessful reverse to index view and emit error message
    """
    try:
        ir.run(start_time, end_time, interval, 'views', utm_campaign, [])
    
    except Exception as inst:
        print >> sys.stderr, type(inst)     # the exception instance
        print >> sys.stderr, inst.args      # arguments stored in .args
        print >> sys.stderr, inst           # __str__ allows args to printed directly
        
        err_msg = 'There is insufficient data to analyze this campaign %s.' % utm_campaign
        return HttpResponseRedirect(reverse('campaigns.views.index'))
    
    """ search for start_time and end_time """
    top_view_interval = max(ir._counts_[utm_campaign])

    start_count = 0
    end_count = len(ir._counts_[utm_campaign]) 
    begin_count = False
    count = 0
    
    """ 
        ESTIMATE THE START AND END TIME OF THE CAMAPIGN
        
        Search for the first instance when more than 10 views are observed over a smapling period
    """
    for i in ir._counts_[utm_campaign]:
        # if i > (0.5 * top_view_interval) and not(begin_count):
        if i > 10:
            start_count = count
            break
        
        count = count + 1
        
    
    count = 0
    ir._counts_[utm_campaign].reverse()
    for i in ir._counts_[utm_campaign]:
        
        if i > 10:
            end_count = count
            break
        
        count = count + 1
    
    ir._counts_[utm_campaign].reverse()
    
    """ Based on where the first and last number of views/interval are observed to be greater that 10, generate the associated timestamps """
    end_count = len(ir._counts_[utm_campaign]) - end_count
    start_time_est = TP.timestamp_to_obj(start_time, 1) + datetime.timedelta(minutes=interval * start_count)
    end_time_est = TP.timestamp_to_obj(start_time, 1) + datetime.timedelta(minutes=interval * end_count)
    start_time_est = TP.timestamp_from_obj(start_time_est, 1, 2)
    end_time_est = TP.timestamp_from_obj(end_time_est, 1, 2)
    
    """ Read the test name """
    ttl = DL.TestTableLoader()
    row = ttl.get_test_row(utm_campaign)
    test_name = ttl.get_test_field(row ,'test_name')
    
    """ Regenerate the data using the estimated start and end times  !! FIXME / MODIFY -- this is cumbersome .. should just generate the plot !! """
    ir = DR.IntervalReporting(was_run=False, use_labels=False, font_size=20, plot_type='line', query_type='campaign', file_path=projSet.__web_home__ + 'campaigns/static/images/')
    ir.run(start_time_est, end_time_est, interval, 'views', utm_campaign, [])

        
    """ determine the type of test """
    """ Get the banners  """
    test_type, artifact_name_list = FDH.get_test_type(utm_campaign, start_time, end_time, DL.CampaignReportingLoader(''))
    
    os.chdir(projSet.__project_home__ + 'web_reporting')

    return render_to_response('campaigns/show_campaigns.html', {'utm_campaign' : utm_campaign, 'test_name' : test_name, 'start_time' : start_time_est, 'end_time' : end_time_est, 'artifacts' : artifact_name_list, 'test_type' : test_type}, context_instance=RequestContext(request))    
    
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

    
    