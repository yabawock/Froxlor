<?php
/**
 * This file is part of the Froxlor project.
 * Copyright (c) 2011 the Froxlor Team (see authors).
 *
 * For the full copyright and license information, please view the COPYING
 * file that was distributed with this source code. You can also view the
 * COPYING file online at http://files.froxlor.org/misc/COPYING.txt
 *
 * @copyright  (c) the authors
 * @author     Froxlor team <team@froxlor.org> (2011-)
 * @license    GPLv2 http://files.froxlor.org/misc/COPYING.txt
 * @package    Classes
 *
 */

/**
 * Froxlor - The main container
 *
 * This static class will be a static container callable from everywhere. It holds
 * all global objects needed in Froxlor, i.e. Smarty, the current user etc.
 * Usage: Froxlor::addObject('smarty', new Smarty()); Froxlor::getSmarty()->...
 */
class Froxlor
{
	/**
	 * The main variable of Froxlor
	 *
	 * This variable contains all objects which
	 * need to be available anytime in Froxlor
	 * @var array All assigned objects
	 */
	private static $objects = array();

	/**
	 * The constructor is private, since this class should only be used statically
	 */
	private function _construct() {}

	/**
	 * Add a new object to be stored
	 *
	 * Adding a new object to this container is easy:
	 * Froxlor::addObject('smarty', new Smarty());
	 * Now the object is available everywhere with Froxlor::getSmarty()
	 * Please note: The get - functionname  will always be camelCase
	 *
	 * @param string $name Name of the new object
	 * @param object $object The already initialized object
	 * @return bool False if the object already existed, true if object was added
	 */
	static function addObject($name, $object)
	{
		// We already have an object with this name stored
		// Prevent overwritting by accident
		if (isset(self::$objects[ucfirst($name)]))
		{
			return false;
		}

		// Add the object to our storage
		self::$objects[ucfirst($name)] = $object;
		return true;
	}

	/**
	 * Remove an object from storage
	 *
	 * Delete a stored object from our storage.
	 * Please note: the object will be destroyed in this progress,
	 * all data stored will be lost if not saved otherwise
	 *
	 * @param string $name Name of the object to be removed
	 * @return bool False if the object did not exist, true if object was deleted
	 */
	static function deleteObject($name)
	{
		// Do we have an object with this name stored?
		if (!isset(self::$objects[ucfirst($name)]))
		{
			return false;
		}

		// Remove the object from our storage
		unset(self::$objects[ucfirst($name)]);
		return true;
	}

	/**
	 * Return a stored object
	 *
	 * The name of the object is derived from the called function
	 * Always access an object with "get<Objectname>()". The name of
	 * the object has to start with an uppercase letter and the rest is
	 * lowercase. If the object is not stored yet, false will be returned.
	 *
	 * @param string $funcname Name of the function being called
	 * @param string $arguments All arguments given at the functioncall
	 * @return object|bool The object if it exists or false
	 */
	static function __callStatic($funcname, $arguments)
	{
		// Does the functioncall match our schema "getObject"?
		if (preg_match('/^get([A-Z][a-z]+)$/', $funcname, $matches))
		{
			// Yes, it does, but does the object exist in our storage?
			if (isset(self::$objects[$matches[1]]))
			{
				// Yes, let's return it
				return self::$objects[$matches[1]];
			}
		}

		// Either the object does not exist in our storage or the function
		// was called incorrect
		return false;
	}
}