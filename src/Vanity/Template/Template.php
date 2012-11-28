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


namespace Vanity\Template;

require_once VANITY_VENDOR . '/twig/twig/lib/Twig/Autoloader.php';

use Aptoma\Twig\Extension\MarkdownExtension;
use dflydev\markdown\MarkdownParser;
use Twig_Autoloader;
use Twig_Environment;
use Twig_Extension_Debug;
use Twig_Filter_Function;
use Twig_Function_Function;
use Twig_Function_Method;
use Twig_Loader_Filesystem;
use Symfony\Component\EventDispatcher\Event;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;
use Vanity\Config\Store as ConfigStore;
use Vanity\Console\Utilities as ConsoleUtil;
use Vanity\Event\Event\Store as EventStore;
use Vanity\Generate\Utilities as GenerateUtils;
use Vanity\GlobalObject\Dispatcher;
use Vanity\GlobalObject\Logger;
use Vanity\Template\TemplateInterface;

abstract class Template implements TemplateInterface
{
	/**
	 * The Twig environment object.
	 * @type Twig_Environment
	 */
	public $twig;

	/**
	 * The file extension to use for generated files.
	 * @type string
	 */
	public $extension;

	/**
	 * Stores the handle to the Symfony Filesystem object.
	 * @type Filesystem
	 */
	public $filesystem;

	/**
	 * Stores the name of the directory that the output is written to.
	 * @type string
	 */
	public static $format_identifier;

	/**
	 * The path to the Twig templates.
	 * @type string
	 */
	public $template_path;

	/**
	 * Stores the data used for typeahead queries.
	 * @type array
	 */
	public static $typeahead = array();

	/**
	 * {@inheritdoc}
	 *
	 * @event EventStore vanity.twig.environment.init
	 */
	public function __construct($template_path, $format_identifier)
	{
		$this->filesystem = new Filesystem();
		self::$format_identifier = $format_identifier;
		$this->template_path = $template_path;

		Twig_Autoloader::register();

		$this->twig = new Twig_Environment(
			new Twig_Loader_Filesystem($this->template_path),
			array(
				'autoescape'          => ConfigStore::get('generator.twig.autoescape'),
				'auto_reload'         => ConfigStore::get('generator.twig.auto_reload'),
				'base_template_class' => ConfigStore::get('generator.twig.base_template_class'),
				'charset'             => ConfigStore::get('generator.twig.charset'),
				'debug'               => ConfigStore::get('generator.twig.debug'),
				'optimizations'       => ConfigStore::get('generator.twig.optimizations'),
				'strict_variables'    => ConfigStore::get('generator.twig.strict_variables'),
				'cache'               => (
		    		sys_get_temp_dir() .
		    		'/' .
		    		ConsoleUtil::asciify(ConfigStore::get('vanity.name'))
		    	),
			)
		);

		// Extensions
		$this->twig->addExtension(new Twig_Extension_Debug());
		$this->twig->addExtension(new MarkdownExtension(new MarkdownParser));

		// Functions
		$this->twig->addFunction('description_as_html', new Twig_Function_Function('vanity_twig_description_as_html'));
		$this->twig->addFunction('namespace_as_path', new Twig_Function_Function('vanity_twig_namespace_as_path'));
		$this->twig->addFunction('filter_by_native', new Twig_Function_Function('vanity_twig_filter_by_native'));
		$this->twig->addFunction('filter_by_inherited', new Twig_Function_Function('vanity_twig_filter_by_inherited'));
		$this->twig->addFunction('filter_by_letter', new Twig_Function_Function('vanity_twig_filter_by_letter'));
		$this->twig->addFunction('names', new Twig_Function_Function('vanity_twig_names'));

		$this->triggerEvent('vanity.twig.environment.init', new EventStore(array(
			'twig' => $this->twig,
		)));
	}

