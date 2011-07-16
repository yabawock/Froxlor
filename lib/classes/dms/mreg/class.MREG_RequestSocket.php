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
 * RRPProxy metaregistry request via socket
 *
 * @author Florian Aders <faders@key-systems.net>
 * @author Tobias Zimmer <tzimmer@key-systems.net>
 * @version 1.0
 * @package rrpproxy
 * @subpackage communication
 * @copyright Copyright (c) 2011, Key-Systems GmbH
 * @license http://opensource.org/licenses/lgpl-2.1 LGPLv2.1
 */
class MREG_RequestSocket extends MREG_Request
{
	/**
	 * @access private
	 * @var string Template for the HTTP - API
	 */
	protected $socket;

	/**
	 * Constructor for the class
	 *
	 * Example:
	 * <code>
	 * $config = array("mregSocket" => "mregd://command:oir4wy2g@/tmp/mregd.service")
	 * $request = new MREG_RequestSocket($config);
	 * $response = $request->send(array("command" => "StatusAccount"));
	 * </code>
	 * @param array $userConfig Configuration - array (opmode, username, password, exceptions)
	 * @return object MREG_RequestSocket Instance of the RRPProxy - metaregistry-request via Socket
	 */
	function __construct($userConfig = array(),$socket)
	{
		$this->paramDivider = "\n";
		$this->config['socket'] = $this->socket = $socket;
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
		parent::config($name, $value);
	}

	/**
	 * Creates the string for the connection - socket
	 *
	 * We only need to configure the {@link paramDivider} here
	 */
	protected function createSocket()
	{
		if(!function_exists("fsockopen"))
		{
			throw new MREG_RequestException("fsockopen missing");
		}
		$this->paramDivider = "\n";
	}

	/**
	 * Sends the request via MREGd to the RRPProxy - system
	 *
	 * MREGd will send us a plaintext - response back
	 * @return string Answer of the RRPProxy - API in native format
	 */
	protected function sendReal()
	{
		$socket = $this->config['socket'];
		if(!$socket)
		{
			throw new MREG_RequestSocketException("No socket to connect given", 504);
		}

		$login = $this->config['username'];
		$password = $this->config['password'];
		$socket = $this->config['socket'];
		if(!$login)
		{
			throw new MREG_RequestSocketException("No login given", 504);
		}
		if(!$password)
		{
			throw new MREG_RequestSocketException("No password given", 504);
		}
		if(!$socket)
		{
			throw new MREG_RequestSocketException("No local socket given", 504);
		}


		if(preg_match("/^\//", $socket))
		{
			$SOCK = fsockopen($socket, -1, $errorno, $errorstr);
			if(!$SOCK || $errorno != 0)
			{
				throw new MREG_RequestSocketException("Unable to connect to socket", 560);
			}
		}
		elseif(preg_match("/([^\:]*)\:(\d+)$/", $socket, $matches))
		{
			$port = $matches[2];
			$host = $matches[1] or "localhost";
			$SOCK = fsockopen( $host, $port, $errorno, $errorstr );
			if(!$SOCK || $errorno != 0)
			{
				throw new MREG_RequestSocketException("Unable to connect to inet socket (Host: $host, Port: $port)", 560);
			}
		}
		else
		{
			throw new MREG_RequestSocketException("No valid socket found", 504);
		}

		// Login to the RRPProxy - system
		$incoming = "";
		while(!preg_match("/\:/", $incoming))
		{
			$incoming .= fgets($SOCK,2);
		}
		fputs($SOCK, "$login\n" );

		$in = "";
		while(!preg_match("/\:/", $in))
		{
			$in .= fgets($SOCK,2);
		}

		fputs($SOCK, "$password\n");

		$request = "[METAREGISTRY]\n"."version=1\n\n";

		if($this->config['username'])
		{
			$request .= "user=" . $this->config['username'] . "\n";
		}
		$request .= "[COMMAND]\n";
		$request .= $this->createRequest();
		$request .= "EOF\n";

		fputs( $SOCK, $request );
		$response = "";
		while(!preg_match('/(^|\n)EOF($|\n)/', $response))
		{
			if(feof($SOCK))
			{
				$response .= "\nEOF\n";
			}
			else
			{
				$response .= fgets($SOCK, 4096);
			}
		}
		fclose($SOCK);
		return $response;
	}
}

/**
 * RRPProxy metaregistry exceptions (Socket - extension)
 *
 * @author Florian Aders <faders@key-systems.net>
 * @author Tobias Zimmer <tzimmer@key-systems.net>
 * @version 1.0
 * @package rrpproxy
 * @subpackage communication
 * @copyright Copyright (c) 2011, Key-Systems GmbH
 * @license http://opensource.org/licenses/lgpl-2.1 LGPLv2.1
 */
class MREG_RequestSocketException extends MREG_RequestException {}
