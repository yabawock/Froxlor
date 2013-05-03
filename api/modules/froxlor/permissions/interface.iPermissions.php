<?php

/**
 * Froxlor API Permissions-Module interface
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
 * Interface iPermissions
 *
 * @copyright  (c) the authors
 * @author     Froxlor team <team@froxlor.org> (2010-)
 * @license    GPLv2 http://files.froxlor.org/misc/COPYING.txt
 * @category   Modules
 * @package    API
 * @since      0.99.0
 */
interface iPermissions {

	/**
	 * returns an array of all available permissions
	 *
	 * @param string $module optional return only permissions defined by given module
	 *
	 * @return array an array of all available permission-beans as array
	 */
	public static function listPermissions();

	/**
	 * returns a permission by given ident
	 *
	 * @param string $ident e.g. Module.permission
	 *
	 * @throws PermissionsException
	 * @return array the permissions-bean-data
	*/
	public static function statusPermission();

	/**
	 * adds a new permission to the database
	 *
	 * @param string $ident identifier for the permission, Module.permname
	 *
	 * @throws PermissionsException in case the permission already exists
	 * @return array permission-bean as array
	*/
	public static function addPermission();

	/**
	 * removes an existing permission from the database (if unused)
	 *
	 * @param string $ident identifier for the permission, Module.permname
	 *
	 * @throws PermissionsException in case the permission does not exist or is in use
	 * @return bool success = true
	*/
	public static function deletePermission();

	/**
	 * checks if a given user (id) has a given permission (ident)
	 * by searching the users groups for this permission
	 *
	 * @param int $userid
	 * @param string $ident e.g. Core.useAPI
	 *
	 * @throws PermissionsException
	 * @return bool allowed=true if user has permission
	*/
	public static function statusUserPermission();

	/**
	 * connects one or more permissions to a given group
	 *
	 * @param int|array $permissions id or list of id's of permissions to give the group
	 * @param string $group name of the group
	 *
	 * @throws PermissionsException
	 * @return bool success = true
	*/
	public static function addPermissionsToGroup();

}
