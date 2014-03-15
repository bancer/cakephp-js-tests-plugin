<?php
App::uses('View', 'View');
App::uses('Helper', 'View');
App::uses('JsTestHelper', 'JsTests.View/Helper');

/**
 * JsTestHelper Test Case
 *
 */
class JsTestHelperTest extends CakeTestCase {

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

/**
 * testTestLink method
 *
 * @return void
 */
	public function testTestLink() {
		$this->markTestSkipped('testIndex not implemented.');
	}

/**
 * testCoverageLink method
 *
 * @return void
 */
	public function testCoverageLink() {
		$this->markTestIncomplete('testIndex not implemented.');
	}

}
