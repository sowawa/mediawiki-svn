<?php

/**
 * Represents an article and associated rating 
 **/
class Rating {
	public $project;
	public $namespace;
	public $title;
	public $quality;
	public $quality_timestamp;
	public $importance;
	public $importance_timestamp;

	private $old_importance;
	private $old_quality;
	private $inDB = false;

	private static function getImportanceColumn( $importance ) {
		$importanceColumnMapping = array(
			'top' => 'ps_top_icount',
			'high' => 'ps_high_icount',
			'mid' => 'ps_mid_icount',
			'low' => 'ps_mid_icount',
			'no' => 'ps_no_icount',
			'' => 'ps_unclassified_icount'
		);
		return $importanceColumnMapping[ strtolower( $importance ) ];
	}

	public function __construct( $project, $namespace, $title, $quality, $quality_timestamp, $importance, $importance_timestamp ) {
		$this->project = $project;
		$this->namespace = $namespace;
		$this->title = $title;
		$this->quality = $quality;
		$this->quality_timestamp = $quality_timestamp;
		$this->importance = $importance;
		$this->importance_timestamp = $importance_timestamp;
	}

	public function update( $importance, $quality, $timestamp ) {
		if( $quality != $this->quality ) {
			$this->old_quality = $this->quality;
			$this->quality = $quality;
			$this->quality_timestamp = $timestamp;
		}
		if( $importance != $this->importance ) {
			$this->old_importance = $this->importance;
			$this->importance = $importance;
			$this->importance_timestamp = $timestamp;
		}
		$this->saveAll();
	}

	// Note: Huge sql injection vector ahead. FIXME
	private function updateAggregateStats( $is_new_rating ) {
		if(! $is_new_rating && empty($this->old_importance) && empty($this->old_quality) ) {
			return;
		}
		$dbw = wfGetDB( DB_MASTER );
		// Rating has just been detected.
		// So we can ignore $old_importance and $old_quality
		$importance_column = Rating::getImportanceColumn( $this->importance );
		$query = "INSERT INTO project_stats (ps_project, ps_quality, $importance_column) ";
		$query .= "VALUES ('$this->project', '$this->quality', 1) ";
		$query .= "ON DUPLICATE KEY ";
		$query .= "UPDATE $importance_column = $importance_column + 1 ";
		if(! $is_new_rating  && ! empty( $this->old_importance ) ) {
			$old_importance_column = Rating::getImportanceColumn( $this->old_importance );
			$query .= ", $old_importance_column = $old_importance_column - 1";
		}
		$query .= ";";
		$dbw->query($query);
		if(! $is_new_rating && ! empty( $this->old_quality ) ) {
			if(! isset($old_importance_column) ) {
				$old_importance_column = $importance_column;
			}
			$query = "UPDATE project_stats SET $old_importance_column = $old_importance_column - 1 ";
			$query .= "WHERE ps_project = '$this->project' and ps_quality = '$this->old_quality';";
			$dbw->query($query);
		}
	}
	public function saveAll() {
		$data_array = array(
			'r_project' => $this->project,
			'r_namespace' => $this->namespace,
			'r_article' => $this->title,
			'r_quality' => $this->quality,
			'r_quality_timestamp' => $this->quality_timestamp,
			'r_importance' => $this->importance,
			'r_importance_timestamp' => $this->importance_timestamp
		);
		$dbw = wfGetDB( DB_MASTER );
		if( $this->inDB ) {
			$dbw->update(
				"ratings",
				$data_array,
				array(
					'r_namespace' => $this->namespace,
					'r_article' => $this->title,
					'r_project' => $this->project
				),
				__METHOD__
			);

			$this->updateAggregateStats( false );
		} else {
			$dbw->insert(
				"ratings",
				$data_array,
				__METHOD__
			);

			$this->updateAggregateStats( true );
			$this->inDB = true;
		}

	}

	public static function forTitle( $title ) {
		$dbr = wfGetDB( DB_SLAVE );
		$query = $dbr->select(
			"ratings",
			array(
				"r_project", "r_namespace", "r_article", "r_quality", 
				"r_quality_timestamp", "r_importance", "r_importance_timestamp"
			),
			array(
				"r_namespace" => $title->getNamespace(),
				"r_article" => $title->getText(),
			),
			__METHOD__
		);

		$ratings = array();

		foreach( $query as $row ) {
			$rating = new Rating( 
				$row->r_project, $row->r_namespace,
				$row->r_article, $row->r_quality,
				$row->r_quality_timestamp, $row->r_importance,
				$row->r_importance_timestamp);
			$rating->inDB = true;
			$ratings[$rating->project] = $rating;
		}
		return $ratings;
	}
}       
