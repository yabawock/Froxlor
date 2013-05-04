<?php

/**
 * Froxlor API User-Module interface
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
 * Interface iUser
 *
 * @copyright  (c) the authors
 * @author     Froxlor team <team@froxlor.org> (2010-)
 * @license    GPLv2 http://files.froxlor.org/misc/COPYING.txt
 * @category   Modules
 * @package    API
 * @since      0.99.0
 */
interface iUser {

	/**
	 * List all stored users. Please remember that the password
	 * and the apikey will *never* be output.
	 *
	 * @param int $ownerid optional select only users of this owner
	 * @param boolean $show_all optional also return all connected data to this user (e.g. server)
	 *
	 * @throws UserException
	 * @return array the user-bean-data as array
	 */
	public static function listUser();

	/**
	 * returns a user by given name
	 *
	 * @param string $name name of the user
	 *
	 * @throws UserException
	 * @return array the users-bean-data as array
	*/
	public static function statusUser();

	/**
	 * deletes a given user but only if the user is not owner of
	 * other users or if it's yourself - in that case a UserException is thrown
	 *
	 * @param int $id user-id of the user entity to remove
	 *
	 * @throws UserException
	 * @return boolean success = true if deleted successfully
	*/
	public static function deleteUser();

}
