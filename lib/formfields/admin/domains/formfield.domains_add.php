<?php

/**
 * This file is part of the Froxlor project.
 * Copyright (c) 2010 the Froxlor Team (see authors).
 *
 * For the full copyright and license information, please view the COPYING
 * file that was distributed with this source code. You can also view the
 * COPYING file online at http://files.froxlor.org/misc/COPYING.txt
 *
 * @copyright  (c) the authors
 * @author     Froxlor team <team@froxlor.org> (2010-)
 * @license    GPLv2 http://files.froxlor.org/misc/COPYING.txt
 * @package    Formfields
 *
 */

return array(
	'domain_add' => array(
		'title' => _('Create domain'),
		'image' => 'icons/domain_add.png',
		'sections' => array(
			'section_a' => array(
				'title' => _('Domain settings'),
				'image' => 'icons/domain_add.png',
				'fields' => array(
					'domain' => array(
						'label' => 'Domain',
						'type' => 'text',
						'mandatory' => true,
						'validation' => array(
							'required' => true,
							'format' => 'domain',
						)
					),
					'customerid' => array(
						'label' => _('Customer'),
						'type' => 'select',
						'select_var' => $customers,
						'mandatory' => true,
						'validate' => array(
							'required' => 'true',
							'format' => 'number',
						)
					),
					'adminid' => array(
						'visible' => (Froxlor::getUser()->getData('resources', 'customers_see_all') == '1' ? true : false),
						'label' => _('Admin'),
						'type' => 'select',
						'select_var' => $admins,
						'mandatory' => true,
						'validate' => array(
							'required' => 'true',
							'format' => 'number',
						)
					),
					'alias' => array(
						'label' => _('Alias for domain'),
						'type' => 'select',
						'select_var' => $domains,
						'validate' => array(
							'required' => 'false',
							'format' => 'number',
						)
					),
					'issubof' => array(
						'label' => _('This domain is a subdomain of another domain'),
						'desc' => _('You have to set this to the correct domain if you want to add a subdomain as full-domain (e.g. you want to add "www.domain.tld", you have to select "domain.tld" here)'),
						'type' => 'select',
						'select_var' => $subtodomains,
						'validate' => array(
							'required' => 'false',
							'format' => 'number',
						)
					),
					'caneditdomain' => array(
						'label' => _('Edit domain'),
						'type' => 'checkbox',
						'values' => array(
										array ('label' => _('yes'), 'value' => '1')
									),
						'value' => array('1'),
						'validate' => array(
							'required' => 'false',
							'format' => 'boolean',
						)
					),
					'add_date' => array(
						'label' => _('Added to Froxlor'),
						'desc' => _('YYYY-MM-DD'),
						'type' => 'label',
						'value' => $add_date,
					),
					'registration_date' => array(
						'label' => _('Added at registry'),
						'desc' => _('YYYY-MM-DD'),
						'type' => 'text',
						'size' => 10,
						'validate' => array(
							'required' => false,
							'regex' => '(19|20)\d\d[-](0[1-9]|1[012])[-](0[1-9]|[12][0-9]|3[01])'
						),
					)
				)
			),
			'section_b' => array(
				'title' => _('Webserver settings'),
				'image' => 'icons/domain_add.png',
				'fields' => array(
					'documentroot' => array(
						'visible' => (Froxlor::getUser()->getData('resources', 'change_serversettings') == '1' ? true : false),
						'label' => 'DocumentRoot',
						'desc' => _('empty for defaults'),
						'type' => 'text'
					),
					'ipandport' => array(
						'label' => _('IP:Port'),
						'type' => 'select',
						'select_var' => $ipsandports,
						'mandatory' => true,
						'validate' => array(
							'required' => 'true',
							'format' => 'number',
						)
					),
					'ssl' => array(
						'visible' => (getSetting('system', 'use_ssl') == '1' ? ($ssl_ipsandports != '' ? true : false) : false),
						'label' => _('SSL'),
						'type' => 'checkbox',
						'values' => array(
										array ('label' => _('yes'), 'value' => '1')
									),
						'value' => array(),
						'validate' => array(
							'required' => 'false',
							'format' => 'boolean',
						)
					),
					'ssl_redirect' => array(
						'visible' => (getSetting('system', 'use_ssl') == '1' ? ($ssl_ipsandports != '' ? true : false) : false),
						'label' => _('SSL Redirect'),
						'type' => 'checkbox',
						'values' => array(
										array ('label' => _('yes'), 'value' => '1')
									),
						'value' => array(),
						'validate' => array(
							'required' => 'false',
							'format' => 'boolean',
						)
					),
					'ssl_ipandport' => array(
						'visible' => (getSetting('system', 'use_ssl') == '1' ? ($ssl_ipsandports != '' ? true : false) : false),
						'label' => _('SSL IP:Port'),
						'type' => 'select',
						'select_var' => $ssl_ipsandports,
						'validate' => array(
							'required' => 'false',
							'format' => 'number',
						)
					),
					'no_ssl_available_info' => array(
						'visible' => (getSetting('system', 'use_ssl') == '1' ? ($ssl_ipsandports == '' ? true : false) : false),
						'label' => _('SSL'),
						'type' => 'label',
						'value' => _('There are currently no ssl ip/port combinations for this server')
					),
					'wwwserveralias' => array(
						'label' => _('Add a "www." ServerAlias'),
						'type' => 'checkbox',
						'values' => array(
										array ('label' => _('yes'), 'value' => '1')
									),
						'value' => array('1'),
						'validate' => array(
							'required' => 'false',
							'format' => 'boolean',
						)
					),
					'speciallogfile' => array(
						'label' => 'Speciallogfile',
						'type' => 'checkbox',
						'values' => array(
										array ('label' => _('yes'), 'value' => '1')
									),
						'value' => array(),
						'validate' => array(
							'required' => 'false',
							'format' => 'boolean',
						)
					),
					'specialsettings' => array(
						'visible' => (Froxlor::getUser()->getData('resources', 'change_serversettings') == '1' ? true : false),
						'style' => 'vertical-align:top;',
						'label' => _('Own vHost-settings'),
						'desc' => _('The content of this field will be included into the domain vHost container directly. Attention: The code won\'t be checked for any errors. If it contains errors, webserver might not start again!'),
						'type' => 'textarea',
						'cols' => 60,
						'rows' => 12,
						'validate' => array(
							'required' => false,
							'regex' => '[^\0]*',
						)
					)
				)
			),
			'section_c' => array(
				'title' => _('PHP Settings'),
				'image' => 'icons/domain_add.png',
				'visible' => ((Froxlor::getUser()->getData('resources', 'change_serversettings') == '1' || Froxlor::getUser()->getData('resources', 'caneditphpsettings') == '1') ? true : false),
				'fields' => array(
					'openbasedir' => array(
						'label' => _('OpenBasedir'),
						'type' => 'checkbox',
						'values' => array(
										array ('label' => _('yes'), 'value' => '1')
									),
						'value' => array('1'),
						'validate' => array(
							'required' => 'false',
							'format' => 'boolean',
						)
					),
					'safemode' => array(
						'label' => _('Safemode'),
						'type' => 'checkbox',
						'values' => array(
										array ('label' => _('yes'), 'value' => '1')
									),
						'value' => array('1'),
						'validate' => array(
							'required' => 'false',
							'format' => 'boolean',
						)
					),
					'phpsettingid' => array(
						'visible' => ((int)getSetting('system', 'mod_fcgid') == 1 ? true : false),
						'label' => _('PHP Configuration'),
						'type' => 'select',
						'select_var' => $phpconfigs,
						'validate' => array(
							'required' => 'false',
							'format' => 'number',
						)
					),
					'mod_fcgid_starter' => array(
						'visible' => ((int)getSetting('system', 'mod_fcgid') == 1 ? true : false),
						'label' => _('PHP Processes for this domain'),
						'desc' => _('empty for default'),
						'type' => 'text',
						'validate' => array(
							'required' => 'false',
							'format' => 'number',
						)
					),
					'mod_fcgid_maxrequests' => array(
						'visible' => ((int)getSetting('system', 'mod_fcgid') == 1 ? true : false),
						'label' => _('Maximum php requests for this domain'),
						'desc' => _('empty for defaults'),
						'type' => 'text',
						'validate' => array(
							'required' => 'false',
							'format' => 'number',
						)
					)
				)
			),
			'section_d' => array(
				'title' => _('Nameserver settings'),
				'image' => 'icons/domain_add.png',
				'visible' => (Froxlor::getUser()->getData('resources', 'change_serversettings') == '1' ? true : false),
				'fields' => array(
					'isbinddomain' => array(
						'label' => _('Nameserver'),
						'type' => 'checkbox',
						'values' => array(
										array ('label' => _('yes'), 'value' => '1')
									),
						'value' => array('1'),
						'validate' => array(
							'required' => 'false',
							'format' => 'boolean',
						)
					),
					'zonefile' => array(
						'label' => _('Zonefile'),
						'desc' => _('empty for defaults'),
						'type' => 'text'
					)
				)
			),
			'section_e' => array(
				'title' => _('Mailserver settings'),
				'image' => 'icons/domain_add.png',
				'fields' => array(
					'isemaildomain' => array(
						'label' => _('Emaildomain'),
						'type' => 'checkbox',
						'values' => array(
										array ('label' => _('yes'), 'value' => '1')
									),
						'value' => array('1'),
						'validate' => array(
							'required' => 'false',
							'format' => 'boolean',
						)
					),
					'email_only' => array(
						'label' => _('Only email?'),
						'type' => 'checkbox',
						'values' => array(
										array ('label' => _('yes'), 'value' => '1')
									),
						'value' => array(),
						'validate' => array(
							'required' => 'false',
							'format' => 'boolean',
						)
					),
					'subcanemaildomain' => array(
						'label' => _('Subdomains as emaildomains'),
						'type' => 'select',
						'select_var' => $subcanemaildomain,
						'validate' => array(
							'required' => 'false',
							'regex' => '(0|1|2|3)',
						)
					),
					'dkim' => array(
						'visible' => (getSetting('dkim', 'use_dkim') == '1' ? true : false),
						'label' => 'DomainKeys',
						'type' => 'checkbox',
						'values' => array(
										array ('label' => _('yes'), 'value' => '1')
									),
						'value' => array('1'),
						'validate' => array(
							'required' => 'false',
							'format' => 'boolean',
						)
					)
				)
			)
		)
	)
);
