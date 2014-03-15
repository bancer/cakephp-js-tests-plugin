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
App::uses('JsTestHelper', 'Plugin/JsTests/View/Helper');

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
		$views = $this->_findTestViews($profileName);
		foreach ($views as $view) {
			$action 			= $this->_getAction($profileName, $view);
			$testName 			= $this->_getTestName($profileName, $action);
			$jsTests 			= $this->_findJsTestFiles($profileName, $testName);
			$jsCoverageTests 	= $this->_findJsCoverageTests($profileName, $jsTests);
			$instrumentedExists = $this->_coverageExists($profileName, $testName);
			$coverageView 		= $this->_findCoverageView($profileName, $action);
			$instrumentedIsUpdated = $this->_coverageFilesAreUpToDate($profileName, $instrumentedExists,
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
	
	/**
	 * Finds full paths to test view files.
	 * @param string $profileName
	 * @return array numerically indexed array of test view files
	 */
	protected function _findTestViews($profileName) {
		$launcher = $this->_profiles[$profileName]['url']['normal_tests'];
		$views = array();
		if(is_string($launcher)) {
			$views = glob(
				$this->_profiles[$profileName]['dir']['normal_tests'].
				$this->_profiles[$profileName]['params']['tests']
			);
		}
		if(is_array($launcher)) {
			if($launcher['plugin']) {
				App::import('Controller', $launcher['plugin'].'.'.$launcher['controller']);
			} else {
				App::import('Controller', $launcher['controller']);
			}
			$methods = get_class_methods($launcher['controller'].'Controller');
			$views = array_filter($methods, function($method) {
				return substr($method, 0, 4) == 'test'; // method starts with 'test'
			});
			foreach ($views as &$view) {
				if($launcher['plugin']) {
					$view = APP.'Plugin'.DS.$launcher['plugin'].DS.'View'.DS.$launcher['controller'].DS.$view.'.ctp';
				} else {
					$view = APP.'View'.DS.$launcher['controller'].DS.$view.'.ctp';
				}
			}
		}
		return $views;
	}
	
	/**
	 * Extracts action from view's path.
	 * @param string $profileName
	 * @param string $view
	 * @return string
	 */
	protected function _getAction($profileName, $view) {
		$launcher = $this->_profiles[$profileName]['url']['normal_tests'];
		if(is_string($launcher)) {
			return basename($view);
		}
		if(is_array($launcher)) {
			return basename($view, '.ctp');
		}
	}

	/**
	 * Extracts test name from action name.
	 * @param string $profileName
	 * @param string $action
	 * @return string
	 */
	protected function _getTestName($profileName, $action) {
		$launcher = $this->_profiles[$profileName]['url']['normal_tests'];
		if(is_string($launcher)) {
			$matches = array();
			$testName = preg_match('#'.$this->_profiles[$profileName]['params']['name'].'#', $action, $matches);
			$testName = isset($matches['name']) ? $matches['name'] : $matches[0];
			return $testName;
		}
		if(is_array($launcher)) {
			return str_replace('test_', '', $action);
		}
	}
	
	/**
	 * Locates all javascript test files related to the specified test.
	 * @param string $profileName
	 * @param string $testName
	 * @return array
	 */
	protected function _findJsTestFiles($profileName, $testName) {
		$testFiles = array();
		foreach ($this->_profiles[$profileName]['params']['files'] as $pattern) {
			$relatedPattern = $this->_profiles[$profileName]['dir']['normal_tests'].str_replace('%name%', $testName, $pattern);
			$testFiles = array_merge($testFiles, glob($relatedPattern));
		}
		return $testFiles;
	}
	
	/**
	 * Locates all javascript coverage test files related to the specified test.
	 * @param string $profileName
	 * @param array $jsTests array with javascript test files
	 * @return array
	 */
	protected function _findJsCoverageTests($profileName, $jsTests) {
		return str_replace(
			$this->_profiles[$profileName]['dir']['normal_tests'],
			$this->_profiles[$profileName]['dir']['instrumented_tests'],
			$jsTests
		);
	}
	
	/**
	 * Verifies whether the test javascript coverage files exist for the specified test.
	 * @param string $profileName
	 * @param string $testName
	 * @return boolean true if test coverage files exist, else false.
	 */
	protected function _coverageExists($profileName, $testName) {
		foreach ($this->_profiles[$profileName]['params']['files'] as $pattern) {
			$jsTest = $this->_profiles[$profileName]['dir']['normal_tests'].str_replace('%name%', $testName, $pattern);
			$jsTestCoverage = $this->_profiles[$profileName]['dir']['instrumented_tests'].str_replace('%name%', $testName, $pattern);
			if(file_exists($jsTest) && !file_exists($jsTestCoverage)) {
				return false;
			}
		}
		return file_exists($this->_profiles[$profileName]['dir']['instrumented_root'].JsTestHelper::COVERAGE_HTML);
	}
	
	/**
	 * Extract coverage view test file full path.
	 * @param string $profileName
	 * @param string $action
	 * @return string
	 */
	protected function _findCoverageView($profileName, $action) {
		$launcher = $this->_profiles[$profileName]['url']['normal_tests'];
		if(is_string($launcher)) {
			return $this->_profiles[$profileName]['dir']['instrumented_tests'].$action;
		}
		if(is_array($launcher)) {
			if($launcher['plugin']) {
				return APP.'Plugin'.DS.$launcher['plugin'].DS.'View'.DS.$launcher['controller'].DS.$action.'.ctp';
			} else {
				return APP.'View'.DS.$launcher['controller'].DS.$action.'.ctp';
			}	
		}
	}
	
	/**
	 * Verifies whether test coverage files are uptodate.
	 * @param boolean $instrumentedExists
	 * @param array $jsTests
	 * @param array $jsCoverageTests
	 * @param string $view
	 * @param string $coverageView
	 * @return boolean
	 */
	protected function _coverageFilesAreUpToDate($profileName, $instrumentedExists, $jsTests, $jsCoverageTests, $view, $coverageView) {
		$instrumentedIsUpdated = false;
		// check if the instrumented version is up to date
		if ($instrumentedExists) {
			$lastNormalModification = $this->_testFilesLastModificationTime($jsTests, $view);
			$lastInstrumentedModification = $this->_coverageFilesLastModificationTime($profileName, 
				$jsCoverageTests, $coverageView);
			$instrumentedIsUpdated = $lastInstrumentedModification > $lastNormalModification - 1;
		}
		return $instrumentedIsUpdated;
	}
	
	/**
	 * Retrieve last modification time of the test files (both view and javascript).
	 * @param array $jsTests
	 * @param string $view
	 * @return int Unix timestamp - the time file was modified
	 */
	protected function _testFilesLastModificationTime($jsTests, $view) {
		$lastNormalModification = filemtime($view);
		foreach ($jsTests as $testFile) {
			if (file_exists($testFile)) {
				$tmp_mtime = filemtime($testFile);
				$lastNormalModification = $tmp_mtime > $lastNormalModification ? $tmp_mtime : $lastNormalModification;
			}
		}
		return $lastNormalModification;
	}
	
	/**
	 * Retrieve last modification time of the test coverage files (both view and javascript).
	 * @param unknown_type $jsCoverageTests
	 * @param unknown_type $view
	 * @return int Unix timestamp - the time file was modified
	 */
	protected function _coverageFilesLastModificationTime($profileName, $jsCoverageTests, $view) {
		$lastInstrumentedModification;
		$launcher = $this->_profiles[$profileName]['url']['normal_tests'];
		if(is_string($launcher)) {
			$lastInstrumentedModification = filemtime($view);
		}
		if(is_array($launcher)) {
			$lastInstrumentedModification = 0;
		}
		foreach ($jsCoverageTests as $testFile) {
			if (file_exists($testFile)) {
				$tmp_mtime = filemtime($testFile);
				$lastInstrumentedModification = $tmp_mtime > $lastInstrumentedModification ? $tmp_mtime : $lastInstrumentedModification;
			}
		}
		if($lastInstrumentedModification == 0) { // if no js tests exist for the test view
			$lastInstrumentedModification = filemtime($this->_profiles[$profileName]['dir']['instrumented_root'].JsTestHelper::COVERAGE_HTML);
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
		$lib = $profileData['instrumentation']['library'];
		$adapter = $lib.'Adapter';
		App::uses($adapter, 'JsTests.Lib');
		$coverage = new $adapter(Configure::read('JsTests.'.$lib.'.executable'));
		return $coverage->execute(
			$profileData['instrumentation']['noInstrument'],
			$profileData['instrumentation']['exclude'],
			$profileData['dir']['normal_root'],
			$profileData['dir']['instrumented_root']
		);		
	}
}
