<?php
/**
 * LinkSuggest
 * This extension provides the user with article title suggestions as he types
 * a link in wikitext.
 *
 * @file
 * @ingroup Extensions
 * @version 1.6 (r32133)
 * @author Inez Korczyński <korczynski at gmail dot com>
 * @author Bartek Łapiński <bartek at wikia-inc dot com>
 * @author Łukasz Garczewski (TOR) <tor at wikia-inc dot com>
 * @author Jesús Martínez Novo <martineznovo at gmail dot com>
 * @author Jack Phoenix <jack@countervandalism.net>
 * @copyright Copyright © 2008-2009, Wikia Inc.
 * @copyright Copyright © 2011 Jesús Martínez Novo
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License 2.0 or later
 * @link http://www.mediawiki.org/wiki/Extension:LinkSuggest Documentation
 */
if( !defined( 'MEDIAWIKI' ) ) {
	die( 'This is not a valid entry point to MediaWiki.' );
}

// Extension credits that will show up on Special:Version
$wgExtensionCredits['other'][] = array(
	'path' => __FILE__,
	'name' => 'LinkSuggest',
	'version' => '1.6',
	'author' => array(
		'Inez Korczyński', 'Bartek Łapiński', 'Łukasz Garczewski',
		'Jesús Martínez Novo', 'Jack Phoenix'
	),
	'descriptionmsg' => 'linksuggest-desc',
	'url' => 'http://www.mediawiki.org/wiki/Extension:LinkSuggest',
);

// Internationalization file
$wgExtensionMessagesFiles['LinkSuggest'] = dirname( __FILE__ ) . '/LinkSuggest.i18n.php';

// ResourceLoader support (MW 1.17+)
$wgResourceModules['ext.LinkSuggest'] = array(
	'scripts' => 'jquery.mw.linksuggest.js',
	'styles' => 'jquery-ui.css', // maybe this isn't needed? I dunno
	'dependencies' => array( 'jquery.ui.autocomplete' ),
	'localBasePath' => dirname( __FILE__ ),
	'remoteExtPath' => 'LinkSuggest'
);

// Hooked functions
$wgHooks['EditPage::showEditForm:initial'][] = 'addLinkSuggest';
$wgHooks['GetPreferences'][] = 'wfLinkSuggestToggle';

/**
 * Adds the new toggle to Special:Preferences for disabling LinkSuggest
 * extension on a per-user basis
 *
 * @param $user Object: User object
 * @param $preferences Object: Preferences object
 * @return Boolean: true
 */
function wfLinkSuggestToggle( $user, &$preferences ) {
	$preferences['disablelinksuggest'] = array(
		'type' => 'toggle',
		'section' => 'editing/advancedediting',
		'label-message' => 'tog-disablelinksuggest',
	);
	return true;
}

/**
 * Add HTML required by LinkSuggest and the appropriate CSS and JS files to the
 * edit form to users who haven't disabled LinkSuggest in their preferences.
 *
 * @param $editPage Object: instance of EditPage
 * @return Boolean: true
 */
function addLinkSuggest( $editPage ) {
	global $wgOut, $wgUser, $wgScriptPath;
	if( $wgUser->getOption( 'disablelinksuggest' ) != true ) {
		if ( defined( 'MW_SUPPORTS_RESOURCE_MODULES' ) ) {
			// Load CSS and JS by using ResourceLoader (only for MW 1.17+)
			$wgOut->addModules( 'ext.LinkSuggest' );
		} else {
			// 1.16 backwards compatibility code...icky
			$wgOut->includeJQuery();
			$wgOut->addInlineScript( '$ = jQuery;' ); // required in 1.16 :-(
			$wgOut->addScriptFile( $wgScriptPath . '/extensions/LinkSuggest/jquery.widget.position.autocomplete-1.8.2.js' );
			$wgOut->addExtensionStyle( $wgScriptPath . '/extensions/LinkSuggest/jquery.autocomplete.css' );
			$wgOut->addScriptFile( $wgScriptPath . '/extensions/LinkSuggest/jquery.mw.linksuggest.js' );
		}
	}
	return true;
}

