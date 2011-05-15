<?php
/**
* BookManager protected functions [Core]
*/

class BookManagerCore extends SpecialPage {
	const VERSION = "0.1.6 ";
	private static $chapterList;
	/**
	 * Get Title
	 * @param $text String Text for title of current page 
	 * @return Object
	 */
	protected static function newTitleObject( &$parser, $text = null ) {
		$t = Title::newFromText( $text );
		if ( is_null( $t ) ) {
			return $parser->getTitle();
		}
		return $t;
	}


	/**
	 * Adaptation of the function "getBookPagePrefixes" from collection extension
	 * (http://svn.wikimedia.org/viewvc/mediawiki/trunk/extensions/Collection/Collection.body.php?revision=79895&view=markup#l440)
	 */
	protected static function getBookPagePrefixes() {
		// global $wgUser;
		global $wgBookManagerPrefixNamespace;

		$result = array();
		/*
		$t = wfMsgForContent( 'coll-user_book_prefix', $wgUser->getName() );
		if ( wfEmptyMsg( 'coll-user_book_prefix', $t ) || $t == '-' ) {
			$userPageTitle = $wgUser->getUserPage()->getPrefixedText();
			$result['user-prefix'] = $userPageTitle . '/'
				. wfMsgForContent( 'coll-collections' ) . '/';
		} else {
			$result['user-prefix'] = $t;
		}
		*/
		$t = wfMsgForContent( 'coll-community_book_prefix' );
		if ( wfEmptyMsg( 'coll-community_book_prefix', $t ) || $t == '-' ) {
			$title = Title::makeTitle(
				$wgBookManagerPrefixNamespace,
				wfMsgForContent( 'coll-collections' )
			);
			$result['community-prefix'] = $title->getPrefixedText() . '/';
		}
		else {
			$result['community-prefix'] = $t;
		}
		return $result;
	}

	/**
	 * Simplification of the function "parseCollectionLine" from collection extension
	 * (http://svn.wikimedia.org/viewvc/mediawiki/trunk/extensions/Collection/Collection.body.php?revision=79895&view=markup#l709)
	 */
	protected static function parseCollectionLine( /* Sem uso por enquanto: &$collection, */ $line ) {
		$line = trim( $line );
		if ( substr( $line, 0, 1 ) == ':' ) { // article
			$pagename = trim( substr( $line, 1 ) );
			if ( preg_match( '/^\[\[:?(.*?)(\|(.*?))?\]\]$/', $pagename, $match ) ) {
				$pagename = $match[1];
			}
			elseif ( preg_match( '/^\[\{\{fullurl:(.*?)\|oldid=(.*?)\}\}\s+(.*?)\]$/', $pagename, $match ) ) {
				$pagename = $match[1];
			}
			else {
				return null;
			}
			$pagetitle = Title::newFromText( $pagename );
			if ( !$pagetitle ) {
				return null;
			}
			$d = $pagetitle->getPrefixedText();
			return $d;
		}
		return null;
	}

	/**
	 * Adaptation of the function "loadCollection" from collection extension
	 * (http://svn.wikimedia.org/viewvc/mediawiki/trunk/extensions/Collection/Collection.body.php?revision=79895&view=markup#l780)
	 */
	protected static function loadListFromCollection( $collectiontitle ) {
		if ( is_null( $collectiontitle ) || !$collectiontitle->exists() ) {
			return false;
		}
		$caps = array();

		$collectionpage = new Article( $collectiontitle );

		foreach ( preg_split( '/[\r\n]+/', $collectionpage->getContent() ) as $line ) {
			$item = self::parseCollectionLine( $line );
			if ( !is_null( $item ) ) {
				$caps[] = $item;
			}
		}
		return $caps;
	}

