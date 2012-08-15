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

namespace Vanity\Parse\User;

use ReflectionClass;
use ReflectionProperty;
use stdClass;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Filesystem;
use Vanity\Config\Store as ConfigStore;
use Vanity\Console\Utilities as ConsoleUtil;
use Vanity\Parse\User\Reflect\AncestryHandler;
use Vanity\Parse\User\Reflect\ConstantHandler;
use Vanity\Parse\User\Reflect\PropertyHandler;
use Vanity\Parse\User\Reflect\TagHandler;
use Vanity\Parse\User\TagFinder;
use Vanity\Parse\Utilities as ParseUtil;
use Vanity\System\DependencyCollector;
use Vanity\System\DocumentationInconsistencyCollector as Inconsistency;
use Vanity\System\Store as SystemStore;

/**
 * Handle the job of reflecting over a single file.
 *
 * @author Ryan Parman <http://ryanparman.com>
 * @link   http://vanitydoc.org
 */
class Reflect
{
	/**
	 * The class name to reflect.
	 * @var string
	 */
	public $class_name;

	/**
	 * Stores a copy of the reflected class.
	 * @var ReflectionClass
	 */
	public $rclass;

	/**
	 * Stores a copy of the class constants.
	 * @var array
	 */
	public $constants;

	/**
	 * Stores a copy of the class properties.
	 * @var array
	 */
	public $properties;

	/**
	 * Stores a copy of the class tags.
	 * @var array
	 */
	public $class_tags;

	/**
	 * The reflected data to store.
	 * @var array
	 */
	public $data;

	/**
	 * Storage for class ancestry data.
	 * @var AncestryHandler
	 */
	public $ancestry;

	/**
	 * Storage for mapping of aliases to namespaces.
	 * @var array
	 */
	public $aliases;

	/**
	 * Storage for text formatters.
	 * @var stdClass
	 */
	public $formatter;

	/**
	 * Constructs a new instance of this class.
	 *
	 * @param string $class The class name to reflect.
	 */
	public function __construct($class)
	{
		$this->class_name  = $class;
		$this->rclass      = new ReflectionClass($class);
		$this->ancestry    = new AncestryHandler($this->rclass);

		$this->inheritance = $this->ancestry->getInheritance();
		$this->implements  = $this->ancestry->getImplementations();
		$this->aliases     = $this->ancestry->getNamespaces();

		$this->constants   = new ConstantHandler($this->rclass, $this->ancestry);
		$this->properties  = new PropertyHandler($this->rclass, $this->ancestry);
		$this->class_tags  = new TagHandler($this->rclass, $this->ancestry);

		$this->formatter   = ConsoleUtil::formatters();
	}

