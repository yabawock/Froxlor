<?php
/**
 * RRPProxy metaregistry connection tools
 * This package gives users the possibility for an easy access to the RRPProxy
 * systems.
 *
 * @author Florian Aders <faders@key-systems.net>
 * @author Tobias Zimmer <tzimmer@key-systems.net>
 * @version 1.0
 * @package rrpproxy
 * @subpackage communication
 * @copyright Copyright (c) 2011, Key-Systems GmbH
 * @license http://opensource.org/licenses/lgpl-2.1 LGPLv2.1
 */

/**
 * Include the needed response - class
 */
include_once "MREG_Response.class.php";

/**
 * RRPProxy metaregistry request
 *
 * @author Florian Aders <faders@key-systems.net>
 * @author Tobias Zimmer <tzimmer@key-systems.net>
 * @version 1.0
 * @package rrpproxy
 * @subpackage communication
 * @copyright Copyright (c) 2011, Key-Systems GmbH
 * @license http://opensource.org/licenses/lgpl-2.1 LGPLv2.1
 */
abstract class MREG_Request
{
	/**
	 * After the request is send, this contains the parsed response
	 * @access public
	 * @var object MREG_Response Object with the parsed response
	 */
	public $response;

	/**
	 * @access private
	 * @var array Collect the parameters to be send to RRPProxy
	 */
	protected $params = array();

	/**
	 * This array holds our configuration.
	 *
	 * Currently supported by the base - class:
	 * <ul>
	 * <li>opmode => LIVE, OTE</li>
	 * <li>username => string</li>
	 * <li>password => string</li>
	 * <li>exceptions => bool</li>
	 * </ul>
	 * @access private
	 * @var array Configuration for the request (opmode, username, password, exceptions)
	 */
	protected $config = array (
		"opmode" => "live",
		"username" => "",
		"password" => "",
		"exceptions" => true
	);

	/**
	 * @access protected
	 * @var string Socket to use for connecting to the RRPProxy - API
	 */
	protected $socket;

	/**
	 * @access protected
	 * @var string Divider between the different request-parameters
	 */
	protected $paramDivider = "";

	abstract protected function createSocket();

	/**
	 * Constructor for the class
	 *
	 * Example:
	 * <code>
	 * $request = new MREG_Request(array("opmode" => "ote"));
	 * </code>
	 * Note: You _MUST_ use one of the "real" classes like {@link MREG_RequestHttp}
	 * @param array $userConfig Configuration - array (opmode, username, password, exceptions)
	 * @return object MREG_Request Instance of the RRPProxy - metaregistry-request
	 */
	function __construct($userConfig = array())
	{
		if (isset($userConfig) && is_array($userConfig))
		{
			foreach ($userConfig as $key => $value)
			{
				$this->config($key, $value);
			}
		}
		$this->createSocket();
	}

	/**
	 * Magic-function for setting a request - parameter
	 *
	 * Warning: This method overrides existing parameters
	 *
	 * Example:
	 * <code>
	 * $request->command = "CheckDomain";
	 * $request->domain = "example.org";
	 * </code>
	 * @param string $name parameter to be send
	 * @param string $value Content of the parameter to be send
	 */
	public function __set ($name,$value)
	{
		// Assign the pair to our request - array
		return $this->assign($name, $value);
	}

	/**
	 * Add one or more parameter to our request
	 *
	 * If first parameter is a string, we'll use the second parameter to add a
	 * single parameter to our request, if it's an array, we'll ignore the second
	 * parameter and add all key/value-pairs of the array to our request
	 *
	 * Example:
	 * <code>
	 * $request->assign("command", "CheckDomains");
	 * $request->assign(array("domain0" => "example.org", "domain1" => "example.com"));
	 * </code>
	 * @param string|array $name Entity to add to the request
	 * @param string $value If first parameter is an string, this holds the value
	 * @return bool Always true
	 */
	public function assign($name, $value = "")
	{
		if(!is_array($this->params))
		{
			$this->params = array();
		}

		// We got a single parameter
		if(is_string($name))
		{
			$name  = str_replace(array("\r", "\n"), "", $name);
			$value = str_replace(array("\r", "\n"), "", $value);
			$this->params[$name] = $value;
			return true;
		}

		// We got an array, we'll merge it to our request
		if(is_array($name) && count($name) > 0)
		{
			foreach($name as $key => $value)
			{
				// Reuse this method for single values
				$this->assign($key, $value);
			}
		}
		return true;
	}

