<?php
App::uses('HtmlHelper', 'View/Helper');

class JsTestHelper extends AppHelper {
	
	const COVERAGE_LINK_FORMAT = '/%sjscoverage.html?u=%s';
	
	public $helpers = array('Html');
	
	public function testLink($title, $url = null, $options = array()) {
		if(is_string($this->settings['url']['normal_tests'])) {
			$url = '/'.$this->settings['url']['normal_tests'].$url;
		}
		return $this->Html->link($title, $url, $options);
	}
	
	public function coverageLink($title, $url = null, $options = array()) {
		$instrumentedTestFileURL = $this->Html->url('/'.$this->settings['url']['instrumented_tests'].$url);
		$instrumentedTestURL = sprintf(
			self::COVERAGE_LINK_FORMAT,
			$this->settings['url']['instrumented_root'],
			$instrumentedTestFileURL
		);
		return $this->Html->link($title, $instrumentedTestURL, $options);
	}
}