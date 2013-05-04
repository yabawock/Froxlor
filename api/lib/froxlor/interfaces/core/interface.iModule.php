<?php

/**
 * Module Interface
 *
 * PHP version 5
 *
 * This file is part of the Froxlor project.
 * Copyright (c) 2013- the Froxlor Team (see authors).
 *
 * For the full copyright and license information, please view the COPYING
 * file that was distributed with this source code. You can also view the
 * COPYING file online at http://files.froxlor.org/misc/COPYING.txt
 *
 * @copyright  (c) the authors
 * @author     Froxlor team <team@froxlor.org> (2013-)
 * @license    GPLv2 http://files.froxlor.org/misc/COPYING.txt
 * @category   core
 * @package    API
 * @since      0.99.0
 */

/**
 * Interface iModule
 *
 * @copyright  (c) the authors
 * @author     Froxlor team <team@froxlor.org> (2013-)
 * @license    GPLv2 http://files.froxlor.org/misc/COPYING.txt
 * @category   core
 * @package    API
 * @since      0.99.0
 */
interface iModule {

	/**
	 * this functions is used to check the availability
	 * of a given list of modules. If either one of
	 * them are not found, throw an ApiException
	 *
	 * @param string|array $modules
	 *
	 * @throws ApiException
	 */
	public static function requireModules($modules = null);

	/**
	 * this function is used in modules to check
	 * for specfic Froxlor-version requirements.
	 * You can specify either min or max or both.
	 *
	 * @param string $min_version at least this version
	 * @param string $max_version at most this version
	 *
	 * @throws ApiException
	*/
	public static function requireVersion($min_version = null, $max_version = null);

	/**
	 * Version compare for update-check and more
	 *
	 * @param string $a
	 * @param string $b
	 *
	 * @return integer 0 if equal, 1 if a>b and -1 if b>a
	*/
	public static function cmpFroxlorVersions($a = null, $b = null);
}
