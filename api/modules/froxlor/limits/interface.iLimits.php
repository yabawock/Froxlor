<?php

/**
 * Froxlor API Limits-Module interface
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
 * Interface iLimits
 *
 * @copyright  (c) the authors
 * @author     Froxlor team <team@froxlor.org> (2010-)
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
	 * {'type' => 0, 'fid' => 1, 'ident' => 'Core.maxloginattempts' [, 'limit' => '3'] }
	 *
	 * @param int $type 0 = user, 1 = server (default is 0)
	 * @param int $id id of the entity (user or server)
	 * @param string $ident e.g. Core.maxloginattempts
	 * @param mixed $limit default is -1
	 *
	 * @throws LimitsException if the entity does not exist
	 * @return array|mixed limits-bean if successful otherwise a non-success-apiresponse
	*/
	public static function addLimit();
}
