
select

if(lp.dt_min < 10, concat(lp.dt_hr, '0', lp.dt_min,'00'), concat(lp.dt_hr, lp.dt_min,'00')) as day_hr,
lp.landing_page,
views,
total_clicks,
donations,
amount50,
donations / total_clicks as completion_rate,
donations / views as don_per_view,
amount / views as amt_per_view,
amount50 / views as amt50_per_view

from

(select 
DATE_FORMAT(request_time,'%sY%sm%sd%sH') as dt_hr,
FLOOR(MINUTE(request_time) / %s) * %s as dt_min,
landing_page,
count(*) as views,
utm_campaign

from landing_page_requests

where request_time >=  '%s' and request_time < '%s'
and utm_campaign REGEXP '%s'
group by 1,2,3) as lp

join

(select 
DATE_FORMAT(receive_date,'%sY%sm%sd%sH') as hr,
FLOOR(MINUTE(receive_date) / %s) * %s as dt_min,
SUBSTRING_index(substring_index(utm_source, '.', 2),'.',-1) as landing_page,
count(*) as total_clicks,
sum(not isnull(drupal.contribution_tracking.contribution_id)) as donations,
sum(total_amount) as amount,
sum(if(total_amount > 50, 50, total_amount)) as amount50
from
drupal.contribution_tracking LEFT JOIN civicrm.civicrm_contribution
ON (drupal.contribution_tracking.contribution_id = civicrm.civicrm_contribution.id)
where receive_date >=  '%s' and receive_date < '%s'
and utm_campaign REGEXP '%s'
group by 1,2,3) as ecomm

on ecomm.landing_page  = lp.landing_page and ecomm.hr = lp.dt_hr and ecomm.dt_min = lp.dt_min

where lp.utm_campaign REGEXP '%s'
group by 1,2
-- having views > 1000 and donations > 10
order by 1 asc;





