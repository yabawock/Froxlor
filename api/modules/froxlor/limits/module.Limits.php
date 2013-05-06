<?php

/**
 * Froxlor API Limits-Module
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
 * Class Limits
 *
 * @copyright  (c) the authors
 * @author     Froxlor team <team@froxlor.org> (2013-)
 * @license    GPLv2 http://files.froxlor.org/misc/COPYING.txt
 * @category   Modules
 * @package    API
 * @since      0.99.0
 */
class Limits extends FroxlorModule {

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
	 * returns all limits added to a given user|server
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
	public static function modifyLimit() {

		$type = self::getIntParam('type', true, 0);
		$fid = self::getIntParam('id');
		$limitid = self::getIntParam('limitid');
		$inuse = self::getIntParam('inuse', true, null);
		$limit = self::getIntParam('limit', true, null);

		if ($type == 0) {
			$entity = Database::load('users', $fid);
		} elseif ($type == 1) {
			$entity = Database::load('servers', $fid);
		} else {
			throw new LimitsException(406, 'Invalid type number #'.$type);
		}

		// valid entity?
		if ($entity->id) {
			// check if the entity has this limit
			if (!isset($entity->ownLimits[$limitid])) {
				throw new LimitsException(404, 'The given limit could not be found in the entity ');
			}
			// get entities limits
			$mylimit = $entity->ownLimits[$limitid];
			if ($limit !== null) {
				if ($limit < $mylimit->inuse) {
					throw new LimitsException(
							406,
							'You cannot set the limit to a lower value than the entity already has in use (used '.$mylimit->inuse.' of '.$mylimit->limit.')'
					);
				}
				$mylimit->limit = $limit;
			}
			if ($inuse !== null) {
				if ($inuse > $mylimit->limit) {
					// get resource
					$res = Database::load('resources', $mylimit->resourceid);
					throw new LimitsException(
							406,
							'You cannot use more of resource "'.$res->module.'.'.$res->resource.'" as your limit is "'.$mylimit->limit.'"'
					);
				}
				$mylimit->inuse = $inuse;
			}
			// update if changed
			if ($mylimit->isTainted()) {
				Database::store($mylimit);
				$mylimit = Database::load('limits', $limitid);
			}
			$limit_array = $mylimit->export();
			return ApiResponse::createResponse(200, null, $limit_array);
		}
		throw new ServerException(404, "Limit with id #".$limitid." could not be found");
	}

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
	public static function deleteLimit() {

		$type = self::getIntParam('type', true, 0);
		$fid = self::getIntParam('id');
		$limitid = self::getIntParam('limitid');

		if ($type == 0) {
			$entity = Database::load('users', $fid);
		} elseif ($type == 1) {
			$entity = Database::load('servers', $fid);
		} else {
			throw new LimitsException(406, 'Invalid type number #'.$type);
		}

		// valid entity?
		if ($entity->id) {
			// check if the entity has this limit
			if (!isset($entity->ownLimits[$limitid])) {
				throw new LimitsException(404, 'The given limit could not be found in the entity ');
			}
			// get entities limits
			$mylimit = $entity->ownLimits[$limitid];
			// remove connection from entity
			unset($entity->ownLimits[$limitid]);
			// save entity
			Database::store($entity);
			// remove limit
			Database::trash($mylimit);
			// return success
			return ApiResponse::createResponse(200, null, array('success' => true));
		}
		throw new ServerException(404, "Limit with id #".$limitid." could not be found");
	}

	/**
	 * (non-PHPdoc)
	 * @see FroxlorModule::Core_moduleSetup()
	 */
	public function Core_moduleSetup() {
	}
}
