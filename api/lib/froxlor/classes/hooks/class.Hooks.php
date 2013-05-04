<?php

/**
 * Hooks class
 *
 * This class provides functions to manage and call hooks
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

define('HOOK_CACHE_TIME', 60 * 60 * 3); // 3 hours

/**
 * Class Hooks
 *
 * This class provides functions to manage and call hooks
 *
 * @copyright  (c) the authors
 * @author     Froxlor team <team@froxlor.org> (2013-)
 * @license    GPLv2 http://files.froxlor.org/misc/COPYING.txt
 * @category   core
 * @package    API
 * @since      0.99.0
*/
class Hooks {

	/**
	 * search for every module whether it includes
	 * a public function named as the hookname (which should
	 * of course be including the Module-name to avoid
	 * conflicts!) and calls it with given parameter
	 *
	 * @param string $hookname
	 * @param mixed $params
	 *
	 * @return nothing
	 */
	public static function callHooks($hookname = null, $params = null) {

		$_cachefile = dirname(__FILE__).'/~hookscache.'.$hookname.'.php';

		// check for cache
		$_recache = true;
		if (@file_exists($_cachefile)) {
			$_recache = false;
			require $_cachefile;
			if (($cache_ts + HOOK_CACHE_TIME) < time()) {
				@unlink($_cachefile);
				$_recache = true;
			}
		}

		if ($_recache) {

			$cachedata = array();

			// modules path
			$paths = array(
					FROXLOR_API_DIR . '/modules/',
			);
			// now iterate through the paths
			foreach ($paths as $path) {
				// valid directory?
				if (is_dir($path)) {
					// create RecursiveIteratorIterator
					$its = new RecursiveIteratorIterator(
							new RecursiveDirectoryIterator($path)
					);

					// check every file
					foreach ($its as $fullFileName => $it ) {
						// does it match the Filename pattern?
						if (preg_match("/^module\.(.+)\.php$/i", $it->getFilename())) {
							// indicator if we can call the function in that module
							$useable = true;
							// get module-name out of filename
							$module = substr($it->getFilename(), 7, -4);
							// check whether function exists in module
							try {
								$refl = new ReflectionMethod($module, $hookname);
								if ($refl->isPublic() == false) {
									// hook-function found but is not available for us :/
									$useable = false;
								}
							} catch (Exception $e) {
								// not found, never mind
								$useable = false;
							}
							// call the hook-function
							if ($useable) {
								$cachedata[] = $module;
							}
							/**
							 * call_user_func does not pass the $params variable
							 * as reference so a hook-function cannot change its
							 * data :/
							 *
							 call_user_func(array($mod, $hookname), $params);
							 */
						}
					}

					$fh = fopen($_cachefile, 'w');
					if ($fh) {
						$cfdata = "<?php\n\$cachedata = array();\n";
						foreach ($cachedata as $module) {
							$cfdata .= "\$cachedata[] = '".$module."';\n";
						}
						$cfdata .= "\$cache_ts = '".time()."';\n";
						fwrite($fh, $cfdata);
						fclose($fh);
					}
				} else {
					// yikes - no valid directory to check
					break;
				}
			}
		}

		foreach ($cachedata as $module) {
			try {
				$mod = new $module();
				$mod->{$hookname}($params);
			} catch (Exception $e) {
				// log error but go on
				ApiLogger::error("Error in execution of hook '".$hookname."'");
				ApiLogger::error($e->getMessage());
			}
		}
	}
}
