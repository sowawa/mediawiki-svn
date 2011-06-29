<?php

class DumpBinaryCountMetric extends ApiAnalyticsBase {

	public function getAllowedFilters() {
		return array(
			'selectprojects',
			'selectwikis',
		);
	}

	public function getDescription() {
		return 'All binary files (nearly all of which are multimedia files) available for download/article inclusion on a wiki';
	}

	public function getVersion() {
		return __CLASS__ . ': $Id$';
	}
}
