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
	public static function setParamList(array $params = null) {
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
	 * @param bool $optional
	 * @param mixed $default value which is returned if optional=true and param is not set
	 *
	 * @throws FroxlorModuleException
	 * @return mixed
	 */
	protected static function getParam($param = null, $optional = false, $default = '') {
		// does it exist?
		if (!isset(self::$parameters[$param])) {
			if ($optional == false) {
				throw new FroxlorModuleException(404, 'Requested parameter "'.$param.'" could not be found');
			}
			return $default;
		}
		// is it empty? (if not allowed)
		if (self::$parameters[$param] == '') {
			if ($optional == false) {
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

	/**
	 * returns an array of parameters when given
	 * a parameter-ident-string, e.g. Module.section.something
	 * results in: array(0 => Module, 1 => section, 2 => something)
	 * optionally checks the required amount of elements if > 0
	 *
	 * @param string $param
	 * @param int $required_elements
	 * @param bool $optional
	 *
	 * @throws FroxlorModuleException
	 * @return array
	 */
	protected static function getParamIdent($param = null, $required_elements = 0, $optional = false) {

		$param = self::getParam($param, $optional);

		$params = explode('.', $param);
		// validate it
		if (!is_array($params)
				|| ($required_elements > 0 && count($params) != $required_elements)
		) {
			throw new ApiException(406, 'invalid parameter list given');
		}

		return $params;
	}
}
