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
 * Class Module
 *
 * this class provides helper functions for basic
 * maintenance stuff, like version check, deprecation, etc.
 *
 * @copyright  (c) the authors
 * @author     Froxlor team <team@froxlor.org> (2010-)
 * @license    GPLv2 http://files.froxlor.org/misc/COPYING.txt
 * @category   core
 * @package    API
 * @since      0.99.0
 */
class Module implements iModule {

	/**
	 * @see iModule::requireModules()
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
				// can we can the class?
				if (!class_exists($module)) {
					// no - we cannot
					throw new ApiException(404, 'The required module "'.$module."' could not be found");
				}
			}
		}
	}

	/**
	 * @see iModule::requireVersion()
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
	 * @see iModule::cmpFroxlorVersions()
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

		// -svn or -dev or -rc ?
		if (stripos($a[count($a)-1], '-') !== false) {
			$x = explode("-", $a[count($a)-1]);
			$a[count($a)-1] = $x[0];
			if (stripos($x[1], 'rc') !== false) {
				$a[] = '1'; // rc > dev
				// number of rc
				$a[] = substr($x[1], 2);
			}
			else if (stripos($x[1], 'dev') !== false) {
				$a[] = '0'; // dev < rc
				// number of dev
				$a[] = substr($x[1], 3);
			}
			else {
				// unknown version string
				return 0;
			}
		}
		// same with $b
		if (stripos($b[count($b)-1], '-') !== false) {
			$x = explode("-", $b[count($b)-1]);
			$b[count($b)-1] = $x[0];
			if (stripos($x[1], 'rc') !== false) {
				$b[] = '1'; // rc > dev
				// number of rc
				$b[] = substr($x[1], 2);
			}
			else if (stripos($x[1], 'dev') !== false) {
				$b[] = '0'; // dev < rc
				// number of dev
				$b[] = substr($x[1], 3);
			}
			else {
				// unknown version string
				return 0;
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

}
