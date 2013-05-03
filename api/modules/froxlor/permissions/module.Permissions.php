<?php

/**
 * Froxlor API Permissions-Module
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
 * Class Permissions
 *
 * @copyright  (c) the authors
 * @author     Froxlor team <team@froxlor.org> (2010-)
 * @license    GPLv2 http://files.froxlor.org/misc/COPYING.txt
 * @category   Modules
 * @package    API
 * @since      0.99.0
 */
class Permissions extends FroxlorModule implements iPermissions {

	/**
	 * @see iPermissions::listPermissions()
	 *
	 * @param string $module optional return only permissions defined by given module
	 *
	 * @return array an array of all available permission-beans as array
	 */
	public static function listPermissions() {

		$module = self::getParam('module', true, null);

		$permissions = array();
		if ($module != null) {
			$perms = Database::find('permissions', ' module = :mod ', array(':mod' => $module));
		} else {
			// get all
			$perms = Database::findAll('permissions',' ORDER BY module');
		}

		// create array from beans
		foreach ($perms as $sbean) {
			$permissions[] = $sbean->export();
		}

		// return all the settings as array (api)
		return ApiResponse::createResponse(
				200,
				null,
				$permissions
		);
	}

	/**
	 * @see iPermissions::statusPermission()
	 *
	 * @param string $ident e.g. Module.name
	 *
	 * @throws PermissionsException
	 * @return array the permissions-bean-data
	 */
	public static function statusPermission() {

		$ident = self::getParamIdent('ident', 2);

		// set database-parameter
		$dbparam = array(
				':mod' => $ident[0],
				':perm' => $ident[1]
		);

		// go find the permission
		$permission = Database::findOne('permissions', 'module = :mod AND name = :perm', $dbparam);

		// if null, no permission was found
		if ($permission === null) {
			throw new PermissionsException(404, 'Permission "'.implode('.', $ident).'" not found');
		}

		// return it as array
		return ApiResponse::createResponse(200, null, $permission->export());
	}

	/**
	 * @see iPermissions::addPermission()
	 *
	 * @param string $ident identifier for the permission, Module.name
	 *
	 * @throws PermissionsException in case the permission already exists
	 * @return array permission-bean as array
	 */
	public static function addPermission() {

		$ident = self::getParamIdent('ident', 2);

		// check if it already exists
		$res_check = Froxlor::getApi()->apiCall('Permissions.statusPermission', array('ident' => implode('.', $ident)));
		if ($res_check->getResponseCode() == 200) {
			throw new ResourcesException(406, 'The permission "'.implode('.', $ident).'" does already exist');
		}

		// create new bean
		$perm = Database::dispense('permissions');
		$perm->module = $ident[0];
		$perm->name = $ident[1];
		$permid = Database::store($perm);

		$perm = Database::load('permissions', $permid);
		// return success and the bean
		return ApiResponse::createResponse(200, null, $perm->export());
	}

	/**
	 * @see iPermissions::deletePermission()
	 *
	 * @param string $ident identifier for the permission, Module.permname
	 *
	 * @throws PermissionsException in case the permission does not exist or is in use
	 * @return bool success = true
	 */
	public static function deletePermission() {

		$ident = self::getParamIdent('ident', 2);

		// get permission
		$perm_check = Froxlor::getApi()->apiCall('Permissions.statusPermission', array('ident' => implode('.', $ident)));
		// check responsecode
		if ($perm_check->getResponseCode() != 200) {
			// return non-success message
			return $perm_check->getResponse();
		}
		// get id from response
		$permid = $perm_check->getData()['id'];
		// check if in use (groups)
		$inuse = Database::find('groups_permissions', ' permissions_id = ? ', array($permid));
		if (is_array($inuse) && count($inuse) > 0) {
			throw new PermissionsException(403, 'The permission "'.implode('.', $ident).'" cannot be deleted as it is in use');
		}
		// delete it
		$perm = Database::load('permissions', $permid);
		Database::trash($perm);
		// return bean as array
		return ApiResponse::createResponse(200, null, array('success' => true));
	}

	/**
	 * @see iPermissions::statusUserPermission()
	 *
	 * @param int $userid
	 * @param string $ident e.g. Core.useAPI
	 *
	 * @throws PermissionsException
	 * @return bool allowed=true if user has permission
	 */
	public static function statusUserPermission() {

		$userid = self::getParam('userid');
		$params = self::getParamIdent('ident', 2);

		$user = Database::load('users', $userid);

		$allowed = false;
		if ($user->id) {
			if (is_array($user->sharedGroups)) {
				$groups = $user->sharedGroups;
				foreach($groups as $group) {
					$perms = $group->withCondition(
							' module = :mod AND name = :name ',
							array(':mod' => $params[0], ':name' => $params[1])
					)->sharedPermissions;

					$keys = array_keys($perms);
					if (count($keys) > 0) {
						$perm = Database::load('permissions', $keys[0]);
						if ($perm->id && $perm->name == $params[1]) {
							return ApiResponse::createResponse(
									200,
									null,
									array('allowed' => true)
							);
						}
					}
				}
			}
		}
		throw new PermissionsException(403, 'You are not allowed to access '.$params[0].'::'.$params[1]);

	}

	/**
	 * @see iPermissions::addPermissionsToGroup();
	 *
	 * @param int|array $permissions id or list of id's of permissions to give the group
	 * @param string $group name of the group
	 *
	 * @throws PermissionsException
	 * @return bool success = true
	 */
	public static function addPermissionsToGroup() {
		// id's
		$perms = self::getParam('permissions');
		// name
		$group = self::getParam('group');

		if (!is_array($perms)) {
			$perms = array($perms);
		}

		$grp_check = Froxlor::getApi()->apiCall('Groups.statusGroup', array('name' => $group));
		// check responsecode
		if ($grp_check->getResponseCode() != 200) {
			throw new PermissionsException(404, 'Group "'.$group.'" could not be found');
		}

		$grp = Database::load('groups', $grp_check->getData()[0]['id']);
		if ($grp->id) {
			// TODO: check which of these permission are already connected to the group
			foreach ($perms as $p) {
				$permission = Database::load('permissions', $p);
				if ($permission->id) {
					$grp->sharedPermissions[] = $permission;
				}
				// just skip if not exists but log warning
				ApiLogger::warn('Permission with id #'.$p.' could not be found');
			}
			Database::store($grp);
			return ApiResponse::createResponse(200, null, array('success' => true));
		}
		throw new PermissionsException(404, 'Group "'.$group.'" could not be found');
	}

	/**
	 * (non-PHPdoc)
	 * @see FroxlorModule::Core_moduleSetup()
	 */
	public function Core_moduleSetup() {
	}
}
