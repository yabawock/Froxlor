<?php

/**
 * Froxlor API version interface
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
 * Interface ApiVersion
 *
 * @copyright  (c) the authors
 * @author     Froxlor team <team@froxlor.org> (2013-)
 * @license    GPLv2 http://files.froxlor.org/misc/COPYING.txt
 * @category   core
 * @package    API
 * @since      0.99.0
 */
interface iApiVersion {

	/**
	 * This is the only place where the
	 * Froxlor API version is defined
	 *
	 * @var const
	 */
	const API_VERSION = '0.99';

	/**
	 * Main Froxlor version which is
	 * used to show in the panel and
	 * check for new version etc.
	 *
	 * @var const
	 */
	const API_RELEASE_VERSION = '0.99.0';
}
