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


namespace Vanity\Parse\User\Reflect;

use phpDocumentor\Reflection\DocBlock;
use phpDocumentor\Reflection\DocBlock\Tag as DBTag;
use Vanity\Config\Store as ConfigStore;
use Vanity\Parse\User\Reflect\AncestryHandler;
use Vanity\Parse\User\Tag;
use Vanity\Parse\User\InlineTag;

/**
 * Handle tags for a class.
 *
 * @author Ryan Parman <http://ryanparman.com>
 * @link   http://vanitydoc.org
 */
class TagHandler
{
	/**
	 * Storage for the docblocks.
	 * @type array
	 */
	protected $docblock;

	/**
	 * Storage for ancestry.
	 * @type AncestryHandler
	 */
	public $ancestry;

	/**
	 * Constructs a new instance of this class.
	 *
	 * @param string          $docblock The docblock to work with.
	 * @param AncestryHandler $ancestry The ancestry data for the class.
	 */
	public function __construct($docblock, AncestryHandler $ancestry)
	{
		$this->docblock = new DocBlock($docblock);
		$this->ancestry = $ancestry;
	}

	/**
	 * Get the full description.
	 *
	 * @return string The description.
	 */
	public function getDescription()
	{
		$output = array($this->docblock->getShortDescription());
		$parsed_contents = $this->docblock->getLongDescription()->getParsedContents();

		if (is_array($parsed_contents) && count($parsed_contents) > 0)
		{
			foreach ($parsed_contents as $content)
			{
				if (is_string($content))
				{
					$output[] = $content;
				}
				elseif ($content instanceof DBTag)
				{
					$dtag = new InlineTag($content, $this->ancestry);
					$output[] = $dtag->determine()->process(ConfigStore::get('api.resolve_aliases'));
				}
				else
				{
					Logger::get()->{ConfigStore::get('api.log.error')}('Unknown inline tag object:', array(__FILE__, print_r($content, true)));
				}
			}
		}

		return $output;
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
				$metadata['tag'][] = $dtag->determine()->process(ConfigStore::get('api.resolve_aliases'));
			}
		}

		return $metadata;
	}
}
