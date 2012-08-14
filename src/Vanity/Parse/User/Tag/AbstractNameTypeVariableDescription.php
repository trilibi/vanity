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
 * The default handler for name:type:variable:description tags.
 */
abstract class AbstractNameTypeVariableDescription extends AbstractHandler implements HandlerInterface
{
	/**
	 * [process description]
	 * @return [type] [description]
	 */
	public function process()
	{
		$return = array();
		$return['name'] = $this->tag->getName();

		$content = $this->clean($this->tag->getContent());
		$content = explode(' ', $content);
		$type = array_shift($content);
		$variable = array_shift($content);
		$description = trim(implode(' ', $content));

		if (strpos($type, '|'))
		{
			$return['type'] = 'mixed';
			$return['types'] = explode('|', $type);
			$return['types'] = array_map(function($type)
			{
				return ParseUtil::elongateType($type);
			},
			$return['types']);
		}
		else
		{
			$return['type'] = ParseUtil::elongateType($type);
		}

		$return['variable'] = str_replace('$', '', $variable);

		if ($description)
		{
			$return['description'] = $description;
		}

		return $return;
	}
}
