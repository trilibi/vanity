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
use Symfony\Component\EventDispatcher\Event;
use Symfony\Component\Filesystem\Filesystem;
use Vanity\Config\Store as ConfigStore;
use Vanity\Console\Utilities as ConsoleUtil;
use Vanity\Event\Event\Store as EventStore;
use Vanity\GlobalObject\Dispatcher;
use Vanity\GlobalObject\Logger;
use Vanity\Parse\GitHub;
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
		$this->traits      = $this->ancestry->getTraits();
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
	 * @event  EventStore vanity.parse.user.reflect.pre
	 * @event  EventStore vanity.parse.user.reflect.post
	 * @return array      An associative array containing all relevant class data.
	 */
	public function process()
	{
		// REFLECT ALL THE THINGS!
		$rclass_methods = $this->rclass->getMethods();
		$long_filename = $this->rclass->getFileName();
		$short_filename = str_replace(
			array(
				(VANITY_SYSTEM . '/'),
				(VANITY_PROJECT_WORKING_DIR . '/'),
			),
			'', $long_filename
		);

		$this->triggerEvent("vanity.parse.user.reflect.pre", new EventStore(array(
			'data' => &$this->data,
		)));

		$this->data['name'] = $this->rclass->getShortName();
		$this->data['namespace'] = $this->rclass->getNamespaceName();
		$this->data['full_name'] = $this->class_name;
		$this->data['namespace_as_path'] = str_replace('\\', '/', $this->class_name);
		$this->data['path'] = $short_filename;

		if (SystemStore::get('_.php54') && $this->rclass->isTrait())
		{
			$this->data['kind'] = 'Trait';
		}
		elseif ($this->rclass->isInterface())
		{
			$this->data['kind'] = 'Interface';
		}
		elseif ($this->rclass->isSubClassOf('Exception'))
		{
			$this->data['kind'] = 'Exception';
		}
		else
		{
			$this->data['kind'] = 'Class';
		}

		SystemStore::add('_.current', $this->class_name);

		#--------------------------------------------------------------------------#

		// Enable GitHub lookups for author data
		if (ConfigStore::get('source.github.user') && ConfigStore::get('source.github.pass'))
		{
			$github = new GitHub(
				ConfigStore::get('source.github.user'),
				ConfigStore::get('source.github.pass')
			);

			$github->setRepository(
				ConfigStore::get('source.github.repo_owner'),
				ConfigStore::get('source.github.repo_name')
			);

			$this->data['github'] = $github->getAuthorsForFile($this->data['path']);
		}

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

		// Add used traits
		if ($this->traits)
		{
			$this->data['traits'] = $this->traits;
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

		// Sort the properties alphabetically
		if (isset($this->data['properties']) &&
		    isset($this->data['properties']['property']) &&
		    is_array($this->data['properties']['property']))
		{
			usort($this->data['properties']['property'], function($a, $b)
			{
				$a = $a['name'];
				$b = $b['name'];

				if ($a === $b) return 0;
				return ($a < $b) ? -1 : 1;
			});
		}

		// Add methods
		if ($methods = $this->methods->getMethods())
		{
			$this->data['methods'] = $methods;
		}

		// Add meta-methods
		if (isset($this->data['metadata']) && isset($this->data['methods']))
		{
			$new = $this->formatMetaMethods($this->data['metadata']);
			$this->data['methods']['method'] = array_merge($this->data['methods']['method'], $new);
			$this->data['methods']['count'] += count($new);
		}
		elseif (isset($this->data['metadata']))
		{
			$new = $this->formatMetaMethods($this->data['metadata']);
			$this->data['methods']['method'] = $new;
			$this->data['methods']['count'] = count($new);
		}

		// Sort the methods alphabetically
		if (isset($this->data['methods']) &&
		    isset($this->data['methods']['method']) &&
		    is_array($this->data['methods']['method']))
		{
			usort($this->data['methods']['method'], function($a, $b)
			{
				$a = $a['name'];
				$b = $b['name'];

				if ($a === $b) return 0;
				return ($a < $b) ? -1 : 1;
			});
		}

		// Sort the metadata tags, post-edit from @method, @property and @return
		if (isset($this->data['metadata']))
		{
			$this->data['metadata']['tag'] = array_values($this->data['metadata']['tag']);

			// Sort the tags alphabetically
			usort($this->data['metadata']['tag'], function($a, $b)
			{
				$a = $a['name'];
				$b = $b['name'];

				if ($a === $b) return 0;
				return ($a < $b) ? -1 : 1;
			});
		}

		// Handle alias tags

		$this->triggerEvent("vanity.parse.user.reflect.post", new EventStore(array(
			'data' => &$this->data,
		)));
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
					// $rf['raw'] = $tag['raw'];
					$rf['name'] = $tag['method'];
					$rf['visibility'] = array('public');

					if ($description = $tag['description'])
					{
						$rf['description'] = $description;
					}

					if ($arguments = $tag['arguments'])
					{
						$rf['parameters'] = array(
							'count' => count($arguments),
							'parameter' => $arguments,
						);
					}

					$rf['return'] = array(
						'type' => $tag['type']
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
	 * Triggers an event and logs it to the log.
	 *
	 * @param  string $event       The string identifier for the event.
	 * @param  Event  $eventObject An object that extends the {@see Symfony\Component\EventDispatcher\Event} object.
	 * @return void
	 */
	public function triggerEvent($event, Event $eventObject = null)
	{
		Logger::get()->{ConfigStore::get('log.events')}('Triggering event:', array($event));
		Dispatcher::get()->dispatch($event, $eventObject);
	}
}
