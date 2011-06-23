


select

lp.utm_source,
lp.landing_page,
impressions as total_impressions,
impressions * (views / total_views) as impressions,
views as views,
total_clicks as clicks,
donations as donations,
amount as amount,
(views / impressions) * (total_views / views) as click_rate,
donations / total_clicks as completion_rate,
round((donations / impressions) * (total_views / views), 6) as don_per_imp,
(amount / impressions) * (total_views / views) as amt_per_imp,
donations / views as don_per_view,
amount / views as amt_per_view,
amount / donations  as amt_per_donation,
(amount50 / impressions) * (total_views / views) as amt50_per_imp,
(amount100 / impressions) * (total_views / views) as amt100_per_imp

from

(select 
utm_source, 
sum(counts) as impressions
from banner_impressions 
where on_minute > '%s' and on_minute < '%s' 
and utm_source REGEXP '%s'
group by 1) as imp

join

(select 
utm_source, 
landing_page, 
count(*) as views
from landing_page_requests
where request_time >= '%s' and request_time < '%s'
and utm_campaign REGEXP '%s'
group by 1,2) as lp

on imp.utm_source =  lp.utm_source

join

(select 
utm_source, 
count(*) as total_views
from landing_page_requests
where request_time >= '%s' and request_time < '%s'
and utm_source REGEXP '%s'
group by 1) as lp_tot

on imp.utm_source =  lp_tot.utm_source

left join

(select 
SUBSTRING_index(substring_index(utm_source, '.', 2),'.',1) as banner,
SUBSTRING_index(substring_index(utm_source, '.', 2),'.',-1) as landing_page,
count(*) as total_clicks,
sum(not isnull(contribution_tracking.contribution_id)) as donations,
sum(total_amount) AS amount,
sum(if(total_amount > 50, 50, total_amount)) as amount50,
sum(if(total_amount > 100, 100, total_amount)) as amount100
from
drupal.contribution_tracking LEFT JOIN civicrm.civicrm_contribution 
ON (contribution_tracking.contribution_id = civicrm.civicrm_contribution.id)
where receive_date >= '%s' and receive_date < '%s'
and utm_campaign REGEXP '%s'
and utm_source REGEXP '%s'
group by 1,2) as ecomm

on ecomm.banner = lp.utm_source and ecomm.landing_page = lp.landing_page

group by 1
-- having impressions > 100000 and donations > 10;
