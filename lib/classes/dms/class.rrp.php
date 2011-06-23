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

class rrp implements dms
{
	private $_user;
	private $_password;
	private $_opmode;
	private $_socket;
	
	private $_config = array();
	private $_reqeust;
	
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
	
	public function handleCreate($handle) {
		
	}
	
	public function handleDelete($handle) {
		
	}
	
	public function handleAlter($handle) {
		
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
}