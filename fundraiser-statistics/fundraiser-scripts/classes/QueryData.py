
"""

    This module effectively functions as a Singleton class.
    
    This module contains and organizes query info.  Depends on the contents of ../sql/ where filenames are 
    coupled with query_name parameters
    
     METHODS:
        
            format_query         
            get_query             
            get_query_header     
            get_key_index
            get_count_index
            get_time_index
            get_campaign_index
            get_banner_index
            get_landing_page_index
            get_metric_index
            get_plot_title
            get_plot_ylabel
            get_metric_full_name
       

    QUERIES:
        {report_campaign_metrics_minutely_all, report_banner_metrics_minutely_all, report_LP_metrics_minutely_all} ==> used for live reporting on donations and clicks .. over all campaigns, banners, and LPs
        {report_campaign_metrics_minutely, report_banner_metrics_minutely, report_LP_metrics_minutely}             ==> used for interval reporting on campaign tests
        {report_campaign_metrics_minutely_total}                                                                   ==> reports totals for a camapign -- combines with report_campaign_metrics_minutely
"""

__author__ = "Ryan Faulkner"
__revision__ = "$Rev$"
__date__ = "November 28th, 2010"

import Fundraiser_Tools.classes.TimestampProcessor as TP
import datetime

def format_query(query_name, sql_stmnt, args):
    
    if query_name == 'report_campaign_ecomm':
        start_time = args[0]
        sql_stmnt = sql_stmnt % (start_time)

    elif query_name == 'report_campaign_logs':
        start_time = args[0]
        sql_stmnt = sql_stmnt % (start_time, start_time, start_time)

    elif query_name == 'report_campaign_ecomm_by_hr':
        start_time = args[0]
        sql_stmnt = sql_stmnt % ('%', '%', '%', '%', start_time)

    elif query_name == 'report_campaign_logs_by_hr':
        start_time = args[0]
        sql_stmnt = sql_stmnt % ('%', '%', '%', '%', start_time, '%', '%', '%', '%', \
        start_time, '%', '%', '%', '%', start_time, '%')

    elif query_name == 'report_impressions_country':
        start_time = args[0]
        sql_stmnt = sql_stmnt % ('%', '%', '%', start_time)

    elif query_name == 'report_campaign_logs_by_min':
        start_time = args[0]
        sql_stmnt = sql_stmnt % ('%', '%', '%', '%', start_time, '%', '%', '%', '%', \
        start_time, '%', '%', '%', '%', start_time)
    
    elif query_name == 'report_non_US_clicks':
        start_time = args[0]
        sql_stmnt = sql_stmnt % ('%', '%', '%', start_time, '%', '%', '%', start_time)
    
    elif query_name == 'report_contribution_tracking':
        start_time = args[0]
        sql_stmnt = sql_stmnt % ('%', '%', '%', '%', '%',start_time)
    
    elif query_name == 'report_total_amounts_by_hr':
        start_time = args[0]
        end_time = args[1]            
        sql_stmnt = sql_stmnt % ('%', '%', '%', ' %H', start_time, end_time)
    
    elif query_name == 'report_total_amounts_by_day':
        start_time = args[0]
        end_time = args[1]
        sql_stmnt = sql_stmnt % ('%', '%', '%', '', start_time, end_time)
    
    elif query_name == 'report_bannerLP_metrics':
        start_time = args[0]
        end_time = args[1]
        campaign = args[2]
        sql_stmnt = sql_stmnt % ('%', '%', '%', '%', start_time, end_time, '%', '%', '%', '%', start_time, end_time, '%', '%', '%', '%', start_time, end_time, campaign, '%', '%', '%', '%', start_time, end_time, campaign)
    
    elif query_name == 'report_latest_campaign':
        start_time = args[0]
        sql_stmnt = sql_stmnt % (start_time)
        
    elif query_name == 'report_confidence_banner':
        start = args[0]
        end = args[1]
        cmpgn = args[2]
        banner = args[3]
        sql_stmnt = sql_stmnt % ('%','%','%','%','10','10', start, end, banner, '%','%','%','%','10','10', start, end, cmpgn, banner, \
        '%','%','%','%','10','10', start, end, cmpgn, banner)
    
    elif query_name == 'report_confidence_lp':
        start = args[0]
        end = args[1]
        cmpgn = args[2]
        banner = args[3]
        sql_stmnt = sql_stmnt % ('%','%','%','%','10','10', start, end, cmpgn, banner, \
        '%','%','%','%','10','10', start, end, cmpgn, banner)
    
    elif query_name == 'report_banner_impressions_by_hour':
        start = args[0]
        end = args[1]
        sql_stmnt = sql_stmnt % ('%','%','%','%', start, end)
    
    elif query_name == 'report_lp_views_by_hour':
        start = args[0]
        end = args[1]
        sql_stmnt = sql_stmnt % ('%','%','%','%', start, end)
    
    elif query_name == 'report_banner_confidence':
        
        start = args[0]
        end = args[1]
        banner = args[2]
        campaign = args[3]
        
        """ The start time for the impression portion of the query should be one second less"""
        start_time_obj = TP.timestamp_to_obj(start,1)
        imp_start_time_obj = start_time_obj + datetime.timedelta(seconds=-1)
        imp_start_time_obj_str = TP.timestamp_from_obj(imp_start_time_obj, 1, 3)
        
        sql_stmnt = sql_stmnt % (imp_start_time_obj_str, end, banner, start, end, campaign, start, end, banner, start, end, campaign, banner)
    
    elif query_name == 'report_bannerLP_confidence':
        
        """ The artifact name is a composition of banner and landing page and must be split """
        bannerlp_arg = args[2].split('-')
        
        start = args[0]
        end = args[1]
        utm_source = bannerlp_arg[0]
        campaign = args[3]
        
        """ The start time for the impression portion of the query should be one second less"""
        start_time_obj = TP.timestamp_to_obj(start,1)
        imp_start_time_obj = start_time_obj + datetime.timedelta(seconds=-1)
        imp_start_time_obj_str = TP.timestamp_from_obj(imp_start_time_obj, 1, 3)
        
        sql_stmnt = sql_stmnt % (imp_start_time_obj_str, end, utm_source, start, end, campaign, start, end, utm_source, start, end, campaign, utm_source)
        
    elif query_name == 'report_LP_confidence':
        start = args[0]
        end = args[1]
        lp = args[2]
        campaign = args[3]
        sql_stmnt = sql_stmnt % (start, end, campaign, lp, start, end, campaign, lp)
        
    elif query_name == 'report_ecomm_by_amount':
        start_time = args[0]
        end_time = args[1]
        sql_stmnt = sql_stmnt % ('%', '%',  '%',  '%', start_time, end_time, end_time)
    
    elif query_name == 'report_ecomm_by_contact':
        where_str = args[0]
        sql_stmnt = sql_stmnt % ('%', '%', '%', '%', where_str)
    
    elif query_name == 'report_banner_metrics_minutely' or query_name == 'report_bannerLP_metrics_minutely':
        start_time = args[0]
        end_time = args[1]
        campaign = args[2]
        interval = args[3]
        
        """ The start time for the impression portion of the query should be one second less"""
        start_time_obj = TP.timestamp_to_obj(start_time,1)
        imp_start_time_obj = start_time_obj + datetime.timedelta(seconds=-1)
        imp_start_time_obj_str = TP.timestamp_from_obj(imp_start_time_obj, 1, 3)
        
        sql_stmnt = sql_stmnt % ('%', '%', '%',  '%', interval, interval, imp_start_time_obj_str, end_time, '%', '%',  '%',  '%', interval, interval, start_time, end_time, campaign, \
                                '%', '%',  '%',  '%', interval, interval, start_time, end_time, '%', '%',  '%',  '%', interval, interval, start_time, end_time, campaign, campaign)
    
    elif query_name == 'report_LP_metrics_minutely':
        start_time = args[0]
        end_time = args[1]
        campaign = args[2]
        interval = args[3]
        
        sql_stmnt = sql_stmnt % ('%', '%', '%',  '%', interval, interval, start_time, end_time, campaign, '%', '%',  '%',  '%', interval, interval, start_time, end_time, campaign, campaign)
    
    elif query_name == 'report_campaign_metrics_minutely':
        start_time = args[0]
        end_time = args[1]
        campaign = args[2]
        interval = args[3]
        
        sql_stmnt = sql_stmnt % (campaign, '%', '%', '%',  '%', interval, interval, start_time, end_time, campaign, '%', '%',  '%',  '%', interval, interval, start_time, end_time, campaign)
    
    elif query_name == 'report_campaign_metrics_minutely_total':
        start_time = args[0]
        end_time = args[1]
        campaign = args[2]
        interval = args[3]
        
        sql_stmnt = sql_stmnt % (campaign, '%', '%', '%',  '%', interval, interval, start_time, end_time, campaign, '%', '%',  '%',  '%', interval, interval, start_time, end_time, campaign)
    
    elif query_name == 'report_campaign_totals':
        start_time = args[0]
        end_time = args[1]
        
        sql_stmnt = sql_stmnt % (start_time, end_time)
    
    elif query_name == 'report_campaign_banners':
        start_time = args[0]
        end_time = args[1]
        utm_campaign = args[2]
        
        sql_stmnt = sql_stmnt % (start_time, end_time, utm_campaign)
        
    elif query_name == 'report_campaign_lps':
        start_time = args[0]
        end_time = args[1]
        utm_campaign = args[2]
        
        sql_stmnt = sql_stmnt % (start_time, end_time, utm_campaign)
    
    elif query_name == 'report_campaign_metrics_minutely_all' or query_name == 'report_banner_metrics_minutely_all' or query_name == 'report_lp_metrics_minutely_all':
        start_time = args[0]
        end_time = args[1]
        interval = args[3]
        
        sql_stmnt = sql_stmnt % ('%', '%', '%',  '%', interval, interval, start_time, end_time)
        
    else:
        return 'no such table\n'

    return sql_stmnt


