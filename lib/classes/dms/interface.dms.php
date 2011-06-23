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
	public function handleDelete($handle);
	public function handleAlter($handle);
	
	/**
	 * This will get all handles, sync them and
	 * return an array with all handles.
	 *
	 * @return array with handle
	 */
	public function handleList();
	
	/*
	public function domainCheck($domain);
	public function domainRegister();
	
	public function eventListener();
	*/
}