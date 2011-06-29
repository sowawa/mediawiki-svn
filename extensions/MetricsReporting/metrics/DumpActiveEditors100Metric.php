<?php

class DumpActiveEditors100Metric extends ApiAnalyticsBase {

	public function getAllowedFilters() {
		return array(
			'selectprojects',
			'selectwikis',
		);
	}

	public function getDescription() {
		return 'All registered editors that made 100 or more edits in a certain month';
	}

	public function getVersion() {
		return __CLASS__ . ': $Id$';
	}
}
