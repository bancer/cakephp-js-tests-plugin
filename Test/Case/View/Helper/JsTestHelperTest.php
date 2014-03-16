<?php
App::uses('View', 'View');
App::uses('Helper', 'View');
App::uses('JsTestHelper', 'JsTests.View/Helper');

/**
 * JsTestHelper Test Case
 *
 */
class JsTestHelperTest extends CakeTestCase {

	public static function setupBeforeClass() {
		require_once(App::pluginPath('JsTests').'Test'.DS.'config.php');
	}

/**
 * setUp method
 *
 * @return void
 */
	public function setUp() {
		parent::setUp();
		$View = new View();
		$this->JsTest = new JsTestHelper($View);
	}

/**
 * tearDown method
 *
 * @return void
 */
	public function tearDown() {
		unset($this->JsTest);

		parent::tearDown();
	}
	
	public function testTestLink() {
		$this->JsTest->settings['url'] = Configure::read('JsTests.Profiles.default.url');
		$result = $this->JsTest->testLink('Run', 'some-library.test.html');
		$expected = $this->JsTest->Html->link('Run', 'js/tests/some-library.test.html');
		$this->assertEqual($result, $expected);
	}

/**
 * testTestLink method
 *
 * @return void
 */
	public function testTestLinkCakeWay() {
		$this->JsTest->settings['url'] = Configure::read('JsTests.Profiles.cake_way.url');
		$result = $this->JsTest->testLink('Run', 'test_example1');
		$expected = $this->JsTest->Html->link('Run', 'js_tests/qunit_tests/test_example1');
		$this->assertEqual($result, $expected);
	}
	
	public function testCoverageLink() {
		$this->JsTest->settings['url'] = Configure::read('JsTests.Profiles.default.url');
		$result = $this->JsTest->coverageLink('Run', 'some-library.test.html');
		$param = $this->JsTest->Html->url('js_instrumented/tests/some-library.test.html');
		$expected = $this->JsTest->Html->link(
			'Run',
			'js_instrumented/jscoverage.html?u='.$param
		);
		$this->assertEqual($result, $expected);
	}

/**
 * testCoverageLink method
 *
 * @return void
 */
	public function testCoverageLinkCakeWay() {
		$this->JsTest->settings['url'] = Configure::read('JsTests.Profiles.cake_way.url');
		$result = $this->JsTest->coverageLink('Run', 'test_example1');
		$param = $this->JsTest->Html->url('js_tests/qunit_tests/test_example1/coverage');
		$expected = $this->JsTest->Html->link(
			'Run',
			'js_instrumented/jscoverage.html?u='.$param
		);
		$this->assertEqual($result, $expected);
	}

}
