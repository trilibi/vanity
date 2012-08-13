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

/**
 * A collection of utilities designed to assist Reflection parsing.
 */
class Utilities
{
	/**
	 * [methodAccess description]
	 * @param  ReflectionMethod $o [description]
	 * @return [type]              [description]
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
	 * [propertyAccess description]
	 * @param  ReflectionProperty $o [description]
	 * @return [type]                [description]
	 */
	public static function propertyAccess(ReflectionProperty $o)
	{
		$accesses = array();

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
	 * [regex_token description]
	 * @param  [type] $token [description]
	 * @return [type]        [description]
	 */
	public static function makeTokenRegexFriendly($token)
	{
		$token = str_replace('/', '\/', $token);
		$token = quotemeta($token);
		return str_replace('\\\\', '\\', $token);
	}

	/**
	 * [line_numbers description]
	 * @param  [type] $lnum    [description]
	 * @param  [type] $content [description]
	 * @return [type]          [description]
	 */
	public static function padLineNumbers($lnum, $content)
	{
		return str_pad($lnum + 1, strlen((string) sizeof($content)), '0', STR_PAD_LEFT);
	}

	/**
	 * [entitize description]
	 * @param  [type] $s [description]
	 * @return [type]    [description]
	 */
	public static function entitize($s)
	{
		return htmlspecialchars($s, ENT_COMPAT, 'UTF-8');
	}

	/**
	 * [size_readable description]
	 * @param  [type] $size    [description]
	 * @param  [type] $unit    [description]
	 * @param  [type] $default [description]
	 * @return [type]          [description]
	 */
	public static function size($size, $unit = null, $default = null)
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

		// Return string
		if ($default === null)
		{
			$default = '%01.2f %s';
		}

		// Loop
		$i = 0;
		while ($unit != $i && $size >= 1024 && $i < $ii)
		{
			$size /= $mod;
			$i++;
		}

		return sprintf($default, $size, $sizes[$i]);
	}

	/**
	 * [unwrap_array description]
	 * @param  [type] $array [description]
	 * @return [type]        [description]
	 */
	public static function unwrapArray($array)
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
	 * [get_parent_classes description]
	 * @param  [type] $rclass [description]
	 * @return [type]         [description]
	 */
	public static function getParentClasses($rclass)
	{
		$class_list = array();

		while ($parent_class = $rclass->getParentClass())
		{
			$class_list[] = $parent_class->getName();
			$rclass = $parent_class;
		}

		return $class_list;
	}

	/**
	 * [clean_docbook description]
	 * @param  [type] $content [description]
	 * @return [type]          [description]
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
	 * [elongate_type description]
	 * @param  [type] $type [description]
	 * @return [type]       [description]
	 */
	public static function elongateType($type)
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

		return $type;
	}

	/**
	 * [strip_root_element description]
	 * @param  [type] $xml     [description]
	 * @param  string $element [description]
	 * @return [type]          [description]
	 */
	public static function strip_root_element($xml, $element = 'listitem')
	{
		$xml = preg_replace('/^<' . $element . '>/i', '', trim($xml));
		$xml = preg_replace('/<\/' . $element . '>$/i', '', $xml);
		return trim($xml);
	}

	/**
	 * [generate_entity_map description]
	 * @return [type] [description]
	 */
	public static function generate_entity_map()
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
	 * [convert_to_html description]
	 * @param  [type] $path [description]
	 * @return [type]       [description]
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
