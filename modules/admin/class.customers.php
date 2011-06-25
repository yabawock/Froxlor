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
		unset($_SESSION['requestData']);

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
		unset($_SESSION['requestData']);

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
		$name = validate($_POST['name'], 'name');
		$firstname = validate($_POST['firstname'], 'first name');
		$company = validate($_POST['company'], 'company');
		$street = validate($_POST['street'], 'street');
		$zipcode = validate($_POST['zipcode'], 'zipcode', '/^[0-9 \-A-Z]*$/');
		$city = validate($_POST['city'], 'city');
		$phone = validate($_POST['phone'], 'phone', '/^[0-9\- \+\(\)\/]*$/');
		$fax = validate($_POST['fax'], 'fax', '/^[0-9\- \+\(\)\/]*$/');
		$idna_convert = new idna_convert();
		$email = $idna_convert->encode(validate($_POST['email'], 'email'));
		$customernumber = validate($_POST['customernumber'], 'customer number', '/^[A-Za-z0-9 \-]*$/Di');
		$def_language = validate($_POST['def_language'], 'default language');
		$diskspace = intval_ressource($_POST['diskspace']);
		$gender = intval_ressource($_POST['gender']);

		$ccode = "";
		if(isset($_POST['countrycode'])) {
			$ccode = $_POST['countrycode'];
		}

		if(isset($_POST['diskspace_ul']))
		{
			$diskspace = - 1;
		}

		$traffic = doubleval_ressource($_POST['traffic']);

		if(isset($_POST['traffic_ul']))
		{
			$traffic = - 1;
		}

		$subdomains = intval_ressource($_POST['subdomains']);

		if(isset($_POST['subdomains_ul']))
		{
			$subdomains = - 1;
		}

		$emails = intval_ressource($_POST['emails']);

		if(isset($_POST['emails_ul']))
		{
			$emails = - 1;
		}

		$email_accounts = intval_ressource($_POST['email_accounts']);

		if(isset($_POST['email_accounts_ul']))
		{
			$email_accounts = - 1;
		}

		$email_forwarders = intval_ressource($_POST['email_forwarders']);

		if(isset($_POST['email_forwarders_ul']))
		{
			$email_forwarders = - 1;
		}

		if(getSetting('system', 'mail_quota_enabled') == '1')
		{
			$email_quota = validate($_POST['email_quota'], 'email_quota', '/^\d+$/', 'vmailquotawrong', array('0', ''));

			if(isset($_POST['email_quota_ul']))
			{
				$email_quota = - 1;
			}
		}
		else
		{
			$email_quota = - 1;
		}

		if(getSetting('autoresponder', 'autoresponder_active') == '1')
		{
			$email_autoresponder = intval_ressource($_POST['email_autoresponder']);

			if(isset($_POST['email_autoresponder_ul']))
			{
				$email_autoresponder = - 1;
			}
		}
		else
		{
			$email_autoresponder = 0;
		}

		$email_imap = 0;
		if(isset($_POST['email_imap']))
			$email_imap = intval_ressource($_POST['email_imap']);

		$email_pop3 = 0;
		if(isset($_POST['email_pop3']))
			$email_pop3 = intval_ressource($_POST['email_pop3']);

		$ftps = 0;
		if(isset($_POST['ftps']))
			$ftps = intval_ressource($_POST['ftps']);

		if(isset($_POST['ftps_ul']))
		{
			$ftps = - 1;
		}

		$tickets = (getSetting('ticket', 'enabled') == 1 ? intval_ressource($_POST['tickets']) : 0);

		if(isset($_POST['tickets_ul'])
		   && getSetting('ticket', 'enabled') == '1')
		{
			$tickets = - 1;
		}

		$mysqls = intval_ressource($_POST['mysqls']);

		if(isset($_POST['mysqls_ul']))
		{
			$mysqls = - 1;
		}

		if(getSetting('aps', 'aps_active') == '1')
		{
			$aps_packages = intval_ressource($_POST['aps_packages']);

			if(isset($_POST['aps_packages_ul']))
			{
				$aps_packages = - 1;
			}
		}
		else
		{
			$aps_packages = 0;
		}

		$createstdsubdomain = 0;
		if(isset($_POST['createstdsubdomain']))
			$createstdsubdomain = intval($_POST['createstdsubdomain']);
		$password = validate($_POST['new_customer_password'], 'password');
		// only check if not empty,
		// cause empty == generate password automatically
		if($password != '')
		{
			$password = validatePassword($password);
		}

		$backup_allowed = 0;
		if(isset($_POST['backup_allowed']))
			$backup_allowed = intval($_POST['backup_allowed']);

		if ($backup_allowed != 0)
		{
			$backup_allowed = 1;
		}

		// gender out of range? [0,2]
		if ($gender < 0 || $gender > 2) {
			$gender = 0;
		}

		$sendpassword = 0;
		if(isset($_POST['sendpassword']))
			$sendpassword = intval($_POST['sendpassword']);

		$phpenabled = 0;
		if(isset($_POST['phpenabled']))
			$phpenabled = intval($_POST['phpenabled']);

		$perlenabled = 0;
		if(isset($_POST['perlenabled']))
			$perlenabled = intval($_POST['perlenabled']);

		$store_defaultindex = 0;
		if(isset($_POST['store_defaultindex']))
			$store_defaultindex = intval($_POST['store_defaultindex']);

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
			if(isset($_POST['new_loginname'])
			   && $_POST['new_loginname'] != '')
			{
				$accountnumber = intval(getSetting('system', 'lastaccountnumber'));
				$loginname = validate($_POST['new_loginname'], 'loginname', '/^[a-z0-9\-_]+$/i');

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


}