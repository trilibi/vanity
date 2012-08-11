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


namespace Vanity\Command\PHP;

use Exception;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Exception\IOException;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Process\Process;
use Vanity\Command\Base as BaseCommand;

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

	protected function configure()
	{
		$this
			->setName('php:fetch')
			->setDescription('Fetches a copy of the latest PHP API Reference from PHP.net. Useful when extending PHP\'s base classes.');
	}

	protected function execute(InputInterface $input, OutputInterface $output)
	{
		$this->logger->info('Running command:', array($this->getName()));
		echo PHP_EOL;

		// Instantiate
		$filesystem = new Filesystem();

		// Handle a fresh checkout
		if (!$filesystem->exists(VANITY_CACHE_DIR))
		{
			$this->logger->info('Cache directory does not exist.');
			$this->logger->info('Attempting to create:', array(VANITY_CACHE_DIR));

			try {
				$filesystem->mkdir(VANITY_CACHE_DIR, 0777);
				$this->triggerEvent('php.fetch.checkout.pre');

				$output->writeln($this->formatter->yellow->apply('PHP DOCUMENTATION CHECKOUT'));
				$output->writeln('Downloading the PHP documentation for the first time. This may take a few minutes.');
				echo PHP_EOL;

				foreach ($this->repositories as $write_to => $repository)
				{
					$url = $repository[0];
					$append = isset($repository[1]) ? $repository[1] : '';
					$output->writeln($this->formatter->green->apply($url));

					$svn = "svn co ${url} ${write_to}${append}";
					$this->logger->debug($svn);
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

				$this->triggerEvent('php.fetch.checkout.post');
			}
			catch (IOException $e)
			{
				$this->logger->info('Failed to create user cache directory. Halting.', array(VANITY_CACHE_DIR));
				throw new IOException('Vanity was unable to create the user cache directory at ' . VANITY_CACHE_DIR . ', or was unable to set the permissions to 0777.');
			}
		}

		// Handle an update
		else
		{
			$this->logger->info('Cache directory already exists.', array(VANITY_CACHE_DIR));

			$this->triggerEvent('php.fetch.update.pre');

			$output->writeln($this->formatter->yellow->apply('PHP DOCUMENTATION UPDATE'));
			$output->writeln('Updating the PHP documentation.');
			echo PHP_EOL;

			foreach ($this->repositories as $write_to => $repository)
			{
				$url = $repository[0];
				$append = isset($repository[1]) ? $repository[1] : '';
				$output->writeln($this->formatter->green->apply($url));

				$svn = "svn up ${write_to}${append}";
				$this->logger->debug($svn);
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

			$this->triggerEvent('php.fetch.update.post');
		}
	}
}
