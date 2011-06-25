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
	'customer_edit' => array(
		'title' => _('Edit customer'),
		'image' => 'icons/user_edit.png',
		'sections' => array(
			'section_a' => array(
				'title' => _('Account data'),
				'image' => 'icons/user_edit.png',
				'fields' => array(
					'loginname' => array(
						'label' => _('Username'),
						'type' => 'label',
						'value' => $result['loginname']
					),
					'documentroot' => array(
						'label' => _('Documentroot'),
						'type' => 'label',
						'value' => $result['documentroot']
					),
					'createstdsubdomain' => array(
						'label' => _('Create standard subdomain'),
						'type' => 'checkbox',
						'values' => array(
										array ('label' => _('Yes'), 'value' => '1')
									),
						'value' => array(($result['standardsubdomain'] != '0') ? '1' : '0')
					),
					'deactivated' => array(
						'label' => $lng['admin']['deactivated_user'],
						'type' => 'checkbox',
						'values' => array(
										array ('label' => _('Yes'), 'value' => '1')
									),
						'value' => array($result['deactivated'])
					),
					'new_customer_password' => array(
						'label' => _('Password').'&nbsp;('._('empty for no changes').')',
						'type' => 'password'
					),
					'new_customer_password_suggestion' => array(
						'label' => _('Password suggestion'),
						'type' => 'text',
						'value' => generatePassword(),
					),
					'def_language' => array(
						'label' => _('Language'),
						'type' => 'select',
						'select_var' => $language_options
					),
					'countrycode' => array(
						'label' => _('Country'),
						'type' => 'select',
						'select_var' => $countrycode
					)
				)
			),
			'section_b' => array(
				'title' => _('Contact data'),
				'image' => 'icons/user_edit.png',
				'fields' => array(
					'name' => array(
						'label' => _('Name'),
						'type' => 'text',
						'mandatory_ex' => true,
						'value' => $result['name']
					),
					'firstname' => array(
						'label' => _('Firstname'),
						'type' => 'text',
						'mandatory_ex' => true,
						'value' => $result['firstname']
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
						'value' => $result['company']
					),
					'street' => array(
						'label' => _('Street'),
						'type' => 'text',
						'value' => $result['street']
					),
					'zipcode' => array(
						'label' => _('Zipcode'),
						'type' => 'text',
						'value' => $result['zipcode']
					),
					'city' => array(
						'label' => _('City'),
						'type' => 'text',
						'value' => $result['city']
					),
					'phone' => array(
						'label' => _('Phone'),
						'type' => 'text',
						'value' => $result['phone']
					),
					'fax' => array(
						'label' => _('Fax'),
						'type' => 'text',
						'value' => $result['fax']
					),
					'email' => array(
						'label' => _('E-mail'),
						'type' => 'text',
						'mandatory' => true,
						'value' => $result['email']
					),
					'customernumber' => array(
						'label' => _('Customer number'),
						'type' => 'text',
						'value' => $result['customernumber']
					)
				)
			),
			'section_c' => array(
				'title' => _('Service data'),
				'image' => 'icons/user_edit.png',
				'fields' => array(
					'diskspace' => array(
						'label' => _('Webspace'),
						'type' => 'textul',
						'value' => $result['diskspace'],
						'maxlength' => 6,
						'mandatory' => true,
						'ul_field' => $diskspace_ul
					),
					'traffic' => array(
						'label' => _('Traffic'),
						'type' => 'textul',
						'value' => $result['traffic'],
						'maxlength' => 4,
						'mandatory' => true,
						'ul_field' => $traffic_ul
					),
					'subdomains' => array(
						'label' => _('Subdomains'),
						'type' => 'textul',
						'value' => $result['subdomains'],
						'maxlength' => 9,
						'mandatory' => true,
						'ul_field' => $subdomains_ul
					),
					'emails' => array(
						'label' => _('E-mail addresses'),
						'type' => 'textul',
						'value' => $result['emails'],
						'maxlength' => 9,
						'mandatory' => true,
						'ul_field' => $emails_ul
					),
					'email_accounts' => array(
						'label' => _('E-mail accounts'),
						'type' => 'textul',
						'value' => $result['email_accounts'],
						'maxlength' => 9,
						'mandatory' => true,
						'ul_field' => $email_accounts_ul
					),
					'email_forwarders' => array(
						'label' => _('E-mail forwarders'),
						'type' => 'textul',
						'value' => $result['email_forwarders'],
						'maxlength' => 9,
						'mandatory' => true,
						'ul_field' => $email_forwarders_ul
					),
					'email_quota' => array(
						'label' => _('E-mail quota'),
						'type' => 'textul',
						'value' => $result['email_quota'],
						'maxlength' => 9,
						'visible' => (getSetting('system', 'mail_quota_enabled') == '1' ? true : false),
						'mandatory' => true,
						'ul_field' => $email_quota_ul
					),
					'email_autoresponder' => array(
						'label' => _('E-mail autoresponder'),
						'type' => 'textul',
						'value' => $result['email_autoresponder'],
						'maxlength' => 9,
						'visible' => (getSetting('autoresponder', 'autoresponder_active') == '1' ? true : false),
						'ul_field' => $email_autoresponder_ul
					),
					'email_imap' => array(
						'label' => _('Allow IMAP for e-mail accounts'),
						'type' => 'checkbox',
						'values' => array(
										array ('label' => _('Yes'), 'value' => '1')
									),
						'value' => array($result['imap']),
						'mandatory' => true
					),
					'email_pop3' => array(
						'label' => _('Allow POP3 for e-mail accounts'),
						'type' => 'checkbox',
						'values' => array(
										array ('label' => _('Yes'), 'value' => '1')
									),
						'value' => array($result['pop3']),
						'mandatory' => true
					),
					'ftps' => array(
						'label' => _('FTP accounts'),
						'type' => 'textul',
						'value' => $result['ftps'],
						'maxlength' => 9,
						'ul_field' => $ftps_ul
					),
					'tickets' => array(
						'label' => _('Support tickets'),
						'type' => 'textul',
						'value' => $result['tickets'],
						'maxlength' => 9,
						'visible' => ($settings['ticket']['enabled'] == '1' ? true : false),
						'ul_field' => $tickets_ul
					),
					'mysqls' => array(
						'label' => _('MySQL databases'),
						'type' => 'textul',
						'value' => $result['mysqls'],
						'maxlength' => 9,
						'mandatory' => true,
						'ul_field' => $mysqls_ul
					),
					'phpenabled' => array(
						'label' => _('PHP enabled'),
						'type' => 'checkbox',
						'values' => array(
										array ('label' => _('Yes'), 'value' => '1')
									),
						'value' => array($result['phpenabled'])
					),
					'perlenabled' => array(
						'label' => _('Perl enabled'),
						'type' => 'checkbox',
						'values' => array(
										array ('label' => _('Yes'), 'value' => '1')
									),
						'value' => array($result['perlenabled'])
					),
					'backup_allowed' => array(
						'label' => _('Backup allowed'),
						'type' => 'checkbox',
						'values' => array(
										array ('label' => _('Yes'), 'value' => '1')
									),
						'value' => array($result['backup_allowed'])
					),
					'aps_packages' => array(
						'label' => _('APS installations'),
						'type' => 'textul',
						'value' => $result['aps_packages'],
						'maxlength' => 9,
						'visible' => ($getSetting('aps', 'aps_active') == '1' ? true : false),
						'ul_field' => $aps_packages_ul
					)
				)
			)
		)
	)
);
