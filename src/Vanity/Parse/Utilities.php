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

namespace Vanity\Parse;

use ReflectionClass;
use ReflectionMethod;
use ReflectionProperty;
use Vanity\Parse\User\Reflect\AncestryHandler;
use Vanity\System\Backtrace;
use Vanity\System\Store as SystemStore;

/**
 * A collection of utilities designed to assist Reflection parsing.
 *
 * @author Ryan Parman <http://ryanparman.com>
 * @link   http://vanitydoc.org
 */
class Utilities
{
	/**
	 * Returns an array of access/visibility data for a method.
	 *
	 * @param  ReflectionMethod $o The method to parse.
	 * @return array               An array of visibilities that apply to this method.
	 */
	public static function methodAccess(ReflectionMethod $o)
	{
		$accesses = array();

		if (method_exists($o, 'isFinal'))
		{
			if ($o->isFinal()) $accesses[] = 'final';
		}
		if (method_exists($o, 'isAbstract'))
		{
			if ($o->isAbstract()) $accesses[] = 'abstract';
		}
		if (method_exists($o, 'isPrivate'))
		{
			if ($o->isPrivate()) $accesses[] = 'private';
		}
		if (method_exists($o, 'isProtected'))
		{
			if ($o->isProtected()) $accesses[] = 'protected';
		}
		if (method_exists($o, 'isPublic'))
		{
			if ($o->isPublic()) $accesses[] = 'public';
		}
		if (method_exists($o, 'isStatic'))
		{
			if ($o->isStatic()) $accesses[] = 'static';
		}

		return $accesses;
	}

	/**
	 * Makes a string regex-ready.
	 *
	 * @param  string $token The string to make regex-ready.
	 * @return string        A regex-ready string.
	 */
	public static function makeTokenRegexFriendly($token)
	{
		$token = str_replace('/', '\/', $token);
		$token = quotemeta($token);
		return str_replace('\\\\', '\\', $token);
	}

	/**
	 * Pads line numbers based on the length of the largest value.
	 *
	 * @param  integer $lnum    The line number to process, generally an array index.
	 * @param  array   $content An array of lines.
	 * @return string           A zero-padded number (e.g., "007" for an array of 100+ lines).
	 */
	public static function padLineNumbers($lnum, array $content)
	{
		return str_pad($lnum + 1, strlen((string) count($content)), '0', STR_PAD_LEFT);
	}

	/**
	 * Convert (UTF-8) special characters into entities.
	 *
	 * @param  string $s A string containing special characters.
	 * @return string    A string with all special characters converted into entities.
	 */
	public static function entitize($s)
	{
		return htmlspecialchars($s, ENT_COMPAT, 'UTF-8');
	}

	/**
	 * Get the human-formatted file size.
	 *
	 * @param  integer $size    The number of bytes to work with.
	 * @param  string  $unit    A unit of measurement to lock to. [Allowed values: `B`, `kB`, `MB`, `GB`, `TB`, `PB`]
	 * @param  string  $format  The format to use. Will be passed to {@see php:sprintf()}.
	 * @return string           The human-formatted file size.
	 */
	public static function size($size, $unit = null, $format = '%01.2f %s')
	{
		// Units
		$sizes = array('B', 'kB', 'MB', 'GB', 'TB', 'PB');
		$mod = 1024;
		$ii = count($sizes) - 1;

		// Max unit
		$unit = array_search((string) $unit, $sizes);
		if ($unit === null || $unit === false)
		{
			$unit = $ii;
		}

		// Loop
		$i = 0;
		while ($unit != $i && $size >= 1024 && $i < $ii)
		{
			$size /= $mod;
			$i++;
		}

		return sprintf($format, $size, $sizes[$i]);
	}

	/**
	 * Produces a string representation of the contents of an array.
	 *
	 * @param  array  $array The array to "unwrap".
	 * @return string        A string representation of the contents of an array.
	 */
	public static function unwrapArray(array $array)
	{
		$out = 'array(';
		$collect = array();
		foreach ($array as $k => $v)
		{
			$key = '';
			if (!is_int($k))
			{
				$key = '"' . $k . '" => ';
			}

			switch (gettype($v))
			{
				case 'integer':
					$collect[] = $key . $v;
					break;

				case 'string':
					$collect[] = $key . '"' . $v . '"';
					break;

				case 'array':
					$collect[] = $key . Util::unwrap_array($v);
					break;

				case 'object':
					$collect[] = $key . get_class($v);
					break;

				default:
					$collect[] = $key . gettype($v);
			}
		}

		$values = implode(', ', $collect);

		$out .= $values ? ' ' : '';
		$out .= $values;
		$out .= $values ? ' ' : '';
		$out .= ')';

		return $out;
	}

