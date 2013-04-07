<?php

/**
 * Froxlor API Class
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
 * Froxlor main-api class
 *
 * This class provides functions to interact with
 * the Froxlor API. Every request and response goes
 * through this class.
 *
 * @copyright  (c) the authors
 * @author     Froxlor team <team@froxlor.org> (2010-)
 * @license    GPLv2 http://files.froxlor.org/misc/COPYING.txt
 * @category   core
 * @package    API
 * @since      0.99.0
 */
class Froxlor implements iFroxlor {

	/**
	 * static object holder
	 * @var Froxlor
	 */
	private static $_frx = null;

	/**
	 * api version indicator
	 * @var string
	 */
	private static $_apiversion = self::API_VERSION;

	/**
	 * internal storage for the
	 * API request in PHP-array format
	 *
	 * @var array
	 */
	private $_request = null;

	/**
	 * internal storage for API responses
	 * @var object ApiResponse
	 */
	private $_lastrepsonse = null;

	/**
	 * the users api-key, used to identify the user
	 */
	private $_apikey = null;

	/**
	 * the data of the api-key user
	 */
	private $_userinfo = null;

	/**
	 * main constructor for Froxlor-API
	 * optionally set the api-version if needed
	 *
	 * @param string $api_key     user api key
	 * @param string $api_version API-version that should be used
	 *
	 * @return null
	 *
	 * @throws ApiException
	 */
	private function __construct($api_key = null, $api_version = null) {

		// set system default
		self::$_apiversion = self::API_VERSION;
		$this->_apikey = $api_key;

		// if api-version is set
		if ($api_version != null
				&& $api_version != ''
		) {
			// overwrite the default value
			self::$_apiversion = $api_version;
		}

		// check prerequisites
		$this->_checkPrerequisites();
	}

	/**
	 * get instance of Froxlor object (singleton)
	 * optionally set the api-version if needed
	 *
	 * @param string $api_key     user api key
	 * @param string $api_version API-version that should be used
	 *
	 * @return Froxlor
	 */
	public static function getInstance($api_key = null, $api_version = null) {
		if ($api_version == null) {
			$api_version = self::API_VERSION;
		}
		if (!is_array(self::$_frx)) {
			self::$_frx = array();
		}
		if (!isset(self::$_frx[$api_key.$api_version])) {
			self::$_frx[$api_key.$api_version] = new Froxlor($api_key, $api_version);
		}
		return self::$_frx[$api_key.$api_version];
	}

	/**
	 * @see iFroxlor::getApiVersion()
	 *
	 * @return string currently used API version
	 */
	public static function getApiVersion() {
		return self::$_apiversion;
	}

	/**
	 * summarize-function to send an api call
	 * and return the response-object
	 *
	 * @param string $function function name
	 * @param array  $paramter optional parameter-array
	 *
	 * @return ApiResponse
	 */
	public function apiCall($function = null, $paramter = null) {
		$req = ApiRequest::createRequest($function, $paramter);
		$res = $this->sendRequest($req);
		return $this->getLastResponse();
	}

	/**
	 * @see iFroxlor::sendRequest()
	 *
	 * @param ApiRequest $api_req object of the ApiRequest class
	 *
	 * @return bool whether the request was sent successfully or not
	 */
	public function sendRequest($api_req = null) {

		// validate request
		if ($api_req instanceof ApiRequest) {

			// pass request to internal array
			$this->_request = $api_req->getRequest();

			// define request-result
			$req_result = null;
			$api_response = new ApiResponse($api_req->getTransId());

			// give it a shot :p
			try {

				$mod = $api_req->getModule();
				$fun = $api_req->getFunction();

				ApiLogger::debug('Calling '.$mod.'::'.$fun);

				// check whether function exists in module
				try {
					$refl = new ReflectionMethod($mod, $fun);
					if ($refl->isStatic() == false) {
						throw new ApiException(503, 'Function '.$fun.' is not available in module '.$mod);
					}
				} catch (Exception $e) {
					throw new ApiException(404, 'Function '.$fun.' could not be found in module '.$mod);
				}

				// append admin/reseller data to the request so we use it internally
				$this->_request['body']['_userinfo'] = $this->_userinfo;
				$req_result = call_user_func($mod.'::'.$fun, $this->_request['body']);

			} catch(ApiException $e) {

				$req_result = $this->_handleException($e);

			}

			// set response value
			$api_response->addResponse($req_result);

			// set lastresponse variable
			$this->_lastrepsonse = $api_response;

			// return
			return true;
		}
		return false;
	}

	/**
	 * @see iFroxlor::getLastResponse()
	 *
	 * @return ApiResponse object of ApiResponse class
	 */
	public function getLastResponse() {
		return $this->_lastrepsonse;
	}

	/**
	 * check the api key against the admins-table
	 *
	 * @return boolean
	 */
	private function _validateApiKey() {

		/**
		 * FIXME testing code
		 */
		$usertable = Database::dispense('users');
		$usertable->apikey = 'mysupersecretkey';
		$usertable->name = 'admin';
		Database::store($usertable);

		$user = Database::findOne('users', ' apikey = ? ', array($this->_apikey));
		if ($user !== null) {
			$this->_userinfo = $user;
			// don't include password and api-key
			$this->_userinfo->apikey = null;
			$this->_userinfo->password = null;
			return true;
		}
		return false;
	}

	/**
	 * check PHP and needed extensions,
	 * api key and valid api-user
	 *
	 * @throws ApiException
	 */
	private function _checkPrerequisites() {

		// FIXME auto-detect or similar
		@date_default_timezone_set('Europe/Berlin');

		// check php version
		if (PHP_VERSION_ID < 50300) {
			throw new ApiException(503, 'Froxlor api requires PHP-5.3 or newer');
		}
		// php pdo extension
		if (!extension_loaded('PDO')) {
			throw new ApiException(503, 'Froxlor api requires PHP compiled with PDO support');
		}
		// check database conf
		$_dbconf = FROXLOR_API_DIR.'/conf/db.inc.php';
		if (!file_exists($_dbconf)) {
			throw new ApiException(503, 'Cannot connect to database due to missing config file');
		}
		if (!is_readable($_dbconf)) {
			throw new ApiException(503, 'Cannot connect to database due to missing permission to read the config file');
		}

		include $_dbconf;
		$driver = $dbconf["db_driver"];
		unset($dbconf);
		$ext = strstr($driver, ":", true);
		// check pdo_extension for used dbms
		if (!in_array($ext, PDO::getAvailableDrivers())) {
			throw new ApiException(503, 'Froxlor api requires PDO compiled with '.$ext.' support');
		}

		// api key
		if ($this->_apikey == null || $this->_apikey == '') {
			throw new ApiException(403, 'No API key set');
		}
		if (!$this->_validateApiKey()) {
			throw new ApiException(403, 'No valid API user. Check your API key');
		}
	}

	/**
	 * Log the error and return a nice ApiResponse object
	 *
	 * @param ApiException $e
	 * @return ApiResponse
	 */
	private function _handleException($e) {

		if (get_class($e) != 'LoggerException') {
			ApiLogger::setErrorException($e);
			ApiLogger::info('Exception occured: '.$e->getMessage());
			ApiLogger::setErrorException(null);
		}

		// response == error
		$req_result = unserialize((string)$e);
		// in case an internal error occured
		if ($req_result === false) {
			$_e = new ApiException(500, (string)$e);
			$req_result = unserialize((string)$_e);
		}

		return $req_result;
	}
}
