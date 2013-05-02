<?php

class FroxlorCliInterface {

	/**
	 * static variable which holds all
	 * available commands for the froxlor-cli
	 *
	 * @var array
	 */
	private static $_commands = null;

	/**
	 * define version of Froxlor-CLI
	 *
	 * @var string
	 */
	private static $_version = '0.1';

	/**
	 * Froxlor API object
	 *
	 * @var Froxlor
	 */
	private $_api = null;

	/**
	 * List of all Functions in Froxlor
	 *
	 * @var array
	 */
	private $_functionlist = array();

	/**
	 * Cache of all Functionparams in Froxlor
	 *
	 * @var array
	*/
	private $_paramlist = array();

	/**
	 * Where the history is found
	 *
	 * @var string
	*/
	private $_historyfile = 'froxlor.history';

	/**
	 * main constructor of CLISystem class
	 *
	 * @param Froxlor $api
	 */
	public function __construct($api) {
		/**
		 * define available commands
		 */
		self::$_commands = array(
				'.help',			/* show help screen */
				'.info',			/* show infos about froxlorcli */
				'.version',			/* show version */
				'.quit'				/* exit the froxlorcli */
		);

		$this->_api = $api;

		// check php readline extension
		if (!extension_loaded('readline')) {
			die('Froxlor Cli-interface requires PHP compiled with "readline" support');
		}

		// Initialize the history
		$this->_historyfile = dirname(__FILE__) . '/froxlor.history';
		if (is_file($this->_historyfile)) {
			readline_read_history($this->_historyfile);
		}

		// Get a list with all API - functions
		try {
			$req = ApiRequest::createRequest("Core.listApiFunctions", array());
			$this->_api->sendRequest($req);
			$response = $this->_api->getLastResponse();
		} catch (ApiException $e) {
			$response = new ApiResponse(null);
			$_response = unserialize((string)$e);
			$response->addResponse($_response);
		}

		$rarr = $response->getResponse();

		// We don't need a fallback - if it doesn't work, simply nothing will be completed
		if ($response->getResponseCode() == 200) {
			// Build an array holding all functions as string
			foreach ($rarr['body'] as $function) {
				$this->_functionlist[] = $function['module'] . '.' . $function['function'];
			}
		}

		// Initialize the shell - completion
		readline_completion_function(array($this, 'readlineCompletion'));

		$this->startShell();
	}

	public function __destruct() {
		readline_write_history($this->_historyfile);
	}

	private function readlineCompletion($string, $index) {
		$matches = array();
		// Get info about the current buffer
		$rl_info = readline_info();

		// Figure out what the entire input is
		$full_input = substr($rl_info['line_buffer'], 0, $rl_info['end']);

		// Let's see if we have a space in the complete string (indicates param-completion)
		if (strpos($full_input, " ") === false) {
			// No string, just return all available functions, readline will do the matching internaly
			return $this->_functionlist;
		}

		// Get the commandname at the beginning of the line
		$commandname = trim(strtok($full_input, " "));
		if (!in_array($commandname, $this->_paramlist)) {
			// Try to fetch the needed parameters
			try {
				$req = ApiRequest::createRequest("Core.listParams", array('ident' => $commandname));
				$this->_api->sendRequest($req);
				$response = $this->_api->getLastResponse();

			} catch (ApiException $e) {
				$response = new ApiResponse(null);
				$_response = unserialize((string)$e);
				$response->addResponse($_response);
			}

			$rarr = $response->getResponse();

			// We initialize the commandname in every case to reduce multiple lookups later
			$this->_paramlist[$commandname] = array();

			// No harm done if we don't get a successful response
			if ($response->getResponseCode() == 200) {
				// Build a list with all parameters
				foreach ($rarr['body']['params'] as $param) {
					$this->_paramlist[$commandname][] = $param['parameter'];
				}
			}
			$matches = $this->_paramlist[$commandname];
		} else {
			$matches = $this->_paramlist[$commandname];
		}
		return $matches;
	}

	/**
	 * main function to parse commands
	 *
	 * @param string $input shell-command
	 */
	private function _parseInput($input) {

		$input = trim($input);

		if($input == '') {
			$this->_unknownCommand();
			return;
		}

		if (strpos($input, " ") !== false) {
			$main_command = substr($input, 0, strpos($input, " "));
		} else {
			$main_command = $input;
		}

		if (substr($main_command, 0, 1) == '.' && !in_array($main_command, self::$_commands)) {
			$this->_unknownCommand($main_command);
			return;
		}

		switch ($main_command) {
			case '.help':
				$this->showHelp();
				break;
			case '.info':
				$this->showInfo();
				break;
			case '.version':
				$this->showVersion();
				break;
			case '.quit':
				echo "Goodbye!\n";
				exit;
			default:
				$inputparam = null;
				if (strlen($input) > strlen($main_command)) {
					$inputparam = substr($input, strlen($main_command)+1);
				}
				$this->parseApiCommand($main_command, $inputparam);
				break;
		}
	}

