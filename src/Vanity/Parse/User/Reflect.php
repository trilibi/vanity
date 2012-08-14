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
use dflydev\markdown\MarkdownExtraParser as Markdown;
use phpDocumentor\Reflection\DocBlock;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Filesystem;
use TokenReflection\Broker;
use Vanity\Config\Store as ConfigStore;
use Vanity\Console\Utilities as ConsoleUtil;
use Vanity\Parse\User\Tag;
use Vanity\Parse\User\TagFinder;
use Vanity\Parse\Utilities as ParseUtil;
use Vanity\System\DependencyCollector;
use Vanity\System\DocumentationInconsistencyCollector as Inconsistency;

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
	 * The reflected data to store.
	 * @var array
	 */
	public $data;

	/**
	 * Storage for text formatters.
	 * @var stdClass
	 */
	public $formatter;

	/**
	 * Storage for the {@see Markdown} class.
	 * @var Markdown
	 */
	public $markdown;

	/**
	 * The {@see Broker} for handling tokenized reflection.
	 * @var Broker
	 */
	public $broker;

	/**
	 * Storage for all of the Namespace/Alias mappings.
	 * @var array
	 */
	public $aliases;

	/**
	 * Constructs a new instance of this class.
	 *
	 * @param string $class The class name to reflect.
	 */
	public function __construct($class)
	{
		$this->class_name = $class;
		$this->formatter = ConsoleUtil::formatters();
		$this->markdown = new Markdown();
		$this->broker = new Broker(new Broker\Backend\Memory());
		$this->aliases = array();
	}

	/**
	 * Reflects over the given class and produces an associative array
	 * containing all relevant class data.
	 *
	 * @return array An associative array containing all relevant class data.
	 *
	 * @todo Extract alias resolution into a separate class.
	 * @todo Extract constant handling into a separate class.
	 * @todo Extract property handling into a separate class.
	 * @todo Extract method handling into a separate class.
	 * @todo Extract parameter handling into a separate class.
	 * @todo Handle resolving aliases to their local namespace.
	 * @todo Verify a namespaced class exists after resolution.
	 * @todo Provide an option to toggle alias resolution (e.g., api.resolve.aliases)
	 * @todo Add support for @method
	 * @todo Add support for {@see}
	 * @todo Add support for {@inheritdoc}
	 * @todo Add support for @example
	 * @todo Add support for {@example}
	 */
	public function process()
	{
		// REFLECT ALL THE THINGS!
		$rclass = new ReflectionClass($this->class_name);
		$rclass_properties = $rclass->getProperties();
		$rclass_constants = $rclass->getConstants();
		$rclass_methods = $rclass->getMethods();
		$rclass_comments = $rclass->getDocComment();
		ksort($rclass_constants);
		ksort($rclass_properties);
		sort($rclass_methods);

		$long_filename = $rclass->getFileName();
		$short_filename = str_replace(VANITY_PROJECT_WORKING_DIR . '/', '', $long_filename);

		$class_docblock = new DocBlock($rclass_comments);

		$this->data['name'] = $rclass->getShortName();
		$this->data['namespace'] = $rclass->getNamespaceName();
		$this->data['full_name'] = $this->class_name;
		$this->data['path'] = $short_filename;

		if ($short_description = $class_docblock->getShortDescription())
		{
			$this->data['short_description'] = $this->markdown->transform($short_description);
		}

		if ($long_description = $class_docblock->getLongDescription()->getFormattedContents())
		{
			$this->data['long_description'] = $long_description;
		}

		#--------------------------------------------------------------------------#

		// Add inheritance chain
		foreach (ParseUtil::getParentClasses($rclass) as $parent)
		{
			if (!isset($this->data['inheritance']))
			{
				$this->data['inheritance'] = array();
			}

			if (!isset($this->data['inheritance']['class']))
			{
				$this->data['inheritance']['class'] = array();
			}

			$parent_class = new ReflectionClass($parent);

			$entry = array();
			$entry['name'] = $parent;
			if ($parent_class->getFileName())
			{
				$entry['path'] = str_replace(VANITY_PROJECT_WORKING_DIR . '/', '', $parent_class->getFileName());
			}

			$this->data['inheritance']['class'][] = $entry;
		}

		#--------------------------------------------------------------------------#

		// Add implemented interfaces
		foreach ($rclass->getInterfaces() as $interface)
		{
			if (!isset($this->data['implements']))
			{
				$this->data['implements'] = array();
			}

			if (!isset($this->data['implements']['interface']))
			{
				$this->data['implements']['interface'] = array();
			}

			$entry = array();
			$entry['name'] = $interface->getName();
			if ($interface->getFileName())
			{
				$entry['path'] = str_replace(VANITY_PROJECT_WORKING_DIR . '/', '', $interface->getFileName());
			}

			$this->data['implements']['interface'][] = $entry;
		}

		#--------------------------------------------------------------------------#

		// Get a list of aliases that map to their proper namespaces
		$aliases = array();
		$class_paths = array();
		$interface_paths = array();

		if (isset($this->data['inheritance']))
		{
			$class_paths = array_values(
				array_filter($this->data['inheritance']['class'],
					function($class)
					{
						return isset($class['path']);
					}
				)
			);
		}

		if (isset($this->data['implements']))
		{
			$interface_paths = array_values(
				array_filter($this->data['implements']['interface'],
					function($interface)
					{
						return isset($interface['path']);
					}
				)
			);
		}

		array_unshift($class_paths, array(
			'path' => $long_filename
		));

		while (count($interface_paths))
		{
			array_push($class_paths, array_shift($interface_paths));
		}

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

		foreach ($aliases as $alias)
		{
			$this->aliases = array_merge($this->aliases, $alias);
		}

		ksort($this->aliases);

		#--------------------------------------------------------------------------#

		// Class tags
		if (count($class_docblock->getTags()))
		{
			if (!isset($this->data['metadata']))
			{
				$this->data['metadata'] = array();
			}

			if (!isset($this->data['metadata']['tag']))
			{
				$this->data['metadata']['tag'] = array();
			}

			foreach ($class_docblock->getTags() as $rtag)
			{
				$dtag = new Tag($rtag);
				$this->data['metadata']['tag'][] = $dtag->determine()->process();
			}
		}

		#--------------------------------------------------------------------------#

		// Add constants
		foreach ($rclass_constants as $rconstant_name => $rconstant_value)
		{
			if (!isset($this->data['constants']))
			{
				$this->data['constants'] = array();
			}

			if (!isset($this->data['constants']['count']))
			{
				$this->data['constants']['count'] = count($rclass_constants);
			}

			if (!isset($this->data['constants']['constant']))
			{
				$this->data['constants']['constant'] = array();
			}

			$entry = array();
			$entry['name'] = $rconstant_name;
			$entry['value'] = $rconstant_value;
			$entry['type'] = gettype($rconstant_value);

			$this->data['constants']['constant'][] = $entry;
		}

		#--------------------------------------------------------------------------#

		// Add properties
		foreach ($rclass_properties as $rproperty)
		{
			if (!isset($this->data['properties']))
			{
				$this->data['properties'] = array();
			}

			if (!isset($this->data['properties']['count']))
			{
				$this->data['properties']['count'] = count($rclass_properties);
			}

			if (!isset($this->data['properties']['property']))
			{
				$this->data['properties']['property'] = array();
			}

			$property_docblock = new DocBlock($rproperty->getDocComment());

			$entry = array();
			$entry['name'] = $rproperty->getName();
			$entry['visibility'] = ParseUtil::propertyAccess($rproperty);

			if ($description = $property_docblock->getShortDescription())
			{
				$entry['description'] = $description;
			}

			// Property inheritance
			if (($declaring_class = $rproperty->getDeclaringClass()->getName()) !== $rclass->getName())
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

			// Property tags
			if (count($property_docblock->getTags()))
			{
				if (!isset($entry['metadata']))
				{
					$entry['metadata'] = array();
				}

				if (!isset($entry['metadata']['tag']))
				{
					$entry['metadata']['tag'] = array();
				}

				foreach ($property_docblock->getTags() as $rtag)
				{
					$dtag = new Tag($rtag);
					$entry['metadata']['tag'][] = $dtag->determine()->process();
				}
			}

			// Default value, if accessible
			if ($rproperty->isPublic())
			{
				$rvalue = $rproperty->getValue($rclass);
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

			$this->data['properties']['property'][] = $entry;
		}

		#--------------------------------------------------------------------------#

		// Add methods and parameters
		$rclass_methods = array_values(array_filter($rclass_methods, function($rmethod)
		{
			return !preg_match(ConfigStore::get('api.exclude.methods'), $rmethod->getName());
		}));

		foreach ($rclass_methods as $rmethod)
		{
			if (!isset($this->data['methods']))
			{
				$this->data['methods'] = array();
			}

			if (!isset($this->data['methods']['count']))
			{
				$this->data['methods']['count'] = count($rclass_methods);
			}

			if (!isset($this->data['methods']['method']))
			{
				$this->data['methods']['method'] = array();
			}

			$method_docblock = new DocBlock($rmethod->getDocComment());

			$entry = array();
			$entry['name'] = $rmethod->getName();
			$entry['visibility'] = ParseUtil::methodAccess($rmethod);

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
			}

			if ($description = $method_docblock->getShortDescription())
			{
				$entry['description'] = $description;
			}

			// Method inheritance
			if (($declaring_class = $rmethod->getDeclaringClass()->getName()) !== $rclass->getName())
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
					$dtag = new Tag($rtag);
					$entry['metadata']['tag'][] = $dtag->determine()->process();
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
						$param['type'] = $this->resolveNamespace($_type);
					}

					if ($_types = $tag_finder->find('types', $param['name']))
					{
						$param['types'] = $_types;
					}

					// Type hinting trumps docblock
					if ($rparameter->getClass())
					{
						// @todo: Improve this logic by resolving namespace aliases.
						if (isset($param['type']) &&
						    $param['type'] !== $rparameter->getClass()->getName())
						{
							Inconsistency::add($rclass->getName() . '::' . $rmethod->getName() . '($' . $rparameter->getName() . ') [' . $param['type'] . ' => ' . $rparameter->getClass()->getName() . ']');
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

			$this->data['methods']['method'][] = $entry;
		}
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

	/**
	 * Resolves a namespace alias into a fully-qualified namespace.
	 *
	 * @param  string $short A shortened namespace alias.
	 * @return string        The fully-qualified namespace, if available.
	 */
	public function resolveNamespace($short)
	{
		if (count($this->aliases))
		{
			if (isset($this->aliases[$short]))
			{
				return $this->aliases[$short];
			}
		}

		return preg_replace('/^\\\/', '', $short);
	}
}
