<?php

abstract class ApiAnalyticsBase extends ApiBase/*ApiQueryBase*/ {

	protected $mDb;

	public function __construct( ApiBase $query, $moduleName, $paramPrefix = '' ) {
		parent::__construct( $query->getMain(), $moduleName, $paramPrefix );
	}

	/**
	 * Get the Query database connection (read-only)
	 * @return DatabaseBase
	 */
	protected function getDB() {
		if ( is_null( $this->mDb ) ) {
			global $wgMetricsDBserver, $wgMetricsDBname, $wgMetricsDBuser, $wgMetricsDBpassword, $wgMetricsDBtype;
			$this->mDb = DatabaseBase::factory( $wgMetricsDBtype,
				array(
					'host' => $wgMetricsDBserver,
					'user' => $wgMetricsDBuser,
					'password' => $wgMetricsDBpassword,
					'dbname' => $wgMetricsDBname,
				)
			);
		}
		return $this->mDb;
	}

	/*public function getPossibleErrors() {
		return array_merge( parent::getPossibleErrors(), array(
		) );
	}*/

	public function getVersion() {
		return __CLASS__ . ': $Id$';
	}
}
