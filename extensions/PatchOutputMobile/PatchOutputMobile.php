<?php

// Needs to be called within MediaWiki; not standalone
if ( !defined( 'MEDIAWIKI' ) ) {
	echo( "This is an extension to the MediaWiki package and cannot be run standalone.\n" );
	die( -1 );
}

// Define the extension; allows us make sure the extension is used correctly
define( 'PATCHOUTPUTMOBILE', 'PatchOutputMobile' );

// Extension credits that will show up on Special:Version
$wgExtensionCredits['other'][] = array(
	'name' => 'PatchOutputMobile',
	'version' => ExtPatchOutputMobile::VERSION,
	'author' => '[http://www.mediawiki.org/wiki/User:Preilly Preilly]',
	'description' => 'Patch HTML output for mobile',
	'url' => 'http://www.mediawiki.org/wiki/Extension:PatchOutputMobile',
);

$dir = dirname(__FILE__) . DIRECTORY_SEPARATOR;
$wgExtensionMessagesFiles['PatchOutputMobile'] = $dir . 'PatchOutputMobile.i18n.php';

$wgExtPatchOutputMobile = new ExtPatchOutputMobile();

$wgHooks['OutputPageBeforeHTML'][] = array( &$wgExtPatchOutputMobile,
											'onOutputPageBeforeHTML' );

class ExtPatchOutputMobile {
	const VERSION = '0.3.3';

	private $doc;
	
	public static $messages = array();
	
	public $contentFormat = '';
	public $WMLSectionSeperator = '***************************************************************************';
	public static $dir;
	public static $code;
	public static $device;

	public $itemsToRemove = array(
		'#contentSub',		  # redirection notice
		'div.messagebox',	  # cleanup data
		'#siteNotice',		  # site notice
		'#siteSub',			  # "From Wikipedia..."
		'#jump-to-nav',		  # jump-to-nav
		'div.editsection',	  # edit blocks
		'div.infobox',		  # Infoboxes in the article
		'table.toc',		  # table of contents
		'#catlinks',		  # category links
		'div.stub',			  # stub warnings
		'table.metadata',	  # ugly metadata
		'form',
		'div.sister-project',
		'script',
		'div.magnify',		  # stupid magnify thing
		'.editsection',
		'span.t',
		'sup[style*="help"]',
		'.portal',
		'#protected-icon',
		'.printfooter',
		'.boilerplate',
		'#id-articulo-destacado',
		'#coordinates',
		'#top',
		'.hiddenStructure',
		'.noprint',
		'.medialist',
		'.mw-search-createlink'
	);

	public function onOutputPageBeforeHTML( &$out, &$text ) {
		global $wgContLang;
		// Need to stash the results of the "wfMsg" call before the Output Buffering handler
		// because at this point the database connection is shut down, etc.
		ExtPatchOutputMobile::$messages['patch-output-mobile-show'] = wfMsg( 'patch-output-mobile-show-button' );
		ExtPatchOutputMobile::$messages['patch-output-mobile-hide'] = wfMsg( 'patch-output-mobile-hide-button' );
		ExtPatchOutputMobile::$messages['patch-output-mobile-back-to-top'] = wfMsg( 'patch-output-mobile-back-to-top-of-section' );
		ExtPatchOutputMobile::$messages['regular_wikipedia'] = wfMsg( 'patch-output-mobile-regular-wikipedia' );
		ExtPatchOutputMobile::$messages['perm_stop_redirect'] = wfMsg( 'patch-output-mobile-perm-stop-redirect' );
		ExtPatchOutputMobile::$messages['copyright'] = wfMsg( 'patch-output-mobile-copyright' );
		ExtPatchOutputMobile::$messages['home_button'] = wfMsg( 'patch-output-mobile-home-button' );
		ExtPatchOutputMobile::$messages['random_button'] = wfMsg( 'patch-output-mobile-random-button' );
		ExtPatchOutputMobile::$dir = $wgContLang->getDir();
		ExtPatchOutputMobile::$code = $wgContLang->getCode();
		
		$userAgent = $_SERVER['HTTP_USER_AGENT'];
		$acceptHeader = $_SERVER["HTTP_ACCEPT"];
		$device = new Device();
		$formatName = $device->formatName( $userAgent, $acceptHeader );
		ExtPatchOutputMobile::$device = $device->format( $formatName );
		
		if ( ExtPatchOutputMobile::$device['view_format'] === 'wml' ) {
			$this->contentFormat = 'WML';
		} elseif ( ExtPatchOutputMobile::$device['view_format'] === 'html' ) {
			$this->contentFormat = 'XHTML';
		}
		
		ob_start( array( $this, 'parse' ) );
		return true;
	}
	
