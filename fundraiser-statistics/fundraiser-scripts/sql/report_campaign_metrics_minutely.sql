

select

if(lp.dt_min < 10, concat(lp.dt_hr, '0', lp.dt_min,'00'), concat(lp.dt_hr, lp.dt_min,'00')) as ts,
concat('%s', ' - ',  lp.banner,' - ', lp.landing_page) as pipeline_name,
views,
donations
	
from



(select 
DATE_FORMAT(request_time,'%sY%sm%sd%sH') as dt_hr,
FLOOR(MINUTE(request_time) / %s) * %s as dt_min,
utm_source as banner, 
landing_page,
count(*) as views
from landing_page_requests
where request_time >=  '%s' and request_time < '%s' and utm_campaign REGEXP '%s'
group by 1,2,3,4) as lp

left join

(select 

DATE_FORMAT(receive_date,'%sY%sm%sd%sH') as hr,
FLOOR(MINUTE(receive_date) / %s) * %s as dt_min,
SUBSTRING_index(substring_index(utm_source, '.', 2),'.',1) as banner,
SUBSTRING_index(substring_index(utm_source, '.', 2),'.',-1) as landing_page,
sum(not isnull(drupal.contribution_tracking.contribution_id)) as donations

from

drupal.contribution_tracking LEFT JOIN civicrm.civicrm_contribution
ON (drupal.contribution_tracking.contribution_id = civicrm.civicrm_contribution.id)

where receive_date >=  '%s' and receive_date < '%s' and utm_campaign REGEXP '%s'
group by 1,2,3,4) as ecomm

on ecomm.banner = lp.banner and ecomm.landing_page = lp.landing_page and ecomm.hr = lp.dt_hr and ecomm.dt_min = lp.dt_min

group by 1,2

order by 1 asc;
