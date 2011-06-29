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

	/**
	 * @return array
	 */
	public function getAllowedFilters() {
		return array();
	}

	public function getAllowedParams() {
		$params = array(
			'months' => 'string',
			'normalized' => 'bool',
			'data' => array(
				ApiBase::PARAM_ISMULTI => true,
				ApiBase::PARAM_TYPE => array(
					'timeseries',
					'timeseriesindexed',
					'percentagegrowthlastmonth',
					'percentagegrowthlasyyear',
					'percentagegrowthfullperiod',
				),
			),
			'reportlanguage' => array(
				ApiBase::PARAM_DFLT => 'en',
				ApiBase::PARAM_TYPE => array(
					'en',
				),
			),
		);

		$select = array(
			'selectregions' => array(
				ApiBase::PARAM_ISMULTI => true,
				ApiBase::PARAM_TYPE => array(
					'as',
					'c',
					'eu',
					'i',
					'la',
					'ma',
					'na',
					'us',
					'w',
				),
			),
			'selectcountries' => array(
				ApiBase::PARAM_ISMULTI => false,
				ApiBase::PARAM_TYPE => 'string',
			),
			'selectwebproperties' => array(
				ApiBase::PARAM_ISMULTI => false,
				ApiBase::PARAM_TYPE => 'string',
			),
			'selectprojects' => array(
				ApiBase::PARAM_ISMULTI => false,
				ApiBase::PARAM_TYPE => array(
					'wb',
					'wk',
					'wm',
					'wp',
					'wq',
					'ws',
					'wv',
					'co',
					'wx',
				),
			),
			'selectwikis' => array(
				ApiBase::PARAM_ISMULTI => false,
				ApiBase::PARAM_TYPE => 'string',
			),
			'selecteditors' => array(
				ApiBase::PARAM_ISMULTI => false,
				ApiBase::PARAM_TYPE => array(
					'a',
					'r',
					'b',
				),
			),
			'selectedits' => array(
				ApiBase::PARAM_ISMULTI => false,
				ApiBase::PARAM_TYPE => array(
					'm',
					'b',
				),
			),
			'selectplatforms' => array(
				ApiBase::PARAM_ISMULTI => false,
				ApiBase::PARAM_TYPE => array(
					'm',
					'n'
				),
			),
		);

		return array_merge( $params, array_intersect_key( $select, $this->getAllowedFilters() ) );
	}

	public function getParamDescription() {
		return array(
			'months' => 'First and last month to include in time series',
			'normalized' => array(

			),
			'data' => array(

			),
			'reportlanguage' => '',
			'selectregions' => '',
			'selectcountries' => '',
			'selectwebproperties' => '',
			'selectprojects' => '',
			'selectwikis' => '',
			'selecteditors' => '',
			'selectedits' => '',
			'selectplatform' => '',
		);
	}

	//public abstract function getDescription();

	//protected abstract function getExamples();

	/*public function getPossibleErrors() {
		return array_merge( parent::getPossibleErrors(), array(
		) );
	}*/

	public function getVersion() {
		return __CLASS__ . ': $Id$';
	}
}
