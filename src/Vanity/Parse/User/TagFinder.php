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

/**
 * Looks up the value of a given metadata @tag.
 *
 * @author Ryan Parman <http://ryanparman.com>
 * @link   http://vanitydoc.org
 */
class TagFinder
{
	/**
	 * The entry array which contains a `metadata` key.
	 * @type array
	 */
	protected $entry;

	/**
	 * Constructs a new instance of this class.
	 *
	 * @param array &$entry The entry array which contains a `metadata` key.
	 */
	public function __construct(array &$entry)
	{
		$this->entry = $entry;
	}

	/**
	 * Retrieves the value of the requested metadata @tag.
	 *
	 * @param  string $data The name of the node to get from the @tag entry.
	 * @param  string $name The `name` element of the @tag data.
	 * @param  string $tag  The metadata tag to look inside (e.g., param).
	 * @return string       The value of the requested node.
	 */
	public function find($data, $name, $tag = 'param')
	{
		if (isset($this->entry['metadata']))
		{
			if (isset($this->entry['metadata']['tag']))
			{
				$tags = array_values(array_filter($this->entry['metadata']['tag'], function($t) use ($tag, $name)
				{
					return (
						isset($t['name']) &&
						$t['name'] === $tag &&
						$t['variable'] === $name
					);
				}));

				if (count($tags) && isset($tags[0][$data]))
				{
					return $tags[0][$data];
				}
			}
		}

		return null;
	}

	/**
	 * Deletes a reference to the requested metadata @tag.
	 *
	 * @param  string  $name The `name` element of the @tag data.
	 * @param  string  $tag  The metadata tag to look inside (e.g., param).
	 * @return boolean       Whether or not the delete was successful. A value of `true` indicates that the value was
	 *                       successfully deleted, or otherwise does not exist. A value of `false` indicates that the
	 *                       deletion was unsuccessful.
	 */
	public function delete($name, $tag = 'param')
	{
		if (isset($this->entry['metadata']))
		{
			if (isset($this->entry['metadata']['tag']))
			{
				foreach ($this->entry['metadata']['tag'] as $index => $t)
				{
					if (isset($t['name']) &&
					    $t['name'] === $tag &&
					    $t['variable'] === $name)
					{
						unset($this->entry['metadata']['tag'][$index]);
						return !isset($this->entry['metadata']['tag'][$index]);
					}
				}
			}
		}

		return true;
	}
}