	private function showHideCallbackWML( $matches ) {
		static $headings = 0;
		$show = ExtPatchOutputMobile::$messages['patch-output-mobile-show'];
		$hide = ExtPatchOutputMobile::$messages['patch-output-mobile-hide'];
		$backToTop = ExtPatchOutputMobile::$messages['patch-output-mobile-back-to-top'];
		++$headings;

		$base = $this->WMLSectionSeperator .
			"<h2 class='section_heading' id='section_{$headings}'>{$matches[2]}</h2>";

		$GLOBALS['headings'] = $headings;

		return $base;
	}

	private function showHideCallbackXHTML( $matches ) {
		static $headings = 0;
		$show = ExtPatchOutputMobile::$messages['patch-output-mobile-show'];
		$hide = ExtPatchOutputMobile::$messages['patch-output-mobile-hide'];
		$backToTop = ExtPatchOutputMobile::$messages['patch-output-mobile-back-to-top'];
		++$headings;
		// Back to top link
		$base = "<div class='section_anchors' id='anchor_" . intval( $headings - 1 ) .
			"'><a href='#section_" . intval( $headings - 1 ) .
			"' class='back_to_top'>&uarr; {$backToTop}</a></div>";
		// generate the HTML we are going to inject
		$buttons = "<button class='section_heading show' section_id='{$headings}'>{$show}</button>" .
			"<button class='section_heading hide' section_id='{$headings}'>{$hide}</button>";
		$base .= "<h2 class='section_heading' id='section_{$headings}'{$matches[1]}{$buttons} <span>" .
			"{$matches[2]}</span></h2><div class='content_block' id='content_{$headings}'>";

		if ( $headings > 1 ) {
			// Close it up here
			$base = '</div>' . $base;
		}

		$GLOBALS['headings'] = $headings;

		return $base;
	}

	public function javascriptize( $s ) {
		$callback = 'showHideCallback';
		$callback .= $this->contentFormat;
		
		// Closures are a PHP 5.3 feature.
		// MediaWiki currently requires PHP 5.2.3 or higher.
		// So, using old style for now.
		$s = preg_replace_callback(
			'/<h2(.*)<span class="mw-headline" [^>]*>(.+)<\/span>\w*<\/h2>/',
			array( $this, $callback ),
			$s
		);

		// if we had any, make sure to close the whole thing!
		if ( isset( $GLOBALS['headings'] ) && $GLOBALS['headings'] > 0 ) {
			$s = str_replace(
				'<div class="visualClear">',
				'</div><div class="visualClear">',
				$s
			);
		}

		return $s;
	}
	
	private function createWMLCard( $s, $title = '' ) {
		$segments = explode( $this->WMLSectionSeperator, $s );
		$card = '';
		$idx = 0;

		$requestedSegment = isset( $_GET['seg'] ) ? $_GET['seg'] : 0;
		$card .= "<card id='{$idx}' title='{$title}'><p>{$segments[$requestedSegment]}</p>";
		$idx = $requestedSegment + 1;
		$segmentsCount = count($segments);
		$card .= $idx . "/" . $segmentsCount;

		if ( $idx < $segmentsCount ) {
			$card .= "<p><a href='{$_SERVER['PHP_SELF']}?seg={$idx}'>Continue ...</a></p>";
		}

		if ( $idx > 1 ) {
			$back_idx = $requestedSegment - 1;
			$card .= "<p><a href='{$_SERVER['PHP_SELF']}?seg={$back_idx}'>Back ...</a></p>";
		}

		$card .= '</card>';
		return $card;
	}

