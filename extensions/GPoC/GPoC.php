<?php
/**
 * Proof of Concept for Yuvi Panda's 2011 GSoC
 *
 * @file
 * @ingroup Extensions
 * @author Yuvi Panda, http://yuvi.in
 * @copyright © 2011 Yuvaraj Pandian (yuvipanda@yuvi.in)
 * @licence Modified BSD License
 */

if( !defined( 'MEDIAWIKI' ) ) {
	echo( "This file is an extension to the MediaWiki software and cannot be used standalone.\n" );
	die( 1 );
}

// Extension credits that will show up on Special:Version

// Set up the new special page
$dir = dirname( __FILE__ ) . '/';

$wgAutoloadClasses['GPoCHooks'] = $dir . 'GPoC.hooks.php';

$wgHooks['ArticleSaveComplete'][] = 'GPoCHooks::ArticleSaveComplete';
$wgHooks['LoadExtensionSchemaUpdates'][] = 'GPoCHooks::SetupSchema';

// Configuration
