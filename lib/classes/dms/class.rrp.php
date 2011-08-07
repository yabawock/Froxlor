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
		$this->_request->reset();
		
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
		$this->_request->reset();
		
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
		$this->_request->reset();
		
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
		$this->_request->reset();
		
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
		$this->_request->reset();
		
		$response = $this->_request->send(array("command" => "CheckDomain", "domain" => $domain));
		
		return $response->code;
	}
	
	/**
	 * (non-PHPdoc)
	 * @see dms::domainStatus()
	 */
	public function domainStatus($domain) {
		$this->_request->reset();
		
		$response = $this->_request->send(array("command" => "StatusDomain", "domain" => $domain->getFQDN()));
		
		if ($response->code == 200) {
			return $response->getList();
		}
		
		return null;
	}
	
	/**
	 * (non-PHPdoc)
	 * @see dms::domainRegister()
	 */
	public function domainRegister($domain) {
		$this->_request->reset();
	}
	
	/**
	 * (non-PHPdoc)
	 * @see dms::domainRegisterFormfield()
	 */
	public function domainRegisterFormfield($domain) {
		/*
15:43 <@EleRas> kurze erkl?rung wie range und description zusammenarbeiten
15:43 <@EleRas> beispiel:
15:43 <@EleRas> RANGE=0|1|<NULL>
15:43 <@EleRas> DESCRIPTION=No|Yes||This is the parameter to turn things of and on again
15:44 <@EleRas> sprich: wenn in der description (exploded mit |) soviele felder sind wie in der range exploded mit |, dann ist die description immer genau zu dem feld
15:45 <@EleRas> <option value="0">No</option><option value="1">Yes</option><option value=""></option>

		 */
		$this->_request->reset();
		
		$response = $this->_request->send(array("command" => "querycommandsyntax",
								"commandname" => "adddomain","domain" => $domain));
		
		if ($response->code == 200) {
			$data = $response->getList();
			$ret = array(
					'domain_add' => array(
						'title' => 'Register Domain',
						'image' => 'icons/domain_add.png',
						'sections' => array(
							'section_a' => array(
								'title' => 'Register Domain',
								'image' => 'icons/domain_add.png',
								'fields' => array()
							)
						)
					)
					);
			
			foreach($data as $val) {
				if (empty($val['parameter']) || empty($val['title'])){ continue;}
				
				// assume default setting
				$field = array(
						'label' => $val['title'],
						'type' => 'text',
						'desc' => $val['description'],
						'mandatory' => !$val['optional'],
					);
				
				// check if it is a select box
				$range = explode("|", $val['range']);
				$desc = explode("|", $val['description']);
				$elements = count($range);
				if ($elements > 2) {
					$options = "";
					for ($i = 0; $i < $elements; $i++) {
						$options .= makeoption($desc[$i], $range[$i]);
						echo $val['range'] ."\n". $val['description'] ."\n". $desc[$i] ."\n".  $range[$i] ."\n";
					}
					
					$field['type'] = 'select';
					$field['sel_var'] = $options;
					$field['desc'] = $desc[$elements];
				}
				
				$ret['domain_add']['sections']['section_a']['fields'][$val['title']] = $field;
			}
			
			return $ret;
		}
		
		return null;
	}
	
	/**
	 * (non-PHPdoc)
	 * @see dms::domainList()
	 */
	public function domainList() {
		$this->_request->reset();
		
		$response = $this->_request->send(array("command" => "QueryDomainList", "domain" => "*", "wide" => 1));
		
		if ($response->code == 200) {
			$ds = $response->getList();
			$domains = array();
			
			foreach($ds as $d) {
				$tmp = explode(".", $d['domain']);
				$domain = new domain($tmp[1], $tmp[0]);
				$status = $this->domainStatus($domain);
				$handle = new handle(array(), $status['owner_contact']);
				$domain->setOwner($handle);
				
				$domains[] = $domain;
			}
			
			return $domains;
		}
		
		return null;
	}
	
	/**
	 * (non-PHPdoc)
	 * @see dms::domainListByContact()
	 */
	public function domainListByContact($handle) {
		$this->_request->reset();
		
		$response = $this->_request->send(array("command" => "QueryDomainList", "domain" => "*",
												"contact" => $handle->getHandleId(), "wide" => 1));
		
		if ($response->code == 200) {
			$ds = $response->getList();
			$domains = array();
			
			foreach($ds as $d) {
				$tmp = explode(".", $d['domain']);
				$domain = new domain($tmp[1], $tmp[0]);
				$domain->setOwner($handle);
				
				$domains[] = $domain;
			}
			
			return $domains;
		}
		
		return null;
	}
}