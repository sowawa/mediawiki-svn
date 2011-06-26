<?php
/**
 * Setup for SignupAPI extension, a special page that cleans up SpecialUserLogin
 * from signup related stuff & adds an API for signup
 *
 * @file
 * @ingroup Extensions
 * @author Akshay Agarwal, akshayagarwal.in
 * @copyright © 2011 Akshay Agarwal
 * @licence GNU General Public Licence 2.0 or later
 */

if( !defined( 'MEDIAWIKI' ) ) {
	echo( "This file is an extension to the MediaWiki software and cannot be used standalone.\n" );
	die( 1 );
}

$wgExtensionCredits['specialpage'][] = array(
        'path' =>  __FILE__,
        'name' => 'SignupAPI',
        'version' => 1.0,
        'author' => 'Akshay Agarwal',
        'url' => 'http://www.mediawiki.org/wiki/Extension:SignupAPI',
	'descriptionmsg' => 'signupapi-desc',
);

$dir = dirname(__FILE__);
$wgExtensionMessagesFiles['SignupAPI'] = $dir . '/SignupAPI.i18n.php';
$wgMyExtensionIncludes = $dir . '/includes';
 
## Special page class
$wgAutoloadClasses['SignupForm'] 
  = $wgMyExtensionIncludes . '/SpecialUserSignup.php';

$wgAutoloadClasses['ApiSignup'] 
  = $wgMyExtensionIncludes . '/APISignup.php';
$wgSpecialPages['UserSignup'] = 'SignupForm';

$wgAPIModules['signup'] = 'ApiSignup';