	/**
	 * tell the user that the command
	 * entered could not be interpretated
	 *
	 * @param string $command the command which is unknown
	 */
	private function _unknownCommand($command = '') {

		echo "Unknown command";
		if($command != '') {
			echo " '".$command."'";
		}
		echo "\n";
		echo "Type 'help' for a list of commands\n\n";
	}

	/**
	 * starts the cli session
	 */
	public function startShell() {
		echo "Starting Froxlor-CLI version ".self::$_version."...\n\n";
		echo "Type '.help' for a list of commands. To see API-functions, just use tab-completion.\n\n";
		$this->showPrompt();
	}

	/**
	 * shows the prompt for input
	 */
	public function showPrompt() {
		while (true) {
			// Get the next input from the user
			$in = trim(readline("froxlor> "));
			// Add the response to our history to enable up/down/ctrl-r - search
			readline_add_history($in);
			$this->_parseInput($in);
		}
	}

	/**
	 * function to output a warning on the shell
	 *
	 * @param string|array $msg message to output, multiline if array
	 */
	public function ewarn($msg = null) {
		echo "\n*";
		if (!is_array($msg) && $msg != '') {
			echo "\n* ".$msg;
		} elseif (is_array($msg)) {
			foreach ($msg as $line) {
				echo "\n* ".$line;
			}
		} else {
			echo "\n* EMPTY WARNING! Should *not* happen!";
		}
		echo "\n*\n\n";
	}

	/**
	 * output help-screen
	 */
	public function showHelp() {
		$this->ewarn(array(
				".help\t\t\tshow this help-screen",
				".info\t\t\toutput information about Froxlor-CLI",
				".version\t\tshow version",
				".quit\t\t\texit the Froxlor-CLI",
				"(more to come in later versions)")
		);
	}

	/**
	 * output version
	 */
	public function showVersion() {
		$this->ewarn("Froxlor-CLI version ".self::$_version);
	}

	/**
	 * output information
	 */
	public function showInfo() {
		$this->ewarn(array(
				"Froxlor-CLI is a shell interface to the server-management-panel Froxlor",
				"",
				"It was designed to perform various Froxlor actions in case you either",
				"do not have access to the Froxlor webinterface anymore (webserver misconfigured)",
				"or you prefer working on a shell :)"
		)
		);
	}

	public function parseApiCommand($function, $params) {

		// build up parameter array
		$request_params = null;
		if ($params !== null) {
			$request_params = $this->_parseParams($params);
		}

		try {
			$req = ApiRequest::createRequest(
					$function,
					$request_params
			);
			$this->_api->sendRequest($req);
			$response = $this->_api->getLastResponse();

		} catch (ApiException $e) {
			$response = new ApiResponse(null);
			$_response = unserialize((string)$e);
			$response->addResponse($_response);
		}

		$rarr = $response->getResponse();

		if ($response->getResponseCode() != 200) {
			$err_arr = array(
					"There was an error in your request!",
					$rarr['header']['code'].': '.$rarr['header']['description']
			);
			if (isset($rarr['header']['detailed_messages'])) {
				if (is_array($rarr['header']['detailed_messages'])) {
					foreach ($rarr['header']['detailed_messages'] as $msg) {
						$err_arr[]=$msg;
					}
				} else {
					$err_arr[]=$rarr['header']['detailed_messages'];
				}
			}
			$this->ewarn($err_arr);
		} else {
			print_r($rarr['body']);
		}
	}

	private function _trimValue(&$val = null) {
		return trim($val);
	}

	private function _parseParams($inputline = null) {
		$result = array();
		$p = "/ (?=[\w]+\s*=)/";
		$fulltokens = preg_split($p, $inputline, 0, PREG_SPLIT_NO_EMPTY | PREG_SPLIT_DELIM_CAPTURE);
		foreach ($fulltokens as $assign) {
			$ass_arr = explode("=", $assign);
			$val = trim($ass_arr[1]);
			if ((substr($val, 0, 1) == '"' && substr($val, -1) == '"')
					|| (substr($val, 0, 1) == "'" && substr($val, -1) == "'")
			) {
				$val = substr($val, 1, -1);
			}
			// array
			if (substr($val, 0, 1) == '{' && substr($val, -1) == '}') {
				$val = substr($val, 1, -1);
				$val = $this->_parseArray($val);
			}
			$result[$ass_arr[0]] = $val;
		}
		return $result;
	}

	/*
	 * TODO allow sub-arrays
	*
	* call parseParams recursivly when it calls us...yo dawg
	*/
	private function _parseArray($arrstring) {
		$parmarr = explode(",", $arrstring);
		$output = array();
		foreach ($parmarr as $name => $value) {
			$value = trim($value);
			$output = array_merge($output, $this->_parseParams($name.'='.$value));
		}
		return $output;
	}
}
