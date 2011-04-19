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
 * RRPProxy metaregistry response
 *
 * @author Florian Aders <faders@key-systems.net>
 * @author Tobias Zimmer <tzimmer@key-systems.net>
 * @version 1.6
 * @package rrpproxy
 * @subpackage communication
 * @copyright Copyright (c) 2011, Key-Systems GmbH
 * @license http://opensource.org/licenses/lgpl-2.1 LGPLv2.1
 */
class MREG_Response
{
	/**
	 * The parsed array in native format
	 *
	 * Example for accessing:
	 * <code>
	 * echo $response->response['property']['status'];
	 * </code>
	 * @access public
	 * @var array The parsed array in native format
	 */
	public $response = array();

	/**
	 * The mreg - response in native format
	 *
	 * Example for accessing:
	 * <code>
	 * echo $response->rawResponse;
	 * </code>
	 * @access public
	 * @var array The mreg - response in native format
	 */
	public $rawResponse = "";

	/**
	 * Direct access to the returncode of the response
	 *
	 * This is just for an easier access and relates to $response['code']
	 *
	 * Example for accessing:
	 * <code>
	 * echo $response->code;
	 * </code>
	 *
	 * @access public
	 * @var int Contains the returncode of the response
	 */
	public $code;

	/**
	 * Direct access to the properties of the response
	 *
	 * This is just for an easier access and relates to $response['property']
	 *
	 * Example for accessing:
	 * <code>
	 * echo $response->property['status'];
	 * </code>
	 *
	 * @access public
	 * @var array Contains the properties of the response
	 */
	public $property;

	/**
	 * Direct access to the description of the response
	 *
	 * This is just for an easier access and relates to $response['description']
	 *
	 * Example for accessing:
	 * <code>
	 * echo $response->description;
	 * </code>
	 *
	 * @access public
	 * @var string Contains the description of the response
	 */
	public $description;
	
	/**
	 * Direct access to the total number of list entries in a response
	 *
	 * This is just for an easier access and relates to $response['property']['total'][0]
	 *
	 * Example for accessing:
	 * <code>
	 * echo $response->total;
	 * </code>
	 *
	 * @access public
	 * @var string total number of list entries in response
	 */
	public $total;

	/**
	 * Direct access to the index of the first list entry in the response
	 *
	 * This is just for an easier access and relates to $response['property']['first'][0]
	 *
	 * Example for accessing:
	 * <code>
	 * echo $response->first;
	 * </code>
	 *
	 * @access public
	 * @var string index of first list entry in response
	 */
	public $first;

	/**
	 * Constructor of the response - class
	 *
	 * @param string $response Response returned from RRPProxy
	 * @return object MREG_Response Instance of the response-class
	 */
	function __construct($response)
	{
		$this->rawResponse = $response;
		$this->parseResponse($response);
		if(isset($this->response['code']))
		{
			$this->code = $this->response['code'];
		}
		if(isset($this->response['description']))
		{
			$this->description = &$this->response['description'];
		}
		if(isset($this->response['property']))
		{
			$this->property = $this->response['property'];
		}
		if(isset($this->response['property']['total']))
		{
			$this->total = $this->response['property']['total'][0];
		}
		if(isset($this->response['property']['first']))
		{
			$this->first = $this->response['property']['first'][0];
		}
	}

	/**
	 * Parse the RRPProxy - Response into an array
	 *
	 * Empty lines and lines containing EOF or [RESPONSE] will be ignored
	 *
	 * @param string $string Raw response returned from RRPProxy
	 * @return array Recursive array containing the response
	 */
	private function parseResponse($string)
	{
		// Walk through the response line by line
		$lines = explode("\n" , $string);
		foreach($lines as $line)
		{
			
			// Just lines containing "real" responses are needed (skipping EOF and such)
			if(!preg_match("/^([^=]*)\s*=\s*(.*)/i", $line, $matches))
			{
				continue;
			}

			// Keys will always be lowercase and we don't want spaces
			$key = strtolower(trim($matches[1]));
			$value = trim($matches[2]);
			// description, code, runtime and queuetime don't contain arrays
			if(substr($key, 0, 8) != "property")
			{
				$this->response[$key] = $value;
				continue;
			}

			// Catch all "array" - data
			preg_match_all("/\[([\sa-z0-9_-]+)]/i", $key, $matches, PREG_SET_ORDER);
			
			// Building up the array from bottom to top
			$matches = array_reverse($matches, true);
			// Build the arraystructure for the current line
			$tmpcontainer = array();
			foreach($matches as $index => $property)
			{
				// Arraykeys may not contain spaces, replace them with underscores
				$property = str_replace(" ", "_", $property[1]);

				// We are at the topmost level, merge the array to the total response['propery']-array
				if($index == 0)
				{
					$this->arraySmoothMerge($property, $tmpcontainer, $this->response['property']);
					continue;
				}

				// During the first "round": assign the value we got
				if(empty($tmpcontainer))
				{
					$tmpcontainer[$property] = $value;
				}
				else
				{
					// Encapsulate the last "container" into a new array
					// You know, like the opposite of peeling an onion
					$tmp = $tmpcontainer;
					$tmpcontainer = array();
					$tmpcontainer[$property] = $tmp;
				}
			}
		}
		return $this->response;
	}

	/**
	 * array_merge_recursive, but working with stringbased indices
	 * @param string $property Name of the new array
	 * @param array $container Content of the multi-level array to be "smoothmerged"
	 * @param array &$target Reference of the target where $container should be inserted
	 */
	private function arraySmoothMerge($property, $container, &$target)
	{
		// The "subarray" does not exist in $target, just add it and we are done :)
		if(!isset($target[$property]))
		{
			$target[$property] = $container;
			return true;
		}
		foreach ($container as $key => $value)
		{
			$this->arraySmoothMerge($key, $value, $target[$property]);
		}
	}

	/**
	 * Returns the RRPProxy Response in list format 
	 *
	 * @return array $list RRPProxy Response in list format
	 */
	public function getList ()
	{
		$list = array();
		foreach ($this->response['property'] as $field => $content_array)
		{
			if (in_array($field,array("count","first","last","total","limit")))
			{
				continue;
			}
			foreach ($content_array as $number => $value)
			{
				$list[$number][$field] = $value;
			}
		}
		return $list;
	}

}

/**
 * RRPProxy metaregistry permanent response exceptions
 *
 * @author Florian Aders <faders@key-systems.net>
 * @author Tobias Zimmer <tzimmer@key-systems.net>
 * @version 1.0
 * @package rrpproxy
 * @subpackage communication
 * @copyright Copyright (c) 2011, Key-Systems GmbH
 * @license http://opensource.org/licenses/lgpl-2.1 LGPLv2.1
 */
class MREG_PermanentErrorException extends Exception {
       protected $detailederrors = array();

       public function getDetailedErrorMessage()
       {
               if (count($this->detailederrors) == 0)
               {
                       return false;
               }
               else
               {
                       return $this->detailederrors;
               }
       }

       public function setDetailedErrorMessage($message)
       {
               $this->detailederrors = $message;
       }
}
/**
 * RRPProxy metaregistry temporary response exceptions
 *
 * @author Florian Aders <faders@key-systems.net>
 * @author Tobias Zimmer <tzimmer@key-systems.net>
 * @version 1.0
 * @package rrpproxy
 * @subpackage communication
 * @copyright Copyright (c) 2011, Key-Systems GmbH
 * @license http://opensource.org/licenses/lgpl-2.1 LGPLv2.1
 */
class MREG_TemporaryErrorException extends Exception {
	public function getDetailedErrorMessage()
	{
		return false;
	}
}
