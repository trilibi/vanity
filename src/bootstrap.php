<?php
/**
 * Copyright (c) 2009-2012 [Ryan Parman](http://ryanparman.com)
 * Copyright (c) 2011-2012 [Amazon Web Services, Inc.](http://aws.amazon.com)
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 *
 * <http://www.opensource.org/licenses/mit-license.php>
 */


/********************************************************/
// PREPARATION

if (php_sapi_name() !== 'cli')
{
	die('Must run from command line');
}

// Init
$start_time = time();
error_reporting(-1);
ini_set('display_errors', 1);
ini_set('log_errors', 0);
ini_set('html_errors', 0);


/********************************************************/
// CONSTANTS

define('TAB', '    ');
define('VANITY_VERSION',             '3.0alpha-' . gmdate('Ymd', filemtime(__FILE__)));
define('VANITY_SYSTEM',              dirname(__DIR__));
define('VANITY_VENDOR',              VANITY_SYSTEM . '/vendor');
define('VANITY_SOURCE',              VANITY_SYSTEM . '/src');
define('VANITY_USER_PROFILE',        $_SERVER['HOME'] ?: VANITY_SYSTEM );
define('VANITY_USER_DATA',           VANITY_USER_PROFILE . '/.vanity');
define('VANITY_CACHE_DIR',           VANITY_USER_DATA . '/cache');
define('VANITY_PHPREF_DIR',          VANITY_CACHE_DIR . '/php');
define('VANITY_ENTITY_GLOBAL_DIR',   VANITY_CACHE_DIR . '/entities');
define('VANITY_ENTITY_LANG_DIR',     VANITY_CACHE_DIR . '/language-entities');
define('VANITY_PROJECT_WORKING_DIR', getcwd());


/********************************************************/
// INCLUDES & NAMESPACES

require_once VANITY_SOURCE . '/Vanity/System/Store.php';

// Save this for later lookup
Vanity\System\Store::add('_.project_config_dir', VANITY_PROJECT_WORKING_DIR . '/.vanity');
Vanity\System\Store::add('_.classes', get_declared_classes());
Vanity\System\Store::add('_.interfaces', get_declared_interfaces());
if (Vanity\System\Store::get('_.php54'))
{
	Vanity\System\Store::add('_.traits', get_declared_traits());
}

// Load class loaders
require_once VANITY_VENDOR . '/symfony/class-loader/Symfony/Component/ClassLoader/UniversalClassLoader.php';
require_once VANITY_VENDOR . '/symfony/class-loader/Symfony/Component/ClassLoader/ApcUniversalClassLoader.php';

use Symfony\Component\ClassLoader\ApcUniversalClassLoader;
use Symfony\Component\ClassLoader\UniversalClassLoader;


/********************************************************/
// APP

// Use the best available class loader
if (extension_loaded('apc'))
{
	$loader = new ApcUniversalClassLoader('vanity.');
}
elseif (extension_loaded('xcache'))
{
	$loader = new XcacheUniversalClassLoader('vanity.');
}
else
{
	$loader = new UniversalClassLoader();
}

// Register namespaces with the class loader
$loader->registerNamespaces(array_merge(
	include_once VANITY_VENDOR . '/composer/autoload_namespaces.php',
	array(
		'Vanity' => __DIR__
	)
));

$loader->register();
