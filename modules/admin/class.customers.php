<?php
/**
 * This file is part of the Froxlor project.
 * Copyright (c) 2011 the Froxlor Team (see authors).
 *
 * For the full copyright and license information, please view the COPYING
 * file that was distributed with this source code. You can also view the
 * COPYING file online at http://files.froxlor.org/misc/COPYING.txt
 *
 * @copyright  (c) the authors
 * @author     Froxlor team <team@froxlor.org> (2011-)
 * @license    GPLv2 http://files.froxlor.org/misc/COPYING.txt
 * @package    Classes
 * @subpackage Admininterface
 */

/**
 * adminCustomers - Management of customers
 */
class adminCustomers
{
	/**
	 * index
	 *
	 * @return string The complete rendered body
	 */
	public function index()
	{
		#Froxlor::getLog()->logAction(ADM_ACTION, LOG_NOTICE, "viewed customers/index");
		// clear request data
		unset($_SESSION['requestData'], $_SESSION['formerror']);

		$where = '';
		if (Froxlor::getUser()->getData('resources', 'customers_see_all'))
		{
			$where = " AND `u2a`.`userid` = `u`.`id` AND `u2a`.`adminid` = '" . Froxlor::getUser()->getId() . "'";
		}
		$result = Froxlor::getDb()->query("SELECT `u`.*, `r`.* FROM `users` AS u, `user_resources` AS r, `user2admin` AS u2a WHERE `u`.`id` = `r`.`id`" . $where);
		$customers = array();
		$maxdisk = 0;
		$maxtraffic = 0;
		while($row = Froxlor::getDb()->fetch_array($result))
		{
			$domains = Froxlor::getDb()->query_first("SELECT COUNT(`id`) AS `domains` " . "FROM `panel_domains` WHERE `customerid`='" . (int)$row['id'] . "' AND `parentdomainid`='0' AND `id`<> '" . (int)$row['standardsubdomain'] . "'");
			$handle = Froxlor::getDb()->query_first("SELECT `h`.* FROM `user_addresses` AS h, `user2handle` AS u2h WHERE `h`.`id` = `u2h`.`handleid` AND `u2h`.`userid` = '" . (int)$row['id'] . "'");
			$admin = Froxlor::getDb()->query_first("SELECT `u`.`loginname` FROM `users` u, `user2admin` u2a WHERE `u`.`id` = `u2a`.`adminid` AND `u2a`.`userid` = '" . (int)$row['id'] . "'");
			$row['domains'] = intval($domains['domains']);
			$row['traffic_used'] = round($row['traffic_used'] / (1024 * 1024), getSetting('panel', 'decimal_places'));
			$row['traffic'] = round($row['traffic'] / (1024 * 1024), getSetting('panel', 'decimal_places'));
			if ($row['traffic'] > $maxtraffic)
			{
				$maxtraffic = $row['traffic'];
			}

			$row['diskspace_used'] = round($row['diskspace_used'] / 1024, getSetting('panel', 'decimal_places'));
			$row['diskspace'] = round($row['diskspace'] / 1024, getSetting('panel', 'decimal_places'));
			if ($row['diskspace'] > $maxdisk)
			{
				$maxdisk = $row['diskspace'];
			}
			$last_login = ((int)$row['lastlogin_succ'] == 0) ? _('No login yet') : date('d.m.Y', $row['lastlogin_succ']);

			/**
			 * percent-values for progressbar
			 */
			if ($row['diskspace'] > 0)
			{
				$percent = round(($row['diskspace_used']*100)/$row['diskspace'], 2);
				$doublepercent = round($percent*2, 2);
			}
			else
			{
				$percent = 0;
				$doublepercent = 0;
			}

			$column_style = '';
			$unlock_link = '';
			if($row['loginfail_count'] >= getSetting('login', 'maxloginattempts')
				&& $row['lastlogin_fail'] > (time() - getSetting('login', 'deactivatetime'))
			) {
				$column_style = ' style="background-color: #f99122;"';
				$unlock_link = '<a href="'.Froxlor::getLinker()->getLink(array('area' => 'admin', 'section' => 'customers', 'action' => 'unlock', 'id' => $row['id'])) . '">'._('Unlock').'</a><br />';

			}
			$customers[] = array('row' => $row, 'column_style' => $column_style, 'unlock_link' => $unlock_link, 'last_login' => $last_login, 'handle' => $handle, 'admin' => $admin, 'doublepercent' => $doublepercent, 'percent' => $percent);
		}
		Froxlor::getSmarty()->assign('customers', $customers);
		Froxlor::getSmarty()->assign('maxdisk', $maxdisk);
		Froxlor::getSmarty()->assign('maxtraffic', $maxtraffic);

		Froxlor::getSmarty()->assign('customercount', Froxlor::getDb()->num_rows($result));
		// Render and return the current page
		return Froxlor::getSmarty()->fetch('admin/customers/index.tpl');
	}

	public function su()
	{
		$id = 0;
		if (isset($_GET['id']))
		{
			$id = (int)$_GET['id'];
		}
		if ($id == 0)
		{
			$_SESSION['errormessage'] = _('You need to submit the customer');
			redirectTo(Froxlor::getLinker()->getLink(array('area' => 'admin', 'section' => 'customers', 'action' => 'index')));
		}
		$result = Froxlor::getDb()->query_first("SELECT `u`.`loginname` FROM `users` u, `user2admin` u2a WHERE `id`='" . $id . "' " . (Froxlor::getUser()->getData('resources', 'customers_see_all') ? '' : " AND `u2a`.`userid` = '" . $id . "' AND `u2a`.`adminid` = '" . Froxlor::getUser()->getId() . "'"));
		$destination_user = $result['loginname'];

		if($destination_user != '')
		{
			$result = Froxlor::getDb()->query_first("SELECT * FROM `panel_sessions` WHERE `userid`='" . (int)Froxlor::getUser()->getId() . "' AND `hash`='" . Froxlor::getDb()->escape(session_id()) . "'");
			$s = md5(uniqid(microtime(), 1));
			Froxlor::getDb()->query("INSERT INTO `panel_sessions` (`hash`, `userid`, `ipaddress`, `useragent`, `lastactivity`, `language`, `adminsession`) VALUES ('" . Froxlor::getDb()->escape($s) . "', '" . (int)$id . "', '" . Froxlor::getDb()->escape($result['ipaddress']) . "', '" . Froxlor::getDb()->escape($result['useragent']) . "', '" . time() . "', '" . Froxlor::getDb()->escape($result['language']) . "', '0')");
			#Froxlor::getLog()->logAction(ADM_ACTION, LOG_INFO, "switched user and is now '" . $destination_user . "'");
			Froxlor::getLinker()->add('s', $s);
			redirectTo(Froxlor::getLinker()->getLink(array('area' => 'customer', 'section' => 'index', 'action' => 'index')));
		}
		$_SESSION['errormessage'] = _('You are either not allowed to incorporate this customer or this customer does not exist');
		redirectTo(Froxlor::getLinker()->getLink(array('area' => 'admin', 'section' => 'customers', 'action' => 'index')));
	}

