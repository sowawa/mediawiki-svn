<?php
if ( !defined( 'MEDIAWIKI' ) ) {
	echo( "This file is an extension to the MediaWiki software and cannot be used standalone.\n" );
	die( 1 );
}

class SpecialArchiveBlacklist extends SpecialPage {
    function __construct() {
	parent::__construct( 'ArchiveBlacklist' );
	
    }
    
    public function execute ( $par ) {
	global $wgOut;
	$this->setHeaders();
	$this->outputHeader();
	
	$wgOut->addWikiText( wfMsg( 'archivelinks-archive-blacklist-desc' ) );
	
	$wgOut->addHTML(
		HTML::openElement('form', array( 'method' => 'get', 'action' => '')) .
		HTML::openElement('fieldset') .
		HTML::element( 'legend', null, wfMsg( 'ArchiveBlacklist') ) .
		HTML::hidden( 'title', SpecialPage::getTitleFor( 'ArchiveBlacklist' )->getPrefixedText() ) .
		HTML::input( 'Blacklist' ) .
		HTML::closeElement('fieldset') .
		HTML::closeElement('form'));
    }
}