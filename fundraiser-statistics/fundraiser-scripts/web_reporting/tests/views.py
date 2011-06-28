"""
    DJANGO VIEW DEFINITIONS:
    ========================
    
    Defines the views for Log Miner Logging (LML) application.  The LML is meant to provide functionality for observing log mining activity and to
    copy and mine logs at the users request.

    Views:
    
    index           -- the index page shows a listing of generated tests by looking at the test table in the FR database via a DataLoader  
    test            -- This view is responsible for generating the test results via DataReporting object  
    add_comment     -- Handles the comment form when users wish to add them 

    Helper:
    
    auto_gen        -- does the actual work of generating the test report plots and results
    
"""

__author__ = "Ryan Faulkner"
__revision__ = "$Rev$"
__date__ = "June 20th, 2011"


""" Import django modules """
from django.shortcuts import render_to_response, redirect
from django.http import Http404
from django.shortcuts import render_to_response, get_object_or_404
from django.template import RequestContext
from django.http import HttpResponseRedirect, HttpResponse
from django.core.urlresolvers import reverse

""" Import python base modules """
import sys, math, os, getopt, re, datetime, operator

""" Import Analytics modules """
import Fundraiser_Tools.classes.Helper as Hlp
import Fundraiser_Tools.classes.DataLoader as DL
import Fundraiser_Tools.classes.DataReporting as DR
import Fundraiser_Tools.classes.FundraiserDataHandler as FDH
import Fundraiser_Tools.classes.TimestampProcessor as TP
import Fundraiser_Tools.classes.QueryData as QD
import Fundraiser_Tools.settings as projSet



