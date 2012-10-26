<?php
namespace VanityTest;

use VanityTest\Test\Klass;
use VanityTest\Test\KlassInterface;

/**
 * @uses Klass
 * @version 1.2.3
 *
 * @method string getString()
 * @method void setInteger(integer $integer)
 * @method setString(integer $integer)
 *
 * @method string getString(array $options) {
 *     This gets the string from something we've fetched.
 *
 *     @param array $options {
 *         This is a hash of options that can be passed to this method.
 *
 *         @type string  NAME      [description]
 *         @type string  "label"   [description]
 *         @type integer "type_id" [description]
 *         @type boolean "visible" [description]
 *         @type string  "default" [description]
 *     }
 *
 *     @return string The description of the string response.
 * }
 */
class Default extends Klass implements KlassInterface
{
	/**************************************************************************/
	// PROPERTIES

	/**
	 * This is an overridden property.
	 * @type stdClass
	 */
	public $override = null;


	/**************************************************************************/
	// METHODS

	/**
	 * {@inheritdoc}
	 */
	public function setString($string) {}

	/**
	 * {@inheritdoc}
	 */
	public function setNumber(integer $number) {}

	/**
	 * {@inheritdoc}
	 */
	public function setBoolean(boolean $trueFalse) {}

	/**
	 * {@inheritdoc}
	 */
	public function setArray(array $list) {}

	/**
	 * {@inheritdoc}
	 */
	public function setMixed($mixed) {}

	/**
	 * {@inheritdoc}
	 */
	public function setXML(SimpleXMLElement $xml) {}

	/**
	 * This is a method which accepts an option hash.
	 *
	 * @param array $options {
	 *     This is a hash of options that can be passed to this method.
	 *
	 *     @type string  NAME      [description]
	 *     @type string  "label"   [description]
	 *     @type integer "type_id" [description]
	 *     @type boolean "visible" [description]
	 *     @type string  "default" [description]
	 * }
	 */
	public function setHash(array $options) {}
}
