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
 * @package    Navigation
 *
 */

return array (
	'customer' => array (
		'index' => array (
			'url' => array('area' => 'customer', 'section' => 'index', 'action' => 'index'),
			'label' => _('Overview'),
			'elements' => array (
				array (
					'label' => sprintf(_('Logged in as %s'), Froxlor::getUser()->getLoginname()),
				),
				array (
					'url' => array('area' => 'customer', 'section' => 'index', 'action' => 'changePassword'),
					'label' => _('Change password'),
				),
				array (
					'url' => array('area' => 'customer', 'section' => 'index', 'action' => 'changeLanguage'),
					'label' => _('Change language'),
				),
				array (
					'url' => array('area' => 'customer', 'section' => 'index', 'action' => 'changeTheme'),
					'label' => _('Change theme'),
				),
				array (
					'url' => array('area' => 'login', 'section' => 'login', 'action' => 'logout'),
					'label' => _('Logout'),
				),
			),
		),
		'email' => array (
			'url' => array('area' => 'customer', 'section' => 'email', 'action' => 'description'),
			'label' => _('E-mail'),
			'elements' => array (
				array (
					'url' => array('area' => 'customer', 'section' => 'email', 'action' => 'index'),
					'label' => _('Addresses'),
					'required_resources' => 'emails',
				),
				array (
					'url' => array('area' => 'customer', 'section' => 'email', 'action' => 'add'),
					'label' => _('Create e-mail address'),
					'required_resources' => 'emails'
				),
				array (
					'url' => array('area' => 'customer', 'section' => 'autoresponder', 'action' => 'index'),
					'label' => _('Autoresponder'),
					'required_resources' => 'emails',
					'show_element' => ( getSetting('autoresponder', 'autoresponder_active') == true ),
				),
				array (
					'url' => getSetting('panel', 'webmail_url'),
					'new_window' => true,
					'label' => _('Webmail'),
					'required_resources' => 'emails_used',
					'show_element' => ( getSetting('panel', 'webmail_url') != '' ),
				),
			),
		),
		'mysql' => array (
			'url' => array('area' => 'customer', 'section' => 'mysql', 'action' => 'description'),
			'label' => _('MySQL'),
			'elements' => array (
				array (
					'url' => array('area' => 'customer', 'section' => 'mysql', 'action' => 'index'),
					'label' => _('Databases'),
					'required_resources' => 'mysqls',
				),
				array (
					'url' => getSetting('panel', 'phpmyadmin_url'),
					'new_window' => true,
					'label' => _('phpMyAdmin'),
					'required_resources' => 'mysqls_used',
					'show_element' => ( getSetting('panel', 'phpmyadmin_url') != '' ),
				),
			),
		),
		'domains' => array (
			'url' => array('area' => 'customer', 'section' => 'domains', 'action' => 'description'),
			'label' => _('Domains'),
			'elements' => array (
				array (
					'url' => array('area' => 'customer', 'section' => 'domains', 'action' => 'index'),
					'label' => _('Settings'),
				),
			),
		),
		'ftp' => array (
			'url' => array('area' => 'customer', 'section' => 'ftp', 'action' => 'description'),
			'label' => _('FTP'),
			'elements' => array (
				array (
					'url' => array('area' => 'customer', 'section' => 'ftp', 'action' => 'index'),
					'label' => _('Accounts'),
				),
				array (
					'url' => getSetting('panel', 'webftp_url'),
					'new_window' => true,
					'label' => _('WebFTP'),
					'show_element' => ( getSetting('panel', 'webftp_url') != '' ),
				),
			),
		),
		'extras' => array (
			'url' => array('area' => 'customer', 'section' => 'extras', 'action' => 'description'),
			'label' => _('Extras'),
			'elements' => array (
				array (
					'url' => array('area' => 'customer', 'section' => 'extras', 'action' => 'htpasswd'),
					'label' => _('Directory protection'),
				),
				array (
					'url' => array('area' => 'customer', 'section' => 'extras', 'action' => 'htaccess'),
					'label' => _('Path options'),
				),
				array (
					'url' => array('area' => 'customer', 'section' => 'extras', 'action' => 'backup'),
					'label' => _('Backup'),
					'required_resources' => 'backup_allowed',
				),
			),
		),
		'traffic' => array (
			'url' => array('area' => 'customer', 'section' => 'traffic', 'action' => 'index'),
			'label' => _('Traffic'),
			'elements' => array (
				array (
					'url' => array('area' => 'customer', 'section' => 'traffic', 'action' => 'current'),
					'label' => _('Current month'),
				),
			),
		),
	),
	'admin' => array (
		'index' => array (
			'url' => array('area' => 'admin', 'section' => 'index', 'action' => 'index'),
			'label' => _('Overview'),
			'elements' => array (
				array (
					'label' => sprintf(_('Logged in as: %s'), Froxlor::getUser()->getLoginname()),
				),
				array (
					'url' => array('area' => 'admin', 'section' => 'index', 'action' => 'changePassword'),
					'label' => _('Change password'),
				),
				array (
					'url' => array('area' => 'admin', 'section' => 'index', 'action' => 'changeLanguage'),
					'label' => _('Change language'),
				),
				array (
					'url' => array('area' => 'admin', 'section' => 'index', 'action' => 'changeTheme'),
					'label' => _('Change theme'),
				),
				array (
					'url' => array('area' => 'login', 'section' => 'login', 'action' => 'logout'),
					'label' => _('Logout'),
				),
			),
		),
		'resources' => array (
			'label' => _('Resources'),
			'required_resources' => 'customers',
			'elements' => array (
				array (
					'url' => array('area' => 'admin', 'section' => 'customers', 'action' => 'index'),
					'label' => _('Customers'),
					'required_resources' => 'customers',
				),
				array (
					'url' => array('area' => 'admin', 'section' => 'domains', 'action' => 'index'),
					'label' => _('Domains'),
					'required_resources' => 'domains',
				),
				array (
					'url' => array('area' => 'admin', 'section' => 'admins', 'action' => 'index'),
					'label' => _('Admins'),
					'required_resources' => 'change_serversettings',
				),
			),
		),
		'traffic' => array (
			'label' => _('Traffic'),
			'required_resources' => 'customers',
			'elements' => array (
				array (
					'url' => array('area' => 'admin', 'section' => 'traffic', 'action' => 'index'),
					'label' => _('Customers'),
					'required_resources' => 'customers',
				),
			),
		),
		'server' => array (
			'label' => _('Server'),
			'required_resources' => 'change_serversettings',
			'elements' => array (
				array (
					'url' => array('area' => 'admin', 'section' => 'configfiles', 'action' => 'index'),
					'label' => _('Configuration'),
					'required_resources' => 'change_serversettings',
				),
				array (
					'url' => array('area' => 'admin', 'section' => 'settings', 'action' => 'index'),
					'label' => _('Settings'),
					'required_resources' => 'change_serversettings',
				),
				array (
					'url' => array('area' => 'admin', 'section' => 'ipsandports', 'action' => 'index'),
					'label' => _('IPs and ports'),
					'required_resources' => 'change_serversettings',
				),
				array (
					'url' => array('area' => 'admin', 'section' => 'cronjobs', 'action' => 'index'),
					'label' => _('Cronjob settings'),
					'required_resources' => 'change_serversettings',
				),
				array (
					'url' => array('area' => 'admin', 'section' => 'settings', 'action' => 'rebuildconfigs'),
					'label' => _('Rebuild config files'),
					'required_resources' => 'change_serversettings',
				),
				array (
					'url' => array('area' => 'admin', 'section' => 'settings', 'action' => 'updatecounters'),
					'label' => _('Recalculate resource usage'),
					'required_resources' => 'change_serversettings',
				),
				array (
					'url' => array('area' => 'admin', 'section' => 'settings', 'action' => 'index'),
					'label' => _('PHP configurations'),
					'show_element' => (
						getSetting('system', 'mod_fcgid') == true
						/*
						 * @TODO activate if phpfpm knows custom php.ini files
						 *
						 * || getSetting('phpfpm', 'enabled') == true
						 */
						),
				),
			),
		),
		'misc' => array (
			'label' => _('Miscellaneous'),
			'elements' => array (
				array (
					'url' => array('area' => 'admin', 'section' => 'templates', 'action' => 'email'),
					'label' => _('E-Mail & file templates'),
				),
				array (
					'url' => array('area' => 'admin', 'section' => 'logger', 'action' => 'index'),
					'label' => _('System logging'),
					'required_resources' => 'change_serversettings',
					'show_element' => ( getSetting('logger', 'enabled') == true ),
				),
				array (
					'url' => array('area' => 'admin', 'section' => 'message', 'action' => 'index'),
					'label' => _('Write a message'),
				),
			),
		),
	),
);