	/**
	 * Change a configuration-parameter
	 *
	 * This class may throw an exception if an unsupported opmode is given
	 *
	 * Example:
	 * <code>
	 * $request->config("opmode", "ote");
	 * </code>
	 * @param string $name Name of the configuration-option
	 * @param string $value Content of the configuration-option
	 * @return bool Shows if the assignment was correct
	 */
	public function config($name, $value)
	{
		if(!is_array($this->params))
		{
			$this->params = array();
		}

		if(!is_string($name) || !is_string($value))
		{
			return false;
		}
		$key = strtolower($name);
		switch ($key)
		{
			case "opmode":
				if(!in_array(strtolower($value), array("live", "ote", "dev")))
				{
					throw new MREG_RequestException("opmode");
				}
				$this->config['opmode'] = strtolower($value);
				break;
			case "username":
				$this->config['username'] = $value;
				break;
			case "password":
				$this->config['password'] = $value;
				break;
			case "exceptions":
				if($value == "false" || $value === false || $value == 0)
				{
					$this->config['exceptions'] = false;
				}
				else
				{
					$this->config['exceptions'] = true;
				}
				break;
			default:
				return false;
		}
		return true;
	}

	/**
	 * Send the request to RRPProxy and get the response
	 *
	 * This method may throw 2 exceptions if exceptions are enabled:
	 * <ul>
	 * <li>MREG_TemporaryErrorException</li>
	 * <li>MREG_PermanentErrorException</li>
	 * </ul>
	 * If parameters are given directly, they will be merged with the existing
	 * request - parameters before the request is actually fired.
	 * @param array $params Array holding the request (optional)
	 * @return object MREG_Response Instance with the parsed response
	 */
	public function send($params = array(),$numberOfTries=0)
	{
		/* If we get an array, we'll merge it to our request,
		this enables the user to send an request and get the response
		with one request */
		if(is_array($params) && count($params) > 0)
		{
			$this->assign($params);
		}

		// Create the correct socket
		$this->createSocket();

		// The parent-class decides which way to send
		$response = $this->sendReal();
		
	        $this->response = new MREG_Response($response);

		if($this->config['exceptions'] === true)
		{
			if(substr($this->response->code, 0, 1) == "4")
			{
				if ($numberOfTries < 4)
				{
					$this->send($params,$numberOfTries+1);
				}
				else
				{
					throw new MREG_TemporaryErrorException($this->response->description."|".$this->params['command'], $this->response->code);
				}
			}
			if(substr($this->response->code, 0, 1) != "4" && substr($this->response->code, 0, 1) != "2")
			{
				$exception = new MREG_PermanentErrorException($this->response->description."|".$this->params['command'], $this->response->code);
				if (is_array($this->response->property))
				{
					$exception->setDetailedErrorMessage($this->response->property);
				}
				throw $exception;
			}
		}
		// For convenience we'll return the parsed response
		return $this->response;
	}

	/**
	 * Build the request to be send
	 *
	 * This method takes all parameters in the {@link $params} array and
	 * connects them toegether with the appropriate format for the chosen
	 * connection - method.
	 * @return string Complete request to be send to the RRPProxy - system
	 */
	protected function createRequest()
	{
		$command = "";
		foreach ($this->params as $key => $value)
		{
			if (strtolower($key) == "phpsessid") { continue; }
			$command .= $this->addParameter($key, $value);
		}
		return $command;
	}

	/**
	 * Build a pair of key/value corresponding to the used transmission-method.
	 * An divider will be added (normally "&" for HTTP-requests or "\n" for mregd)
	 * @param string $name Parametername to be added
	 * @param string $value Value to be added
	 * @return string Concatenated key/value-pair with divider
	 */
	protected function addParameter($name, $value)
	{
		return urlencode($name) . "=" . urlencode($value) . $this->paramDivider;
	}

	/**
	 * Reset the object for a new request
	 *
	 * Example:
	 * <code>
	 * $response = $request->send(array("command" => "StatusAccount"));
	 * $request->clear();
	 * $response2 = $request->send(array("command" => "CheckDomain", "domain" => "example.org"));
	 * </code>
	 */
	public function reset()
	{
		$this->response = "";
		$this->socket = "";
		$this->params = array();
	}


	/**
	 * Transform a multilevel object/array into RRPProxy - native - format
	 *
	 * @param array|object $response StdClass Container holding a part of the mixture
	 * @param string $begin The current line should be prefixed with this
	 * @return string The input converted into RRPProxy native format
	 */
	protected function transformToNative($response, $begin = "")
	{
		$result = "";
		$line = "";
		foreach($response as $key => $value)
		{
			if($begin != "")
			{
				// We already have something in this line, assume we are an array
				$line = $begin . "[" . strtolower($key) . "]";
			}
			else
			{
				// We start a new line and therefore don't need []
				$line = $begin . strtolower($key);
			}
			if(is_array($value) || $value instanceof StdClass)
			{
				// Arrays and objects need to go through the same procedure again
				$line = MREG_Request::transformToNative($value, $line);
			}
			else
			{
				// We got a plain value, append it to the line
				$line .= "=" . $value;
			}
			$result .= $line . "\n";
		}
		return $result;
	}
}

/**
 * RRPProxy metaregistry exceptions
 *
 * @author Florian Aders <faders@key-systems.net>
 * @author Tobias Zimmer <tzimmer@key-systems.net>
 * @version 1.0
 * @package rrpproxy
 * @subpackage communication
 * @license http://opensource.org/licenses/lgpl-2.1 LGPLv2.1
 */
class MREG_RequestException extends Exception {}
?>
