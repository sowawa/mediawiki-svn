<?php

/**
 * Babel Extension
 *
 * Adds a parser function to allow automated generation of a babel userbox
 * column with the ability to include custom templates.
 *
 * @file
 * @ingroup Extensions
 *
 * @link http://www.mediawiki.org/wiki/Extension:Babel
 *
 * @author Robert Leverington <robert@rhl.me.uk>
 * @copyright Copyright © 2008 - 2011 Robert Leverington.
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License 2.0 or later
 */

if ( !defined( 'MEDIAWIKI' ) ) die( 'Invalid entry point.' );

$wgExtensionCredits['parserhook'][] = array(
	'path'            => __FILE__,
	'name'            => 'Babel',
	'version'         => '1.6.0',
	'author'          => 'Robert Leverington',
	'url'             => 'http://www.mediawiki.org/wiki/Extension:Babel',
	'descriptionmsg'  => 'babel-desc',
);

$wgHooks['ParserFirstCallInit'][] = 'BabelStatic::Setup';
$wgHooks['AbortNewAccount'][]     = 'BabelAutoCreate::RegisterAbort';

$dir = dirname( __FILE__ );

$wgExtensionMessagesFiles['Babel']      = $dir . '/Babel.i18n.php';
$wgExtensionMessagesFiles['BabelMagic'] = $dir . '/Babel.i18n.magic.php';

$wgAutoloadClasses['Babel']              = $dir . '/Babel.class.php';
$wgAutoloadClasses['BabelLanguageCodes'] = $dir . '/BabelLanguageCodes.class.php';
$wgAutoloadClasses['BabelStatic']        = $dir . '/BabelStatic.class.php';
$wgAutoloadClasses['BabelAutoCreate']    = $dir . '/BabelAutoCreate.class.php';

// Configuration setttings.
// A boolean (true or false) indicating whether users should be entered in to a category if they use xx-0; defaults to false.
$wgBabelUseLevelZeroCategory = false;
// A boolean (true or false) indicating whether categories for abilities should be used; defaults to false.
$wgBabelUseSimpleCategories  = false;
// A boolean (true or false) indicating whether main categories featuring all users who specify a level for that language should be added to a xx category; defaults to true.
$wgBabelUseMainCategories    = true;
// Language names and codes constant database files, the defaults should suffice.
$wgBabelLanguageCodesCdb     = $dir . '/codes.cdb';
$wgBabelLanguageNamesCdb     = $dir . '/names.cdb';
// Format of category names - variables: %code% %wikiname% %nativename%
$wgBabelCategoryNames = array(
	0 => '%code%-0',
	1 => '%code%-1',
	2 => '%code%-2',
	3 => '%code%-3',
	4 => '%code%-4',
	5 => '%code%-5',
	'N' => '%code%-N',
	'main' => '%code%'
);

/* Other settings, to be made in-wiki:
MediaWiki:Babel-template-prefix and MediaWiki:Babel-template-suffix
    The prefix and suffix to be prepended or appended to the template name when one is being included.
MediaWiki:Babel-portal-prefix and MediaWiki:Babel-portal-suffix
    The prefix and suffix to be prepended or appended to the target of the link from the language code.
*/