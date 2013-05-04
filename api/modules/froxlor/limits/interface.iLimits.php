<?php

/**
 * Froxlor API Limits-Module interface
 *
 * PHP version 5
 *
 * This file is part of the Froxlor project.
 * Copyright (c) 2013- the Froxlor Team (see authors).
 *
 * For the full copyright and license information, please view the COPYING
 * file that was distributed with this source code. You can also view the
 * COPYING file online at http://files.froxlor.org/misc/COPYING.txt
 *
 * @copyright  (c) the authors
 * @author     Froxlor team <team@froxlor.org> (2013-)
 * @license    GPLv2 http://files.froxlor.org/misc/COPYING.txt
 * @category   Modules
 * @package    API
 * @since      0.99.0
 */

/**
 * Interface iLimits
 *
 * @copyright  (c) the authors
 * @author     Froxlor team <team@froxlor.org> (2013-)
 * @license    GPLv2 http://files.froxlor.org/misc/COPYING.txt
 * @category   Modules
 * @package    API
 * @since      0.99.0
 */
interface iLimits {

	/**
	 * returns a limit by given id and ident, e.g.
	 * {'fid' => 1, 'ident' => 'Core.maxloginattempts' }
	 *
	 * @param int $type 0 = user, 1 = server (default is 0)
	 * @param int $id id of the entity (user or server)
	 * @param string $ident e.g. Core.maxloginattempts
	 *
	 * @throws LimitsException
	 * @return array
	 */
	public static function statusLimit();

	/**
	 * returns all limits added to a given user|server
	 *
	 * @param int $type 0 = user, 1 = server (default is 0)
	 * @param int $id id of the entity (user or server)
	 *
	 * @throws LimitsException if entity not found
	 * @return array
	*/
	public static function listLimits();

	/**
	 * connects a resource to a given entity identified by ident and id, e.g.
	 * {'type' => 0, 'id' => 1, 'ident' => 'Core.maxloginattempts' [, 'limit' => '3'] }
	 *
	 * @param int $type 0 = user, 1 = server (default is 0)
	 * @param int $id id of the entity (user or server)
	 * @param string $ident e.g. Core.maxloginattempts
	 * @param int $limit default is -1
	 *
	 * @throws LimitsException if the entity does not exist
	 * @return array|mixed limits-bean if successful otherwise a non-success-apiresponse
	*/
	public static function addLimit();

	/**
	 * Modify an entity limit (user|server) identified by id and a
	 * limit to modify identified by limitid. Sets new inuse and/or new limit value
	 *
	 * {'type' => 0, 'id' => 1, 'limitid' => 2 [, 'inuse' => x [, 'limit' => y]] }
	 *
	 * @param int $type 0 = user, 1 = server (default is 0)
	 * @param int $id id of the entity (user or server)
	 * @param int $limitid id of the limit to modify
	 * @param int $inuse optional
	 * @param int $limit optional
	 *
	 * @throws LimitsException if the entity does not exist
	 * @return array|mixed limits-bean if successful otherwise a non-success-apiresponse
	*/
	public static function modifyLimit();

	/**
	 * delete a limit identified by limitid from a given resource identified by type and id
	 * {'type' => 0, 'id' => 1, 'limitid' => 2}
	 *
	 * @param int $type 0 = user, 1 = server (default is 0)
	 * @param int $id id of the entity (user or server)
	 * @param int $limitid id of the limit to delete
	 *
	 * @throws LimitsException if the entity does not exist
	 * @return bool success = true
	*/
	public static function deleteLimit();
}
