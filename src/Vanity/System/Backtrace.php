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


namespace Vanity\System;

use Vanity\Console\Utilities as ConsoleUtil;

/**
 * A collection of utilities for working with backtraces.
 */
class Backtrace
{
	/**
	 * Renders a backtrace message as a string.
	 *
	 * @param  array  $backtrace The result of `debug_backtrace()`.
	 * @return string            A stringified backtrace message for printing to the console.
	 */
	public static function render(array $backtrace)
	{
		$messages = array();
		$output = array();
		$formatter = ConsoleUtil::formatters();

		foreach ($backtrace as $trace)
		{
			@$messages[$trace['file'] . ':' . $trace['line']] = $trace['class'] . $trace['type'] . $trace['function'] . '()';
		}

		$padding = ConsoleUtil::tablify($messages);

		foreach ($messages as $code => $line)
		{
			$output[] = implode(' @ ', array(
				str_pad($code, $padding, ' ', STR_PAD_RIGHT),
				$formatter->gold->apply($line),
			));
		}

		return PHP_EOL . TAB . TAB . implode(PHP_EOL . TAB . TAB, $output);
	}
}
