<?php
App::uses('ACoverageAdapter', 'JsTests.Lib');

class JSCoverAdapter extends ACoverageAdapter {
	
	protected function _getExecuteFormat() {
		return 'java -jar %s -fs %s %s "%s" "%s"';
	}
}