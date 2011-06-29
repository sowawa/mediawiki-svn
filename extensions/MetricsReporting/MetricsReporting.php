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

$dir = dirname( __FILE__ ) . '/';

$wgAutoloadClasses['ApiAnalytics'] = $dir . 'ApiAnalytics.php';
$wgAPIModules['analytics'] = 'ApiAnalytics';
