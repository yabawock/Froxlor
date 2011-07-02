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
	'customer_add' => array(
		'title' => _('Create customer'),
		'image' => 'icons/user_add.png',
		'sections' => array(
			'section_a' => array(
				'title' => _('Account data'),
				'image' => 'icons/user_add.png',
				'fields' => array(
					'new_loginname' => array(
						'label' => _('Username'),
						'type' => 'text',
						'validation' => array(
							'required' => false,
							'regex' => '[a-zA-Z][a-zA-Z0-9]+',
							'minlen' => 1,
							'maxlen' => 12
						)
					),
					'createstdsubdomain' => array(
						'label' => _('Create standard subdomain'),
						'type' => 'checkbox',
						'values' => array(
										array ('label' => _('Yes'), 'value' => '1')
									),
						'value' => array('1'),
						'validation' => array(
							'required' => false,
							'minval' => 0,
							'maxval' => 1
						)
					),
					'store_defaultindex' => array(
						'label' => _('Store default index file to customers docroot'),
						'type' => 'checkbox',
						'values' => array(
										array ('label' => _('Yes'), 'value' => '1')
									),
						'value' => array('1'),
						'validation' => array(
							'required' => false,
							'minval' => 0,
							'maxval' => 1
						)
					),
					'new_customer_password' => array(
						'label' => _('Password'),
						'type' => 'password',
						'validation' => array(
							'required' => false,
							'format' => 'password',
						),
					),
					'new_customer_password_suggestion' => array(
						'label' => _('Password suggestion'),
						'type' => 'text',
						'value' => generatePassword(),
					),
					'sendpassword' => array(
						'label' => _('Send password'),
						'type' => 'checkbox',
						'values' => array(
										array ('label' => _('Yes'), 'value' => '1')
									),
						'value' => array('1'),
						'validation' => array(
							'required' => false,
							'minval' => 0,
							'maxval' => 1
						)
					),
					'def_language' => array(
						'label' => _('Language'),
						'type' => 'select',
						'select_var' => $language_options
					)
				)
			),
			'section_b' => array(
				'title' => _('Contact data'),
				'image' => 'icons/user_add.png',
				'fields' => array(
					'name' => array(
						'label' => _('Name'),
						'type' => 'text',
						'mandatory_ex' => true,
						'validation' => array(
							'required' => false,
							'format' => 'string',
						)
					),
					'firstname' => array(
						'label' => _('Firstname'),
						'type' => 'text',
						'mandatory_ex' => true,
						'validation' => array(
							'required' => false,
							'format' => 'string',
						)
					),
					'gender' => array(
						'label' => _('Title'),
						'type' => 'select',
						'select_var' => $gender_options
					),
					'company' => array(
						'label' => _('Company'),
						'type' => 'text',
						'mandatory_ex' => true,
						'validation' => array(
							'required' => false,
							'format' => 'string',
						)
					),
					'street' => array(
						'label' => _('Street'),
						'type' => 'text',
						'validation' => array(
							'required' => false,
							'format' => 'string',
						)
					),
					'zipcode' => array(
						'label' => _('Zipcode'),
						'type' => 'text',
						'validation' => array (
							'required' => false,
							'format' => 'zipcode',
						)
					),
					'city' => array(
						'label' => _('City'),
						'type' => 'text',
						'validation' => array(
							'required' => false,
							'format' => 'string',
						)
					),
					'countrycode' => array(
						'label' => _('Country'),
						'type' => 'select',
						'select_var' => $countrycode
					),
					'phone' => array(
						'label' => _('Phone'),
						'type' => 'text',
						'validation' => array (
							'required' => false,
							'format' => 'phone',
						)
					),
					'fax' => array(
						'label' => _('Fax'),
						'type' => 'text',
						'validation' => array(
							'required' => false,
							'format' => 'phone',
						)
					),
					'email' => array(
						'label' => _('E-mail'),
						'type' => 'text',
						'mandatory' => true,
						'validation' => array(
							'required' => true,
							'format' => 'email',
						)
					),
					'customernumber' => array(
						'label' => _('Customer number'),
						'type' => 'text',
						'validation' => array(
							'required' => false,
							'format' => 'string',
						)
					)
				)
			),
			'section_c' => array(
				'title' => _('Service data'),
				'image' => 'icons/user_add.png',
				'fields' => array(
					'diskspace' => array(
						'label' => _('Webspace'),
						'type' => 'textul',
						'value' => 0,
						'maxlength' => 6,
						'mandatory' => true,
						'ul_field' => $diskspace_ul,
						'validation' => array(
							'required' => true,
							'format' => 'decimal',
						)

					),
					'traffic' => array(
						'label' => _('Traffic'),
						'type' => 'textul',
						'value' => 0,
						'maxlength' => 4,
						'mandatory' => true,
						'ul_field' => $traffic_ul,
						'validation' => array(
							'required' => true,
							'format' => 'decimal',
						)
					),
					'subdomains' => array(
						'label' => _('Subdomains'),
						'type' => 'textul',
						'value' => 0,
						'maxlength' => 9,
						'mandatory' => true,
						'ul_field' => $subdomains_ul,
						'validation' => array(
							'required' => true,
							'format' => 'number',
						)
					),
					'emails' => array(
						'label' => _('E-mail addresses'),
						'type' => 'textul',
						'value' => 0,
						'maxlength' => 9,
						'mandatory' => true,
						'ul_field' => $emails_ul,
						'validation' => array(
							'required' => true,
							'format' => 'number',
						)
					),
					'email_accounts' => array(
						'label' => _('E-mail accounts'),
						'type' => 'textul',
						'value' => 0,
						'maxlength' => 9,
						'mandatory' => true,
						'ul_field' => $email_accounts_ul,
						'validation' => array(
							'required' => true,
							'format' => 'number',
						)
					),
					'email_forwarders' => array(
						'label' => _('E-mail forwarders'),
						'type' => 'textul',
						'value' => 0,
						'maxlength' => 9,
						'mandatory' => true,
						'ul_field' => $email_forwarders_ul,
						'validation' => array(
							'required' => true,
							'format' => 'number',
						)
					),
					'email_quota' => array(
						'label' => _('E-mail quota'),
						'type' => 'textul',
						'value' => 0,
						'maxlength' => 9,
						'visible' => (getSetting('system', 'mail_quota_enabled') == '1' ? true : false),
						'mandatory' => true,
						'ul_field' => $email_quota_ul,
						'validation' => array(
							'required' => true,
							'format' => 'number',
						)
					),
					'email_autoresponder' => array(
						'label' => _('E-mail autoresponder'),
						'type' => 'textul',
						'value' => 0,
						'maxlength' => 9,
						'visible' => (getSetting('autoresponder', 'autoresponder_active') == '1' ? true : false),
						'ul_field' => $email_autoresponder_ul,
						'validation' => array(
							'required' => true,
							'format' => 'number',
						)
					),
					'email_imap' => array(
						'label' => _('Allow IMAP for e-mail accounts'),
						'type' => 'checkbox',
						'values' => array(
										array ('label' => _('Yes'), 'value' => '1')
									),
						'value' => array('1'),
						'mandatory' => true,
						'validation' => array(
							'required' => true,
							'format' => 'boolean',
						)
					),
					'email_pop3' => array(
						'label' => _('Allow POP3 for e-mail accounts'),
						'type' => 'checkbox',
						'values' => array(
										array ('label' => _('Yes'), 'value' => '1')
									),
						'value' => array('1'),
						'mandatory' => true,
						'validation' => array(
							'required' => true,
							'format' => 'boolean',
						)
					),
					'ftps' => array(
						'label' => _('FTP accounts'),
						'type' => 'textul',
						'value' => 0,
						'maxlength' => 9,
						'ul_field' => $ftps_ul,
						'validation' => array(
							'required' => true,
							'format' => 'number',
						)
					),
					'tickets' => array(
						'label' => _('Support tickets'),
						'type' => 'textul',
						'value' => 0,
						'maxlength' => 9,
						'visible' => (getSetting('ticket', 'enabled') == '1' ? true : false),
						'ul_field' => $tickets_ul,
						'validation' => array(
							'required' => true,
							'format' => 'number',
						)
					),
					'mysqls' => array(
						'label' => _('MySQL databases'),
						'type' => 'textul',
						'value' => 0,
						'maxlength' => 9,
						'mandatory' => true,
						'ul_field' => $mysqls_ul,
						'validation' => array(
							'required' => true,
							'format' => 'number',
						)
					),
					'aps_packages' => array(
						'label' => _('APS installations'),
						'type' => 'textul',
						'value' => 0,
						'maxlength' => 9,
						'visible' => (getSetting('aps', 'aps_active') == '1' ? true : false),
						'ul_field' => $aps_packages_ul,
						'validation' => array(
							'required' => true,
							'format' => 'number',
						)
					),
					'phpenabled' => array(
						'label' => _('PHP enabled'),
						'type' => 'checkbox',
						'values' => array(
										array ('label' => _('Yes'), 'value' => '1')
									),
						'value' => array('1'),
						'validation' => array(
							'required' => false,
							'format' => 'boolean',
						)
					),
					'perlenabled' => array(
						'label' => _('Perl enabled'),
						'type' => 'checkbox',
						'values' => array(
										array ('label' => _('Yes'), 'value' => '1')
									),
						'validation' => array(
							'required' => false,
							'format' => 'boolean',
						)
					),
					'backup_allowed' => array(
						'label' => _('Backup allowed'),
						'type' => 'checkbox',
						'values' => array(
										array ('label' => _('Yes'), 'value' => '1')
									),
						'value' => array('0'),
						'validation' => array(
							'required' => false,
							'format' => 'boolean',
						)
					)
				)
			)
		)
	)
);
