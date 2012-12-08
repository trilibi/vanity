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


namespace Vanity\Generate;

use dflydev\markdown\MarkdownExtraParser as Markdown;
use ReflectionClass;
use stdClass;
use Symfony\Component\Console\Formatter\OutputFormatterStyle;
use Vanity\Config\Store as ConfigStore;

/**
 * A collection of utilities to simplify the generation process.
 *
 * @author Ryan Parman <http://ryanparman.com>
 * @link   http://vanitydoc.org
 */
class Utilities
{
	/**
	 * Find the templates directory for the Template via namespace.
	 *
	 * @param  string $namespace The fully-qualified class name (with namespace) of the template.
	 * @return string            The template directory.
	 */
	public static function findTemplatesFor($namespace)
	{
		$rclass = new ReflectionClass($namespace);

		return dirname($rclass->getFileName()) . '/template';
	}

	/**
	 * Find the static assets directory for the Template via namespace.
	 *
	 * @param  string $namespace The fully-qualified class name (with namespace) of the template.
	 * @return string            The static assets directory.
	 */
	public static function findStaticAssetsFor($namespace)
	{
		$rclass = new ReflectionClass($namespace);

		return dirname($rclass->getFileName()) . '/static';
	}

	/**
	 * Returns a list of hashes containing the names of the namespaces, as well as their matching paths, to be used for
	 * generating breadcrumbs.
	 *
	 * @param  string  $fullName The full classname (including namespace).
	 * @param  integer $offset   The positive/negative offset to apply.
	 * @return array             A list of hash containing the names of the namespaces as well as their matching paths.
	 */
	public static function getBreadcrumbs($fullName, $offset = 0)
	{
		$is_method = (strpos($fullName, '()') !== false);
		$pieces = explode('\\', $fullName);
		$count = count($pieces);
		$output = array();

		foreach ($pieces as $index => $piece)
		{
			$parts = ($count - $index) + $offset;

			if ($parts < 0)
			{
				$path = '';
			}
			elseif ($parts === 0 && $is_method)
			{
				$path = './';
			}
			elseif ($parts === 0 && !$is_method)
			{
				$path = '';
			}
			else
			{
				$path = str_repeat('../', $parts);
			}

			$output[] = array(
				'name' => $piece,
				'path' => $path,
			);
		}

		return $output;
	}

	/**
	 * From a given fully-qualified class name, determine the relative path back to the root of the output.
	 *
	 * @param  string  $fullName The full classname (including namespace).
	 * @param  integer $offset   The positive/negative offset to apply.
	 * @return string            The relative path back to the root of the output.
	 */
	public static function getRelativeBasePath($fullName, $offset = 0)
	{
		$pieces = explode('\\', $fullName);

		return str_repeat('../', count($pieces) + $offset) . '..';
	}

	/**
	 * Return the absolute path for the root of the output.
	 *
	 * @param  string $format_identifier The identifier for the format. Used as the folder name the output is saved to.
	 * @return string                    The absolute path for the root of the output.
	 */
	public static function getAbsoluteBasePath($format_identifier)
	{
		return str_replace('%FORMAT%', $format_identifier, ConfigStore::get('generator.output'));
	}

	/**
	 * From a given fully-qualified class name, determine the path that would be represented by it.
	 *
	 * @param  string $fullName The full classname (including namespace).
	 * @return string           The path that would be represented by the fully-qualified class name.
	 */
	public static function namespaceAsPath($fullName)
	{
		return str_replace('\\', '/', $fullName);
	}

	/**
	 * Get two lists, filtered by native vs. inherited.
	 *
	 * @param  array $list The list of methods from the JSON model.
	 * @return array       An array containing `native` and `inherited` keys. Each of these contains a `count` key
	 *                     and a `methods` key. The `methods` key is a list of methods.
	 */
	public static function getFilteredList(array $list)
	{
		$native = array();
		$inherited = array();

		foreach ($list as $item)
		{
			if (isset($item['inheritance']))
			{
				$inherited[] = $item;
			}
			else
			{
				$native[] = $item;
			}
		}

		return array(
			'native' => array(
				'count'   => count($native),
				'methods' => $native,
			),
			'inherited' => array(
				'count'   => count($inherited),
				'methods' => $inherited,
			),
		);
	}

	/**
	 * Get list filtered by native.
	 *
	 * @param  array $list The list of methods from the JSON model.
	 * @return array       An array containing information about the `native` keys.
	 */
	public static function getNativeFilteredList(array $list)
	{
		$o = self::getFilteredList($list);
		return $o['native'];
	}

	/**
	 * Get list filtered by inherited.
	 *
	 * @param  array $list The list of methods from the JSON model.
	 * @return array       An array containing information about the `inherited` keys.
	 */
	public static function getInheritedFilteredList(array $list)
	{
		$o = self::getFilteredList($list);
		return $o['inherited'];
	}

	/**
	 * Filters node names by letter.
	 *
	 * @param  array $list The list of methods from the JSON model.
	 * @return array       An array containing matching nodes.
	 */
	public static function getListByLetter(array $list)
	{
		$output = array();

		foreach ($list as $item)
		{
			$letter = strtoupper(substr($item['name'], 0, 1));

			if (preg_match('/^[^a-z]/i', $letter))
			{
				$letter = '#';
			}

			if (!isset($output[$letter]))
			{
				$output[$letter] = array(
					'letter' => $letter,
					'nodes'  => array(),
				);
			}

			$output[$letter]['nodes'][] = $item;
		}

		return $output;
	}

