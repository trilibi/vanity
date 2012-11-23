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


namespace Vanity\Config;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Yaml\Yaml as YAML;
use Vanity\Config\Store as ConfigStore;
use Vanity\Console\Utilities as ConsoleUtil;
use Vanity\System\Store as SystemStore;

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
	 * @type InputInterface
	 */
	protected $input;

	/**
	 * Storage for text formatters.
	 * @type stdClass
	 */
	protected $formatter;

	/**
	 * Storage for the config "variable" hashmap.
	 * @type array
	 */
	protected $variable_map;

	/**
	 * Storage for the configuration file path.
	 * @type string
	 */
	protected $config_path;

	/**
	 * Instantiates the {@see Resolve} class.
	 *
	 * @param InputInterface $input The command-line input.
	 * @param string $configPath The path to the config definition.
	 */
	public function __construct(InputInterface $input, $configPath)
	{
		$this->input = $input;
		$this->formatter = ConsoleUtil::formatters();
		$this->variable_map = array();
		$this->config_path = $configPath;
	}

	/**
	 * Read the config.yml file.
	 *
	 * @return void
	 */
	public function read()
	{
		$resolved_configs = array();

		// Merge all possible configuration types
		$config_store = array_merge(
			$this->defaultValues(),
			$this->fileValues(),
			$this->cliValues()
		);

		// Resolve the realpath for the config directory
		if (isset($config_store['vanity.config_dir']) && realpath($config_store['vanity.config_dir']))
		{
			$config_store['vanity.config_dir'] = realpath($config_store['vanity.config_dir']);
		}

		// Update the persistent variable map
		foreach ($config_store as $config => $value)
		{
			$this->variable_map['%' . strtoupper($config) . '%'] = $value;
		}

		// Resolve the values
		foreach ($config_store as $config => $value)
		{
			if ($value === 'true')
			{
				$resolved_configs[$config] = true;
			}
			elseif ($value === 'false')
			{
				$resolved_configs[$config] = false;
			}
			else
			{
				$resolved_configs[$config] = $this->resolveVariables($value);
			}
		}

		// Store the config information
		ConfigStore::set($resolved_configs);
	}

	/**
	 * Resolves `%VARIABLE%`-style "variables" from configuration values.
	 *
	 * @param  string $s The value to resolve variables for.
	 * @return string    The value with the variables resolved.
	 */
	public function resolveVariables($s)
	{
		foreach ($this->variable_map as $variable => $value)
		{
			if (is_string($s) && strpos($s, $variable) !== false)
			{
				$s = str_replace($variable, $value, $s);
			}
		}

		return $s;
	}

	/**
	 * Return the default config values.
	 *
	 * @return array The default config values.
	 */
	private function defaultValues()
	{
		$options = include $this->config_path;
		$config = ConfigStore::convert($options);

		foreach ($config as $name => $value)
		{
			list($type, $description, $default) = $value;
			$config[$name] = $default;
		}

		// $config = array_filter($config);

		return $config;
	}

	/**
	 * Return the config values passed in the config.yml file.
	 *
	 * @return array The config values passed in the config.yml file.
	 */
	private function fileValues()
	{
		// Use the config directory passed to the CLI
		$config_dir = $this->cliValues(true) ?: SystemStore::get('_.project_config_dir');

		if (file_exists($config_dir . '/config.yml'))
		{
			ConfigStore::$messages[] = 'Merged configuration options from ' . $this->formatter->info->apply(' ' . $config_dir . '/config.yml ');
			$options = YAML::parse($config_dir . '/config.yml');

			$config = ConfigStore::convert($options);
			$config = array_filter($config);

			return $config;
		}

		return array();
	}

	/**
	 * Return the config values passed to the CLI.
	 *
	 * @param  boolean $returnConfigDir Whether or not to return the configuration directory directly.
	 * @return array                    The config values passed to the CLI.
	 */
	private function cliValues($returnConfigDir = false)
	{
		$available_configs = include $this->config_path;
		$available_configs = array_keys(ConfigStore::convert($available_configs));
		$config = array();

		foreach ($available_configs as $option)
		{
			$config[$option] = $this->input->getOption($option);
		}

		$config = array_filter($config);

		if ($returnConfigDir)
		{
			return (isset($config['vanity.config_dir']) && file_exists($config['vanity.config_dir'])) ?
				realpath($config['vanity.config_dir']) :
				null;
		}

		if (count($config) > 0)
		{
			ConfigStore::$messages[] = 'Merged configuration options from the console.';
		}

		return $config;
	}
}
