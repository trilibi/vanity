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


namespace Vanity\Parse\User\Tag;

use phpDocumentor\Reflection\DocBlock\Tag;
use Vanity\Parse\User\Tag\AbstractNameTypeVariableDescription;
use Vanity\Parse\User\Tag\HandlerInterface;

/**
 * The handler for @property tags.
 */
class PropertyHandler extends AbstractNameTypeVariableDescription implements HandlerInterface
{
	/**
	 * {@inheritdoc}
	 */
	public function process($elongate = false)
	{
		$content = $this->clean($this->tag->getContent());

		$return = array(
			'raw'         => $content,
			'name'        => $this->tag->getName(),
			'type'        => 'void',
			'property'    => null,
			'description' => null,
		);

		$pattern = '/
			^[\s]*                # Preceding whitespace
			(?:
				([\w\|_\\\\]+)    # Type, if exists
				[\s]+
			)?
			\$([\w\|_\\\\]+)      # Property name
			[\s]*
			(.*)                  # Description
		/ux';

		if (preg_match($pattern, $content, $m))
		{
			list(, $type, $property, $description) = $m;

			$return['property'] = $property;

			$return = array_merge(
				$return,
				$this->handleType($type, $elongate),
				$this->handleDescription($description, $elongate)
			);
		}

		return $return;
	}
}
