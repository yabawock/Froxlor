<?php

/**
 * SyslogLogger class
 *
 * This class is used by the Logger object to log
 * specific messages to the system log.
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
 * Class SyslogLogger
 *
 * This class is used by the Logger object to log
 * specific messages to the system log.
 *
 * @copyright  (c) the authors
 * @author     Froxlor team <team@froxlor.org> (2010-)
 * @license    GPLv2 http://files.froxlor.org/misc/COPYING.txt
 * @category   core
 * @package    API
 * @since      0.99.0
 */
class SyslogLogger implements iAbstractLogger {

	/**
	 * syslog-logger object array
	 * for singleton design pattern
	 * implementation
	 *
	 * @var array
	 */
	private static $_sl = null;

	/**
	 * loglevel indicator for syslog
	 * 
	 * @var int
	 */
	private $_loglevel = LOG_INFO;

	/**
	 * main constructor of SyslogLogger class.
	 * Opens the systemlog and throws a LoggerException
	 * if failed.
	 *
	 * @return null
	 * @throws LoggerException
	 */
	public function __construct() {

		$opened = openlog("froxlor", LOG_ODELAY, LOG_USER | LOG_LOCAL0);

		if (!$opened) {
			throw new LoggerException(503, "Unable to access system log");
		}
	}

	/**
	 * close the file-handle if object
	 * is being destructed
	 *
	 * @return null
	 */
	public function __destruct() {
		closelog();
	}

	/**
	 * @see iAbstractLogger::getInstance()
	 *
	 * @return SyslogLogger object of SyslogLogger class
	 */
	public static function getInstance() {

		// check whether we have a FileLogger object
		if (is_null(self::$_sl)) {
			// if not, create one
			self::$_sl = new SyslogLogger();
		}
		// return existing object
		return self::$_sl;
	}

	/**
	 * @see iAbstractLogger::log()
	 *
	 * @param string $text log-message
	 *
	 * @return null
	 * @throws LoggerException
	 */
	public function log($text = '') {
		syslog($this->_loglevel, $text);
	}

	/**
	 * set the loglevel we get from the 
	 * main Logger object
	 * 
	 * @param int $ll
	 */
	public function setLogLevel($ll = LOG_INFO) {
		$this->_loglevel = (int)$ll;
	}
}
