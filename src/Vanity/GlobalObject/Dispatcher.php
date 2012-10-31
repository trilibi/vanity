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


namespace Vanity\GlobalObject;

use Symfony\Component\EventDispatcher\EventDispatcher;

/**
 * Maintains the global event dispatcher for the app.
 *
 * @author Ryan Parman <http://ryanparman.com>
 * @link   http://vanitydoc.org
 */
class Dispatcher
{
	/**
	 * Stores the Event Dispatcher.
	 * @type EventDispatcher
	 */
	private static $dispatcher;

	/**
	 * Retrieve the Event Dispatcher.
	 *
	 * @return EventDispatcher The event dispatcher instance to use.
	 */
	public static function get()
	{
		return self::$dispatcher;
	}

	/**
	 * Set the Event Dispatcher.
	 *
	 * @param  EventDispatcher $dispatcher The event dispatcher instance to use.
	 * @return void
	 */
	public static function set(EventDispatcher $dispatcher)
	{
		self::$dispatcher = $dispatcher;
	}
}