	/**
	* Get the book or chapter name from page title 
	* @param $text String Text for title of current page 
	* @param $part Integer. Title like 'Foo/Bar/Baz' "0" is Foo , the book name, and "1" is Bar/Baz, the chapter name.
	* @return String with book or chapter
	*/
	protected static function bookparts( &$parser, $text = null, $part = 1 ) {
		$t = self::newTitleObject( $parser, $text );
		// No book should have '/' in it's name, so...
		$book = explode( "/", $t->getText(), 2 ); 
		if ( count( $book ) > 1 ) {
			return $book[$part];
		}
		else {
			return $t->getText();
		}

	}
	/**
	 * Get the prefixed title of a page near the given page.
	 * @param $text String Text for title of current page
	 * @param $p Integer/String Position of wanted page. Next page is +1; Previous page is -1; Random position is 'rand'
	 * @return String The prefixed title or empty string if not found or found but not valid
	 */
	protected static function pageText( &$parser, $text = null, $p = 0 ) {
		$pagetitle = self::newTitleObject( $parser, $text );
		$prefixes = self::getBookPagePrefixes();
		$booktitle = Title::newFromText( $prefixes['community-prefix'] . self::bookparts( $parser, $text, 0 ) ); // ...the book name will be 'Foo'.

		if ( !self::$chapterList ) {
			self::$chapterList = self::loadListFromCollection( $booktitle );
		}
		if ( self::$chapterList === false ) {
			return '';
		}
		$current = array_search( $pagetitle, self::$chapterList );
		if ( $current === false || !isset( self::$chapterList[ $current + $p ] ) ) {
			return '';
		}
		$otherpagetitle = Title::newFromText( self::$chapterList[ $current + $p ] );
		if ( is_null( $otherpagetitle ) ) {
			return '';
		}
		if ( $p == 'rand' ){
			$limit = count( self::$chapterList ) - 1;
			$randPosition = rand( 0, $limit );
			return Title::newFromText( self::$chapterList[ $randPosition ] )->getText();
		}
		return wfEscapeWikiText( $otherpagetitle->getText() );
	}
}
/**
* BookManager Functions [Variables]
*/
class BookManagerVariables extends BookManagerCore {
	static function register( $parser ) {
		# optional SFH_NO_HASH to omit the hash from calls (e.g. {{int:...}}
		# instead of {{#int:...}})
		$parser->setFunctionHook( 'prevpagename',	array( __CLASS__, 'prevpagename' ),	SFH_NO_HASH );
		$parser->setFunctionHook( 'prevpagenamee',	array( __CLASS__, 'prevpagenamee' ),	SFH_NO_HASH );
		$parser->setFunctionHook( 'nextpagename',	array( __CLASS__, 'nextpagename' ),	SFH_NO_HASH );
		$parser->setFunctionHook( 'nextpagenamee',	array( __CLASS__, 'nextpagenamee' ),	SFH_NO_HASH );
		$parser->setFunctionHook( 'rootpagename',	array( __CLASS__, 'rootpagename' ),	SFH_NO_HASH );
		$parser->setFunctionHook( 'rootpagenamee',	array( __CLASS__, 'rootpagenamee' ),	SFH_NO_HASH );
		$parser->setFunctionHook( 'chaptername',	array( __CLASS__, 'chaptername' ),	SFH_NO_HASH );
		$parser->setFunctionHook( 'chapternamee',	array( __CLASS__, 'chapternamee' ),	SFH_NO_HASH );
		$parser->setFunctionHook( 'randomchapter',	array( __CLASS__, 'randomchapter' ),	SFH_NO_HASH );
		$parser->setFunctionHook( 'randomchaptere',	array( __CLASS__, 'randomchaptere' ),	SFH_NO_HASH );
		return true;
	}
	# Function to declare magicword id
	static function DeclareVarIds( &$aCustomVariableIds ) {
		# aCustomVariableIds is where MediaWiki wants to store its
		# list of custom variable ids. We oblige by adding ours:
		$aCustomVariableIds[] = 'prevpagename';
		$aCustomVariableIds[] = 'prevpagenamee';
		$aCustomVariableIds[] = 'nextpagename';
		$aCustomVariableIds[] = 'nextpagenamee';
		$aCustomVariableIds[] = 'rootpagename';
		$aCustomVariableIds[] = 'rootpagenamee';
		$aCustomVariableIds[] = 'chaptername';
		$aCustomVariableIds[] = 'chapternamee';
		$aCustomVariableIds[] = 'randomchapter';
		$aCustomVariableIds[] = 'randomchaptere';

		return true;
	}