	public function unlock()
	{
		$id = 0;
		if (isset($_GET['id']))
		{
			$id = (int)$_GET['id'];
		}
		if ($id == 0)
		{
			$_SESSION['errormessage'] = _('You need to submit the customer');
			redirectTo(Froxlor::getLinker()->getLink(array('area' => 'admin', 'section' => 'customers', 'action' => 'index')));
		}
		$result = Froxlor::getDb()->query_first("SELECT `u`.`loginname` FROM `users` u, `user2admin` u2a WHERE `id`='" . (int)$id . "' " . (Froxlor::getUser()->getData('resources', 'customers_see_all') ? '' : " AND `u2a`.`userid` = '" . $id . "' AND `u2a`.`adminid` = '" . Froxlor::getUser()->getId() . "'"));

		if($result['loginname'] != '')
		{
			return ask_yesno(_('Do you really want to unlock customer %s?'), array('area' => 'admin', 'section' => 'customers', 'action' => 'unlock'), array('id' => $id), $result['loginname'] );
		}
		$_SESSION['errormessage'] = _('You are either not allowed to unlock this customer or this customer does not exist');
		redirectTo(Froxlor::getLinker()->getLink(array('area' => 'admin', 'section' => 'customers', 'action' => 'index')));
	}

	public function unlockPost()
	{
		$id = 0;
		if (isset($_POST['id']))
		{
			$id = (int)$_POST['id'];
		}
		$result = Froxlor::getDb()->query_first("SELECT `u`.`loginname` FROM `users` u, `user2admin` u2a WHERE `id`='" . (int)$id . "' " . (Froxlor::getUser()->getData('resources', 'customers_see_all') ? '' : " AND `u2a`.`userid` = '" . $id . "' AND `u2a`.`adminid` = '" . Froxlor::getUser()->getId() . "'"));

		if($result['loginname'] != '')
		{
			$result = Froxlor::getDb()->query("UPDATE `users` SET `loginfail_count` = '0', `lastlogin_fail` = '0' WHERE `id`= '" . (int)$id . "'");
			$_SESSION['successmessage'] = sprintf(_('User %s successfully unlocked'), $result['loginname']);
			redirectTo(Froxlor::getLinker()->getLink(array('area' => 'admin', 'section' => 'customers', 'action' => 'index')));
		}
		$_SESSION['errormessage'] = _('You are either not allowed to unlock this customer or this customer does not exist');
		redirectTo(Froxlor::getLinker()->getLink(array('area' => 'admin', 'section' => 'customers', 'action' => 'index')));
	}

	public function add()
	{
		if(Froxlor::getUser()->getData('resources', 'customers_used') >= Froxlor::getUser()->getData('resources', 'customers') && Froxlor::getUser()->getData('resources', 'customers') != '-1')
		{
			$_SESSION['errormessage'] = sprintf(_('You may not add more than %s customers'), Froxlor::getUser()->getData('resources', 'customers'));
			redirectTo(Froxlor::getLinker()->getLink(array('area' => 'admin', 'section' => 'customers', 'action' => 'index')));
		}

		$language_options = '';
		$languages = Froxlor::getLanguage()->getWorkingLanguages();

		while(list($language_file, $language_name) = each($languages))
		{
			$language_options.= makeoption('def_language', $language_name, $language_file, getSetting('panel', 'standardlanguage'), true);
		}

		$countrycode = countrycode::get(true, 'countrycode');

		$diskspace_ul = makecheckbox('diskspace_ul', _('Unlimited'), '-1', false, '0', true, true);
		$traffic_ul = makecheckbox('traffic_ul', _('Unlimited'), '-1', false, '0', true, true);
		$subdomains_ul = makecheckbox('subdomains_ul', _('Unlimited'), '-1', false, '0', true, true);
		$emails_ul = makecheckbox('emails_ul', _('Unlimited'), '-1', false, '0', true, true);
		$email_accounts_ul = makecheckbox('email_accounts_ul', _('Unlimited'), '-1', false, '0', true, true);
		$email_forwarders_ul = makecheckbox('email_forwarders_ul', _('Unlimited'), '-1', false, '0', true, true);
		$email_quota_ul = makecheckbox('email_quota_ul', _('Unlimited'), '-1', false, '0', true, true);
		$email_autoresponder_ul = makecheckbox('email_autoresponder_ul', _('Unlimited'), '-1', false, '0', true, true);
		$ftps_ul = makecheckbox('ftps_ul', _('Unlimited'), '-1', false, '0', true, true);
		$tickets_ul = makecheckbox('tickets_ul', _('Unlimited'), '-1', false, '0', true, true);
		$mysqls_ul = makecheckbox('mysqls_ul', _('Unlimited'), '-1', false, '0', true, true);
		$aps_packages_ul = makecheckbox('aps_packages_ul', _('Unlimited'), '-1', false, '0', true, true);

		$gender_options = makeoption('gender', '', 0, true, true, true);
		$gender_options .= makeoption('gender', _('Male'), 1, null, true, true);
		$gender_options .= makeoption('gender', _('Female'), 2, null, true, true);

		$customer_add_data = include_once dirname(__FILE__).'/../../lib/formfields/admin/customer/formfield.customer_add.php';
		$customer_add_form = htmlform::genHTMLForm($customer_add_data);
		unset($_SESSION['requestData'], $_SESSION['formerror']);

		Froxlor::getSmarty()->assign('title', $customer_add_data['customer_add']['title']);
		Froxlor::getSmarty()->assign('image', $customer_add_data['customer_add']['image']);
		Froxlor::getSmarty()->assign('customer_add_form', $customer_add_form);

		return Froxlor::getSmarty()->fetch('admin/customers/customers_add.tpl');
	}

