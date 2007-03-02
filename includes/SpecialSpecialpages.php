<?php
/**
 *
 * @addtogroup SpecialPage
 */

/**
 *
 */
function wfSpecialSpecialpages() {
	global $wgOut, $wgUser;

	$wgOut->setRobotpolicy( 'index,nofollow' );
	$sk = $wgUser->getSkin();

	/** Pages available to all */
	wfSpecialSpecialpages_gen( SpecialPage::getRegularPages(), 'spheading', $sk, false );

	/** Restricted special pages */
	wfSpecialSpecialpages_gen( SpecialPage::getRestrictedPages(), 'restrictedpheading', $sk, true );
}

/**
 * sub function generating the list of pages
 * @param $pages the list of pages
 * @param $heading header to be used
 * @param $sk skin object ???
 * @param $restricted, restricted pages or not
 */
function wfSpecialSpecialpages_gen($pages,$heading,$sk,$restricted) {
	global $wgOut, $wgUser, $wgSortSpecialPages, $wgLogRestrictions, $wgLogNames;

	if( count( $pages ) == 0 ) {
		# Yeah, that was pointless. Thanks for coming.
		return;
	}

	/** Put them into a sortable array */
	$sortedPages = array();
	foreach ( $pages as $page ) {
		if ( $page->isListed() ) {
			$sortedPages[$page->getDescription()] = $page->getTitle();
		}
	}
	
	# Add private logs
	if ( $restricted && isset($wgLogRestrictions) ) {
		foreach ( $wgLogRestrictions as $type => $restriction ) {
			$page = SpecialPage::getTitleFor( 'Log', $type );
			if ( $restriction != '' && $wgUser->isAllowed( $restriction ) ) {
				$name = wfMsgHtml( $wgLogNames[$type] );
				$sortedPages[$name] = $page;
			}
		}
	}

	/** Sort */
	if ( $wgSortSpecialPages ) {
		ksort( $sortedPages );
	}

	/** Now output the HTML */
	$wgOut->addHTML( '<h2>' . wfMsgHtml( $heading ) . "</h2>\n<ul>" );
	foreach ( $sortedPages as $desc => $title ) {
		$link = $sk->makeKnownLinkObj( $title , htmlspecialchars( $desc ) );
		$wgOut->addHTML( "<li>{$link}</li>\n" );
	}
	$wgOut->addHTML( "</ul>\n" );
}

?>
