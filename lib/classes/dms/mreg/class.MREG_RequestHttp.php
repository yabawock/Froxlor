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
 * RRPProxy metaregistry request via HTTP requests
 *
 * @author Florian Aders <faders@key-systems.net>
 * @author Tobias Zimmer <tzimmer@key-systems.net>
 * @version 1.0
 * @package rrpproxy
 * @subpackage communication
 * @copyright Copyright (c) 2011, Key-Systems GmbH
 * @license http://opensource.org/licenses/lgpl-2.1 LGPLv2.1
 */
class MREG_RequestHttp extends MREG_Request
{
	/**
	 * @access private
	 * @var string Template for the HTTP - API
	 */
	private $httpSocket;

	/**
	 * Constructor for the class
	 *
	 * Example:
	 * <code>
	 * $config = array("username" => "<user>", "password" => "<pass>", "opmode" => "ote", "socket" => "http://api-ote.rrpproxy.net/call?")
	 * $request = new MREG_RequestHttp($config);
	 * $response = $request->send(array("command" => "StatusAccount"));
	 * </code>
	 * @param array $userConfig Configuration - array (opmode, username, password, exceptions, socket)
	 * @param string $httpSocket The HTTP - socket to connect to
	 * @return object MREG_RequestHttp Instance of the RRPProxy - metaregistry-request via HTTP
	 */
	function __construct($userConfig = array(), $httpSocket = '')
	{
		if ($httpSocket != '')
		{
			$this->httpSocket = $httpSocket;
		}
		if (array_key_exists('socket', $userConfig))
		{
			$this->httpSocket = $userConfig['socket'];
		}
		// Since we rely on cURL, bail out if the cURL - extension is missing
		if(!function_exists("curl_init"))
		{
			throw new MREG_RequestException("curl missing");
		}
		$this->paramDivider = "&";
		parent::__construct($userConfig);
	}


	/**
	 * Change a configuration-parameter
	 *
	 * Example:
	 * <code>
	 * $request->config("opmode", "ote");
	 * $request->config("username", "demoote");
	 * $request->config("password", "demo");
	 * </code>
	 * @param string $name Name of the configuration-option
	 * @param string $value Content of the configuration-option
	 * @return bool Shows if the assignment was correct
	 */
	public function config($name, $value)
	{
		if (strtolower($name) == 'socket')
		{
			$this->httpSocket = $value;
			return true;
		}
		else
		{
			parent::config($name, $value);
		}
	}

	/**
	 * Creates the string for the connection - socket
	 *
	 * Since we use HTTP, loginname, password, parameters etc are transmitted by GET
	 */
	protected function createSocket()
	{
		$this->paramDivider = "&";
		$this->socket  = $this->httpSocket;
	}

	/**
	 * Sends the request via cURL to the RRPProxy - system
	 *
	 * All parameters will the added to the "GET" - request and the answer is
	 * in the body we receive back
	 * @return string Answer of the RRPProxy - API in native format
	 */
	protected function sendReal()
	{
		$socket = $this->socket;
		$post = 's_opmode=' . rawurlencode($this->config['opmode']);
		$post .= '&s_login=' . rawurlencode($this->config['username']);
		$post .= '&s_pw=' . rawurlencode($this->config['password']);
		foreach($this->params as $key => $value)
		{
			// Be sure to not send a sessionid ;)
			if (strtolower($key) == "phpsessid") { continue; }
			$post .= '&' . rawurlencode($key) . '=' . rawurlencode($value);
		}

		$ch = curl_init($socket);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
		$response = curl_exec($ch);
		curl_close($ch);
		return $response;
	}
}

/**
 * RRPProxy metaregistry exceptions (HTTP - extension)
 *
 * @author Florian Aders <faders@key-systems.net>
 * @author Tobias Zimmer <tzimmer@key-systems.net>
 * @version 1.0
 * @package rrpproxy
 * @subpackage communication
 * @copyright Copyright (c) 2011, Key-Systems GmbH
 * @license http://opensource.org/licenses/lgpl-2.1 LGPLv2.1
 */
class MREG_RequestHttpException extends MREG_RequestException {}
