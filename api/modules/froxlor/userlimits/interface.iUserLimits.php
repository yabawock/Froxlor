<?php

/**
 * Froxlor API UserLimits-Module interface
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
 * Interface iUserLimits
 *
 * @copyright  (c) the authors
 * @author     Froxlor team <team@froxlor.org> (2010-)
 * @license    GPLv2 http://files.froxlor.org/misc/COPYING.txt
 * @category   Modules
 * @package    API
 * @since      0.99.0
 */
interface iUserLimits {

	/**
	 * returns a user-limit by given user-id and ident, e.g.
	 * {'userid' => 1, 'ident' => 'Core.maxloginattempts' }
	 *
	 * @param int $userid
	 * @param string $ident e.g. Core.maxloginattempts
	 *
	 * @throws UserLimitsException
	 * @return array
	 */
	public static function statusUserLimit();

	/**
	 * returns all user-limits added to a given user-id
	 *
	 * @param int $userid
	 *
	 * @throws UserLimitsException if user not found
	 * @return array
	*/
	public static function listUserLimits();

	/**
	 * connects a resource to a given user identified by ident and userid, e.g.
	 * {'userid' => 1, 'ident' => 'Core.maxloginattempts' [, 'limit' => '3'] }
	 *
	 * @param int $userid
	 * @param string $ident e.g. Core.maxloginattempts
	 * @param mixed $limit default is -1
	 *
	 * @throws UserLimitsException if the user does not exist
	 * @return bool|mixed success=true if successful otherwise a non-success-apiresponse
	*/
	public static function addUserLimit();
}
