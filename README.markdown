# CakePHP JsTests Plugin #

## About ##

JsTests is a [CakePHP][] plugin which tries to make it easy for you to run unit tests on your
JavaScript code. As the modern applications become more and more client side oriented, the need
for JS unit tests is increasing. This plugin uses [QUnit][] with [JSCoverage][] or [JSCover][] 
to test JavaScript files and provide code coverage.

## Usage ##

**Important**: Minimum requirements for this plugin: `CakePHP 2.2+`.

First, obtain the plugin. If you're using Git, run this while in your app folder:

	git submodule add git://github.com/lecterror/cakephp-js-tests-plugin.git Plugin/JsTests
	git submodule init
	git submodule update

Or visit <http://github.com/lecterror/cakephp-js-tests-plugin/> and download the
plugin manually to your `app/Plugin/JsTests/` folder.

The best way to start using the plugin is to copy the examples from the plugin "examples"
folder to your `app/webroot/js/`. Additionally, create a `app/webroot/js_instrumented/` folder
and make it world-writable (also make sure it's completely empty!).

Next, copy the `JsTests/Config/core.php.default` to `JsTests/Config/core.php`. 

If you are going to use JSCoverage do the steps from **JSCoverage** section below.
If you are going to use JSCover or you do not know what library to use do the steps 
from **JSCover** section below. 

Now activate the plugin in your cake app, including the plugin core configuration file:

	CakePlugin::loadAll
		(
			array
			(
				'JsTests' => array('bootstrap' => array('core'))
			)
		);

You should now be ready to open the tests in your browser:

	[your app root]/js_tests/js_test_runner/run

If not, you've probably messed something up. The examples use [QUnit][] as test framework, and this
plugin has not been tested with anything else. So, good luck if you do try something else.

When you've figured out all the basic stuff, try creating your own test profiles in the
`JsTests/Config/core.php`. If you run into trouble, you can always revert to the default
profile, or submit a ticket if you think you've run into a bug.

### JSCoverage ###
**Note**: only for those who use JSCoverage library.

Next, make sure you have [JSCoverage][] somewhere on your system. On Ubuntu this is as simple as:

	sudo apt-get install jscoverage

If you're on Windows, download the Windows binaries and place them somewhere warm and comfy.

Now, open the file `JsTests/Config/core.php` and find the line which says:

	'executable'	=> '/usr/bin/jscoverage'

and change the path to JSCoverage executable on your system.

### JSCover ###
**Note**: only for those who use JSCover library.
**Important**: make sure you have Java runtime environment installed on your server.

By default and for illustration purposes tests are placed into the plugin folder.
If you are happy with that skip this section.
If you prefer to have tests in you app folder you need to:

1. move `JSTests/QunitTestsController.php` file to you `app/Controller` folder,
2. move `JSTests/View/QunitTests` folder with all files to `app/View` folder,
3. open the file `JsTests/Config/core.php` and find the line which says:

	'plugin' 	 => 'JsTests',

and change it to

	'plugin' 	 => false,

## Contributing ##

If you'd like to contribute, clone the source on GitHub, make your changes and send me a pull request.
If you don't know how to fix the issue or you're too lazy to do it, create a ticket and we'll see
what happens next.

**Important**: If you're sending a patch, follow the coding style! If you don't, there is a great
chance I won't accept it. For example:

	// bad
	function drink() {
		return false;
	}

	// good
	function drink()
	{
		return true;
	}

## Licence ##

Multi-licenced under:

* MPL <http://www.mozilla.org/MPL/MPL-1.1.html>
* LGPL <http://www.gnu.org/licenses/lgpl.html>
* GPL <http://www.gnu.org/licenses/gpl.html>


[CakePHP]: http://cakephp.org/
[JSCoverage]: http://siliconforks.com/jscoverage/
[JSCover]: http://tntim96.github.io/JSCover/
[Vim]: http://www.vim.org/ "The Editor"
[QUnit]: http://docs.jquery.com/Qunit
