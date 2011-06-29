<?php

class DumpActiveEditors5Metric extends ApiAnalyticsBase {

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
		return 'All registered editors that made 5 or more edits in a certain month';
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
