<?php
/**
	CakePHP JsTests Plugin - JavaScript unit tests and code coverage

	Copyright (C) 2010-3827 dr. Hannibal Lecter / lecterror
	<http://lecterror.com/>

	Multi-licensed under:
		MPL <http://www.mozilla.org/MPL/MPL-1.1.html>
		LGPL <http://www.gnu.org/licenses/lgpl.html>
		GPL <http://www.gnu.org/licenses/gpl.html>
*/

App::uses('Component', 'Controller');
App::uses('Router', 'Routing');

class TestHandlerComponent extends Component {
	
	public $name = 'TestHandler';
	
	protected $_tests = array();
	
	public $_profiles = array();

	/**
	 * Detects tests for a given profile, reads all the data for the test
	 * and prepares everything for testing.
	 *
	 * @param string $profileName
	 * @param array $profileData
	 * @return array A set of detected tests for a profile.
	 */
	function loadTests($profileName) {
		$this->_tests = array($profileName => array());

		// detect tests
		$views = $this->_findTestViews($profileName);

		foreach ($views as $view) {
			// get the name and related test files
			$action = basename($view);
			$testName = $this->_getTestName($profileName, $action);
			$jsTests = $this->_findJsTestFiles($profileName, $view, $testName);
			$jsCoverageTests = $this->_findJsCoverageTests($profileName, $jsTests);
			$instrumentedExists = $this->_coverageExists($profileName, $action);
			$coverageView = $this->_findCoverageView($profileName, $action);
			$instrumentedIsUpdated = $this->_coverageFilesAreUpToDate($instrumentedExists,
					$jsTests, $jsCoverageTests, $view, $coverageView);
			
			$this->_tests[$profileName][$testName] = array(
				'mainTestFile' 					=> $action,					// test file action (used to build url)
				'normalTestPath' 				=> $view,					// used nowhere
				'instrumentedTestPath' 			=> $coverageView, 			// used nowhere
				'normalRelatedTestFiles' 		=> $jsTests,				// used nowhere
				'instrumentedRelatedTestFiles' 	=> $jsCoverageTests,		// used nowhere
				'instrumentedExists' 			=> $instrumentedExists,		// this flag is used to display coverage links
				'instrumentedIsUpdated' 		=> $instrumentedIsUpdated	// this flag is used to show whether the coverage needs an update
			);
		}
		return $this->_tests[$profileName];
	}
	
	protected function _getTestName($profileName, $action) {
		$matches = array();	
		$testName = preg_match('#'.$this->_profiles[$profileName]['params']['name'].'#', $action, $matches);
		$testName = isset($matches['name']) ? $matches['name'] : $matches[0];
		return $testName;
	}
	
	protected function _findTestViews($profileName) {
		$testsGlob = $this->_profiles[$profileName]['dir']['normal_tests'].$this->_profiles[$profileName]['params']['tests'];
		$views = glob($testsGlob);
		return $views;
	}
	
	protected function _findJsTestFiles($profileName, $view, $testName) {
		$testFiles = array();
		foreach ($this->_profiles[$profileName]['params']['files'] as $pattern) {
			$relatedPattern = dirname($view).DS.str_replace('%name%', $testName, $pattern);
			$testFiles = array_merge($testFiles, glob($relatedPattern));
		}
		return $testFiles;
	}
	
	protected function _findJsCoverageTests($profileName, $jsTests) {
		return str_replace(
			$this->_profiles[$profileName]['dir']['normal_tests'],
			$this->_profiles[$profileName]['dir']['instrumented_tests'],
			$jsTests
		);
	}
	
	protected function _coverageExists($profileName, $action) {
		return file_exists($this->_profiles[$profileName]['dir']['instrumented_tests'].$action);
	}
	
	protected function _findCoverageView($profileName, $action) {
		return $this->_profiles[$profileName]['dir']['instrumented_tests'].$action;
	}
	
