<?php
if ( !defined( 'MEDIAWIKI' ) ) die();
/**
 * @copyright Copyright © 2010 Michael Dale <michael.dale@kaltura.com>
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License 2.0 or later
 */

$wgExtensionCredits['other'][] = array(
	'path' => __FILE__,
	'name' => 'JS2Support',
	'url' => 'http://www.mediawiki.org/wiki/Extension:JS2Support',
	'author' => array( 'Michael Dale' ),
	'descriptionmsg' => 'js2support-desc',
);

$js2Dir = dirname( __FILE__ ) . '/';
$wgExtensionMessagesFiles[ 'JS2Support' ] = $js2Dir . 'JS2Support.i18n.php';


/**
 * Setup the js2 extension:
 */
$wgExtensionFunctions[] = 'wfReMapOutputPage';
function wfReMapOutputPage(){
	global $wgOut, $js2Dir, $wgAutoloadClasses, $wgScriptLoaderNamedPaths;

	// Remap output page as part of the extension setup
	$wgOut = new StubObject( 'wgOut', 'ScriptLoaderOutputPage' );

	$wgAutoloadClasses[ 'ScriptLoaderOutputPage' ] = $js2Dir . 'ScriptLoaderOutputPage.php';

	// Include all the mediaWiki autoload classes:
	require( $js2Dir . 'JS2AutoLoader.php');
}

/**
 * MakeGlobalVariablesScript hook ( add the wgScriptLoaderPath var )
 */
$wgHooks['MakeGlobalVariablesScript'][] = 'js2SupportAddJSVars';
function js2SupportAddJSVars( &$vars ) {
	global $wgExtensionAssetsPath;
	$vars = array_merge( $vars,
		array(
			'wgScriptLoaderLocation' => $wgExtensionAssetsPath . 'JS2Support/mwScriptLoader.php'
		)
	);
	return true;
}

/****************************
* Configuration
* Could eventually go into DefaultSettings.php
*****************************/

/*
 * Simple global to tell extensions JS2 support is available
 */
$wgEnableJS2system = true;


/**
 * For defining the location of loader.js files of
 * for js-modules. ( ie modules hosted inside of extensions )
 */
$wgExtensionJavascriptLoader = array();

/**
 * The set of script-loader Named Paths, populated via extensions and javascript module loaders
 */
$wgScriptLoaderNamedPaths = array();

/*
 * $wgEnableScriptLoader; If the script loader should be used to group all javascript requests.
 * more about the script loader: http://www.mediawiki.org/wiki/ScriptLoader
 *
 */
$wgEnableScriptLoader = false;

/**
 * $wgScriptModifiedCheck should run a file modified check on javascript files when
 * generating unique request ids for javascript include using the script-loader
 *
 * note this will only check core scripts that are directly included on the page.
 * (not scripts loaded after the initial page display since after initial page
 * display scripts inherit the unique request id)
 *
 * You can also update $wgStyleVersion
 */
$wgScriptModifiedFileCheck = true;

/**
 * The google closure compiler path
 */
$wgClosureCompilerPath = false;

/**
 * The path to java run time environment
 */
$wgJavaPath = false;

/**
 *  The level of optimization for the closure compiler
 *  NOTE: SIMPLE_OPTIMIZATIONS is recommended since it preserves
 *  functionality of syntactically valid JavaScript
 *
 *  for more info see: http://code.google.com/closure/compiler/docs/compilation_levels.html
 */
$wgClosureCompilerLevel = 'SIMPLE_OPTIMIZATIONS';

/*
 * $wgScriptModifiedMsgCheck Checks MediaWiki NS for latest messege
 * Revision for generating the request id.
 *
 */
$wgScriptModifiedMsgCheck = false;


/**
 * boolean; if we should enable javascript localization (it loads mw.addMessages json
 * call with mediaWiki msgs)
 */
$wgEnableScriptLocalization = true;

/**
 * Path for mwEmbed normally js/mwEmbed/
 */
$wgMwEmbedDirectory = "extensions/JS2Support/mwEmbed";

/**
 * Enables javascript on debugging
 * forces fresh non-minified copies of javascript to be generated
 * on every request.
 */
$wgDebugJavaScript = false;
