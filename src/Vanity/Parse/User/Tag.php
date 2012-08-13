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

namespace Vanity\Parse\User;

use phpDocumentor\Reflection\DocBlock;
use Vanity\Parse\User\TagInterface;

/**
 * Determines the type of "@tag" and hands-off to the appropriate handling class.
 */
class Tag implements TagInterface
{
	/**
	 * The tag to handle.
	 * @var string
	 */
	protected $tag;

	/**
	 * [__construct description]
	 * @param DocBlock\Tag $tag [description]
	 */
	public function __construct(DocBlock\Tag $tag)
	{
		$this->tag = $tag;
	}

	/**
	 * [process description]
	 * @return [type] [description]
	 */
	public function determine()
	{
		switch (strtolower($this->tag->getName()))
		{
			case 'api':
				return new Tag\ApiHandler($this->tag);

			case 'author':
				return new Tag\AuthorHandler($this->tag);

			case 'category':
				return new Tag\CategoryHandler($this->tag);

			case 'copyright':
				return new Tag\CopyrightHandler($this->tag);

			case 'deprecated':
			case 'depreciated':
				return new Tag\DeprecatedHandler($this->tag);

			case 'event':
				return new Tag\EventHandler($this->tag);

			case 'filesource':
				return new Tag\FilesourceHandler($this->tag);

			case 'final':
				return new Tag\FinalHandler($this->tag);

			case 'global':
				return new Tag\GlobalHandler($this->tag);

			case 'ignore':
				return new Tag\IgnoreHandler($this->tag);

			case 'internal':
				return new Tag\InternalHandler($this->tag);

			case 'license':
				return new Tag\LicenseHandler($this->tag);

			case 'link':
				return new Tag\LinkHandler($this->tag);

			case 'package':
			case 'subpackage':
				return new Tag\PackageHandler($this->tag);

			case 'param':
				return new Tag\ParamHandler($this->tag);

			case 'property':
			case 'property-read':
			case 'property-write':
				return new Tag\PropertyHandler($this->tag);

			case 'return':
			case 'returns':
				return new Tag\ReturnHandler($this->tag);

			case 'see':
				return new Tag\SeeHandler($this->tag);

			case 'since':
			case 'available':
				return new Tag\SinceHandler($this->tag);

			case 'throw':
			case 'throws':
				return new Tag\ThrowHandler($this->tag);

			case 'todo':
			case 'fixme':
				return new Tag\TodoHandler($this->tag);

			case 'uses':
			case 'used-by':
				return new Tag\UsesHandler($this->tag);

			case 'var':
				return new Tag\VarHandler($this->tag);

			case 'version':
				return new Tag\VersionHandler($this->tag);

			default:
				return new Tag\DefaultHandler($this->tag);
		}
	}
}
