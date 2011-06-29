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

	public function getVersion() {
		return __CLASS__ . ': $Id$';
	}
}
