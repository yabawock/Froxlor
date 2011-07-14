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
	private $_city;
	private $_zip;
	private $_countrycode;
	private $_phone;
	private $_fax;
	private $_email;
	
	private $_db;

	/**
	 * Constructor.
	 *
	 * @param array  $data     array provided by a MREG_Response
	 * @param string $handleid the handleid (optional)
	 */
	public function __construct($data = array(), $handleid = null) {
		global $db;
		
		// provide the database object
		$this->_db = $db;
		
		// check if data is provided
		if (count($data) > 0 && is_null($handleid)) {
			$this->setName($data['contact_lastname'], $data['contact_firstname']);
			$this->setCompany($data['contact_organization']);
			$this->setAddress($data['contact_street'], $data['contact_city'], $data['contact_zip'], $data['contact_country']);
			$this->setContactData($data['contact_phone'], $data['contact_email'], $data['contact_fax']);
			
			$this->_handleId = $data['contact'];
		} elseif (!is_null($handleid)) {
			$data = $this->_db->query_first("SELECT * FROM ". TABLE_USER_ADDRESSES ." WHERE `handleid` = '". $handleid ."'");
			$this->setName($data['contact_lastname'], $data['contact_firstname']);
			$this->setCompany($data['contact_organization']);
			$this->setAddress($data['contact_street'], $data['contact_city'], $data['contact_zip'], $data['contact_country']);
			$this->setContactData($data['contact_phone'], $data['contact_email'], $data['contact_fax']);
		}
	}

	/**
	 * Sets the firstname and name.
	 *
	 * @param string $name
	 * @param string $firstname
	 */
	public function setName($name, $firstname) {
		$this->_name = $name;
		$this->_firstname = $firstname;
	}

	/**
	 * Sets the company.
	 *
	 * @param string $company
	 */
	public function setCompany($company) {
		$this->_company = $company;
	}

	/**
	 * Set the address data.
	 *
	 * @param string $street
	 * @param string $city
	 * @param string $zip
	 * @param string $country
	 */
	public function setAddress($street, $city, $zip, $country) {
		$this->_street = $street;
		$this->_city = $city;
		$this->_zip = $zip;
		$this->_countrycode = $country;
	}

	/**
	 * Sets the contact data.
	 *
	 * @param string $phone
	 * @param string $email
	 * @param string $fax
	 */
	public function setContactData($phone, $email, $fax = "") {
		$this->_phone = $phone;
		$this->_email = $email;
		$this->_fax = $fax;
	}
	
	/**
	 * Sets the handle id.
	 *
	 * @param string $id contact id
	 */
	public function setHandleId($id) {
		$this->_handleId = $id;
	}

	/**
	 * @return string handleid
	 */
	public function getHandleId() {
		return $this->_handleId;
	}
	
	/**
	 * @return string name
	 */
	public function getName() {
		return $this->_name;
	}
	
	/**
	 * @return string firstname
	 */
	public function getFirstname() {
		return $this->_firstname;
	}
	
	/**
	 * @return string company
	 */
	public function getCompany() {
		return $this->_company;
	}
	
	/**
	 * @return string street name
	 */
	public function getStreet() {
		return $this->_street;
	}
	
	/**
	 * @return string zip code
	 */
	public function getZip() {
		return $this->_zip;
	}
	
	/**
	 * @return string country code
	 */
	public function getCountrycode() {
		return $this->_countrycode;
	}
	
	/**
	 * @return string phone number
	 */
	public function getPhone() {
		return $this->_phone;
	}
	
	/**
	 * @return string the fax
	 */
	public function getFax() {
		return $this->_fax;
	}
	
	/**
	 * @return string the email
	 */
	public function getEmail() {
		return $this->_email;
	}
	
	/**
	 * This function synchronize this object with the database.
	 */
	public function sync() {
		$sql = "INSERT INTO `domain_handle` (`handleid`, `name`, `firstname`, `company`, `street`, `zip`, `city`, `countrycode`, `phone`, `email`)
				VALUES ('".$this->_handleId."','".$this->_name."','".$this->_firstname."','".$this->_company."',".$this->_street."','".$this->_zip."','".$this->_city."','".$this->_countrycode."','".$this->_phone."','".$this->_email."')
				ON DUPLICATE KEY UPDATE;";
		
		$this->_db->query($sql);
	}
}