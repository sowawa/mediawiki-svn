<?php

class DumpNewRegisteredEditorsMetric extends ApiAnalyticsBase {

	public function getAllowedFilters() {
		return array(
			'selectprojects',
			'selectwikis',
		);
	}

	protected function getQueryInfo() {
		return array();
	}

	protected function getQueryFields() {
		return array();
	}

	public function getDescription() {
		return 'All registered editors that in a certain month for the first time crossed the threshold of 10 edits since signing up';
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
