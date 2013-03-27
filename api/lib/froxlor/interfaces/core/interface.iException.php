<?php

/**
 * Exception interface
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

	/* Protected methods inherited from Exception class */
	// Exception message
	public function getMessage();
	// User-defined Exception code
	public function getCode();
	// Source filename
	public function getFile();
	// Source line
	public function getLine();
	// An array of the backtrace()
	public function getTrace();
	// Formated string of trace
	public function getTraceAsString();

	/* Overrideable methods inherited from Exception class */
	/**
	 * format output string in api-protocol style
	 *
	 * @return string serialized array
	*/
	public function __toString();

	/**
	 * default constructor
	 *
	 * @param int    $code    custom error code
	 * @param string $message error-message
	 *
	 * @throws ApiException
	*/
	public function __construct($message = null, $code = 0);
}
