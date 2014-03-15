<?php
define('JS_TEST_PLUGIN_ROOT', App::pluginPath('JsTests'));
define('JS_TESTDATA', JS_TEST_PLUGIN_ROOT.'Test'.DS.'data'.DS);

if (!defined('CAKEPHP_UNIT_TEST_EXECUTION'))
{
	define('CAKEPHP_UNIT_TEST_EXECUTION', 1);
}

Configure::write('JsTests.Profiles',
	array
	(
		'default' => array
		(
			'dir' => array
			(
				'normal_root'			=> JS_TESTDATA.'js'.DS,
				'normal_tests'			=> JS_TESTDATA.'js'.DS.'tests'.DS,
				'instrumented_root'		=> JS_TESTDATA.'js_instrumented'.DS,
				'instrumented_tests'	=> JS_TESTDATA.'js_instrumented'.DS.'tests'.DS
			),
			'url' => array
			(
				'normal_root'			=> 'js/',
				'normal_tests'			=> 'js/tests/',
				'instrumented_root'		=> 'js_instrumented/',
				'instrumented_tests'	=> 'js_instrumented/tests/',
			),
			'params' => array
			(
				'tests'		=> '*.test.html',
				'name'		=> '^(?P<name>[a-zA-Z_\-0-9]+).test.html$',
				'files'		=> array('%name%.test.js', '%name%.lib.js'),
			),
			'instrumentation' => array
			(
				'noInstrument'		=> array('tests'/*, 'jquery'*/),
				'exclude'			=> array('.svn'),
				'library' 			=> 'JSCoverage'
			)
		),
		'ze-empty' => array(),
		'invalid' => array('dir' => array('normal-root' => '')),
			
		'cake_way' => array(
			'dir' => array(
				'normal_root'			=> JS_TESTDATA.'js'.DS,
				'normal_tests'			=> JS_TESTDATA.'js'.DS.'tests'.DS,
				'instrumented_root'		=> JS_TESTDATA.'js_instrumented'.DS,
				'instrumented_tests'	=> JS_TESTDATA.'js_instrumented'.DS.'tests'.DS
			),
			'url' => array(
				'normal_root'			=> null,
				'normal_tests'			=> array(
					'plugin' 	 => 'JsTests',
					'controller' => 'QunitTests'
				),
				'instrumented_root'		=> 'js_instrumented/',
				'instrumented_tests'	=> 'js_instrumented/tests/',
			),
			'params' => array(
				'tests'		=> null,
				'name'		=> '^test_(?P<name>[a-zA-Z_\-0-9]+).ctp$',
				'files'		=> array('%name%.test.js', '%name%.lib.js'),
			),
			'instrumentation' => array(
				'noInstrument'	=> array('tests', 'qunit'),
				'exclude'		=> array('.svn'),
				'library' => 'JSCover'
			),
		)
	)
);