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


namespace Vanity\Command;

use Exception;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use Monolog\Processor\IntrospectionProcessor;
use stdClass;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Formatter\OutputFormatterStyle;
use Symfony\Component\Filesystem\Filesystem;
use Vanity\Event\Dispatcher;

class Base extends Command
{
	protected $formatter;
	protected $logger;

	/**
	 * [__construct description]
	 * @param [type] $name [description]
	 */
	public function __construct($name = null)
	{
		parent::__construct();

		$filesystem = new Filesystem();
		$this->formatter = new stdClass();

		// Text styles
		$this->formatter->green  = new OutputFormatterStyle('green', null, array('bold'));
		$this->formatter->yellow = new OutputFormatterStyle('yellow', null, array('bold'));
		$this->formatter->grey   = new OutputFormatterStyle('white');

		// Highlighted styles
		$this->formatter->info    = new OutputFormatterStyle('white',  'blue',  array('bold'));
		$this->formatter->success = new OutputFormatterStyle('white',  'green', array('bold'));
		$this->formatter->warning = new OutputFormatterStyle('white',  'red',   array('bold'));
		$this->formatter->pending = new OutputFormatterStyle('black',  'white');

		// Create logging directory
		if (!is_dir(VANITY_LOGS))
		{
			if (!$filesystem->mkdir(VANITY_LOGS, 0777))
			{
				throw new Exception('Vanity was unable to create the logging directory at ' . VANITY_LOGS);
			}
		}

		// Construct logging handlers
		$streamInfo  = new StreamHandler(VANITY_LOGS . '/info.log',  Logger::INFO);
		$streamDebug = new StreamHandler(VANITY_LOGS . '/debug.log', Logger::DEBUG);
		$streamDebug->pushProcessor(new IntrospectionProcessor);

		// Instantiate the logger
		$this->logger = new Logger('Vanity');
		$this->logger->pushHandler($streamInfo);
		$this->logger->pushHandler($streamDebug);
	}

	/**
	 * [appInfo description]
	 * @param  [type] $output [description]
	 * @return [type]         [description]
	 */
	public function appInfo($output)
	{
		// List the application information
		$output->writeln($this->formatter->yellow->apply('VANITY ' . VANITY_VERSION));
		$output->writeln(TAB . 'by Ryan Parman and Contributors');
		$output->writeln(TAB . 'http://vanitydoc.org');
		echo PHP_EOL;
	}

	/**
	 * [triggerEvent description]
	 * @param  [type] $event [description]
	 * @return [type]        [description]
	 */
	public function triggerEvent($event)
	{
		$this->logger->info('Triggering event:', array($event));
		Dispatcher::get()->dispatch($event);
	}
}