def get_query_header(query_name):
    if query_name == 'report_contribution_tracking':
        return ['Time','Banner','Landing Page','Campaign','Converted Amount', 'Suffix']
    elif query_name == 'report_ecomm_by_amount':
        return ['Timestamp','First Name','Last Name','Country','ISO Code', 'Amount', 'First Donation?', 'Date of First']
    elif query_name == 'report_ecomm_by_contact':
        return ['Timestamp','First Name','Last Name','Country','ISO Code', 'Amount']
    else:
        return 'no such table'

"""     
    Returns the index of the key for the query data 
"""
def get_key_index(query_name):
    if query_name == 'report_banner_metrics_minutely':
        return 1
    elif query_name == 'report_bannerLP_metrics_minutely':
        return 1
    elif query_name == 'report_LP_metrics_minutely':
        return 1
    elif query_name == 'report_campaign_metrics_minutely':
        return 1
    elif query_name == 'report_campaign_metrics_minutely_total':
        return 1
    elif query_name == 'report_campaign_totals':
        return 1
    elif query_name == 'report_campaign_banners':
        return 0
    elif query_name == 'report_campaign_lps':
        return 0
    elif query_name == 'report_campaign_metrics_minutely_all' or query_name == 'report_banner_metrics_minutely_all' or query_name == 'report_lp_metrics_minutely_all':
        return 1
    else:
        return 1
    
