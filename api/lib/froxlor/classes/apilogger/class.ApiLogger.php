<?php

/**
 * ApiLogger class
 *
 * This class provides functions to log internal messages to
 * a logfile (see FileLogger for details)
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
 * Class ApiLogger
 *
 * This class provides functions to interact with
 * the Froxlor API. Every request and response goes
 * through this class.
 *
 * @copyright  (c) the authors
 * @author     Froxlor team <team@froxlor.org> (2013-)
 * @license    GPLv2 http://files.froxlor.org/misc/COPYING.txt
 * @category   core
 * @package    API
 * @since      0.99.0
 */
class ApiLogger implements iLogger {

	/**
	 * holds the source class/function
	 * read from backtrace
	 *
	 * @var string
	 */
	private $_source = '';

	/**
	 * general log-level (default 4 = warnings)
	 *
	 * @var int
	 */
	private $_loglevel = 0;

	/**
	 * simplexml object with the
	 * config content
	 *
	 * @var SimpleXML
	 */
	private $_logconf = null;

	/**
	 * if set using setErrorException() the message, file, line, etc. for the
	 * response is taken from the exception instead of the debug_backtrace()
	 *
	 * @var Exception
	 */
	private static $_errException = null;

	/**
	 * if an exception is set via this function, this exception
	 * is being used for loggin instead of debug_backtrace()
	 *
	 * @param Exception $e
	 */
	public static function setErrorException($e = null) {
		self::$_errException = $e;
	}

	/**
	 * class constructor, read in loglevel,
	 * get source class/function and log
	 * the message if it's within the desired loglevel.
	 *
	 * @param string $text     message to log
	 * @param int    $loglevel log level
	 *
	 * @return null
	 */
	public function __construct($text, $loglevel) {

		// read log-settings
		$this->_readLogConf();

		// get calling class/funtion from backtrace
		$trace_from = 2;
		while($trace_from >= 0) {
			$this->_source = $this->_getSourceFromBacktrace($trace_from);
			if (self::$_errException !== null) {
				break;
			}
			if (substr(trim($this->_source), 0, 1) != ':') {
				break;
			}
			$trace_from--;
		}

		// only log if it's within the desired loglevel
		if ($loglevel >= $this->_loglevel && $this->_loglevel > 0) {
			$this->_write($text, $loglevel);
		}
	}

	/**
	 * read log-configuration from the /conf/log-conf.xml file
	 * if not found and creation of default fails it will be
	 * disabled.
	 *
	 * @return null
	 */
	private function _readLogConf() {

		$settingsfile = FROXLOR_API_DIR.'/conf/log-conf.xml';
		$logcf = true;
		if (!file_exists($settingsfile)) {
			// create default
			$logcf = $this->_createDefaultLogConf($settingsfile);
		}
		if ($logcf) {
			$xml = @file_get_contents($settingsfile);
			$this->_logconf = simplexml_load_string($xml);
			if ((string)$this->_logconf->enabled == 'true') {
				$this->_loglevel = (int)$this->_logconf->level;
				return;
			}
		}
		$this->_loglevel = 0;
	}

	/**
	 * generate a default log-conf.xml file if it
	 * could not be found
	 *
	 * @param string $sf settings-filename
	 *
	 * @return boolean
	 */
	private function _createDefaultLogConf($sf = null) {
		$fh = @fopen($sf, 'w');
		if ($fh) {
			$defaults = '<?xml version="1.0" encoding="UTF-8"?>
					<froxlorlog>
					<enabled>true</enabled>
					<level>4</level>
					<facilities>
					<syslog>
					<enabled>false</enabled>
					</syslog>
					<file>
					<enabled>true</enabled>
					<filename>logs/froxlor.log</filename>
					</file>
					</facilities>
					</froxlorlog>'."\n";
			fwrite($fh, $defaults);
			fclose($fh);
			@chmod($sf, 0655);
			return true;
		}
		return false;
	}

	/**
	 * this function creates a new *Logger
	 * instance for each log-facility and calls
	 * its log-function with the specific message and loglevel
	 *
	 * @param string $text     message to log
	 * @param int    $loglevel log level
	 *
	 * @return null
	 */
	private function _write($text, $loglevel) {
		// i can do better than this, we should
		// iterate through all facilities so we
		// don't have the same log-line more than once
		if (isset($this->_logconf->facilities->file)
				&& (string)$this->_logconf->facilities->file->enabled == 'true'
		) {
			$fl = ApiFileLogger::getInstance($this->_logconf);
			$fl->log('['.$this->_source.'] ['.$this->_loglevelToText($loglevel).'] '.$text."\n");
		}
		if (isset($this->_logconf->facilities->syslog)
				&& (string)$this->_logconf->facilities->syslog->enabled == 'true'
		) {
			$sl = SyslogLogger::getInstance();
			$sl->setLogLevel($loglevel);
			$sl->log('['.$this->_source.'] ['.$this->_loglevelToText($loglevel).'] '.$text."\n");
		}
	}

	/**
	 * return a speaking name for the given loglevel
	 *
	 * @param int $loglevel log level
	 *
	 * @return string
	 */
	private function _loglevelToText($loglevel) {
		$level = array(
				0 => 'off',
				1 => 'debug',
				2 => 'info',
				4 => 'warning',
				8 => 'error',
				16 => 'fatal'
		);
		return $level[$loglevel];
	}

	/**
	 * get the calling class/function from the debug_backtrace()
	 * line-numbers come from one level above, don't ask me why (d00p)
	 *
	 * @param int $level debug-trace level (0 = latest)
	 *
	 * @return string
	 */
	private function _getSourceFromBacktrace($level = 1) {
		if (self::$_errException !== null) {
			$file = self::$_errException->getFile();
			$l = self::$_errException->getLine();
		} else {
			$t = debug_backtrace();
			$file = $t[$level]['file'];
			$l = (isset($t[$level]['line']) ? $t[$level]['line'] : 0);
		}
		return basename($file).':'.$l;
	}

	/**
	 * (non-PHPdoc)
	 * @see iLogger::debug()
	 *
	 * @param string $text debug message
	 *
	 * @return null
	 */
	public static function debug($text) {
		$logger = new ApiLogger($text, ApiLogger::DEBUG);
		unset($logger);
	}

	/**
	 * (non-PHPdoc)
	 * @see iLogger::info()
	 *
	 * @param string $text info message
	 *
	 * @return null
	 */
	public static function info($text) {
		$logger = new ApiLogger($text, ApiLogger::INFO);
		unset($logger);
	}

	/**
	 * (non-PHPdoc)
	 * @see iLogger::warn()
	 *
	 * @param string $text warning message
	 *
	 * @return null
	 */
	public static function warn($text) {
		$logger = new ApiLogger($text, ApiLogger::WARN);
		unset($logger);
	}

	/**
	 * (non-PHPdoc)
	 * @see iLogger::error()
	 *
	 * @param string $text error message
	 *
	 * @return null
	 */
	public static function error($text) {
		$logger = new ApiLogger($text, ApiLogger::ERROR);
		unset($logger);
	}

	/**
	 * (non-PHPdoc)
	 * @see iLogger::fatal()
	 *
	 * @param string $text fatal message
	 *
	 * @return null
	 */
	public static function fatal($text) {
		$logger = new ApiLogger($text, ApiLogger::FATAL);
		unset($logger);
	}

}
