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
		global $db;
		$this->_db = $db;
		
		if (ctype_digit($id)) {
			$this->_id = $id;
		
			$this->init();
		}
	}
	
	/**
	 * Creates a user object based on loginname and password.
	 *
	 * @param string $loginname
	 * @param string $password
	 */
	public function __construct($loginname, $password) {
		global $db;
		$this->_db = $db;
		
		if ($this->performLogin($loginname, $password)) {
			$this->init();
		}
	}
	
	/**
	 * This function initializes all data.
	 */
	private function init() {
		$this->fetchGeneralData();
		$this->fetchUserAddress();
		$this->fetchUserResources();
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
		$success = false;
		$row = $this->_db->query_first("SELECT `loginname`, `isadmin` FROM `" . TABLE_USERS . "` WHERE `loginname`='" . $db->escape($loginname) . "'");

		if($row['loginname'] == $loginname) {
			// check if the user is an admin
			if ($row['isadmin']) {
				$this->_isAdmin = true;
			}
			
			$success = true;
		}
		else if((int)$settings['login']['domain_login'] == 1) {
			/**
			 * check if the customer tries to login with a domain, #374
			 */
			$domainname = $idna_convert->encode(preg_replace(Array('/\:(\d)+$/', '/^https?\:\/\//'), '', $loginname));
			
			$sql = "SELECT a.`customerid`, b.`isadmin`, b.`loginname`
					FROM `". TABLE_PANEL_DOMAINS ."` a, `". TABLE_USERS ."` b
					WHERE a.`domain` = '' && a.`customerid` = b.`id`";
			
			$row = $this->_db->query_first($sql);
			if ($row) {
				$this->_loginname = $tow['loginname'];
				$this->_id = $row['id'];
				$this->_isAdmin = $row['isadmin'];
				
				$success = true;
			}
		}
		
		return $success;
	}
	
	/**
	 * Loads general data from database.
	 */
	private function fetchGeneralData() {
		$sql = "SELECT * FROM ". TABLE_USERS ." WHERE id = '".$this->getId()."'";
		
		$row = $this->_db->query_first($sql);
		if ($row) {
			// @TODO maybe unset password or update query to not fetch it?
			$this->_data['general'] = $row;
		}
	}
	
	/**
	 * Loads address data from database.
	 */
	private function fetchUserAddress() {
		$sql = "SELECT * FROM ". TABLE_USER_ADDRESSES ." WHERE id = '".$this->getId()."'";
		
		$row = $this->_db->query_first($sql);
		if ($row) {
			$this->_data['address'] = $row;
		}
	}
	
	/**
	 * Loads ressource data from database.
	 */
	private function fetchUserResources() {
		$table = TABLE_USER_RESOURCES;
		
		if ($this->isAdmin()) {
			$table = TABLE_ADMIN_RESOURCES;
		}
		
		$sql = "SELECT * FROM ". $table ." WHERE id = '".$this->getId()."'";
		
		$row = $this->_db->query_first($sql);
		if ($row) {
			$this->_data['resources'] = $row;
		}
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
	 * @param string $area area
	 * @param string $key  index
	 *
	 * @return data set for given $key
	 */
	public function getData($area, $key) {
		return $this->_data[$area][$key];
	}
	
	/**
	 * @return admin flag
	 */
	public function isAdmin() {
		return $this->_isAdmin;
	}
	
	/**
	 * Updates the user data (in database too).
	 *
	 * @param string $area  area like general, address, resource
	 * @param string $key   key
	 * @param string $value value
	 */
	public function setData($area, $key, $value) {
		$this->_data[$area][$key] = $value;
		
		$this->sync($area, $key);
	}
	
	/**
	 * Updates the user data.
	 *
	 * @param string $area   area
	 * @param array  $values mixed array
	 */
	public function setAllData($area, $values) {
		$this->_data[$area] = $values;
	}
	
	/**
	 * Updates the database record.
	 *
	 * @param string $area
	 * @param string $key
	 *
	 * @return string RessourceId
	 */
	private function sync($area, $key) {
		$sql = "UPDATE ". $this->area2table($area) ." SET `". $key ."` = `". $this->getData($area, $key) ."` WHERE id = `". $this->getId() ."`;";
		
		return $this->_db->query($sql);
	}
	
	/**
	 * Updates all database records for this user.
	 */
	private function syncAll() {
		// TABLE_USERS
		$data = $this->_db->array2update($this->_data['general']);
		$sql = "UPDATE ". TABLE_USERS ." SET ". $data ." WHERE `id` = `". $this->getId() ."`";
		$this->_db->query($sql);
		
		// TABLE_USER_ADDRESSES
		$data = $this->_db->array2update($this->_data['address']);
		$sql = "UPDATE ". TABLE_USER_ADDRESSES ." SET ". $data ." WHERE `id` = `". $this->getId() ."`";
		$this->_db->query($sql);
		
		if ($this->isAdmin()) {
			// TABLE_ADMIN_RESOURCES
			$data = $this->_db->array2update($this->_data['resources']);
			$sql = "UPDATE ". TABLE_ADMIN_RESOURCES ." SET ". $data ." WHERE `id` = `". $this->getId() ."`";
			$this->_db->query($sql);
		} else {
			// TABLE_USER_RESOURCES
			$data = $this->_db->array2update($this->_data['resources']);
			$sql = "UPDATE ". TABLE_USER_RESOURCES ." SET ". $data ." WHERE `id` = `". $this->getId() ."`";
			$this->_db->query($sql);
		}
	}
	
	/**
	 * Converts the area to database table name.
	 *
	 * @param string $area
	 *
	 * @return string table name
	 */
	private function area2table($area) {
		$table = "does_not_exist";
		
		switch ($area) {
			case "general":
				$table = TABLE_USERS;
			break;
			
			case "resources":
				if ($this->isAdmin()) {
					$table = TABLE_ADMIN_RESOURCES;
				} else {
					$table = TABLE_USER_RESOURCES;
				}
			break;
			
			case "address":
				$table = TABLE_USER_ADDRESSES;
			break;
		}
		
		return $table;
	}
}
