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
	'source' => array(

		'input'           => array(InputOption::VALUE_OPTIONAL, 'Where should we start looking for code? (Use * for wildcard.)', VANITY_PROJECT_WORKING_DIR . '/src/*.php'),
		'output'          => array(InputOption::VALUE_OPTIONAL, 'Where should we put the documentation when we\'re done? (Variables: %FORMAT%, %STAGE%, %VERSION%)', '%VANITY.CONFIG_DIR%/output/%VANITY.VERSION%-%VANITY.STAGE%/%FORMAT%'),
		'resolve_aliases' => array(InputOption::VALUE_OPTIONAL, 'Whether or not to resolve namespace aliases to fully-qualified namespaces for type lookups.', true),
		'todo'            => array(InputOption::VALUE_OPTIONAL, 'PCRE regex pattern for matching TODOs in the source code.', '/@?\s*(todo|fixme)(:|\s).+/i'),
		'viewsource'      => array(InputOption::VALUE_OPTIONAL, 'Point to an online location to view the source. (Variables: %LINE%, %PATH%)', null),

		// What should we exclude from the documentation?
		'exclude' => array(
			'classes'    => array(InputOption::VALUE_OPTIONAL, 'Which classes should we exclude? Pass an array of explicit names, or a PCRE regex pattern as a string.', null),
			'methods'    => array(InputOption::VALUE_OPTIONAL, 'Which methods should we exclude? Pass an array of explicit names, or a PCRE regex pattern as a string.', null),
			'visibility' => array(InputOption::VALUE_OPTIONAL, 'Which visibility types should we exclude?', 'private'),
		),

		// GitHub integration
		'github' => array(
			'user'       => array(InputOption::VALUE_OPTIONAL, 'The GitHub username to use for authenticating.', null),
			'pass'       => array(InputOption::VALUE_OPTIONAL, 'The GitHub password to use for authenticating. DO NOT STORE THIS IN YOUR CONFIG FILE!', null),
			'repo_owner' => array(InputOption::VALUE_OPTIONAL, 'The GitHub user or organization which owns the repository.', null),
			'repo_name'  => array(InputOption::VALUE_OPTIONAL, 'The GitHub repository name.', null),
			// 'commit_sha' => array(InputOption::VALUE_OPTIONAL, 'The SHA hash of the Git commit to use. The commit must have been pushed to GitHub. Requires the GitHub username and password to be set. Will use the latest commit SHA by default.', null),
		),
	),
));