	protected function _coverageFilesAreUpToDate($instrumentedExists, $testFiles, $jsCoverageTests, $view, $coverageView) {
		$instrumentedIsUpdated = false;
		// check if the instrumented version is up to date
		if ($instrumentedExists) {
			$lastNormalModification = $this->_testFilesLastModificationTime($testFiles, $view);
			$lastInstrumentedModification = $this->_coverageFilesLastModificationTime($jsCoverageTests, $coverageView);
			$instrumentedIsUpdated = $lastInstrumentedModification >= $lastNormalModification;
		}
		return $instrumentedIsUpdated;
	}
	
	protected function _testFilesLastModificationTime($testFiles, $view) {
		$lastNormalModification = filemtime($view);
		foreach ($testFiles as $testFile) {
			if (file_exists($testFile)) {
				$tmp_mtime = filemtime($testFile);
				$lastNormalModification = $tmp_mtime > $lastNormalModification ? $tmp_mtime : $lastNormalModification;
			}
		}
		return $lastNormalModification;
	}
	
	protected function _coverageFilesLastModificationTime($jsCoverageTests, $view) {
		$lastInstrumentedModification = filemtime($view);
		foreach ($jsCoverageTests as $testFile) {
			if (file_exists($testFile)) {
				$tmp_mtime = filemtime($testFile);
				$lastInstrumentedModification = $tmp_mtime > $lastInstrumentedModification ? $tmp_mtime : $lastInstrumentedModification;
			}
		}
		return $lastInstrumentedModification;
	}

	/**
	 * Checks profile data for any errors, should be called before invoking a test profile
	 * to make sure everything is configured properly.
	 *
	 * @param array $profileData
	 * @return bool True if the profile data is correct, false if any setting is missing.
	 */
	function checkProfile($profileData, $verbose = false)
	{
		$passed = true;

		$checks = array
			(
				'dir.normal_root'				=> 'Normal root dir not set - you will not be able to run instrumentation!',
				'dir.normal_tests'				=> 'Normal test dir not set - no tests will be detected!',
				'dir.instrumented_root'			=> 'Instrumented root dir not set - instrumentation may not be possible!',
				'dir.instrumented_tests'		=> 'Instrumented test dir not set - instrumentation may not be possible!',
				'url.normal_root'				=> 'Normal root URL not set - you will not be able to run instrumentation!',
				'url.normal_tests'				=> 'Normal test URL not set - no tests will be detected!',
				'url.instrumented_root'			=> 'Instrumented root URL not set - instrumentation may not be possible!',
				'url.instrumented_tests'		=> 'Instrumented test URL not set - instrumentation may not be possible!',
				'params.tests'					=> 'Main test param not set - tests will not be detected!',
				'params.name'					=> 'Test name detection regex not set - tests might not work properly!',
				'params.files'					=> 'Related test file patterns not set - additional test files will not be checked for instrumentation!',
				'instrumentation.noInstrument'	=> 'Instrumentation exceptions not set - this can be empty but you may see invalid code coverage!',
				'instrumentation.exclude'		=> 'Instrumentation excludes not set - if not used this can be left empty but it must exist!',
			);

		foreach ($checks as $key => $error)
		{
			if (!Set::check($profileData, $key))
			{
				if ($verbose == true)
				{
					throw new CakeException($error);
				}

				$passed = false;
			}
		}

		return $passed;
	}

	/**
	 * Run instrumentation for a test profile.
	 *
	 * @param array $profileData
	 */
	function instrument($profileData) {
		$adapter = $profileData['instrumentation']['library'].'Adapter';
		App::uses($adapter, 'JsTests.Lib');
		$coverage = new $adapter(Configure::read('JsTests.JSCoverage.executable'));
		return $coverage->execute(
			$profileData['instrumentation']['noInstrument'],
			$profileData['instrumentation']['exclude'],
			$profileData['dir']['normal_root'],
			$profileData['dir']['instrumented_root']
		);		
	}
}
