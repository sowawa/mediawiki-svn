


select

lp.landing_page,
views as views,
total_clicks as clicks,
donations as donations,
amount as amount,
donations / total_clicks as completion_rate,
donations / views as don_per_view,
amount / views as amt_per_view,
amount / donations  as amt_per_donation,
amount50 / views as amt50_per_view

from

(select 
landing_page, 
utm_campaign,
count(*) as views
from landing_page_requests
where request_time >= '%s' and request_time < '%s'
and utm_campaign REGEXP '%s'
and landing_page REGEXP '%s'
group by 1,2) as lp

left join

(select 
SUBSTRING_index(substring_index(utm_source, '.', 2),'.',-1) as landing_page,
utm_campaign,
count(*) as total_clicks,
sum(not isnull(contribution_tracking.contribution_id)) as donations,
sum(total_amount) AS amount,
sum(if(total_amount > 50, 50, total_amount)) as amount50
from
drupal.contribution_tracking LEFT JOIN civicrm.civicrm_contribution 
ON (contribution_tracking.contribution_id = civicrm.civicrm_contribution.id)
where receive_date >= '%s' and receive_date < '%s'
and utm_campaign REGEXP '%s'
and SUBSTRING_index(substring_index(utm_source, '.', 2),'.',-1) REGEXP '%s'
group by 1,2) as ecomm

on ecomm.landing_page = lp.landing_page and ecomm.utm_campaign = lp.utm_campaign

group by 1;
