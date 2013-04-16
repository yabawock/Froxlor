<?php

/**
 * Froxlor API Core-Module interface
 *
 * PHP version 5
 *
 * This file is part of the Froxlor project.
 * Copyright (c) 2010- the Froxlor Team (see authors).
 *
 * For the full copyright and license information, please view the COPYING
 * file that was distributed with this source code. You can also view the
 * COPYING file online at http://files.froxlor.org/misc/COPYING.txt
 *
 * @copyright  (c) the authors
 * @author     Froxlor team <team@froxlor.org> (2010-)
 * @license    GPLv2 http://files.froxlor.org/misc/COPYING.txt
 * @category   Modules
 * @package    API
 * @since      0.99.0
 */

/**
 * Interface iCore
 *
 * @copyright  (c) the authors
 * @author     Froxlor team <team@froxlor.org> (2010-)
 * @license    GPLv2 http://files.froxlor.org/misc/COPYING.txt
 * @category   Modules
 * @package    API
 * @since      0.99.0
 */
interface iCore {

	/**
	 * return the current release version
	 *
	 * @return string version
	 */
	public static function statusVersion();

	/**
	 * return the current API version
	 *
	 * @return string version
	*/
	public static function statusApiVersion();

	/**
	 * return whether a newer version is available.
	 * Hooks that are being called:
	 * - statusUpdate_beforeReturn
	 *
	 * @return string
	*/
	public static function statusUpdate();

	/**
	 * returns various system information.
	 * Hooks that are being called:
	 * - statusSystem_beforeReturn
	 *
	 * @return array
	*/
	public static function statusSystem();

	/**
	 * list all available api-functions (possible
	 * restrictions due permissions are not checked)
	 *
	 * @param string $module optional only list functions of specific module
	 *
	 * @throws CoreException
	 * @return array
	*/
	public static function listApiFunctions();

	/**
	 * function that calls Core_moduleSetup() in
	 * all modules via Hook. Mandatory for all modules,
	 * even if the method is empty.
	 *
	 * @return null
	*/
	public static function doSetup();
}
