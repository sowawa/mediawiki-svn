

select

if(dt_min < 10, concat(dt_hr, '0', dt_min,'00'), concat(dt_hr, dt_min,'00')) as ts,
landing_page,
donations, 
clicks
	
from

(select 

DATE_FORMAT(ts,'%sY%sm%sd%sH') as dt_hr,
FLOOR(MINUTE(ts) / %s) * %s as dt_min,
SUBSTRING_index(substring_index(utm_source, '.', 2),'.',-1) as landing_page,
sum(not isnull(civicrm.civicrm_contribution.id)) as donations,
sum(civicrm.civicrm_contribution.id) as clicks

from

drupal.contribution_tracking LEFT JOIN civicrm.civicrm_contribution
ON (drupal.contribution_tracking.contribution_id = civicrm.civicrm_contribution.id)

where ts >= '%s' and ts < '%s'
group by 1,2,3) as ecomm


order by 2,1 asc;