	/**
	 * Reflects over the given class and produces an associative array
	 * containing all relevant class data.
	 *
	 * @return array An associative array containing all relevant class data.
	 *
	 * @todo Extract method handling into a separate class.
	 * @todo Extract parameter handling into a separate class.
	 * @todo Provide an option to toggle alias resolution (e.g., api.resolve.aliases)
	 * @todo Add support for @method
	 * @todo Add support for {@see}
	 * @todo Add support for {@inheritdoc}
	 * @todo Add support for @example
	 * @todo Add support for {@example}
	 * @todo Handle true vs. "true" config passing.
	 */
	public function process()
	{
		// REFLECT ALL THE THINGS!
		$rclass_methods = $this->rclass->getMethods();
		sort($rclass_methods);

		$long_filename = $this->rclass->getFileName();
		$short_filename = str_replace(VANITY_PROJECT_WORKING_DIR . '/', '', $long_filename);

		// $this->data['aliases'] = $this->aliases;

		$this->data['name'] = $this->rclass->getShortName();
		$this->data['namespace'] = $this->rclass->getNamespaceName();
		$this->data['full_name'] = $this->class_name;
		$this->data['path'] = $short_filename;

		SystemStore::add('_.current', $this->class_name);

		#--------------------------------------------------------------------------#

		// Add short descriptions
		if ($short_description = $this->class_tags->getShortDescription())
		{
			$this->data['short_description'] = $short_description;
		}

		// Add long descriptions
		if ($long_description = $this->class_tags->getLongDescription())
		{
			$this->data['long_description'] = $long_description;
		}

		// Add inheritance chain
		if ($this->inheritance)
		{
			$this->data['inheritance'] = $this->inheritance;
		}

		// Add implemented interfaces
		if ($this->implements)
		{
			$this->data['implements'] = $this->implements;
		}

		// Add class tags
		if ($tags = $this->class_tags->getTags())
		{
			$this->data['metadata'] = $tags;
		}

		// Add constants
		if ($constants = $this->constants->getConstants())
		{
			$this->data['constants'] = $constants;
		}

		// Add properties
		if ($properties = $this->properties->getProperties())
		{
			$this->data['properties'] = $properties;
		}

		#--------------------------------------------------------------------------#

		// Add methods and parameters
		// $rclass_methods = array_values(array_filter($rclass_methods, function($rmethod)
		// {
		// 	return !preg_match(ConfigStore::get('api.exclude.methods'), $rmethod->getName());
		// }));

		// foreach ($rclass_methods as $rmethod)
		// {
		// 	if (!isset($this->data['methods']))
		// 	{
		// 		$this->data['methods'] = array();
		// 	}

		// 	if (!isset($this->data['methods']['count']))
		// 	{
		// 		$this->data['methods']['count'] = count($rclass_methods);
		// 	}

		// 	if (!isset($this->data['methods']['method']))
		// 	{
		// 		$this->data['methods']['method'] = array();
		// 	}

		// 	$method_docblock = new DocBlock($rmethod->getDocComment());

		// 	$entry = array();
		// 	$entry['name'] = $rmethod->getName();
		// 	$entry['visibility'] = ParseUtil::methodAccess($rmethod);

		// 	if ($extension = $rmethod->getExtensionName())
		// 	{
		// 		$entry['extension'] = $extension;
		// 		DependencyCollector::add($extension);
		// 	}

		// 	if ($rmethod->getFileName())
		// 	{
		// 		$entry['path'] = str_replace(VANITY_PROJECT_WORKING_DIR . '/', '', $rmethod->getFileName());
		// 		$entry['lines'] = array(
		// 			'start' => $rmethod->getStartLine(),
		// 			'end'   => $rmethod->getEndLine(),
		// 		);
		// 	}

		// 	if ($description = $method_docblock->getShortDescription())
		// 	{
		// 		$entry['description'] = $description;
		// 	}

		// 	// Method inheritance
		// 	if (($declaring_class = $rmethod->getDeclaringClass()->getName()) !== $this->rclass->getName())
		// 	{
		// 		if (!isset($entry['inheritance']))
		// 		{
		// 			$entry['inheritance'] = array();
		// 		}

		// 		if (!isset($entry['inheritance']['class']))
		// 		{
		// 			$entry['inheritance']['class'] = array();
		// 		}

		// 		$declaring_class = new ReflectionClass($declaring_class);

		// 		$subentry = array();
		// 		$subentry['name'] = $declaring_class->getName();
		// 		if ($declaring_class->getFileName())
		// 		{
		// 			$subentry['path'] = str_replace(VANITY_PROJECT_WORKING_DIR . '/', '', $declaring_class->getFileName());
		// 		}

		// 		$entry['inheritance']['class'][] = $subentry;
		// 	}

		// 	// Method tags
		// 	if (count($method_docblock->getTags()))
		// 	{
		// 		if (!isset($entry['metadata']))
		// 		{
		// 			$entry['metadata'] = array();
		// 		}

		// 		if (!isset($entry['metadata']['tag']))
		// 		{
		// 			$entry['metadata']['tag'] = array();
		// 		}

		// 		foreach ($method_docblock->getTags() as $rtag)
		// 		{
		// 			$dtag = new Tag($rtag);
		// 			$entry['metadata']['tag'][] = $dtag->determine()->process();
		// 		}
		// 	}

		// 	// Method parameters
		// 	if ($count = count($rmethod->getParameters()))
		// 	{
		// 		if (!isset($entry['parameters']))
		// 		{
		// 			$entry['parameters'] = array();
		// 		}

		// 		if (!isset($entry['parameters']['count']))
		// 		{
		// 			$entry['parameters']['count'] = $count;
		// 		}

		// 		if (!isset($entry['parameters']['parameter']))
		// 		{
		// 			$entry['parameters']['parameter'] = array();
		// 		}

		// 		foreach ($rmethod->getParameters() as $rparameter)
		// 		{
		// 			$tag_finder = new TagFinder($entry);

		// 			$param = array();
		// 			$param['name'] = $rparameter->getName();
		// 			$param['required'] = !$rparameter->isOptional();
		// 			$param['passed_by_reference'] = $rparameter->isPassedByReference();

		// 			if ($rparameter->isDefaultValueAvailable())
		// 			{
		// 				$param['default'] = $rparameter->getDefaultValue();
		// 			}

		// 			// Pull-in from @tags
		// 			if ($_description = $tag_finder->find('description', $param['name']))
		// 			{
		// 				$param['description'] = $_description;
		// 			}

		// 			if ($_type = $tag_finder->find('type', $param['name']))
		// 			{
		// 				$param['type'] = $this->ancestry->resolveNamespace($_type);
		// 			}

		// 			if ($_types = $tag_finder->find('types', $param['name']))
		// 			{
		// 				$param['types'] = $_types;
		// 			}

		// 			// Type hinting trumps docblock
		// 			if ($rparameter->getClass())
		// 			{
		// 				// @todo: Improve this logic by resolving namespace aliases.
		// 				if (isset($param['type']) &&
		// 				    $param['type'] !== $rparameter->getClass()->getName())
		// 				{
		// 					Inconsistency::add($this->rclass->getName() . '::' . $rmethod->getName() . '($' . $rparameter->getName() . ') [' . $param['type'] . ' => ' . $rparameter->getClass()->getName() . ']');
		// 				}

		// 				$param['type'] = $rparameter->getClass()->getName();

		// 				if (isset($param['types']))
		// 				{
		// 					unset($param['types']);
		// 				}
		// 			}


		// 			$entry['parameters']['parameter'][] = $param;
		// 		}
		// 	}

		// 	$this->data['methods']['method'][] = $entry;
		// }
	}

	/**
	 * Saves the reflected data as a JSON document.
	 *
	 * @param  string          $path   The file system path to save the JSON document to.
	 * @param  OutputInterface $output The command-line output.
	 * @return void
	 */
	public function save($path, OutputInterface $output)
	{
		// Determine the path & filename
		$path = $path . '/' . str_replace(array('\\', '_'), '/', $this->class_name) . '.json';
		$directory = pathinfo($path, PATHINFO_DIRNAME);
		$filename = pathinfo($path, PATHINFO_BASENAME);

		// Create the directory
		$filesystem = new Filesystem();
		$filesystem->mkdir($directory, 0777);

		$encoded_data = ConsoleUtil::json_encode($this->data);

		// Write the file
		file_put_contents($directory . '/' . $filename, $encoded_data);
		$output->writeln(TAB . $this->formatter->green->apply('-> ') . $directory . '/' . $filename);
	}
}
