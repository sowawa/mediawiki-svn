

select

if(lp_tot.dt_min < 10, concat(lp_tot.dt_hr, '0', lp_tot.dt_min,'00'), concat(lp_tot.dt_hr, lp_tot.dt_min,'00')) as ts,
'%s' as pipeline_name,
views,
donations
	
from

(select 
DATE_FORMAT(request_time,'%sY%sm%sd%sH') as dt_hr,
FLOOR(MINUTE(request_time) / %s) * %s as dt_min,
count(*) as views
from landing_page_requests
where request_time >=  '%s' and request_time < '%s' and utm_campaign REGEXP '%s'
group by 1,2) as lp_tot

left join

(select 

DATE_FORMAT(receive_date,'%sY%sm%sd%sH') as hr,
FLOOR(MINUTE(receive_date) / %s) * %s as dt_min,
sum(not isnull(drupal.contribution_tracking.contribution_id)) as donations

from

drupal.contribution_tracking LEFT JOIN civicrm.civicrm_contribution
ON (drupal.contribution_tracking.contribution_id = civicrm.civicrm_contribution.id)

where receive_date >=  '%s' and receive_date < '%s' and utm_campaign REGEXP '%s'
group by 1,2) as ecomm

on ecomm.hr = lp_tot.dt_hr and ecomm.dt_min = lp_tot.dt_min

group by 1,2

order by 1 asc;
