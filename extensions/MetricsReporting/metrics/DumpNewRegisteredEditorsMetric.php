<?php

class DumpNewRegisteredEditorsMetric extends ApiAnalyticsBase {

	public function getAllowedFilters() {
		return array(
			'selectprojects',
			'selectwikis',
		);
	}

	public function getDescription() {
		return 'All registered editors that in a certain month for the first time crossed the threshold of 10 edits since signing up';
	}

	public function getVersion() {
		return __CLASS__ . ': $Id$';
	}
}