	public function parse( $s ) {
		return $this->DOMParse( $s );
	}

	private function parseItemsToRemove() {
		$itemToRemoveRecords = array();

		foreach ( $this->itemsToRemove as $itemToRemove ) {
			$type = '';
			$rawName = '';
			CssDetection::detectIdCssOrTag( $itemToRemove, $type, $rawName );
			$itemToRemoveRecords[$type][] = $rawName;
		}

		return $itemToRemoveRecords;
	}

	public function DOMParse( $html ) {
		libxml_use_internal_errors( true );
		$this->doc = DOMDocument::loadHTML( '<?xml encoding="UTF-8">' . $html );
		libxml_use_internal_errors( false );
		$this->doc->preserveWhiteSpace = false;
		$this->doc->strictErrorChecking = false;
		$this->doc->encoding = 'UTF-8';

		$itemToRemoveRecords = $this->parseItemsToRemove();

		$titleNode = $this->doc->getElementsByTagName( 'title' );

		if ( $titleNode->length > 0 ) {
			$title = $titleNode->item( 0 )->nodeValue;
		}

		// Tags

		// You can't remove DOMNodes from a DOMNodeList as you're iterating
		// over them in a foreach loop. It will seemingly leave the internal
		// iterator on the foreach out of wack and results will be quite
		// strange. Though, making a queue of items to remove seems to work.
		// For example:

		$domElemsToRemove = array();
		foreach ( $itemToRemoveRecords['TAG'] as $tagToRemove ) {
			$tagToRemoveNodes = $this->doc->getElementsByTagName( $tagToRemove );

			foreach( $tagToRemoveNodes as $tagToRemoveNode ) {
				if ( $tagToRemoveNode ) {
					$domElemsToRemove[] = $tagToRemoveNode;
				}
			}
		}

		foreach( $domElemsToRemove as $domElement ) {
			$domElement->parentNode->removeChild( $domElement );
		}

		// Elements with named IDs
		foreach ( $itemToRemoveRecords['ID'] as $itemToRemove ) {
			$itemToRemoveNode = $this->doc->getElementById( $itemToRemove );
			if ( $itemToRemoveNode ) {
				$removedItemToRemove = $itemToRemoveNode->parentNode->removeChild( $itemToRemoveNode );
			}
		}

		// CSS Classes
		$xpath = new DOMXpath( $this->doc );
		foreach ( $itemToRemoveRecords['CLASS'] as $classToRemove ) {
			$elements = $xpath->query( '//*[@class="' . $classToRemove . '"]' );

			foreach( $elements as $element ) {
				$removedElement = $element->parentNode->removeChild( $element );
			}
		}

		// Tags with CSS Classes
		foreach ( $itemToRemoveRecords['TAG_CLASS'] as $classToRemove ) {
			$parts = explode( '.', $classToRemove );

			$elements = $xpath->query(
				'//' . $parts[0] . '[@class="' . $parts[1] . '"]'
			);

			foreach( $elements as $element ) {
				$removedElement = $element->parentNode->removeChild( $element );
			}
		}
		
		// Handle red links with action equal to edit
		$redLinks = $xpath->query( '//a[@class="new"]' );
		foreach( $redLinks as $redLink ) {
			//PHP Bug #36795 — Inappropriate "unterminated entity reference"
			$spanNode = $this->doc->createElement( "span", str_replace( "&", "&amp;", $redLink->nodeValue ) );

			if ( $redLink->hasAttributes() ) {
				$attributes = $redLink->attributes;
				foreach ( $attributes as $i => $attribute ) {
					$spanNode->setAttribute( $attribute->name, $attribute->value );
				}
			}

			$redLink->parentNode->replaceChild( $spanNode, $redLink );
		}
		$content = $this->doc->getElementById( 'content' );

		$contentHtml = $this->doc->saveXML( $content, LIBXML_NOEMPTYTAG );

		if ( empty( $title ) ) {
			$title = 'Wikipedia';
		}
		
		$dir = ExtPatchOutputMobile::$dir;
		$code = ExtPatchOutputMobile::$code;
		$regular_wikipedia = ExtPatchOutputMobile::$messages['regular_wikipedia'];
		$perm_stop_redirect = ExtPatchOutputMobile::$messages['perm_stop_redirect'];
		$copyright = ExtPatchOutputMobile::$messages['copyright'];
		$home_button = ExtPatchOutputMobile::$messages['home_button'];
		$random_button = ExtPatchOutputMobile::$messages['random_button'];
		
		if ( strlen( $contentHtml ) > 4000 && $this->contentFormat == 'XHTML' 
			&& ExtPatchOutputMobile::$device['supports_javascript'] === true ) {
			$contentHtml =	$this->javascriptize( $contentHtml );
		} else if ( $this->contentFormat == 'WML' ) {
			$contentHtml = $this->javascriptize( $contentHtml );
			$contentHtml = $this->createWMLCard( $contentHtml, $title );
			require( 'views/layout/application.wml.php' );
		}
		
		if ( $this->contentFormat == 'XHTML' ) {
			require( 'views/notices/_donate.html.php' );
			require( 'views/layout/_search_webkit.html.php' );
			require( 'views/layout/_footmenu_default.html.php' );
			require( 'views/layout/application.html.php' );
		}
		
		return $applicationHtml;
	}
}

