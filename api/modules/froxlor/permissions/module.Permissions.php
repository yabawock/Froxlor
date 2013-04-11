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
}
