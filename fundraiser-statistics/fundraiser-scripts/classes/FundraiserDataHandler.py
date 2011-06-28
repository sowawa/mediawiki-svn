

"""

Implements the stateful FundraiserDataHandler class that co-ordinates the data loading and reporting particular to the fundraiser

!! MODIFY -- make singleton ??? !!


"""

__author__ = "Ryan Faulkner"
__revision__ = "$Rev$"
__date__ = "May 8th, 2011"


import sys


""" Query Types """
_QTYPE_BANNER_ = 'banner'
_QTYPE_LP_ = 'lp'
_QTYPE_BANNER_LP_ = 'bannerlp'
_QTYPE_CAMPAIGN_ = 'campaign'
_QTYPE_TIME_ = 'minutely'


""" Test Types """
_TESTTYPE_BANNER_ = 'banner'
_TESTTYPE_LP_ = 'lp'
_TESTTYPE_BANNER_LP_ = 'bannerlp'



""" Column Types """
_COLTYPE_RATE_ = 'rate'
_COLTYPE_AMOUNT_ = 'amount'
_COLTYPE_KEY_ = 'key'
_COLTYPE_TIME_ = 'time'


""" Defines the metric type of each column in the interval reporting queries  """
_bannerlp_interval_reporting_col_types_ = [_COLTYPE_TIME_, _COLTYPE_KEY_, _COLTYPE_AMOUNT_, _COLTYPE_AMOUNT_, _COLTYPE_AMOUNT_, _COLTYPE_AMOUNT_, _COLTYPE_AMOUNT_, _COLTYPE_RATE_, _COLTYPE_RATE_, _COLTYPE_RATE_, _COLTYPE_RATE_, _COLTYPE_RATE_, _COLTYPE_RATE_, _COLTYPE_RATE_]
_banner_interval_reporting_col_types_ = [_COLTYPE_TIME_, _COLTYPE_KEY_, _COLTYPE_AMOUNT_, _COLTYPE_AMOUNT_, _COLTYPE_AMOUNT_, _COLTYPE_AMOUNT_, _COLTYPE_AMOUNT_, _COLTYPE_RATE_, _COLTYPE_RATE_, _COLTYPE_RATE_, _COLTYPE_RATE_]
_lp_interval_reporting_col_types_ = [_COLTYPE_TIME_, _COLTYPE_KEY_, _COLTYPE_AMOUNT_, _COLTYPE_AMOUNT_, _COLTYPE_AMOUNT_, _COLTYPE_AMOUNT_, _COLTYPE_RATE_, _COLTYPE_RATE_, _COLTYPE_RATE_, _COLTYPE_RATE_] 


def get_col_types(query_type):
    
    if query_type == _TESTTYPE_BANNER_:
        return _banner_interval_reporting_col_types_
    elif query_type == _TESTTYPE_LP_:
        return _lp_interval_reporting_col_types_
    elif query_type == _TESTTYPE_BANNER_LP_:
        return _bannerlp_interval_reporting_col_types_
    
    return []

"""
    Get Test type from campaign.  The logic in this method will evolve as new ways to classify test types are developed
            
"""
def get_test_type(utm_campaign, start_time, end_time, campaign_reporting_loader):
    
    campaign_reporting_loader._query_type_ = _TESTTYPE_BANNER_
    banner_list = campaign_reporting_loader.run_query({'utm_campaign' : utm_campaign, 'start_time' : start_time, 'end_time' : end_time})
    
    campaign_reporting_loader._query_type_ = _TESTTYPE_LP_
    lp_list = campaign_reporting_loader.run_query({'utm_campaign' : utm_campaign, 'start_time' : start_time, 'end_time' : end_time})
    
    if len(lp_list) > 1 and len(banner_list) > 1:
        
        """ In case of BannerLP test concatenate banner lp names -- this """
        new_artifacts = list()

        for i in range(len(banner_list)):
            new_artifacts.append(banner_list[i] + '-' + lp_list[i])
        
        return _TESTTYPE_BANNER_LP_, new_artifacts
    if len(banner_list) > 1:
        return _TESTTYPE_BANNER_, banner_list
    elif len(lp_list) > 1:
        return _TESTTYPE_LP_, lp_list
    elif len(lp_list) > len(banner_list):
        return _TESTTYPE_LP_, lp_list
    elif len(banner_list) > len(lp_list):
        return _TESTTYPE_BANNER_, banner_list
    else:
        """ !! MODIFY -- hardcode for now, insert custom logic !! """
        if utm_campaign == '20101228JA075':
            return _TESTTYPE_BANNER_, banner_list 
        elif utm_campaign == '20101230JA089_US':
             return _TESTTYPE_BANNER_, banner_list
         
        return _TESTTYPE_BANNER_, banner_list 
    
"""
    Return the metrics measured for a particular type of test
            
"""
def get_test_type_metrics(test_type):
    
    if test_type == _TESTTYPE_BANNER_:
        test_metrics = ['imp', 'click_rate', 'donations', 'don_per_imp', 'amt50_per_imp', ]
    if test_type == _TESTTYPE_LP_:
        test_metrics = ['views', 'donations', 'don_per_view', 'amt50_per_view']
    if test_type == _TESTTYPE_BANNER_LP_:
        test_metrics = ['imp', 'views', 'click_rate', 'donations', 'don_per_imp', 'amt50_per_imp', 'don_per_view', 'amt50_per_view']
        
    return test_metrics


"""
    Ordering for columns as SQL output
        
    @param keys: list of column names
    @type keys: list of strings    
"""
def order_column_keys(keys):
    
    new_keys = list()
    
    """ """
    order = ['ts', 'day_hr', 'utm_campaign', 'utm_source', 'banner', 'landing_page', 'impressions', 'imp', 'views', 'donations', 'clicks', 'total_clicks', 'amount','amount50', 'amount100', 'click_rate', 'conversion_rate', 'don_per_imp', 'amt_per_imp', 'amt50_per_imp', 'don_per_view', 'amt_per_view', 'amt50_per_view']
    
    for i in range(len(order)):
        if order[i] in keys:
            new_keys.append(order[i])
        
    return new_keys
