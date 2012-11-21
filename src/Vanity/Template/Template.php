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

use Twig_Autoloader;
use Twig_Environment;
use Twig_Function_Function;
use Twig_Function_Method;
use Twig_Loader_Filesystem;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;
use Vanity\Config\Store as ConfigStore;
use Vanity\Console\Utilities as ConsoleUtil;
use Vanity\Event\Event\Store as EventStore;
use Vanity\Generate\Utilities as GenerateUtils;
use Vanity\GlobalObject\Dispatcher;
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
	public $format_identifier;

	/**
	 * {@inheritdoc}
	 */
	public function __construct($template_path, $format_identifier)
	{
		$this->filesystem = new Filesystem();
		$this->format_identifier = $format_identifier;

		Twig_Autoloader::register();

		$this->twig = new Twig_Environment(
			new Twig_Loader_Filesystem($template_path),
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

		$this->twig->addFunction('description_as_html', new Twig_Function_Function('vanity_twig_description_as_html'));
		$this->twig->addFunction('namespace_as_path', new Twig_Function_Function('vanity_twig_namespace_as_path'));
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
			$finder = new Finder();

			$template = new $calledClass(
				GenerateUtils::findTemplatesFor($calledClass),
				$nameOfFormatterEvent
			);

			echo $formatter->yellow->apply('GENERATING: ' . strtoupper($nameOfFormatterEvent)) . PHP_EOL;

			$files = $event->get('files');
			foreach ($files['absolute'] as $file)
			{
				echo TAB . $formatter->green->apply('-> ') . ($template->generateAPIReference($file)) . PHP_EOL;
			}

			echo PHP_EOL;
			echo $formatter->yellow->apply('COPYING STATIC ASSETS:') . PHP_EOL;

			$staticAssets = $finder
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

			echo PHP_EOL;
			echo 'Copied ' . $formatter->info->apply(" ${assets} ") . ' static ' . ConsoleUtil::pluralize($assets, 'file', 'files') . '.';
			echo PHP_EOL;
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
	 */
	public function generateAPIReference($json_file)
	{
		$data = json_decode(file_get_contents($json_file), true);
		$path = $this->convertNamespaceToPath($data['full_name']);

		$twig_options = array(
			'json'      => $data,
			'vanity'    => array(
				'base_path'            => GenerateUtils::getRelativeBasePath($data['full_name']),
				'breadcrumbs'          => GenerateUtils::getBreadcrumbs($data['full_name']),
				'page_name'            => $data['name'],
				'page_title'           => $data['full_name'],
				'project'              => ConfigStore::get('vanity.name'),
				'project_with_version' => (ConfigStore::get('vanity.name') . ' ' . ConfigStore::get('vanity.version')),
				'link'                 => array(
					'api_reference' => GenerateUtils::getRelativeBasePath($data['full_name']) . '/api-reference',
					'user_guide'    => GenerateUtils::getRelativeBasePath($data['full_name']) . '/user-guide',
				),
			)
		);

		$this->filesystem->mkdir($path);

		file_put_contents(
			$path . '/index.' . $this->extension,
			$this->twig->render('class.twig', $twig_options)
		);

		return $path . '/index.' . $this->extension;
	}

	/**
	 * Convert a namespace into the proper output path.
	 *
	 * @param  string $namespace The namespace to determine the output path for.
	 * @return string            The output path.
	 */
	public function convertNamespaceToPath($namespace)
	{
		return str_replace('%FORMAT%', $this->format_identifier, ConfigStore::get('generator.output')) .
			'/api-reference/' . str_replace('\\', '/', $namespace);
	}
}