	/**
	 * Return a list of simple parameter names.
	 *
	 * @param  array $list The list of methods from the JSON model.
	 * @return array       An array containing matching nodes.
	 */
	public static function getNames(array $list)
	{
		$output = array();

		foreach ($list as $item)
		{
			$output[] = $item['name'];
		}

		return $output;
	}

	/**
	 * Apply Markdown to a string.
	 * @param  string $string A Markdown-formatted string.
	 * @return string         An HTML representation of the Markdown-formatted string.
	 */
	public static function markdown($string)
	{
		$md = new Markdown();
		return $md->transformMarkdown($string);
	}

	/**
	 * Find a specific metadata tag in a list of metadata tags.
	 *
	 * @param  array   $list    The list of methods from the JSON model.
	 * @param  string  $tagName The name of the tag to match.
	 * @param  boolean $exists  Only return a boolean value about whether or not there was a match.
	 * @return array            Matching nodes in the list.
	 */
	public static function findTag($list, $tagName, $exists = false)
	{
		if (is_array($list))
		{
			$results = array_filter($list, function($item) use ($tagName)
			{
				if (isset($item['name']) && strtolower($item['name']) === strtolower($tagName))
				{
					return $item;
				}
			});

			if ($exists)
			{
				return (count($results) > 0);
			}

			return $results;
		}
	}

	/**
	 * Convert author metadata into a hyperlinked, text list.
	 *
	 * @param  array  $authors An array of author metadata.
	 * @return string          A hyperlinked, text list.
	 */
	public static function authorsAsLinks(array $authors)
	{
		$a = array();

		foreach ($authors as $author)
		{
			switch ($author['uri_hint'])
			{
				case 'mail':
					$a[] = sprintf('<a href="mailto:%s">%s</a>', $author['uri'], $author['author']);
					break;

				case 'url':
					$a[] = sprintf('<a href="%s">%s</a>', $author['uri'], $author['author']);
					break;

				case 'service':
					$a[] = sprintf('<a href="%s" title="%s">%s</a>', $author['uri'], $author['description'], $author['author']);
					break;

				default:
					$a[] = $author['author'];
					break;
			}
		}

		$last = null;
		if (count($a) > 1)
		{
			$last = array_pop($a);
		}

		$rest = implode(', ', $a);

		if ($last)
		{
			$rest .= ' and ' . $last;
		}

		return $rest;
	}

	/**
	 * Convert license metadata into hyperlinked text.
	 *
	 * @param  array  $license An array of license metadata.
	 * @return string          Hyperlinked text.
	 */
	public static function licenseAsLink(array $license)
	{
		if (isset($license['uri']) && isset($license['description']))
		{
			return sprintf('<a href="%s">%s</a>', $license['uri'], $license['description']);
		}
	}

	/**
	 * Converts a description node into something HTML-appropriate.
	 *
	 * @param  array  $description The description node to handle.
	 * @return string              The description as it has been understood.
	 */
	public static function descriptionAsHTML(array $description)
	{
		$output = array();

		foreach ($description as $index => $desc)
		{
			// If this is a string, just pass it along.
			if (is_string($desc))
			{
				if ($index === 0)
				{
					$desc .= ' ';
				}

				$output[] = $desc;
			}

			// What kind of data is this?
			elseif (is_array($desc))
			{
				switch (strtolower($desc['name']))
				{
					case 'example':
						// @todo: Support this!
						break;

					case 'internal':
						// Ignore
						break;

					case 'link':
						if (isset($desc['description']))
						{
							$output[] = '<a href="' . $desc['uri'] . '">' . $desc['description'] . '</a>';
						}
						else
						{
							$output[] = '<a href="' . $desc['uri'] . '">' . $desc['uri'] . '</a>';
						}
						break;

					case 'see':
						switch ($desc['entity_hint'])
						{
							case 'class':
								$output[] = '<a href="' .
									self::getRelativeBasePath($desc['entity'], -1) .
									'/api-reference/' .
									self::namespaceAsPath($desc['entity']) .
									'/index.html' .
									'">' . $desc['entity'] . '</a>';
								break;

							case 'method':
								list($__class, $__method) = explode('::', $desc['entity']);
								$output[] = '<a href="' .
									self::getRelativeBasePath($__class) .
									'/api-reference/' .
									self::namespaceAsPath($__class) .
									'/' . str_replace('()', '', $__method) . '.html' .
									'">' . $desc['entity'] . '</a>';
								break;

							case 'property':
								list($__class, $__property) = explode('::$', $desc['entity']);
								$output[] = '<a href="' .
									self::getRelativeBasePath($__class) .
									'/api-reference/' .
									self::namespaceAsPath($__class) .
									'/properties.html' .
									'">' . $desc['entity'] . '</a>';
								break;
						}
						break;
				}
			}
		}

		$md = new Markdown();
		return $md->transformMarkdown(implode('', $output));
	}
}
