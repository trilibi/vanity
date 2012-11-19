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
use phpDocumentor\Reflection\DocBlock;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Vanity\Command\Base as BaseCommand;
use Vanity\Config\Resolve as ConfigResolve;
use Vanity\Config\Store as ConfigStore;
use Vanity\Console\Utilities as ConsoleUtil;
use Vanity\Event\Event\Store as EventStore;
use Vanity\Find\Find;
use Vanity\GlobalObject\Logger;
use Vanity\Parse\User\ReflectAll;
use Vanity\Parse\Utilities as ParseUtils;

/**
 * Command that executes `generate`.
 *
 * @author Ryan Parman <http://ryanparman.com>
 * @link   http://vanitydoc.org
 */
class Generate extends BaseCommand
{
	/**
	 * The command-line arguments and options.
	 *
	 * @return void
	 */
	protected function configure()
	{
		$this
			->setName('generate')
			->setDescription('Generate the documentation output from JSON source definitions.')
		;

		$options = include __DIR__ . '/generate_configs.php';
		$options = ConfigStore::convert($options);

		foreach ($options as $option => $details)
		{
			list($type, $description, $default) = $details;

			if (!is_null($default))
			{
				if (is_bool($default))
				{
					$default = $default ? 'true' : 'false';
				}

				$description .= ConsoleUtil::formatters()->gold->apply(' (default: ' . (is_array($default) ?  ParseUtils::unwrapArray($default) : $default) . ')');
			}

			$this->addOption($option, null, $type, $description);
		}
	}

	/**
	 * Execute the logic for the command.
	 *
	 * @event  Event           vanity.command.generate.files.pre
	 * @event  Event           vanity.command.generate.files.post
	 * @event  Event           vanity.command.complete
	 * @param  InputInterface  $input  The command-line input.
	 * @param  OutputInterface $output The command-line output.
	 * @return void
	 */
	protected function execute(InputInterface $input, OutputInterface $output)
	{
		echo PHP_EOL;

		// Resolve the configuration and display it
		$config = new ConfigResolve($input, __DIR__ . '/generate_configs.php');
		$config->read();
		$this->displayConfig($output);

		Logger::get()->{ConfigStore::get('log.commands')}('Running command:', array($this->getName()));

		if ($input->getOption('vanity.view_config')) exit;

		// Load the bootstrap, if any
		if (file_exists($bootstrap = ConfigStore::get('vanity.bootstrap')))
		{
			include_once $bootstrap;
		}

		$output->writeln($this->formatter->yellow->apply('SOURCE DEFINITIONS TO DOCUMENT:'));

		// Parse the pattern to determine the files to match
		$path = pathinfo(ConfigStore::get('generator.input'), PATHINFO_DIRNAME);
		$pattern = pathinfo(ConfigStore::get('generator.input'), PATHINFO_BASENAME);
		$path = str_replace('%STAGE%', ConsoleUtil::asciify(ConfigStore::get('vanity.stage')), $path);
		$path = str_replace('%VERSION%', ConsoleUtil::asciify(ConfigStore::get('vanity.version')), $path);
		$files = Find::files($path, $pattern);

		$this->triggerEvent('vanity.command.generate.files.pre', new EventStore(array(
			'files' => &$files
		)));

		// Display the list of matches
		foreach ($files['relative'] as $file)
		{
			$output->writeln(TAB . $this->formatter->green->apply('-> ') . $file);
		}

		// Count the matches
		echo PHP_EOL;
		$count = count($files['relative']);
		$output->writeln('Matched ' . $this->formatter->info->apply(" ${count} ") . ' ' . ConsoleUtil::pluralize($count, 'file', 'files') . '.');
		echo PHP_EOL;

		// Trigger events
		$this->triggerEvent('vanity.command.generate.files.post', new EventStore(array(
			'files' => &$files
		)));

		#--------------------------------------------------------------------------#

		foreach (ConfigStore::get('generator.formats') as $format)
		{
			$this->triggerEvent("vanity.generate.format.${format}.pre", new EventStore(array(
				'files' => &$files,
				'input' => ConfigStore::get('generator.input'),
				'output' => ConfigStore::get('generator.output'),
			)));

			$this->triggerEvent("vanity.generate.format.${format}", new EventStore(array(
				'files' => &$files,
				'input' => ConfigStore::get('generator.input'),
				'output' => ConfigStore::get('generator.output'),
			)));

			$this->triggerEvent("vanity.generate.format.${format}.post", new EventStore(array(
				'files' => &$files,
				'input' => ConfigStore::get('generator.input'),
				'output' => ConfigStore::get('generator.output'),
			)));
		}

		#--------------------------------------------------------------------------#

		$this->triggerLogMessageEvent();
		$this->triggerEvent('vanity.command.complete');

		echo PHP_EOL;
	}
}
