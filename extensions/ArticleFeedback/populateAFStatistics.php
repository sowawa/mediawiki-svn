<?php

$IP = getenv( 'MW_INSTALL_PATH' );
if ( $IP === false ) {
	$IP = dirname( __FILE__ ) . '/../..';
}
require( "$IP/maintenance/Maintenance.php" );

class PopulateAFStatistics extends Maintenance {
	/**
	 * The number of records to attempt to insert at any given time.
	 * @var int
	 */
	public $insert_batch_size = 100;
	
	/**
	 * The period (in seconds) before now for which to gather stats
	 * @var int
	 */
	public $polling_period = 86400;
	
	/**
	 * The formatted timestamp from which to determine stats
	 * @var int
	 */
	protected $lowerBoundTimestamp;
	
	/**
	 * DB slave
	 * @var object
	 */
	protected $dbr;
	
	/**
	 * DB master
	 * @var object
	 */
	protected $dbw;
	
	public function __construct() {
		parent::__construct();
		$this->mDescription = "Populates the article feedback stats tables";
	}
	
	public function syncDBs() {
		// FIXME: Copied from populateAFRevisions.php, which coppied from updateCollation.php, should be centralized somewhere
		$lb = wfGetLB();
		// bug 27975 - Don't try to wait for slaves if there are none
		// Prevents permission error when getting master position
		if ( $lb->getServerCount() > 1 ) {
			$dbw = $lb->getConnection( DB_MASTER );
			$pos = $dbw->getMasterPos();
			$lb->waitForAll( $pos );
		}
	}
	
