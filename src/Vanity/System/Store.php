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

/**
 * Store the custom cache data for the app.
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
	protected static $config = array();

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
		self::$config = array_merge(self::$config, $config);
	}

	/**
	 * Add a configuration.
	 *
	 * @param  string $key    The key to use for lookups.
	 * @param  mixed  $config Configuration data to store.
	 * @return void
	 */
	public static function add($key, $config)
	{
		self::$config[$key] = $config;
	}
}
