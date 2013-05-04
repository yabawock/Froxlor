<?php

/**
 * Froxlor API error handler
 *
 * This file catches php-errors and php-exceptions not
 * caught or handled by the api and returns a nice api-response
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
 * Error handler
 *
 * Handle errors the API way
 *
 * @param integer
 * @param string
 * @param string
 * @param integer
 */
function __froxlor_error($type, $message, $file, $line) {

	$arr_errors = array (
			E_ERROR             => 'Fatal error',
			E_WARNING           => 'Warning',
			E_PARSE             => 'Parsing error',
			E_NOTICE            => 'Notice',
			E_CORE_ERROR        => 'Core error',
			E_CORE_WARNING      => 'Core warning',
			E_COMPILE_ERROR     => 'Compile error',
			E_COMPILE_WARNING   => 'Compile warning',
			E_USER_ERROR        => 'Fatal error',
			E_USER_WARNING      => 'Warning',
			E_USER_NOTICE       => 'Notice',
			E_STRICT            => 'Runtime notice',
			4096                => 'Recoverable error',
			8192                => 'Deprecated notice'
	);

	$_message = $arr_errors[$type].': '.$message.'; in '.$file.':'.$line;
	throw new ApiException(500, $_message);
}

/**
 * Exception handler
 *
 * @param Exception
 */
function __froxlor_exception($e) {
	// this might not always be working out good
	//throw new ApiException(500, $e->getMessage());
	var_dump($e);
}
