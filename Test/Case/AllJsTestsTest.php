<?php
class AllJsTestsTest extends CakeTestSuite {
	
	public static function suite() {
		$suite = new CakeTestSuite('All JsTests plugin tests');
		$suite->addTestDirectoryRecursive(App::pluginPath('JsTests').DS.'Test'.DS.'Case');
		return $suite;
	}
}