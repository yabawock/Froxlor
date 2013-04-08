<?php

/**
 * ApiRequest interface
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
 * Interface ApiRequest
 *
 * @copyright  (c) the authors
 * @author     Froxlor team <team@froxlor.org> (2010-)
 * @license    GPLv2 http://files.froxlor.org/misc/COPYING.txt
 * @category   core
 * @package    API
 * @since      0.99.0
 */
interface iApiRequest {

	/**
	 * this function is a wrapper for developers
	 * to create a valid request to send to the api
	 *
	 * @param string $function function-name
	 * @param array  $params   parameter-array (optional)
	 *
	 * @return ApiRequest api-request-object
	 */
	public static function createRequest($function = null, $params = null);

	/**
	 * The module where the function can be found
	 * For example: Core
	 *
	 * @internal only for internal use
	 *
	 * @param string $mod module-name
	 *
	 * @return bool
	*/
	public function setModule($mod = null);

	/**
	 * The function which is to be called.
	 * For example: getApiVersion
	 *
	 * @internal only for internal use
	 *
	 * @param string $func function-name
	 *
	 * @return bool
	*/
	public function setFunction($func = null);

	/**
	 * Use this function to set several parameters
	 * for the given function you want to call.
	 *
	 * The setFucntion()-call can be followed by an
	 * unspecified amount of setParam()-calls.
	 *
	 * The parameters can be passed in the following
	 * structure:
	 * - name
	 * - group.name
	 * - group.subgroup.name
	 * - etc.
	 *
	 * @internal only for internal use
	 *
	 * @param string $params parameter-name (optionally with grouping)
	 * @param mixed  $value  value for the parameter
	 *
	 * @return bool
	*/
	public function setParam($param = null, $value = null);

	/**
	 * This function returns a valid API-request for
	 * the API-object in PHP-array format.
	 *
	 * @internal only for internal use
	 *
	 * @return array API-request
	*/
	public function getRequest();

	/**
	 * returns, if set, the client transmission id of
	 * the current request
	 *
	 * @internal only for internal use
	 *
	 * @return string|null either a ClTrId if exists or null
	*/
	public function getTransId();
}
