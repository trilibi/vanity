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
	    Vanity\Config\Store as ConfigStore;

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
			$config_cli = array();

			// Non-namespaced
			$config_cli['product_version'] = $this->input->getOption('product-version');
			$config_cli['github']          = $this->input->getOption('github');
			$config_cli['google_code']     = $this->input->getOption('google-code');

			// Parser
			$config_cli['parser']['bootstrap']       = $this->input->getOption('bootstrap');
			$config_cli['parser']['match']           = $this->input->getOption('match');
			$config_cli['parser']['exclude_access']  = $this->input->getOption('exclude-access');
			$config_cli['parser']['exclude_classes'] = $this->input->getOption('exclude-classes');
			$config_cli['parser']['exclude_methods'] = $this->input->getOption('exclude-methods');
			$config_cli['parser']['use_changelog']   = $this->input->getOption('use-changelog');
			$config_cli['parser']['use_groups']      = $this->input->getOption('use-groups');
			$config_cli['parser']['use_seealso']     = $this->input->getOption('use-seealso');
			$config_cli['parser']['create_indexes']  = $this->input->getOption('create-indexes');
			$config_cli['parser']['create_todos']    = $this->input->getOption('create-todos');
			$config_cli['parser']['pattern_todos']   = $this->input->getOption('pattern-todos');
			$config_cli['parser']['warn_todo']       = $this->input->getOption('warn-todo');
			$config_cli['parser']['warn_groups']     = $this->input->getOption('warn-groups');
			$config_cli['parser']['stage']           = $this->input->getOption('stage');

			$config_file = YAML::parse(VANITY_PROJECT_CONFIG_DIR . '/config.yml');

			ConfigStore::set(array_merge($config_file, $config_cli));
		}

		/**
		 * Display the configuration to the Console.
		 *
		 * @return void
		 */
		public function display()
		{
			$this->output->writeln(print_r(ConfigStore::get(), true));
		}
	}
}
