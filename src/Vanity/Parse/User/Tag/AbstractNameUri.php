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

use phpDocumentor\Reflection\DocBlock;
use Vanity\Parse\User\Tag\AbstractHandler;
use Vanity\Parse\User\Tag\HandlerInterface;
use Vanity\Parse\Utilities as ParseUtil;

/**
 * The default handler for name:uri tags.
 */
abstract class AbstractNameUri extends AbstractHandler implements HandlerInterface
{
	/**
	 * [process description]
	 * @return [type] [description]
	 */
	public function process()
	{
		$content = $this->clean($this->tag->getContent());
		$identifier = null;
		$uri = null;

		// Match: My Name <URI>
		preg_match_all('/([^<]*)?(<([^<]*)>)?/', $content, $matches);
		if (isset($matches[1]))
		{
			$identifier = $matches[1][0];
		}
		if (isset($matches[3]))
		{
			$uri = $matches[3][0];
		}

		$return = array();
		$return['name'] = $this->tag->getName();

		if ($uri)
		{
			$return['uri'] = $uri;
			$return['identifier'] = $return['uri'];

			// http://example.com
			if (preg_match('/^https?:/i', $return['uri']))
			{
				$return['uri_hint'] = 'url';
			}

			// me@example.com
			elseif (preg_match('/[\w\._\-\+]+@[\w\._\-\+]+\./i', $return['uri']))
			{
				$return['uri_hint'] = 'mail';
			}

			// @example (e.g., Twitter)
			elseif (preg_match('/^@/', $return['uri']))
			{
				$return['uri_hint'] = 'screen_name';
			}

			// gravatar:066da34008adb924c115df7a39779d8d
			// github:skyzyx
			elseif (preg_match_all('/\w+:(.+)/i', $return['uri'], $m))
			{
				$return['identifier'] = trim($m[1][0]);
				$return['uri_hint'] = 'service';
			}
		}

		if ($identifier)
		{
			$return['identifier'] = trim($identifier);
		}

		return $return;
	}
}