	public function addPost()
	{
		if(Froxlor::getUser()->getData('resources', 'customers_used') >= Froxlor::getUser()->getData('resources', 'customers') && Froxlor::getUser()->getData('resources', 'customers') != '-1')
		{
			$_SESSION['errormessage'] = sprintf(_('You may not add more than %s customers'), Froxlor::getUser()->getData('resources', 'customers'));
			redirectTo(Froxlor::getLinker()->getLink(array('area' => 'admin', 'section' => 'customers', 'action' => 'index')));
		}

		$_SESSION['requestData'] = $_POST;

		$returnto = array('area' => 'admin', 'section' => 'customers', 'action' => 'add');

		// We need to hide errors in the include_once since there are many undefined variables in the formfield, but these not required for validation
		$form = @include_once dirname(__FILE__) . '/../../lib/formfields/admin/customer/formfield.customer_add.php';
		$validation = validateForm::validate($_POST, $form['customer_add']);
		if (count($validation['failed']) > 0)
		{
			$_SESSION['errormessage'] = '';
			foreach ($validation['failed'] as $error)
			{
				$label = $error['label'];
				unset($error['label']);
				$error = join("<br />$label: ", $error);
				$_SESSION['errormessage'] .= "$label: $error<br />";
			}
			$_SESSION['formerror'] = $validation['failed'];
			redirectTo(Froxlor::getLinker()->getLink(array('area' => 'admin', 'section' => 'customers', 'action' => 'add')));
		}

		foreach(array('name', 'firstname', 'company', 'street', 'zipcode', 'city', 'phone', 'fax', 'customernumber', 'def_language', 'gender') as $fieldname)
		{
			$$fieldname = $validation['safe'][$fieldname];
		}
		$idna_convert = new idna_convert();
		$email = $idna_convert->encode($validation['safe']['email']);

		$ccode = $validation['safe']['countrycode'];

		foreach(array('diskspace', 'traffic', 'subdomains', 'emails', 'email_accounts', 'email_forwarders', 'email_quota', 'email_autoresponder', 'ftps', 'tickets', 'mysqls', 'aps_packages') as $type)
		{
			$check = 1;
			if ($type == 'email_quota' && getSetting('systems', 'mail_quota_enabled') != 1)
			{
				$check = 0;
			}
			if ($type == 'email_autoresponder' && getSetting('systems', 'autoresponder_active') != 1)
			{
				$check = 0;
			}
			if ($type == 'tickets' && getSetting('ticket', 'enabled') != 1)
			{
				$check = 0;
			}
			if ($type == 'aps_packages' && getSetting('aps', 'aps_active') != 1)
			{
				$check = 0;
			}

			if ($check)
			{
				$$type = $validation['safe'][$type];
				if (isset($validation['safe'][$type . '_ul']))
				{
					$$type = -1;
				}
			}
			else
			{
				$$type = ($$type == 'email_quota' ? -1 : 0);
			}
		}

		foreach(array('createstdsubdomain', 'deactivated', 'email_imap', 'email_pop3', 'backup_allowed', 'phpenabled', 'perlenabled', 'sendpassword', 'store_defaultindex') as $type)
		{
			$$type = 0;
			if (isset($validation['safe'][$type]))
			{
				$$type = (int)$validation['safe'][$type];
			}
			if ($$type != 1)
			{
				$$type = 0;
			}
		}

		$password = $validation['safe']['new_customer_password'];
		// only check if not empty,
		// cause empty == generate password automatically

		// gender out of range? [0,2]
		if ($gender < 0 || $gender > 2) {
			$gender = 0;
		}

		foreach(array('diskspace', 'mysqls', 'emails', 'email_accounts', 'email_forwarders', 'email_quota', 'email_autoresponder', 'ftps', 'tickets', 'subdomains', 'aps_packages') as $type)
		{
			$ok = 1;
			if (((Froxlor::getUser()->getData('resources', $type . '_used') + $$type) > Froxlor::getUser()->getData('resources', $type))
				 && Froxlor::getUser()->getData('resources', $type) != '-1')
			{
				$ok = 0;
				// Mailquota would be wrong, but since the mailquota - system is disabled, it doesn't matter
				if ($type == 'email_quota' && getSetting('system', 'mail_quota_enabled') == 0)
				{
					$ok = 1;
				}

				// Autoresponder would be wrong, but since the autoresponder - system is disabled, it doesn't matter
				if ($type == 'email_autoresponder' && getSetting('autoresponder', 'autoresponder_active') == 0)
				{
					$ok = 1;
				}

				// APS would be wrong, but since the APS - system is disabled, it doesn't matter
				if ($type == 'aps_packages' && getSetting('aps', 'aps_active') == 0)
				{
					$ok = 1;
				}

			}

			if ($$type < '-1')
			{
				$ok = 0;
			}

			if (!$ok)
			{
				$_SESSION['errormessage'] = sprintf(_('You may not allocate more resources for the resource \'%s\' than you have'), $type);
				redirectTo(Froxlor::getLinker()->getLink($returnto));
			}
		}

		// Either $name and $firstname or the $company must be inserted
		if($name == '' && $company == '')
		{
			$_SESSION['errormessage'] = sprintf(_('Missing input in field \'%s\''), _('Name'));
			redirectTo(Froxlor::getLinker()->getLink($returnto));
		}
		elseif($firstname == '' && $company == '')
		{
			$_SESSION['errormessage'] = sprintf(_('Missing input in field \'%s\''), _('Firstname'));
			redirectTo(Froxlor::getLinker()->getLink($returnto));
		}
		elseif($email == '')
		{
			$_SESSION['errormessage'] = sprintf(_('Missing input in field \'%s\''), _('E-mail'));
			redirectTo(Froxlor::getLinker()->getLink($returnto));
		}
		elseif(!validateEmail($email))
		{
			$_SESSION['errormessage'] = sprintf(_('The entered e-mail address \'%s\' is wrong'), htmlspecialchars($email));
			redirectTo(Froxlor::getLinker()->getLink($returnto));
		}
		else
		{
			if(isset($validation['safe']['new_loginname'])
			   && $validation['safe']['new_loginname'] != '')
			{
				$accountnumber = intval(getSetting('system', 'lastaccountnumber'));

				// Accounts which match systemaccounts are not allowed, filtering them

				if(preg_match('/^' . preg_quote(getSetting('customer', 'accountprefix'), '/') . '([0-9]+)/', $loginname))
				{
					$_SESSION['errormessage'] = sprintf(_('You cannot create accounts which are similar to system accounts (as for example begin with \'%s\'). Please enter another account name.'), getSetting('customer', 'accountprefix'));
					redirectTo(Froxlor::getLinker()->getLink($returnto));
				}
			}
			else
			{
				$accountnumber = intval(getSetting('system', 'lastaccountnumber') + 1);
				$loginname = getSetting('customer', 'accountprefix') . $accountnumber;
			}

			// Check if the account already exists
			$loginname_check = Froxlor::getDb()->query_first("SELECT `loginname` FROM `users` WHERE `loginname` = '" . Froxlor::getDb()->escape($loginname) . "'");

			if(strtolower($loginname_check['loginname']) == strtolower($loginname))
			{
				$_SESSION['errormessage'] = sprintf(_('An account called \'%s\' already exists'), $loginname);
				redirectTo(Froxlor::getLinker()->getLink($returnto));
			}
			elseif(!validateUsername($loginname, getSetting('panel', 'unix_names'), 14 - strlen(getSetting('customer', 'mysqlprefix'))))
			{
				$_SESSION['errormessage'] = sprintf(_('The chosen account name \'%s\' contains invalid characters'), $loginname);
				redirectTo(Froxlor::getLinker()->getLink($returnto));
			}

			$guid = intval(getSetting('system', 'lastguid')) + 1;
			$documentroot = makeCorrectDir(getSetting('system', 'documentroot_prefix') . '/' . $loginname);

			if(file_exists($documentroot))
			{
				$_SESSION['errormessage'] = sprintf(_('The directory \'%s\' already exists for this customer. Please remove this before adding the customer again.'), $documentroot);
				redirectTo(Froxlor::getLinker()->getLink($returnto));
			}

			if($createstdsubdomain != '1')
			{
				$createstdsubdomain = '0';
			}

			if($phpenabled != '0')
			{
				$phpenabled = '1';
			}

			if($perlenabled != '0')
			{
				$perlenabled = '1';
			}

			if($password == '')
			{
				$password = substr(md5(uniqid(microtime(), 1)), 12, 6);
			}

			$newuserdata = array();
			$newuserdata['general']['loginname'] = $loginname;
			$newuserdata['general']['password'] = md5($password);
			$newuserdata['general']['isadmin'] = 0;
			$newuserdata['general']['def_language'] = $def_language;
			$newuserdata['general']['guid'] = $guid;
			$newuserdata['general']['theme'] = getSetting('panel', 'default_theme');

			foreach(array('diskspace', 'traffic', 'subdomains', 'emails', 'email_accounts', 'email_forwarders', 'email_quota', 'email_autoresponder', 'ftps', 'tickets', 'mysqls', 'aps_packages', 'documentroot', 'perlenabled', 'phpenabled','backup_allowed') as $type)
			{
				$newuserdata['resources'][$type] = $$type;
			}
			$newuserdata['resources']['imap'] = $email_imap;
			$newuserdata['resources']['pop3'] = $email_pop3;

			foreach(array('name', 'firstname', 'gender', 'company', 'street', 'zipcode', 'city', 'phone', 'fax', 'email') as $type)
			{
				$newuserdata['address'][$type] = $$type;
			}

			try
			{
				$newuser = new user();
				$newuser->createNewUser($newuserdata);
			}
			catch (Exception $e)
			{
				$_SESSION['errormessage'] = sprintf(_('The customer \'%1$s\' could not be created: %2$s'), htmlspecialchars($loginname), htmlspecialchars($e->getMessage()));
				redirectTo(Froxlor::getLinker()->getLink($returnto));
			}

			$result = Froxlor::getDb()->query("INSERT INTO `user2admin` SET `adminid` = '" . Froxlor::getUser()->getId() . "', `userid` = '" . $newuser->getId() . "'");

			Froxlor::getUser()->setData('resources', 'customers_used', Froxlor::getUser()->getData('resources', 'customers_used') + 1);
			foreach (array('diskspace', 'traffic', 'subdomains', 'emails', 'email_accounts', 'email_forwarders', 'email_quota', 'email_autoresponder', 'ftps', 'tickets', 'mysqls', 'aps_packages') as $type)
			{
				$update = 1;
				if ($type == 'tickets' && getSetting('ticket', 'enabled') != 1)
				{
					$update = 0;
				}
				elseif($type == 'email_autoresponder' && getSetting('autoresponder', 'autoresponder_active') != 1)
				{
					$update = 0;
				}
				if ($update && $$type != -1)
				{
					Froxlor::getUser()->setData('resources', $type . '_used', Froxlor::getUser()->getData('resources', $type . '_used') + (int)$$type);
				}
			}

			Froxlor::getDb()->query("UPDATE `panel_settings` SET `value`='" . Froxlor::getDb()->escape($guid) . "' " . "WHERE `settinggroup`='system' AND `varname`='lastguid'");

			if($accountnumber != intval(getSetting('system', 'lastaccountnumber')))
			{
				Froxlor::getDb()->query("UPDATE `panel_settings` SET `value`='" . Froxlor::getDb()->escape($accountnumber) . "' " . "WHERE `settinggroup`='system' AND `varname`='lastaccountnumber'");
			}

			#Froxlor::getLog()->logAction(ADM_ACTION, LOG_INFO, "added user '" . $loginname . "'");
			inserttask('2', $loginname, $guid, $guid, $store_defaultindex);

			# Using filesystem - quota, insert a task which cleans the filesystem - quota
			if (getSetting('system', 'diskquota_enabled'))
			{
				inserttask('10');
			}
			// Add htpasswd for the webalizer stats

			if(CRYPT_STD_DES == 1)
			{
				$saltfordescrypt = substr(md5(uniqid(microtime(), 1)), 4, 2);
				$htpasswdPassword = crypt($password, $saltfordescrypt);
			}
			else
			{
				$htpasswdPassword = crypt($password);
			}

			if(getSetting('system', 'awstats_enabled') == '1')
			{
				Froxlor::getDb()->query("INSERT INTO `" . TABLE_PANEL_HTPASSWDS . "` " . "(`customerid`, `username`, `password`, `path`) " . "VALUES ('" . (int)$newuser->getId() . "', '" . Froxlor::getDb()->escape($loginname) . "', '" . Froxlor::getDb()->escape($htpasswdPassword) . "', '" .Froxlor::getDb() ->escape(makeCorrectDir($documentroot . '/awstats/')) . "')");
				#Froxlor::getLog()->logAction(ADM_ACTION, LOG_NOTICE, "automatically added awstats htpasswd for user '" . $loginname . "'");
			}
			else
			{
				Froxlor::getDb()->query("INSERT INTO `" . TABLE_PANEL_HTPASSWDS . "` " . "(`customerid`, `username`, `password`, `path`) " . "VALUES ('" . (int)$newuser->getId() . "', '" . Froxlor::getDb()->escape($loginname) . "', '" . Froxlor::getDb()->escape($htpasswdPassword) . "', '" . Froxlor::getDb()->escape(makeCorrectDir($documentroot . '/webalizer/')) . "')");
				#Froxlor::getLog()->logAction(ADM_ACTION, LOG_NOTICE, "automatically added webalizer htpasswd for user '" . $loginname . "'");
			}

			inserttask('1');
			$result = Froxlor::getDb()->query("INSERT INTO `" . TABLE_FTP_USERS . "` " . "(`customerid`, `username`, `password`, `homedir`, `login_enabled`, `uid`, `gid`) " . "VALUES ('" . (int)$newuser->getId() . "', '" . Froxlor::getDb()->escape($loginname) . "', ENCRYPT('" . Froxlor::getDb()->escape($password) . "'), '" . Froxlor::getDb()->escape($documentroot) . "', 'y', '" . (int)$guid . "', '" . (int)$guid . "')");
			$result = Froxlor::getDb()->query("INSERT INTO `" . TABLE_FTP_GROUPS . "` " . "(`customerid`, `groupname`, `gid`, `members`) " . "VALUES ('" . (int)$newuser->getId() . "', '" . Froxlor::getDb()->escape($loginname) . "', '" . Froxlor::getDb()->escape($guid) . "', '" . Froxlor::getDb()->escape($loginname) . "')");
			$result = Froxlor::getDb()->query("INSERT INTO `" . TABLE_FTP_QUOTATALLIES . "` (`name`, `quota_type`, `bytes_in_used`, `bytes_out_used`, `bytes_xfer_used`, `files_in_used`, `files_out_used`, `files_xfer_used`) VALUES ('" . Froxlor::getDb()->escape($loginname) . "', 'user', '0', '0', '0', '0', '0', '0')");
			#Froxlor::getLog()->logAction(ADM_ACTION, LOG_NOTICE, "automatically added ftp-account for user '" . $loginname . "'");

			if($createstdsubdomain == '1')
			{
				if (getSetting('system', 'stdsubdomain') != '')
				{
					$_stdsubdomain = $loginname . '.' . getSetting('system', 'stdsubdomain');
				}
				else
				{
					$_stdsubdomain = $loginname . '.' . getSetting('system', 'hostname');
				}

				Froxlor::getDb()->query("INSERT INTO `" . TABLE_PANEL_DOMAINS . "` SET " .
					"`domain` = '". Froxlor::getDb()->escape($_stdsubdomain) . "', " .
					"`customerid` = '" . (int)$newuser->getId() . "', " .
					"`adminid` = '" . (int)Froxlor::getUser()->getId() . "', " .
					"`parentdomainid` = '-1', " .
					"`ipandport` = '" . Froxlor::getDb()->escape(getSetting('system', 'defaultip')) . "', " .
					"`documentroot` = '" . Froxlor::getDb()->escape($documentroot) . "', " .
					"`zonefile` = '', " .
					"`isemaildomain` = '0', " .
					"`caneditdomain` = '0', " .
					"`openbasedir` = '1', " .
					"`safemode` = '1', " .
					"`speciallogfile` = '0', " .
					"`specialsettings` = '', " .
					"`add_date` = '".date('Y-m-d')."'");
				$domainid = Froxlor::getDb()->insert_id();
				Froxlor::getDb()->query('UPDATE `' . TABLE_PANEL_CUSTOMERS . '` SET `standardsubdomain`=\'' . (int)$domainid . '\' WHERE `customerid`=\'' . (int)$newuser->getId() . '\'');
				#Froxlor::getLog()->logAction(ADM_ACTION, LOG_NOTICE, "automatically added standardsubdomain for user '" . $loginname . "'");
				inserttask('1');
			}

			$_SESSION['successmessage'] = sprintf(_('User \'%s\' successfully created'), $loginname);
			if($sendpassword == '1')
			{
				$replace_arr = array(
					'FIRSTNAME' => $firstname,
					'NAME' => $name,
					'COMPANY' => $company,
					'SALUTATION' => getCorrectUserSalutation(array('firstname' => $firstname, 'name' => $name, 'company' => $company)),
					'USERNAME' => $loginname,
					'PASSWORD' => $password
				);

				// Get mail templates from database; the ones from 'admin' are fetched for fallback

				$result = Froxlor::getDb()->query_first('SELECT `value` FROM `' . TABLE_PANEL_TEMPLATES . '` WHERE `adminid`=\'' . (int)Froxlor::getUser()->getId() . '\' AND `language`=\'' . Froxlor::getDb()->escape($def_language) . '\' AND `templategroup`=\'mails\' AND `varname`=\'createcustomer_subject\'');
				$mail_subject = html_entity_decode(replace_variables((($result['value'] != '') ? $result['value'] : $lng['mails']['createcustomer']['subject']), $replace_arr));
				$result = Froxlor::getDb()->query_first('SELECT `value` FROM `' . TABLE_PANEL_TEMPLATES . '` WHERE `adminid`=\'' . (int)Froxlor::getUser()->getId() . '\' AND `language`=\'' . Froxlor::getDb()->escape($def_language) . '\' AND `templategroup`=\'mails\' AND `varname`=\'createcustomer_mailbody\'');
				$mail_body = html_entity_decode(replace_variables((($result['value'] != '') ? $result['value'] : $lng['mails']['createcustomer']['mailbody']), $replace_arr));

				$_mailerror = false;
				try {
					Froxlor::getMail()->Subject = $mail_subject;
					Froxlor::getMail()->AltBody = $mail_body;
					Froxlor::getMail()->MsgHTML(str_replace("\n", "<br />", $mail_body));
					Froxlor::getMail()->AddAddress($email, getCorrectUserSalutation(array('firstname' => $firstname, 'name' => $name, 'company' => $company)));
					Froxlor::getMail()->Send();
				} catch(phpmailerException $e) {
					$mailerr_msg = $e->errorMessage();
					$_mailerror = true;
				} catch (Exception $e) {
					$mailerr_msg = $e->getMessage();
					$_mailerror = true;
				}

				if ($_mailerror) {
					#Froxlor::getLog()->logAction(ADM_ACTION, LOG_ERR, "Error sending mail: " . $mailerr_msg);
					$_SESSION['errormessage'] = sprintf(_('Welcome - e-mail could not be sent for customer \'%s\''), $loginname);
					redirectTo(Froxlor::getLinker()->getLink(array('area' => 'admin', 'section' => 'customers', 'action' => 'index')));
				}

				Froxlor::getMail()->ClearAddresses();
				#Froxlor::getLog()->logAction(ADM_ACTION, LOG_NOTICE, "automatically sent password to user '" . $loginname . "'");
			}
			redirectTo(Froxlor::getLinker()->getLink(array('area' => 'admin', 'section' => 'customers', 'action' => 'index')));
		}
	}

