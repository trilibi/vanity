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


namespace Vanity\Parse\User;

use phpDocumentor\Reflection\DocBlock\Tag as DBTag;
use Vanity\Parse\User\Reflect\AncestryHandler;
use Vanity\Parse\User\TagInterface;
use Vanity\Parse\User\Tag\ApiHandler;
use Vanity\Parse\User\Tag\AuthorHandler;
use Vanity\Parse\User\Tag\CategoryHandler;
use Vanity\Parse\User\Tag\CopyrightHandler;
use Vanity\Parse\User\Tag\DefaultHandler;
use Vanity\Parse\User\Tag\DeprecatedHandler;
use Vanity\Parse\User\Tag\EventHandler;
use Vanity\Parse\User\Tag\FilesourceHandler;
use Vanity\Parse\User\Tag\FinalHandler;
use Vanity\Parse\User\Tag\GlobalHandler;
use Vanity\Parse\User\Tag\IgnoreHandler;
use Vanity\Parse\User\Tag\InternalHandler;
use Vanity\Parse\User\Tag\LicenseHandler;
use Vanity\Parse\User\Tag\LinkHandler;
use Vanity\Parse\User\Tag\PackageHandler;
use Vanity\Parse\User\Tag\ParamHandler;
use Vanity\Parse\User\Tag\PropertyHandler;
use Vanity\Parse\User\Tag\ReturnHandler;
use Vanity\Parse\User\Tag\SeeHandler;
use Vanity\Parse\User\Tag\SinceHandler;
use Vanity\Parse\User\Tag\ThrowHandler;
use Vanity\Parse\User\Tag\TodoHandler;
use Vanity\Parse\User\Tag\UsesHandler;
use Vanity\Parse\User\Tag\VarHandler;
use Vanity\Parse\User\Tag\VersionHandler;
use Vanity\System\Store as SystemStore;

/**
 * Determines the type of "@tag" and hands-off to the appropriate handling class.
 *
 * @author Ryan Parman <http://ryanparman.com>
 * @link   http://vanitydoc.org
 */
class Tag implements TagInterface
{
	/**
	 * The tag to handle.
	 * @var string
	 */
	protected $tag;

	/**
	 * Storage for ancestry.
	 * @var AncestryHandler
	 */
	public $ancestry;

	/**
	 * {@inheritdoc}
	 */
	public function __construct(DBTag $tag, AncestryHandler $ancestry)
	{
		$this->tag = $tag;
		$this->ancestry = $ancestry;
	}

	/**
	 * {@inheritdoc}
	 */
	public function determine()
	{
		// Where are we?
		SystemStore::add('_.current', SystemStore::get('_.current') . ' [@' . $this->tag->getName() . ']');

		switch (strtolower($this->tag->getName()))
		{
			case 'api':
				return new ApiHandler($this->tag, $this->ancestry);

			case 'author':
				return new AuthorHandler($this->tag, $this->ancestry);

			case 'copyright':
				return new CopyrightHandler($this->tag, $this->ancestry);

			case 'deprecated':
			case 'depreciated':
				return new DeprecatedHandler($this->tag, $this->ancestry);

			case 'dispatches':
			case 'event':
				return new EventHandler($this->tag, $this->ancestry);

			case 'global':
				return new GlobalHandler($this->tag, $this->ancestry);

			case 'internal':
				return new InternalHandler($this->tag, $this->ancestry);

			case 'license':
				return new LicenseHandler($this->tag, $this->ancestry);

			case 'link':
				return new LinkHandler($this->tag, $this->ancestry);

			case 'package':
				return new PackageHandler($this->tag, $this->ancestry);

			case 'param':
				return new ParamHandler($this->tag, $this->ancestry);

			case 'property':
			case 'property-read':
			case 'property-write':
				return new PropertyHandler($this->tag, $this->ancestry);

			case 'return':
			case 'returns':
				return new ReturnHandler($this->tag, $this->ancestry);

			case 'alias':
			case 'see':
			case 'uses':
			case 'used-by':
				return new SeeHandler($this->tag, $this->ancestry);

			case 'since':
			case 'available':
				return new SinceHandler($this->tag, $this->ancestry);

			case 'throw':
			case 'throws':
				return new ThrowHandler($this->tag, $this->ancestry);

			case 'todo':
			case 'fixme':
				return new TodoHandler($this->tag, $this->ancestry);

			case 'type':
			case 'var':
				return new VarHandler($this->tag, $this->ancestry);

			case 'version':
				return new VersionHandler($this->tag, $this->ancestry);

			default:
				return new DefaultHandler($this->tag, $this->ancestry);
		}
	}
}
