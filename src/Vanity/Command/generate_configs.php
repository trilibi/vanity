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


/********************************************************/
// AVAILABLE CONFIGURATION-RELATED OPTIONS

use Symfony\Component\Console\Input\InputOption;
use Vanity\System\Store as SystemStore;

return array_merge(include __DIR__ . '/base_configs.php', array(

	// Configurations related to the API Reference
	'generator' => array(
		'formats' => array(InputOption::VALUE_OPTIONAL | InputOption::VALUE_IS_ARRAY, 'Which formats should we produce', array('default-html')),
		'input'   => array(InputOption::VALUE_OPTIONAL, 'Where should we start looking for code? (Use * for wildcard.)', '%VANITY.CONFIG_DIR%/output/%VANITY.VERSION%-%VANITY.STAGE%/json/*.json'),
		'output'  => array(InputOption::VALUE_OPTIONAL, 'Where should we put the documentation when we\'re done? (Variables: %FORMAT%, %STAGE%, %VERSION%)', '%VANITY.CONFIG_DIR%/output/%VANITY.VERSION%-%VANITY.STAGE%/%FORMAT%'),

		// Configurations that are passed directly to Twig
		'twig' => array(
			'autoescape'          => array(InputOption::VALUE_OPTIONAL, 'If set to true, auto-escaping will be enabled by default for all templates. You can set the escaping strategy to use (html, js, false to disable). You can also set the escaping strategy to use (css, url, html_attr, or a PHP callback that takes the template "filename" and must return the escaping strategy to use -- the callback cannot be a function name to avoid collision with built-in escaping strategies).', true),
			'auto_reload'         => array(InputOption::VALUE_OPTIONAL, 'When developing with Twig, it\'s useful to recompile the template whenever the source code changes. If you don\'t provide a value for the auto_reload option, it will be determined automatically based on the debug value.', '%GENERATOR.TWIG.AUTOESCAPE%'),
			'base_template_class' => array(InputOption::VALUE_OPTIONAL, 'The base template class to use for generated templates.', 'Twig_Template'),
			'charset'             => array(InputOption::VALUE_OPTIONAL, 'The charset used by the templates.', 'UTF-8'),
			'debug'               => array(InputOption::VALUE_OPTIONAL, 'When set to true, the generated templates have a __toString() method that you can use to display the generated nodes.', false),
			'optimizations'       => array(InputOption::VALUE_OPTIONAL, 'A flag that indicates which optimizations to apply. Set it to -1 for all optimizations. Set it to 0 to disable.', -1),
			'strict_variables'    => array(InputOption::VALUE_OPTIONAL, 'If set to false, Twig will silently ignore invalid variables (variables and or attributes/methods that do not exist) and replace them with a null value. When set to true, Twig throws an exception instead.', false),
		),

		// Configurations that can be used in templates. Enables better SEO.
		'template' => array(
			'comments' => array(
				'disqus'          => array(InputOption::VALUE_OPTIONAL, 'Your Disqus "shortcode" for this site. Learn more at http://disqus.com/admin/ > Settings > Site.', null),
				'facebook_app_id' => array(InputOption::VALUE_OPTIONAL, 'Your Facebook App ID for this site. Create a new app at https://developers.facebook.com/apps/. The `generator.template.web_root` option must also be set correctly.', null),
				'intense_debate'  => array(InputOption::VALUE_OPTIONAL, 'Your Intense Debate Site Account ID for this site. Learn more at http://intensedebate.com/sitekey/. (example: e074c11fd3d7example3a3786example)', null),
			),
			'meta' => array(
				'windows_bgcolor' => array(InputOption::VALUE_OPTIONAL, 'The hex color code to use for Windows 7-style pinned sites, and Windows 8-style Metro tiles. Only used if your project has a .vanity/static/windows-pinned-site.png image.', '#fff'),
			),
			'seo' => array(
				'contributors'        => array(InputOption::VALUE_OPTIONAL | InputOption::VALUE_IS_ARRAY, 'The names of the primary contributors.', null),
				'copyright_owner'     => array(InputOption::VALUE_OPTIONAL, 'The name of the person or organization who owns the copyright to this code and/or documentation.', null),
				'copyright_owner_url' => array(InputOption::VALUE_OPTIONAL, 'The URL for the person or organization who owns the copyright to this code and/or documentation.', null),
				'copyright_years'     => array(InputOption::VALUE_OPTIONAL, 'The years of copyright (e.g., 2010-' . date('Y') . ').', date('Y')),
				'description'         => array(InputOption::VALUE_OPTIONAL, 'The description to use for the project. Text may be slightly longer than a tweet (155 characters) for optimal usage.', null),
				'google_analytics'    => array(InputOption::VALUE_OPTIONAL, 'Your Google Analytics account ID for this site. Learn more at https://support.google.com/analytics/bin/answer.py?answer=1008080. (example: UA-XXXXX-Y)', null),
				'google_verification' => array(InputOption::VALUE_OPTIONAL, 'Your Google Verification ID. Learn more at https://www.google.com/webmasters/verification/home. (example: m0EXAMPLE_rWlf0WEXAMPLE4wuYoEXAMPLEXSWcEMMs)', null),
				'license_url'         => array(InputOption::VALUE_OPTIONAL, 'The URL for the licensing terms.', null),
				'locale'              => array(InputOption::VALUE_OPTIONAL, 'The code that represents the locale of the language used. Should be in the {language} or the {language}-{region} format (e.g., en, en-US, en-GB, fr, fr-FR). See http://www.langtag.net/registries/lsr-language-utf8.txt and http://www.langtag.net/registries/lsr-region-utf8.txt for valid values.', 'en'),
			),
			'social_buttons' => array(
				'facebook_app_id'  => array(InputOption::VALUE_OPTIONAL, 'Your Facebook App ID for this site. Create a new app at https://developers.facebook.com/apps/.', null),
				'facebook_page'    => array(InputOption::VALUE_OPTIONAL, 'Your Facebook Page to like. The default value is this URL, provided the `generator.template.web_root` option is also set correctly.', null),
				'google_plus'      => array(InputOption::VALUE_NONE, 'Whether or not to enable a Google +1 button.', false),
				'twitter_username' => array(InputOption::VALUE_OPTIONAL, 'Your Twitter username.', null),
			),
			'web_root' => array(InputOption::VALUE_OPTIONAL, 'The web root of the documentation URL. If the documentation homepage should live at `http://example.com/docs/index.html`, then the correct value would be `http://example.com/docs/` (with trailing slash).', null),
		),
	),

	// Configurations related to the User Guide content
	// 'guide' => array(
	// 	'root' => array(InputOption::VALUE_OPTIONAL, 'Where should we look for the documentation?', VANITY_PROJECT_WORKING_DIR . '/doc'),
	// ),
));
