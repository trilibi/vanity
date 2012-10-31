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


namespace Vanity\Dictionary;

/**
 * Maintains a global list of supported shortnames for services.
 *
 * @author Ryan Parman <http://ryanparman.com>
 * @link   http://vanitydoc.org
 */
class Services
{
	/**
	 * Stores an in-memory copy of the service list.
	 * @type array
	 */
	private static $services = null;

	/**
	 * Retrieve service information based on its short code.
	 *
	 * @param  string $code The service shortcode.
	 * @param  string $user The username or identifier for the user of the service.
	 * @return array        Information about the specified short code.
	 */
	public static function get($code, $user)
	{
		if (is_null(self::$services))
		{
			self::parseAndCache();
		}

		if (isset(self::$services[$code]))
		{
			$info = self::$services[$code];
			$info['uri'] = str_replace('{user}', $user, $info['uri']);

			return $info;
		}

		return false;
	}

	/**
	 * Parse and cache the service list.
	 *
	 * @return void
	 */
	protected static function parseAndCache()
	{
		$json = VANITY_VENDOR . '/skyzyx/service-listing/services.json';

		if (file_exists($json))
		{
			self::$services = json_decode(file_get_contents($json), true);
		}
	}
}
