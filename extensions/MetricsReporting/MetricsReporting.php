<?php

if ( !defined( 'MEDIAWIKI' ) ) {
	die();
}

$wgExtensionCredits['other'][] = array(
	'path' => __FILE__,
	'name' => 'MetricsReporting',
	'url' => 'http://www.mediawiki.org/wiki/Extension:MetricsReporting',
	'author' => 'Sam Reed',
	'description' => 'Api for Wikimedia Metrics Reporting output',
);

$wgMetricsDBserver         = '';
//$wgMetricsDBport           = 5432;
$wgMetricsDBname           = '';
$wgMetricsDBuser           = '';
$wgMetricsDBpassword       = '';
//$wgMetricsDBtype           = 'mysql';

$dir = dirname( __FILE__ ) . '/';

$wgAutoloadClasses['ApiAnalytics'] = $dir . 'ApiAnalytics.php';
$wgAPIModules['analytics'] = 'ApiAnalytics';

$wgAutoloadClasses['ApiAnalyticsBase'] = $dir . 'ApiAnalyticsBase.php';
