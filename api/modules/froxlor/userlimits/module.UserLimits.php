<?php

/**
 * Froxlor API UserLimits-Module
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
 * Class UserLimits
 *
 * @copyright  (c) the authors
 * @author     Froxlor team <team@froxlor.org> (2010-)
 * @license    GPLv2 http://files.froxlor.org/misc/COPYING.txt
 * @category   Modules
 * @package    API
 * @since      0.99.0
 */
class UserLimits extends FroxlorModule implements iUserLimits {

	/**
	 * @see iResources::addUserLimit()
	 *
	 * @param int $userid
	 * @param string $ident e.g. Core.maxloginattempts
	 * @param mixed $limit default is -1
	 *
	 * @throws UserLimitsException if the user does not exist
	 * @return bool|mixed success=true if successful otherwise a non-success-apiresponse
	 */
	public static function addUserLimit() {

		$ident = self::getParamIdent('ident', 2);
		$userid = self::getParam('userid');
		$limit = self::getIntParam('limit', true, -1);

		// get the resource
		$res_resp = Froxlor::getApi()->apiCall(
				'Resources.statusResource',
				array('ident' => implode('.', $ident))
		);

		// did we get the resource?
		if ($res_resp->getResponseCode() == 200) {

			// get response data
			$res_arr = $res_resp->getData();
			// load beans
			$resource = Database::load('resources', $res_arr['id']);
			$user = Database::load('users', $userid);

			// valid user?
			if ($user->id) {
				// check if the user already owns this resource
				foreach ($user->ownUserLimits as $res) {
					if ($res->resourceid == $resource->id) {
						throw new UserLimitsException(406, 'User already has resource "'.implode('.', $ident).'" assigned');
					}
				}
				$userlimit = Database::dispense('userlimits');
				$userlimit->resourceid = $resource->id;
				$userlimit->limit = $limit;
				$userlimit->inuse = 0;
				$ulid = Database::store($userlimit);
				$user->ownUserLimits[] = $userlimit;
				return ApiResponse::createResponse(200, null, array('success' => true));
			}

			// user not found
			throw new UserLimitsException(404, 'User with the id #'.$userid.' could not be found');
		}

		// return the response which is != 200
		return $res_resp->getResponse();
	}
}
