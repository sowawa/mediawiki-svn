<?php

class DumpBinaryCountMetric extends ApiAnalyticsBase {

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
		return 'All binary files (nearly all of which are multimedia files) available for download/article inclusion on a wiki';
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