class CssDetection {

	public static function detectIdCssOrTag( $snippet, &$type, &$rawName ) {
		$output = '';

		if ( strpos( $snippet, '.' ) === 0 ) {
			$output = 'Class found: ';
			$type = 'CLASS';
			$rawName = substr( $snippet, 1 );
		}

		if ( strpos( $snippet, '#' ) === 0 ) {
			$output = 'ID found: ';
			$type = 'ID';
			$rawName = substr( $snippet, 1 );
		}

		if ( strpos( $snippet, '.' ) !== 0 &&
			strpos( $snippet, '.' ) !== false ) {
			$output = 'Tag with Class found: ';
			$type = 'TAG_CLASS';
			$rawName = $snippet;
		}

		if ( strpos( $snippet, '.' ) === false &&
			strpos( $snippet, '#' ) === false &&
			strpos( $snippet, '[' ) === false &&
			strpos( $snippet, ']' ) === false ) {
			$output = 'Tag found: ';
			$type = 'TAG';
			$rawName = $snippet;
		}

		if ( empty( $output ) ) {
			$output = 'Unknown HTML snippet found: ';
			$type = 'UNKNOWN';
			$rawName = $snippet;
		}

		return $output;
	}
}

// Provides an abstraction for a device 
// A device can select which format a request should recieve and
// may be extended to provide access to particular devices functionality
class Device {
	
