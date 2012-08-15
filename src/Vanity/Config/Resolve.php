<?php
/**
 * Copyright (c) 2009-2012 [Ryan Parman](http://ryanparman.com)
 * Copyright (c) 2011-2012 [Amazon Web Services, LLC](http://aws.amazon.com)
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


namespace Vanity\Config;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Yaml\Yaml as YAML;
use Vanity\Config\Store as ConfigStore;
use Vanity\Console\Utilities as ConsoleUtil;

/**
 * Resolves configurations passed to the Vanity CLI application.
 *
 * Merges the default values, the values stored in the config.yml file, and the
 * values passed via the CLI.
 *
 * @author Ryan Parman <http://ryanparman.com>
 * @link   http://vanitydoc.org
 */
class Resolve
{
	/**
	 * Storage for the command-line input.
	 * @var InputInterface
	 */
	protected $input;

	/**
	 * Storage for text formatters.
	 * @var stdClass
	 */
	protected $formatter;

	/**
	 * Instantiates the {@see Resolve} class.
	 *
	 * @param InputInterface $input The command-line input.
	 */
	public function __construct(InputInterface $input)
	{
		$this->input = $input;
		$this->formatter = ConsoleUtil::formatters();
	}

	/**
	 * Read the config.yml file.
	 *
	 * @return void
	 */
	public function read()
	{
		// Store the config information
		ConfigStore::set(array_merge(
			$this->default_values(),
			$this->file_values(),
			$this->cli_values()
		));
	}

	/**
	 * Return the default config values.
	 *
	 * @return array The default config values.
	 */
	private function default_values()
	{
		$options = include VANITY_SOURCE . '/configs.php';
		$config = ConfigStore::convert($options);

		foreach ($config as $name => $value)
		{
			list($type, $description, $default) = $value;
			$config[$name] = $default;
		}

		$config = array_filter($config);

		return $config;
	}

	/**
	 * Return the config values passed in the config.yml file.
	 *
	 * @return array The config values passed in the config.yml file.
	 */
	private function file_values()
	{
		if (file_exists(VANITY_PROJECT_CONFIG_DIR . '/config.yml'))
		{
			ConfigStore::$messages[] = 'Merged configuration options from ' . $this->formatter->info->apply(' ' . VANITY_PROJECT_CONFIG_DIR . '/config.yml ');
			$options = YAML::parse(VANITY_PROJECT_CONFIG_DIR . '/config.yml');

			$config = ConfigStore::convert($options);
			$config = array_filter($config);

			return $config;
		}

		return array();
	}

	/**
	 * Return the config values passed to the CLI.
	 *
	 * @return array The config values passed to the CLI.
	 */
	private function cli_values()
	{
		$available_configs = include VANITY_SOURCE . '/configs.php';
		$available_configs = array_keys(ConfigStore::convert($available_configs));
		$config = array();

		foreach ($available_configs as $option)
		{
			$config[$option] = $this->input->getOption($option);
		}

		$config = array_filter($config);

		if (count($config) > 0)
		{
			ConfigStore::$messages[] = 'Merged configuration options from the console.';
		}

		return $config;
	}
}
