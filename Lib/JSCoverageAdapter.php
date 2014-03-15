<?php
App::uses('ACoverageAdapter', 'JsTests.Lib');

class JSCoverageAdapter extends ACoverageAdapter {
	
	protected function _getExecuteFormat() {
		return '%s -v %s %s "%s" "%s"';
	}
}