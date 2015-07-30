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
 *
 */

return array(
	'groups' => array(
			'ssl' => array(
					'title' => $lng['admin']['sslsettings'],
					'fields' => array(
							'system_ssl_enabled' => array(
									'label' => $lng['serversettings']['ssl']['use_ssl'],
									'settinggroup' => 'system',
									'varname' => 'use_ssl',
									'type' => 'bool',
									'default' => false,
									'save_method' => 'storeSettingField',
									'overview_option' => true
							),
							'system_ssl_cipher_list' => array(
									'label' => $lng['serversettings']['ssl']['ssl_cipher_list'],
									'settinggroup' => 'system',
									'varname' => 'ssl_cipher_list',
									'type' => 'string',
									'string_emptyallowed' => false,
									'default' => 'ECDHE-RSA-AES128-GCM-SHA256:ECDHE-ECDSA-AES128-GCM-SHA256:ECDHE-RSA-AES256-GCM-SHA384:ECDHE-ECDSA-AES256-GCM-SHA384:DHE-RSA-AES128-GCM-SHA256:DHE-DSS-AES128-GCM-SHA256:kEDH+AESGCM:ECDHE-RSA-AES128-SHA256:ECDHE-ECDSA-AES128-SHA256:ECDHE-RSA-AES128-SHA:ECDHE-ECDSA-AES128-SHA:ECDHE-RSA-AES256-SHA384:ECDHE-ECDSA-AES256-SHA384:ECDHE-RSA-AES256-SHA:ECDHE-ECDSA-AES256-SHA:DHE-RSA-AES128-SHA256:DHE-RSA-AES128-SHA:DHE-DSS-AES128-SHA256:DHE-RSA-AES256-SHA256:DHE-DSS-AES256-SHA:DHE-RSA-AES256-SHA:AES128-GCM-SHA256:AES256-GCM-SHA384:AES128-SHA256:AES256-SHA256:AES128-SHA:AES256-SHA:AES:CAMELLIA:DES-CBC3-SHA:!aNULL:!eNULL:!EXPORT:!DES:!RC4:!MD5:!PSK:!aECDH:!EDH-DSS-DES-CBC3-SHA:!EDH-RSA-DES-CBC3-SHA:!KRB5-DES-CBC3-SHA',
									'save_method' => 'storeSettingField',
							),
							'system_ssl_cert_file' => array(
									'label' => $lng['serversettings']['ssl']['ssl_cert_file'],
									'settinggroup' => 'system',
									'varname' => 'ssl_cert_file',
									'type' => 'string',
									'string_type' => 'file',
									'string_emptyallowed' => true,
									'default' => '/etc/apache2/apache2.pem',
									'save_method' => 'storeSettingField',
							),
							'system_ssl_key_file' => array(
									'label' => $lng['serversettings']['ssl']['ssl_key_file'],
									'settinggroup' => 'system',
									'varname' => 'ssl_key_file',
									'type' => 'string',
									'string_type' => 'file',
									'string_emptyallowed' => true,
									'default' => '/etc/apache2/apache2.key',
									'save_method' => 'storeSettingField',
							),
							'system_ssl_cert_chainfile' => array(
									'label' => $lng['admin']['ipsandports']['ssl_cert_chainfile'],
									'settinggroup' => 'system',
									'varname' => 'ssl_cert_chainfile',
									'type' => 'string',
									'string_type' => 'file',
									'string_emptyallowed' => true,
									'default' => '',
									'save_method' => 'storeSettingField',
							),
							'system_ssl_ca_file' => array(
									'label' => $lng['serversettings']['ssl']['ssl_ca_file'],
									'settinggroup' => 'system',
									'varname' => 'ssl_ca_file',
									'type' => 'string',
									'string_type' => 'file',
									'string_emptyallowed' => true,
									'default' => '',
									'save_method' => 'storeSettingField',
							)
					)
			)
		)
	);
