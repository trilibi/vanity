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


namespace Vanity\Command;

use Exception;
use stdClass;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Formatter\OutputFormatterStyle;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\EventDispatcher\Event;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Yaml\Yaml as YAML;
use Vanity\Config\Store as ConfigStore;
use Vanity\Console\Utilities as ConsoleUtil;
use Vanity\Event\Event\Store as EventStore;
use Vanity\GlobalObject\Dispatcher;
use Vanity\GlobalObject\Logger;
use Vanity\System\Store as SystemStore;

/**
 * Base class for all Vanity-specific command-line actions.
 *
 * @author Ryan Parman <http://ryanparman.com>
 * @link   http://vanitydoc.org
 */
class Base extends Command
{
	/**
	 * Storage for text formatters.
	 * @type stdClass
	 */
	public $formatter;

	/**
	 * Instantiate options for all Vanity-specific commands.
	 */
	public function __construct()
	{
		parent::__construct();
		$this->formatter = ConsoleUtil::formatters();
	}

	/**
	 * Triggers an event and logs it to the INFO log.
	 *
	 * @param  string $event       The string identifier for the event.
	 * @param  Event  $eventObject An object that extends the {@see Symfony\Component\EventDispatcher\Event} object.
	 * @return void
	 */
	public function triggerEvent($event, Event $eventObject = null)
	{
		Logger::get()->{ConfigStore::get('log.events')}('Triggering event:', array($event));
		Dispatcher::get()->dispatch($event, $eventObject);
	}

	/**
	 * Display the configuration to the Console.
	 *
	 * @param  OutputInterface $output The command-line output.
	 * @return void
	 */
	public function displayConfig(OutputInterface $output)
	{
		// Title and formatting
		$output->writeln($this->formatter->yellow->apply('ACTIVE CONFIGURATION OPTIONS:'));
		$padding = ConsoleUtil::tablify(ConfigStore::get());
		$self = $this;

		// Write the tablified listing to the buffer
		$output->writeln(
			ConsoleUtil::indent(
				YAML::dump(ConfigStore::get(), 1),
				$this->formatter->green->apply('-> '),
				function ($line) use ($self, $padding)
				{
					$pieces = explode(': ', $line);
					$pieces[0] = str_pad($pieces[0], $padding, ' ', STR_PAD_RIGHT);
					$pieces[1] = $self->formatter->gold->apply($pieces[1]);

					return implode(' : ', $pieces);
				}
			)
		);

		// Write any stored messages to the buffer
		if (count(ConfigStore::$messages) > 0)
		{
			foreach (ConfigStore::$messages as $message)
			{
				$output->writeln($message);
			}
		}

		echo PHP_EOL;
	}

	/**
	 * Trigger an event containing the log path.
	 *
	 * @return void
	 */
	public function triggerLogMessageEvent()
	{
		$this->triggerEvent('vanity.command.log_path', new EventStore(array(
			'log_path' => SystemStore::get('_.log_dir'),
			'time' => SystemStore::get('_.run_time'),
		)));
	}
}
