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


namespace Vanity\Parse\User\Reflect;

use ReflectionClass;
use ReflectionException;
use Vanity\Parse\User\Reflect\AncestryHandler;
use Vanity\System\Store as SystemStore;

/**
 * Handle constants for a class.
 *
 * @author Ryan Parman <http://ryanparman.com>
 * @link   http://vanitydoc.org
 */
class ConstantHandler
{
	/**
	 * The {@see ReflectionClass} which represents the class to work with.
	 * @var ReflectionClass
	 */
	protected $class;

	/**
	 * Storage for the constants.
	 * @var array
	 */
	protected $constants;

	/**
	 * Storage for ancestry.
	 * @var AncestryHandler
	 */
	public $ancestry;

	/**
	 * Constructs a new instance of this class.
	 *
	 * @param ReflectionClass $class    The class to work with.
	 * @param AncestryHandler $ancestry  The ancestry data for the class.
	 */
	public function __construct(ReflectionClass $class, AncestryHandler $ancestry)
	{
		$this->class = $class;
		$this->constants = array();
		$this->ancestry = $ancestry;
	}

	/**
	 * Retrieve the constants for the class.
	 *
	 * @return array A list of constants.
	 */
	public function getConstants()
	{
		$rclass_constants = $this->class->getConstants();
		ksort($rclass_constants);

		// Add constants
		foreach ($rclass_constants as $rconstant_name => $rconstant_value)
		{
			if (!isset($this->constants['count']))
			{
				$this->constants['count'] = count($rclass_constants);
			}

			if (!isset($this->constants['constant']))
			{
				$this->constants['constant'] = array();
			}

			$entry = array();
			$entry['name'] = $rconstant_name;
			$entry['value'] = $rconstant_value;
			$entry['type'] = gettype($rconstant_value);

			// Where are we?
			SystemStore::add('_.current', $this->class->getName() . '::' . $rconstant_name);

			$this->constants['constant'][] = $entry;
		}

		return $this->constants;
	}
}