	/**
	 * {@inheritdoc}
	 */
	public static function register($nameOfFormatterEvent)
	{
		$calledClass = get_called_class();

		Dispatcher::get()->addListener("vanity.generate.format.${nameOfFormatterEvent}",
			function(EventStore $event) use ($calledClass, $nameOfFormatterEvent)
		{
			$formatter = ConsoleUtil::formatters();
			$filesystem = new Filesystem();

			$template = new $calledClass(
				GenerateUtils::findTemplatesFor($calledClass),
				$nameOfFormatterEvent
			);

			echo $formatter->yellow->apply('COPYING STATIC ASSETS:') . PHP_EOL;

			// Copy the template's static assets.
			$staticAssets = new Finder();
			$staticAssets = $staticAssets
				->files()
				->in(GenerateUtils::findStaticAssetsFor($calledClass). '/');

			$assets = 0;
			foreach ($staticAssets as $asset)
			{
				$filesystem->copy(
					$asset->getRealPath(),
					GenerateUtils::getAbsoluteBasePath($nameOfFormatterEvent) . '/' . str_replace(
						GenerateUtils::findStaticAssetsFor($calledClass). '/',
						'',
						$asset->getRealPath()
					)
				);

				echo TAB . $formatter->green->apply('-> ') . ($asset->getRealPath()) . PHP_EOL;
				$assets++;
			}

			// Copy the project's static assets.
			if (file_exists(ConfigStore::get('vanity.config_dir') . '/static/'))
			{
				$staticProjectAssets = new Finder();
				$staticProjectAssets = $staticProjectAssets
					->files()
					->in(ConfigStore::get('vanity.config_dir') . '/static/');

				foreach ($staticProjectAssets as $asset)
				{
					$filesystem->copy(
						$asset->getRealPath(),
						GenerateUtils::getAbsoluteBasePath($nameOfFormatterEvent) . '/' . str_replace(
							ConfigStore::get('vanity.config_dir') . '/static/',
							'',
							$asset->getRealPath()
						)
					);

					echo TAB . $formatter->green->apply('-> ') . ($asset->getRealPath()) . PHP_EOL;
					$assets++;
				}
			}

			echo PHP_EOL;
			echo 'Copied ' . $formatter->info->apply(" ${assets} ") . ' static ' . ConsoleUtil::pluralize($assets, 'file', 'files') . '.' . PHP_EOL;
			echo PHP_EOL;

			echo $formatter->yellow->apply('GENERATING: ' . strtoupper($nameOfFormatterEvent)) . PHP_EOL;

			// Keep track of the number of files written
			$count = 0;

			$files = $event->get('files');
			foreach ($files['absolute'] as $file)
			{
				foreach ($template->generateAPIReference($file) as $wrote)
				{
					echo TAB . $formatter->green->apply('-> ') . $wrote . PHP_EOL;
					$count++;
				}
			}
			echo TAB . $formatter->green->apply('-> ') . self::writeTypeahead(self::$typeahead) . PHP_EOL;
			$count++;

			self::wroteFileCount($count);
		});
	}

	/**
	 * {@inheritdoc}
	 */
	public function setFileExtension($extension = 'html')
	{
		$this->extension = $extension;
	}

