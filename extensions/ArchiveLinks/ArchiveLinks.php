<?php
/**
 * This is an extension to archive preemptively archive external links so that
 * in the even they go down a backup will be available.
 */

if ( !defined( 'MEDIAWIKI' ) ) {
	echo( "This file is an extension to the MediaWiki software and cannot be used standalone.\n" );
	die( 1 );
}

error_reporting ( E_ALL | E_STRICT );

$path = dirname( __FILE__ );

$wgExtensionMessagesFiles['ArchiveLinks'] =  "$path/ArchiveLinks.i18n.php";
$wgExtensionMessagesFiles['ModifyBlacklist'] = "$path/ArchiveLinks.i18n.php";

$wgAutoloadClasses['ArchiveLinks'] = "$path/ArchiveLinks.class.php";
$wgAutoloadClasses['SpecialModifyBlacklist'] = "$path/SpecialModifyBlacklist.php";
    
$wgHooks['ArticleSaveComplete'][] = 'ArchiveLinks::queueExternalLinks';
$wgHooks['LinkerMakeExternalLink'][] = 'ArchiveLinks::rewriteLinks';

$wgSpecialPages['ModifyBlacklist'] = 'SpecialModifyBlacklist';

$wgArchiveLinksConfig = array (
    'archive_service' => 'wikiwix',
    'use_multiple_archives' => false,
    'run_spider_in_loop' => false,
);