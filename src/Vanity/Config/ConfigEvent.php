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

namespace Vanity\Config
{
	use Vanity,
	    Symfony\Component\Console\Formatter\OutputFormatterStyle as ConsoleFormat,
	    Symfony\Component\Console\Input\InputInterface,
	    Symfony\Component\Console\Output\OutputInterface,
	    Symfony\Component\EventDispatcher\Event,
	    Symfony\Component\Yaml\Yaml as YAML,
	    Vanity\Config\Store as ConfigStore,
	    Vanity\Console\Utilities as ConsoleUtil;

	class ConfigEvent extends Event
	{
		/**
		 * Stores the input object.
		 */
		public $input;

		/**
		 * Stores the output object.
		 */
		public $output;

		/**
		 * Stores the Console Output Formatter object.
		 */
		public $formatter;

		/**
		 * Constructs a new instance of <Vanity\Console\FetchEvent>.
		 *
		 * @param InputInterface  $input  The console input object.
		 * @param OutputInterface $output The console output object.
		 * @return void
		 */
		public function __construct(InputInterface $input, OutputInterface $output)
		{
			$this->input  = $input;
			$this->output = $output;

			$this->h1_formatter = new ConsoleFormat('yellow');
			$this->h1_formatter->setOption('bold');
			$this->h2_formatter = new ConsoleFormat('green');
		}

		/**
		 * Read the config.yml file.
		 *
		 * @return void
		 */
		public function read()
		{
			ConfigStore::set(array_merge_recursive(
				$this->default_values(),
				$this->file_values(),
				$this->cli_values()
			));
		}

		/**
		 * Display the configuration to the Console.
		 *
		 * @return void
		 */
		public function display()
		{
			$this->output->writeln($this->h1_formatter->apply('ACTIVE CONFIGURATION OPTIONS:'));

			$this->output->writeln(
				ConsoleUtil::indent(
					YAML::dump(ConfigStore::get()),
					$this->h2_formatter->apply('-> ')
				)
			);
		}

		/**
		 * Return the default config values.
		 *
		 * @return array The default config values.
		 */
		private function default_values()
		{
			$config = array(
				'product_version' => null,
				'github'          => null,
				'google_code'     => null,
				'parser'          => array(
					'bootstrap'        => null,
					'match'            => '*.php',
					'exclude_access'   => 'private',
					'exclude_classes'  => '/Exception/i',
					'exclude_methods'  => '/__([a-z]+)/i',
					'use_changelog'    => 'changelog.yml',
					'use_groups'       => 'groups.yml',
					'use_seealso'      => 'seealso.yml',
					'generate_indexes' => 'true',
					'generate_todos'   => 'true',
					'pattern_todos'    => '/@?\s*todo(:|\s)+/i',
					'warn_todo'        => 'false',
					'warn_groups'      => 'true',
					'stage'            => 'production',
				)
			);

			$config = array_filter($config);
			$config['parser'] = array_filter($config['parser']);

			return $config;
		}

		/**
		 * Return the config values passed in the config.yml file.
		 *
		 * @return array The config values passed in the config.yml file.
		 */
		private function file_values()
		{
			$config = YAML::parse(VANITY_PROJECT_CONFIG_DIR . '/config.yml');

			$config = array_filter($config);
			$config['parser'] = array_filter($config['parser']);

			return $config;
		}

		/**
		 * Return the config values passed to the CLI.
		 *
		 * @return array The config values passed to the CLI.
		 */
		private function cli_values()
		{
			$cli = array();

			// Non-namespaced
			foreach (array(
				'product-version',
				'github',
				'google-code'
			) as $value) {
				$cli[str_replace('-', '_', $value)] = $this->input->getOption($value);
			}

			// Parser-namespaced
			foreach (array(
				'bootstrap',
				'exclude-access',
				'exclude-classes',
				'exclude-methods',
				'generate-indexes',
				'generate-todos',
				'match',
				'pattern-todos',
				'stage',
				'use-changelog',
				'use-groups',
				'use-seealso',
				'warn-groups',
				'warn-todo',
			) as $value) {
				$cli['parser'][str_replace('-', '_', $value)] = $this->input->getOption($value);
			}

			$cli = array_filter($cli);
			$cli['parser'] = array_filter($cli['parser']);

			return $cli;
		}
	}
}
