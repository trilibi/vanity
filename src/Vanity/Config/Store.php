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
	class Store
	{
		/**
		 * Stores the configuration.
		 */
		private static $config;

		/**
		 * Retrieve the configuration.
		 */
		public static function get()
		{
			return self::$config;
		}

		/**
		 * Set the configuration.
		 */
		public static function set($config)
		{
			self::$config = self::convert($config);
		}

		/**
		 * Converts multi-dimensional arrays into period-delimited strings.
		 *
		 * @param  array  $config The configuration setting.
		 * @param  string $prefix The prefix for the node.
		 * @return array  The configuration array.
		 */
		private static function convert(array $config, $prefix = '')
		{
			foreach ($config as $key => $value)
			{
				if (is_array($value))
				{
					unset($config[$key]);
					$config = array_merge($config, self::convert($value, $key));
				}
				else
				{
					if ($prefix)
					{
						unset($config[$key]);
						$config[$prefix . '.' . $key] = $value;
					}
					else
					{
						unset($config[$key]);
						$config[$key] = $value;
					}
				}
			}

			return $config;
		}
	}
}