	# Values functions
	static function prevpagename( &$parser, $text = null ) {
		$t = self::pageText( $parser, $text, - 1 );
		return $t;
	}
	static function prevpagenamee( &$parser, $text = null ) {
		$t = self::pageText( $parser, $text, - 1 );
		return wfUrlEncode( $t );
	}
	static function nextpagename( &$parser, $text = null ) {

		$t = self::pageText( $parser, $text, + 1 );
		return $t;
	}
	static function nextpagenamee( &$parser, $text = null ) {
		$t = self::pageText( $parser, $text, + 1 );
		return wfUrlEncode( $t );
	}
	static function rootpagename( &$parser, $text = null ) {
		$t = self::bookparts( $parser, $text, 0 );
		return $t;
	}
	static function rootpagenamee( &$parser, $text = null ) {
		$t = self::bookparts( $parser, $text, 0 );
		return wfUrlEncode( $t );
	}
	static function chaptername( &$parser, $text = null ) {
		$t = self::bookparts( $parser, $text, 1 );
		return $t;
	}
	static function chapternamee( &$parser, $text = null ) {
		$t = self::bookparts( $parser, $text, 1 );
		return wfUrlEncode( $t );
	}
	static function randomchapter( &$parser, $text = null ) {
		$t = self::pageText( $parser, $text, 'rand' );
		return $t;
	}
	static function randomchaptere( &$parser, $text = null ) {
		$t = self::pageText( $parser, $text, 'rand' );
		return wfUrlEncode( $t );
	}

	# Function for use with MW Variables on the current page
	static function AssignAValue( &$parser, &$cache, &$magicWordId, &$ret ) {
		switch( $magicWordId ) {
		case 'prevpagename':
			$ret = BookManagerVariables::prevpagename( $parser );
			return true;
		case 'prevpagenamee':
			$ret = BookManagerVariables::prevpagenamee( $parser );
			return true;
		case 'nextpagename':
			$ret = BookManagerVariables::nextpagename( $parser );
			return true;
		case 'nextpagenamee':
			$ret = BookManagerVariables::nextpagenamee( $parser );
			return true;
		case 'rootpagename':
			$ret = BookManagerVariables::rootpagename( $parser );
			return true;
		case 'rootpagenamee':
			$ret = BookManagerVariables::rootpagenamee( $parser );
			return true;
		case 'chaptername':
			$ret = BookManagerVariables::chaptername( $parser );
			return true;
		case 'chapternamee':
			$ret = BookManagerVariables::chapternamee( $parser );
			return true;
		case 'randomchapter':
			$ret = BookManagerVariables::randomchapter( $parser );
			return true;
		case 'randomchaptere':
			$ret = BookManagerVariables::randomchaptere( $parser );
			return true;

		}
		return false;
	}
}
/**
* BookManager Functions [Navigation Bar]
* inspired by PageNotice extension
* (http://svn.wikimedia.org/svnroot/mediawiki/trunk/extensions/PageNotice/PageNotice.php&view=markup)
*/

class BookManagerNavBar extends BookManagerCore {

