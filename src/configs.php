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


/********************************************************/
// AVAILABLE CONFIGURATION-RELATED OPTIONS

use Symfony\Component\Console\Input\InputOption;

/*
InputOption::VALUE_NONE
InputOption::VALUE_REQUIRED
InputOption::VALUE_OPTIONAL
InputOption::VALUE_IS_ARRAY
*/

return array(

	// Configurations for the overall Vanity tool
	'vanity' => array(
		'bootstrap' => array(InputOption::VALUE_OPTIONAL, 'This file is loaded first. Useful for telling Vanity how to load project classes, settings constants, or other things.', VANITY_PROJECT_CONFIG_DIR . '/bootstrap.php'),
		'name'      => array(InputOption::VALUE_OPTIONAL, 'The name of the product.', pathinfo(VANITY_PROJECT_WORKING_DIR, PATHINFO_FILENAME)),
		'version'   => array(InputOption::VALUE_OPTIONAL, 'The version number we should use.', 'latest'),
	),

	// Configurations related to the API Reference
	'api' => array(

		'formats'    => array(InputOption::VALUE_OPTIONAL, 'Comma-separated list of one or more documentation formats. JSON is used internally, so you get this one no matter what.', NULL),
		'input'      => array(InputOption::VALUE_OPTIONAL, 'Where should we start looking for code? (Use * for wildcard.)', VANITY_PROJECT_WORKING_DIR . '/src/*.php'),
		'output'     => array(InputOption::VALUE_OPTIONAL, 'Where should we put the documentation when we\'re done? (Variables: format, stage, tag)', VANITY_PROJECT_CONFIG_DIR . '/output/%VERSION%%STAGE%/%FORMAT%'),
		'readme'     => array(InputOption::VALUE_OPTIONAL, 'The file to use as the default page.', 'README.*'),
		'stage'      => array(InputOption::VALUE_OPTIONAL, 'The stage that the project is currently in. Can be any ASCII value. (e.g., development, alpha, beta, rc, production).', ''),
		'todo'       => array(InputOption::VALUE_OPTIONAL, 'PCRE regex pattern for matching TODOs in the source code.', '/@?\s*(todo|fixme)(:|\s).+/i'),
		'viewsource' => array(InputOption::VALUE_OPTIONAL, 'Point to an online location to view the source. (Variables: line, path)', NULL),

		// What should we exclude from the documentation?
		'exclude' => array(
			'classes'    => array(InputOption::VALUE_OPTIONAL, 'Which classes should we exclude? Pass an array of explicit names, or a PCRE regex pattern as a string.', '/Exception/i'),
			'methods'    => array(InputOption::VALUE_OPTIONAL, 'Which methods should we exclude? Pass an array of explicit names, or a PCRE regex pattern as a string.', '/__([a-z]+)/i'),
			'visibility' => array(InputOption::VALUE_OPTIONAL, 'Which visibility types should we exclude?', 'private'),
		),

		// Should we show warnings on the Console in certain cases?
		'warn' => array(
			'todo'      => array(InputOption::VALUE_OPTIONAL, 'Warn on the console if there are TODOs.', 'false'),
			'ungrouped' => array(InputOption::VALUE_OPTIONAL, 'Warn on the console if there are ungrouped methods.', 'false'),
		),
	),

	// Configurations related to the User Guide content
	'guide' => array(
		'root' => array(InputOption::VALUE_OPTIONAL, 'Where should we look for the documentation?', VANITY_PROJECT_WORKING_DIR . '/doc'),
	),
);