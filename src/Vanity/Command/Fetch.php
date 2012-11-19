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
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Exception\IOException;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Process\Process;
use Vanity\Command\Base as BaseCommand;
use Vanity\Config\Resolve as ConfigResolve;
use Vanity\Config\Store as ConfigStore;
use Vanity\Console\Utilities as ConsoleUtil;
use Vanity\Event\Event\Store as EventStore;
use Vanity\GlobalObject\Logger;

/**
 * Command that executes `fetch`.
 *
 * @author Ryan Parman <http://ryanparman.com>
 * @link   http://vanitydoc.org
 */
class Fetch extends BaseCommand
{
	/**
	 * The PHP subversion repositories to pull from.
	 */
	private $repositories = array(
		VANITY_PHPREF_DIR        => array('http://svn.php.net/repository/phpdoc/en/trunk/reference/'),
		VANITY_ENTITY_GLOBAL_DIR => array('http://svn.php.net/repository/phpdoc/doc-base/trunk/entities/'),
		VANITY_ENTITY_LANG_DIR   => array('http://svn.php.net/repository/phpdoc/en/trunk/', ' --depth files')
	);

	/**
	 * The command-line arguments and options.
	 *
	 * @return void
	 */
	protected function configure()
	{
		$this
			->setName('fetch')
			->setDescription('Fetches a copy of the latest PHP API Reference from PHP.net. Useful when extending PHP\'s base classes.')
		;

		$options = include __DIR__ . '/fetch_configs.php';
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

				$description .= ConsoleUtil::formatters()->gold->apply(' (default: ' . $default . ')');
			}

			$this->addOption($option, null, $type, $description);
		}
	}

	/**
	 * Execute the logic for the command.
	 *
	 * @param  InputInterface  $input  The command-line input.
	 * @param  OutputInterface $output The command-line output.
	 * @return void
	 */
	protected function execute(InputInterface $input, OutputInterface $output)
	{
		echo PHP_EOL;

		// Resolve the configuration and display it
		$config = new ConfigResolve($input, __DIR__ . '/fetch_configs.php');
		$config->read();
		$this->displayConfig($output);

		Logger::get()->{ConfigStore::get('log.commands')}('Running command:', array($this->getName()));
		echo PHP_EOL;

		// Instantiate
		$filesystem = new Filesystem();

		// Handle a fresh checkout
		if (!$filesystem->exists(VANITY_CACHE_DIR))
		{
			Logger::get()->{ConfigStore::get('log.info')}('Cache directory does not exist.');
			Logger::get()->{ConfigStore::get('log.info')}('Attempting to create:', array(VANITY_CACHE_DIR));

			try {
				$filesystem->mkdir(VANITY_CACHE_DIR, 0777);
				$this->triggerEvent('vanity.command.php.fetch.checkout.pre', new EventStore(array(
					'cache_dir' => VANITY_CACHE_DIR,
					'type'      => 'checkout'
				)));

				$output->writeln($this->formatter->yellow->apply('PHP DOCUMENTATION CHECKOUT'));
				$output->writeln('Downloading the PHP documentation for the first time. This may take a few minutes.');
				echo PHP_EOL;

				foreach ($this->repositories as $write_to => $repository)
				{
					$url = $repository[0];
					$append = isset($repository[1]) ? $repository[1] : '';
					$output->writeln($this->formatter->green->apply($url));

					$svn = "svn co ${url} ${write_to}${append}";
					Logger::get()->{ConfigStore::get('log.commands')}($svn);
					$process = new Process($svn);
					$process->run(function($type, $buffer) use ($output)
					{
						if ($type === 'err')
						{
							$output->writeln('ERR > ' . $buffer);
						}
						else
						{
							$output->writeln(TAB . trim($buffer));
						}
					});

					unset($process);
					echo PHP_EOL;
				}

				$this->triggerEvent('vanity.command.php.fetch.checkout.post', new EventStore(array(
					'cache_dir' => VANITY_CACHE_DIR,
					'type'      => 'update'
				)));
			}
			catch (IOException $e)
			{
				Logger::get()->{ConfigStore::get('log.error')}('Failed to create user cache directory. Halting.', array(VANITY_CACHE_DIR));
				throw new IOException('Vanity was unable to create the user cache directory at ' . VANITY_CACHE_DIR . ', or was unable to set the permissions to 0777.');
			}
		}

		// Handle an update
		else
		{
			Logger::get()->{ConfigStore::get('log.info')}('Cache directory already exists.', array(VANITY_CACHE_DIR));

			$this->triggerEvent('vanity.command.php.fetch.update.pre', new EventStore(array(
				'cache_dir' => VANITY_CACHE_DIR,
				'type'      => 'update'
			)));

			$output->writeln($this->formatter->yellow->apply('PHP DOCUMENTATION UPDATE'));
			$output->writeln('Updating the PHP documentation.');
			echo PHP_EOL;

			foreach ($this->repositories as $write_to => $repository)
			{
				$url = $repository[0];
				$append = isset($repository[1]) ? $repository[1] : '';
				$output->writeln($this->formatter->green->apply($url));

				$svn = "svn up ${write_to}${append}";
				Logger::get()->{ConfigStore::get('log.commands')}($svn);
				$process = new Process($svn);
				$process->run(function($type, $buffer) use ($output)
				{
					if ($type === 'err')
					{
						$output->writeln('ERR > ' . $buffer);
					}
					else
					{
						$output->writeln(TAB . trim($buffer));
					}
				});

				unset($process);
				echo PHP_EOL;
			}

			$this->triggerEvent('vanity.command.php.fetch.update.post', new EventStore(array(
				'cache_dir' => VANITY_CACHE_DIR,
				'type'      => 'update'
			)));
		}

		$this->triggerLogMessageEvent();
		$this->triggerEvent('vanity.command.complete');

		echo PHP_EOL;
	}
}
