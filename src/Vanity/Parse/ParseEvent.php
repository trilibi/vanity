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

namespace Vanity\Parse
{
	use Vanity,
	    Symfony\Component\Console\Formatter\OutputFormatterStyle as ConsoleFormat,
	    Symfony\Component\Console\Input\InputInterface,
	    Symfony\Component\Console\Output\OutputInterface,
	    Symfony\Component\EventDispatcher\Event,
	    Symfony\Component\Finder\Finder,
	    Vanity\Config\Store as ConfigStore,
	    Vanity\Console\Utilities as ConsoleUtil;

	class ParseEvent extends Event
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
		 * Stores the list of parsable files.
		 */
		public $parsable_files = array();

		/**
		 * Stores the list of parsable classes.
		 */
		public $parsable_classes = array();

		/**
		 * Constructs a new instance of <Vanity\Parse\ParseEvent>.
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
			$this->hide_formatter = new ConsoleFormat('black');
			$this->hide_formatter->setOption('bold');
		}

		public function find_project_files()
		{
			$this->output->writeln($this->h1_formatter->apply('MATCHED PROJECT FILES:'));
			$counter = 0;

			$finder = Finder::create()
				->files()
				->name(ConfigStore::get('parser.match'))
				->in(VANITY_PROJECT_WORKING_DIR);

			foreach ($finder as $file)
			{
				self::$parsable_files[] = $file->getRealpath();
				$file = str_replace(VANITY_PROJECT_WORKING_DIR . '/', '', $file->getRealpath());
				$this->output->writeln(ConsoleUtil::indent($file, $this->h2_formatter->apply('-> ')));
				$counter++;
			}

			$this->output->writeln('');
			$this->output->writeln("Matched ${counter} files.");
			$this->output->writeln('');
		}

		public function get_class_list()
		{
			$this->output->writeln($this->h1_formatter->apply('DOCUMENTABLE CLASSES:'));

			if (!class_exists('\DocBlox_Parallel_Manager'))    require_once VANITY_VENDOR . '/docblox/parallel/Manager.php';
			if (!class_exists('\DocBlox_Parallel_Worker'))     require_once VANITY_VENDOR . '/docblox/parallel/Worker.php';
			if (!class_exists('\DocBlox_Parallel_WorkerPipe')) require_once VANITY_VENDOR . '/docblox/parallel/WorkerPipe.php';

			$manager = new \DocBlox_Parallel_Manager();
			$manager
				->addWorker(new \DocBlox_Parallel_Worker(function() { sleep(1); return 'a'; }))
				->addWorker(new \DocBlox_Parallel_Worker(function() { sleep(2); return 'b'; }))
				->addWorker(new \DocBlox_Parallel_Worker(function() { sleep(3); return 'c'; }))
				->addWorker(new \DocBlox_Parallel_Worker(function() { sleep(2); return 'd'; }))
				->addWorker(new \DocBlox_Parallel_Worker(function() { sleep(1); return 'e'; }))
				->execute();

			foreach ($manager as $worker)
			{
				var_dump($worker->getResult());
			}
		}
	}
}
