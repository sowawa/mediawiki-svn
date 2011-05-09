


select 

faulkner.test.test_name,
drupal.contribution_tracking.utm_campaign,
sum(not isnull(contribution_tracking.contribution_id)) as donations,
min(receive_date) as earliest_timestamp,
if(not isnull(faulkner.test.test_name),1,0) as report_exists

from 
drupal.contribution_tracking left join civicrm.civicrm_contribution ON (drupal.contribution_tracking.contribution_id = civicrm.civicrm_contribution.id)
left join faulkner.test on faulkner.test.utm_campaign = drupal.contribution_tracking.utm_campaign

where receive_date >=  %s and receive_date < %s  

group by 2
order by 3 desc;

