<?php

/**
 * Froxlor API Core-Module
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
 * @category   Modules
 * @package    API
 * @since      0.99.0
 */

/**
 * Class Core
 *
 * @copyright  (c) the authors
 * @author     Froxlor team <team@froxlor.org> (2010-)
 * @license    GPLv2 http://files.froxlor.org/misc/COPYING.txt
 * @category   Modules
 * @package    API
 * @since      0.99.0
 */
class Core extends FroxlorModule implements iCore {

	/**
	 * @see iCore::statusVersion()
	 *
	 * @return string version
	 */
	public static function statusVersion() {
		return ApiResponse::createResponse(
				200,
				null,
				array('version' => Froxlor::API_RELEASE_VERSION)
		);
	}

	/**
	 * @see iCore::statusApiVersion()
	 *
	 * @return string version
	 */
	public static function statusApiVersion() {
		return ApiResponse::createResponse(
				200,
				null,
				array('version' => Froxlor::getApiVersion())
		);
	}

	/**
	 * @see iCore::statusUpdate()
	 *
	 * @return string
	 */
	public static function statusUpdate() {

		// define URL to check
		$update_check_uri = 'http://version.froxlor.org/Froxlor/api/' . Froxlor::API_RELEASE_VERSION;

		if (!function_exists('curl_init')) {
			// awww, we can't check, just post where they shall go
			return ApiResponse::createResponse(
					404,
					array(
							"Could not find the PHP cURL extension to automatically check.",
							"Please visit '".$update_check_uri."/pretty' to check manually for a new version of froxlor."
					),
					null
			);
		} else {

			$ch = curl_init();
			// set url
			curl_setopt($ch, CURLOPT_URL, $update_check_uri);
			// no header in result
			curl_setopt($ch, CURLOPT_HEADER, 0);
			// return result
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			// timeout
			curl_setopt($ch, CURLOPT_TIMEOUT, 10);
			// now check
			$latestversion = curl_exec($ch);
			// clear
			curl_close($ch);
			// split
			// 0 => version
			// 1 => info-link
			// 2 => whether it's a testing-version or not
			// 3 => extra message
			$_vinfo = explode('|', $latestversion);

			$result_data = array(
					'version' => Froxlor::API_RELEASE_VERSION,
					'online_version' => $_vinfo[0],
					'online_info' => $_vinfo[1],
					'online_extra_message' => (isset($_vinfo[3]) && $_vinfo[3] != '' ? $_vinfo[3] : null),
					'is_newer' => (Module::cmpFroxlorVersions(Froxlor::API_RELEASE_VERSION, $_vinfo[0]) == -1 ? 1 : 0),
					'is_testing' => ((int)$_vinfo[2] == 1 ? 1 : 0),
			);

			// call beforeReturn hooks
			Hooks::callHooks('statusUpdate_beforeReturn', $result_data);

			// create response
			return ApiResponse::createResponse(
					200,
					null,
					$result_data
			);
		}
	}

