<?php

/**
 * Module Class
 *
 * this class provides helper functions for basic
 * maintenance stuff, like version check, deprecation, etc.
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
 * Class Module
 *
 * this class provides helper functions for basic
 * maintenance stuff, like version check, deprecation, etc.
 *
 * @copyright  (c) the authors
 * @author     Froxlor team <team@froxlor.org> (2013-)
 * @license    GPLv2 http://files.froxlor.org/misc/COPYING.txt
 * @category   core
 * @package    API
 * @since      0.99.0
 */
class Module {

	/**
	 * this functions is used to check the availability
	 * of a given list of modules. If either one of
	 * them are not found, throw an ApiException
	 *
	 * @param string|array $modules
	 *
	 * @throws ApiException
	 */
	public static function requireModules($modules = null) {

		if ($modules != null) {
			// no array -> create one
			if (!is_array($modules)) {
				$modules = array($modules);
			}
			// check all the modules
			foreach ($modules as $module) {
				try {
					// can we use the class?
					if (class_exists($module)) {
						continue;
					} else {
						throw new ApiException(404, 'The required class "'.$module."' could not be found but the module-file exists");
					}
				} catch (CoreException $e) {
					// The autoloader will throw a CoreException
					// that the required class could not be found
					// but we want a nicer error-message for this here
					throw new ApiException(404, 'The required module "'.$module."' could not be found");
				}
			}
		}
	}

	/**
	 * this function is used in modules to check
	 * for specfic Froxlor-version requirements.
	 * You can specify either min or max or both.
	 *
	 * @param string $min_version at least this version
	 * @param string $max_version at most this version
	 *
	 * @throws ApiException
	 */
	public static function requireVersion($min_version = null, $max_version = null) {

		// just check if we have the required minimum
		if ($min_version != null) {
			$cmp = self::_version_compare2(Froxlor::getApiVersion(), $min_verion);
			if ($cmp == -1) {
				throw new ApiException(406, 'Module requires at least version "'.$min_version.'"');
			}
		}
		// just check if we meet the required maximum
		if ($max_version != null) {
			$cmp = self::_version_compare2(Froxlor::getApiVersion(), $max_verion);
			if ($cmp == 1) {
				throw new ApiException(406, 'Module requires an older version of the API ('.$max_version.')');
			}
		}
	}

	/**
	 * Version compare for update-check and more
	 *
	 * @param string $a
	 * @param string $b
	 * @return integer 0 if equal, 1 if a>b and -1 if b>a
	 */
	public static function cmpFroxlorVersions($a = null, $b = null) {
		return self::_version_compare2($a, $b);
	}

	/**
	 * compare of froxlor versions
	 *
	 * @param string $a
	 * @param string $b
	 *
	 * @return integer 0 if equal, 1 if a>b and -1 if b>a
	 */
	private static function _version_compare2($a, $b) {

		// split version into pieces and remove trailing .0
		$a = explode(".", rtrim($a, ".0"));
		$b = explode(".", rtrim($b, ".0"));

		self::_parseVersionArray($a);
		self::_parseVersionArray($b);

		while (count($a) != count($b)) {
			if (count($a) < count($b)) {
				$a[] = '0';
			}
			elseif (count($b) < count($a)) {
				$b[] = '0';
			}
		}

		foreach ($a as $depth => $aVal) {
			// iterate over each piece of A
			if (isset($b[$depth])) {
				// if B matches A to this depth, compare the values
				if ($aVal > $b[$depth]) {
					return 1; // A > B
				}
				else if ($aVal < $b[$depth]) {
					return -1; // B > A
				}
				// an equal result is inconclusive at this point
			} else {
				// if B does not match A to this depth, then A comes after B in sort order
				return 1; // so A > B
			}
		}
		// at this point, we know that to the depth that A and B extend to, they are equivalent.
		// either the loop ended because A is shorter than B, or both are equal.
		return (count($a) < count($b)) ? -1 : 0;
	}

	/**
	 * helperfunction to parse version-numbers and respect
	 * -dev, -rc strings in it
	 *
	 * @param string $arr
	 */
	private static function _parseVersionArray(&$arr = null) {
		// -svn or -dev or -rc ?
		if (stripos($arr[count($arr)-1], '-') !== false) {
			$x = explode("-", $arr[count($arr)-1]);
			$arr[count($arr)-1] = $x[0];
			if (stripos($x[1], 'rc') !== false) {
				$arr[] = '-1';
				$arr[] = '2'; // rc > dev > svn
				// number of rc
				$arr[] = substr($x[1], 2);
			}
			else if (stripos($x[1], 'dev') !== false) {
				$arr[] = '-1';
				$arr[] = '1'; // svn < dev < rc
				// number of dev
				$arr[] = substr($x[1], 3);
			}
			// -svn version are deprecated
			else if (stripos($x[1], 'svn') !== false) {
				$arr[] = '-1';
				$arr[] = '0'; // svn < dev < rc
				// number of svn
				$arr[] = substr($x[1], 3);
			}
		}
	}

}
