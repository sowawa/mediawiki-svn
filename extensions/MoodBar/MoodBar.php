<?php
/**
 * MoodBar extension
 * Allows specified users to send their "mood" back to the site operator.
 */

$wgExtensionCredits['other'][] = array(
	'author' => array( 'Andrew Garrett', 'Timo Tijhof' ),
	'descriptionmsg' => 'moodbar-desc',
	'name' => 'MoodBar',
	'url' => 'http://www.mediawiki.org/wiki/MoodBar',
	'version' => '0.1',
	'path' => __FILE__,
);

// Object model
$wgAutoloadClasses['MBFeedbackItem'] = dirname(__FILE__).'/FeedbackItem.php';

// API
$wgAutoloadClasses['ApiMoodBar'] = dirname(__FILE__).'/ApiMoodBar.php';
$wgAPIModules['moodbar'] = 'ApiMoodBar';

// Hooks
$wgAutoloadClasses['MoodBarHooks'] = dirname(__FILE__).'/MoodBar.hooks.php';
$wgHooks['BeforePageDisplay'][] = 'MoodBarHooks::onPageDisplay';
$wgHooks['LoadExtensionSchemaUpdates'][] = 'MoodBarHooks::onLoadExtensionSchemaUpdates';

// Internationalisation
$wgExtensionMessagesFiles['MoodBar'] = dirname(__FILE__).'/MoodBar.i18n.php';

// Resources
$mbResourceTemplate = array(
	'localBasePath' => dirname(__FILE__) . '/modules',
	'remoteExtPath' => 'MoodBar/modules'
);

$wgResourceModules['ext.moodBar'] = $mbResourceTemplate + array(
	'styles' => 'ext.moodBar/ext.moodBar.css',
	'scripts' => 'ext.moodBar/ext.moodBar.js',
	'messages' => array(
		'moodbar-trigger-using',
		'tooltip-p-moodbar-trigger-using',
		'moodbar-trigger-feedback',
		'tooltip-p-moodbar-trigger-feedback',
	),
	'position' => 'top',
);