	/**
	 * Replaces all known DocBook tags with HTML equivalents.
	 *
	 * @param  string $content The content to parse.
	 * @return string          The content with all DocBook tags replaced.
	 */
	public static function cleanDocBook($content)
	{
		$content = preg_replace('/(\s+)/m', ' ', $content);
		$content = preg_replace('/\s?<(\/?)(para)([^>]*)>\s?/i', '<\\1p\\3>', $content);
		$content = preg_replace('/<(\/?)(literal)([^>]*)>/i', '<\\1code\\3>', $content);
		$content = preg_replace('/<(\/?)(orderedlist)([^>]*)>/i', '<\\1ol\\3>', $content);
		$content = preg_replace('/<(\/?)(itemizedlist)([^>]*)>/i', '<\\1ul\\3>', $content);
		$content = preg_replace('/<(\/?)(listitem)([^>]*)>/i', '<\\1li\\3>', $content);
		$content = preg_replace('/<constant([^>]*)>(\w*)<\/constant>/i', '<code>\\2</code>', $content);
		$content = preg_replace('/<type([^>]*)>(\w*)<\/type>/i', '<a href="http://php.net/\\2"><code>\\2</code></a>', $content);
		$content = preg_replace('/<classname([^>]*)>(\w*)<\/classname>/i', '<a href="http://php.net/\\2"><code>\\2</code></a>', $content);
		$content = preg_replace('/<methodname([^>]*)>(\w*)::(\w*)<\/methodname>/i', '<a href="http://php.net/\\2.\\3"><code>\\2::\\3</code></a>', $content);
		$content = preg_replace('/<link linkend="([^"]*)">([^>]*)<\/link>/i', '<a href="http://php.net/\\1"><code>\\2</code></a>', $content);

		$content = str_replace('<pmeter>', ' <code>', $content);
		$content = str_replace('</pmeter>', '</code> ', $content);
		$content = str_replace('<row>', '<tr>', $content);
		$content = str_replace('</row>', '</tr>', $content);
		$content = str_replace('<entry>', '<td>', $content);
		$content = str_replace('</entry>', '</td>', $content);

		return trim($content);
	}

	/**
	 * Converts short-form native types to long-form native types.
	 * Also resolves namespace aliases with a provided alias mapping.
	 *
	 * @param  string          $type      The name of the type.
	 * @param  AncestryHandler $ancestry  The ancestry data for the class.
	 * @return string                     The long-form version of the type.
	 */
	public static function elongateType($type, AncestryHandler $ancestry)
	{
		$types = array(
			'bool' => 'boolean',
			'int'  => 'integer',
			'str'  => 'string',
		);

		if (isset($types[strtolower($type)]))
		{
			return $types[strtolower($type)];
		}

		return $ancestry->resolveNamespace($type);
	}

	/**
	 * Strips the root element of an XML string.
	 *
	 * @param  string $xml     A string of XML.
	 * @param  string $element The name of the root element.
	 * @return string          A string of XML with the root element stripped.
	 */
	public static function stripRootElement($xml, $element = 'listitem')
	{
		$xml = preg_replace('/^<' . $element . '>/i', '', trim($xml));
		$xml = preg_replace('/<\/' . $element . '>$/i', '', $xml);
		return trim($xml);
	}

	/**
	 * Generates an entity map for use with PHP.net documentation.
	 *
	 * @return array The entity map.
	 *
	 * @todo Rewrite this method.
	 */
	public static function generateEntityMap()
	{
		$master_map = array();
		$glob = array_merge(
			Util::rglob(ENTITY_GLOBAL_DIR . '**.ent'),
			Util::rglob(ENTITY_LANG_DIR . '**.ent')
		);

		foreach ($glob as $file)
		{
			$entities = file_get_contents($file);
			preg_match_all('/<!ENTITY\s+([^\s]*)\s+("|\')([^\\2]*)\\2\s*>/Ui', $entities, $m);

			for ($i = 0, $max = count($m[0]); $i < $max; $i++)
			{
				$v = str_replace(array("\r\n", "\n"), ' ', $m[3][$i]);
				$map[$m[1][$i]] = $v;
			}

			$master_map = array_merge($master_map, $map);
		}

		ksort($master_map);
		return $master_map;
	}

	/**
	 * Converts the templates into HTML documents based on the file type.
	 *
	 * @param  string $path The filename of the template.
	 * @return string       The converted HTML content from that template.
	 *
	 * @todo Rewrite this method.
	 */
	public static function convert_to_html($path)
	{
		$pathinfo = pathinfo($path);
		$extension = strtolower($pathinfo['extension']);

		switch ($extension)
		{
			// Markdown
			case 'md':
			case 'mdown':
			case 'markdown':
				return trim(SmartyPants(Markdown(file_get_contents($path))));
				break;

			// PHP-infused HTML
			case 'phtml':
				Generator::start();
				include $path;
				$phtml_content = Generator::end();
				return SmartyPants(trim($phtml_content));
				break;

			// Pre-formatted text
			case '':
			case 'txt':
			case 'text':
				return '<pre>' . trim(file_get_contents($path)) . '</pre>';
				break;

			// Plain ol' HTML
			default:
				return trim(SmartyPants(file_get_contents($path)));
				break;
		}
	}
}
