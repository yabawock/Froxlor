<?php

/**
 * This file is part of the Froxlor project.
 * Copyright (c) 2010 the Froxlor Team (see authors).
 *
 * For the full copyright and license information, please view the COPYING
 * file that was distributed with this source code. You can also view the
 * COPYING file online at http://files.froxlor.org/misc/COPYING.txt
 *
 * @copyright  (c) the authors
 * @author     Froxlor team <team@froxlor.org> (2010-)
 * @author     Andreas Burchert <scarya@froxlor.org>
 * @license    GPLv2 http://files.froxlor.org/misc/COPYING.txt
 * @package    DMS
 */

/**
 *
 * @todo listing in rrp do only support 1k response entries. work with first/limit
 */
class rrp implements dms
{
	private $_user;
	private $_password;
	private $_opmode;
	private $_socket;
	
	private $_config = array();
	private $_reqeust;
	
	/**
	 * Contructor.
	 *
	 * @param string $user
	 * @param string $password
	 * @param string $opmode
	 * @param string $socket
	 */
	public function __construct($user, $password, $opmode = "ote", $socket = "http://api-ote.rrpproxy.net/call?") {
		$this->_config = array("username" => $user,
						"password" => $password,
						"opmode" => $opmode,
						"socket" => $socket);
		
		$this->_user = $user;
		$this->_password = $password;
		$this->_opmode = $opmode;
		$this->_socket = $socket;
		
		$this->_request = new MREG_RequestHttp($this->_config);
	}
	
	/**
	 * (non-PHPdoc)
	 * @see dms::handleCreate()
	 */
	public function handleCreate($handle) {
		$command = array(
			"command" => "AddContact",
			"firstname" => $handle->getFirstname(),
			"lastname" => $handle->getName(),
			"organization" => $handle->getCompany(),
			"street" => $handle->getStree(),
			"zip" => $handle->getZip(),
			"city" => $handle->getCity(),
			"country" => $handle->getCountrycode(),
			"phone" => $handle->getPhone(),
			"fax" => $handle->getFax(),
			"email" => $handle->getEmail()
		);
		
		$response = $this->_reqeust->send($command);
		
		if ($response->code == 200) {
			$data = $response->getList();
			
			$id = $data['0']['contact'];
			$handle->setHandleId($id);
			$handle->sync();
			
			return $handle;
		}
		
		return false;
	}
	
	/**
	 * (non-PHPdoc)
	 * @see dms::handleDelete()
	 */
	public function handleDelete($handle) {
		$command = array("command" => "DeleteContact", "contact" => $handle->getHandleId());
		$response = $this->_reqeust->send($command);
		
		if ($response->code == 200) {
			return true;
		}
		
		return false;
	}
	
	/**
	 * (non-PHPdoc)
	 * @see dms::handleModify()
	 */
	public function handleModify($handle) {
		$command = array(
			"command" => "ModifyContact",
			"contact" => $handle->getHandleId(),
			"firstname" => $handle->getFirstname(),
			"lastname" => $handle->getName(),
			"organization" => $handle->getCompany(),
			"street" => $handle->getStree(),
			"zip" => $handle->getZip(),
			"city" => $handle->getCity(),
			"country" => $handle->getCountrycode(),
			"phone" => $handle->getPhone(),
			"fax" => $handle->getFax(),
			"email" => $handle->getEmail()
		);
		
		$response = $this->_reqeust->send($command);
		
		if ($response->code == 200) {
			$handle->sync();
			return true;
		}
		
		return false;
	}
	
	/**
	 * (non-PHPdoc)
	 * @see dms::handleList()
	 */
	public function handleList() {
		$response = $this->_request->send(array("command" => "QueryContactList", "wide" => "1"));
		$handles = array();
		
		// check status code
		if ($response->code == 200) {
			$arr = $response->getList();
			
			// create all handles
			foreach ($arr as $vars) {
				$handle = new handle($vars);
				// and sync them
				$handle->sync();
				
				// add it to the list
				$handles[] = $handle;
			}
		}
		
		return $handles;
	}
	
	/**
	 * (non-PHPdoc)
	 * @see dms::domainCheck()
	 */
	public function domainCheck($domain) {
		$response = $this->_request->send(array("command" => "CheckDomain", "domain" => $domain));
		
		return $response->code;
	}
}