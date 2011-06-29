<?php

class ComScoreReachPercentageMetric extends ApiAnalyticsBase {

	public function getAllowedFilters() {
		return array(
			'selectregions',
			'selectcountries',
		);
	}

	public function getDescription() {
		return 'Percentage of total unique visitors to any web property which also visited a Wikimedia wiki';
	}

	public function getVersion() {
		return __CLASS__ . ': $Id$';
	}
}
