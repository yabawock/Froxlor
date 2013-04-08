<?php

/**
 * iLogger interface
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
 * Interface iLogger
 *
 * @copyright  (c) the authors
 * @author     Froxlor team <team@froxlor.org> (2010-)
 * @license    GPLv2 http://files.froxlor.org/misc/COPYING.txt
 * @category   core
 * @package    API
 * @since      0.99.0
 */
interface iLogger {
	
	/**
	 * Loggingmode: off
	 * 
	 * @var int
	 */
	const OFF	= 0;
	
	/**
	 * Loggingmode: debug (contains INFO, WARN, ERROR, FATAL)
	 * @var int
	 */
	const DEBUG	= 1;
	
	/**
	 * Loggingmode: info (contains WARN, ERROR, FATAL)
	 * 
	 * @var int
	 */
	const INFO	= 2;
	
	/**
	 * Loggingmode: warn (contains ERROR, FATAL)
	 * 
	 * @var int
	 */
	const WARN	= 4;
	
	/**
	 * Loggingmode: error (contains FATAL)
	 * 
	 * @var int
	 */
	const ERROR	= 8;
	
	/**
	 * Loggingmode: fatal
	 * 
	 * @var int
	 */
	const FATAL	= 16;
	
	/**
	 * Logs a DEBUG message.
	 * 
	 * @param string $text debug message
	 */
	public static function debug($text);
	
	/**
	 * Logs a INFO message.
	 * 
	 * @param string $text info message
	 */
	public static function info($text);
	
	/**
	 * Logs a WARN message.
	 * 
	 * @param string $text warning message
	 */
	public static function warn($text);
	
	/**
	 * Logs a ERROR message.
	 * 
	 * @param string $text error meesage
	 */
	public static function error($text);
	
	/**
	 * Logs a FATAL message.
	 * 
	 * @param string $text fatal message
	 */
	public static function fatal($text);

}
