<?php

/**
 * Froxlor API Settings-Module
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
 * Class Settings
 *
 * @copyright  (c) the authors
 * @author     Froxlor team <team@froxlor.org> (2013-)
 * @license    GPLv2 http://files.froxlor.org/misc/COPYING.txt
 * @category   Modules
 * @package    API
 * @since      0.99.0
 */
class Settings extends FroxlorModule {

	/**
	 * list all available settings, optionally
	 * limited by given 'module' and 'section'
	 * (separated by a dot, e.g. 'limit' => 'Core[.system]')
	 * In case you need to list all settings of a
	 * specific section, you can use an asterisk,
	 * e.g. 'limit' => '*.domain'
	 *
	 * @return array
	 */
	public static function listSettings() {

		// check for limit-parameter
		$limit = array();
		$params = self::getParam('limit', true);
		$limit = explode('.', $params);

		// is something in there?
		if (isset($limit[0])
				&& $limit[0] != ''
		) {
			// limit by given module
			$fields = '';
			$values = array();

			if ($limit[0] != '*') {
				$fields .= 'module = :mod';
				$values[':mod'] = $limit[0];
			}

			// check for more limitation
			if (isset($limit[1])
					&& $limit[1] != ''
					&& $limit[1] != '*'
			) {
				// limit also by section
				if ($fields != '') {
					$fields .= ' AND ';
				}
				$fields .= 'section = :sec';
				$values[':sec'] = $limit[1];
			}
			// find them
			$settings = Database::find('settings', $fields, $values);
		} else {
			// find all of them
			$settings = Database::findAll('settings',' ORDER BY module');
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
	 * return a specific setting by given
	 * 'module', 'section' and 'var'
	 * (separated by a dot, e.g. 'ident' => 'Core.system.something')
	 *
	 * @return mixed settings value
	 */
	public static function statusSetting() {

		// get ident parameter
		$params = self::getParamIdent('ident', 3);

		// set database-parameter
		$dbparam = array(
				':mod' => $params[0],
				':sec' => $params[1],
				':nam' => $params[2]
		);

		// go find the setting
		$setting = Database::findOne('settings', 'module = :mod AND section = :sec AND name = :nam', $dbparam);

		// if null, no setting was found
		if ($setting === null) {
			throw new SettingsException(404, 'Setting "'.implode('.', $params).'" not found');
		}

		// return it as array
		return ApiResponse::createResponse(
				200,
				null,
				$setting->export()
		);
	}

	/**
	 * (non-PHPdoc)
	 * @see FroxlorModule::Core_moduleSetup()
	 */
	public function Core_moduleSetup() {
	}
}
