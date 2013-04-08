<?php

/**
 * Froxlor API Settings-Module interface
 *
 * PHP version 5
 *
 * This file is part of the Froxlor project.
 * Copyright (c) 2003-2009 the SysCP Team (see authors).
 * Copyright (c) 2010- the Froxlor Team (see authors).
 *
 * For the full copyright and license information, please view the COPYING
 * file that was distributed with this source code. You can also view the
 * COPYING file online at http://files.froxlor.org/misc/COPYING.txt
 *
 * @copyright  (c) the authors
 * @author     Froxlor team <team@froxlor.org> (2010-)
 * @license    GPLv2 http://files.froxlor.org/misc/COPYING.txt
 * @category   core
 * @package    API
 * @since      0.99.0
 */

/**
 * Interface iSettings
 *
 * @copyright  (c) the authors
 * @author     Froxlor team <team@froxlor.org> (2010-)
 * @license    GPLv2 http://files.froxlor.org/misc/COPYING.txt
 * @category   core
 * @package    API
 * @since      0.99.0
 */
interface iSettings {

	/**
	 * list all available settings, optionally
	 * limited by given 'module' and 'section'
	 * (separated by a dot, e.g. 'limit' => 'Core[.system]')
	 *
	 * @return array 
	 */
	public static function listSettings(array &$params);

	/**
	 * return a specific setting by given
	 * 'module', 'section' and 'var'
	 * (separated by a dot, e.g. 'ident' => 'Core.system.something')
	 *
	 * @return mixed settings value
	*/
	public static function statusSetting(array &$param);

}