	/**
	 * {@inheritdoc}
	 *
	 * @event EventStore vanity.twig.generate.options
	 */
	public function generateAPIReference($json_file)
	{
		$data = json_decode(file_get_contents($json_file), true);
		$path = $this->convertNamespaceToPath($data['full_name']);
		$wrote = array();

		$twig_options = array(
			'json'      => $data,
			'vanity'    => array(
				'base_path'            => GenerateUtils::getRelativeBasePath($data['full_name']),
				'breadcrumbs'          => GenerateUtils::getBreadcrumbs($data['full_name'], -1),
				'config'               => ConfigStore::get(),
				'page_name'            => $data['name'],
				'page_title'           => $data['full_name'],
				'project'              => ConfigStore::get('vanity.name'),
				'project_with_version' => (ConfigStore::get('vanity.name') . ' ' . ConfigStore::get('vanity.version')),
				'assets'               => array(
					'apple_touch_icon' => (file_exists(str_replace('%FORMAT%', self::$format_identifier, ConfigStore::get('generator.output')) . '/apple-touch-icon.png')
							? GenerateUtils::getRelativeBasePath($data['full_name']) . '/apple-touch-icon.png'
							: null),
					'favicon' => (file_exists(str_replace('%FORMAT%', self::$format_identifier, ConfigStore::get('generator.output')) . '/favicon.ico')
							? GenerateUtils::getRelativeBasePath($data['full_name']) . '/favicon.ico'
							: null),
					'windows_pinned_site' => (file_exists(str_replace('%FORMAT%', self::$format_identifier, ConfigStore::get('generator.output')) . '/windows-pinned-site.png')
							? GenerateUtils::getRelativeBasePath($data['full_name']) . '/windows-pinned-site.png'
							: null),
				),
				'link' => array(
					'base_dir'      => GenerateUtils::getRelativeBasePath($data['full_name']),
					'api_reference' => GenerateUtils::getRelativeBasePath($data['full_name']) . '/api-reference',
					'user_guide'    => GenerateUtils::getRelativeBasePath($data['full_name']) . '/user-guide',
				),
				'message' => array(
					'not_yet_implemented' => '<i class="icon-exclamation-sign"></i>&nbsp;<em>Not yet documented.</em>',
				),
			)
		);

		$this->triggerEvent('vanity.twig.generate.options', new EventStore(array(
			'twig_options' => &$twig_options,
		)));

		$this->filesystem->mkdir($path);

		// Classes/Interfaces/Traits
		if (file_exists($this->template_path . '/class.twig'))
		{
			file_put_contents(
				$path . '/index.' . $this->extension,
				$this->twig->render('class.twig', $twig_options)
			);
			$wrote[] = $path . '/index.' . $this->extension;
			self::$typeahead[] = $data['full_name'];

			// Methods
			if (file_exists($this->template_path . '/method.twig'))
			{
				if (isset($data['methods']) && isset($data['methods']['method']))
				{
					foreach ($data['methods']['method'] as $method)
					{
						$method_name = $method['name'];

						$twig_options['method'] = $method;
						$twig_options['vanity']['base_path'] = GenerateUtils::getRelativeBasePath($data['full_name'] . "\\${method_name}", -1);
						$twig_options['vanity']['breadcrumbs'] = GenerateUtils::getBreadcrumbs($data['full_name'] . "\\${method_name}()", -2);

						file_put_contents(
							$path . "/${method_name}." . $this->extension,
							$this->twig->render('method.twig', $twig_options)
						);
						$wrote[] = $path . "/${method_name}." . $this->extension;
						self::$typeahead[] = $data['full_name'] . '::' . $method_name . '()';
					}
				}
			}
		}

		return $wrote;
	}

	/**
	 * Convert a namespace into the proper output path.
	 *
	 * @param  string $namespace The namespace to determine the output path for.
	 * @return string            The output path.
	 */
	public function convertNamespaceToPath($namespace)
	{
		return str_replace('%FORMAT%', self::$format_identifier, ConfigStore::get('generator.output')) .
			'/api-reference/' . str_replace('\\', '/', $namespace);
	}

	/**
	 * Triggers an event and logs it to the INFO log.
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

	/**
	 * Log the number of files that were written to the console.
	 *
	 * @param  integer $count The number of files that were written.
	 * @return void
	 */
	public static function wroteFileCount($count)
	{
		$formatter = ConsoleUtil::formatters();

		echo PHP_EOL;
		echo 'Matched ' . $formatter->info->apply(" ${count} ") . ' ' . ConsoleUtil::pluralize($count, 'file', 'files') . '.' . PHP_EOL;
	}

	/**
	 * Write the typeahead file for use in JavaScript searches.
	 *
	 * @param  array $typeahead The array of data to write.
	 * @return void
	 */
	public static function writeTypeahead($typeahead)
	{
		$filename = str_replace('%FORMAT%', self::$format_identifier, ConfigStore::get('generator.output')) .
			'/api-reference/typeahead.json';

		file_put_contents($filename, 'var VANITY={"TYPEAHEAD":' . json_encode($typeahead) . '}');

		return $filename;
	}

	/**
	 * Write the sitemap.xml file used by Google and other search engines.
	 *
	 * @return boolean Whether or not the file was written successfully. A value of `true` means that the file was
	 *                 written successfully. A value of `false` means that the file was NOT written successfully.
	 */
	public function writeSitemap()
	{
		$path = str_replace('%FORMAT%', self::$format_identifier, ConfigStore::get('generator.output'));
		$sitemap = simplexml_load_string('<?xml version="1.0"?><urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"/>');
		$current_date = gmdate(DATE_W3C);

		$files = new Finder();
		$files = $files
			->files()
			->name('*.html')
			->in($path);

		foreach ($files as $file)
		{
			$xurl = $sitemap->addChild('url');
			$xurl->addChild('loc', ConfigStore::get('generator.template.web_root') . $file->getRelativePath() . '/' . $file->getFilename());
			$xurl->addChild('lastmod', $current_date);
			$xurl->addChild('changefreq', 'weekly');
		}

		return file_put_contents($path . '/sitemap.xml', $sitemap->asXML());
	}
}
