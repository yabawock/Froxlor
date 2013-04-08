<?php

/**
 * ApiResponse class
 *
 * This class provides functions to work with
 * API-responses from the Froxlor API (internal PHP-array
 * api protocol) and manage the output
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
 * Class ApiResponse
 *
 * This class provides functions to work with
 * API-responses from the Froxlor API (internal PHP-array
 * api protocol) and manage the output
 *
 * @copyright  (c) the authors
 * @author     Froxlor team <team@froxlor.org> (2010-)
 * @license    GPLv2 http://files.froxlor.org/misc/COPYING.txt
 * @category   core
 * @package    API
 * @since      0.99.0
 */
class ApiResponse implements iApiResponse {

	/**
	 * internal storage of the response-data
	 *
	 * @var array
	 */
	private $_data = null;

	/**
	 * internal storage of the request-id
	 *
	 * @var array
	 */
	private $_transid = null;

	/**
	 * main constructor for an ApiResponse
	 *
	 * @param string|null $cltrid client transmission id
	 */
	public function __construct($cltrid = null) {

		// initialize internal data-array
		$this->_data = array();

		// set transmission id
		$this->_transid = $cltrid;
	}

	/**
	 * @see iApiResponse::createResponse()
	 *
	 * @param int          $code    general return code
	 * @param string|array $message additional error-messages (optional)
	 * @param array        $content body of the response (optional)
	 *
	 * @return array
	 */
	public static function createResponse($code = 200, $message = null, $content = null) {

		// initialize variable
		$result = array();

		// check whether we have detailed messages
		if ($message != null
				&& !is_array($message)
		) {
			$desc = array($message);
		} else {
			$desc = $message;
		}

		// main body
		$result = array(
				'header' => array(
						'version' => Froxlor::getApiVersion(),
						'code' => $code,
						'description' => ApiCodes::getCodeDescription($code)
				),
				'body' => $content
		);

		// optional detailed_messages
		if (isset($desc)
				&& is_array($desc)
				&& count($desc) > 0
		) {
			$result['header']['detailed_messages'] = $desc;
		}

		if ($code != 200) {
			ApiLogger::debug('Returning status '.$code.' ('.ApiCodes::getCodeDescription($code).'):');
			if (is_array($desc) && isset($desc[0])) {
				foreach($desc as $msg) {
					ApiLogger::debug($code.': '.$msg);
				}
			}
		}
		return $result;
	}

	/**
	 * @see iApiResponse::addResponse()
	 *
	 * @internal only used in Froxlor()
	 *
	 * @param array $response response from api-request (php-array)
	 *
	 * @return bool
	 */
	public function addResponse($response = null) {

		// add response to internal array
		$this->_data = $response;

		// add transaction id to the theader
		if (!isset($this->_data['header']['cltrid'])) {
			$this->_data['header']['cltrid'] = $this->_transid;
		}

		// return
		return true;
	}

	/**
	 * @see iApiResponse::getResponseCode()
	 *
	 * @return int status-code
	 */
	public function getResponseCode() {
		// return
		return isset($this->_data['header']['code']) ? (int)$this->_data['header']['code'] : -1;
	}

	/**
	 * @see iApiResponse::getResponse()
	 *
	 * @return array
	 */
	public function getResponse() {
		return $this->_data;
	}

	/**
	 * @see iApiResponse::getData()
	 *
	 * @return array|null
	 */
	public function getData() {
		return isset($this->_data['body']) ? $this->_data['body'] : null;
	}
}