global $wgAjaxExportList;
$wgAjaxExportList[] = 'getLinkSuggest';
$wgAjaxExportList[] = 'getLinkSuggestImage';

/**
 * Creates a thumbnail from an image name.
 *
 * @return AjaxResponse containing the thumbnail image
 */
function getLinkSuggestImage() {
	global $wgRequest;
	$imageName = $wgRequest->getText( 'imageName' );

	$out = 'N/A';
	try {
		$img = wfFindFile( $imageName );
		if( $img ) {
			$out = $img->createThumb( 180 );
		}
	} catch( Exception $e ) {
		$out = 'N/A';
	}

	$ar = new AjaxResponse( $out );
	$ar->setCacheDuration( 60 * 60 );
	return $ar;
}

/**
 * AJAX callback function
 *
 * @return $ar Array of link suggestions
 */
function getLinkSuggest() {
	global $wgRequest, $wgContLang;

	// trim passed query and replace spaces by underscores
	// - this is how MediaWiki stores article titles in database
	$query = urldecode( trim( $wgRequest->getText( 'query' ) ) );
	$query = str_replace( ' ', '_', $query );

	// explode passed query by ':' to get namespace and article title
	$queryParts = explode( ':', $query, 2 );

	if( count( $queryParts ) == 2 ) {
		$query = $queryParts[1];

		$namespaceName = $queryParts[0];

		// try to get the index by canonical name first
		$namespace = MWNamespace::getCanonicalIndex( strtolower( $namespaceName ) );
		if ( $namespace == null ) {
			// if we failed, try looking through localized namespace names
			$namespace = array_search(
				ucfirst( $namespaceName ),
				$wgContLang->getNamespaces()
			);
			if( empty( $namespace ) ) {
				// getting here means our "namespace" is not real and can only
				// be a part of the title
				$query = $namespaceName . ':' . $query;
			}
		}
	}

	$results = array();

	if( empty( $namespace ) ) {
		// default namespace to search in
		$namespace = NS_MAIN;
	}

	// get localized namespace name
	$namespaceName = $wgContLang->getNsText( $namespace );
	// and prepare it for later use...
	$namespacePrefix = ( !empty( $namespaceName ) ) ? $namespaceName . ':' : '';

	$dbr = wfGetDB( DB_SLAVE );
	$query = $dbr->strencode( mb_strtolower( $query ) );

	$res = $dbr->select(
		array( 'querycache', 'page' ),
		'qc_title',
		array(
			'qc_title = page_title',
			'qc_namespace = page_namespace',
			'page_is_redirect = 0',
			'qc_type' => 'Mostlinked',
			"LOWER(qc_title) LIKE LOWER('{$query}%')",
			'qc_namespace' => $namespace
		),
		__METHOD__,
		array( 'ORDER BY' => 'qc_value DESC', 'LIMIT' => 10 )
	);

	foreach( $res as $row ) {
		$results[] = str_replace( '_', ' ', $namespacePrefix . $row->qc_title );
	}

	$res = $dbr->select(
		'page',
		'page_title',
		array(
			"LOWER(page_title) LIKE '{$query}%'",
			'page_is_redirect' => 0,
			'page_namespace' => $namespace
		),
		__METHOD__,
		array(
			'ORDER BY' => 'page_title ASC',
			'LIMIT' => ( 15 - count( $results ) )
		)
	);

	foreach( $res as $row ) {
		$results[] = str_replace( '_', ' ', $namespacePrefix . $row->page_title );
	}

	$results = array_unique( $results );
	$format = $wgRequest->getText( 'format' );

	if( $format == 'json' ) {
		$out = json_encode( array(
			'query' => $wgRequest->getText( 'query' ),
			'suggestions' => array_values( $results )
		));
	} else {
		$out = implode( "\n", $results );
	}

	$ar = new AjaxResponse( $out );
	$ar->setCacheDuration( 60 * 60 ); // cache results for one hour

	// set proper content type to ease development
	if ( $format == 'json' ) {
		$ar->setContentType( 'application/json; charset=utf-8' );
	} else {
		$ar->setContentType( 'text/plain; charset=utf-8' );
	}

	return $ar;
}