"""     
    Returns the index of the timestamp for the query data 
"""
def get_time_index(query_name):
    if query_name == 'report_campaign_logs_by_min':
        return 0
    elif query_name == 'report_campaign_logs_by_hr':
        return 0
    elif query_name == 'report_non_US_clicks':
        return 0
    elif query_name == 'report_contribution_tracking':
        return 0
    elif query_name == 'report_bannerLP_metrics':
        return 0
    elif query_name == 'report_latest_campaign':
        return 1
    elif query_name == 'report_banner_impressions_by_hour':
        return 0
    elif query_name == 'report_lp_views_by_hour':
        return 0
    elif query_name == 'report_banner_metrics_minutely':
        return 0
    elif query_name == 'report_bannerLP_metrics_minutely':
        return 0
    elif query_name == 'report_LP_metrics_minutely':
        return 0
    elif query_name == 'report_campaign_metrics_minutely':
        return 0
    elif query_name == 'report_campaign_metrics_minutely_total':
        return 0
    elif query_name == 'report_campaign_totals':
        return 0
    elif query_name == 'report_campaign_metrics_minutely_all' or query_name == 'report_banner_metrics_minutely_all' or query_name == 'report_lp_metrics_minutely_all':
        return 0
    else:
        return -1



def get_metric_index(query_name, metric_name):
    if query_name == 'report_campaign_logs_by_min':
        if metric_name == 'click_rate':
            return 9
    elif query_name == 'report_campaign_logs_by_hr':
        if metric_name == 'click_rate':
            return 8
    elif query_name == 'report_contribution_tracking':
        if metric_name == 'converted_amount':
            return 4
    elif query_name == 'report_bannerLP_metrics':
        if metric_name == 'total_impressions':
            return 2
        elif metric_name == 'impressions':
            return 3
        elif metric_name == 'views':
            return 4
        elif metric_name == 'clicks':
            return 5
        elif metric_name == 'donations':
            return 6
        elif metric_name == 'amount':
            return 7
        elif metric_name == 'click_rate':
            return 8
        elif metric_name == 'completion_rate':
            return 9
        elif metric_name == 'don_per_imp':
            return 10
        elif metric_name == 'amt_per_imp':
            return 11
        elif metric_name == 'don_per_view':
            return 12
        elif metric_name == 'amt_per_view':
            return 13
        elif metric_name == 'amt_per_donation':
            return 14
        elif metric_name == 'max_amt':
            return 15
        elif metric_name == 'pp_don':
            return 16
        elif metric_name == 'cc_don':
            return 17
        elif metric_name == 'paypal_click_thru':
            return 18
        elif metric_name == 'creditcard_click_thru':
            return 19
        else:
            return -1
    elif query_name == 'report_banner_confidence':
        if metric_name == 'click_rate':
            return 7
        elif metric_name == 'don_per_imp':
            return 9
        elif metric_name == 'amt_per_imp':
            return 10
        elif metric_name == 'amt50_per_imp':
            return 14
        elif metric_name == 'amt100_per_imp':
            return 15
        else:
            return -1
    elif query_name == 'report_LP_confidence':
        if metric_name == 'completion_rate':
            return 5
        elif metric_name == 'don_per_view':
            return 6
        elif metric_name == 'amt_per_view':
            return 7
        elif metric_name == 'amt_per_donation':
            return 8
        elif metric_name == 'amt50_per_view':
            return 9
        elif metric_name == 'amt100_per_view':
            return 10
        else:
            return -1
    elif query_name == 'report_bannerLP_confidence':
        if metric_name == 'click_rate':
            return 7
        elif metric_name == 'completion_rate':
            return 8
        elif metric_name == 'don_per_imp':
            return 9
        elif metric_name == 'amt_per_imp':
            return 10
        elif metric_name == 'don_per_view':
            return 11
        elif metric_name == 'amt_per_view':
            return 12
        elif metric_name == 'amt_per_donation':
            return 13
        elif metric_name == 'amt50_per_imp':
            return 14
        elif metric_name == 'amt100_per_imp':
            return 15
        else:
            return -1
    elif query_name == 'report_LP_metrics_minutely':
        if metric_name == 'views':
            return 2
        elif metric_name == 'donations':
            return 4
        elif metric_name == 'amount50':
            return 5
        elif metric_name == 'don_per_view':
            return 7
        elif metric_name == 'amt50_per_view':
            return 9
        else:
            return -1
    elif query_name == 'report_banner_metrics_minutely':
        if metric_name == 'imp':
            return 2
        elif metric_name == 'donations':
            return 4
        elif metric_name == 'amount50':
            return 6
        elif metric_name == 'don_per_imp':
            return 8
        elif metric_name == 'amt50_per_imp':
            return 10
        elif metric_name == 'click_rate':
            return 7
        else:
            return -1
    elif query_name == 'report_campaign_metrics_minutely':
        if metric_name == 'donations':
            return 3
        elif metric_name == 'views':
            return 2
        else:
            return -1
    elif query_name == 'report_campaign_metrics_minutely_total':
        if metric_name == 'donations':
            return 3
        elif metric_name == 'views':
            return 2
        else:
            return -1
    elif query_name == 'report_campaign_totals':
        if metric_name == 'donations':
            return 2
        elif metric_name == 'name':
            return 0
        elif metric_name == 'earliest_timestamp':
            return 3
        else:
            return -1
    
    elif query_name == 'report_campaign_metrics_minutely_all' or query_name == 'report_banner_metrics_minutely_all' or query_name == 'report_lp_metrics_minutely_all':
        if metric_name == 'donations':
            return 2
        elif metric_name == 'clicks':
            return 3
        else:
            return -1
    
    elif query_name == 'report_bannerLP_metrics_minutely':
        if metric_name == 'imp':
            return 2
        elif metric_name == 'views':
            return 3
        elif metric_name == 'donations':
            return 4
        elif metric_name == 'amount50':
            return 6
        elif metric_name == 'don_per_imp':
            return 8
        elif metric_name == 'amt50_per_imp':
            return 10
        elif metric_name == 'don_per_view':
            return 11
        elif metric_name == 'amt50_per_view':
            return 13
        elif metric_name == 'click_rate':
            return 7
        else:
            return -1
        
    else:
        return 'no such table'
        
