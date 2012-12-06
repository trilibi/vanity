<?php
namespace VanityTest;

require_once __DIR__ . '/Test/KlassInterface.php';
require_once __DIR__ . '/Test/Klass.php';
require_once __DIR__ . '/Exception/TestException.php';

use SimpleXMLElement;
use VanityTest\Test\Klass;
use VanityTest\Test\KlassInterface;

/**
 * This internal description has a simple description. It also has a nested
 * {@internal Description tag.} tag.
 *
 * We also have a {@see Klass} and {@see KlassInterface Description.}
 * class references, a {@see setHash()} method reference, a
 * {@see SimpleXMLElement::asXML()} remote method reference, and a
 * {@see $override} property reference.
 *
 * Nested: {@internal}
 *
 * I also have a link to {@link http://google.com Google}, and a test of the
 * service resolution: {@link adn:skyzyx}. Oh, and here's a cool
 * {@example http://example.com/source.phps} and another one with a description:
 * {@example http://example.com/source.phps My example source}.
 *
 * @uses Klass
 * @version 1.2.3
 *
 * @method string getString()
 * @method void setInteger(integer $integer)
 * @method setString1(integer $integer, Klass $class)
 *
 * @method string getString2(integer $integer, string &$string, SimpleXMLElement $xml='<?xml>', array $options = array()) {
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
 *
 * @deprecated
 * @example  http://example.com/source.phps
 * @example  http://example.com/source.phps Source code example.
 */
class DefaultKlass extends Klass implements KlassInterface
{
	/**************************************************************************/
	// PROPERTIES

	/**
	 * This is an overridden property.
	 * @type stdClass
	 */
	public $override = null;

	/**
	 * {@inheritdoc}
	 */
	public $mixed = null;


	/**************************************************************************/
	// METHODS

	/**
	 * {@inheritdoc}
	 */
	public function setString($string) {}

	/**
	 * {@inheritdoc}
	 */
	public function setNumber($number) {}

	/**
	 * {@inheritdoc}
	 */
	public function setBoolean($trueFalse) {}

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
	 *
	 * @deprecated
	 */
	public function setHash(array $options) {}

	/**
	 * {@inheritdoc}
	 */
	public function test1() {}
}
