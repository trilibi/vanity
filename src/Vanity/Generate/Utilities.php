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
	 * @param  string $fullName The full classname (including namespace).
	 * @return array            A list of hash containing the names of the namespaces, as well as their matching paths.
	 */
	public static function getBreadcrumbs($fullName)
	{
		$pieces = explode('\\', $fullName);
		$count = count($pieces);
		$output = array();

		foreach ($pieces as $index => $piece)
		{
			$output[] = array(
				'name' => $piece,
				'path' => str_repeat('../', $count - $index - 1),
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
	 * Converts a description node into something HTML-appropriate.
	 *
	 * @param  array  $description The description node to handle.
	 * @return string              The description as it has been understood.
	 */
	public static function descriptionAsHTML(array $description)
	{
		$output = array();

		foreach ($description as $desc)
		{
			// If this is a string, just pass it along.
			if (is_string($desc))
			{
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
						$output[] = '<a href="' . $desc['uri'] . '">' . $desc['description'] . '</a>';
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