def get_plot_title(query_name):
    if query_name == 'report_banner_impressions_by_hour':
        return 'Banner Impressions Over the Past 24 Hours'
    elif query_name == 'report_lp_views_by_hour':
        return 'Landing Page Views Over the Past 24 Hours'
    else:
        return 'no such table'
    
def get_plot_ylabel(query_name):
    if query_name == 'report_banner_impressions_by_hour':
        return 'IMPRESSIONS'
    elif query_name == 'report_lp_views_by_hour':
        return 'VIEWS'
    else:
        return'no such table'
    
def get_metric_full_name(metric_name):
    if metric_name == 'imp':
        return 'Banner Impressions'
    elif metric_name == 'views':
        return 'Landing Page Views'
    elif metric_name == 'don_per_imp':
        return 'Donations per Impression'
    elif metric_name == 'don_per_view':
        return 'Donations per View'
    elif metric_name == 'amt50_per_imp':
        return 'Amount50 per Impression'
    elif metric_name == 'amt50_per_view':
        return 'Amount50 per View'
    elif metric_name == 'amount50':
        return 'Amount50'
    elif metric_name == 'donations':
        return 'Donations'
    elif metric_name == 'amt_per_imp':
        return 'Amount per Impression'
    elif metric_name == 'amt_per_view':
        return 'Amount per View'
    elif metric_name == 'amount':
        return 'Amount'
    elif metric_name == 'click_rate':
        return 'Banner Click Rate'
    else:
        return'no such metric'


def get_metric_data_type(metric_name, elem):
    
    if metric_name == 'ts' or metric_name == 'day_hr' or metric_name == 'utm_campaign' or metric_name == 'utm_source' or metric_name == 'landing_page':
        return elem
    elif metric_name == 'imp' or metric_name == 'impressions' or metric_name == 'views' or metric_name == 'donations' or metric_name ==  'clicks' or metric_name == 'total_clicks':
        return str(int(elem))
    elif metric_name == 'amt50_per_view' or metric_name == 'amt50_per_imp' or metric_name == 'don_per_view' or metric_name == 'don_per_imp' or metric_name == 'amt_per_imp' or metric_name == 'amt_per_view' or metric_name == 'click_rate':
        return "%.6f" % elem
    elif metric_name == 'amount' or metric_name == 'amount50' or metric_name == 'amount100':
        return "%.2f" % elem
    else:
        return'no such metric'
    