	/**
	 * @see iCore::statusSystem()
	 *
	 * @return array
	 */
	public static function statusSystem() {

		// check permissions
		$user = self::getParam('_userinfo');
		if (!self::isAllowed($user, 'Core.statusSystem')) {
			throw new ApiException(403, 'You are not allowed to access this function');
		}

		// PHP version
		$phpversion = phpversion();
		// PHP memory limit
		$phpmemorylimit = @ini_get("memory_limit");
		// PHP SAPI
		$webserverinterface = strtoupper(@php_sapi_name());

		// get PDO Object
		$pdo = Database::getDatabaseAdapter()->getDatabase();
		// DB Type
		$dbtype = $pdo->getDatabaseType();
		// DB Version
		$dbversion = $pdo->getDatabaseVersion();

		// load average
		if (function_exists('sys_getloadavg')) {
			$loadArray = sys_getloadavg();
			$load = number_format($loadArray[0], 2, '.', '') . " / " . number_format($loadArray[1], 2, '.', '') . " / " . number_format($loadArray[2], 2, '.', '');
		} else {
			$load = @file_get_contents('/proc/loadavg');
			if (!$load) {
				$load = 'unknown';
			}
		}

		// Kernel
		if (function_exists('posix_uname')) {
			$kernel_nfo = posix_uname();
			$kernel = $kernel_nfo['release'] . ' (' . $kernel_nfo['machine'] . ')';
		} else {
			$kernel = 'unknown';
		}

		// Server uptime
		$uptime_array = explode(" ", @file_get_contents("/proc/uptime"));

		if (is_array($uptime_array)
				&& isset($uptime_array[0])
				&& is_numeric($uptime_array[0])
		) {
			// Some calculatioon to get a nicly formatted display
			$seconds = round($uptime_array[0], 0);
			$minutes = $seconds / 60;
			$hours = $minutes / 60;
			$days = floor($hours / 24);
			$hours = floor($hours - ($days * 24));
			$minutes = floor($minutes - ($days * 24 * 60) - ($hours * 60));
			$seconds = floor($seconds - ($days * 24 * 60 * 60) - ($hours * 60 * 60) - ($minutes * 60));
			$uptime = "{$days}d, {$hours}h, {$minutes}m, {$seconds}s";
			// Just cleanup
			unset($uptime_array, $seconds, $minutes, $hours, $days);
		} else {
			$uptime = 'unknown';
		}

		$result_data = array(
				'froxlor_version' => Froxlor::API_RELEASE_VERSION,
				'php_version' => $phpversion,
				'php_sapi' => $webserverinterface,
				'php_memorylimit' => $phpmemorylimit,
				'db_type' => $dbtype,
				'db_version' => $dbversion,
				'server_load' => $load,
				'server_kernel' => $kernel,
				'server_uptime' => $uptime
		);

		// call beforeReturn hooks
		Hooks::callHooks('statusSystem_beforeReturn', $result_data);

		return ApiResponse::createResponse(
				200, null,
				$result_data
		);
	}

	/**
	 * @see iCore::listApiFunctions()
	 *
	 * @param string $module optional only list functions of specific module
	 *
	 * @throws CoreException
	 * @return array
	 */
	public static function listApiFunctions() {

		$module = self::getParam('module', true, null);

		$functions = array();
		if ($module != null) {
			// check for existence
			Module::requireModules($module);
			// now get all static functions
			$reflection = new ReflectionClass($module);
			$_functions = $reflection->getMethods(ReflectionMethod::IS_STATIC);
			foreach ($_functions as $func) {
				if ($func->class == $module && $func->isPublic()) {
					array_push($functions, array(
					'function' => $func->name,
					'module' => $func->class
					));
				}
			}
		} else {
			// check all the modules
			$path = FROXLOR_API_DIR . '/modules/';
			// valid directory?
			if (is_dir($path)) {
				// create RecursiveIteratorIterator
				$its = new RecursiveIteratorIterator(
						new RecursiveDirectoryIterator($path)
				);
				// check every file
				foreach ($its as $fullFileName => $it ) {
					// does it match the Filename pattern?
					$matches = array();
					if (preg_match("/^module\.(.+)\.php$/i", $it->getFilename(), $matches)) {
						// check for existence
						try {
							Module::requireModules($matches[1]);
						} catch (ApiException $e) {
							ApiLogger::warn($e->getMessage());
							continue;
						}
						// now get all static functions
						$reflection = new ReflectionClass($matches[1]);
						$_functions = $reflection->getMethods(ReflectionMethod::IS_STATIC);
						foreach ($_functions as $func) {
							if ($func->class == $matches[1] && $func->isPublic()) {
								array_push($functions, array(
								'function' => $func->name,
								'module' => $func->class
								));
							}
						}
					}
				}
			} else {
				// yikes - no valid directory to check
				throw new CoreException(500, "Cannot search directory '".$path."'. No such directory.");
			}
		}

		// return the list
		return ApiResponse::createResponse(200, null, $functions);
	}

