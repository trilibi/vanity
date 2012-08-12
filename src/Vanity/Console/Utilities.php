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

namespace Vanity\Console;

use stdClass;
use Symfony\Component\Console\Formatter\OutputFormatterStyle;

class Utilities
{
	/**
	 * [formatters description]
	 * @return [type] [description]
	 */
	public static function formatters()
	{
		$formatter = new stdClass();

		// Text styles
		$formatter->green     = new OutputFormatterStyle('green', null, array('bold'));
		$formatter->yellow    = new OutputFormatterStyle('yellow', null, array('bold'));
		$formatter->gold      = new OutputFormatterStyle('yellow');
		$formatter->grey      = new OutputFormatterStyle('white');
		$formatter->dark_grey = new OutputFormatterStyle('black');

		// Highlighted styles
		$formatter->info    = new OutputFormatterStyle('white',  'blue',  array('bold'));
		$formatter->success = new OutputFormatterStyle('white',  'green', array('bold'));
		$formatter->warning = new OutputFormatterStyle('white',  'red',   array('bold'));
		$formatter->pending = new OutputFormatterStyle('black',  'white');

		return $formatter;
	}

	/**
	 * Indents the content on the Console.
	 *
	 * @param  string $content The textual content to indent.
	 * @return string The indented text.
	 */
	public static function indent($content, $prefix = '', $callback = null)
	{
		$callback = $callback? :(function($line) { return $line; });
		$contents = explode("\n", $content);

		$contents = array_map(function($line) use (&$prefix, &$callback)
		{
			if (trim($line) !== '')
			{
				if (is_callable($callback))
				{
					return TAB . $prefix . $callback($line);
				}

				return TAB . $prefix . $line;
			}

		}, $contents);

		return implode("\n", $contents);
	}

	/**
	 * Converts the number of seconds into HH:MM:SS format.
	 *
	 * @param  integer $seconds The number of seconds to format.
	 * @return string The formatted time.
	 */
	public static function time_hms($seconds = 0)
	{
		$time = '';

		// First pass
		$hours = (integer) ($seconds / 3600);
		$seconds = $seconds % 3600;
		$minutes = (integer) ($seconds / 60);
		$seconds = $seconds % 60;

		// Cleanup
		$time .= ($hours) ? $hours . ':' : '';
		$time .= ($minutes < 10 && $hours > 0) ? '0' . $minutes : $minutes;
		$time .= ':';
		$time .= ($seconds < 10) ? '0' . $seconds : $seconds;

		return $time;
	}

	/**
	 * Return the number of characters to pad the array key by.
	 *
	 * @param  array  $array The array to be tablified.
	 * @return integer The number of characters to pad the array key by.
	 */
	public static function tablify(array $array)
	{
		$array = array_keys($array);
		$array = array_map(function($item)
		{
			return strlen($item);
		}, $array);

		return max($array);
	}
}
