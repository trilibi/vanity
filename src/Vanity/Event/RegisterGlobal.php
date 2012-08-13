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

namespace Vanity\Event;

use ReflectionExtension;
use Symfony\Component\EventDispatcher\Event;
use Symfony\Component\Filesystem\Filesystem;
use Vanity\Config\Store as ConfigStore;
use Vanity\Console\Utilities as ConsoleUtil;
use Vanity\Event\Dispatcher;
use Vanity\System\Timer;
use Vanity\System\DependencyCollector;
use Vanity\System\ExtensionDependencyResolver;

/**
 * Stores all event handlers that are intended to be run on a global level.
 */
class RegisterGlobal
{
	/**
	 * [events description]
	 * @return [type] [description]
	 */
	public static function events()
	{
		// command.complete event
		Dispatcher::get()->addListener('command.complete', function(Event $event)
		{
			$formatter = ConsoleUtil::formatters();
			$stop_time = Timer::stop();

			echo PHP_EOL;
			echo $formatter->pending->apply(' Completed in ' . ConsoleUtil::time_hms(round($stop_time)) . ' (' . $stop_time . ') ') . PHP_EOL;
		});

		// api.report.dependencies event
		Dispatcher::get()->addListener('api.report.dependencies', function(Event $event)
		{
			// jsonify!
			$json = ConsoleUtil::json_encode(self::getDependencies());

			// Make sure the directory is created
			$filesystem = new Filesystem();
			$filesystem->mkdir(ConfigStore::get('vanity.reports'));

			file_put_contents(ConfigStore::get('vanity.reports') . '/dependencies.json', $json);
		});

		// api.warn.dependencies event
		Dispatcher::get()->addListener('api.warn.dependencies', function(Event $event)
		{
			$formatter = ConsoleUtil::formatters();
			$dependencies = self::getDependencies();

			echo PHP_EOL;
			echo $formatter->yellow->apply('REPORT: DEPENDENCIES') . PHP_EOL;

			foreach ($dependencies as $dependency)
			{
				echo TAB . $formatter->green->apply('-> ') . $dependency . PHP_EOL;
			}

			// Count the classes
			echo PHP_EOL;
			$count = count($dependencies);
			echo 'Found ' . $formatter->info->apply(" ${count} ") . ' ' . ConsoleUtil::pluralize($count, 'dependency', 'dependencies') . '.' . PHP_EOL;
		});
	}

	protected static function getDependencies()
	{
		// Collect all of the extension names that we received
		$dependencies = array_map(function($entry)
		{
			return strtolower($entry['message']);
		},
		DependencyCollector::read());

		// Remove all duplicates
		$dependencies = array_values(array_unique($dependencies));

		// Resolve the dependency chain
		$resolver = new ExtensionDependencyResolver($dependencies);

		return $resolver->resolve();
	}
}