	public function availableFormats() {		
		$formats = array (
			  'html' => 
			  array (
				'view_format' => 'html',
				'search_bar' => 'default',
				'footmenu' => 'default',
				'with_layout' => 'application',
				'css_file_name' => 'default',
				'supports_javascript' => false,
				'disable_zoom' => true,
				'parser' => 'html',
				'disable_links' => true,
			  ),
			  'capable' => 
			  array (
				'view_format' => 'html',
				'search_bar' => 'default',
				'footmenu' => 'default',
				'with_layout' => 'application',
				'css_file_name' => 'default',
				'supports_javascript' => true,
				'disable_zoom' => true,
				'parser' => 'html',
				'disable_links' => true,
			  ),
			  'simplehtml' => 
			  array (
				'view_format' => 'html',
				'search_bar' => 'simple',
				'footmenu' => 'simple',
				'with_layout' => 'application',
				'css_file_name' => 'simple',
				'supports_javascript' => false,
				'disable_zoom' => true,
				'parser' => 'html',
				'disable_links' => true,
			  ),
			  'webkit' => 
			  array (
				'view_format' => 'html',
				'search_bar' => 'webkit',
				'footmenu' => 'default',
				'with_layout' => 'application',
				'css_file_name' => 'webkit',
				'supports_javascript' => true,
				'disable_zoom' => true,
				'parser' => 'html',
				'disable_links' => true,
			  ),
			  'webkit_old' => 
			  array (
				'view_format' => 'html',
				'search_bar' => 'default',
				'footmenu' => 'default',
				'with_layout' => 'application',
				'css_file_name' => 'webkit_old',
				'supports_javascript' => true,
				'disable_zoom' => true,
				'parser' => 'html',
				'disable_links' => true,
			  ),
			  'android' => 
			  array (
				'view_format' => 'html',
				'search_bar' => 'default',
				'footmenu' => 'default',
				'with_layout' => 'application',
				'css_file_name' => 'android',
				'supports_javascript' => true,
				'disable_zoom' => false,
				'parser' => 'html',
				'disable_links' => true,
			  ),
			  'iphone' => 
			  array (
				'view_format' => 'html',
				'search_bar' => 'webkit',
				'footmenu' => 'default',
				'with_layout' => 'application',
				'css_file_name' => 'iphone',
				'supports_javascript' => true,
				'disable_zoom' => true,
				'parser' => 'html',
				'disable_links' => true,
			  ),
			  'iphone2' => 
			  array (
				'view_format' => 'html',
				'search_bar' => 'default',
				'footmenu' => 'default',
				'with_layout' => 'application',
				'css_file_name' => 'iphone2',
				'supports_javascript' => true,
				'disable_zoom' => true,
				'parser' => 'html',
				'disable_links' => true,
			  ),
			  'native_iphone' => 
			  array (
				'view_format' => 'html',
				'search_bar' => false,
				'footmenu' => 'default',
				'with_layout' => 'application',
				'css_file_name' => 'default',
				'supports_javascript' => true,
				'disable_zoom' => true,
				'parser' => 'html',
				'disable_links' => false,
			  ),
			  'palm_pre' => 
			  array (
				'view_format' => 'html',
				'search_bar' => 'default',
				'footmenu' => 'default',
				'with_layout' => 'application',
				'css_file_name' => 'palm_pre',
				'supports_javascript' => true,
				'disable_zoom' => true,
				'parser' => 'html',
				'disable_links' => true,
			  ),
			  'kindle' => 
			  array (
				'view_format' => 'html',
				'search_bar' => 'kindle',
				'footmenu' => 'default',
				'with_layout' => 'application',
				'css_file_name' => 'kindle',
				'supports_javascript' => false,
				'disable_zoom' => true,
				'parser' => 'html',
				'disable_links' => true,
			  ),
			  'kindle2' => 
			  array (
				'view_format' => 'html',
				'search_bar' => 'kindle',
				'footmenu' => 'default',
				'with_layout' => 'application',
				'css_file_name' => 'kindle',
				'supports_javascript' => false,
				'disable_zoom' => true,
				'parser' => 'html',
				'disable_links' => true,
			  ),
			  'blackberry' => 
			  array (
				'view_format' => 'html',
				'search_bar' => 'default',
				'footmenu' => 'default',
				'with_layout' => 'application',
				'css_file_name' => 'blackberry',
				'supports_javascript' => true,
				'disable_zoom' => true,
				'parser' => 'html',
				'disable_links' => true,
			  ),
			  'netfront' => 
			  array (
				'view_format' => 'html',
				'search_bar' => 'simple',
				'footmenu' => 'simple',
				'with_layout' => 'application',
				'css_file_name' => 'simple',
				'supports_javascript' => false,
				'disable_zoom' => true,
				'parser' => 'html',
				'disable_links' => true,
			  ),
			  'wap2' => 
			  array (
				'view_format' => 'html',
				'search_bar' => 'simple',
				'footmenu' => 'simple',
				'with_layout' => 'application',
				'css_file_name' => 'simple',
				'supports_javascript' => false,
				'disable_zoom' => true,
				'parser' => 'html',
				'disable_links' => true,
			  ),
			  'psp' => 
			  array (
				'view_format' => 'html',
				'search_bar' => 'simple',
				'footmenu' => 'simple',
				'with_layout' => 'application',
				'css_file_name' => 'psp',
				'supports_javascript' => false,
				'disable_zoom' => true,
				'parser' => 'html',
				'disable_links' => true,
			  ),
			  'ps3' => 
			  array (
				'view_format' => 'html',
				'search_bar' => 'simple',
				'footmenu' => 'simple',
				'with_layout' => 'application',
				'css_file_name' => 'simple',
				'supports_javascript' => false,
				'disable_zoom' => true,
				'parser' => 'html',
				'disable_links' => true,
			  ),
			  'wii' => 
			  array (
				'view_format' => 'html',
				'search_bar' => 'wii',
				'footmenu' => 'default',
				'with_layout' => 'application',
				'css_file_name' => 'wii',
				'supports_javascript' => true,
				'disable_zoom' => true,
				'parser' => 'html',
				'disable_links' => true,
			  ),
			  'operamini' => 
			  array (
				'view_format' => 'html',
				'search_bar' => 'simple',
				'footmenu' => 'simple',
				'with_layout' => 'application',
				'css_file_name' => 'operamini',
				'supports_javascript' => false,
				'disable_zoom' => true,
				'parser' => 'html',
				'disable_links' => true,
			  ),
			  'nokia' => 
			  array (
				'view_format' => 'html',
				'search_bar' => 'webkit',
				'footmenu' => 'default',
				'with_layout' => 'application',
				'css_file_name' => 'nokia',
				'supports_javascript' => true,
				'disable_zoom' => true,
				'parser' => 'html',
				'disable_links' => true,
			  ),
			  'wml' => 
			  array (
				'view_format' => 'wml',
				'search_bar' => 'wml',
				'supports_javascript' => false,
				'parser' => 'wml',
			  ),
			);
		return $formats;
	}
	
