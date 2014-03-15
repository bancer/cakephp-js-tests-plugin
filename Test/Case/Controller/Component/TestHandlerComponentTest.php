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

App::uses('Controller', 'Controller');
App::uses('Component', 'Controller');
App::uses('CakeRequest', 'Network');
App::uses('CakeResponse', 'Network');
App::uses('Hash', 'Utility');

App::uses('TestHandlerComponent', 'JsTests.Controller/Component');

class TestHandlerComponentTestController extends Controller
{
	public $uses = null;
}


class TestHandlerComponentTest extends CakeTestCase
{

	/**
	 *
	 * @var Controller
	 */
	public $Controller = null;

	/**
	 *
	 * @var TestHandlerComponent
	 */
	public $Component = null;

	public static function setupBeforeClass() {
		require_once(App::pluginPath('JsTests').'Test'.DS.'config.php');
	}

	public function setUp()
	{
		parent::setUp();

		$request = new CakeRequest('/');
		$response = new CakeResponse();
		$this->Controller = new TestHandlerComponentTestController($request, $response);
		$this->Controller->constructClasses();
		$this->Component = new TestHandlerComponent($this->Controller->Components);
		$this->Component->_profiles = Configure::read('JsTests.Profiles');
	}


	public function tearDown()
	{
		unset($this->Component);
		unset($this->Controller);

		parent::tearDown();
	}

	function testLoadTests()
	{
		if (DIRECTORY_SEPARATOR != '\\' && function_exists('posix_getpwuid'))
		{
			$currentUser = exec('whoami');
			$fileowner = posix_getpwuid(fileowner(JS_TESTDATA.'js'.DS.'tests'.DS.'some-library.test.html'));
			$fileowner = $fileowner['name'];

			$this->skipIf($currentUser != $fileowner, 'Test data files are not owned by Apache user ('.$currentUser.')');
		}

		touch(JS_TESTDATA.'js'.DS.'tests'.DS.'some-library.test.html', strtotime('-1 minute'));
		touch(JS_TESTDATA.'js'.DS.'tests'.DS.'some-library.test.js', strtotime('-1 minute'));
		touch(JS_TESTDATA.'js'.DS.'tests'.DS.'some-library.lib.js', strtotime('-1 minute'));
		touch(JS_TESTDATA.'js_instrumented'.DS.'tests'.DS.'some-library.test.html', strtotime('+1 minute'));
		touch(JS_TESTDATA.'js_instrumented'.DS.'tests'.DS.'some-library.test.js', strtotime('+1 minute'));
		touch(JS_TESTDATA.'js_instrumented'.DS.'tests'.DS.'some-library.lib.js', strtotime('+1 minute'));
		@unlink(JS_TESTDATA.'js_instrumented'.DS.'tests'.DS.'some-other-library.test.html');

		$expected = array
			(
				'some-library' => array
				(
					'mainTestFile' => 'some-library.test.html',
					'normalTestPath' => JS_TESTDATA.'js'.DS.'tests'.DS.'some-library.test.html',
					'instrumentedTestPath' => JS_TESTDATA.'js_instrumented'.DS.'tests'.DS.'some-library.test.html',
					'normalRelatedTestFiles' => array
					(
						JS_TESTDATA.'js'.DS.'tests'.DS.'some-library.test.js',
						JS_TESTDATA.'js'.DS.'tests'.DS.'some-library.lib.js'
					),
					'instrumentedRelatedTestFiles' => array
					(
						JS_TESTDATA.'js_instrumented'.DS.'tests'.DS.'some-library.test.js',
						JS_TESTDATA.'js_instrumented'.DS.'tests'.DS.'some-library.lib.js'
					),
					'instrumentedExists' => true,
					'instrumentedIsUpdated' => true
				),
				'some-other-library' => array
				(
					'mainTestFile' => 'some-other-library.test.html',
					'normalTestPath' => JS_TESTDATA.'js'.DS.'tests'.DS.'some-other-library.test.html',
					'instrumentedTestPath' => JS_TESTDATA.'js_instrumented'.DS.'tests'.DS.'some-other-library.test.html',
					'normalRelatedTestFiles' => array(),
					'instrumentedRelatedTestFiles' => array(),
					'instrumentedExists' => false,
					'instrumentedIsUpdated' => false
				)
			);
		$result = $this->Component->loadTests('default');
		$this->assertProfilesEqual($result, $expected);
	}
	
