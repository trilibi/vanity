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

namespace Vanity\Parse\User;

use Symfony\Component\Console\Output\OutputInterface;
use Vanity\Config\Store as ConfigStore;
use Vanity\Console\Utilities as ConsoleUtil;
use Vanity\Find\Find;
use Vanity\Event\Dispatcher;
use Vanity\Parse\User\Reflect;

class ReflectAll
{
	/**
	 * [$classes description]
	 * @var [type]
	 */
	public $classes;

	/**
	 * [$path_pattern description]
	 * @var [type]
	 */
	public $path_pattern;

	/**
	 * [__construct description]
	 * @param array  $classes      [description]
	 * @param [type] $path_pattern [description]
	 */
	public function __construct(array $classes, $path_pattern)
	{
		$this->path_pattern = $path_pattern;
		$this->formatter = ConsoleUtil::formatters();
		$this->classes = $classes;
	}

	/**
	 * [process description]
	 * @param  OutputInterface $output [description]
	 * @return [type]                  [description]
	 */
	public function process(OutputInterface $output)
	{
		$output->writeln($this->formatter->yellow->apply('WRITING CLASS DEFINITIONS'));

		// Resolve output path variables
		Dispatcher::get()->dispatch('parse.user.reflect.all.pre');
		$this->path_pattern = str_replace('%STAGE%', $this->asciify(ConfigStore::get('api.stage')), $this->path_pattern);
		$this->path_pattern = str_replace('%VERSION%', $this->asciify(ConfigStore::get('vanity.version')), $this->path_pattern);
		$this->path_pattern = str_replace('%FORMAT%', 'json', $this->path_pattern);

		foreach ($this->classes as $class)
		{
			$reflect = new Reflect($class);
			$reflect->process();
			$reflect->save($this->path_pattern, $output);
		}

		Dispatcher::get()->dispatch('parse.user.reflect.all.post');

		// Count the classes
		echo PHP_EOL;
		$files = Find::files($this->path_pattern, '*.json');
		$count = count($files['absolute']);
		$output->writeln('Wrote ' . $this->formatter->info->apply(" ${count} ") . ' class definition ' . ConsoleUtil::pluralize($count, 'file', 'files') . '.');
	}

	/**
	 * [asciify description]
	 * @param  [type] $s [description]
	 * @return [type]    [description]
	 */
	protected function asciify($s)
	{
		return preg_replace('/[^a-z0-9_\-\.]/i', '', $s);
	}
}
