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
			return ApiResponse::createResponse(200, null, Database::exportAll($server));
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
	 * @see iServer::addServer()
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
	public static function addServer() {

		$name = self::getParam('name');
		$desc = self::getParam('desc', true, "");
		$ipaddress = self::getParam('ipaddress');
		$owners = self::getParam('owners', true, null);

		// check permissions
		$user = self::getParam('_userinfo');
		$api_response = Froxlor::getApi()->apiCall(
				'Permissions.statusUserPermission',
				array('userid' => $user->id, 'ident' => 'Server.addServer')
		);

		if ($api_response->getResponseCode() != 200) {
			throw new ApiException(403, 'You are not allowed to access this function');
		}

		// set up new server
		$server = Database::dispense('server');
		$server->name = $name;
		$server->desc = $desc;

		// check for owners
		$owners = array();
		// the creater is always an owner
		$owners[] = Database::load('user', $user->id);
		if (is_array($owners) && count($owners > 0)) {
			// iterate and check
			foreach ($owners as $owner) {
				$o = Database::load('user', $owner);
				if ($o->id) {
					$owners[] = $o;
				}
			}
		}
		$server->sharedUser = $owners;
		$server_id = Database::store($server);
		// load server bean
		$serverbean = Database::load('server', $server_id);
		$server_array = Database::exportAll($serverbean);

		Hooks::callHooks('addServer_afterStore', $server_array);

		// now add IP address
		$ip_result = Froxlor::getApi()->apiCall(
				'Server.addServerIP',
				array('ipaddress' => $ipaddress, 'isdefault' => true, 'serverid' => $server_id)
		);
		if ($ip_result->getResponseCode() == 200) {
			Hooks::callHooks('addServer_beforeReturn', $server_array);
			// return result with updated server-bean
			return ApiResponse::createResponse(200, null, $server_array);
		}
		// rollback, there was an error, so the server needs to be removed from the database
		Database::trash($serverbean);
		// return the error-message from addServerIP
		return $ip_result->getResponse();

	}

	/**
	 * @see iServer::addServerIP()
	 *
	 * @param string $ipadress the IP adress (v4 or v6)
	 * @param int $serverid the id of the server to add the IP to
	 * @param bool $isdefault optional, whether this ip should be the default server ip, default: false
	 *
	 * @throws ServerException
	 * @return array exported added IP bean
	 */
	public static function addServerIP() {

		$ipaddress = self::getParam('ipaddress');
		$serverid = self::getIntParam('serverid');
		$isdefault = self::getParam('isdefault', true, false);

		// look for duplicate
		$ip_check = Database::findOne('ipaddress', ' ip = ?', array($ipaddress));
		if ($ip_check !== null) {
			$server = Database::load('server', $ip_check->server_id);
			throw new ServerException(406, 'IP address "'.$ipaddress.'" already exists for server "'.$server->name.'"');
		}
		$ip = Database::dispense('ipaddress');
		$ip->ip = $ipaddress;
		$ip->isdefault = $isdefault;
		$ip->server_id = $serverid;
		$ip_id = Database::store($ip);
		$ip_array = Database::load('ipaddress', $ip_id)->export();

		Hooks::callHooks('addServerIP_afterStore', $ip_array);

		// now update the "old" default IP to be non-default
		$formerdefault = Database::findOne('ipaddress',
				' isdefault = :isdef AND server_id = :sid',
				array(':isdef' => true, ':sid' => $serverid)
		);
		// this is faster than calling apiCall('Server.modifyServerIP')
		if ($formerdefault !== null) {
			$formerdefault->isdefault = false;
			Database::store($formerdefault);
		}

		Hooks::callHooks('addServerIP_beforeReturn', $ip_array);

		// return newly added ip
		return ApiResponse::createResponse(200, null, $ip_array);
	}

	/**
	 * @see iServer::modifyServerIP()
	 *
	 * @param int $id id of the ip-address
	 * @param string $ipaddress new IP address value
	 * @param bool $isdefault optional, whether this ip should be the default server ip, default: false
	 *
	 * @throws ServerException
	 * @return array exported updated IP bean
	 */
	public static function modifyServerIP() {

		$iid = self::getIntParam('id');
		$ipaddress = self::getParam('ipaddress');
		$isdefault = self::getParam('isdefault', true, false);

		// get the bean
		$ip = Database::load('ipaddress', $iid);

		// is it valid?
		if ($ip === null) {
			throw new ServerException(404, 'IP address "'.$ipaddress.'" with id #'.$iid.' could not be found');
		}

		// set new values
		$ip->ip = $ipaddress;
		$ip->isdefault = $isdefault;
		Database::store($ip);
		$ip_array = Database::load('ipaddress', $iid)->export();

		Hooks::callHooks('addServerIP_beforeReturn', $ip_array);

		// return updated bean
		return ApiResponse::createResponse(200, null, $ip_array);
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
		$srv->sharedUser = array(Database::load('user', 1));
		$srvid = Database::store($srv);

		// TODO permission / resources
		$perm = Database::dispense('permissions');
		$perm->module = 'Server';
		$perm->name = 'addServer';
		$permid = Database::store($perm);

		// load superadmin group and add permissions
		$sagroup = Database::findOne('groups', ' groupname = :grp', array(':grp' => '@superadmin'));
		if ($sagroup !== null) {
			$sagroup->sharedPermissions[] = Database::load('permissions', $permid);
			Database::store($sagroup);
		}
	}
}
