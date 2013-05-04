<?php

/**
 * Exception interface
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
 * Interface Exception
 *
 * @copyright  (c) the authors
 * @author     Froxlor team <team@froxlor.org> (2010-)
 * @license    GPLv2 http://files.froxlor.org/misc/COPYING.txt
 * @category   core
 * @package    API
 * @since      0.99.0
 */
interface iException {

	// Protected methods inherited from Exception class

	/**
	 * returns the error message
	 *
	 * @return string
	 */
	public function getMessage();

	/**
	 * returns the error code
	 *
	 * @return int
	*/
	public function getCode();

	/**
	 * returns the error source-file
	 *
	 * @return string
	*/
	public function getFile();

	/**
	 * returns the line in the file causing the error
	 *
	 * @return int
	*/
	public function getLine();

	/**
	 * returns the error-backtrace as array
	 *
	 * @return array
	*/
	public function getTrace();

	/**
	 * returns the backtrace as string
	 *
	 * @return string
	*/
	public function getTraceAsString();

	// Overrideable methods inherited from Exception class

	/**
	 * format output string in api-protocol style
	 *
	 * @return string serialized array
	*/
	public function __toString();

	/**
	 * default constructor
	 *
	 * @param string $message error-message
	 * @param int    $code    custom error code
	 *
	 * @throws ApiException
	*/
	public function __construct($message = null, $code = 0);
}
