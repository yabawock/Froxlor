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
	 * True if the user is deactivated.
	 * @var boolean
	 */
	private $_isDeactivated = true;

	/**
	 * Contains all data.
	 * @var array
	 */
	private $_data;

	/**
	 * Contains the database handle.
	 * @var db
	 */
	private $_db;

	/**
	 * Constructor.<br />
	 * You can use to types of initializing:
	 * 1 login by (int) id
	 * 2 login by name/domain and password
	 * 3 forgot password, login with name and email (minimal data)
	 */
	public function __construct() {
		$this->_db = Froxlor::getDb();

		$num = func_num_args();

		if ($num == 0) {
			// create a new user
			// nothing to do
		}
		else if ($num == 1) {
			// this must be id based login
			$this->createById(func_get_arg(0));
		} else if ($num == 2) {
			// loginname based
			$this->createByName(func_get_arg(0), func_get_arg(1));
		} elseif ($num == 3) {
			// forgot password, initialize with minimal data
			$this->createByNameEmail(func_get_arg(1), func_get_arg(2));
		}
	}

	/**
	 * Creates a user object based on the user id.
	 *
	 * @param int $id
	 *
	 * @throws Exception
	 */
	private function createById($id) {
		if (is_int($id)) {
			$this->_id = $id;

			$this->init();
		} else {
			throw new InvalidArgumentException("Provided id is not valid!");
		}
	}

	/**
	 * Creates a user object based on loginname and password.
	 *
	 * @param string $loginname
	 * @param string $password
	 *
	 * @throws Exception on failed login
	 */
	private function createByName($loginname, $password) {
		if ($this->performLogin($loginname, $password)) {
			$this->init();
		} else {
			throw new Exception("Login failed!");
		}
	}

	/**
	 * Performs a login based on loginname and email address.
	 *
	 * @param string $loginname
	 * @param string $email
	 *
	 * @throws Exception if no record is found
	 */
	private function createByNameEmail($loginname, $email) {
		$sql = "SELECT `u`.`id` FROM `". TABLE_USERS ."` `u`, `". TABLE_USER_ADDRESSES ."` `a`
		 WHERE `u`.`loginname` = '". $loginname ."' AND `u`.`contactid` = `a`.`id` AND `a`.`email` = '". $email ."'";
		$result = $db->query($sql);

		if ($result !== null) {
			$row = $db->fetch_array($result);

			if(isset($row['id'])) {
				$this->_id = $row['id'];

				// fetch only general data
				$this->fetchGeneralData();
				$this->fetchUserAddress();

				return;
			}
		}

		throw new Exception("User not found.");
	}

	/**
	 * Create a new user.
	 *
	 * @param array $data
	 * @throws Exception
	 */
	public function createNewUser($data) {
		// creating a new user is possible
		if ($this->_id == -1) {
			// create a new record
			$sql = "INSERT INTO `". TABLE_USERS ."` SET `deactivated` = '0', `loginname` = '" . $this->_db->escape($data['general']['loginname']) . "'";
			$result = $this->_db->query($sql);

			if (!$result) {
				throw new Exception("Could not insert into: ".TABLE_USERS);
			}

			$this->_id = $this->_db->insert_id();
			$data['general']['id'] = $this->_id;
			$data['resources']['id'] = $this->getId();

			// now setup user address record
			if (!isset($data['general']['contactid'])) {
				$sql = "INSERT INTO `". TABLE_USER_ADDRESSES ."` SET `name` = 'temp'";
				$result = $this->_db->query($sql);

				if (!$result) {
					throw new Exception("Could not insert into: ".TABLE_USERS);
				}
				$contactid = $this->_db->insert_id();
				$data['general']['contactid'] = $contactid;
			}

			// setup resources
			if ($data['general']['isadmin']) {
				$this->_isAdmin = true;

				$sql = "INSERT INTO `".TABLE_ADMIN_RESOURCES."` SET `id` = '".$this->getId()."'";

				$result = $this->_db->query($sql);

				if (!$result) {
					throw new Exception("Could not insert into: ".TABLE_USERS);
				}
			} else {
				$sql = "INSERT INTO `".TABLE_USER_RESOURCES."` SET `id` = '".$this->getId()."'";

				$result = $this->_db->query($sql);

				if (!$result) {
					throw new Exception("Could not insert into: ".TABLE_USERS);
				}
			}

			// @TODO setup the users admin

			$this->_data = $data;
			$this->syncAll();
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
		$row = $this->_db->query_first("SELECT `id`,`loginname`, `password`, `isadmin` FROM `" . TABLE_USERS . "` WHERE `loginname` = '" . $this->_db->escape($loginname) . "'");

		if($row['loginname'] == $loginname) {
			$this->_id = $row['id'];

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

			$sql = "SELECT a.`customerid`, b.`isadmin`, b.`loginname`, b.`password`
					FROM `". TABLE_PANEL_DOMAINS ."` a, `". TABLE_USERS ."` b
					WHERE a.`domain` = '' && a.`customerid` = b.`id`";

			$row = $this->_db->query_first($sql);
			if ($row) {
				$this->_loginname = $row['loginname'];
				$this->_id = $row['id'];
				$this->_isAdmin = $row['isadmin'];

				$success = true;
			}
		}

		// check password
		if (md5($password) != $row['password']) {
			$success = false;
		}

		return $success;
	}

	/**
	 * Loads general data from database.
	 */
	private function fetchGeneralData() {
		$sql = "SELECT * FROM ". TABLE_USERS ." WHERE `id` = '".$this->getId()."'";

		$row = $this->_db->query_first($sql);
		if ($row) {
			// @TODO maybe unset password or update query to not fetch it?
			$this->_data['general'] = $row;
			$this->_loginname = $row['loginname'];
			$this->_isAdmin = $row['isadmin'];
			$this->_isDeactivated = $row['deactivated'];
		}
	}

	/**
	 * Loads address data from database.
	 */
	private function fetchUserAddress() {
		$sql = "SELECT * FROM ". TABLE_USER_ADDRESSES ." WHERE `id` = '".$this->getData("general", "contactid")."'";

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

		$sql = "SELECT * FROM ". $table ." WHERE `id` = '".$this->getId()."'";

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
	 * @return true if the user is deactivated
	 */
	public function isDeactivated() {
		return $this->_isDeactivated;
	}

	/**
	 * Updates the user data (in database too).
	 *
	 * @param string $area  area like general, address, resource
	 * @param string $key   key
	 * @param string $value value
	 */
	public function setData($area, $key, $value) {
		if (isset($this->_data[$area]) && is_array($this->_data[$area])) {
			$this->_data[$area][$key] = $value;

			$this->sync($area, $key);
		}
	}

	/**
	 * Updates the user data.
	 *
	 * @param string $area   area
	 * @param array  $values mixed array
	 */
	public function setAllData($area, $values) {
		if (isset($this->_data[$area]) && is_array($this->_data[$area])) {
			$this->_data[$area] = $values;
		}
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
		$sql = "UPDATE ". $this->area2table($area) ." SET `". $this->_db->escape($key) ."` = '". $this->_db->escape($this->getData($area, $key)) ."' WHERE `id` = '". $this->getId() ."';";

		return $this->_db->query($sql);
	}

	/**
	 * Updates all database records for this user.
	 */
	private function syncAll() {
		// TABLE_USERS
		$data = $this->_db->array2update($this->_data['general']);
		$sql = "UPDATE ". TABLE_USERS ." SET ". $data ." WHERE `id` = '". $this->getId() ."'";
		$this->_db->query($sql);

		// TABLE_USER_ADDRESSES
		$data = $this->_db->array2update($this->_data['address']);
		$sql = "UPDATE ". TABLE_USER_ADDRESSES ." SET ". $data ." WHERE `id` = '". $this->data['general']['contactid'] ."'";
		$this->_db->query($sql);

		if ($this->isAdmin()) {
			// TABLE_ADMIN_RESOURCES
			$data = $this->_db->array2update($this->_data['resources']);
			$sql = "UPDATE ". TABLE_ADMIN_RESOURCES ." SET ". $data ." WHERE `id` = '". $this->getId() ."'";
			$this->_db->query($sql);
		} else {
			// TABLE_USER_RESOURCES
			$data = $this->_db->array2update($this->_data['resources']);
			$sql = "UPDATE ". TABLE_USER_RESOURCES ." SET ". $data ." WHERE `id` = '". $this->getId() ."'";
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
