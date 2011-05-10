from django.shortcuts import render_to_response, redirect
from django.http import Http404
from django.shortcuts import render_to_response, get_object_or_404
from django.template import RequestContext
from django.http import HttpResponseRedirect, HttpResponse
from django.core.urlresolvers import reverse

import sys
import math
import os
import getopt
import re
import datetime

import Fundraiser_Tools.classes.Helper as Hlp
import Fundraiser_Tools.classes.DataLoader as DL
import Fundraiser_Tools.classes.DataReporting as DR
import Fundraiser_Tools.classes.FundraiserDataHandler as FDH
import Fundraiser_Tools.classes.TimestampProcessor as TP
import Fundraiser_Tools.settings as projSet
import operator


"""
    Index page for tests ... lists all existing tests and allows a new test to be run
"""
def index(request):
    
    ttl = DL.TestTableLoader()
    test_rows = ttl.get_all_test_rows()
    
    return render_to_response('tests/index.html', {'test_rows' : test_rows},  context_instance=RequestContext(request))





"""
    Executes a test, builds a report, and adds it to the test table
    
"""
def test(request):
    
    """ check where the request came from """
    """ redirect based on origin if there is an error """
    """ check post data """
    
    try:
        
        test_name_var = request.POST['test_name']
        utm_campaign_var = request.POST['utm_campaign']
        start_time_var = request.POST['start_time']
        end_time_var = request.POST['end_time']
        
        try: 
            test_type_var = request.POST['test_type']
            labels = request.POST['artifacts']
            
                
        except KeyError as e:
                        
            os.chdir(projSet.__project_home__ + '/Fundraiser_Tools/classes')
            test_type_var, labels = FDH.get_test_type(utm_campaign_var, start_time_var, end_time_var)
            os.chdir(projSet.__project_home__ + '/Fundraiser_Tools/web_reporting')
           
            labels = labels.__str__() 
        
        labels = labels[1:-1].split(',')
        label_dict = dict()
            
        for i in range(len(labels)):
            label = labels[i].split('\'')[1]
            label = label.strip()            
            label_dict[label] = label
        
    except KeyError as e:
        """ flag an error here for the user """
        return HttpResponseRedirect(reverse('tests.views.index'))
        # pass
        
    os.chdir(projSet.__project_home__ + '/Fundraiser_Tools/classes')
    
    """ Execute Report """
    
    # Build a test interval - use the entire test period
    start_time_obj = TP.timestamp_to_obj(start_time_var, 1)
    end_time_obj = TP.timestamp_to_obj(end_time_var, 1)
    time_diff = end_time_obj - start_time_obj
    time_diff_min = time_diff.seconds / 60.0
    test_interval = int(math.floor(time_diff_min / 2)) # 2 is the interval
    
    
    
    os.chdir(projSet.__project_home__ + '/Fundraiser_Tools/web_reporting')
    
    metric_types = FDH.get_test_type_metrics(test_type_var)
    
    if test_type_var == FDH._TESTTYPE_BANNER_:
        
        winner_dpi, percent_win_dpi, conf_dpi, winner_api, percent_win_api, conf_api =  auto_gen(test_name_var, start_time_var, end_time_var, utm_campaign_var, label_dict, 2, test_interval, test_type_var, metric_types)
        
        html = render_to_response('tests/results_' + FDH._TESTTYPE_BANNER_ + '.html', {'winner' : winner_dpi, 'percent_win_dpi' : '%.2f' % percent_win_dpi, 'percent_win_api' : '%.2f' % percent_win_api, 'conf_dpi' : conf_dpi, 'conf_api' : conf_api, 'utm_campaign' : utm_campaign_var, \
                                                        'metric_names' : metric_types}, context_instance=RequestContext(request))
    elif test_type_var == FDH._TESTTYPE_LP_:
        
        winner_dpv, percent_win_dpv, conf_dpv, winner_apv, percent_win_apv, conf_apv =  auto_gen(test_name_var, start_time_var, end_time_var, utm_campaign_var, label_dict, 2, test_interval, test_type_var, metric_types)
        
        html = render_to_response('tests/results_' + FDH._TESTTYPE_LP_ + '.html', {'winner' : winner_dpv, 'percent_win_dpv' : '%.2f' % percent_win_dpv, 'percent_win_apv' : '%.2f' % percent_win_apv, 'conf_dpv' : conf_dpv, 'conf_apv' : conf_apv, 'utm_campaign' : utm_campaign_var, \
                                                        'metric_names' : metric_types}, context_instance=RequestContext(request))
            
    """ Write to test table """
    
    ttl = DL.TestTableLoader()
    
    """ Format the html string """
    html_string = html.__str__()
    html_string = html_string.replace('"', '\\"')
    html_string = Hlp.stringify(html_string)

