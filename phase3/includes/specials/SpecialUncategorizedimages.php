<?php
/**
 * Special page lists images which haven't been categorised
 *
 * @file
 * @ingroup SpecialPage
 * @author Rob Church <robchur@gmail.com>
 */

/**
 * @ingroup SpecialPage
 */
// FIXME: Use an instance of UncategorizedPagesPage or something
class UncategorizedImagesPage extends ImageQueryPage {

	function getName() {
		return 'Uncategorizedimages';
	}

	function sortDescending() {
		return false;
	}

	function isExpensive() {
		return true;
	}

	function isSyndicated() {
		return false;
	}

	function getSQL() {
		$dbr = wfGetDB( DB_SLAVE );
		list( $page, $categorylinks ) = $dbr->tableNamesN( 'page', 'categorylinks' );
		$ns = NS_FILE;

		return "SELECT 'Uncategorizedimages' AS type, page_namespace AS namespace,
				page_title AS title, page_title AS value
				FROM {$page} LEFT JOIN {$categorylinks} ON page_id = cl_from
				WHERE cl_from IS NULL AND page_namespace = {$ns} AND page_is_redirect = 0";
	}
	
	function getQueryInfo() {
		return array (
			'tables' => array( 'page', 'categorylinks' ),
			'fields' => array( 'page_namespace AS namespace',
					'page_title AS title',
					'page_title AS value' ),
			'conds' => array( 'cl_from IS NULL',
					'page_namespace' => NS_FILE,
					'page_is_redirect' => 0 ),
			'join_conds' => array( 'categorylinks' => array(
					'LEFT JOIN', 'cl_from=page_id' ) )
		);
	}

}

function wfSpecialUncategorizedimages() {
	$uip = new UncategorizedImagesPage();
	list( $limit, $offset ) = wfCheckLimits();
	return $uip->doQuery( $offset, $limit );
}
