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


namespace Vanity\Console;

use stdClass;
use Symfony\Component\Console\Formatter\OutputFormatterStyle;

/**
 * A collection of utilities for working with console apps.
 *
 * @author Ryan Parman <http://ryanparman.com>
 * @link   http://vanitydoc.org
 */
class Utilities
{
	/**
	 * A collection of text formatters for the console.
	 *
	 * @return stdClass A collection of text formatters.
	 */
	public static function formatters()
	{
		$formatter = new stdClass();

		// Text styles
		$formatter->green     = new OutputFormatterStyle('green', null, array('bold'));
		$formatter->yellow    = new OutputFormatterStyle('yellow', null, array('bold'));
		$formatter->_white_   = new OutputFormatterStyle('white', null, array('bold', 'underscore'));
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
	 * @return string          The indented text.
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
	 * @return string           The formatted time.
	 */
	public static function timeHMS($seconds = 0)
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
	 * @return integer       The number of characters to pad the array key by.
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

	/**
	 * Replacement for the built-in {@see php:json_encode()} method that prettifies
	 * the output when called under PHP 5.4.
	 *
	 * @param  array  $data The array to convert into a JSON document.
	 * @return string       A JSON document.
	 */
	public static function json_encode(array $data)
	{
		if (version_compare(PHP_VERSION, '5.4.0', '>='))
		{
			return json_encode($data, JSON_PRETTY_PRINT);
		}

		return json_encode($data);
	}

	/**
	 * Determines whether to return the singular or plural form of a word based
	 * on the `$count` value.
	 *
	 * @param  integer $count    The number of things.
	 * @param  string  $singular The singular form of a word.
	 * @param  string  $plural   The plural form of a word.
	 * @return string            Either the singular or plural form.
	 */
	public static function pluralize($count, $singular, $plural)
	{
		return ($count === 1) ? $singular : $plural;
	}

	/**
	 * Removes all characters from a string that are not alphanumeric,
	 * underscore, hyphen or period. Used for determining ideal filenames.
	 *
	 * @param  string $s The string to parse.
	 * @return string    The string will all non-whitelisted characters removed.
	 */
	public static function asciify($s)
	{
		return preg_replace('/[^a-z0-9_\-\.]/i', '', $s);
	}
}
