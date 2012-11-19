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
use Twig_Loader_Filesystem;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;
use Vanity\Config\Store as ConfigStore;
use Vanity\Console\Utilities as ConsoleUtil;
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
	 * {@inheritdoc}
	 */
	public function __construct($template_path)
	{
		Twig_Autoloader::register();

		$this->twig = new Twig_Environment(
			new Twig_Loader_Filesystem($template_path),
			array(
		    	'cache' => (
		    		sys_get_temp_dir() .
		    		'/' .
		    		ConsoleUtil::asciify(ConfigStore::get('vanity.name'))
		    	),
			)
		);
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
	public function generate($json_file)
	{
		$data = json_decode(file_get_contents($json_file), true);
		$path = $this->namespaceToPath($data['full_name']);

		$filesystem = new Filesystem();
		$filesystem->mkdir($path);

		file_put_contents(
			$path . '/index.' . $this->extension,
			$this->twig->render('class.twig', array(
				'base_path' => '.',
				'vanity'    => array(
					'project'    => (ConfigStore::get('vanity.name') . ' ' . ConfigStore::get('vanity.version')),
					'page_title' => $data['full_name'],
				)
			))
		);

		return $path . '/index.' . $this->extension;
	}

	/**
	 * Convert a namespace into the proper output path.
	 *
	 * @param  string $namespace The namespace to determine the output path for.
	 * @return string            The output path.
	 */
	public function namespaceToPath($namespace)
	{
		return str_replace('%FORMAT%', $this->extension, ConfigStore::get('generator.output')) .
			'/' . str_replace('\\', '/', $namespace);
	}
}
