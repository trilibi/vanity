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

use Vanity\Console\Utilities as ConsoleUtil;
use Vanity\Parse\User\Tag\HandlerInterface;
use Vanity\Parse\User\Tag\AbstractNameTypeDescription;
use Vanity\System\DocumentationInconsistencyCollector as Inconsistency;
use Vanity\System\Store as SystemStore;

/**
 * The handler for @see tags.
 */
class SeeHandler extends AbstractNameTypeDescription implements HandlerInterface
{
	/**
	 * {@inheritdoc}
	 */
	public function process($elongate = false)
	{
		$return = parent::process(true);

		if (isset($return['type']))
		{
			$return['entity'] = $return['type'];
			unset($return['type']);
		}

		// Property
		if (preg_match('/(\w+::)?\$\w+/', $return['entity']))
		{
			$return['entity_hint'] = 'property';
		}

		// Method
		elseif (preg_match('/(\w+::)?[\w_]+(\(\))/', $return['entity']))
		{
			$return['entity_hint'] = 'method';
		}

		// Method
		elseif (preg_match('/[\w_]+/', $return['entity']))
		{
			$return['entity_hint'] = 'class';
		}

		// URL
		elseif (preg_match('/https?:/', $return['entity']))
		{
			// Used @see when @link was more appropriate
			$formatter = ConsoleUtil::formatters();
			Inconsistency::add('Used @see when @link was more appropriate. => ' . $formatter->gold->apply(SystemStore::get('_.current')));

			$return['entity_hint'] = 'uri';
		}

		// Do we need to resolve?
		if (strpos($return['entity'], '::') !== false)
		{
			list($class, $entity) = explode('::', $return['entity']);
			$class = $this->ancestry->resolveNamespace($class);
			$return['entity'] = implode('::', array($class, $entity));
		}
		elseif (
			$return['entity_hint'] === 'method' ||
			$return['entity_hint'] === 'property'
		)
		{
			$class = $this->ancestry->getClass();
			$return['entity'] = implode('::', array($class, $return['entity']));
		}

		return $return;
	}
}
