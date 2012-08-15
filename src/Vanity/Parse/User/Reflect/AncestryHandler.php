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
use TokenReflection\Broker;
use Vanity\Console\Utilities as ConsoleUtil;
use Vanity\System\DocumentationInconsistencyCollector as Inconsistency;
use Vanity\System\Store as SystemStore;

/**
 * Handle resolving the ancestry and namespaces for a class.
 *
 * @author Ryan Parman <http://ryanparman.com>
 * @link   http://vanitydoc.org
 */
class AncestryHandler
{
	/**
	 * Storage for all of the Namespace/Alias mappings.
	 * @var array
	 */
	protected $aliases;

	/**
	 * The {@see Broker} for handling tokenized reflection.
	 * @var Broker
	 */
	protected $broker;

	/**
	 * The {@see ReflectionClass} which represents the class to work with.
	 * @var ReflectionClass
	 */
	protected $class;

	/**
	 * Discovers the interfaces that this class implements.
	 * @var array
	 */
	protected $implements;

	/**
	 * Discovers the classes that this class inherits from.
	 * @var array
	 */
	protected $inherits;

	/**
	 * Constructs a new instance of this class.
	 *
	 * @param Reflector $reflector The reflector to work with.
	 */
	public function __construct(Reflector $reflector)
	{
		$this->aliases = array();
		$this->broker = new Broker(new Broker\Backend\Memory());
		$this->class = $reflector;
		$this->implements = array();
		$this->inherits = array();
	}

	/**
	 * Get a list of namespaces.
	 *
	 * @return array A list of namespaces.
	 */
	public function getNamespaces()
	{
		$aliases = array();
		$class_paths = array();
		$interface_paths = array();

		// Get classes with paths
		if (isset($this->inherits['class']) && count($this->inherits['class']))
		{
			$class_paths = array_values(
				array_filter($this->inherits['class'],
					function($class)
					{
						return isset($class['path']);
					}
				)
			);
		}

		// Get interfaces with paths
		if (isset($this->implements['interface']) && count($this->implements['interface']))
		{
			$interface_paths = array_values(
				array_filter($this->implements['interface'],
					function($interface)
					{
						return isset($interface['path']);
					}
				)
			);
		}

		// Produce a singular list
		while (count($interface_paths))
		{
			array_push($class_paths, array_shift($interface_paths));
		}
		array_push($class_paths, array('path' => $this->class->getFileName()));

		// Go through each file and get a mapping of aliases to namespaces
		foreach ($class_paths as $class_path)
		{
			$tokenized_file = $this->broker->processFile($class_path['path'], true);
			$aliases = array_merge($aliases,
				array_map(function($namespace)
				{
					return $namespace->getNamespaceAliases();
				},
				$tokenized_file->getNamespaces())
			);
		}

		// Flatten the list
		foreach ($aliases as $alias)
		{
			$this->aliases = array_merge($this->aliases, $alias);
		}

		// Include native class types
		$classes = SystemStore::get('_.classes');
		$classes = array_merge($classes, array(
			// http://php.net/types
			'boolean',
			'integer',
			'float',
			'double',
			'string',
			'array',
			'object',
			'resource',
			'NULL',
			'null',
			'callable',
			'mixed',
			'number',
			'callback',
		));
		$native_classmap = array_combine($classes, $classes);
		$this->aliases = array_merge($this->aliases, $native_classmap);

		// Sort the list
		ksort($this->aliases);

		return $this->aliases;
	}

	/**
	 * Gets the list of interfaces that this class implements.
	 *
	 * @return array A list of name/path pairs for implemented interfaces.
	 */
	public function getImplementations()
	{
		$rclass_interfaces = $this->class->getInterfaces();

		foreach ($rclass_interfaces as $interface)
		{
			$entry = array();
			$entry['name'] = $interface->getName();

			if (!isset($this->implements['count']))
			{
				$this->implements['count'] = count($rclass_interfaces);
			}

			if (!isset($this->implements['interface']))
			{
				$this->implements['interface'] = array();
			}

			if ($interface->getFileName())
			{
				$entry['path'] = str_replace(VANITY_PROJECT_WORKING_DIR . '/', '', $interface->getFileName());
			}

			$this->implements['interface'][] = $entry;
		}

		return $this->implements;
	}

	/**
	 * Gets the inheritance chain for the class.
	 *
	 * @return array A list of name/path pairs for inherited classes.
	 */
	public function getInheritance()
	{
		foreach ($this->getParentClasses() as $parent)
		{
			$parent_class = new ReflectionClass($parent);
			$entry = array();
			$entry['name'] = $parent;

			if (!isset($this->inherits['count']))
			{
				$this->inherits['count'] = count($this->getParentClasses());
			}

			if (!isset($this->inherits['class']))
			{
				$this->inherits['class'] = array();
			}

			if ($parent_class->getFileName())
			{
				$entry['path'] = str_replace(VANITY_PROJECT_WORKING_DIR . '/', '', $parent_class->getFileName());
			}

			$this->inherits['class'][] = $entry;
		}

		return $this->inherits;
	}

	/**
	 * Collect a list of all parent classes.
	 *
	 * @return array A list of all parent names as strings.
	 */
	protected function getParentClasses()
	{
		$class_list = array();
		$rclass = $this->class;

		while ($parent_class = $rclass->getParentClass())
		{
			$class_list[] = $parent_class->getName();
			$rclass = $parent_class;
		}

		return $class_list;
	}

	/**
	 * Resolves a namespace alias into a fully-qualified namespace.
	 *
	 * @param  string $short A shortened namespace alias.
	 * @return string        The fully-qualified namespace, if available.
	 */
	public function resolveNamespace($short)
	{
		if (isset($this->aliases[$short]))
		{
			return $this->aliases[$short];
		}
		else
		{
			try
			{
				$namespace = $this->class->getNamespaceName() . '\\' . $short;
				new ReflectionClass($namespace);

				// If we didn't throw an exception, we're good.
				return $namespace;
			}
			catch (ReflectionException $e)
			{
				try
				{
					$class = preg_replace('/^\\\/', '', $short);
					new ReflectionClass($class);

					// If we didn't throw an exception, we're good.
					return $class;
				}
				catch (ReflectionException $e)
				{
					$formatter = ConsoleUtil::formatters();
					Inconsistency::add($class . $formatter->gold->apply(' => ' . SystemStore::get('_.current')));

					// No match. Return it as-is (without any starting backslash).
					return $class;
				}
			}
		}
	}
}
