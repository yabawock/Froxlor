<?php

/**
 * Froxlor API Server-Module interface
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
 * Interface iServer
 *
 * @copyright  (c) the authors
 * @author     Froxlor team <team@froxlor.org> (2010-)
 * @license    GPLv2 http://files.froxlor.org/misc/COPYING.txt
 * @category   Modules
 * @package    API
 * @since      0.99.0
 */
interface iServer {

	/**
	 * returns a server by given id
	 *
	 * @param int $id id of the server
	 *
	 * @throws ServerException
	 * @return array
	 */
	public static function statusServer();

	/**
	 * returns all servers, optionally only servers
	 * owned by given $owner
	 *
	 * @param int $owner optional id of the owner-user
	 *
	 * @throws ServerException if given owner does not exist
	 * @return array
	*/
	public static function listServer();

	/**
	 * adds a new server to the database, additionally
	 * @see iServer::addServerIP() is used to assign
	 * the default ip to the server (editable if > one IP)
	 * via @see iServer::modifyServerIP()
	 * Hooks that are being called:
	 * - addServer_afterStore
	 * - addServer_beforeReturn
	 *
	 * @param string $name name of server
	 * @param string $desc description
	 * @param string $ipaddress initial default IP of that server
	 * @param array $owners optional, array of user-id's;
	 *                      in any case, the user who adds the server is added as owner
	 *
	 * @throws ServerException
	 * @return array exported newly added server bean
	*/
	public static function addServer();

	/**
	 * adds and assigns a new ipaddress to a server
	 * Hooks that are being called:
	 * - addServerIP_afterStore
	 * - addServerIP_beforeReturn
	 *
	 * @param string $ipadress the IP adress (v4 or v6)
	 * @param bool $isdefault whether this ip should be the default server ip, default: false
	 *
	 * @throws ServerException
	 * @return array exported added IP bean
	*/
	public static function addServerIP();

	/**
	 * update a servers IP address, if $isdefault is set
	 * the former default IP will be set isdefault=false
	 * Hooks that are being called:
	 * - modifyServerIP_beforeReturn
	 *
	 * @param int $id id of the ip-address
	 * @param string $ipaddress new IP address value
	 * @param bool $isdefault whether this ip should be the default server ip, default: false
	 *
	 * @throws ServerException
	 * @return array exported updated IP bean
	*/
	public static function modifyServerIP();
}
