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

	public function execute() {
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
				ApiBase::PARAM_DFLT => 'timeseries',
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
				ApiBase::PARAM_TYPE => array(
					'AF',
					'AX',
					'AL',
					'DZ',
					'AS',
					'AD',
					'AO',
					'AI',
					'AQ',
					'AG',
					'AR',
					'AM',
					'AW',
					'AU',
					'AT',
					'AZ',
					'BS',
					'BH',
					'BD',
					'BB',
					'BY',
					'BE',
					'BZ',
					'BJ',
					'BM',
					'BT',
					'BO',
					'BQ',
					'BA',
					'BW',
					'BV',
					'BR',
					'IO',
					'BN',
					'BG',
					'BF',
					'BI',
					'KH',
					'CM',
					'CA',
					'CV',
					'KY',
					'CF',
					'TD',
					'CL',
					'CN',
					'CX',
					'CC',
					'CO',
					'KM',
					'CG',
					'CD',
					'CK',
					'CR',
					'CI',
					'HR',
					'CU',
					'CW',
					'CY',
					'CZ',
					'DK',
					'DJ',
					'DM',
					'DO',
					'EC',
					'EG',
					'SV',
					'GQ',
					'ER',
					'EE',
					'ET',
					'FK',
					'FO',
					'FJ',
					'FI',
					'FR',
					'GF',
					'PF',
					'TF',
					'GA',
					'GM',
					'GE',
					'DE',
					'GH',
					'GI',
					'GR',
					'GL',
					'GD',
					'GP',
					'GU',
					'GT',
					'GG',
					'GN',
					'GW',
					'GY',
					'HT',
					'HM',
					'VA',
					'HN',
					'HK',
					'HU',
					'IS',
					'IN',
					'ID',
					'IR',
					'IQ',
					'IE',
					'IM',
					'IL',
					'IT',
					'JM',
					'JP',
					'JE',
					'JO',
					'KZ',
					'KE',
					'KI',
					'KP',
					'KR',
					'KW',
					'KG',
					'LA',
					'LV',
					'LB',
					'LS',
					'LR',
					'LY',
					'LI',
					'LT',
					'LU',
					'MO',
					'MK',
					'MG',
					'MW',
					'MY',
					'MV',
					'ML',
					'MT',
					'MH',
					'MQ',
					'MR',
					'MU',
					'YT',
					'MX',
					'FM',
					'MD',
					'MC',
					'MN',
					'ME',
					'MS',
					'MA',
					'MZ',
					'MM',
					'NA',
					'NR',
					'NP',
					'NL',
					'NC',
					'NZ',
					'NI',
					'NE',
					'NG',
					'NU',
					'NF',
					'MP',
					'NO',
					'OM',
					'PK',
					'PW',
					'PS',
					'PA',
					'PG',
					'PY',
					'PE',
					'PH',
					'PN',
					'PL',
					'PT',
					'PR',
					'QA',
					'RE',
					'RO',
					'RU',
					'RW',
					'BL',
					'SH',
					'KN',
					'LC',
					'MF',
					'PM',
					'VC',
					'WS',
					'SM',
					'ST',
					'SA',
					'SN',
					'RS',
					'SC',
					'SL',
					'SG',
					'SX',
					'SK',
					'SI',
					'SB',
					'SO',
					'ZA',
					'GS',
					'ES',
					'LK',
					'SD',
					'SR',
					'SJ',
					'SZ',
					'SE',
					'CH',
					'SY',
					'TW',
					'TJ',
					'TZ',
					'TH',
					'TL',
					'TG',
					'TK',
					'TO',
					'TT',
					'TN',
					'TR',
					'TM',
					'TC',
					'TV',
					'UG',
					'UA',
					'AE',
					'GB',
					'US',
					'UM',
					'UY',
					'UZ',
					'VU',
					'VE',
					'VN',
					'VG',
					'VI',
					'WF',
					'EH',
					'YE',
					'ZM',
					'ZW',

				),
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

		return array_merge( $params, array_intersect_key( $select, array_flip( $this->getAllowedFilters() ) ) );
	}

	public function getParamDescription() {
		return array(
			'months' => 'First and last month to include in time series',
			'normalized' => array(
				'Only applies to squidpageviews, where data for each month are recalculated to 30 days (other metrics may follow)',
				'(WMF Report Card will use normalized time series when available)',
			),
			'data' => array(
					' timeseries - returns ordered list of value pairs, on efor each month within range',
					' timeseriesindexed - like timeseries, but each month\'s value will be relative to oldest month\'s value which is always 100',
					' percentagegrowthlastmonth, percentagegrowthlastyear, percentagegrowthfullperiod',
					'  growth percentages are relative to oldest value (80->100=25%) although trivial, requesting these metrics through API ensures all clients use same calculation',
			),
			'reportlanguage' => 'Language code, used to expand region and country codes into region and country name',
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