	public function edit()
	{
		$id = 0;
		if (isset($_GET['id']))
		{
			$id = (int)$_GET['id'];
		}
		if ($id == 0)
		{
			$_SESSION['errormessage'] = _('You need to submit the customer');
			redirectTo(Froxlor::getLinker()->getLink(array('area' => 'admin', 'section' => 'customers', 'action' => 'index')));
		}

		if (Froxlor::getUser()->getData('resources', 'customers_see_all') != 1)
		{
			$sql = "SELECT `userid` FROM `user2admin` WHERE `userid` = '" . $id . "' AND `adminid` = '" . Froxlor::getUser()->getId() . "'";
			$result = Froxlor::getDb()->query_first($sql);
			if (!isset($result['userid']) || $result['userid'] != $id)
			{
				$_SESSION['errormessage'] = _('You don\'t have the permission to edit this customer');
				redirectTo(Froxlor::getLinker()->getLink(array('area' => 'admin', 'section' => 'customers', 'action' => 'index')));
			}
		}
		try
		{
			$user = new user($id);
		}
		catch (Exception $e)
		{
			$_SESSION['errormessage'] = _('The chosen customer does not exist');
			redirectTo(Froxlor::getLinker()->getLink(array('area' => 'admin', 'section' => 'customers', 'action' => 'index')));
		}

		Froxlor::getSmarty()->assign('id', $id);

		$language_options = '';
		$languages = Froxlor::getLanguage()->getWorkingLanguages();

		while(list($language_file, $language_name) = each($languages))
		{
			$language_options.= makeoption($language_name, $language_file, $user->getData('general', 'def_language'), true);
		}
		Froxlor::getSmarty()->assign('language_options', $language_options);
		$idna_convert = new idna_convert();

		foreach(array('diskspace', 'traffic', 'subdomains', 'emails', 'email_accounts', 'email_forwarders', 'email_quota', 'email_autoresponder', 'ftps', 'tickets', 'mysqls', 'aps_packages') as $type)
		{
			${$type . '_ul'} = makecheckbox($type . '_ul', _('Unlimited'), '-1', false, $user->getData('resources', $type), true, true);
		}
		$gender_options = makeoption('title', '', 0, ($user->getData('address', 'gender') == '0' ? true : false), true, true);
		$gender_options .= makeoption('title', _('Male'), 1, ($user->getData('address', 'gender') == '1' ? true : false), true, true);
		$gender_options .= makeoption('title', _('Female'), 2, ($user->getData('address', 'gender') == '2' ? true : false), true, true);

		$countrycode = countrycode::get(true, 'countrycode');

		$customer_edit_data = include_once dirname(__FILE__).'/../../lib/formfields/admin/customer/formfield.customer_edit.php';
		Froxlor::getSmarty()->assign('customer_edit_form', htmlform::genHTMLForm($customer_edit_data));

		Froxlor::getSmarty()->assign('title', $customer_edit_data['customer_edit']['title']);
		Froxlor::getSmarty()->assign('image', $customer_edit_data['customer_edit']['image']);

		return Froxlor::getSmarty()->fetch('admin/customers/customers_edit.tpl');
	}


