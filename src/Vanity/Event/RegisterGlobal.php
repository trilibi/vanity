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


namespace Vanity\Event;

use ReflectionExtension;
use Symfony\Component\EventDispatcher\Event;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;
use Vanity\Config\Store as ConfigStore;
use Vanity\Console\Utilities as ConsoleUtil;
use Vanity\Event\Event\Store as EventStore;
use Vanity\GlobalObject\Dispatcher;
use Vanity\System\Timer;
use Vanity\System\DependencyCollector;
use Vanity\System\DocumentationInconsistencyCollector;
use Vanity\System\ExtensionDependencyResolver;
use Vanity\Template\DesktopHTML\Bootstrap as DesktopHTMLTemplate;

/**
 * Stores all event handlers that are intended to be run on a global level.
 *
 * @author Ryan Parman <http://ryanparman.com>
 * @link   http://vanitydoc.org
 */
class RegisterGlobal
{
	/**
	 * Executes all of the event handlers.
	 *
	 * @return void
	 */
	public static function events()
	{
		$self = get_called_class();

		// vanity.command.complete event
		Dispatcher::get()->addListener('vanity.command.complete', function(Event $event)
		{
			$formatter = ConsoleUtil::formatters();
			$stop_time = Timer::stop();

			echo PHP_EOL;
			echo $formatter->pending->apply(' Completed in ' . ConsoleUtil::timeHMS(round($stop_time)) . ' (' . $stop_time . ') | Peak memory usage: ' . ConsoleUtil::formatSize(memory_get_peak_usage()) . ' (' . number_format(memory_get_peak_usage()) . ' bytes) ') . PHP_EOL;
		});

		// vanity.command.log_path event
		Dispatcher::get()->addListener('vanity.command.log_path', function(EventStore $event)
		{
			$finder = new Finder();
			$formatter = ConsoleUtil::formatters();
			$log_path = $event->get('log_path');
			$time = $event->get('time');

			echo PHP_EOL;
			echo $formatter->yellow->apply('LOG FILES FOR THIS RUN') . PHP_EOL;

			$files = $finder
				->files()
				->name("vanity-run-${time}-*.log")
				->depth(0)
				->in($log_path);

			$count = 0;
			foreach ($files as $file)
			{
				$count++;
				echo TAB . $formatter->green->apply('-> ') . $file->getRealpath() . PHP_EOL;
			}

			// Count the classes
			echo PHP_EOL;
			echo 'Found ' . $formatter->info->apply(" ${count} ") . ' log ' . ConsoleUtil::pluralize($count, 'file', 'files') . '.' . PHP_EOL;
		});

		// vanity.command.parse.report.dependencies event
		Dispatcher::get()->addListener('vanity.command.parse.report.dependencies', function(Event $event)
		{
			// jsonify!
			$json = ConsoleUtil::json_encode(self::getDependencies());

			// Make sure the directory is created
			$filesystem = new Filesystem();
			$filesystem->mkdir(ConfigStore::get('vanity.reports'));

			file_put_contents(ConfigStore::get('vanity.reports') . '/dependencies.json', $json);
		});

		// vanity.command.parse.warn.dependencies event
		Dispatcher::get()->addListener('vanity.command.parse.warn.dependencies', function(Event $event) use (&$self)
		{
			$formatter = ConsoleUtil::formatters();
			$dependencies = $self::getDependencies();

			echo PHP_EOL;
			echo $formatter->yellow->apply('REPORT: DEPENDENCIES ON EXTENSIONS') . PHP_EOL;

			foreach ($dependencies as $dependency)
			{
				echo TAB . $formatter->green->apply('-> ') . $dependency . PHP_EOL;
			}

			// Count the classes
			echo PHP_EOL;
			$count = count($dependencies);
			echo 'Found ' . $formatter->info->apply(" ${count} ") . ' ' . ConsoleUtil::pluralize($count, 'dependency', 'dependencies') . '.' . PHP_EOL;
		});

		// vanity.command.parse.warn.inconsistencies event
		Dispatcher::get()->addListener('vanity.command.parse.warn.inconsistencies', function(Event $event)
		{
			$formatter = ConsoleUtil::formatters();
			$inconsistencies = DocumentationInconsistencyCollector::read();

			echo PHP_EOL;
			echo $formatter->yellow->apply('REPORT: DOCBLOCK INCONSISTENCIES') . PHP_EOL;

			// We really need \Array->apply(), don't we?
			echo 'Tags where type is inferred: ' .
				implode(', ',
					array_map(function($w) use ($formatter) {
						return $formatter->green->apply($w);
					},
					explode(', ', '@param, @return, @returns, @see, @throw, @throws, @uses, @used-by, @type, @var')
				)
			) . '.' . PHP_EOL;

			foreach ($inconsistencies as $inconsistency)
			{
				echo TAB . $formatter->green->apply('-> ') . $inconsistency['message'] . PHP_EOL;
			}

			// Count the classes
			echo PHP_EOL;
			$count = count($inconsistencies);
			echo 'Found ' . $formatter->info->apply(" ${count} ") . ' ' . ConsoleUtil::pluralize($count, 'inconsistency', 'inconsistencies') . '.' . PHP_EOL;
		});

		// Handle default HTML template
		DesktopHTMLTemplate::register('default-html');
	}

	/**
	 * Resolves the list of extension dependencies from the messages that were
	 * stored using {@see ExtensionDependencyResolver}.
	 *
	 * @return array A list of extension dependencies.
	 */
	public static function getDependencies()
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
