<?php

/**
 * ApiResponse interface
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
 * Interface ApiResponse
 *
 * @copyright  (c) the authors
 * @author     Froxlor team <team@froxlor.org> (2010-)
 * @license    GPLv2 http://files.froxlor.org/misc/COPYING.txt
 * @category   core
 * @package    API
 * @since      0.99.0
 */
interface iApiResponse {

	/**
	 * this function is a wrapper for module-developers
	 * to create a valid response for their functions
	 * 
	 * @param int          $code    general return code
	 * @param string|array $message additional error-messages (optional)
	 * @param array        $content body of the response (optional)
	 * 
	 * @return array
	 */
	public static function createResponse($code = 200, $message = null, $content = null);

	/**
	 * add an api-response to the internal array
	 * (output of function createResponse() is expected)
	 * 
	 * @internal only used in Froxlor()
	 * 
	 * @param array  $response response from api-request (php-array)
	 * 
	 * @return bool
	 */
	public function addResponse($response = null);

	/**
	 * returns API response status of a request
	 * which is either 0 (success) or an error code
	 * 
	 * @return int status-code
	 */
	public function getResponseCode();

	/**
	 * returns the raw API response as php-array
	 * 
	 * @return array
	 */
	public function getResponse();

	/**
	 * returns the response data-body as php-array
	 *
	 * @return array|null
	 */
	public function getData();

}
