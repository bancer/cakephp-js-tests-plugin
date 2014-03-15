<?php
App::uses('ICoverageAdapter', 'JsTests.Lib');

/**
 * Abstract coverage adapter contraining common functionality of 
 * JSCoverageAdapter and JSCoverAdapter.
 */
abstract class ACoverageAdapter implements ICoverageAdapter {
	
	const NO_INSTRUMENT_FORMAT = '--no-instrument="%s"';
	
	const EXCLUDE_FORMAT = '--exclude="%s"';
	
	/**
	 * Executable command format getter.
	 */
	protected abstract function _getExecuteFormat();
	
	protected $executable;

	public function __construct($executable) {
		if (!file_exists($executable)){
			trigger_error('JSCoverage executable not found!');
			return;
		}
		$this->executable = $executable;
	}
	
	public function execute($noInstrument, $exclude, $sourceDir, $targetDir) {
		foreach ($noInstrument as &$item) {
			$item = sprintf(self::NO_INSTRUMENT_FORMAT, $item);
		}
		foreach ($exclude as &$item) {
			$item = sprintf(self::EXCLUDE_FORMAT, $item);
		}
		$command = sprintf(
			$this->_getExecuteFormat(),
			$this->executable,
			join(' ', $noInstrument),
			join(' ', $exclude),
			str_replace('\\', '/', $sourceDir),
			str_replace('\\', '/', $targetDir)
		);
		if (DIRECTORY_SEPARATOR != '\\') {
			$command = $command.' 2>&1';
		}
		// 		else {
		// 			$command = str_replace('\\', '/', $command);
		// 		}
		//		pr($command);die;
		$output = array();
		$exitCode = null;
		if (!defined('CAKEPHP_UNIT_TEST_EXECUTION')) {
			exec($command, $output, $exitCode);
		}
		return array('output' => $output, 'exitCode' => $exitCode);
	}
}