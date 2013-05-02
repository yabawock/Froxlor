<?php

/**
 * Froxlor API Resources-Module
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
 * Class Resources
 *
 * @copyright  (c) the authors
 * @author     Froxlor team <team@froxlor.org> (2010-)
 * @license    GPLv2 http://files.froxlor.org/misc/COPYING.txt
 * @category   Modules
 * @package    API
 * @since      0.99.0
 */
class Resources extends FroxlorModule implements iResources {

	/**
	 * @see iResources::listResources()
	 *
	 * @return array the resources-bean-data as array
	 */
	public static function listResources() {
		// get all resources
		$resources = Database::findAll('resources', ' ORDER BY module ASC, resource ASC');
		// create array from beans
		$res_array = Database::exportAll($resources, false);
		// return all the resources as array (api)
		return ApiResponse::createResponse(
				200,
				null,
				$res_array
		);
	}

	/**
	 * @see iResources::statusResource()
	 *
	 * @param string $ident e.g. Core.maxloginattempts
	 *
	 * @throws ResourcesException
	 * @return array the resource-bean-data
	 */
	public static function statusResource() {

		$ident = self::getParamIdent('ident', 2);

		// set database-parameter
		$dbparam = array(
				':mod' => $ident[0],
				':res' => $ident[1]
		);

		// go find the resource
		$resource = Database::findOne('resources', 'module = :mod AND resource = :res', $dbparam);

		// if null, no resource was found
		if ($resource === null) {
			throw new ResourcesException(404, 'Resource "'.implode('.', $ident).'" not found');
		}

		// return it as array
		return ApiResponse::createResponse(200, null, $resource->export());
	}

	/**
	 * @see iResources::addResource()
	 *
	 * @param string $ident e.g. Core.maxloginattempts
	 * @param mixed $default a default value for the resources, if empty -1 is used
	 *
	 * @throws ResourcesException if an equal resource exists
	 * @return array exported resource-bean of the new resource-entry
	 */
	public static function addResource() {

		$ident = self::getParamIdent('ident', 2);
		$default = self::getParam('default', true, -1);

		// check if it already exists
		$res_check = Froxlor::getApi()->apiCall('Resources.statusResource', array('ident' => implode('.', $ident)));
		if ($res_check->getResponseCode() == 200) {
			throw new ResourcesException(406, 'The resource "'.implode('.', $ident).'" does already exist');
		}

		// create new bean
		$res = Database::dispense('resources');
		$res->module = $ident[0];
		$res->resource = $ident[1];
		$res->default = $default;
		$resid = Database::store($res);

		$res = Database::load('resources', $resid);
		// return the bean as array
		return ApiResponse::createResponse(200, null, $res->export());
	}

	/**
	 * modifies a resource's default value, ident cannot be changed
	 *
	 * @param string $ident e.g. Core.maxloginattempts
	 * @param mixed $default a default value for the resources
	 *
	 * @throws ResourcesException if resource does not exists
	 * @return array exported resource-bean of the updated resource-entry
	 */
	public static function modifyResource() {

		$ident = self::getParamIdent('ident', 2);
		$default = self::getParam('default', true, null);

		// get resource
		$res_check = Froxlor::getApi()->apiCall('Resources.statusResource', array('ident' => implode('.', $ident)));
		// check responsecode
		if ($res_check->getResponseCode() != 200) {
			// return non-success message
			return $res_check->getResponse();
		}
		// get id from response
		$resid = $res_check->getData()['id'];
		// load bean
		$res = Database::load('resources', $resid);
		// check for changes
		if ($default !== null) {
			$res->default = $default;
			// did the value change?
			if ($res->isTainted()) {
				// update
				Database::store($res);
				$res = Database::load('resources', $resid);
			}
		}
		// return bean as array
		return ApiResponse::createResponse(200, null, $res->export());
	}

	/**
	 * deletes a resources from the database (only if not in use)
	 *
	 * @param string $ident e.g. Core.maxloginattempts
	 *
	 * @throws ResourcesException if still in use or not found
	 * @return bool success = true
	 */
	public static function deleteResource() {

		$ident = self::getParamIdent('ident', 2);

		// get resource
		$res_check = Froxlor::getApi()->apiCall('Resources.statusResource', array('ident' => implode('.', $ident)));
		// check responsecode
		if ($res_check->getResponseCode() != 200) {
			// return non-success message
			return $res_check->getResponse();
		}
		// get id from response
		$resid = $res_check->getData()['id'];
		// load bean
		$res = Database::load('resources', $resid);
		// check if in use (limits)
		$inuse = Database::find('limits', ' resourceid = ? ', array($resid));
		if (is_array($inuse) && count($inuse) > 0) {
			throw new ResourcesException(403, 'The resource "'.implode('.', $ident).'" cannot be deleted as it is in use');
		}
		// delete it
		Database::trash($res);
		// return bean as array
		return ApiResponse::createResponse(200, null, array('success' => true));

	}

	/**
	 * (non-PHPdoc)
	 * @see FroxlorModule::Core_moduleSetup()
	 */
	public function Core_moduleSetup() {
	}
}
