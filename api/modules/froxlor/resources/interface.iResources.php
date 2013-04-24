<?php

/**
 * Froxlor API Resources-Module interface
 *
 * PHP version 5
 *
 * This file is part of the Froxlor project.
 * Copyright (c) 2010- the Froxlor Team (see authors).
 *
 * For the full copyright and license information, please view the COPYING
 * file that was distributed with this source code. You can also view the
 * COPYING file online at http://files.froxlor.org/misc/COPYING.txt
 *
 * @copyright  (c) the authors
 * @author     Froxlor team <team@froxlor.org> (2010-)
 * @license    GPLv2 http://files.froxlor.org/misc/COPYING.txt
 * @category   Modules
 * @package    API
 * @since      0.99.0
 */

/**
 * Interface iResources
 *
 * @copyright  (c) the authors
 * @author     Froxlor team <team@froxlor.org> (2010-)
 * @license    GPLv2 http://files.froxlor.org/misc/COPYING.txt
 * @category   Modules
 * @package    API
 * @since      0.99.0
 */
interface iResources {

	/**
	 * List all stored resources
	 *
	 * @return array the resources-bean-data as array
	 */
	public static function listResources();

	/**
	 * returns a resource by given ident
	 *
	 * @param string $ident e.g. Core.maxloginattempts
	 *
	 * @throws ResourcesException
	 * @return array the resource-bean-data
	*/
	public static function statusResource();

	/**
	 * adds a new resources to the database
	 *
	 * @param string $ident e.g. Core.maxloginattempts
	 * @param mixed $default a default value for the resources, if empty -1 is used
	 *
	 * @throws ResourcesException if an equal resource exists
	 * @return array exported resource-bean of the new resource-entry
	*/
	public static function addResource();

	/**
	 * modifies a resource's default value, ident cannot be changed
	 *
	 * @param string $ident e.g. Core.maxloginattempts
	 * @param mixed $default a default value for the resources
	 *
	 * @throws ResourcesException if resource does not exists
	 * @return array exported resource-bean of the updated resource-entry
	*/
	public static function modifyResource();

	/**
	 * deletes a resources from the database (only if not in use)
	 *
	 * @param string $ident e.g. Core.maxloginattempts
	 *
	 * @throws ResourcesException if still in use or not found
	 * @return bool success = true
	*/
	public static function deleteResource();

}
