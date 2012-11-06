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
use Symfony\Component\EventDispatcher\Event;
use Vanity\Config\Store as ConfigStore;
use Vanity\Event\Event\Store as EventStore;
use Vanity\GlobalObject\Dispatcher;
use Vanity\GlobalObject\Logger;
use Vanity\Parse\User\Reflect\AncestryHandler;
use Vanity\Parse\User\TagInterface;
use Vanity\Parse\User\Tag\DefaultHandler;
use Vanity\Parse\User\Tag\ExampleHandler;
use Vanity\Parse\User\Tag\InheritdocHandler;
use Vanity\Parse\User\Tag\InternalHandler;
use Vanity\Parse\User\Tag\LinkHandler;
use Vanity\Parse\User\Tag\SeeHandler;
use Vanity\System\Store as SystemStore;

/**
 * Determines the type of "@tag" and hands-off to the appropriate handling class.
 *
 * @author Ryan Parman <http://ryanparman.com>
 * @link   http://vanitydoc.org
 */
class InlineTag implements TagInterface
{
	/**
	 * The tag to handle.
	 * @type string
	 */
	protected $tag;

	/**
	 * Storage for ancestry.
	 * @type AncestryHandler
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
		SystemStore::add('_.current', preg_replace('/\[@([^\]]*)\]/', ' [@' . $this->tag->getName() . ']', SystemStore::get('_.current')));

		$tag = strtolower($this->tag->getName());

		$this->triggerEvent("vanity.parse.user.description.${tag}.pre", new EventStore(array(
			'tag'      => &$tag,
			'ancestry' => $this->ancestry,
		)));

		switch ($tag)
		{
			case 'example':
				$processed = new ExampleHandler($this->tag, $this->ancestry);
				break;

			case 'inheritdoc':
				$processed = new InheritdocHandler($this->tag, $this->ancestry);
				break;

			case 'internal':
				$processed = new InternalHandler($this->tag, $this->ancestry);
				break;

			case 'link':
				$processed = new LinkHandler($this->tag, $this->ancestry);
				break;

			case 'see':
				$processed = new SeeHandler($this->tag, $this->ancestry);
				break;

			default:
				$processed = new DefaultHandler($this->tag, $this->ancestry);
				break;
		}

		$this->triggerEvent("vanity.parse.user.description.${tag}.post", new EventStore(array(
			'tag'      => &$tag,
			'ancestry' => $this->ancestry,
		)));

		return $processed;
	}

	/**
	 * Triggers an event and logs it to the INFO log.
	 *
	 * @param  string $event       The string identifier for the event.
	 * @param  Event  $eventObject An object that extends the {@see Symfony\Component\EventDispatcher\Event} object.
	 * @return void
	 */
	public function triggerEvent($event, Event $eventObject = null)
	{
		Logger::get()->{ConfigStore::get('api.log.events')}('Triggering event:', array($event));
		Dispatcher::get()->dispatch($event, $eventObject);
	}
}
