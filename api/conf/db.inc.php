<?php

/**
 * Froxlor API Database configuration file
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
 * @category   Modules
 * @package    API
 * @since      0.99.0
 */

/**
 * database configuration array
 * @var array
 */
$dbconf = array(
//		'db_driver' => 'mysql:host=localhost;dbname=mydatabase',
		'db_driver' => 'sqlite:'.dirname(FROXLOR_API_DIR).'/data/froxlor.db',
		'db_user' => '',
		'db_password' => ''
);
