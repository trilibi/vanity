<?php
namespace VanityTest\Test;

use stdClass;
use VanityTest\Default;
use VanityTest\Exception\TestException;

/**
 * @author My Name
 * @author My Name <my.name@example.com>
 * @author My Name <http://example.com>
 * @author My Name <adn:example>
 *
 * @copyright 2012 My Company Name
 *
 * @global
 * @internal
 *
 * @license Apache-2.0
 * @license http://www.spdx.org/licenses/MIT MIT License
 *
 * @link http://example.com/my/bar Documentation of Foo.
 *
 * @package Klass
 *
 * @available 1.2.3
 * @since     1.2.3
 *
 * @fixme This is something that needs to be fixed.
 * @todo  This is something that needs to be fixed.
 *
 * @used-by Default
 */
class Klass
{
	/**
	 * This is a description of a string.
	 * @type string
	 */
	public $string = '';

	/**
	 * This is a description of a number.
	 * @type integer
	 */
	public $number = 123;

	/**
	 * This is a description of a boolean.
	 * @type boolean
	 */
	public $trueFalse = true;

	/**
	 * This is a description of an array.
	 * @type array
	 */
	public $list = array();

	/**
	 * This is a description of a mixed type.
	 * @type integer|string|SimpleXMLElement|array
	 */
	public $mixed = null;


	/**************************************************************************/
	// METHODS

	/**
	 * This is a description of an stdClass method.
	 *
	 * @api
	 *
	 * @param  stdClass $class This is the class parameter.
	 * @return stdClass        A standard class.
	 */
	public function setStandardClass(stdClass $class) {}

    /**
     * @alias setStandardClass()
     * @see setStandardClass()
     */
	public function setStdClass(stdClass $class) {}

	/**
	 * @deprecated
	 * @deprecated 1.0.0
	 * @deprecated No longer used by internal code and not recommended.
	 * @deprecated 1.0.0 No longer used by internal code and not recommended.
	 *
	 * @event <stdClass> vendor.task.all.complete Triggers when all of the tasks have been completed.
	 * @event <Klass>    vendor.task.failure      Triggers when a task fails to complete successfully.
	 * @event            onComplete               Triggers when the task has been completed.
	 * @event            vendor.task.all.complete Triggers when all of the tasks have been completed.
	 * @event            onBefore
	 * @event            onComplete
	 * @event            onAfter
	 *
	 * @throw  TestException
	 * @throws TestException
	 */
	public function test1() {}
}
