<?php

/**
 * Froxlor API Limits-Module
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
 * Class Limits
 *
 * @copyright  (c) the authors
 * @author     Froxlor team <team@froxlor.org> (2010-)
 * @license    GPLv2 http://files.froxlor.org/misc/COPYING.txt
 * @category   Modules
 * @package    API
 * @since      0.99.0
 */
class Limits extends FroxlorModule implements iLimits {

	/**
	 * @see iLimits::statusLimit()
	 *
	 * @param int $type 0 = user, 1 = server (default is 0)
	 * @param int $id id of the entity (user or server)
	 * @param string $ident e.g. Core.maxloginattempts
	 *
	 * @throws LimitsException
	 * @return array
	 */
	public static function statusLimit() {

		$type = self::getIntParam('type', true, 0);
		$ident = self::getParamIdent('ident', 2);
		$fid = self::getIntParam('id');

		if ($type == 0) {
			$entity = Database::load('users', $fid);
		} elseif ($type == 1) {
			$entity = Database::load('servers', $fid);
		} else {
			throw new LimitsException(406, 'Invalid type number #'.$type);
		}

		// valid entity?
		if ($entity->id) {
			// check if the entity already owns this resource
			foreach ($entity->ownLimits as $res) {
				$ul = Database::load('resources', $res->resourceid);
				if ($ul->module == $ident[0]
						&& $ul->resource == $ident[1]
				) {
					// return the limit-bean
					return ApiResponse::createResponse(200, null, $res->export());
				}
			}

			// resource-limit for entity not found
			throw new LimitsException(404, 'Entity has no resource "'.implode('.', $ident).'"');
		}

		// entity not found
		throw new LimitsException(404, 'Entity with the id #'.$fid.' could not be found');
	}

	/**
	 * @see iLimits::listLimits()
	 *
	 * @param int $type 0 = user, 1 = server (default is 0)
	 * @param int $id id of the entity (user or server)
	 *
	 * @throws LimitsException if entity not found
	 * @return array
	 */
	public static function listLimits() {

		$type = self::getIntParam('type', true, 0);
		$fid = self::getIntParam('id');

		if ($type == 0) {
			$entity = Database::load('users', $fid);
		} elseif ($type == 1) {
			$entity = Database::load('servers', $fid);
		} else {
			throw new LimitsException(406, 'Invalid type number #'.$type);
		}

		// valid entity?
		if ($entity->id) {
			$limits = array();
			// check if the entity already owns this resource
			foreach ($entity->ownLimits as $res) {
				$limits[] = $res->export();
			}
			// return the limits
			return ApiResponse::createResponse(200, null, $limits);
		}

		// entity not found
		throw new LimitsException(404, 'Entity with the id #'.$fid.' could not be found');

	}

	/**
	 * @see iLimits::addLimit()
	 *
	 * @param int $type 0 = user, 1 = server (default is 0)
	 * @param int $id id of the entity (user or server)
	 * @param string $ident e.g. Core.maxloginattempts
	 * @param mixed $limit default is -1
	 *
	 * @throws LimitsException if the entity does not exist
	 * @return array|mixed limits-bean if successful otherwise a non-success-apiresponse
	 */
	public static function addLimit() {

		$type = self::getIntParam('type', true, 0);
		$fid = self::getIntParam('id');
		$ident = self::getParamIdent('ident', 2);
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

			if ($type == 0) {
				$entity = Database::load('users', $fid);
			} elseif ($type == 1) {
				$entity = Database::load('servers', $fid);
			} else {
				throw new LimitsException(406, 'Invalid type number #'.$type);
			}

			// valid entity?
			if ($entity->id) {
				// check if the entity already owns this resource
				foreach ($entity->ownLimits as $res) {
					if ($res->resourceid == $resource->id) {
						throw new LimitsException(406, 'Entity already has resource "'.implode('.', $ident).'" assigned');
					}
				}
				$elimit = Database::dispense('limits');
				$elimit->resourceid = $resource->id;
				// if no limit given, use the resources default
				$elimit->limit = ($limit == -1 ? $resource->default : $limit);
				$elimit->inuse = 0;
				$ulid = Database::store($elimit);
				$entity->ownLimits[] = Database::load('limits', $ulid);
				Database::store($entity);
				return ApiResponse::createResponse(200, null, $elimit->export());
			}

			// entity not found
			throw new LimitsException(404, 'Entity with the id #'.$fid.' could not be found');
		}

		// return the response which is != 200
		return $res_resp->getResponse();
	}

	/**
	 * (non-PHPdoc)
	 * @see FroxlorModule::Core_moduleSetup()
	 */
	public function Core_moduleSetup() {
	}
}