	public function format( $formatName ) {
		$format = $this->availableFormats();
		return ( isset( $format[$formatName] ) ) ? $format[$formatName] : array();
	}
	
	public function testFormatName() {
		$testResults = '';
		
		$userAgents = array();
		$userAgents['android']   = 'Mozilla/5.0 (Linux; U; Android 2.1; en-us; Nexus One Build/ERD62) AppleWebKit/530.17 (KHTML, like Gecko) Version/4.0 Mobile Safari/530.17';
		$userAgents['iphone2']   = 'Mozilla/5.0 (ipod: U;CPU iPhone OS 2_2 like Mac OS X: es_es) AppleWebKit/525.18.1 (KHTML, like Gecko) Version/3.0 Mobile/3B48b Safari/419.3';
		$userAgents['iphone']    = 'Mozilla/5.0 (iPhone; U; CPU like Mac OS X; en) AppleWebKit/420.1 (KHTML, like Gecko) Version/3.0 Mobile/3B48b Safari/419.3';
		$userAgents['nokia']     = 'Mozilla/5.0 (SymbianOS/9.1; U; [en]; SymbianOS/91 Series60/3.0) AppleWebKit/413 (KHTML, like Gecko) Safari/413';
		$userAgents['palm_pre']  = 'Mozilla/5.0 (webOS/1.0; U; en-US) AppleWebKit/525.27.1 (KHTML, like Gecko) Version/1.0 Safari/525.27.1 Pre/1.0';
		$userAgents['wii']       = 'Opera/9.00 (Nintendo Wii; U; ; 1309-9; en)';
		$userAgents['operamini'] = 'Opera/9.50 (J2ME/MIDP; Opera Mini/4.0.10031/298; U; en)';
		$userAgents['iphone']    = 'Opera/9.51 Beta (Microsoft Windows; PPC; Opera Mobi/1718; U; en)';
		$userAgents['kindle']    = 'Mozilla/4.0 (compatible; Linux 2.6.10) NetFront/3.3 Kindle/1.0 (screen 600x800)';
		$userAgents['kindle2']   = 'Mozilla/4.0 (compatible; Linux 2.6.22) NetFront/3.4 Kindle/2.0 (screen 824x1200; rotate)';
		$userAgents['capable']   = 'Mozilla/5.0 (X11; Linux i686; rv:2.0.1) Gecko/20100101 Firefox/4.0.1';
		$userAgents['netfront']  = 'Mozilla/4.08 (Windows; Mobile Content Viewer/1.0) NetFront/3.2';
		$userAgents['wap2']      = 'SonyEricssonK608i/R2L/SN356841000828910 Browser/SEMC-Browser/4.2 Profile/MIDP-2.0 Configuration/CLDC-1.1';
		$userAgents['wap2']      = 'NokiaN73-2/3.0-630.0.2 Series60/3.0 Profile/MIDP-2.0 Configuration/CLDC-1.1';
		$userAgents['psp']       = 'Mozilla/4.0 (PSP (PlayStation Portable); 2.00)';
		$userAgents['ps3']       = 'Mozilla/5.0 (PLAYSTATION 3; 1.00)';
		
		foreach ( $userAgents as $formatName => $userAgent ) {
			if ( $this->formatName( $userAgent ) === $formatName ) {
				$result = ' has PASSED!';
			} else {
				$result = ' has FAILED!';
			}
			
			$testResults .= $formatName . $result . '<br/>' . PHP_EOL;
		}
		return $testResults;
	}

