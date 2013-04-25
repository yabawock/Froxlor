<?php

/**
 * This file is used for testing purposes only!
 */

include_once dirname(__FILE__).'/api/froxlor-api.php';

$apikey = null; // "mysupersecretkey";

$username = 'superadmin';
$password = 'omgsomethingcrypted';

try {

	$froxlor = Froxlor::getInstance($apikey);
	$froxlor->apiCall('Core.doLogin', array('username' => $username, 'password' => $password));
	$cli = new FroxlorCliInterface($froxlor);

} catch (Exception $e) {
	// response == error
	$req_result = @unserialize((string)$e);
	// in case an internal error occured
	if ($req_result === false) {
		$_e = new ApiException(500, (string)$e);
		$req_result = unserialize((string)$_e);
	}
	$api_response = new ApiResponse();
	$api_response->addResponse($req_result);
	print_r($api_response->getResponse());
}
