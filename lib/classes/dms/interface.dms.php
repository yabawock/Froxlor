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
 * @package    Cron
 * @version    $Id$
 */

/**
 * This will be the base class for all dms.
 * All subclasses have to create their queries.
 */
interface dms
{
	/**
	 * Creates a new handle.
	 *
	 * @param handle $handle
	 */
	public function handleCreate($handle);
	
	/**
	 * Deletes a handle if no resources are associated to it.
	 *
	 * @param handle $handle
	 *
	 * @return boolean
	 */
	public function handleDelete($handle);
	
	/**
	 * Modifies a handle.
	 *
	 * @param handle $handle
	 *
	 * @return boolean
	 */
	public function handleModify($handle);
	
	/**
	 * This will get all handles, sync them and
	 * return an array with all handles.
	 *
	 * @return array with handle
	 */
	public function handleList();
	
	/**
	 * Checks if a domain is available for registration.
	 *
	 * @param string $domain
	 *
	 * @return int statuscode
	 */
	public function domainCheck($domain);
	
	/**
	 * Retuns all information for the domain object.
	 *
	 * @param domain $domain
	 *
	 * @return array or null
	 */
	public function domainStatus($domain);
	
	/**
	 * Registers the new domain.
	 *
	 * @param string $domain
	 *
	 * @return int statuscode
	 */
	public function domainRegister($domain);
	
	/**
	 * Creates a formfield for domain registration.
	 *
	 * @param string $domain
	 *
	 * @return array formfield
	 */
	public function domainRegisterFormfield($domain);
	
	/**
	 * This will get all domains.
	 *
	 * @return array with domains
	 */
	public function domainList();
	
	/**
	 * This will get all domains for a specific handle.
	 *
	 * @param handle $handle
	 *
	 * @return array domains
	 */
	public function domainListByContact($handle);
	
	/*
	public function eventListener();
	*/
}