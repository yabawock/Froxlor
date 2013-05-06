<?php

/**
 * Froxlor API User-Module
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
 * Class User
 *
 * @copyright  (c) the authors
 * @author     Froxlor team <team@froxlor.org> (2013-)
 * @license    GPLv2 http://files.froxlor.org/misc/COPYING.txt
 * @category   Modules
 * @package    API
 * @since      0.99.0
 */
class User extends FroxlorModule {

	/**
	 * List all stored users. Please remember that the password
	 * and the apikey will *never* be output.
	 *
	 * @param int $ownerid optional select only users of this owner
	 * @param bool $show_all optional also return all connected data to this user (e.g. server)
	 *
	 * @throws UserException
	 * @return array the user-bean-data as array
	 */
	public static function listUser() {

		$ownerid = self::getIntParam('ownerid', true);
		$showall = self::getIntParam('show_all', true, 0);

		if ($showall !== true) {
			$showall = false;
		}

		if ($ownerid > 0) {
			// check the owner
			$owner = Database::load('users', $ownerid);
			if ($owner->id) {
				// get the owners users
				$users = Database::find('users', ' users_id = :owner', array(':owner' => $owner->id));
			} else {
				throw new UserException(404, 'Unknown user/owner with id #'.$ownerid);
			}
		} else {
			// get all users
			$users = Database::findAll('users');
		}

		// clean user info's (no passwords or api-keys
		$_usr_array = array();
		foreach ($users as $user) {
			// hide important data
			$user->apikey = null;
			$user->password = null;
			$_usr_array[] = $user;
		}

		// create array from beans
		$usr_array = Database::exportAll($_usr_array, $showall);

		// return all the users as array (api)
		return ApiResponse::createResponse(
				200,
				null,
				$usr_array
		);
	}

	/**
	 * returns a user by given name
	 *
	 * @param string $name name of the user
	 *
	 * @throws UserException
	 * @return array the users-bean-data
	 */
	public static function statusUser() {

		$name = self::getParam('name');

		// go find the user
		$user = Database::findOne('users', 'name = :name', array(':name' => $name));
			
		// if null, no user was found
		if ($user === null) {
			throw new UserException(404, 'User "'.$name.'" not found');
		}

		// hide important data
		$user->apikey = null;
		$user->password = null;

		// return it as array
		return ApiResponse::createResponse(200, null, Database::exportAll($user));
	}

	/**
	 * deletes a given user but only if the user is not owner of
	 * other users or if it's yourself - in that case a UserException is thrown
	 *
	 * @param int $id user-id of the user entity to remove
	 *
	 * @throws UserException
	 * @return bool success = true if deleted successfully
	 */
	public static function deleteUser() {
		// get id-parameter
		$id = self::getIntParam('id');
		// get user-bean
		$user = Database::load('users', $id);
		// check user
		if ($user->id) {
			// don't let the user delete itself
			$myself = self::getParam('_userinfo');
			if ($user->id == $myself->id) {
				throw new UserException(406, 'You cannot delete yourself');
			}
			// check if we're owner of others
			$others = $user->ownUsers;
			if (is_array($others) && count($others) > 0) {
				throw new UserException(406, 'The user "'.$user->name.'" (#'.$user->id.') still is parent/owner of other users. Cannot delete this user.');
			}

			// FIXME: call hook beforeDelete here so all modules can clean up

			// now finally
			Database::trash($user);
			// and return
			return ApiResponse::createResponse(200, null, array('success' => true));
		}
		// woops, there's no such user
		throw new UserException(404, 'Unknown user with id #'.$ownerid);
	}

	/**
	 * (non-PHPdoc)
	 * @see FroxlorModule::Core_moduleSetup()
	 */
	public function Core_moduleSetup() {
	}
}
