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

use ReflectionClass;
use ReflectionProperty;
use stdClass;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Filesystem;
use Vanity\Console\Utilities as ConsoleUtil;
use Vanity\Parse\User\Reflect\AncestryHandler;
use Vanity\Parse\User\Reflect\ConstantHandler;
use Vanity\Parse\User\Reflect\InlineTagHandler;
use Vanity\Parse\User\Reflect\MethodHandler;
use Vanity\Parse\User\Reflect\PropertyHandler;
use Vanity\Parse\User\Reflect\TagHandler;
use Vanity\Parse\Utilities as ParseUtil;
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
	 * @type string
	 */
	public $class_name;

	/**
	 * Stores a copy of the reflected class.
	 * @type ReflectionClass
	 */
	public $rclass;

	/**
	 * Stores a copy of the class constants.
	 * @type ConstantHandler
	 */
	public $constants;

	/**
	 * Stores a copy of the class properties.
	 * @type PropertyHandler
	 */
	public $properties;

	/**
	 * Stores a copy of the class methods.
	 * @type MethodHandler
	 */
	public $methods;

	/**
	 * Stores a copy of the class tags.
	 * @type TagHandler
	 */
	public $class_tags;

	/**
	 * The reflected data to store.
	 * @type array
	 */
	public $data;

	/**
	 * Storage for class ancestry data.
	 * @type AncestryHandler
	 */
	public $ancestry;

	/**
	 * Storage for mapping of aliases to namespaces.
	 * @type array
	 */
	public $aliases;

	/**
	 * Storage for text formatters.
	 * @type stdClass
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
		$this->methods     = new MethodHandler($this->rclass, $this->ancestry);
		$this->class_tags  = new TagHandler($this->rclass->getDocComment(), $this->ancestry);

		$this->formatter   = ConsoleUtil::formatters();
	}

	/**
	 * Reflects over the given class and produces an associative array
	 * containing all relevant class data.
	 *
	 * @return array An associative array containing all relevant class data.
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

		// Add description
		if ($description = $this->class_tags->getDescription())
		{
			$this->data['description'] = $description;
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

		// Add meta-properties
		if (isset($this->data['metadata']) && isset($this->data['properties']))
		{
			$new = $this->formatMetaProperties($this->data['metadata']);
			$this->data['properties']['property'] = array_merge($this->data['properties']['property'], $new);
			$this->data['properties']['count'] += count($new);
		}
		elseif (isset($this->data['metadata']))
		{
			$new = $this->formatMetaProperties($this->data['metadata']);
			$this->data['properties']['property'] = $new;
			$this->data['properties']['count'] = count($new);
		}

		// Add methods
		if ($methods = $this->methods->getMethods())
		{
			$this->data['methods'] = $methods;
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
	 * Removes meta-properties from the metadata collection, and reformats them
	 * to fit with real properties.
	 *
	 * @param  array &$metadata The source metadata.
	 * @return array            The reformatted array of meta-properties.
	 */
	public function formatMetaProperties(&$metadata)
	{
		$reformatted = array();

		if (isset($metadata['tag']) && count($metadata['tag']) > 0)
		{
			foreach ($metadata['tag'] as $index => $tag)
			{
				if (isset($tag['name']) && $tag['name'] === 'property')
				{
					$rf = array();
					$rf['raw'] = $tag['raw'];
					$rf['name'] = $tag['variable'];
					$rf['visibility'] = array(
						'public'
					);
					$rf['metadata'] = array(
						'tag' => array(
							array(
								'name'        => 'type',
								'type'        => $tag['type'],
								'description' => $tag['description'],
							)
						)
					);

					$reformatted[] = $rf;
					unset($metadata['tag'][$index]);
				}
			}

			// Remove if empty
			if (count($this->data['metadata']['tag']) === 0)
			{
				unset($this->data['metadata']);
			}
		}

		return $reformatted;
	}

	/**
	 * Removes meta-methods from the metadata collection, and reformats them
	 * to fit with real methods.
	 *
	 * @param  array &$metadata The source metadata.
	 * @return array            The reformatted array of meta-methods.
	 */
	public function formatMetaMethods(&$metadata)
	{
		$reformatted = array();

		if (isset($metadata['tag']) && count($metadata['tag']) > 0)
		{
			foreach ($metadata['tag'] as $index => $tag)
			{
				if (isset($tag['name']) && $tag['name'] === 'method')
				{
					$rf = array();
					$rf['name'] = $tag['method'];
					$rf['visibility'] = array(
						'public'
					);
					$rf['path'] = '';
					$rf['description'] = $tag['description'];
					$rf['metadata'] = array(
						'tag' => array()
					);

					// @todo: Add support for @param.
					// $rf['metadata']['tag'][] = array(
					// 	'name'        => 'return',
					// 	'type'        => 'void',
					// 	'variable'    => null,
					// 	'arguments'   => null,
					// 	'description' => null,
					// );

					// $rf['metadata']['tag'][] = array(
					// 	'name' => 'return',
					// 	'type' => (isset($tag['type']) ? $tag['type'] : 'void'),
					// )
/*
{
    {
        "name": "return",
        "type": "Guzzle\\Service\\Builder\\ServiceBuilder"
    }

    "parameters": {
        "count": 2,
        "parameter": [
            {
                "name": "config",
                "required": false,
                "passed_by_reference": false,
                "default": null
            },
            {
                "name": "globalParameters",
                "required": false,
                "passed_by_reference": false,
                "default": [

                ]
            }
        ]
    }
},
*/

					$reformatted[] = $rf;
					unset($metadata['tag'][$index]);
				}
			}

			// Remove if empty
			if (count($this->data['metadata']['tag']) === 0)
			{
				unset($this->data['metadata']);
			}
		}

		return $reformatted;
	}
}
