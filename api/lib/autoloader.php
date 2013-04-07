<?php

/**
 * Froxlor API class autoloader
 *
 * This file can be included to PHP Projects to use the Froxlor-API.
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

define('FROXLOR_API_DIR', dirname(dirname(__FILE__)));

Autoloader::init();

/**
 * Class Autoloader
 *
 * iterates through given directory and includes
 * the file which matches $classname
 *
 * @copyright  (c) the authors
 * @author     Froxlor team <team@froxlor.org> (2010-)
 * @license    GPLv2 http://files.froxlor.org/misc/COPYING.txt
 * @category   core
 * @package    API
 * @since      0.99.0
*/
class Autoloader {

	/**
	 * returns a new AutoLoader-object
	 * @return Autoloader
	 */
	public static function init() {
		return new self();
	}

	/**
	 * class constructor
	 *
	 * @return null
	 */
	public function __construct() {
		// register autoload.function
		spl_autoload_register(array($this, 'doAutoload'));
	}

	/**
	 * gets the class to load as parameter, searches the library-paths
	 * recursively for this class and includes it
	 *
	 * @param string $class
	 *
	 * @throws CoreException
	 * @return boolean
	 */
	public function doAutoload($class) {

		// Database-related (redbean created) classes
		// are handled by RedBean -> include it and go
		if (substr($class, 0, 6) == 'Model_') {
			include_once FROXLOR_API_DIR.'/lib/froxlor/classes/database/rb.php';
			return;
		}

		// define the paths where to look for classes
		$paths = array(
				dirname(__FILE__) . '/froxlor/',
				//dirname(__FILE__) . '/external/',
				FROXLOR_API_DIR . '/modules/'
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
					if (preg_match("/^(class|module|interface|)\.?$class\.php$/i", $it->getFilename())) {
						// include the file and return from the loop
						include_once $fullFileName;
						return true;
					}
				}
			} else {
				// yikes - no valid directory to check
				throw new CoreException(-1, "Cannot autoload from directory '".$path."'. No such directory.");
			}
		}
		// yikes - class not found
		throw new CoreException(-1, "Could not find class '".$class."'");
	}
}
