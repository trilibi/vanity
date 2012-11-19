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

return array(

	// Configurations for the overall Vanity tool
	'vanity' => array(
		'view_config' => array(InputOption::VALUE_NONE,     'Display Vanity\'s configuration settings, and stop.', null),
		'config_dir'  => array(InputOption::VALUE_OPTIONAL, 'The directory which contains the project\'s Vanity configuration. Vanity will use this for resolving default paths.', SystemStore::get('_.project_config_dir')),
		'bootstrap'   => array(InputOption::VALUE_OPTIONAL, 'This file is loaded first. Useful for telling Vanity how to load project classes, settings constants, or other things.', '%VANITY.CONFIG_DIR%/bootstrap.php'),
		'name'        => array(InputOption::VALUE_OPTIONAL, 'The name of the product.', pathinfo(VANITY_PROJECT_WORKING_DIR, PATHINFO_FILENAME)),
		'stage'       => array(InputOption::VALUE_OPTIONAL, 'The stage that the project is currently in. Can be any ASCII value. (e.g., development, alpha, beta, rc, production).', 'development'),
		'version'     => array(InputOption::VALUE_OPTIONAL, 'The version number we should use.', 'latest'),
		'reports'     => array(InputOption::VALUE_OPTIONAL, 'The location to use for writing various reports that have been enabled.', '%VANITY.CONFIG_DIR%/reports'),
	),

	// How should we determine log severity?
	'log' => array(
		'aliases'  => array(InputOption::VALUE_OPTIONAL, 'Which severity should Alias/Ancestry Resolution messages have?', 'debug'),
		'commands' => array(InputOption::VALUE_OPTIONAL, 'Which severity should Command begin/end messages have?', 'debug'),
		'error'    => array(InputOption::VALUE_OPTIONAL, 'Which severity should Error messages have?', 'error'),
		'events'   => array(InputOption::VALUE_OPTIONAL, 'Which severity should Event Trigger messages have?', 'debug'),
		'info'     => array(InputOption::VALUE_OPTIONAL, 'Which severity should Notice messages have?', 'info'),
		'warn'     => array(InputOption::VALUE_OPTIONAL, 'Which severity should Warning messages have?', 'warn'),
	),

	// Should we write report files?
	'report' => array(
		'dependencies'    => array(InputOption::VALUE_OPTIONAL, 'Write a report if there are environment dependencies for the project.', false),
		'inconsistencies' => array(InputOption::VALUE_OPTIONAL, 'Write a report if there are documentation inconsistencies.', false),
		'todo'            => array(InputOption::VALUE_OPTIONAL, 'Write a report if there are TODOs.', false),
		'ungrouped'       => array(InputOption::VALUE_OPTIONAL, 'Write a report if there are ungrouped methods.', false),
	),

	// Should we show warnings on the Console in certain cases?
	'warn' => array(
		'dependencies'    => array(InputOption::VALUE_OPTIONAL, 'Warn on the console if there are environment dependencies for the project.', false),
		'inconsistencies' => array(InputOption::VALUE_OPTIONAL, 'Warn on the console if there are documentation inconsistencies.', false),
		'todo'            => array(InputOption::VALUE_OPTIONAL, 'Warn on the console if there are TODOs.', false),
		'ungrouped'       => array(InputOption::VALUE_OPTIONAL, 'Warn on the console if there are ungrouped methods.', false),
	),
);
