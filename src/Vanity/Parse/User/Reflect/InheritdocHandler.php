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


namespace Vanity\Parse\User\Reflect;

use Exception;
use ReflectionException;
use ReflectionMethod;
use ReflectionProperty;
use phpDocumentor\Reflection\DocBlock;
use phpDocumentor\Reflection\DocBlock\Tag as DBTag;
use Vanity\Config\Store as ConfigStore;
use Vanity\Console\Utilities as ConsoleUtil;
use Vanity\Exception\Exception as VanityException;
use Vanity\Exception\CouldNotResolveInheritdocException;
use Vanity\Exception\InheritdocInInterfaceException;
use Vanity\Exception\InheritdocInTraitException;
use Vanity\GlobalObject\Logger;
use Vanity\Parse\User\Reflect\AncestryHandler;
use Vanity\Parse\User\Tag;
use Vanity\Parse\User\InlineTag;
use Vanity\System\DocumentationInconsistencyCollector as Inconsistency;
use Vanity\System\Store as SystemStore;

/**
 * Handle tags for a class.
 *
 * @author Ryan Parman <http://ryanparman.com>
 * @link   http://vanitydoc.org
 */
class InheritdocHandler
{
	public static function resolve($reflected)
	{
		$is_method = false;
		$is_property = false;

		// Are we working with a property or a method?
		if ($reflected instanceof ReflectionMethod)
		{
			$is_method = true;
		}
		elseif ($reflected instanceof ReflectionProperty)
		{
			$is_property = true;
		}
		else
		{
			throw new Exception('Only methods and properties can be reflected with ' . get_called_class());
		}

		// Parse the docblock
		$docblock = new DocBlock($reflected->getDocComment());
		$found_description = false;
		$return = $reflected;

		// Save these for messaging
		$__class = $reflected->getDeclaringClass()->getName();
		$__kind = $reflected->getName();

		// Can we just do a straight-up inherit?
		// @todo: Do a better job of handling {@inheritdoc} according to the spec.
		try
		{
			while (!$found_description && strpos($docblock->getShortDescription(), '{@inheritdoc}') !== false)
			{
				// Start over...
				$found_description = false;

				// Log that we're starting...
				Logger::get()->{ConfigStore::get('log.info')}('{@inheritdoc} Starting resolution:', array(
					sprintf(
						"${__class}%s${__kind}%s",
						($is_method ? '::' : '::$'),
						($is_method ? '()' : '')
					),
				));

				// Grab a reference to the class containing the entity with the {@inheritdoc} tag
				$klass = $reflected->getDeclaringClass();

				// Is this an Interface?
				if ($klass->isInterface())
				{
					throw new InheritdocInInterfaceException(
						'The {@inheritdoc} tag is not resolvable from within Interfaces. Methods and properties should '
						. 'be fully-documented.');
				}

				// Is this a Trait?
				elseif (SystemStore::get('_.php54') && $klass->isTrait())
				{
					throw new InheritdocInTraitException(
						'The {@inheritdoc} tag is not resolvable from within Traits. Methods and properties should '
						. 'be fully-documented.');
				}

				// Are we using Interfaces?
				if (!$found_description && ($interface_count = count($klass->getInterfaces())) > 0)
				{
					$count = 1;
					foreach ($klass->getInterfaces() as $rinterface)
					{
						Logger::get()->{ConfigStore::get('log.info')}("{@inheritdoc} Checking Interface ${count}/${interface_count}:", array(
							$rinterface->getName(),
						));

						try
						{
							$return = $rinterface->getMethod($reflected->getName());

							Logger::get()->{ConfigStore::get('log.info')}('{@inheritdoc} Match!', array(
								$rinterface->getName(),
								$reflected->getName(),
								'Method'
							));

							$found_description = true;
							break 2;
						}
						catch (Exception $e)
						{
							try
							{
								$return = $rinterface->getProperty($reflected->getName());

								Logger::get()->{ConfigStore::get('log.info')}('{@inheritdoc} Match!', array(
									$rinterface->getName(),
									$reflected->getName(),
									'Property'
								));

								$found_description = true;
								break 2;
							}
							catch (Exception $e)
							{
								Logger::get()->{ConfigStore::get('log.info')}('{@inheritdoc} No match. Will keep looking...', array(
									$rinterface->getName(),
									$reflected->getName(),
								));
							}
						}

						$count++;
					}
				}

				// Are we using Traits?
				if (!$found_description && SystemStore::get('_.php54') && ($trait_count = count($klass->getTraits())) > 0)
				{
					$count = 1;
					foreach ($klass->getTraits() as $rtrait)
					{
						Logger::get()->{ConfigStore::get('log.info')}("{@inheritdoc} Checking Trait ${count}/${trait_count}:", array(
							$rtrait->getName(),
						));

						try
						{
							$return = $rtrait->getMethod($reflected->getName());

							Logger::get()->{ConfigStore::get('log.info')}('{@inheritdoc} Match!', array(
								$rtrait->getName(),
								$reflected->getName(),
								'Method'
							));

							$found_description = true;
							break 2;
						}
						catch (Exception $e)
						{
							try
							{
								$return = $rtrait->getProperty($reflected->getName());

								Logger::get()->{ConfigStore::get('log.info')}('{@inheritdoc} Match!', array(
									$rtrait->getName(),
									$reflected->getName(),
									'Property'
								));

								$found_description = true;
								break 2;
							}
							catch (Exception $e)
							{
								Logger::get()->{ConfigStore::get('log.info')}('{@inheritdoc} No match. Will keep looking...', array(
									$rtrait->getName(),
									$reflected->getName(),
								));
							}
						}

						$count++;
					}
				}

				// Are we extending a class?
				if ($klass->getParentClass())
				{
					// Continue climbing up the ancestry as necessary
					while (!$found_description && $klass->getParentClass())
					{
						// Rewrite the reference to $klass
						$klass = $klass->getParentClass();

						Logger::get()->{ConfigStore::get('log.info')}("{@inheritdoc} Checking the parent class:", array(
							$klass->getName(),
						));

						try
						{
							$return = $klass->getMethod($reflected->getName());

							Logger::get()->{ConfigStore::get('log.info')}('{@inheritdoc} Match!', array(
								$klass->getName(),
								$reflected->getName(),
								'Method'
							));

							$found_description = true;
							break 2;
						}
						catch (Exception $e)
						{
							try
							{
								$return = $klass->getProperty($reflected->getName());

								Logger::get()->{ConfigStore::get('log.info')}('{@inheritdoc} Match!', array(
									$klass->getName(),
									$reflected->getName(),
									'Property'
								));
								$found_description = true;
								break 2;
							}
							catch (Exception $e)
							{
								Logger::get()->{ConfigStore::get('log.info')}('{@inheritdoc} No match. Will keep looking...', array(
									$klass->getName(),
									$reflected->getName(),
								));
							}
						}
					}
				}

				// We couldn't find anything
				throw new CouldNotResolveInheritdocException('Leaving as-is. The tag will be viewable in the '
					. 'resulting documentation.');
			}
		}
		catch (InheritdocInInterfaceException $e)
		{
			$message = sprintf(
				"${__class}%s${__kind}%s",
				($is_method ? '::' : '::$'),
				($is_method ? '()' : '')
			);

			// Log that we're starting...
			Logger::get()->{ConfigStore::get('log.info')}('{@inheritdoc} ' . $e->getMessage(), array(
				$message
			));

			$formatter = ConsoleUtil::formatters();
			Inconsistency::add($message . $formatter->gold->apply(' => Could not resolve {@inheritdoc}. ' . $e->getMessage()));
		}
		catch (InheritdocInTraitException $e)
		{
			$message = sprintf(
				"${__class}%s${__kind}%s",
				($is_method ? '::' : '::$'),
				($is_method ? '()' : '')
			);

			// Log that we're starting...
			Logger::get()->{ConfigStore::get('log.info')}('{@inheritdoc} ' . $e->getMessage(), array(
				$message
			));

			$formatter = ConsoleUtil::formatters();
			Inconsistency::add($message . $formatter->gold->apply(' => Could not resolve {@inheritdoc}. ' . $e->getMessage()));
		}
		catch (CouldNotResolveInheritdocException $e)
		{
			$message = sprintf(
				"${__class}%s${__kind}%s",
				($is_method ? '::' : '::$'),
				($is_method ? '()' : '')
			);

			// Log that we're starting...
			Logger::get()->{ConfigStore::get('log.info')}('{@inheritdoc} ' . $e->getMessage(), array(
				$message
			));

			$formatter = ConsoleUtil::formatters();
			Inconsistency::add($message . $formatter->gold->apply(' => Could not resolve {@inheritdoc}. ' . $e->getMessage()));
		}
		catch (VanityException $e) {}
		catch (Exception $e) {}

		return $return;
	}
}
