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

class handle {
	private $_handleId;
	private $_name;
	private $_firstname;
	private $_company;
	private $_street;
	private $_zip;
	private $_countrycode;
	private $_phone;
	private $_fax;
	private $_email;
	
	private $_db;

	public function __construct() {
		global $db;
		
		$this->_db = $db;
	}

	public function setName($name, $firstname) {
		$this->_name = $name;
		$this->_firstname = $firstname;
	}

	public function setCompany($company) {
		$this->_company = $company;
	}

	public function setAddress($street, $zip, $country) {
		$this->_street = $street;
		$this->_zip = $zip;
		$this->_countrycode = $country;
	}

	public function setContactData($phone, $email, $fax = "") {
		$this->_phone = $phone;
		$this->_email = $email;
		$this->_fax = $fax;
	}

	public function getHandleId() {
		return $this->_handleId;
	}
	
	public function getName() {
		return $this->_name;
	}
	
	public function getFirstname() {
		return $this->_firstname;
	}
	
	public function getCompany() {
		return $this->_company;
	}
	
	public function getStreet() {
		return $this->_street;
	}
	
	public function getZip() {
		return $this->_zip;
	}
	
	public function getCountrycode() {
		return $this->_countrycode;
	}
	
	public function getPhone() {
		return $this->_phone;
	}
	
	public function getFax() {
		return $this->_fax;
	}
	
	public function getEmail() {
		return $this->_email;
	}
	
	public function sync() {
		
	}
}