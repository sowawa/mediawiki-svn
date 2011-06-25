<?php
/**
 * This class is for the actual spidering and will be calling wget
 */

$path = getenv( 'MW_INSTALL_PATH' );
if ( strval( $path ) === '' ) {
    $path = dirname( __FILE__ ) . '/../..';
}

require_once "$path/maintenance/Maintenance.php";

class ArchiveLinksSpider extends Maintenance {
    private $db_master;
    private $db_slave;
    private $db_result;
    
    public function execute() {
	global $wgArchiveLinksConfig;
	
	$this->db_master = $this->getDB( DB_MASTER );
	$this->db_slave = $this->getDB( DB_SLAVE );
	$this->db_result = array();
	
	if ( $wgArchiveLinksConfig['run_spider_in_loop'] ) {
	    while ( TRUE ) {		
		if ( ( $url = $this->check_queue() ) !== false ) {
		    //do stuff
		}
		sleep(1);
	    }
	} else {
	    if ( ( $url = $this->check_queue() ) !== false ) {
		//do stuff
	    }
	}
	return null;
    }
    
    private function check_queue() {
	$this->db_result['job-fetch'] = $this->db_slave->select( 'el_archive_queue', '*', 
		'`el_archive_queue`.`delay_time` <= ' . time()
		. ' AND `el_archive_queue`.`in_progress` = 0'
		. ' ORDER BY `el_archive_queue`.`queue_id` ASC'
		. ' LIMIT 1');
	
	if ( $this->db_result['job-fetch']->numRows() > 0 ) {
	    $row = $this->db_result['job-fetch']->fetchRow();	    
	    
	    //Since we querried the slave to check for dups when we insterted instead of the master let's check
	    //that the job isn't in the queue twice, we don't want to archive it twice
	    $this->db_result['dup-check'] = $this->db_slave->select( 'el_archive_queue', '*', '`el_archive_queue`.`url` = "' . $row['url']
		    . '" ORDER BY `el_archive_queue`.`queue_id` ASC' );
	    
	    if ( $this->db_result['dup-check']->numRows() > 1 ) {
		//keep only the original jobs and remove all duplicates
		$this->db_result['dup-check']->fetchRow();
		while ( $del_row = $this->db_result['dup-check']->fetchRow() ) {
		    echo 'you have a dup ';
		    var_dump( $del_row );
		    //this is commented for testing purposes, so I don't have to keep readding the duplicate to my test db
		    //in other words this has a giant "remove before flight" ribbon hanging from it...
		    //$this->db_master->delete( 'el_archive_queue', '`el_archive_queue`.`queue_id` = ' . $del_row['queue_id'] );    
		}
		
	    }
	    
	    return $row['url'];
	} else {
	    //there are no jobs to do right now
	    return false;
	}
    }
}

$maintClass = 'ArchiveLinksSpider';
require_once RUN_MAINTENANCE_IF_MAIN;

?>