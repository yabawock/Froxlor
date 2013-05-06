<?php

/**
 * iAbstractLogger interface
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
 * Interface iAbstractLogger
 *
 * @copyright  (c) the authors
 * @author     Froxlor team <team@froxlor.org> (2013-)
 * @license    GPLv2 http://files.froxlor.org/misc/COPYING.txt
 * @category   core
 * @package    API
 * @since      0.99.0
 */
interface iAbstractLogger {

	/**
	 * return a Logger object and automatically
	 * set the current date.
	 * Implementation of singleton design pattern
	 *
	 * @return Logger object of a specific Logger class
	 */
	public static function getInstance();

	/**
	 * write the given message to the facility given
	 *
	 * @param string $text log-message
	 *
	 * @return null
	*/
	public function log($text = '');

}
