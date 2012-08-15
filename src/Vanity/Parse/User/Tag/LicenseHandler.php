<?php
/**
 * Copyright (c) 2009-2012 [Ryan Parman](http://ryanparman.com)
 * Copyright (c) 2011-2012 [Amazon Web Services, LLC](http://aws.amazon.com)
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


namespace Vanity\Parse\User\Tag;

use Vanity\Parse\User\Tag\HandlerInterface;
use Vanity\Parse\User\Tag\AbstractNameTypeDescription;

/**
 * The handler for @license tags.
 */
class LicenseHandler extends AbstractNameTypeDescription implements HandlerInterface
{
	public function process($elongate = false)
	{
		$return = parent::process($elongate);

		if (isset($return['type']))
		{
			// @license http://whateversclever.com/license <description>
			if (preg_match('/^https?\:/', $return['type']))
			{
				$return['uri'] = $return['type'];
			}

			// @license <description>
			else
			{
				// Automatically handle known license identifiers
				// Adding to this list? Submit a pull request!
				switch (strtolower($return['type']))
				{
					case 'agpl':
					case 'agpl3':
					case 'agpl-3.0':
						$return['uri'] = 'http://opensource.org/licenses/AGPL-3.0';
						$return['description'] = 'GNU Affero General Public License, Version 3';
						break;

					case 'apache2':
					case 'apache-2.0':
						$return['uri'] = 'http://opensource.org/licenses/Apache-2.0';
						$return['description'] = 'Apache License, Version 2.0';
						break;

					case 'bsd-3-clause':
					case 'bsd-1999':
						$return['uri'] = 'http://opensource.org/licenses/BSD-3-Clause';
						$return['description'] = 'BSD 3-clause License (1999)';
						break;

					case 'bsd-2-clause':
					case 'bsd-2008':
					case 'freebsd':
						$return['uri'] = 'http://opensource.org/licenses/BSD-2-Clause';
						$return['description'] = 'BSD 2-clause License (2008)';
						break;

					case 'gpl2':
					case 'gpl-2.0':
						$return['uri'] = 'http://opensource.org/licenses/GPL-2.0';
						$return['description'] = 'GNU General Public License, Version 2';
						break;

					case 'gpl':
					case 'gpl3':
					case 'gpl-3.0':
						$return['uri'] = 'http://opensource.org/licenses/GPL-3.0';
						$return['description'] = 'GNU General Public License, Version 3';
						break;

					case 'lgpl2':
					case 'lgpl-2.1':
						$return['uri'] = 'http://opensource.org/licenses/LGPL-2.1';
						$return['description'] = 'GNU Lesser General Public License, Version 2.1';
						break;

					case 'lgpl':
					case 'lgpl3':
					case 'lgpl-3.0':
						$return['uri'] = 'http://opensource.org/licenses/LGPL-3.0';
						$return['description'] = 'GNU Lesser General Public License, Version 3';
						break;

					case 'mit':
						$return['uri'] = 'http://opensource.org/licenses/MIT';
						$return['description'] = 'MIT License';
						break;

					case 'mpl':
					case 'mpl2':
					case 'mpl-2.0':
						$return['uri'] = 'http://opensource.org/licenses/MPL-2.0';
						$return['description'] = 'Mozilla Public License, Version 2';
						break;

					case 'php':
					case 'php3':
					case 'php-3.0':
					case 'php-3.01':
					case 'php-3.0.1':
						$return['uri'] = 'http://www.php.net/license/3_01.txt';
						$return['description'] = 'PHP License, Version 3.01';
						break;

					case 'w3c':
						$return['uri'] = 'http://opensource.org/licenses/W3C';
						$return['description'] = 'W3C Software Notice and License';
						break;

					case 'zlib':
					case 'zlib/libpng':
						$return['uri'] = 'http://opensource.org/licenses/Zlib';
						$return['description'] = 'Zlib/libpng License';
						break;

					default:
						$return['description'] = $return['type'] . ' ' . $return['description'];
						break;
				}
			}

			unset($return['type']);
		}

		return $return;
	}
}
