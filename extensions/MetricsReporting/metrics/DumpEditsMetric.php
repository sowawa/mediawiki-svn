<?php

class DumpEditsMetric extends ApiAnalyticsBase {

	public function getAllowedFilters() {
		return array(
			'selectprojects',
			'selectwikis',
		);
	}

	public function getDescription() {
		return 'All edits on articles (as defined by dumparticlecount)';
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
