


select 

SUBSTRING_index(substring_index(utm_source, '.', 2),'.',-1) as lp

from drupal.contribution_tracking left join civicrm.civicrm_contribution ON (drupal.contribution_tracking.contribution_id = civicrm.civicrm_contribution.id)

where ts >= '%s' and ts < '%s'  and utm_campaign REGEXP '%s'

group by 1;

