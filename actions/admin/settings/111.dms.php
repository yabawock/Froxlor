<?php

/**
 * This file is part of the Froxlor project.
 * Copyright (c) 2003-2009 the SysCP Team (see authors).
 * Copyright (c) 2010 the Froxlor Team (see authors).
 *
 * For the full copyright and license information, please view the COPYING
 * file that was distributed with this source code. You can also view the
 * COPYING file online at http://files.froxlor.org/misc/COPYING.txt
 *
 * @copyright  (c) the authors
 * @author     Florian Lippert <flo@syscp.org> (2003-2009)
 * @author     Froxlor team <team@froxlor.org> (2010-)
 * @license    GPLv2 http://files.froxlor.org/misc/COPYING.txt
 * @package    Settings
 * @version    $Id$
 */

return array(
	'groups' => array(
		'dms' => array(
			'title' => 'Domain Management System',
			'fields' => array(
				'dms_active' => array(
					'label' => 'Activate',
					'settinggroup' => 'dms',
					'varname' => 'dms_active',
					'type' => 'bool',
					'default' => true,
					'save_method' => 'storeSettingField',
				),
				'dms_backend' => array(
					'label' => 'Select a backend',
					'settinggroup' => 'dms',
					'varname' => 'dms_backend',
					'type' => 'option',
					'option_mode' => 'one',
					'option_options_method' => 'getDMSBackend',
					'save_method' => 'storeSettingField',
				),
				'dms_sub_default' => array(
					'label' => 'Default Account',
					'varname' => 'dms_sub_default_user',
					'type' => 'string'
				),
				'dms_sub_default_password' => array(
					'label' => 'Default Password',
					'varname' => 'dms_sub_default_pass',
					'type' => 'string'
				),
				'dms_sub_create_new' => array(
					'label' => 'Create new Sub-Accounts',
					'settinggroup' => 'dms',
					'varname' => 'dms_active',
					'type' => 'bool',
					'default' => true,
					'save_method' => 'storeSettingField',
				),
				'dms_sub_create_new_max' => array(
					'label' => 'Create max. new Sub-Accounts',
					'settinggroup' => 'dms',
					'varname' => 'dms_active',
					'type' => 'string',
					'default' => '5',
					'save_method' => 'storeSettingField',
				),
			)
		)
	),
);

?>