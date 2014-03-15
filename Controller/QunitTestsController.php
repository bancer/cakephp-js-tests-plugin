<?php
App::uses('AppController', 'Controller');
App::uses('JsTestsAppController', 'Plugin/JsTests/Controller');

class QunitTestsController extends JsTestsAppController {
	
	public $layout = 'qunit';
	
	public function test_all($coverage = false) {}
	
	public function test_example1($coverage = false) {}
	
	public function test_example2($coverage = false) {}
}