<?php
if ( !defined( 'MEDIAWIKI' ) ) {
	echo( "This file is an extension to the MediaWiki software and cannot be used standalone.\n" );
	die( 1 );
}

class SpecialModifyBlacklist extends SpecialPage {
    function __construct() {
	parent::__construct( 'ModifyBlacklist' );
	
    }
    
    public function execute ( $par ) {
	global $wgOut, $wgRequest;
	$this->setHeaders();
	$this->outputHeader();
	
	$wgOut->addWikiText( wfMsg( 'archivelinks-modify-blacklist-desc' ) );
	
	$wgOut->addHTML(
		HTML::openElement('form', array( 'method' => 'post', 'action' => SpecialPage::getTitleFor( 'ModifyBlacklist' )->getLocalUrl() )) .
		HTML::openElement('fieldset') .
		HTML::element( 'legend', null, wfMsg( 'ModifyBlacklist' ) ) .
		//HTML::hidden( 'title', SpecialPage::getTitleFor( 'ArchiveBlacklist' )->getPrefixedText() ) .
		HTML::openElement('table') .
		HTML::openElement('tr') .
		HTML::openElement('td') .
		wfMsg( 'archivelinks-modify-blacklist-blacklist-or-whitelist-field-label' ) .
		HTML::closeElement('td') .
		HTML::openElement('td') .
		HTML::element( 'input', array( 'name' => 'bl_blacklist-whitelist', 'type' => 'radio', 'value' => 'blacklist' ) ) .
		'&#160;' .
		wfMsg( 'archivelinks-modify-blacklist-blacklist-field-label' ) .
		HTML::closeElement('td') .
		HTML::closeElement('tr') .
		HTML::openElement('tr') .
		HTML::openElement('td') .
		HTML::closeElement('td') .
		HTML::openElement('td') .
		HTML::element( 'input', array( 'name' => 'bl_blacklist-whitelist', 'type' => 'radio', 'value' => 'whitelist' ) ) .
		'&#160;' .
		wfMsg( 'archivelinks-modify-blacklist-whitelist-field-label' ) .		
		HTML::closeElement('td') .
		HTML::closeElement('tr') .		
		HTML::openElement('tr') .
		HTML::openElement('td') .
		wfMsg( 'archivelinks-modify-blacklist-add-or-remove-field-label' ) .
		HTML::closeElement('td') .
		HTML::openElement('td') .
		HTML::element( 'input', array( 'name' => 'bl_add-remove', 'type' => 'radio', 'value' => 'add' ) ) .
		'&#160;' .
		wfMsg( 'archivelinks-modify-blacklist-add-field-label' ) .
		HTML::closeElement('td') .
		HTML::closeElement('tr') .
		HTML::openElement('tr') .
		HTML::openElement('td') .
		HTML::closeElement('td') .
		HTML::openElement('td') .
		HTML::element( 'input', array( 'name' => 'bl_add-remove', 'type' => 'radio', 'value' => 'remove' ) ) .
		'&#160;' .
		wfMsg( 'archivelinks-modify-blacklist-remove-field-label' ) .		
		HTML::closeElement('td') .
		HTML::closeElement('tr') .
		HTML::openElement('tr') .
		HTML::openElement('td') .
		HTML::element( 'label' , array( 'for' => 'bl_url' ), wfMsg( 'archivelinks-modify-blacklist-url-field-label' ) ) .
		HTML::closeElement('td') .
		HTML::openElement('td') .
		HTML::element( 'input', array( 'name' => 'bl_url', 'type' => 'text', 'size' => '110' ) ) .
		HTML::closeElement('td') .
		HTML::closeElement('tr') .
		HTML::openElement('tr') .
		HTML::openElement('td') .
		HTML::element( 'label', array( 'for' => 'bl_duration' ), wfMsg( 'archivelinks-modify-blacklist-duration-field-label' )) .
		HTML::closeElement('td') .
		HTML::openElement('td') .
		HTML::element( 'input', array( 'name' => 'bl_duration', 'type' => 'text', 'size' => '60' ) ) .
		HTML::closeElement('td') .
		HTML::closeElement('tr') .		
		HTML::openElement('tr') .
		HTML::openElement('td') .
		HTML::element( 'label', array( 'for' => 'bl_reason' ), wfMsg( 'archivelinks-modify-blacklist-reason-field-label' )) .
		HTML::closeElement('td') .
		HTML::openElement('td') .
		HTML::element( 'input', array( 'name' => 'bl_reason', 'type' => 'text', 'size' => '60' ) ) .
		HTML::closeElement('td') .
		HTML::closeElement('tr') .	
		HTML::closeElement('table') .
		XML::submitButton( 'Blacklist URL' ) .
		HTML::closeElement('fieldset') .
		HTML::closeElement('form')
		);	
    }
}