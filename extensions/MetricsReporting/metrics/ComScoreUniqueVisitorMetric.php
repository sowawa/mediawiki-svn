<?php

class ComScoreUniqueVisitorMetric extends ApiAnalyticsBase {

	public function getAllowedFilters() {
		return array(
			'selectregions',
			'selectcountries',
		);
	}

	protected function getQueryInfo() {
		return array(
			'table' => array( 'comscore', 'comscore_regions' ),
			'conds' => array(),
			'options' => array( 'ORDER BY' => 'date, project_code, region_code' ),
			'join_conds' => array( 'comscore_regions' => array( 'LEFT JOIN', 'comscore.region_code = comscore_regions.region_code' ) ),
		);
	}

	protected function getQueryFields() {
		return array(
			'date', 'country_code', /* 'country_name', */ 'comscore.region_code',
			'region_name', 'web_property', 'project_code', 'reach', 'visitors'
		);
	}

	public function getDescription() {
		return 'Unique persons that visited one of the Wikimedia wikis at least once in a certain month';
	}

	protected function getExamples() {
		return array(
			'api.php?action=analytics&metric=',
		);
	}

	public function getVersion() {
		return __CLASS__ . ': $Id$';
	}
}
