<?php
/* run this script out of your $IP/maintenance folder */
require_once( 'Maintenance.php' );

class FixOTSLinks extends Maintenance {
	public function __construct() {
		parent::__construct();
		$this->mDescription = "Fix oracle text index links for documents";
		$this->addOption( 'all', 'Update all links (not only missing ones)' );
	}

	public function execute() {
		$doAll = $this->hasOption( 'all' );
		if ($doAll) {
			$this->output( "Recreate all index links to documents\n\n" );
		} else {
			$this->output( "Recreate missing index links to documents\n\n" );
		}
		$this->doRecreate($doAll);
	}

	private function doRecreate($all) {
		global $wgExIndexMIMETypes, $wgExIndexOnHTTP;
		$dbw = wfGetDB( DB_MASTER );

		$tbl_pag = $dbw->tableName( 'page' );
		$tbl_idx = $dbw->tableName( 'searchindex' );
		
		$searchWhere = $all ? '' : ' AND NOT EXISTS (SELECT null FROM '.$tbl_idx.' WHERE si_page=p.page_id AND si_url IS NOT null)';
		$result = $dbw->doQuery('SELECT p.page_id FROM '.$tbl_pag.' p WHERE p.page_namespace = '.NS_FILE.$searchWhere );
		$this->output($result->numRows().' files found.'."\n\n");
		
		$syncIdx = false;
		
		while (($row = $result->fetchObject()) !== false) {
			$titleObj = Title::newFromID($row->page_id);
			$file = wfLocalFile($titleObj->getText());

			$this->output('Updating "'.$titleObj->getText().'" ... ');

			if (in_array( $file->getMimeType(), $wgExIndexMIMETypes )) {
				$url = $wgExIndexOnHTTP ? preg_replace( '/^https:/i', 'http:', $file->getFullUrl() ) : $file->getFullUrl();
				$dbw->update('searchindex',
					array( 'si_url' => $url ), 
					array( 'si_page' => $row->page_id ),
					'SearchIndexUpdate:update' );
				$this->output('complete'."\n");
				$syncIdx = true;
			} else {
				$this->output('skipped (unsupported or excluded mime-type)'."\n");
			}
		}
		
		if ( $syncIdx ) {
			$this->output("\n".'Syncing Index'."\n");
			$index = $dbw->getProperty('mTablePrefix')."si_url_idx";
			$dbw->query( "CALL ctx_ddl.sync_index('$index')" );
		}
		
		$this->output('Recreate finished');
	}
}

$maintClass = "FixOTSLinks";
require_once( DO_MAINTENANCE );

