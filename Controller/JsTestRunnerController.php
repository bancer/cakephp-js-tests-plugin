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

/**
 * @property TestHandlerComponent $TestHandler
 */
class JsTestRunnerController extends JsTestsAppController
{
	public $name = 'JsTestRunner';
	public $uses = array();
	public $components = array('Session', 'RequestHandler', 'JsTests.TestHandler');
	public $helpers = array('JsTests.JsTest');

	public function run()
	{
		$passed = $this->TestHandler->checkProfile($this->activeProfileData);

		if (!$passed)
		{
			$tests = array();
		}
		else
		{
			$tests = $this->TestHandler->loadTests($this->activeProfileName);
		}

		$allProfiles = Configure::read('JsTests.Profiles');
		$availableProfiles = array();

		foreach ($allProfiles as $profileName => $profileData)
		{
			$availableProfiles[$profileName] = $this->TestHandler->checkProfile($profileData);
		}

		$this->set('activeProfileName', $this->activeProfileName);
		$this->set('activeProfileData', $this->activeProfileData);
		$this->set(compact('tests', 'availableProfiles'));
	}

	public function instrument()
	{
		if (!$this->RequestHandler->isPost())
		{
			$this->redirect($this->referer());
		}

		$profile = $this->data['profile'];
		$profileData = Configure::read(sprintf('JsTests.Profiles.%s', $profile));

		if (!$this->TestHandler->checkProfile($profileData))
		{
			$this->Session->setFlash('Instrumentation failed: profile not configured correctly!');
			$this->redirect($this->referer());
		}

		$output = $this->TestHandler->instrument($profileData);

		if ($output['exitCode'] != 0)
		{
			$this->Session->setFlash(sprintf('Instrumentation failed: JSCoverage returned a status of %s', $output['exitCode']));
			$this->Session->write('JSCoverage.output', serialize($output['output']));
			$this->redirect($this->referer());
		}
		else
		{
			$this->Session->setFlash('Instrumentation successfull');
			$this->redirect($this->referer());
		}
	}
	
	public function beforeRender() {
		parent::beforeRender();
		$this->helpers['JsTests.JsTest']['url'] = $this->activeProfileData['url'];
	}
	
	public function beforeFilter() {
		parent::beforeFilter();
		$this->TestHandler->_profiles = Configure::read('JsTests.Profiles');
	}
}