	/**
	 * @see iCore::doLogin()
	 *
	 * @param string $username
	 * @param string $password encrypted password
	 *
	 * @return array user-bean as array
	 */
	public static function doLogin() {

		$username = self::getParam('username');
		$password = self::getParam('password');

		$user = Database::findOne('users', ' name = :name AND password = :cryptedpasswd ',
				array(
						':name' => $username,
						':cryptedpasswd' => $password
				)
		);
		if ($user !== null) {
			// set the api key
			Froxlor::getApi()->setApiKey($user->apikey);
			// hide important data
			$user->apikey = null;
			$user->password = null;

			if (!self::_validateApiKey($user)) {
				throw new CoreException(406, 'Sorry, you are not allowed to access the API');
			}
			// return the user bean
			return ApiResponse::createResponse(200, null, $user->export());
		}
		// too bad :/
		throw new CoreException(406, 'Sorry, your login was unsuccessfull');
	}

	/**
	 * check if a user is allowed to use the api
	 *
	 * @param $user user-bean-array
	 *
	 * @return bool
	 */
	private static function _validateApiKey($user) {

		if ($user !== null) {

			$api_response = Froxlor::getApi()->apiCall(
					'Permissions.statusUserPermission',
					array('userid' => $user->id, 'ident' => 'Core.useAPI')
			);

			if ($api_response->getResponseCode() == 200) {
				Froxlor::getApi()->setUser($user);
				return true;
			}
		}
		return false;
	}
	/**
	 * @see iCore::doSetup();
	 *
	 * @return null
	 */
	public static function doSetup() {
		// call the hook to run Core_moduleSetup on all modules
		Hooks::callHooks('Core_moduleSetup');
		return ApiResponse::createResponse(200, 'Setup finished without errors');
	}

	/**
	 * @see iCore::listParams()
	 *
	 * @param string $ident a module.function ident
	 *
	 * @throws CoreException
	 * @return array all parameters and the return-type of the given module-function
	 */
	public static function listParams() {
		$ident = self::getParamIdent('ident', 2);
		$result = self::_getParamListFromDoc($ident[0], $ident[1]);

		if ($result === false) {
			throw new CoreException(404, 'No parameter list found for "'.implode('.', $ident).'". The function might not exist though');
		}
		return $result;
	}

	/**
	 * generate an api-response to list all parameters and the return-value of
	 * a given module.function-combination
	 *
	 * @param string $module
	 * @param string $function
	 *
	 * @throws FroxlorModuleException
	 * @return array|bool
	 */
	private static function _getParamListFromDoc($module = null, $function = null) {
		try {
			$cls = new ReflectionMethod($module, $function);
			$comment = $cls->getDocComment();
			if ($comment == false) {
				throw new FroxlorModuleException(404, 'There is no comment-block for "'.$module.'.'.$function.'"');
			}
			$clines = explode("\n", $comment);
			$result = array();
			$result['params'] = array();
			foreach ($clines as $c) {
				$c = trim($c);
				// check param-section
				if (strpos($c, '@param')) {
					preg_match('/^\*\s\@param\s(\w+)\s(\$\w+)(\s.*)?/', $c, $r);
					// cut $ off the parameter-name as it is not wanted in the api-request
					$result['params'][] = array('parameter' => substr($r[2], 1), 'type' => $r[1], 'desc' => (isset($r[3]) ? trim($r['3']) : ''));
				}
				// check return-section
				elseif (strpos($c, '@return')) {
					preg_match('/^\*\s\@return\s(\w+)(\s.*)?/', $c, $r);
					if (!isset($r[0]) || empty($r[0])) {
						$r[1] = 'null';
						$r[2] = 'This function has no return value given';
					}
					$result['return'] = array('type' => $r[1], 'desc' => (isset($r[2]) ? trim($r[2]) : ''));
				}
			}
			return ApiResponse::createResponse(200, null, $result);
		} catch (ReflectionException $e) {
			return false;
		}
	}

	/**
	 * (non-PHPdoc)
	 * @see FroxlorModule::Core_moduleSetup()
	 */
	public function Core_moduleSetup() {
	}
}
