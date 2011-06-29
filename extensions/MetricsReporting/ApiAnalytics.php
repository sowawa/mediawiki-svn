<?php

class ApiMetrics extends ApiBase {

	private $metricModuleNames;

	private $metricModules = array(
	);

	public function __construct( $main, $action ) {
		parent::__construct( $main, $action );
		$this->metricModuleNames = array_keys( $this->metricModules );
	}

	public function execute() {
	}

	public function getAllowedParams() {
		return array(
			'metric' => array(
				ApiBase::PARAM_ISMULTI => true,
				ApiBase::PARAM_TYPE => $this->metricModuleNames
			),
		);
	}

	public function getParamDescription() {
		return array(
			'metric' => '',
		);
	}

	public function getDescription() {
		return array(
			''
		);
	}

	/*public function getPossibleErrors() {
		return array_merge( parent::getPossibleErrors(), array(
		) );
	}*/

	protected function getExamples() {
		return array(
			'api.php?action=analytics&metric=',
		);
	}

	/**
	 * @return string
	 */
	public function makeHelpMsg() {
		$msg = '';

		$querySeparator = str_repeat( '--- ', 12 );
		$moduleSeparator = str_repeat( '*** ', 14 );
		$msg .= "\n$querySeparator Analytics: Metric  $querySeparator\n\n";
		$msg .= $this->makeHelpMsgHelper( $this->metricModules, 'metric' );
		$msg .= "\n\n$moduleSeparator Modules: continuation  $moduleSeparator\n\n";

		// Perform the base call last because the $this->mAllowedGenerators
		// will be updated inside makeHelpMsgHelper()
		// Use parent to make default message for the query module
		$msg = parent::makeHelpMsg() . $msg;

		return $msg;
	}

	/**
	 * For all modules in $moduleList, generate help messages and join them together
	 * @param $moduleList Array array(modulename => classname)
	 * @param $paramName string Parameter name
	 * @return string
	 */
	private function makeHelpMsgHelper( $moduleList, $paramName ) {
		$moduleDescriptions = array();

		foreach ( $moduleList as $moduleName => $moduleClass ) {
			$module = new $moduleClass( $this, $moduleName, null );

			$msg = ApiMain::makeHelpMsgHeader( $module, $paramName );
			$msg2 = $module->makeHelpMsg();
			if ( $msg2 !== false ) {
				$msg .= $msg2;
			}
			$moduleDescriptions[] = $msg;
		}

		return implode( "\n", $moduleDescriptions );
	}

	public function getVersion() {
		return __CLASS__ . ': $Id: $';
	}
}
