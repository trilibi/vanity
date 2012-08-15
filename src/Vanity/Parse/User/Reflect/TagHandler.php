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


namespace Vanity\Parse\User\Reflect;

use Reflector;
use ReflectionClass;
use ReflectionException;
use phpDocumentor\Reflection\DocBlock;
use Vanity\Parse\User\Reflect\AncestryHandler;
use Vanity\Parse\User\Tag;

/**
 * Handle tags for a class.
 *
 * @author Ryan Parman <http://ryanparman.com>
 * @link   http://vanitydoc.org
 */
class TagHandler
{
	/**
	 * The {@see Reflector} which represents the reflector to work with.
	 * @var Reflector
	 */
	protected $reflector;

	/**
	 * Storage for the docblocks.
	 * @var array
	 */
	protected $docblock;

	/**
	 * Storage for ancestry.
	 * @var AncestryHandler
	 */
	public $ancestry;

	/**
	 * Constructs a new instance of this class.
	 *
	 * @param Reflector       $reflector The reflector to work with.
	 * @param AncestryHandler $ancestry  The ancestry data for the class.
	 */
	public function __construct(Reflector $reflector, AncestryHandler $ancestry)
	{
		$this->reflector = $reflector;
		$this->ancestry = $ancestry;
		$this->docblock = new DocBlock($this->reflector->getDocComment());
	}

	/**
	 * Get the short description.
	 *
	 * @return string The description.
	 */
	public function getShortDescription()
	{
		return $this->docblock->getShortDescription();
	}

	/**
	 * Get the long description.
	 *
	 * @return string The description.
	 */
	public function getLongDescription()
	{
		return $this->docblock->getLongDescription()->getContents();
	}

	/**
	 * Retrieve the tags for the class.
	 *
	 * @return array A list of tags.
	 */
	public function getTags()
	{
		$metadata = array();
		$tags = $this->docblock->getTags();

		if (count($tags))
		{
			if (!isset($metadata['tag']))
			{
				$metadata['tag'] = array();
			}

			foreach ($tags as $rtag)
			{
				$dtag = new Tag($rtag, $this->ancestry);
				$metadata['tag'][] = $dtag->determine()->process();
			}
		}

		return $metadata;
	}
}
