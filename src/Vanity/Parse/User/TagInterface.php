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


namespace Vanity\Parse\User;

use phpDocumentor\Reflection\DocBlock\Tag as DBTag;
use Vanity\Parse\User\Reflect\AncestryHandler;
use Vanity\Parse\User\Tag\AbstractHandler;

/**
 * The interface for the tag handlers.
 *
 * @author Ryan Parman <http://ryanparman.com>
 * @link   http://vanitydoc.org
 */
interface TagInterface
{
	/**
	 * Constructs a new instance of this class.
	 *
	 * @param Tag             $tag       The parsed DocBlock tag to handle.
	 * @param AncestryHandler $ancestry  The ancestry data for the class.
	 */
	public function __construct(DBTag $tag, AncestryHandler $ancestry);

	/**
	 * Determines which type of tag should be passed to which sub-parser.
	 *
	 * @event  EventStore      vanity.parse.user.reflect.pre
	 * @event  EventStore      vanity.parse.user.reflect.post
	 * @event  EventStore      vanity.parse.user.tag.{tag}.pre
	 * @event  EventStore      vanity.parse.user.tag.{tag}.post
	 * @event  EventStore      vanity.parse.user.description.{inlineTag}.pre
	 * @event  EventStore      vanity.parse.user.description.{inlineTag}.post
	 * @return AbstractHandler An object that extends from {@see AbstractHandler}.
	 */
	public function determine();
}
