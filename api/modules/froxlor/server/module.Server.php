<?php

/**
 * Froxlor API Server-Module
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
 * Class Server
 *
 * @copyright  (c) the authors
 * @author     Froxlor team <team@froxlor.org> (2010-)
 * @license    GPLv2 http://files.froxlor.org/misc/COPYING.txt
 * @category   Modules
 * @package    API
 * @since      0.99.0
 */
class Server extends FroxlorModule implements iServer {

	/**
	 * @see iServer::statusServer()
	 *
	 * @param int $id id of the server
	 *
	 * @throws ServerException
	 * @return array
	 */
	public static function statusServer() {
		$sid = self::getIntParam('id');
		$server = Database::load('server', $sid);
		if ($server->id) {
			return ApiResponse::createResponse(200, null, Database::exportAll($server, false));
		}
		throw new ServerException(404, 'Server with id #'.$sid.' could not be found');
	}

	/**
	 * @see iServer::listServer()
	 *
	 * @param int $owner optional id of the owner-user
	 *
	 * @throws ServerException if given owner does not exist
	 * @return array
	 */
	public static function listServer() {

		// get owner, non-negative, default 0 = no owner
		$owner = self::getIntParam('owner', true, 0, false);

		if ($owner > 0) {
			$servers = Database::find('server', ' user_id = ? ORDER BY name ASC', array($owner));
		} else {
			$servers = Database::findAll('server', ' ORDER BY name ASC');
		}

		// create array from beans
		$server_array = Database::exportAll($servers, false);

		// return all the servers as array (api)
		return ApiResponse::createResponse(
				200,
				null,
				$server_array
		);
	}

	/**
	 * (non-PHPdoc)
	 * @see FroxlorModule::Core_moduleSetup()
	 */
	public function Core_moduleSetup() {
		// FIXME just testing-code here!
		// default IP
		$ip = Database::dispense('ipaddress');
		$ip->ip = '127.0.0.1';
		$ipid = Database::store($ip);

		// default server
		$srv = Database::dispense('server');
		$srv->name = 'Testserver';
		$srv->desc = 'This is an automatically added default server';
		$srv->ownIpaddress = array(Database::load('ipaddress', $ipid));
		$srv->ownUser = array(Database::load('user', 1));
		$srvid = Database::store($srv);
		// TODO: services
	}
}