"""
    Index page for tests ... lists all existing tests and allows a new test to be run
"""
def index(request):
    
    ttl = DL.TestTableLoader()
    test_rows = ttl.get_all_test_rows()

    l = []
    for i in test_rows:
        l.append(i)
    l.reverse()
    
    return render_to_response('tests/index.html', {'test_rows' : l},  context_instance=RequestContext(request))





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
        test_type_override = request.POST['test_type_override']
        
        try: 
            test_type_var = request.POST['test_type']
            labels = request.POST['artifacts']
            
                
        except KeyError as e:
                        
            os.chdir(projSet.__project_home__ + 'classes')
            test_type_var, labels = FDH.get_test_type(utm_campaign_var, start_time_var, end_time_var, DL.CampaignReportingLoader(''))  # submit an empty query type
            os.chdir(projSet.__project_home__ + 'web_reporting')
           
            labels = labels.__str__() 
        
        label_dict = dict()
        
        """ Look at the artifact names and map them into a dict() 
            Determine if artifacts were chosen by the user """
        
        if request.POST.__contains__('artifacts_chosen'):
            
            artifacts_chosen =  request.POST.getlist()
            
            for elem in artifacts_chosen:
                label_dict[elem] = elem
        else:
            print labels
            labels = labels[1:-1].split(',')
            
            for i in range(len(labels)):
                label = labels[i].split('\'')[1]
                label = label.strip()            
                pieces = label.split(' ')
                label = pieces[0]
                for j in range(len(pieces) - 1):
                    label = label + '_' + pieces[j+1]
                label_dict[label] = label
        
    except Exception as inst:
        
        print type(inst)     # the exception instance
        print inst.args      # arguments stored in .args
        print inst           # __str__ allows args to printed directly

        """ flag an error here for the user """
        return HttpResponseRedirect(reverse('tests.views.index'))
        # pass
    
    os.chdir(projSet.__project_home__ + 'classes')
    
    crl = DL.CampaignReportingLoader('')
    artifact_list = list()
    
    """
        TEST TYPE OVERRIDE HANDLING:
        
        if the user wishes to specify the test type then incorporate that request into the logic
    """
    if test_type_override == 'Banner':
        test_type_var = FDH._TESTTYPE_BANNER_
        crl._query_type_ = test_type_var
        artifact_list = crl.run_query({'utm_campaign' : utm_campaign_var, 'start_time' : start_time_var, 'end_time' : end_time_var})
    elif test_type_override == 'Landing Page':
        test_type_var = FDH._TESTTYPE_LP_
        crl._query_type_ = test_type_var
        artifact_list = crl.run_query({'utm_campaign' : utm_campaign_var, 'start_time' : start_time_var, 'end_time' : end_time_var})
    elif test_type_override == 'Banner and LP':
        test_type_var = FDH._TESTTYPE_BANNER_LP_
        crl._query_type_ = test_type_var
        artifact_list = crl.run_query({'utm_campaign' : utm_campaign_var, 'start_time' : start_time_var, 'end_time' : end_time_var})
    
    
    """ convert the artifact list into a label dictionary for the template """
    if len(artifact_list) > 0:
        label_dict = dict()
        for elem in artifact_list:
            label_dict[elem] = elem
    

    """ 
        EXECUTE REPORT 
    
        -> setup time parameters
        -> determine test metrics
        -> execute queries
    """
    
    sample_interval = 2
    
    start_time_obj = TP.timestamp_to_obj(start_time_var, 1)
    end_time_obj = TP.timestamp_to_obj(end_time_var, 1)
    
    time_diff = end_time_obj - start_time_obj
    time_diff_min = time_diff.seconds / 60.0
    test_interval = int(math.floor(time_diff_min / sample_interval)) # 2 is the interval
    
    
    os.chdir(projSet.__project_home__ + 'web_reporting')
    
    metric_types = FDH.get_test_type_metrics(test_type_var)
    metric_types_full = dict()
    
    
    """ Get the full (descriptive) version of the metric names 
        !! FIXME / TODO -- order these properly !! """
    
    for i in range(len(metric_types)):
        metric_types_full[metric_types[i]] = QD.get_metric_full_name(metric_types[i])
    
    
    """ USE autogen() TO GENERATE THE REPORT DATA - dependent on test type """

    if test_type_var == FDH._TESTTYPE_BANNER_:
        
        winner_dpi, percent_win_dpi, conf_dpi, winner_api, percent_win_api, conf_api, percent_win_cr, conf_cr, html_table =  auto_gen(test_name_var, start_time_var, end_time_var, utm_campaign_var, label_dict, sample_interval, test_interval, test_type_var, metric_types)
        
        html = render_to_response('tests/results_' + FDH._TESTTYPE_BANNER_ + '.html', {'winner' : winner_dpi, 'percent_win_dpi' : '%.2f' % percent_win_dpi, 'percent_win_api' : '%.2f' % percent_win_api, 'percent_win_cr' : '%.2f' % percent_win_cr, \
                                                                                       'conf_dpi' : conf_dpi, 'conf_api' : conf_api, 'conf_cr' : conf_cr, 'utm_campaign' : utm_campaign_var, 'metric_names_full' : metric_types_full, \
                                    'summary_table': html_table, 'sample_interval' : sample_interval}, context_instance=RequestContext(request))
    elif test_type_var == FDH._TESTTYPE_LP_:
        
        winner_dpv, percent_win_dpv, conf_dpv, winner_apv, percent_win_apv, conf_apv, html_table =  auto_gen(test_name_var, start_time_var, end_time_var, utm_campaign_var, label_dict, sample_interval, test_interval, test_type_var, metric_types)
        
        html = render_to_response('tests/results_' + FDH._TESTTYPE_LP_ + '.html', {'winner' : winner_dpv, 'percent_win_dpv' : '%.2f' % percent_win_dpv, 'percent_win_apv' : '%.2f' % percent_win_apv, 'conf_dpv' : conf_dpv, 'conf_apv' : conf_apv, 'utm_campaign' : utm_campaign_var, \
                                    'metric_names_full' : metric_types_full, 'summary_table': html_table, 'sample_interval' : sample_interval}, context_instance=RequestContext(request))
    
    elif test_type_var == FDH._TESTTYPE_BANNER_LP_:
        
        winner_dpi, percent_win_dpi, conf_dpi, winner_api, percent_win_api, conf_api, percent_win_cr, conf_cr, html_table =  auto_gen(test_name_var, start_time_var, end_time_var, utm_campaign_var, label_dict, sample_interval, test_interval, test_type_var, metric_types)
        
        html = render_to_response('tests/results_' + FDH._TESTTYPE_BANNER_LP_ + '.html', {'winner' : winner_dpi, 'percent_win_dpi' : '%.2f' % percent_win_dpi, 'percent_win_api' : '%.2f' % percent_win_api, 'percent_win_cr' : '%.2f' % percent_win_cr, \
                                                                                          'conf_dpi' : conf_dpi, 'conf_api' : conf_api, 'conf_cr' : conf_cr, 'utm_campaign' : utm_campaign_var, 'metric_names_full' : metric_types_full, \
                                                                                          'summary_table': html_table, 'sample_interval' : sample_interval}, context_instance=RequestContext(request))
            
    
    
    """ WRITE TO TEST TABLE """
    
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

    Helper method for 'test' view which generates a report based on parameters
    

