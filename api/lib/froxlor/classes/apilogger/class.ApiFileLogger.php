<?php

/**
 * ApiFileLogger class
 *
 * This class is used by the Logger object to log
 * specific messages to a log file. This logfile
 * will be created if it does not exist.
 *
 * Logs will be stored in the given directory with the given filename
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
 * Class ApiFileLogger
 *
 * This class is used by the Logger object to log
 * specific messages to a log file. This logfile
 * will be created if it does not exist.
 *
 * Logs will be stored in the given directory with the given filename
 *
 * @copyright  (c) the authors
 * @author     Froxlor team <team@froxlor.org> (2010-)
 * @license    GPLv2 http://files.froxlor.org/misc/COPYING.txt
 * @category   core
 * @package    API
 * @since      0.99.0
 */
class ApiFileLogger implements iAbstractLogger {

	/**
	 * file-logger object array
	 * for singleton design pattern
	 * implementation
	 *
	 * @var array
	 */
	private static $_fl = null;

	/**
	 * internal file-handle of log-file
	 *
	 * @var int
	 */
	private $_fh = null;

	/**
	 * main constructor of FileLogger class.
	 * Opens the logfile and throws a LoggerException
	 * if failed.
	 *
	 * @param object $lc SimpleXML object with the log-conf content
	 *
	 * @return null
	 * @throws LoggerException
	 */
	public function __construct($lc = null) {

		// set defaults
		$logfile = dirname(FROXLOR_API_DIR).'/logs/froxlor.log';

		// check for config overwrites
		if (isset($lc->facilities->file->filename)
				&& (string)$lc->facilities->file->filename != ''
		) {
			$logfile = (string)$lc->facilities->file->filename;
			if (substr($logfile, 0, 1) != '/') {
				$logfile = dirname(FROXLOR_API_DIR).'/'.$logfile;
			}
		}
		$this->_fh = @fopen($logfile, 'a');
		if (!$this->_fh) {
			throw new LoggerException(503, "Unable to open log-file '".$logfile."'");
		}
	}

	/**
	 * close the file-handle if object
	 * is being destructed
	 *
	 * @return null
	 */
	public function __destruct() {
		if ($this->_fh) {
			@fclose($this->_fh);
		}
	}

	/**
	 * @see iAbstractLogger::getInstance()
	 *
	 * @param object $lc SimpleXML object with the log-conf content
	 *
	 * @return FileLogger object of FileLogger class
	 */
	public static function getInstance($lc = null) {

		// check whether we have a FileLogger object
		if (is_null(self::$_fl)) {
			// if not, create one
			self::$_fl = new ApiFileLogger($lc);
		}
		// return existing object
		return self::$_fl;
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
		if ($this->_fh) {
			fwrite($this->_fh, date('d.m.Y H:i:s', time()).' '.$text);
			return;
		}
		throw new LoggerException(503, "Cannot write to logfile due to previous errors");
	}
}