#    Strips out whitespace
#    html_string_parts = html_string.split()
#    html_string = ''
#    for i in html_string_parts:
#        html_string = html_string + i
    
    if ttl.record_exists(utm_campaign=utm_campaign_var):
        ttl.update_test_row(test_name=test_name_var,test_type=test_type_var,utm_campaign=utm_campaign_var,start_time=start_time_var,end_time=end_time_var,html_report=html_string)
    else:
        ttl.insert_row(test_name=test_name_var,test_type=test_type_var,utm_campaign=utm_campaign_var,start_time=start_time_var,end_time=end_time_var,html_report=html_string)
    
    return html


"""

    Helper method for 'test' view which generates a report
    
    INPUT:
    RETURN:

"""
def auto_gen(test_name, start_time, end_time, campaign, labels, sample_interval, test_interval, test_type, metric_types):

    # e.g. labels = {'Static banner':'20101227_JA061_US','Fading banner':'20101228_JAFader_US'}
    
    os.chdir('/home/rfaulkner/trunk/projects/Fundraiser_Tools/classes')
    
    use_labels_var = True
    if len(labels) == 0:
        use_labels_var = False
        
    """ Build reporting objects """
    ir = DR.IntervalReporting(use_labels=use_labels_var,font_size=20,plot_type='step',file_path=projSet.__web_home__ + 'tests/static/images/')
    ir_cmpgn = DR.IntervalReporting(use_labels=False,font_size=20,plot_type='line',data_loader='campaign',file_path=projSet.__web_home__ + 'tests/static/images/')
    cr = DR.ConfidenceReporting(use_labels=use_labels_var,font_size=20,plot_type='line',hyp_test='t_test',file_path=projSet.__web_home__ + 'tests/static/images/')
    
    """ generate interval reporting plots """ 
    
    
    # print 'Generating interval plots ...\n'
    for metric in metric_types:
        if test_type == FDH._TESTTYPE_BANNER_:
            ir.run(start_time, end_time, sample_interval, 'banner', metric, campaign, labels.keys())
        if test_type == FDH._TESTTYPE_LP_:
            ir.run(start_time, end_time, sample_interval, 'LP', metric, campaign, labels.keys())
            
    # print 'Generating campaign plots...\n'
    ir_cmpgn.run(start_time, end_time, sample_interval, 'campaign', 'views', campaign, [])
    ir_cmpgn.run(start_time, end_time, sample_interval, 'campaign', 'donations', campaign, [])
    
    """ generate confidence reporting plots """ 
    # print 'Executing hypothesis testing ...\n'
    # cr.run('Fader VS Static','report_banner_confidence','don_per_imp','20101228JA075',{'Static banner':'20101227_JA061_US','Fading banner':'20101228_JAFader_US'},'20101229141000','20101229155000',2,10)
    if test_type == FDH._TESTTYPE_BANNER_:
        winner_dpi, percent_increase_dpi, confidence_dpi = cr.run(test_name,'report_banner_confidence','don_per_imp',campaign, labels, start_time, end_time, sample_interval,test_interval)
        winner_api, percent_increase_api, confidence_api = cr.run(test_name,'report_banner_confidence','amt50_per_imp',campaign, labels, start_time, end_time, sample_interval,test_interval)
    elif test_type == FDH._TESTTYPE_LP_:
        winner_dpi, percent_increase_dpi, confidence_dpi = cr.run(test_name,'report_LP_confidence','don_per_view',campaign, labels, start_time, end_time, sample_interval,test_interval)
        winner_api, percent_increase_api, confidence_api = cr.run(test_name,'report_LP_confidence','amt50_per_view',campaign, labels, start_time, end_time, sample_interval,test_interval)

    """ compose HTML """
    
    os.chdir('/home/rfaulkner/trunk/projects/Fundraiser_Tools/classes/tests/')
    
    
    f = open('auto_report.html', 'w')
    
    html_script = ''
    
    html_script = html_script + '\n<html>\n<head>\n<title>Big Ass Reportin\'</title>'
    
    html_script = html_script + '</head>\n<body>\n<h1>Test Report</h1>\n<br>\n'

    html_script = html_script + '<h3><u>Interval Reporting</u></h3>\n'
    
    html_script = html_script + '<OBJECT WIDTH="1000" HEIGHT="600" data="' + campaign + '_banner_' + metric_types[0] + '.png" type="image/png">\n<p>.</p>\n</OBJECT><br>\n'
    html_script = html_script + '<OBJECT WIDTH="1000" HEIGHT="600" data="' + campaign + '_banner_' + metric_types[1] + '.png" type="image/png">\n<p>.</p>\n</OBJECT><br>\n'
    html_script = html_script + '<OBJECT WIDTH="1000" HEIGHT="600" data="' + campaign + '_banner_' + metric_types[2] + '.png" type="image/png">\n<p>.</p>\n</OBJECT><br>\n'
    html_script = html_script + '<OBJECT WIDTH="1000" HEIGHT="600" data="' + campaign + '_banner_' + metric_types[3] + '.png" type="image/png">\n<p>.</p>\n</OBJECT><br>\n'
    html_script = html_script + '<OBJECT WIDTH="1000" HEIGHT="600" data="' + campaign + '_campaign_views' + '.png" type="image/png">\n<p>.</p>\n</OBJECT><br>\n'
    html_script = html_script + '<OBJECT WIDTH="1000" HEIGHT="600" data="' + campaign + '_campaign_donations' + '.png" type="image/png">\n<p>.</p>\n</OBJECT><br>\n'
    
    html_script = html_script + '<h3><u>Confidence Reporting</u></h3>\n'
    
    html_script = html_script + '<OBJECT WIDTH="1000" HEIGHT="600" data="' + campaign + '_conf_don_per_imp' + '.png" type="image/png">\n<p>.</p>\n</OBJECT><br>\n'
    html_script = html_script + '<OBJECT WIDTH="1000" HEIGHT="600" data="' + campaign + '_conf_amt50_per_imp' + '.png" type="image/png">\n<p>.</p>\n</OBJECT><br>\n'
    
    """ !! MODIFY -- THIS currently doesn't look great !!
    
    f_test_results_1 = open(campaign + '_conf_don_per_imp' + '.txt', 'r')
    f_test_results_2 = open(campaign + '_conf_amt50_per_imp' + '.txt', 'r')
    
    data_results_1 = ''
    line = f_test_results_1.readline()
    while (line):
        data_results_1 = data_results_1 + line + '<br>'
        line = f_test_results_1.readline()
    
    data_results_2 = ''
    line = f_test_results_2.readline()
    while (line):
        data_results_2 = data_results_2 + line + '<br>'
        line = f_test_results_2.readline()
        
    html_script = html_script + data_results_1 + '<br><br>'
    html_script = html_script + data_results_2

    """ 
    
    html_script = html_script + '</body></html>\n'

    # print html_script
    
    f.write(html_script)
    
    f.close()
        
    return [winner_dpi, percent_increase_dpi, confidence_dpi, winner_api, percent_increase_api, confidence_api]


