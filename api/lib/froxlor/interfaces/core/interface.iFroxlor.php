<?php

/**
 * Froxlor API interface
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
 * Interface for main api class
 * 
 * @copyright  (c) the authors
 * @author     Froxlor team <team@froxlor.org> (2013-)
 * @license    GPLv2 http://files.froxlor.org/misc/COPYING.txt
 * @category   core
 * @package    API
 * @since      0.99.0
 */
interface iFroxlor extends iApiVersion {

	/**
	 * return the currently used API version.
	 * The API version can be overwritten in
	 * the constructor of the Froxlor-class
	 *
	 * @return string currently used API version
	 */
	public static function getApiVersion();

	/**
	 * return the instanciated Froxlor-object
	 * only used to place api-calls "internally"
	 *
	 * @throws ApiException
	 * @return Froxlor
	 * @internal
	 */
	public static function getApi();

	/**
	 * sends an API-request to the request-handler
	 *
	 * @param ApiRequest $api_req object of the ApiRequest class
	 *
	 * @return bool whether the request was sent successfully or not
	*/
	public function sendRequest($api_req = null);

	/**
	 * this function returns an ApiResponse object
	 * of the last request.
	 *
	 * @return ApiResponse object of ApiResponse class
	*/
	public function getLastResponse();
}