"""
def auto_gen(test_name, start_time, end_time, campaign, labels, sample_interval, test_interval, test_type, metric_types):

    # e.g. labels = {'Static banner':'20101227_JA061_US','Fading banner':'20101228_JAFader_US'}
    
    os.chdir('/home/rfaulkner/trunk/projects/Fundraiser_Tools/classes')
    
    """ Labels will always be metric names in this case """
    use_labels_var = True
    if len(labels) == 0:
        use_labels_var = False
        
    """ 
        BUILD REPORTING OBJECTS 
    """
    if test_type == FDH._TESTTYPE_BANNER_:
        ir = DR.IntervalReporting(use_labels=use_labels_var,font_size=20,plot_type='step',query_type=FDH._QTYPE_BANNER_,file_path=projSet.__web_home__ + 'tests/static/images/')
    elif test_type == FDH._TESTTYPE_LP_:
        ir = DR.IntervalReporting(use_labels=use_labels_var,font_size=20,plot_type='step',query_type=FDH._QTYPE_LP_, file_path=projSet.__web_home__ + 'tests/static/images/')
    elif test_type == FDH._TESTTYPE_BANNER_LP_:
        ir = DR.IntervalReporting(use_labels=use_labels_var,font_size=20,plot_type='step',query_type=FDH._QTYPE_BANNER_LP_,file_path=projSet.__web_home__ + 'tests/static/images/')
        
    ir_cmpgn = DR.IntervalReporting(use_labels=False,font_size=20,plot_type='line',query_type='campaign',file_path=projSet.__web_home__ + 'campaigns/static/images/')
    cr = DR.ConfidenceReporting(use_labels=use_labels_var,font_size=20,plot_type='line',hyp_test='t_test',file_path=projSet.__web_home__ + 'tests/static/images/')
    

    """
        GENERATE PLOTS FOR EACH METRIC OF INTEREST
         !! MODIFY -- allow a list of metrics to be passed 
    """
    for metric in metric_types:
        #print [start_time, end_time, sample_interval, metric, campaign, labels.keys()]
        ir.run(start_time, end_time, sample_interval, metric, campaign, labels.keys())
        
        
    """ GENERATE A REPORT SUMMARY """
    ir._write_html_table()
    html_table = ir._table_html_
    
    
    """ CHECK THE CAMPAIGN VIEWS AND DONATIONS """
    ir_cmpgn.run(start_time, end_time, sample_interval, 'views', campaign, [])
    ir_cmpgn.run(start_time, end_time, sample_interval, 'donations', campaign, [])
    
    
    """ PERFORM HYPOTHESIS TESTING """
    
    if test_type == FDH._TESTTYPE_BANNER_:
        winner_dpi, percent_increase_dpi, confidence_dpi = cr.run(test_name,'report_banner_confidence','don_per_imp',campaign, labels, start_time, end_time, sample_interval,test_interval)
        winner_api, percent_increase_api, confidence_api = cr.run(test_name,'report_banner_confidence','amt50_per_imp',campaign, labels, start_time, end_time, sample_interval,test_interval)
        winner_cr, percent_increase_cr, confidence_cr = cr.run(test_name,'report_banner_confidence','click_rate',campaign, labels, start_time, end_time, sample_interval,test_interval)
        
        return [winner_dpi, percent_increase_dpi, confidence_dpi, winner_api, percent_increase_api, confidence_api, percent_increase_cr, confidence_cr, html_table]
    
    elif test_type == FDH._TESTTYPE_LP_:
        winner_dpi, percent_increase_dpi, confidence_dpi = cr.run(test_name,'report_LP_confidence','don_per_view',campaign, labels, start_time, end_time, sample_interval,test_interval)
        winner_api, percent_increase_api, confidence_api = cr.run(test_name,'report_LP_confidence','amt50_per_view',campaign, labels, start_time, end_time, sample_interval,test_interval)
        
        return [winner_dpi, percent_increase_dpi, confidence_dpi, winner_api, percent_increase_api, confidence_api, html_table]
    
    elif test_type == FDH._TESTTYPE_BANNER_LP_:
        winner_dpi, percent_increase_dpi, confidence_dpi = cr.run(test_name,'report_bannerLP_confidence','don_per_imp',campaign, labels, start_time, end_time, sample_interval,test_interval)
        winner_api, percent_increase_api, confidence_api = cr.run(test_name,'report_bannerLP_confidence','amt50_per_imp',campaign, labels, start_time, end_time, sample_interval,test_interval)
        winner_cr, percent_increase_cr, confidence_cr = cr.run(test_name,'report_bannerLP_confidence','click_rate',campaign, labels, start_time, end_time, sample_interval,test_interval)
        
        return [winner_dpi, percent_increase_dpi, confidence_dpi, winner_api, percent_increase_api, confidence_api, percent_increase_cr, confidence_cr, html_table]
        
    #winner_dpi, percent_increase_dpi, confidence_dpi = ['',0.0,'']
    #winner_api, percent_increase_api, confidence_api = ['',0.0,'']
    # return [winner_dpi, percent_increase_dpi, confidence_dpi, winner_api, percent_increase_api, confidence_api, html_table]


"""
    Inserts a comment into an existing report
    
    !! FIXME - do this dynamically with AJAX !! 
        
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
    
    """ Insert comment into the page html """
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