"""
    Inserts a comment into an existing report 
        
"""
def add_comment(request, utm_campaign):
    
    try:
        comments = request.POST['comments']
        
    except:
        return HttpResponseRedirect(reverse('tests.views.index'))
    
    """ Retrieve the report """
    ttl = DL.TestTableLoader()
    row = ttl.get_test_row(utm_campaign)
    html_string = ttl.get_test_field(row, 'html_report')
    
    """ Insert comment """
    new_html = ''
    lines = html_string.split('\n')
    now = datetime.datetime.utcnow().__str__()
    
    for line in lines:
        
        if line == '<!-- Cend -->':
            line = '\n<br>' + comments + '\n<br><br>  --' + now + '<br>\n' + '<!-- Cend -->'
        
        new_html = new_html + line + '\n'
    
    html_string = new_html
    html_string = html_string.replace('"', '\\"')
    html_string = Hlp.stringify(html_string)
    
    # parse the html for <!-- Cbegin --> <!-- Cend -->
    # add the comment above this
    
    """ Update the report """
    ttl.update_test_row(test_name=ttl.get_test_field(row, 'test_name'), test_type=ttl.get_test_field(row, 'test_type'), utm_campaign=ttl.get_test_field(row, 'utm_campaign'), start_time=ttl.get_test_field(row, 'start_time'), \
                        end_time=ttl.get_test_field(row, 'end_time'), winner=ttl.get_test_field(row, 'winner'), is_conclusive=ttl.get_test_field(row, 'is_conclusive'), html_report=html_string)

    
    return HttpResponse(new_html)

