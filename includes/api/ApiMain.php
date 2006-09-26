<?php


/*
 * Created on Sep 4, 2006
 *
 * API for MediaWiki 1.8+
 *
 * Copyright (C) 2006 Yuri Astrakhan <FirstnameLastname@gmail.com>
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along
 * with this program; if not, write to the Free Software Foundation, Inc.,
 * 59 Temple Place - Suite 330, Boston, MA 02111-1307, USA.
 * http://www.gnu.org/copyleft/gpl.html
 */

if (!defined('MEDIAWIKI')) {
	// Eclipse helper - will be ignored in production
	require_once ("ApiBase.php");
}

/**
* @desc This exception will be thrown when DieUsage is called to stop module execution.
*/
class UsageException extends Exception {

	private $codestr;

	public function __construct($message, $codestr) {
		parent :: __construct($message);
		$this->codestr = $codestr;
	}
	public function __toString() {
		return "{$this->codestr}: {$this->message}";
	}
}

class ApiMain extends ApiBase {

	private $mPrinter, $mModules, $mModuleNames, $mFormats, $mFormatNames, $mApiStartTime, $mResult;

	/**
	* Constructor
	* $apiStartTime - time of the originating call for profiling purposes
	* $modules - an array of actions (keys) and classes that handle them (values) 
	*/
	public function __construct($apiStartTime, $modules, $formats) {
		// Special handling for the main module: $parent === $this
		parent :: __construct($this);

		$this->mModules = $modules;
		$this->mModuleNames = array_keys($modules);
		$this->mFormats = $formats;
		$this->mFormatNames = array_keys($formats);
		$this->mApiStartTime = $apiStartTime;
		$this->mResult = new ApiResult($this);
	}

	public function GetResult() {
		return $this->mResult;
	}

	protected function GetAllowedParams() {
		return array (
			'format' => array (
				GN_ENUM_DFLT => API_DEFAULT_FORMAT,
				GN_ENUM_TYPE => $this->mFormatNames
			),
			'action' => array (
				GN_ENUM_DFLT => 'help',
				GN_ENUM_TYPE => $this->mModuleNames
			)
		);
	}

	protected function GetParamDescription() {
		return array (
			'format' => 'The format of the output',
			'action' => 'What action you would like to perform'
		);
	}

	public function Execute() {
		$action = $format = null;
		try {
			extract($this->ExtractRequestParams());

			// Create an appropriate printer
			$this->mPrinter = new $this->mFormats[$format] ($this, $format);

			// Instantiate and execute module requested by the user
			$module = new $this->mModules[$action] ($this, $action);
			$module->Execute();
			$this->PrintResult(false);
		} catch (UsageException $e) {
			// Printer may not be initialized if the ExtractRequestParams() fails for the main module
			if (!isset ($this->mPrinter))
				$this->mPrinter = new $this->mFormats[API_DEFAULT_FORMAT] ($this, API_DEFAULT_FORMAT);
			$this->PrintResult(true);
		}
	}

	/**
	 * Internal printer
	 */
	private function PrintResult($isError) {
		$this->mPrinter->InitPrinter($isError);
		if (!$this->mPrinter->GetNeedsRawData())
			$this->GetResult()->SanitizeData();
		$this->mPrinter->Execute();
		$this->mPrinter->ClosePrinter();
	}

	protected function GetDescription() {
		return array (
			'',
			'This API allows programs to access various functions of MediaWiki software.',
			''
		);
	}

	public function MainDieUsage($description, $errorCode, $httpRespCode = 0) {
		$this->mResult->Reset();
		$this->mResult->addMessage('error', null, $errorCode);
		if ($httpRespCode === 0)
			header($errorCode, true);
		else
			header($errorCode, true, $httpRespCode);

		$this->mResult->addMessage('usage', null, $this->MakeHelpMsg());

		throw new UsageException($description, $errorCode);
	}

	/**
	 * Override the parent to generate help messages for all available modules.
	 */
	public function MakeHelpMsg() {

		// Use parent to make default message for the main module
		$msg = parent :: MakeHelpMsg();

		$astriks = str_repeat('*** ', 10);
		$msg .= "\n\n$astriks Modules  $astriks\n\n";
		foreach ($this->mModules as $moduleName => $moduleClass) {
			$msg .= "* action=$moduleName *";
			$module = new $this->mModules[$moduleName] ($this, $moduleName);
			$msg2 = $module->MakeHelpMsg();
			if ($msg2 !== false)
				$msg .= $msg2;
			$msg .= "\n";
		}

		$msg .= "\n$astriks Formats  $astriks\n\n";
		foreach ($this->mFormats as $moduleName => $moduleClass) {
			$msg .= "* format=$moduleName *";
			$module = new $this->mFormats[$moduleName] ($this, $moduleName);
			$msg2 = $module->MakeHelpMsg();
			if ($msg2 !== false)
				$msg .= $msg2;
			$msg .= "\n";
		}

		return $msg;
	}

	private $mIsBot = null;
	public function IsBot() {
		if (!isset ($this->mIsBot)) {
			global $wgUser;
			$this->mIsBot = $wgUser->isAllowed('bot');
		}
		return $this->mIsBot;
	}
}
?>