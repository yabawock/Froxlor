<?php

/**
 * Froxlor API Codes class
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
 * Froxlor API Codes class
 *
 * This class defines the API return codes 
 *
 * @copyright  (c) the authors
 * @author     Froxlor team <team@froxlor.org> (2013-)
 * @license    GPLv2 http://files.froxlor.org/misc/COPYING.txt
 * @category   core
 * @package    API
 * @since      0.99.0
 */
class APICodes implements iApiCodes {

	/**
	 * global return-code array
	 */
	protected static $_API_RETURN_CODES = array(
			/*
			 * standard success messages
			 */
			200 => 'Command executed successfully',

			/*
			 * failure messages
			 */
			400 => 'Invalid API structure',
			403 => 'Not allowed',
			404 => 'Not found',
			406 => 'Unacceptable',

			/*
			 * internal failure messages
			 */
			500 => 'Internal API error',
			503 => 'Service unavailable',

			/*
			 * bohoo
			 */
			900 => 'Unknown result'
	);

	/**
	 * (non-PHPdoc)
	 * @see iApiCodes::getCodeDescription()
	 *
	 * @internal for internal use only
	 *
	 * @param int $code the return-code
	 *
	 * @return string code-description
	 * @throws ApiException
	*/
	public static function getCodeDescription($code = 200) {
		if (isset(self::$_API_RETURN_CODES[(int)$code])) {
			return self::$_API_RETURN_CODES[(int)$code];
		}
		throw new ApiException(500, 'Unknown API return code: '.$code);
	}

}
