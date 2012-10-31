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


namespace Vanity\System;

use ReflectionExtension;

/**
 * Resolves system dependencies returned by \ReflectionExtension.
 */
class ExtensionDependencyResolver
{
	/**
	 * Storage for the list of dependencies.
	 * @type array
	 */
	protected $list;

	/**
	 * Constructs a new instance of this class.
	 *
	 * @param array $list The list of extension names with dependencies to resolve.
	 */
	public function __construct(array $list)
	{
		$this->list = $list;
	}

	/**
	 * Resolve the dependencies.
	 *
	 * @return array A resolved list of dependencies.
	 */
	public function resolve()
	{
		$queue = $this->list;
		$done = array();

		while (count($queue))
		{
			// Grab an extension name from the queue
			$extension = array_shift($queue);

			// Mark this one as done, even though we're still working on it
			$done[] = $extension;

			// Get a reflected object for it
			$rextension = new ReflectionExtension($extension);

			// Grab a list of extension names that are required dependencies of the current extension
			$new = array_keys(array_filter($rextension->getDependencies(), function($status)
			{
				return ($status === 'Required');
			}));

			// Go through each new extension name in the list
			foreach ($new as $item)
			{
				// As long as we haven't already process it...
				if (array_search($item, $done) === false)
				{
					// Add it to the todo list.
					$queue[] = $item;
				}
			}
		}

		sort($done);
		return $done;
	}
}