	public function editPost()
	{

		$id = 0;
		if (isset($_POST['id']))
		{
			$id = (int)$_POST['id'];
		}
		if ($id == 0)
		{
			$_SESSION['errormessage'] = _('You need to submit the customer');
			redirectTo(Froxlor::getLinker()->getLink(array('area' => 'admin', 'section' => 'customers', 'action' => 'index')));
		}


		if (Froxlor::getUser()->getData('resources', 'customers_see_all') != 1)
		{
			$sql = "SELECT `userid` FROM `user2admin` WHERE `userid` = '" . $id . "' AND `adminid` = '" . Froxlor::getUser()->getId() . "'";
			$result = Froxlor::getDb()->query_first($sql);
			if (!isset($result['userid']) || $result['userid'] != $id)
			{
				$_SESSION['errormessage'] = _('You don\'t have the permission to edit this customer');
				redirectTo(Froxlor::getLinker()->getLink(array('area' => 'admin', 'section' => 'customers', 'action' => 'index')));
			}
		}
		try
		{
			$user = new user($id);
		}
		catch (Exception $e)
		{
			$_SESSION['errormessage'] = _('The chosen customer does not exist');
			redirectTo(Froxlor::getLinker()->getLink(array('area' => 'admin', 'section' => 'customers', 'action' => 'index')));
		}

		$_SESSION['requestData'] = $_POST;

		$returnto = array('area' => 'admin', 'section' => 'customers', 'action' => 'edit', 'id' => $id);

		Froxlor::getSmarty()->assign('id', $id);

		// We need to hide errors in the include_once since there are many undefined variables in the formfield, but these not required for validation
		$form = @include_once dirname(__FILE__) . '/../../lib/formfields/admin/customer/formfield.customer_edit.php';
		$validation = validateForm::validate($_POST, $form['customer_add']);
		if (count($validation['failed']) > 0)
		{
			$_SESSION['errormessage'] = '';
			foreach ($validation['failed'] as $error)
			{
				$label = $error['label'];
				unset($error['label']);
				$error = join("<br />$label: ", $error);
				$_SESSION['errormessage'] .= "$label: $error<br />";
			}
			$_SESSION['formerror'] = $validation['failed'];
			redirectTo(Froxlor::getLinker()->getLink(array('area' => 'admin', 'section' => 'customers', 'action' => 'add')));
		}

		foreach(array('name', 'firstname', 'company', 'street', 'zipcode', 'city', 'phone', 'fax', 'customernumber', 'def_language', 'gender') as $fieldname)
		{
			$$fieldname = $validation['safe'][$fieldname];
		}
		$idna_convert = new idna_convert();
		$email = $idna_convert->encode($validation['safe']['email']);
		$password = $validation['safe']['new_customer_password'];

		foreach(array('diskspace', 'traffic', 'subdomains', 'emails', 'email_accounts', 'email_forwarders', 'email_quota', 'email_autoresponder', 'ftps', 'tickets', 'mysqls', 'aps_packages') as $type)
		{
			$check = 1;
			if ($type == 'email_quota' && getSetting('systems', 'mail_quotaenabled') != 1)
			{
				$check = 0;
			}
			if ($type == 'email_autoresponder' && getSetting('systems', 'autoresponder_active') != 1)
			{
				$check = 0;
			}
			if ($type == 'tickets' && getSetting('ticket', 'enabled') != 1)
			{
				$check = 0;
			}
			if ($type == 'aps_packages' && getSetting('aps', 'aps_active') != 1)
			{
				$check = 0;
			}

			if ($check)
			{
				$$type = $validation['safe'][$type];
				if (isset($validation['safe'][$type . '_ul']))
				{
					$$type = -1;
				}
			}
			else
			{
				$$type = ($$type == 'email_quota' ? -1 : 0);
			}
		}

		foreach(array('createstdsubdomain', 'deactivated', 'email_imap', 'email_pop3', 'backup_allowed', 'phpenabled', 'perlenabled') as $type)
		{
			$$type = 0;
			if (isset($validation['safe'][$type]))
			{
				$$type = (int)($validation['safe'][$type]);
			}
			if ($$type != 1)
			{
				$$type = 0;
			}
		}

		// gender out of range? [0,2]
		if ($gender < 0 || $gender > 2) {
			$gender = 0;
		}

		foreach(array('diskspace', 'mysqls', 'emails', 'email_accounts', 'email_forwarders', 'email_quota', 'email_autoresponder', 'ftps', 'tickets', 'subdomains', 'aps_packages') as $type)
		{
			$ok = 1;
			if (((Froxlor::getUser()->getData('resources', $type . '_used') + $$type - $user->getData('resources', $type)) > Froxlor::getUser()->getData('resources', $type))
				 && Froxlor::getUser()->getData('resources', $type) != '-1')
			{
				$ok = 0;
				// Mailquota would be wrong, but since the mailquota - system is disabled, it doesn't matter
				if ($type == 'email_quota' && getSetting('system', 'mail_quota_enabled') == 0)
				{
					$ok = 1;
				}

				// Autoresponder would be wrong, but since the autoresponder - system is disabled, it doesn't matter
				if ($type == 'email_autoresponder' && getSetting('autoresponder', 'autoresponder_active') == 0)
				{
					$ok = 1;
				}

				// APS would be wrong, but since the APS - system is disabled, it doesn't matter
				if ($type == 'aps_packages' && getSetting('aps', 'aps_active') == 0)
				{
					$ok = 1;
				}

			}

			if ($$type < '-1')
			{
				$ok = 0;
			}

			if (!$ok)
			{
				$_SESSION['errormessage'] = sprintf(_('You may not allocate more resources for the resource \'%s\' than you have'), $type);
				redirectTo(Froxlor::getLinker()->getLink($returnto));
			}
		}

		// Either $name and $firstname or the $company must be inserted
		if($name == '' && $company == '')
		{
			$_SESSION['errormessage'] = sprintf(_('Missing input in field \'%s\''), _('Name'));
			redirectTo(Froxlor::getLinker()->getLink($returnto));
		}
		elseif($firstname == '' && $company == '')
		{
			$_SESSION['errormessage'] = sprintf(_('Missing input in field \'%s\''), _('Firstname'));
			redirectTo(Froxlor::getLinker()->getLink($returnto));
		}
		elseif($email == '')
		{
			$_SESSION['errormessage'] = sprintf(_('Missing input in field \'%s\''), _('E-mail'));
			redirectTo(Froxlor::getLinker()->getLink($returnto));
		}
		elseif(!validateEmail($email))
		{
			$_SESSION['errormessage'] = sprintf(_('The entered e-mail address \'%s\' is wrong'), htmlspecialchars($email));
			redirectTo(Froxlor::getLinker()->getLink($returnto));
		}
		else
		{
			if($password != '')
			{
				$password = validatePassword($password);
				$user->setData('general', 'password', md5($password));
			}

			if($createstdsubdomain != '1')
			{
				$createstdsubdomain = '0';
			}

			if($createstdsubdomain == '1'
			   && $user->getData('resources', 'standardsubdomain') == '0')
			{
				if (getSetting('system', 'stdsubdomain') != '')
				{
					$_stdsubdomain = $user->getLoginname() . '.' . getSetting('system', 'stdsubdomain');
				}
				else
				{
					$_stdsubdomain = $user->getLoginname() . '.' . getSetting('system', 'hostname');
				}

				Froxlor::getDb()->query("INSERT INTO `panel_domains` " . "(`domain`, `customerid`, `adminid`, `parentdomainid`, `ipandport`, `documentroot`, `zonefile`, `isemaildomain`, `caneditdomain`, `openbasedir`, `safemode`, `speciallogfile`, `specialsettings`, `add_date`) " . "VALUES ('" . Froxlor::getDb()->escape($_stdsubdomain) . "', '" . (int)$user->getId() . "', '" . (int)Froxlor::getUser()->getId() . "', '-1', '" . Froxlor::getDb()->escape(getSetting('system', 'defaultip')) . "', '" . Froxlor::getDb()->escape($user->getData('resources', 'documentroot')) . "', '', '0', '0', '1', '1', '0', '', '".date('Y-m-d')."')");
				$domainid = Froxlor::getDb()->insert_id();
				$user->setData('resources', 'standardsubdomain', $domainid);
				#Froxlor::getLog()->logAction(ADM_ACTION, LOG_NOTICE, "automatically added standardsubdomain for user '" . $user->getLoginname() . "'");
				inserttask('1');
			}

			if($createstdsubdomain == '0'
			   && $user->getData('resources', 'standardsubdomain') != '0')
			{
				Froxlor::getDb()->query('DELETE FROM `panel_domains` WHERE `id`=\'' . (int)$user->getData('resources', 'standardsubdomain') . '\'');
				$user->setData('resources', 'standardsubdomain', 0);
				#Froxlor::getLog()->logAction(ADM_ACTION, LOG_NOTICE, "automatically deleted standardsubdomain for user '" . $user->getLoginname() . "'");
				inserttask('1');
			}

			if($deactivated != '1')
			{
				$deactivated = '0';
			}

			if($phpenabled != '0')
			{
				$phpenabled = '1';
			}

			if($perlenabled != '0')
			{
				$perlenabled = '1';
			}

			if($phpenabled != $user->getData('resources', 'phpenabled')
				|| $perlenabled != $user->getData('resources', 'perlenabled'))
			{
				inserttask('1');
			}

			if($deactivated != $user->isDeactivated())
			{
				Froxlor::getDb()->query("UPDATE `" . TABLE_MAIL_USERS . "` SET `postfix`='" . (($deactivated) ? 'N' : 'Y') . "', `pop3`='" . (($deactivated) ? '0' : '1') . "', `imap`='" . (($deactivated) ? '0' : '1') . "' WHERE `customerid`='" . (int)$user->getId() . "'");
				Froxlor::getDb()->query("UPDATE `" . TABLE_FTP_USERS . "` SET `login_enabled`='" . (($deactivated) ? 'N' : 'Y') . "' WHERE `customerid`='" . (int)$user->getId() . "'");
				Froxlor::getDb()->query("UPDATE `" . TABLE_PANEL_DOMAINS . "` SET `deactivated`='" . (int)$deactivated . "' WHERE `customerid`='" . (int)$user->getId() . "'");

				/* Retrieve customer's databases */
				$databases = Froxlor::getDb()->query("SELECT * FROM " . TABLE_PANEL_DATABASES . " WHERE customerid='" . (int)$user->getId() . "' ORDER BY `dbserver`");
				$db_root = new db($sql_root[0]['host'], $sql_root[0]['user'], $sql_root[0]['password'], '');
				unset($db_root->password);
				$last_dbserver = 0;

				/* For each of them */
				while($row_database = Froxlor::getDb()->fetch_array($databases))
				{
					if($last_dbserver != $row_database['dbserver'])
					{
						$db_root->query('FLUSH PRIVILEGES;');
						$db_root->close();
						$db_root = new db($sql_root[$row_database['dbserver']]['host'], $sql_root[$row_database['dbserver']]['user'], $sql_root[$row_database['dbserver']]['password'], '');
						unset($db_root->password);
						$last_dbserver = $row_database['dbserver'];
					}

					foreach(array_unique(explode(',', getSetting('system', 'mysql_access_host'))) as $mysql_access_host)
					{
						$mysql_access_host = trim($mysql_access_host);

						/* Prevent access, if deactivated */
						if($deactivated)
						{
							$db_root->query('REVOKE ALL PRIVILEGES ON * . * FROM `' . $db_root->escape($row_database['databasename']) . '`@`' . $db_root->escape($mysql_access_host) . '`');
							$db_root->query('REVOKE ALL PRIVILEGES ON `' . str_replace('_', '\_', $db_root->escape($row_database['databasename'])) . '` . * FROM `' . $db_root->escape($row_database['databasename']) . '`@`' . $db_root->escape($mysql_access_host) . '`');
						}
						else /* Otherwise grant access */
						{
							$db_root->query('GRANT ALL PRIVILEGES ON `' . $db_root->escape($row_database['databasename']) .'`.* TO `' . $db_root->escape($row_database['databasename']) . '`@`' . $db_root->escape($mysql_access_host) . '`');
							$db_root->query('GRANT ALL PRIVILEGES ON `' . str_replace('_', '\_', $db_root->escape($row_database['databasename'])) . '` . * TO `' . $db_root->escape($row_database['databasename']) . '`@`' . $db_root->escape($mysql_access_host) . '`');
						}
					}
				}

				/* At last flush the new privileges */
				$db_root->query('FLUSH PRIVILEGES;');
				$db_root->close();

				#Froxlor::getLog()->logAction(ADM_ACTION, LOG_INFO, "deactivated user '" . $user->getLoginname() . "'");
				inserttask('1');
			}

			// Disable or enable POP3 Login for customers Mail Accounts
			if($email_pop3 != $user->getData('resources', 'pop3'))
			{
				Froxlor::getDb()->query("UPDATE `" . TABLE_MAIL_USERS . "` SET `pop3`='" . (int)$email_pop3 . "' WHERE `customerid`='" . (int)$user->getId() . "'");
			}

			// Disable or enable IMAP Login for customers Mail Accounts
			if($email_imap != $user->getData('resources', 'imap'))
			{
				Froxlor::getDb()->query("UPDATE `" . TABLE_MAIL_USERS . "` SET `imap`='" . (int)$email_imap . "' WHERE `customerid`='" . (int)$user->getId() . "'");
			}

			$user->setData('general', 'def_language', $def_language);
			foreach(array('name', 'firstname', 'gender', 'company', 'street', 'zipcode', 'city', 'phone', 'fax', 'email') as $type)
			{
				$user->setData('address', $type, $$type);
			}

			# Using filesystem - quota, insert a task which cleans the filesystem - quota
			if (getSetting('system', 'diskquota_enabled'))
			{
				inserttask('10');
			}

			foreach(array('diskspace', 'mysqls', 'emails', 'email_accounts', 'email_forwarders', 'email_quota', 'email_autoresponder', 'ftps', 'tickets', 'subdomains', 'aps_packages') as $type)
			{
				if ($$type != -1 || $user->getData('resources', $type) != -1)
				{
					$newdata = Froxlor::getUser()->getData('resources', $type . '_used');
					if ($$type != '-1')
					{
						$newdata += (int)$$type;
					}
					if ($user->getData('resources', $$ype) != '-1')
					{
						$newdata -= (int)$user->getData('resources', $type);
					}
					Froxlor::getUser()->setData('resources', $type . '_used', $newdata);
				}
				$user->setData('resources', $type, $$type);
			}

			foreach(array('phpenabled', 'perlenabled', 'backup_allowed') as $type)
			{
				if ($$type != -1 || $user->getData('resources', $type) != -1)
				{

				}
				$user->setData('resources', $type, $$type);
			}

			#Froxlor::getLog()->logAction(ADM_ACTION, LOG_INFO, "edited user '" . $user->getLoginname() . "'");
			$_SESSION['successmessage'] = sprintf(_('The customer \'%s\' was updated successfully'), $user->getLoginname());
			redirectTo(Froxlor::getLinker()->getLink(array('area' => 'admin', 'section' => 'customers', 'action' => 'index')));
		}
	}
}