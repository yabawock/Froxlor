<?php

/**
 * Froxlor API CLI starter file
 *
 * This file is being run via the api-cli shell script
 * to open the froxlor-cli shell
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
 * if you don't want to login all the time, set your
 * username and password here
 */
$login = array(
		'username' => 'superadmin',
		'password' => 'omgsomethingcrypted'
);

// --- don't touch anything below ---

include_once dirname(__FILE__).'/froxlor-api.php';

$do_quicklogin = false;
if (is_array($login)
		&& count($login) == 2
		&& isset($login['username']) && $login['username'] !== ''
		&& isset($login['password']) && $login['password'] !== ''
) {
	$do_quicklogin = true;
}

try {

	$froxlor = Froxlor::getInstance(null);
	// quick login
	if ($do_quicklogin) {
		$froxlor->apiCall('Core.doLogin', $login);
	}
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
