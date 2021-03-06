<?php
App::uses('AppHelper', 'View/Helper');
App::uses('HtmlHelper', 'View/Helper');

class JsTestHelper extends AppHelper {
	
	const COVERAGE_HTML = 'jscoverage.html';
	
	const COVERAGE_LINK_FORMAT = '/%s%s?u=%s';
	
	public $helpers = array('Html');
	
	public function testLink($title, $url = null, $options = array()) {
		if(is_string($this->settings['url']['normal_tests'])) {
			$url = '/'.$this->settings['url']['normal_tests'].$url;
		}
		if(is_array($this->settings['url']['normal_tests'])) {
			$url = array(
				'plugin' => Inflector::underscore($this->settings['url']['normal_tests']['plugin']),
				'controller' => Inflector::underscore($this->settings['url']['normal_tests']['controller']),
				'action' => $url
			);
		}
		return $this->Html->link($title, $url, $options);
	}
	
	public function coverageLink($title, $url = null, $options = array()) {
		$instrumentedTestFileURL;
		if(is_string($this->settings['url']['normal_tests'])) {
			$instrumentedTestFileURL = $this->Html->url('/'.$this->settings['url']['instrumented_tests'].$url);
		}
		if(is_array($this->settings['url']['normal_tests'])) {
			$instrumentedTestFileURL = $this->Html->url(array(
				'plugin' => Inflector::underscore($this->settings['url']['normal_tests']['plugin']),
				'controller' => Inflector::underscore($this->settings['url']['normal_tests']['controller']),
				'action' => $url,
				'coverage'
			));
		}
		$instrumentedTestURL = sprintf(
			self::COVERAGE_LINK_FORMAT,
			$this->settings['url']['instrumented_root'],
			self::COVERAGE_HTML,
			$instrumentedTestFileURL
		);
		return $this->Html->link($title, $instrumentedTestURL, $options);
	}
}