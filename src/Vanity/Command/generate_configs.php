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

		'formats' => array(InputOption::VALUE_OPTIONAL | InputOption::VALUE_IS_ARRAY, 'Which formats should we produce', array('html')),
		'input'   => array(InputOption::VALUE_OPTIONAL, 'Where should we start looking for code? (Use * for wildcard.)', '%VANITY.CONFIG_DIR%/output/%VANITY.VERSION%-%VANITY.STAGE%/json/*.json'),
		'output'  => array(InputOption::VALUE_OPTIONAL, 'Where should we put the documentation when we\'re done? (Variables: %FORMAT%, %STAGE%, %VERSION%)', '%VANITY.CONFIG_DIR%/output/%VANITY.VERSION%-%VANITY.STAGE%/%FORMAT%'),
	),

	// Configurations related to the User Guide content
	'guide' => array(
		'root' => array(InputOption::VALUE_OPTIONAL, 'Where should we look for the documentation?', VANITY_PROJECT_WORKING_DIR . '/doc'),
	),
));
