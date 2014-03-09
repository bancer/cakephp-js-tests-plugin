<?php
App::uses('ICoverageAdapter', 'JsTests.Lib');

class JSCoverageAdapter implements ICoverageAdapter {
	
	const EXECUTE_FORMAT = '%s -v %s %s "%s" "%s"';
	
	const NO_INSTRUMENT_FORMAT = '--no-instrument="%s"';
	
	const EXCLUDE_FORMAT = '--exclude="%s"';
	
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
			self::EXECUTE_FORMAT,
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