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
 * Include the basic class we extend
 */
include_once "MREG_Request.class.php";

/**
 * RRPProxy metaregistry request via local MREGd
 *
 * @author Florian Aders <faders@key-systems.net>
 * @author Tobias Zimmer <tzimmer@key-systems.net>
 * @version 1.0
 * @package rrpproxy
 * @subpackage communication
 * @copyright Copyright (c) 2011, Key-Systems GmbH
 * @license http://opensource.org/licenses/lgpl-2.1 LGPLv2.1
 */
class MREG_RequestMregd extends MREG_Request
{
	/**
	 * @access private
	 * @var string Template for the HTTP - API
	 */
	private $mregdSocket = "/tmp/mregd.service";

	/**
	 * Constructor for the class
	 *
	 * Example:
	 * <code>
	 * $config = array("mregSocket" => "mregd://command:oir4wy2g@/tmp/mregd.service")
	 * $request = new MREG_RequestMregd($config);
	 * $response = $request->send(array("command" => "StatusAccount"));
	 * </code>
	 * @param array $userConfig Configuration - array (opmode, username, password, exceptions)
	 * @return object MREG_RequestHttp Instance of the RRPProxy - metaregistry-request via Mregd
	 */
	function __construct($userConfig = array())
	{
		$this->paramDivider = "\n";
		$this->config['mregSocket'] = $this->mregdSocket;
		parent::__construct($userConfig);
	}

	/**
	 * Change a configuration-parameter
	 *
	 * Example:
	 * <code>
	 * $request->config("exceptions", false);
	 * </code>
	 *
	 * Note: This implementation adds the option to set a "mregSocket". This
	 * socket should point to a local running mregd provided by RRPProxy. The
	 * format is "mregd://<username>:<password>@<unixsocket>". The username and
	 * password are the same as given in the mreg.conf in the option "socket".
	 *
	 * Due to the nature of mregd, the options "opmode" and "password" are
	 * ignored and "username" will only be used to tell your local running mregd
	 * who you are. No username or password set in this class will be send to
	 * the RRPProxy - system! Everything related to this needs to be set in mreg.conf
	 *
	 * @param string $name Name of the configuration-option
	 * @param string $value Content of the configuration-option
	 * @return bool Shows if the assignment was correct
	 */
	public function config($name, $value)
	{
		if(strtolower($name) == "mregsocket" && preg_match("/^mregd\:\/\/([a-z0-9]+)\:([a-z0-9]+)\@(.*)$/", $value))
		{
			$this->config['mregSocket'] = $value;
			return true;
		}
		elseif(strtolower($name) == "mregsocket")
		{
			throw new MREG_RequestMregdException("Invalid mreg - socket", 506);
		}
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
		$socket = $this->config['mregSocket'];
		if(!$socket)
		{
			throw new MREG_RequestMregdException("No socket to connect given", 504);
		}

		$login = $this->config['username'];
		$password = $this->config['password'];
		$socket = $this->config['mregSocket'];
		if(preg_match( "/^mregd\:\/\/([a-z0-9]+)\:([a-z0-9]+)\@(.*)$/", $socket, $socketMatches ))
		{
			$login = $socketMatches[1];
			$password = $socketMatches[2];
			$socket = $socketMatches[3];
		}
		if(!$login)
		{
			throw new MREG_RequestMregdException("No login given", 504);
		}
		if(!$password)
		{
			throw new MREG_RequestMregdException("No password given", 504);
		}
		if(!$socket)
		{
			throw new MREG_RequestMregdException("No local socket given", 504);
		}


		if(preg_match("/^\//", $socket))
		{
			$SOCK = fsockopen("unix://" . $socket, -1, $errorno, $errorstr);
			if(!$SOCK || $errorno != 0)
			{
				throw new MREG_RequestMregdException("Unable to connect to local socket", 560);
			}
		}
		elseif(preg_match("/([^\:]*)\:?(\d+)$/", $socket, $matches))
		{
			$port = $matches[2];
			$host = $matches[1] or "localhost";
			$SOCK = fsockopen( $host, $port, $errorno, $errorstr );
			if(!$SOCK || $errorno != 0)
			{
				throw new MREG_RequestMregdException("Unable to connect to inet socket (Host: $host, Port: $port)", 560);
			}
		}
		else
		{
			throw new MREG_RequestMregdException("No valid socket found", 504);
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
 * RRPProxy metaregistry exceptions (Mregd - extension)
 *
 * @author Florian Aders <faders@key-systems.net>
 * @author Tobias Zimmer <tzimmer@key-systems.net>
 * @version 1.0
 * @package rrpproxy
 * @subpackage communication
 * @copyright Copyright (c) 2011, Key-Systems GmbH
 * @license http://opensource.org/licenses/lgpl-2.1 LGPLv2.1
 */
class MREG_RequestMregdException extends MREG_RequestException {}
