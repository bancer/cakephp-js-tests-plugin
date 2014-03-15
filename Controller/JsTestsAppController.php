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

App::uses('Hash', 'Utility');

class JsTestsAppController extends AppController
{
	var $activeProfileName = null;
	var $activeProfileData = null;

	function beforeFilter()
	{
		parent::beforeFilter();

		if (isset($this->passedArgs['profile']))
		{
			$exists = Hash::check(Configure::read('JsTests.Profiles'), sprintf('%s', $this->passedArgs['profile']));

			if (!$exists)
			{
				trigger_error(sprintf('Profile "%s" does not exist!', $this->passedArgs['profile']));
			}
			else
			{
				Configure::write('JsTests.ActiveProfile', $this->passedArgs['profile']);
			}
		}

		$this->activeProfileName = Configure::read('JsTests.ActiveProfile');
		$this->activeProfileData = Configure::read(sprintf('JsTests.Profiles.%s', $this->activeProfileName));

		if (empty($this->activeProfileData))
		{
			trigger_error('Error: JsTests profile not properly configured!');
			return;
		}
	}
	
	public function beforeRender() {
		$jsRoot = '';
		if(isset($this->request->params['pass'][0]) && $this->request->params['pass'][0] == 'coverage') {
			$jsRoot = '/'.$this->activeProfileData['url']['instrumented_root'];
		}
		$this->set('jsRoot', $jsRoot);
	}
}
