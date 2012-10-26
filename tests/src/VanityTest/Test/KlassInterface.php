<?php
namespace VanityTest\Test;

use SimpleXMLElement;

interface KlassInterface
{
	/**
	 * This is a description of a string method.
	 *
	 * @param  string $string This is the string parameter.
	 * @return string         This is the return type of the method.
	 */
	public function setString($string);

	/**
	 * This is a description of a integer method.
	 *
	 * @param  integer $number This is the integer parameter.
	 * @return integer         This is the return type of the method.
	 */
	public function setNumber($number);

	/**
	 * This is a description of a boolean method.
	 *
	 * @param  boolean $trueFalse This is the boolean parameter.
	 * @return boolean            This is the return type of the method.
	 */
	public function setBoolean($trueFalse);

	/**
	 * This is a description of an array method.
	 *
	 * @param  array $list This is the array parameter.
	 * @return array       This is the return type of the method.
	 */
	public function setArray(array $list);

	/**
	 * This is a description of a mixed method.
	 *
	 * @param  integer|string|SimpleXMLElement|array $mixed This is the mixed parameter.
	 * @return integer|string|SimpleXMLElement|array        This is the return type of the method.
	 */
	public function setMixed($mixed);

	/**
	 * This is a description of an SimpleXMLElement method.
	 *
	 * @param  SimpleXMLElement $xml This is the XML parameter.
	 * @return SimpleXMLElement      This is the return type of the method.
	 */
	public function setXML(SimpleXMLElement $xml);
}
