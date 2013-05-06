<?php

/**
 * ApiRequest class
 *
 * This class provides functions to build up a valid
 * API-request for the Froxlor API (internal PHP-array
 * api protocol)
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
 * Class ApiRequest
 *
 * This class provides functions to build up a valid
 * API-request for the Froxlor API (internal PHP-array
 * api protocol)
 *
 * @copyright  (c) the authors
 * @author     Froxlor team <team@froxlor.org> (2013-)
 * @license    GPLv2 http://files.froxlor.org/misc/COPYING.txt
 * @category   core
 * @package    API
 * @since      0.99.0
 */
class ApiRequest {

	/**
	 * internal storage of the request-data
	 *
	 * @var array
	 */
	private $_data = null;

	/**
	 * internal storage for the final
	 * API request in PHP-array format
	 *
	 * @var array
	 */
	private $_request = null;

	/**
	 * client transmission id
	 *
	 * @var string
	 */
	private $_cltrid = null;

	/**
	 * main constructor for an ApiRequest
	 */
	public function __construct() {

		// initialize internal data-array
		$this->_data = array();

		// create client transmission id
		$this->_cltrid = $this->_generateClTrID();
	}

	/**
	 * this function is a wrapper for developers
	 * to create a valid request to send to the api
	 *
	 * @param string $function function-name
	 * @param array  $params   parameter-array (optional)
	 *
	 * @return ApiRequest api-request-object
	 */
	public static function createRequest($function = null, $params = null) {

		// initialize class
		$request = new self();

		// separate module and function
		if (strpos($function, '.') === false) {
			$request->setModule('Core');
		} else {
			$request->setModule(substr($function, 0, strpos($function, '.')));
			$function = substr($function, strpos($function, '.')+1);
		}

		// set the function
		$request->setFunction($function);

		// check for parameters
		if (isset($params)
				&& is_array($params)
		) {
			// add parameters to request
			foreach ($params as $param => $value) {
				$request->setParam($param, $value);
			}
		}

		// return valid API-request array
		return $request;
	}

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
	public function setModule($mod = null) {

		// set the module
		$this->_data['request_module'] = $mod;

		// return
		return true;
	}

	/**
	 * Returns the module which is related to that request
	 * For example: Core
	 *
	 * @return string
	 */
	public function getModule() {
		return (isset($this->_data['request_module']) ? $this->_data['request_module'] : '');
	}

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
	public function setFunction($func = null) {

		// validate function roughly
		if (preg_match('/^(\w+)$/', $func) == false) {
			throw new ApiException(406, 'The given function name is not valid');
		}

		// set the function
		$this->_data['request_function'] = $func;

		// return
		return true;
	}

	/**
	 * Returns the function which is to be called in that request
	 * For example: statusVersion
	 *
	 * @return string
	 */
	public function getFunction() {
		if (!isset($this->_data['request_function'])
				|| $this->_data['request_function'] == ''
		) {
			throw new ApiException(406, 'No function given. I don\'t know what to do :/');
		}
		return $this->_data['request_function'];
	}

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
	 * @param string $param parameter-name (optionally with grouping)
	 * @param mixed  $value  value for the parameter
	 *
	 * @return bool
	 */
	public function setParam($param = null, $value = null) {
		// avoid undefined indexes
		if (!isset($this->_data['request_params'])) {
			$this->_data['request_params'] = array();
		}
		// set the parameter as index with value
		$this->_data['request_params'][$param] = $value;
		return true;
	}

	/**
	 * This function returns a valid API-request for
	 * the API-object in PHP-array format.
	 *
	 * @internal only for internal use
	 *
	 * @return array API-request
	 */
	public function getRequest() {
		/**
		 * if $_request is not set
		 * build the api structure
		 */
		if ($this->_request == null
				|| $this->_request == ''
		) {
			$this->_buildApiStructure();
		}
		return $this->_request;
	}

	/**
	 * returns, if set, the client transmission id of
	 * the current request
	 *
	 * @internal only for internal use
	 *
	 * @return string|null either a ClTrId if exists or null
	 */
	public function getTransId() {
		$result = null;
		if (isset($this->_request['header']['cltrid'])) {
			$result = $this->_request['header']['cltrid'];
		}
		return $result;
	}

	/**
	 * builds the specific api-structure
	 * from the data in the internal
	 * storage $_data
	 *
	 * @return array
	 */
	private function _buildApiStructure() {
		$this->_request = array (
				// API header
				'header' => $this->_buildApiHeader(),
				// API body
				'body' => $this->_buildApiBody()
		);
	}

	/**
	 * build up standard API-header php-array
	 *
	 * @return array
	 */
	private function _buildApiHeader() {
		$result = array(
				'version' => Froxlor::getApiVersion(),
				'module' => (isset($this->_data['request_module']) ? $this->_data['request_module'] : ''),
				'function' => (isset($this->_data['request_function']) ? $this->_data['request_function'] : ''),
				'cltrid' => $this->_cltrid
		);
		return $result;
	}

	/**
	 * build up standard API-body php-array
	 *
	 * @return array
	 */
	private function _buildApiBody() {

		// initialize params variable
		$result = null;

		// check for parameters
		if (isset($this->_data['request_params'])
				&& count($this->_data['request_params']) > 0
		) {
			$result = $this->_buildApiBodyParameters();
		}

		// return it
		return $result;
	}

	/**
	 * creates an array for given parameters
	 *
	 * @return array
	 */
	private function _buildApiBodyParameters() {
		$result = array();
		// split group.subgroup.name parameters into array
		// @TODO if group/subgroup occurs more than once
		//       summarize them in one array
		foreach ($this->_data['request_params'] as $param => $value) {
			$result[$param] = $value;
		}
		//$result = $this->_data['request_params'];
		return $result;
	}

	/**
	 * make an array out of a function parameter-string,
	 * e.g. group.subgroup.name
	 *
	 * @param array $_param parameter-string as array
	 * @param mixed $_value value to set for the parameter
	 *
	 * @return array
	 *
	 private function _setParamValue($_param, $_value) {
		$result = array();
		if (count($_param) > 1) {
		$_p = $_param[0];
		unset($_param[0]);
		$_param = array_values($_param);
		$result[$_p] = $this->_setParamValue($_param, $_value);
		} else {
		$result[$_param[0]] = $_value;
		}
		return $result;
		}
		*/

	/**
	 * generates a random client transaction ID (UUID)
	 * for the API-header
	 *
	 * @param string $prefix an optional prefix
	 *
	 * @return string
	 */
	private function _generateClTrID($prefix = '') {
		$chars = md5(uniqid(mt_rand(), true));
		$uuid  = substr($chars, 0, 8) . '-';
		$uuid .= substr($chars, 8, 4) . '-';
		$uuid .= substr($chars, 12, 4) . '-';
		$uuid .= substr($chars, 16, 4) . '-';
		$uuid .= substr($chars, 20, 12);
		return $prefix . $uuid;
	}

}
