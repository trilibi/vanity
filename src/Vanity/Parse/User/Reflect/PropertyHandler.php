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


namespace Vanity\Parse\User\Reflect;

use Reflector;
use ReflectionClass;
use ReflectionException;
use ReflectionProperty;
use Vanity\Parse\User\Reflect\AncestryHandler;
use Vanity\Parse\Utilities as ParseUtil;
use Vanity\System\Store as SystemStore;

/**
 * Handle tags for a class.
 *
 * @author Ryan Parman <http://ryanparman.com>
 * @link   http://vanitydoc.org
 */
class PropertyHandler
{
	/**
	 * The {@see ReflectionClass} which represents the class to work with.
	 * @type ReflectionClass
	 */
	protected $class;

	/**
	 * Storage for the properties.
	 * @type array
	 */
	protected $properties;

	/**
	 * Storage for ancestry.
	 * @type AncestryHandler
	 */
	public $ancestry;

	/**
	 * Constructs a new instance of this class.
	 *
	 * @param Reflector       $reflector The reflector to work with.
	 * @param AncestryHandler $ancestry  The ancestry data for the class.
	 */
	public function __construct(Reflector $reflector, AncestryHandler $ancestry)
	{
		$this->class = $reflector;
		$this->ancestry = $ancestry;
		$this->properties = array();
	}

	/**
	 * Retrieve the properties for the class.
	 *
	 * @return array A list of properties.
	 */
	public function getProperties()
	{
		$rproperties = $this->class->getProperties();

		foreach ($rproperties as $rproperty)
		{
			$_tags = new TagHandler($rproperty, $this->ancestry);

			if (!isset($this->properties['count']))
			{
				$this->properties['count'] = count($rproperties);
			}

			if (!isset($this->properties['property']))
			{
				$this->properties['property'] = array();
			}

			// Property-specific data
			$entry = array();
			$entry['name'] = $rproperty->getName();
			$entry['visibility'] = $this->access($rproperty);

			// Where are we?
			SystemStore::add('_.current', $this->class->getName() . '::$' . $rproperty->getName());

			if ($description = $_tags->getShortDescription())
			{
				$entry['description'] = $description;
			}

			// Property inheritance
			if (($declaring_class = $rproperty->getDeclaringClass()->getName()) !== $this->class->getName())
			{
				if (!isset($entry['inheritance']))
				{
					$entry['inheritance'] = array();
				}

				if (!isset($entry['inheritance']['class']))
				{
					$entry['inheritance']['class'] = array();
				}

				$declaring_class = $rproperty->getDeclaringClass();

				$subentry = array();
				$subentry['name'] = $declaring_class->getName();
				if ($declaring_class->getFileName())
				{
					$subentry['path'] = str_replace(VANITY_PROJECT_WORKING_DIR . '/', '', $declaring_class->getFileName());
				}

				$entry['inheritance']['class'][] = $subentry;
			}

			// Default value, if accessible
			if ($rproperty->isPublic())
			{
				$rvalue = $rproperty->getValue($this->class);
				$adjusted_rvalue = null;

				switch (strtolower(gettype($rvalue)))
				{
					case 'boolean':
						$adjusted_rvalue = ($rvalue == 1) ? 'true' : 'false';
						break;

					case 'null':
						$adjusted_rvalue = 'null';
						break;

					case 'string':
						$adjusted_rvalue = $rvalue;
						break;

					case 'integer':
						$adjusted_rvalue = (integer) $rvalue;
						break;

					case 'array':
						$adjusted_rvalue = ParseUtil::unwrapArray($rvalue);
						break;
				}

				$entry['initializer'] = array();
				$entry['initializer']['type'] = gettype($rvalue);
				$entry['initializer']['value'] = $adjusted_rvalue;
			}

			// Property tags
			if ($t = $_tags->getTags())
			{
				$entry['metadata'] = $t;
			}

			$this->properties['property'][] = $entry;
		}

		return $this->properties;
	}

	/**
	 * Returns an array of access/visibility data for a property.
	 *
	 * @param  ReflectionProperty $o The property to parse.
	 * @return array               An array of visibilities that apply to this property.
	 */
	public function access(ReflectionProperty $o)
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
}