	public function formatName( $userAgent, $acceptHeader = '' ) {
		$formatName = '';

		if ( preg_match( '/Android/', $userAgent ) ) {
			$formatName = 'android';
		} elseif ( preg_match( '/iPhone.* Safari/', $userAgent ) ) {
			if ( strpos( $userAgent, 'iPhone OS 2' ) !== false ) {
				$formatName = 'iphone2';
			} else {
				$formatName = 'iphone';
			}
		} elseif ( preg_match( '/iPhone/', $userAgent ) ) {
			if ( strpos( $userAgent, 'Opera' ) !== false ) {
				$formatName = 'operamini';
			} else {
				$formatName = 'native_iphone';
			}
		} elseif ( preg_match( '/WebKit/', $userAgent ) ) {
			if ( preg_match( '/Series60/', $userAgent ) ) {
				$formatName = 'nokia';
			} elseif ( preg_match( '/webOS/', $userAgent ) ) {
				$formatName = 'palm_pre';
			} else {
				$formatName = 'webkit';
			}
		} elseif ( preg_match( '/Opera/', $userAgent ) ) {
			if ( strpos( $userAgent, 'Nintendo Wii' ) !== false ) {
				$formatName = 'wii';
			} elseif ( strpos( $userAgent, 'Opera Mini' ) !== false ) { 
				$formatName = 'operamini';
			} elseif ( strpos( $userAgent, 'Opera Mobi' ) !== false ) {
				$formatName = 'iphone';
			} else {
				$formatName = 'webkit';
			}
		} elseif ( preg_match( '/Kindle\/1.0/', $userAgent ) ) {
			$formatName = 'kindle';
		} elseif ( preg_match( '/Kindle\/2.0/', $userAgent ) ) {
			$formatName = 'kindle2';
		} elseif ( preg_match( '/Firefox/', $userAgent ) ) {
			$formatName = 'capable';
		} elseif ( preg_match( '/NetFront/', $userAgent ) ) {
			$formatName = 'netfront';
		} elseif ( preg_match( '/SEMC-Browser/', $userAgent ) ) {
			$formatName = 'wap2';
		} elseif ( preg_match( '/Series60/', $userAgent ) ) {
			$formatName = 'wap2';
		} elseif ( preg_match( '/PlayStation Portable/', $userAgent ) ) {
			$formatName = 'psp';
		} elseif ( preg_match( '/PLAYSTATION 3/', $userAgent ) ) {
			$formatName = 'ps3';
		}

		if ( $formatName === '' ) {
			if ( strpos( $acceptHeader, 'application/vnd.wap.xhtml+xml' ) !== false ) {
				$formatName = 'wap2';
			} elseif ( strpos( $acceptHeader, 'vnd.wap.wml' ) !== false ) {
				$formatName = 'wml';
			} else {
				$formatName = 'html';
			}
		}
		return $formatName;
	}
}
