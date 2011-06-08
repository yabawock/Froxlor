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
 * @author     Andreas Burchert (scarya@froxlor.org)
 * @author     Froxlor team <team@froxlor.org> (2010-)
 * @license    GPLv2 http://files.froxlor.org/misc/COPYING.txt
 *
 */

/**
 * This class is intended to handle all user related things.
 */
class user {
	
	/**
	 * The userid.
	 * @var int
	 */
	private $_id = -1;
	
	/**
	 * The loginname.
	 * @var string
	 */
	private $_loginname;
	
	/**
	 * Whether the user is an admin.
	 * @var boolean
	 */
	private $_isAdmin = false;
	
	/**
	 * The users sessionId.
	 * @todo not sure if sessionId in user object is needed
	 * @var string
	 */
	private $_sessionId = null;
	
	/**
	 * This is true if a login was successful
	 * @var boolean
	 */
	private $_logedIn = false;
	
	/**
	 * Contains all data.
	 * @var array
	 */
	private $_data;
	
	/**
	 * Contains the database handle.
	 * @var
	 */
	private $_db;
	
	/**
	 * Creates a user object based on the user id.
	 *
	 * @param int $id
	 */
	public function __construct($id) {
		
	}
	
	/**
	 * Creates a user object based on loginname and password.
	 *
	 * @param string $loginname
	 * @param string $password
	 */
	public function __construct($loginname, $password) {
		
	}
	
	/**
	 * This function initializes all data.
	 */
	private function init() {
		
	}
	
	/**
	 * Check if the provided data is valid.
	 *
	 * @param string $loginname
	 * @param string $password
	 *
	 * @return true on success, else false
	 */
	private function performLogin($loginname, $password) {
		// try a normal login
		
		// check if loginname is a domain name
		
		return false;
	}
	
	/**
	 * Loads general data from database.
	 */
	private function fetchGeneralData() {
		
	}
	
	/**
	 * Loads address data from database.
	 */
	private function fetchUserAddress() {
		
	}
	
	/**
	 * Loads ressource data from database.
	 */
	private function fetchUserResources() {
		
	}
	
	/**
	 * @return the user id
	 */
	public function getId() {
		return $this->_id;
	}
	
	/**
	 * @return the loginname
	 */
	public function getLoginname() {
		return $this->_loginname;
	}
	
	/**
	 * @return all data
	 */
	public function getAllData() {
		return $this->_data;
	}
	
	/**
	 * @param string $key index
	 *
	 * @return data set for given $key
	 */
	public function getData($key) {
		return $this->_data[$key];
	}
	
	/**
	 * @return admin flag
	 */
	public function isAdmin() {
		return $this->_isAdmin;
	}
	
	/**
	 * Updates the data (in database too).
	 *
	 * @param string $key
	 * @param string $value
	 */
	public function setData($key, $value) {
		
	}
}