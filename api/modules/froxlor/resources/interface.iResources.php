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
	 * @return int id of the new resource-entry
	*/
	public static function addResource();

	/**
	 * connects a resource to a given user, identified
	 * by ident and userid, e.g.
	 * {'userid' => 1, 'ident' => 'Core.maxloginattempts'}
	 *
	 * @param int $userid
	 * @param string $ident e.g. Core.maxloginattempts
	 *
	 * @throws ResourcesException if the user does not exist
	 * @return bool|mixed success=true if successful otherwise a non-success-apiresponse
	*/
	public static function addResourceToUser();
}