	public function execute() {
		global $wgMemc;
		$this->dbr = wfGetDB( DB_SLAVE );
		$this->dbw = wfGetDB( DB_MASTER );
		
		// the data structure to store ratings for a given page
		$ratings = array();  // stores rating-specific info
		$rating_set_count = array(); // keep track of rating sets
		$highs_and_lows = array(); // store highest/lowest rated page stats
		$averages = array(); // store overall averages for a given page
		
		// fetch the ratings since the lower bound timestamp
		$this->output( 'Fetching page ratings between now and ' . date('Y-m-d H:i:s', strtotime( $this->getLowerBoundTimestamp())) . "...\n");
		$res = $this->dbr->select(
			'article_feedback', 
			array( 
				'aa_revision',
				'aa_user_text',
				'aa_rating_id',
				'aa_user_anon_token',
				'aa_page_id', 
				'aa_rating_value',
			), 
			array( 'aa_timestamp >= ' . $this->dbr->addQuotes( $this->getLowerBoundTimestamp() ) ),
			__METHOD__,
			array()
		);
		
		// assign the rating data to our data structure
		foreach ( $res as $row ) {
			// determine the unique hash for a given rating set (page rev + user identifying info)
			$rating_hash = md5( $row->aa_revision . $row->aa_user_text . $row->aa_user_anon_token );
			
			// keep track of how many rating sets a particular page has
			if ( !isset( $rating_count[ $row->aa_page_id ][ $rating_hash ] )) {
				// we store the rating hash as a key rather than value as checking isset( $arr[$hash] ) is way faster
				// than doing something like array_search( $hash, $arr ) when dealing with large arrays
				$rating_set_count[ $row->aa_page_id ][ $rating_hash ] = 1;
			}
			
			$ratings[ $row->aa_page_id ][ $row->aa_rating_id ][] = $row->aa_rating_value; 
		}
		$this->output( "Done\n" );

		// determine the average ratings for a given page
		$this->output( "Determining average ratings for articles ...\n" );
		foreach ( $ratings as $page_id => $data ) {
			// make sure that we have at least 10 rating sets for this page in order to qualify for ranking
			if ( count( array_keys( $rating_set_count[ $page_id ] )) < 5 ) {
				continue;
			}
			
			// calculate the rating averages for a given page
			foreach( $data as $rating_id => $rating ) {
				$rating_sum = array_sum( $rating );
				$rating_avg = $rating_sum / count( $rating );
				$highs_and_lows[ $page_id ][ 'avg_ratings' ][ $rating_id ] = $rating_avg;
			}
			
			// calculate the overall average for a page
			$overall_rating_sum = array_sum( $highs_and_lows[ $page_id ][ 'avg_ratings' ] );
			$overall_rating_average = $overall_rating_sum / count( $highs_and_lows[ $page_id ][ 'avg_ratings' ] );
			$highs_and_lows[ $page_id ][ 'average' ] = $overall_rating_average;
			
			// store overall average rating seperately so we can easily sort
			$averages[ $page_id ] = $overall_rating_average;
		}
		$this->output( "Done.\n" );

		// determine highest 50 and lowest 50
		$this->output( "Determining 50 highest and 50 lowest rated articles...\n" );
		asort( $averages );
		// take lowest 50 and highest 50
		$highest_and_lowest_page_ids = array_slice( $averages, 0, 50, true );
		if ( count( $averages ) > 50 ) {
			$highest_and_lowest_page_ids += array_slice( $averages, -50, 50, true );
		}
		$this->output( "Done\n" );
		
		// prepare data for insert into db
		$this->output( "Preparing data for db insertion ...\n");
		$cur_ts = $this->dbw->timestamp();
		$rows = array();
		foreach( $highs_and_lows as $page_id => $data ) {
			// make sure this is one of the highest/lowest average ratings
			if ( !isset( $highest_and_lowest_page_ids[ $page_id ] )) {
				continue;
			}
			$rows[] = array(
				'afshl_page_id' => $page_id,
				'afshl_avg_overall' => $data[ 'average' ],
				'afshl_avg_ratings' => FormatJson::encode( $data[ 'avg_ratings' ] ),
				'afshl_ts' => $cur_ts,
			);
		}
		$this->output( "Done.\n" );

		// insert data to db
		$this->output( "Writing data to article_feedback_stats_highs_lows ...\n" );
		$rowsInserted = 0;
		while( $rows ) {
			$batch = array_splice( $rows, 0, $this->insert_batch_size );
			$this->dbw->insert( 
				'article_feedback_stats_highs_lows',
				$batch,
				__METHOD__
			);
			$rowsInserted += count( $batch );
			$this->syncDBs();
			$this->output( "Inserted " . $rowsInserted . " rows\n" );
		}
		$this->output( "Done.\n" );
		
		// loading data into caching
		$this->output( "Caching latest highs/lows (if cache present).\n" );
		$key = wfMemcKey( 'article_feedback_stats_highs_lows' );
		$result = $this->dbr->select(
			'article_feedback_stats_highs_lows',
			array(
				'afshl_page_id',
				'afshl_avg_overall',
				'afshl_avg_ratings'
			),
			array( 'afshl_ts' => $cur_ts ),
			__METHOD__,
			array( "ORDER BY" => "afshl_avg_overall" )
		);
		// grab the article feedback special page so we can reuse the data structure building code
		// FIXME this logic should not be in the special page class
		$highs_lows = SpecialArticleFeedback::buildHighsAndLows( $result );
		// stash the data structure in the cache
		$wgMemc->set( $key, $highs_lows, 86400 );
		$this->output( "Done\n" );
	}
	
	
	/**
	 * Set $this->timestamp
	 * @param int $ts
	 */
	public function setLowerBoundTimestamp( $ts ) {
		if ( !is_numeric( $ts )) {
			throw new InvalidArgumentException( 'Timestamp must be numeric.' );
		}
		$this->lowerBoundTimestamp = $ts;
	}
	

	/**
	 * Get $this->lowerBoundTimestamp
	 * 
	 * If it hasn't been set yet, set it based on the defined polling period.
	 * 
	 * @return int
	 */
	public function getLowerBoundTimestamp() {
		if ( !$this->lowerBoundTimestamp ) {
			$timestamp = $this->dbw->timestamp( strtotime( $this->polling_period . ' seconds ago' ));
			$this->setLowerBoundTimestamp( $timestamp );
		}
		return $this->lowerBoundTimestamp;
	}
}

$maintClass = "PopulateAFStatistics";
require_once( DO_MAINTENANCE );
