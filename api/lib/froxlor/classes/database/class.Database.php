<?php

/**
 * Database class
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
 * Class Database
 *
 * @copyright  (c) the authors
 * @author     Froxlor team <team@froxlor.org> (2013-)
 * @license    GPLv2 http://files.froxlor.org/misc/COPYING.txt
 * @category   core
 * @package    API
 * @since      0.99.0
 */
class Database {

	/**
	 * database connection link
	 */
	private static $_link = null ;

	/**
	 * function to create a new database
	 * connection if not connected already
	 *
	 * @return PDO
	 */
	private static function init() {

		if (self::$_link) {
			return self::$_link;
		}

		require FROXLOR_API_DIR . "/conf/db.inc.php";
		require_once dirname(__FILE__)."/rb.php";

		// MySQL: 'mysql:host=localhost;dbname=mydatabase', 'user','password'
		// SQLite: 'sqlite:/tmp/dbfile.txt', 'user','password'
		$driver = $dbconf["db_driver"];
		$user = $dbconf["db_user"];
		$password = $dbconf["db_password"];

		// Setup RedBeansPHP
		R::setup($driver, $user, $password);
		//R::freeze(true);
		R::debug(false);
		R::$writer->setUseCache(true);

		unset($dbconf);

		return 'R';
	}

	/**
	 * magic function which takes all static calls,
	 * creates a database object if needed and runs
	 * the given method.
	 *
	 * @param string $name
	 * @param mixed $args
	 *
	 * @return mixed
	 */
	public static function __callStatic($name, $args) {
		$callback = array(self::init(), $name);
		return call_user_func_array($callback, $args );
	}
}
