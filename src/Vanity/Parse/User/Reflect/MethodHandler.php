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


namespace Vanity\Parse\User\Reflect;

use Reflector;
use ReflectionClass;
use ReflectionException;
use ReflectionMethod;
use phpDocumentor\Reflection\DocBlock;
use Vanity\Config\Store as ConfigStore;
use Vanity\Console\Utilities as ConsoleUtil;
use Vanity\GlobalObject\Logger;
use Vanity\Parse\User\Reflect\AncestryHandler;
use Vanity\Parse\User\Tag;
use Vanity\Parse\User\TagFinder;
use Vanity\System\DependencyCollector;
use Vanity\System\DocumentationInconsistencyCollector as Inconsistency;
use Vanity\System\Store as SystemStore;

/**
 * Handle tags for a method.
 *
 * @author Ryan Parman <http://ryanparman.com>
 * @link   http://vanitydoc.org
 */
class MethodHandler
{
	/**
	 * The {@see ReflectionClass} which represents the class to work with.
	 * @type ReflectionClass
	 */
	protected $class;

	/**
	 * Storage for the methods.
	 * @type array
	 */
	protected $methods;

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
		$this->methods = array();
	}

	/**
	 * Retrieve the properties for the class.
	 *
	 * @return array A list of properties.
	 */
	public function getMethods()
	{
		$rclass_methods = $this->class->getMethods();

		// Add methods and parameters
		$rclass_methods = array_values(array_filter($rclass_methods, function($rmethod)
		{
			return !preg_match(ConfigStore::get('source.exclude.methods'), $rmethod->getName());
		}));

		foreach ($rclass_methods as $rmethod)
		{
			$documentThis = true;

			if (!isset($this->methods['count']))
			{
				$this->methods['count'] = count($rclass_methods);
			}

			if (!isset($this->methods['method']))
			{
				$this->methods['method'] = array();
			}

			$rmethod = InheritdocHandler::resolve($rmethod);
			$_tags = new TagHandler($rmethod->getDocComment(), $this->ancestry);
			$method_docblock = new DocBlock($rmethod->getDocComment());

			$entry = array();
			$entry['name'] = $rmethod->getName();
			$entry['visibility'] = $this->methodAccess($rmethod);

			if ($extension = $rmethod->getExtensionName())
			{
				$entry['extension'] = $extension;
				DependencyCollector::add($extension);
			}

			if ($rmethod->getFileName())
			{
				$entry['path'] = str_replace(VANITY_PROJECT_WORKING_DIR . '/', '', $rmethod->getFileName());
				$entry['lines'] = array(
					'start' => $rmethod->getStartLine(),
					'end'   => $rmethod->getEndLine(),
				);

				if ($viewsource = ConfigStore::get('source.viewsource'))
				{
					$entry['viewsource'] = str_replace(array('%PATH%', '%LINE%'), array($entry['path'], $entry['lines']['start']), $viewsource);
				}
			}

			if ($description = $_tags->getDescription())
			{
				$entry['description'] = $description;
			}

			// Method inheritance
			if (($declaring_class = $rmethod->getDeclaringClass()->getName()) !== $this->class->getName())
			{
				if (!isset($entry['inheritance']))
				{
					$entry['inheritance'] = array();
				}

				if (!isset($entry['inheritance']['class']))
				{
					$entry['inheritance']['class'] = array();
				}

				$declaring_class = new ReflectionClass($declaring_class);

				$subentry = array();
				$subentry['name'] = $declaring_class->getName();
				if ($declaring_class->getFileName())
				{
					$subentry['path'] = str_replace(VANITY_PROJECT_WORKING_DIR . '/', '', $declaring_class->getFileName());
				}

				$entry['inheritance']['class'][] = $subentry;
			}

			// Method tags
			if (count($method_docblock->getTags()))
			{
				if (!isset($entry['metadata']))
				{
					$entry['metadata'] = array();
				}

				if (!isset($entry['metadata']['tag']))
				{
					$entry['metadata']['tag'] = array();
				}

				foreach ($method_docblock->getTags() as $rtag)
				{
					$dtag = new Tag($rtag, $this->ancestry);
					$tagData = $dtag->determine()->process(ConfigStore::get('source.resolve_aliases'));

					if ($tagData['name'] === 'alias')
					{
						SystemStore::add(
							'alias.' . $tagData['entity'],
							$this->class->getName() . '::' . $rmethod->getName()
						);
						$documentThis = false;
					}

					$entry['metadata']['tag'][] = $tagData;
				}
			}

			// Method parameters
			if ($count = count($rmethod->getParameters()))
			{
				if (!isset($entry['parameters']))
				{
					$entry['parameters'] = array();
				}

				if (!isset($entry['parameters']['count']))
				{
					$entry['parameters']['count'] = $count;
				}

				if (!isset($entry['parameters']['parameter']))
				{
					$entry['parameters']['parameter'] = array();
				}

				foreach ($rmethod->getParameters() as $rparameter)
				{
					$tag_finder = new TagFinder($entry);

					$param = array();
					$param['name'] = $rparameter->getName();
					$param['required'] = !$rparameter->isOptional();
					$param['passed_by_reference'] = $rparameter->isPassedByReference();

					if ($rparameter->isDefaultValueAvailable())
					{
						$param['default'] = $rparameter->getDefaultValue();
					}

					// Pull-in from @tags
					if ($_description = $tag_finder->find('description', $param['name']))
					{
						$param['description'] = $_description;
					}

					if ($_type = $tag_finder->find('type', $param['name']))
					{
						$param['type'] = $this->ancestry->resolveNamespace($_type);
					}

					if ($_types = $tag_finder->find('types', $param['name']))
					{
						$param['types'] = $_types;
					}

					// Clean-up parameter metadata tags
					if (isset($entry['metadata']) && isset($entry['metadata']['tag']))
					{
						foreach ($entry['metadata']['tag'] as $index => $tag)
						{
							if ($tag['name'] === 'param' && $tag['variable'] === $param['name'])
							{
								unset($entry['metadata']['tag'][$index]);
							}
						}
					}

					// Type hinting trumps docblock
					if ($rparameter->getClass())
					{
						if (isset($param['type']) &&
						    $param['type'] !== $rparameter->getClass()->getName())
						{
							// @todo: Resolve namespace of declaring class.
							Inconsistency::add($this->class->getName() . '::' . $rmethod->getName() . '($' . $rparameter->getName() . ') [' . $param['type'] . ' => ' . $rparameter->getClass()->getName() . ']');
						}

						$param['type'] = $rparameter->getClass()->getName();

						if (isset($param['types']))
						{
							unset($param['types']);
						}
					}

					$entry['parameters']['parameter'][] = $param;
				}
			}

			// Return value
			$entry['return'] = array('type' => 'void');
			if (isset($entry['metadata']) && isset($entry['metadata']['tag']))
			{
				foreach ($entry['metadata']['tag'] as $index => $tag)
				{
					if ($tag['name'] === 'return')
					{
						$entry['return'] = $tag;
						unset($entry['return']['name']);

						// Clean-up return metadata tags
						unset($entry['metadata']['tag'][$index]);
					}
				}
			}

			if ($documentThis)
			{
				$this->methods['method'][] = $entry;
			}
		}

		return $this->methods;
	}

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
}
