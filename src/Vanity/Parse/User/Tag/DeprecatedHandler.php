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

namespace Vanity\Parse\User\Tag;

use Vanity\Parse\User\Tag\HandlerInterface;
use Vanity\Parse\User\Tag\AbstractNameTypeDescription;

/**
 * The handler for @deprecated tags.
 */
class DeprecatedHandler extends AbstractNameTypeDescription implements HandlerInterface
{
	public function process()
	{
		$return = parent::process();

		if (isset($return['type']))
		{
			// If it starts with a digit, let's assume it's a version number
			if (preg_match('/^\d+/', $return['type']))
			{
				$return['version'] = $return['type'];
			}
			elseif (isset($return['description']))
			{
				$return['description'] = $return['type'] . ' ' . $return['description'];
			}

			unset($return['type']);
		}

		return $return;
	}
}
