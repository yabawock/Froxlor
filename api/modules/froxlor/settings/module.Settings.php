<?php

/**
 * Froxlor API Settings-Module
 *
 * PHP version 5
 *
 * This file is part of the Froxlor project.
 * Copyright (c) 2003-2009 the SysCP Team (see authors).
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
 * Class Settings
 *
 * @copyright  (c) the authors
 * @author     Froxlor team <team@froxlor.org> (2010-)
 * @license    GPLv2 http://files.froxlor.org/misc/COPYING.txt
 * @category   core
 * @package    API
 * @since      0.99.0
 */
class Settings implements iSettings {

	/**
	 * @see iSettings::listSettings()
	 *
	 * @return array
	 */
	public static function listSettings(array &$params = null) {

		// check for limit-parameter
		$limit = array();
		if ($params != null
				&& is_array($params)
				&& isset($params['limit'])
				&& $params['limit'] != ''
		) {
			// explode by dot
			$limit = explode('.', $params['limit']);
		}

		// is something in there?
		if (isset($limit[0])
				&& $limit[0] != ''
		) {
			// limit by given module
			$fields = 'module = :mod';
			$values = array(':mod' => $limit[0]);
			// check for more limitation
			if (isset($limit[1])
					&& $limit[1] != ''
			) {
				// limit also by section
				$fields .= ' AND section = :sec';
				$values[':sec'] = $limit[1];
			}
			// find them
			$settings = Database::find('frx_settings', $fields, $values);
		} else {
			// find all of them
			$settings = Database::findAll('frx_settings',' ORDER BY module');
		}

		// create array from beans
		$settings_array = array();
		foreach ($settings as $sbean) {
			$settings_array[] = $sbean->export();
		}

		// return all the settings as array (api)
		return ApiResponse::createResponse(
				200,
				null,
				$settings_array
		);
	}

	/**
	 * @see iSettings::statusSetting()
	 *
	 * @return mixed settings value
	 */
	public static function statusSetting(array &$param) {

		// explode ident-parameter by dot
		$params = explode('.', $param['ident']);

		// validate it
		if (!is_array($params)
				|| count($params) != 3
		) {
			throw new ApiException(406, 'invalid parameter list for '.__FUNCTION__);
		}

		// set database-parameter
		$dbparam = array(
				':mod' => $params[0],
				':sec' => $params[1],
				':nam' => $params[2]
		);

		// go find the setting
		$setting = Database::findOne('frx_settings', 'module = :mod AND section = :sec AND name = :nam', $dbparam);

		// if null, no setting was found
		if ($setting === null) {
			throw new ApiException(404, 'Setting "'.$param['ident'].'" not found');
		}

		// return it as array
		return ApiResponse::createResponse(
				200,
				null,
				$setting->export()
		);
	}
}
