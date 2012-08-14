<?php
/**
 * Copyright (c) 2009-2012 [Ryan Parman](http://ryanparman.com)
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

namespace Vanity\Find;

use Symfony\Component\ClassLoader\UniversalClassLoader;
use Symfony\Component\Finder\Finder;

/**
 * Finds the files to parse and the classes inside of them.
 *
 * @author Ryan Parman <http://ryanparman.com>
 * @link   http://vanitydoc.org
 */
class Find
{
	/**
	 * Retrieves the list of matching files.
	 *
	 * @param  string $path    The file system path to scan.
	 * @param  string $pattern The filename pattern to match.
	 * @return array           An array of matching filenames.
	 */
	public static function files($path, $pattern)
	{
		$parsable_files = array(
			'absolute' => array(),
			'relative' => array(),
		);

		// Symfony Finder instance
		$finder = Finder::create()
			->files()
			->name($pattern)
			->in($path);

		// Handle the list of files
		foreach ($finder as $file)
		{
			$parsable_files['absolute'][] = $file->getRealpath();
			$parsable_files['relative'][] = str_replace(VANITY_PROJECT_WORKING_DIR . '/', '', $file->getRealpath());
		}

		return $parsable_files;
	}

	/**
	 * An array of file paths retrieved from {@see files()}.
	 *
	 * @param  array  $files A list of file paths.
	 * @return array         A list of classes.
	 */
	public static function classes(array $files)
	{
		$loader = new UniversalClassLoader();

		// Support PSR-0 autoloading with a composer.json file
		// @todo: Add support for Composer's classmap autoloading.
		if (file_exists(VANITY_PROJECT_WORKING_DIR . '/vendor/composer/autoload_namespaces.php'))
		{
			// Register namespaces with the class loader
			$loader->registerNamespaces(include VANITY_PROJECT_WORKING_DIR . '/vendor/composer/autoload_namespaces.php');
		}
		elseif (file_exists(VANITY_PROJECT_WORKING_DIR . '/composer.json'))
		{
			// Register namespaces with the class loader
			$composer = json_decode(file_get_contents(VANITY_PROJECT_WORKING_DIR . '/composer.json'), true);

			if (isset($composer['autoload']) && isset($composer['autoload']['psr-0']))
			{
				$loader->registerNamespaces($composer['autoload']['psr-0']);
			}
		}

		$loader->register();

		$class_list = array();
		$before = get_declared_classes();

		// Let's continue to be able to document ourselves.
		if (defined('VANITY_AM_I'))
		{
			$before = array_filter($before, function($class)
			{
				return (substr($class, 0, 7) !== 'Vanity\\');
			});
		}

		foreach ($files as $file)
		{
			include_once $file;
		}

		$after = get_declared_classes();

		$class_list = array_values(array_unique(array_diff($after, $before)));
		sort($class_list);

		return $class_list;
	}
}