	private function assertProfilesEqual($result, $expected) {
		foreach ($result as $title => $profile) {
			foreach($profile as $name => $preference) {
				$this->assertEqual(
					$preference,
					$expected[$title][$name],
					sprintf('$result[\'%s\'][\'%s\'] is not equal to $expected[\'%s\'][\'%s\']', $title, $name, $title, $name)
				);
				if(is_array($preference)) {
					foreach ($preference as $k => $file) {
						$this->assertEqual($file, $expected[$title][$name][$k]);
					}
				}
			}
		}
	}

	function testCheckProfile()
	{
		$result = $this->Component->checkProfile(Configure::read('JsTests.Profiles.default'));
		$this->assertTrue($result);

		$result = $this->Component->checkProfile(Configure::read('JsTests.Profiles.ze-empty'));
		$this->assertFalse($result);

		try
		{
			$result = $this->Component->checkProfile(Configure::read('JsTests.Profiles.invalid'), true);
			$this->assertTrue(false);
		}
		catch (Exception $ex)
		{
			$this->assertTrue(true);
		}
	}

	function testInstrument()
	{
		Configure::write('JsTests.JSCoverage', array('executable' => '/usr/bin/notajscoverage'));

		try
		{
			$result = $this->Component->instrument(Configure::read('JsTests.Profiles.default'));
			$this->assertTrue(false);
		}
		catch (Exception $ex)
		{
			$this->assertTrue(true);
		}

		$testJSCoveragePath = '/usr/bin/jscoverage';
		//$testJSCoveragePath = 'c:\\usr\\bin\\jscoverage-0.5.1\\jscoverage.exe';

		$this->skipIf(!file_exists($testJSCoveragePath));
		Configure::write('JsTests.JSCoverage', array('executable' => $testJSCoveragePath));
		$profile = Configure::read('JsTests.Profiles.default');
		$this->skipIf(!is_writable($profile['dir']['instrumented_root']));

		$result = $this->Component->instrument($profile);
		$expected = array('output' => array(), 'exitCode' => null);

		$this->assertEqual($result, $expected);
	}
	
	public function testLoadTestsCakeWay() {
		$dir = App::pluginPath('JsTests').'View'.DS.'QunitTests'.DS;
		$expected = array(
			'all' => array(
				'mainTestFile' 					=> 'test_all',
				'normalTestPath' 				=> $dir.'test_all.ctp',
				'instrumentedTestPath' 			=> $dir.'test_all.ctp',
				'normalRelatedTestFiles' 		=> array(),
				'instrumentedRelatedTestFiles' 	=> array(),
				'instrumentedExists' 			=> true,
				'instrumentedIsUpdated' 		=> true
			),
			'example1' => array(
				'mainTestFile' 					=> 'test_example1',
				'normalTestPath' 				=> $dir.'test_example1.ctp',
				'instrumentedTestPath' 			=> $dir.'test_example1.ctp',
				'normalRelatedTestFiles' 		=> array(
					JS_TESTDATA.'js'.DS.'tests'.DS.'example1.test.js'
				),
				'instrumentedRelatedTestFiles' 	=> array(
					JS_TESTDATA.'js_instrumented'.DS.'tests'.DS.'example1.test.js',
				),
				'instrumentedExists' 			=> true,
				'instrumentedIsUpdated' 		=> false
			),
			'example2' => array(
				'mainTestFile' 					=> 'test_example2',
				'normalTestPath' 				=> $dir.'test_example2.ctp',
				'instrumentedTestPath' 			=> $dir.'test_example2.ctp',
				'normalRelatedTestFiles' 		=> array(
					JS_TESTDATA.'js'.DS.'tests'.DS.'example2.test.js'
				),
				'instrumentedRelatedTestFiles' 	=> array(
					JS_TESTDATA.'js_instrumented'.DS.'tests'.DS.'example2.test.js',
				),
				'instrumentedExists' 			=> false,
				'instrumentedIsUpdated' 		=> false
			)
		);
		touch($expected['example1']['normalRelatedTestFiles'][0], strtotime('+1 minute'));
		$result = $this->Component->loadTests('cake_way');
		$this->assertProfilesEqual($result, $expected);
	}
}
