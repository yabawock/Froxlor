<?php

/**
 * FroxlorModule BaseClass
 *
 * every module should extend this base-class
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
 * @category   core
 * @package    API
 * @since      0.99.0
 */

/**
 * Abstract class FroxlorModule
 *
 * every module should extend this base-class
 *
 * @copyright  (c) the authors
 * @author     Froxlor team <team@froxlor.org> (2010-)
 * @license    GPLv2 http://files.froxlor.org/misc/COPYING.txt
 * @category   core
 * @package    API
 * @since      0.99.0
 */
abstract class FroxlorModule implements iFroxlorModule {

	/**
	 * ApiRequest-Body passed to the module
	 *
	 * @var array
	 */
	public static $parameters = null;

	/**
	 * @see iFroxlorModule::setParamList()
	 */
	public static function setParamList(array &$params = null) {
		self::$parameters = $params;
	}

	/**
	 * @see iFroxlorModule::getParamList()
	 */
	public static function getParamList() {
		return self::$parameters;
	}

	/**
	 * get specific parameter from the parameterlist;
	 * check for existence and != empty if needed.
	 * Maybe more in the future
	 *
	 * @param string $param
	 * @param bool $empty_allowed
	 *
	 * @throws FroxlorModuleException
	 * @return mixed
	 */
	protected static function getParam($param = null, $empty_allowed = false) {
		// does it exist?
		if (!isset(self::$parameters[$param])) {
			if ($empty_allowed == false) {
				throw new FroxlorModuleException(404, 'Requested parameter "'.$param.'" could not be found');
			}
			return '';
		}
		// is it empty? (if not allowed)
		if (self::$parameters[$param] == '') {
			if ($empty_allowed == false) {
				throw new FroxlorModuleException(406, 'Requested parameter "'.$param.'" is empty where it should not be');
			}
			return '';
		}
		// everything else is fine
		return self::$parameters[$param];
	}

	/**
	 * set specific parameter from the parameterlist;
	 *
	 * @param string $param
	 * @param mixed $new_value
	 *
	 * @throws FroxlorModuleException
	 * @return mixed
	 */
	protected static function setParam($param = null, $new_value = null) {
		self::$parameters[$param] = $new_value;
	}
}
