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

namespace Vanity\Config;

/**
 * Store the configuration data for the app.
 *
 * @author Ryan Parman <http://ryanparman.com>
 * @link   http://vanitydoc.org
 */
class Store
{
	/**
	 * Stores the configuration.
	 * @var array
	 */
	private static $config;

	/**
	 * Stores the messages to display.
	 * @var array
	 */
	public static $messages = array();

	/**
	 * Retrieve the configuration.
	 *
	 * @param  string $key The configuration key to look up.
	 * @return string|null The value of the configuration key. Returns `null` if key is unmatched.
	 */
	public static function get($key = null)
	{
		if ($key)
		{
			if (isset(self::$config[$key]))
			{
				return self::$config[$key];
			}

			return null;
		}

		return self::$config;
	}

	/**
	 * Set the configuration.
	 *
	 * @param  array $config Configuration data to store.
	 * @return void
	 */
	public static function set(array $config)
	{
		self::$config = $config;
	}

	/**
	 * Converts multi-dimensional arrays into period-delimited strings.
	 *
	 * @param  array  $config The configuration setting.
	 * @param  string $prefix The prefix for the node.
	 * @return array          The configuration array.
	 */
	public static function convert(array $config, $prefix = '')
	{
		foreach ($config as $key => $value)
		{
			unset($config[$key]);

			if (is_array($value))
			{
				if (self::is_indexed_array($value))
				{
					if ($prefix)
					{
						// Save
						$config[$prefix . '.' . $key] = $value;
					}
					else
					{
						// Save
						$config[$key] = $value;
					}
				}
				else
				{
					if ($prefix)
					{
						$key = $prefix . '.' . $key;
					}

					// Recurse associative arrays
					$config = array_merge($config, self::convert($value, $key));
				}
			}
			else
			{
				if ($prefix)
				{
					$config[$prefix . '.' . $key] = $value;
				}
				else
				{
					$config[$key] = $value;
				}
			}
		}

		return $config;
	}

	/**
	 * Determines whether or not the specified array is an indexed array.
	 *
	 * @param  array   $array The array to verify.
	 * @return boolean        Whether or not the specified array is an indexed array.
	 */
	private static function is_indexed_array(array $array)
	{
		$keys = array_keys($array);

		$tested_keys = array_filter($keys, function($key)
		{
			return is_int($key);
		});

		return (count($keys) === count($tested_keys));
	}
}
