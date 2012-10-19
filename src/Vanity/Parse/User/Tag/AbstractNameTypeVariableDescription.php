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

use phpDocumentor\Reflection\DocBlock;
use Vanity\Parse\User\Reflect\AncestryHandler;
use Vanity\Parse\User\Reflect\TagHandler;
use Vanity\Parse\User\Tag\AbstractHandler;
use Vanity\Parse\User\Tag\HandlerInterface;
use Vanity\Parse\Utilities as ParseUtil;

/**
 * The default handler for name:type:variable:description tags.
 */
abstract class AbstractNameTypeVariableDescription extends AbstractHandler
{
	/**
	 * Handle the type/types for the tag.
	 *
	 * @param  string  $type     The raw type that has been parsed from the tag.
	 * @param  boolean $elongate Whether or not to elongate/resolve classes and aliases.
	 * @return array             An array containing a `type` and `types` key that should be merged back into the
	 *                           parent node.
	 */
	public function handleType($type, $elongate)
	{
		$return = array();

		if ($type && strpos($type, '|'))
		{
			$self = $this;
			$return['type'] = 'mixed';
			$return['types'] = explode('|', $type);
			$return['types'] = array_map(function($type) use ($self, $elongate)
			{
				return $elongate ? AncestryHandler::elongateType($type, $self->ancestry) : $type;
			},
			$return['types']);
		}
		elseif ($type)
		{
			$return['type'] = $elongate ? AncestryHandler::elongateType($type, $this->ancestry) : $type;
		}

		return $return;
	}

	/**
	 * Handle the arguments for the tag.
	 *
	 * @param  string  $arguments The raw arguments that have been parsed from the tag.
	 * @param  boolean $elongate  Whether or not to elongate/resolve classes and aliases.
	 * @return array              An array containing a `type` and `types` key that should be merged back into the
	 *                            parent node.
	 */
	public function handleArguments($arguments, $elongate)
	{
		$return = array();

		$return['arguments'] = $arguments;

		return $return;
	}

	/**
	 * Handle the arguments for the tag.
	 *
	 * @todo: Add support for resolving sub-blocks.
	 *
	 * @param  string  $description The raw description that have been parsed from the tag.
	 * @param  boolean $elongate    Whether or not to elongate/resolve classes and aliases.
	 * @return array                An array containing a `type` and `types` key that should be merged back into the
	 *                              parent node.
	 */
	public function handleDescription($description, $elongate)
	{
		$return = array();

		$return['description'] = $description;

		return $return;
	}
}
