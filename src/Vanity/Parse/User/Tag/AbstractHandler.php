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

use dflydev\markdown\MarkdownExtraParser as Markdown;
use phpDocumentor\Reflection\DocBlock\Tag;
use Vanity\Parse\User\Reflect\AncestryHandler;

/**
 * Implementation of the basic constructor pattern for Tag Handlers.
 */
abstract class AbstractHandler
{
	/**
	 * The tag to handle.
	 * @var string
	 */
	public $tag;

	/**
	 * Storage for ancestry.
	 * @var AncestryHandler
	 */
	public $ancestry;

	/**
	 * {@inheritdoc}
	 */
	public function __construct(Tag $tag, AncestryHandler $ancestry)
	{
		$this->tag = $tag;
		$this->ancestry = $ancestry;
	}

	/**
	 * [clean description]
	 * @param  [type] $content [description]
	 * @return [type]          [description]
	 */
	public function clean($content)
	{
		return trim(preg_replace('/\s+/', ' ', $content));
	}
}