	private static function camDisplayNavBar( &$out ) {
		global $wgRequest, $wgBookManagerNamespaces, $wgBookManagerNavBar;
		$ns = $out->getTitle()->getNamespace();
		$action = $wgRequest->getVal( 'action', 'view' );
		$isViewAction = ( $action == 'view' || $action == 'purge' || $action == 'submit' );
 		$t = ( $wgBookManagerNavBar && in_array( $ns, $wgBookManagerNamespaces ) && $isViewAction );
		return $t;
	}
	static function addNavBar( &$out, &$sk ) {
		global $wgParser;
		if ( !BookManagerNavBar::camDisplayNavBar( $out ) ) {
			return true;
		}
		$opt = array(
			'parseinline',
		);
		# Get $out title
		$currenttitletext = $out->getTitle()->getText();
		# Get: prev, next and base chapter from the list
		$prev = self::pageText( $wgParser, $currenttitletext, - 1 );
		$base = Title::newFromText( $currenttitletext )->getBaseText();
		$next = self::pageText( $wgParser, $currenttitletext, + 1 );
		if ( $prev === '' && $next === '' ) {
			return true;
		}
		# Generate HTML or system messages values( $1 for $prev, $2 for $prevtext, $3 for $base, $4 for $basetext, $5 for $next and $6 for $nexttext ).
		$prevtext = ( $prev !== '' ) ? Title::newFromText( $prev )->getSubpageText(): '' ;
		$basetext = Title::newFromText( $base )->getSubpageText();
		$nexttext = ( $next !== '' ) ? Title::newFromText( $next )->getSubpageText(): '' ;
		$defaultBar = Xml::openElement( 'ul', array( 'class' => 'mw-book-navigation' ) );
		if ( $prev !== '' ) {
			$prevlink = Title::newFromText( $prev )->getLocalURL();
			$defaultBar .= Xml::openElement( 'li', array( 'class' => 'mw-prev' ) );
			$defaultBar .= Xml::element( 'a', array( 'href' => $prevlink, 'title' => $prev ), $prevtext );
			$defaultBar .= Xml::closeElement( 'li' );
		}
		$baselink = Title::newFromText( $base )->getLocalURL();
		$defaultBar .= Xml::openElement( 'li', array( 'class' => 'mw-index' ) );
		$defaultBar .= Xml::element( 'a', array( 'href' => $baselink, 'title' => $base ), $basetext );
		$defaultBar .= Xml::closeElement( 'li' );
		if ( $next !== '' ) {
			$nextlink = Title::newFromText( $next )->getLocalURL();
			$defaultBar .= Xml::openElement( 'li', array( 'class' => 'mw-next' ) );
			$defaultBar .= Xml::element( 'a', array( 'href' => $nextlink, 'title' => $next ), $nexttext );
			$defaultBar .= Xml::closeElement( 'li' );
		}
		$defaultBar .= Xml::closeElement( 'ul' );
		# Gets navigation bar from custom system messages or from default defined above
		$customBoth = wfEmptyMsg( 'BookManager' ) ? false : 'BookManager';
		$customTop = wfEmptyMsg( 'BookManager-top' ) ? $customBoth : 'BookManager-top';
		$customBottom = wfEmptyMsg( 'BookManager-bottom' ) ? $customBoth : 'BookManager-bottom';
		$opt = array(
			'parseinline',
		);
		if ( $customTop ) {
			$top = wfMsgExt( $customTop, $opt, $prev, $prevtext, $base, $basetext, $next, $nexttext );
		}
		else {
			$top = $defaultBar;
		}
		if ( $customBottom ) {
			$bottom = wfMsgExt( $customBottom, $opt, $prev, $prevtext, $base, $basetext, $next, $nexttext );
		}
		else {
			$bottom = $defaultBar;
		}
		# Adds navigation before and after the page text
		$out->prependHTML( "<div>$top</div>" );
		$out->addHTML( "<div>$bottom</div>" );
		# adds CSS and JS to navigation bar
		$out->addModuleStyles( 'ext.BookManager' );
		$out->addModules( 'ext.BookManager' );
 		return true;
 	}
	//known BUG: The category appears more than once when action is not 'view'
	static function CatByPrefix( &$parser, &$text ) {
		global $wgOut;
		if ( !BookManagerNavBar::camDisplayNavBar( $wgOut ) && $wgCategorizationByPrefix == false ) {
			return true;
		}
		$catTitle = Title::newFromText( self::bookparts( $parser, $text, 0 ));
		$parserOutput = $parser->getOutput();
		$parserOutput->addCategory( $catTitle->getDBkey() , $catTitle->getText() );

		return true;
	}

	static function bookToolboxSection( &$sk, &$toolbox ) {
		global $wgTitle, $wgParser;
		$currenttitletext = $wgTitle->getText();
		$randchapter = self::pageText( $wgParser, $currenttitletext, 'rand' );
		# Add book tools section and all your items 
		if ( $randchapter ){
			?><div class="portal" id='p-tb'><?php
					?><h5><?php $sk->msg( 'bm-booktools-section' ); ?></h5><?php
					?><div class="body"><?php
						?><ul><?php
							?><li id="t-rating"><?php
								?><a href="<?php echo htmlspecialchars( Title::newFromText( $randchapter )->getLocalURL() ) ?>"><?php
								echo $sk->msg( 'bm-randomchapter-link' );
								?></a><?php
							?></li><?php
						?></ul><?php
					?></div><?php
			?></div><?php
		}
		return true;
	}
}
/**
* BookManager Functions [PrintVersion]
*/
class PrintVersion extends BookManagerCore {

	function __construct() {
		parent::__construct( 'PrintVersion' );
	}
	function execute( $book ) {
		global $wgOut, $wgRequest;

		$this->setHeaders();
		$this->outputHeader();

		$book = !is_null( $book ) ? $book : $wgRequest->getVal( 'book' );
		if ( !isset( $book ) ) {
			$wgOut->addWikiMsg( 'bm-printversion-no-book' );
			return;
		}
		$prefixes = self::getBookPagePrefixes();
		$booktitle = Title::newFromText( $prefixes['community-prefix'] . $book );
		$chapterList = self::loadListFromCollection( $booktitle );
		if ( $chapterList === false ) {
			$wgOut->addWikiMsg( 'bm-printversion-inexistent-book' );
			return;
		}
		$text = '';
		foreach ( $chapterList as $chapter ) {
			$chaptertitle = Title::newFromText( $chapter );
			$sectionname = $chaptertitle->getSubpageText();
			$text .= "= $sectionname =\n";
			$text .= "{{:$chapter}}\n\n";
		}
		$wgOut->addWikiText( $text );
	}

}
