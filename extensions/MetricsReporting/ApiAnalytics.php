<?php

class ApiAnalytics extends ApiBase {

	private $metricModuleNames, $params;

	private $metricModules = array(
	);

	public function __construct( $main, $action ) {
		parent::__construct( $main, $action );
		$this->metricModuleNames = array_keys( $this->metricModules );
	}

	public function execute() {
		$this->params = $this->extractRequestParams();

		// Instantiate requested modules
		$modules = array();
		$this->instantiateModules( $modules, 'prop', $this->mQueryPropModules );

		// Execute all requested modules.
		foreach ( $modules as $module ) {
			$module->profileIn();
			$module->execute();
			$module->profileOut();
		}
	}

	/**
	 * Create instances of all modules requested by the client
	 * @param $modules Array to append instantiated modules to
	 * @param $param string Parameter name to read modules from
	 * @param $moduleList Array array(modulename => classname)
	 */
	protected function instantiateModules( &$modules, $param, $moduleList ) {
		if ( isset( $this->params[$param] ) ) {
			foreach ( $this->params[$param] as $moduleName ) {
				$modules[] = new $moduleList[$moduleName] ( $this, $moduleName );
			}
		}
	}

	public function getAllowedParams() {
		return array(
			'metric' => array(
				ApiBase::PARAM_ISMULTI => false,
				ApiBase::PARAM_TYPE => $this->metricModuleNames,
				ApiBase::PARAM_REQUIRED => true,
			),
		);
	}

	public function getParamDescription() {
		return array(
			'metric' => array(
				'Type of data to collect',
				'About metric names: these include source of data, to allow for alternate sources of similar metrics,',
				'which likely are defined differently or have other intrinsic issues (e.g. precision/reliability).',
			),
		);
	}

	public function getDescription() {
		return array(
			'Collect data from the analytics database'
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
		$msg .= "\n$querySeparator Analytics: Metrics  $querySeparator\n\n";
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
		return __CLASS__ . ': $Id$';
	}
}
