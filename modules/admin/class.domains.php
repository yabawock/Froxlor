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
 * adminIndex - Dashboard for the administrator
 *
 * This module contains the dashboard and account - management for
 * the administrator, i.e. changing password or language
 */
class adminDomains {
	public function index() {
		// $log->logAction(ADM_ACTION, LOG_NOTICE, "viewed admin_domains");

		// Get the number of users available
		if (Froxlor::getUser()->getData('resources', 'customers_see_all') == 1) {
			// this admin can see every customer - but we only want to get the customers, not admins
			$countcustomers = Froxlor::getDb()->query_first('SELECT COUNT(`id`) as `countcustomers` FROM `users` WHERE `isadmin` = "0";');
		} else {
			// this admin cannot see every customer, just select those where we are the admin
			$countcustomers = Froxlor::getDb()->query_first('SELECT COUNT(`id`) as `countcustomers` FROM `users`,`user2admin` WHERE `user2admin`.`adminid` = "'.Froxlor::getUser()->getId().'" AND `user2admin`.`userid` = `users`.`id`;');
		}

		// Let's see how many customers you are able to see
		if ($countcustomers == 0)
		{
			// You can't see any customer: without a customer, you can't add a domain!
			// Redirect to admin/customers/add with a helpful errormessage
			$_SESSION['errormessage'] = _('It\'s not possible to add a domain currently. You first need to add at least one customer.');
			redirectTo(Froxlor::getLinker()->getLink(array('area' => 'admin', 'section' => 'customers', 'action' => 'add')));
		}

		// Select all domains visible to this admin
		$result = Froxlor::getDb()->query(
		"SELECT `d`.*,
				`users`.`loginname`,
				`c`.`name`, `c`.`firstname`, `c`.`company`,
				`user_resources`.`standardsubdomain`,
				`ad`.`id` AS `aliasdomainid`, `ad`.`domain` AS `aliasdomain`,
				`ip`.`id` AS `ipid`, `ip`.`ip`, `ip`.`port`
				FROM `users`,`user_resources`, `user_addresses` `c`, `panel_domains` `d`
				LEFT JOIN `panel_domains` `ad` ON `d`.`aliasdomain`=`ad`.`id`
				LEFT JOIN `panel_ipsandports` `ip` ON (`d`.`ipandport` = `ip`.`id`)
				WHERE `d`.`parentdomainid`= '0'
						AND `user_resources`.`id` = `users`.`id`
						AND `d`.`customerid` = `users`.`id`
						AND `d`.`ipandport` = `ip`.`id`
				" . (Froxlor::getUser()->getData('resources', 'customers_see_all') ? '' : " AND `d`.`adminid` = '" . Froxlor::getUser()->getId() . "' ")
		);
		
		// Initialize the domain - storage
		$domain_array = array();
		// Initialize the IDNA - converter for IDN - domains
		$idna_convert = new idna_convert_wrapper();

		// Loop through all domains visible to this admin
		while($row = Froxlor::getDb()->fetch_array($result))
		{
			// Decode domain / aliasdomain - punycode into human readable "real" domains
			$row['domain'] = $idna_convert->decode($row['domain']);
			$row['aliasdomain'] = $idna_convert->decode($row['aliasdomain']);

			// Check if the domain uses IPv4 or IPv6
			if(filter_var($row['ip'], FILTER_VALIDATE_IP, FILTER_FLAG_IPV6))
			{
				// This domain uses IPv6, [] around the IP is needed
				$row['ipandport'] = '[' . $row['ip'] . ']:' . $row['port'];
			}
			else
			{
				// Plain old IPv4 is used
				$row['ipandport'] = $row['ip'] . ':' . $row['port'];
			}

			// Build the currect customername based on the data in the query
			// and save it in the resultset
			$row['customer'] = user::getCorrectFullUserDetails($row);

			// Let's see if we already added this domain to our completed domainlist
			if(!isset($domain_array[$row['domain']]))
			{
				// NO: Just add the result to the full list
				$domain_array[$row['domain']] = $row;
			}
			else
			{
				// YES: Merge the new result with the one already given
				// BTW: Can anyone tell me (EleRas) why this is needed? Shouldn't every domain be unique?
				$domain_array[$row['domain']] = array_merge($row, $domain_array[$row['domain']]);
			}

			// Let's see: is this an aliasdomain?
			if(isset($row['aliasdomainid']) && $row['aliasdomainid'] != NULL && isset($row['aliasdomain']) && $row['aliasdomain'] != '')
			{
				// It's an aliasdomain - do we have this domain in our full list already?
				if(!isset($domain_array[$row['aliasdomain']]))
				{
					// Domain was not added yet - initialize the array
					$domain_array[$row['aliasdomain']] = array();
				}

				// Add the data of the aliasdomain to the full resultset
				$domain_array[$row['aliasdomain']]['domainaliasid'] = $row['id'];
				$domain_array[$row['aliasdomain']]['domainalias'] = $row['domain'];
			}
		}

		// Assign the fully built domainlist to smarty for display
		Froxlor::getSmarty()->assign('domains', $domain_array);

		// Tell Smarty how many domains we have
		Froxlor::getSmarty()->assign('domainscount', count($domain_array));

		// Render and return the current page
		return Froxlor::getSmarty()->fetch('admin/domains/index.tpl');
	}

	public function add()
	{
		// Does this admin have enough free resources to add a new domain?
		if(Froxlor::getUser()->getData('resources', 'domains_used') >= Froxlor::getUser()->getData('resources', 'domains') && Froxlor::getUser()->getData('resources', 'domains') != '-1')
		{
			// NO: redirect to admin/domains/index for the complete list and issue an errormessage
			$_SESSION['errormessage'] = sprintf(_('You may not add more than %s domains'), Froxlor::getUser()->getData('resources', 'domains'));
			redirectTo(Froxlor::getLinker()->getLink(array('area' => 'admin', 'section' => 'domains', 'action' => 'index')));
		}

		// Prepare the dropdown of all customers
		// TODO: Make this more smarty - compatible
		$customers = makeoption('customerid', _('Please choose'), 0, 0, true);

		// Select all customers visible to this admin
		$result_customers = Froxlor::getDb()->query("
SELECT `users`.`id`, `loginname`, `name`, `firstname`, `company`
					FROM `users`, `user_addresses`, `user2admin`
					WHERE `users`.`id` = `user2admin`.`userid`
						AND `users`.`isadmin` = '0'
						AND `users`.`contactid` = `user_addresses`.`id`
						" . (Froxlor::getUser()->getData('resources', 'customers_see_all') ? '' : "
							AND `user2admin`.`adminid` = '" . Froxlor::getUser()->getId() . "'
						") . "
					ORDER BY `name` ASC");

		// Loop through all visible customers to build the dropdown
		while($row_customer = Froxlor::getDb()->fetch_array($result_customers))
		{
			// Add this customer to the dropdown and format the displayed name
			// TODO: Make this more smarty - compatible
			$customers.= makeoption('customerid', user::getCorrectFullUserDetails($row_customer) . ' (' . $row_customer['loginname'] . ')', $row_customer['id']);
		}

		// If this admin is able to see all customers, also select all admins
		$admins = '';
		if(Froxlor::getUser()->getData('resources', 'customers_see_all') == '1')
		{
			// Select every admin in this system with free domain - resources
			$result_admins = Froxlor::getDb()->query("SELECT `users`.`id`, `users`.`loginname`, `user_addresses`.`name`
						FROM `users`, `user_resources_admin`, `user_addresses`
						WHERE `user_resources_admin`.`domains_used` < `user_resources_admin`.`domains`
							OR `user_resources_admin`.`domains` = '-1'
							AND `users`.`id` = `user_addresses`.`id`
							AND `user_resources_admin`.`id` = `users`.`id`
						ORDER BY `user_addresses`.`name` ASC");

			// Loop through the selected admins
			while($row_admin = Froxlor::getDb()->fetch_array($result_admins))
			{
				// Add the admin to the dropdown with selecting the current admin as default
				// TODO: Make this more smarty - compatible
				$admins.= makeoption('adminid', user::getCorrectFullUserDetails($row_admin) . ' (' . $row_admin['loginname'] . ')', $row_admin['id'], Froxlor::getUser()->getId());
			}
		}

		// Does this admin have a designated IP or is he able to choose from the pool?
		// Let's prepare the corresponding MySQL - queries
		if(Froxlor::getUser()->getData('resources', 'ip') == "-1")
		{
			// This admin uses the complete IP - pool: select all IPs, seperated by "normal" and "SSL" - IPs
			$result_ipsandports = Froxlor::getDb()->query("SELECT `id`, `ip`, `port` FROM `" . TABLE_PANEL_IPSANDPORTS . "` WHERE `ssl`='0' ORDER BY `ip`, `port` ASC");
			$result_ssl_ipsandports = Froxlor::getDb()->query("SELECT `id`, `ip`, `port` FROM `" . TABLE_PANEL_IPSANDPORTS . "` WHERE `ssl`='1' ORDER BY `ip`, `port` ASC");
		}
		else
		{
			// This admin has to use a designated ip:port - combination: select it
			$admin_ip = Froxlor::getDb()->query_first("SELECT `id`, `ip`, `port` FROM `" . TABLE_PANEL_IPSANDPORTS . "` WHERE `id`='" . (int)Froxlor::getUser()->getData('resources', 'ip') . "' ORDER BY `ip`, `port` ASC");

			$result_ipsandports = Froxlor::getDb()->query("SELECT `id`, `ip`, `port` FROM `" . TABLE_PANEL_IPSANDPORTS . "` WHERE `ssl`='0' AND `ip`='" . $admin_ip['ip'] . "' ORDER BY `ip`, `port` ASC");
			$result_ssl_ipsandports = Froxlor::getDb()->query("SELECT `id`, `ip`, `port` FROM `" . TABLE_PANEL_IPSANDPORTS . "` WHERE `ssl`='1' AND `ip`='" . $admin_ip['ip'] . "' ORDER BY `ip`, `port` ASC");
		}

		// Loop through the prepared IP:Port query and turn the results into a dropdown
		$ipsandports = '';
		while($row_ipandport = Froxlor::getDb()->fetch_array($result_ipsandports))
		{
			// Is the IP an IPv6 - address?
			if(filter_var($row_ipandport['ip'], FILTER_VALIDATE_IP, FILTER_FLAG_IPV6))
			{
				// It's IPv6: put [] around the IP
				$row_ipandport['ip'] = '[' . $row_ipandport['ip'] . ']';
			}
			
			// Add the IP:Port combination to the dropdown, setting the system - defaultip as selected default
			$ipsandports.= makeoption('ipandport', $row_ipandport['ip'] . ':' . $row_ipandport['port'], $row_ipandport['id'], getSetting('system', 'defaultip'));
		}

		
		// Loop through the prepared SSL-IP:Port query and turn the results into a dropdown
		$ssl_ipsandports = '';
		while($row_ssl_ipandport = Froxlor::getDb()->fetch_array($result_ssl_ipsandports))
		{
			// Is the IP an IPv6 - address?
			if(filter_var($row_ssl_ipandport['ip'], FILTER_VALIDATE_IP, FILTER_FLAG_IPV6))
			{
				// It's IPv6: put [] around the IP
				$row_ssl_ipandport['ip'] = '[' . $row_ssl_ipandport['ip'] . ']';
			}

			// Add the SSL - IP:Port combination to the dropdown, setting the system - defaultip as selected default
			$ssl_ipsandports.= makeoption('ssl_ipandport', $row_ssl_ipandport['ip'] . ':' . $row_ssl_ipandport['port'], $row_ssl_ipandport['id'], getSetting('system', 'defaultip'));
		}

		// We need the standardsubdomains to exclude them from the aliasdomain-dropdown
		$standardsubdomains = array();

		// Select all standardsubdomains in the system
		$result_standardsubdomains = Froxlor::getDb()->query('SELECT `d`.`id` FROM `' . TABLE_PANEL_DOMAINS . '` `d`, `user_resources` `c` WHERE `d`.`id`=`c`.`standardsubdomain`');

		// Loop through the standardsubdomains
		while($row_standardsubdomain = Froxlor::getDb()->fetch_array($result_standardsubdomains))
		{
			// Add the standardsubdomain to our storage - array
			$standardsubdomains[] = Froxlor::getDb()->escape($row_standardsubdomain['id']);
		}

		// Let's see how many standardsubdomains are in the system
		if(count($standardsubdomains) > 0)
		{
			// There are standardsubdomains in the system: incorporate them into the SQL query for later usage
			$standardsubdomains = 'AND `d`.`id` NOT IN (' . join(',', $standardsubdomains) . ') ';
		}
		else
		{
			// There are no standardsubdomains in the system: nothing to be excluded
			$standardsubdomains = '';
		}

		// Prepare the aliasdomain - dropdown
		$domains = makeoption('alias', _('No alias domain'), 0, NULL, true);

		// Initialize the IDNA - converter
		$idna_convert = new idna_convert_wrapper();

		// Select all available domains in the system not being aliasdomains or standardsubdomains
		$result_domains = Froxlor::getDb()->query("SELECT `d`.`id`, `d`.`domain`, `c`.`loginname`
				FROM `" . TABLE_PANEL_DOMAINS . "` `d`, `users` `c`
				WHERE `d`.`aliasdomain` IS NULL
					AND `d`.`parentdomainid`=0
					" . $standardsubdomains . (Froxlor::getUser()->getData('resources', 'customers_see_all') ? '' : "
						AND `d`.`adminid` = '" . Froxlor::getUser()->getId() . "'") . "
				AND `d`.`customerid` = `c`.`id`
				ORDER BY `loginname`, `domain` ASC");

		// Loop through the selected domains
		while($row_domain = Froxlor::getDb()->fetch_array($result_domains))
		{
			// Put the domain into the "alias" - dropdown
			$domains.= makeoption('alias', $idna_convert->decode($row_domain['domain']) . ' (' . $row_domain['loginname'] . ')', $row_domain['id']);
		}

		// Prepare the dropdown for subdomainto
		$subtodomains = makeoption('issubof', _('No subdomain of a full domain'), 0, NULL, true);

		// Again: select all domains except aliasdomains, standardsubdomains and(!) domains already being subdomains of other domains
		$result_domains = Froxlor::getDb()->query("SELECT `d`.`id`, `d`.`domain`, `c`.`loginname`
				FROM `" . TABLE_PANEL_DOMAINS . "` `d`, `users` `c`
				WHERE `d`.`aliasdomain` IS NULL
					AND `d`.`parentdomainid` = 0
					AND `d`.`ismainbutsubto` = 0 " . $standardsubdomains .
						(Froxlor::getUser()->getData('resources', 'customers_see_all') ? '' : "
						AND `d`.`adminid` = '" . Froxlor::getUser()->getId() . "'") . "
					AND `d`.`customerid` = `c`.`id`
					AND `c`.`isadmin` = '0'
				ORDER BY `loginname`, `domain` ASC");

		// Loop through the selected domains
		while($row_domain = Froxlor::getDb()->fetch_array($result_domains))
		{
			// Add the domain to the "issubof" - dropdown
			$subtodomains.= makeoption('issubof', $idna_convert->decode($row_domain['domain']) . ' (' . $row_domain['loginname'] . ')', $row_domain['id']);
		}

		// Let's get all available PHP - configs
		$phpconfigs = '';
		$configs = Froxlor::getDb()->query("SELECT * FROM `" . TABLE_PANEL_PHPCONFIGS . "`");

		// Loop through all available PHP - configurations
		while($row = Froxlor::getDb()->fetch_array($configs))
		{
			// Add the name of the config to the "phpsettingid" - dropdown
			$phpconfigs.= makeoption('phpsettingid', $row['description'], $row['id'], getSetting('system', 'mod_fcgid_defaultini'), true, true);
		}

		// Prepare the dropdown which allows choosing if subdomains may be used as emaildomains
		$subcanemaildomain = makeoption('subcanemaildomain', _('Never'), '0', '0', true, true);
		$subcanemaildomain .= makeoption('subcanemaildomain', _('Chooseable, default no'), '1', '0', true, true);
		$subcanemaildomain .= makeoption('subcanemaildomain', _('Chooseable, default yes'), '2', '0', true, true);
		$subcanemaildomain .= makeoption('subcanemaildomain', _('Always'), '3', '0', true, true);

		$add_date = date('Y-m-d');

		// Get the array holding the form - definition
		$domain_add_data = include_once dirname(__FILE__).'/../../lib/formfields/admin/domains/formfield.domains_add.php';
		
		// Generate the HTML - form with the help of the stored array
		$domain_add_form = htmlform::genHTMLForm($domain_add_data);
		
		// Unset the "errormessages" for formfields in the session, so they can be filled with fresh data on the next submit
		unset($_SESSION['requestData'], $_SESSION['formerror']);

		// Assign various form-stuff to smarty for usage
		Froxlor::getSmarty()->assign('title', $domain_add_data['domain_add']['title']);
		Froxlor::getSmarty()->assign('image', $domain_add_data['domain_add']['image']);
		Froxlor::getSmarty()->assign('domain_add_form', $domain_add_form);

		// Render and return the current page
		return Froxlor::getSmarty()->fetch('admin/domains/domains_add.tpl');
	}
	
	public function addPost()
	{
		// Does this admin have enough free resources to add a new domain?
		if(Froxlor::getUser()->getData('resources', 'domains_used') >= Froxlor::getUser()->getData('resources', 'domains') && Froxlor::getUser()->getData('resources', 'domains') != '-1')
		{
			// NO: redirect to admin/domains/index for the complete list and issue an errormessage
			$_SESSION['errormessage'] = sprintf(_('You may not add more than %s domains'), Froxlor::getUser()->getData('resources', 'domains'));
			redirectTo(Froxlor::getLinker()->getLink(array('area' => 'admin', 'section' => 'domains', 'action' => 'index')));
		}

		$_SESSION['requestData'] = $_POST;

		$returnto = array('area' => 'admin', 'section' => 'domains', 'action' => 'add');

		// We need to hide errors in the include_once since there are many undefined variables in the formfield, but these not required for validation
		$form = @include_once dirname(__FILE__) . '/../../lib/formfields/admin/domains/formfield.domains_add.php';
		$validation = validateForm::validate($_POST, $form['domain_add']);
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
			redirectTo(Froxlor::getLinker()->getLink($returnto));
		}

		if($validation['safe']['domain'] == getSetting('system', 'hostname'))
		{
			$_SESSION['errormessage'] = _('Sorry. You can not use the Server Hostname as normal domain');
			redirectTo(Froxlor::getLinker()->getLink($returnto));
		}

		$idna_convert = new idna_convert();

		$domain = $idna_convert->encode(preg_replace(array('/\:(\d)+$/', '/^https?\:\/\//'), '', $validation['safe']['domain']));
		$subcanemaildomain = (int)$validation['safe']['subcanemaildomain'];

		$isemaildomain = 0;
		if(isset($validation['safe']['isemaildomain']))
		$isemaildomain = (int)$validation['safe']['isemaildomain'];

		$email_only = 0;
		if(isset($validation['safe']['email_only']))
			$email_only = $validation['safe']['email_only'];

		$wwwserveralias = 0;
		if(isset($validation['safe']['wwwserveralias']))
			$wwwserveralias = (int)$validation['safe']['wwwserveralias'];

		$speciallogfile = 0;
		if(isset($validation['safe']['speciallogfile']))
			$speciallogfile = (int)$validation['safe']['speciallogfile'];

		$aliasdomain = (int)$validation['safe']['alias'];
		$issubof = (int)$validation['safe']['issubof'];
		$customerid = (int)$validation['safe']['customerid'];

		$customer = Froxlor::getDb()->query_first("
			SELECT * FROM `users`, `user_resources`,`user2admin`
				WHERE `users`.`id` = '" . (int)$customerid . "'
					AND `users`.`id` = `user_resources`.`id`
				" . (Froxlor::getUser()->getData('resources', 'customers_see_all') ? '' : "
					AND `user2admin`.`userid` = '" . (int)$customerid . "'
					AND `user2admin`.`adminid` = '" . Froxlor::getUser()->getId() . "'
					") . " ");

		if(empty($customer)
			|| $customer['id'] != $customerid)
		{
			$_SESSION['errormessage'] = _('The customer you have chosen doesn\'t exist.');
			redirectTo(Froxlor::getLinker()->getLink($returnto));
		}

		if(Froxlor::getUser()->getData('resources', 'customers_see_all') == '1')
		{
			$adminid = (int)$validation['safe']['adminid'];
			$admin = Froxlor::getDb()->query_first("
				SELECT `users`.`id`
				FROM `users`, `user_resources_admin`
				WHERE `users`.`id` = '" . (int)$adminid . "'
					AND `users`.`id` = `user_resources_admin`.`id`
					AND `users`.`isadmin` = 1
					AND ( `domains_used` < `domains` OR `domains` = '-1' )");
			
			if(empty($admin)
				|| $admin['id'] != $adminid)
			{
				$_SESSION['errormessage'] = _('The admin you have chosen doesn\'t exist or does not have enough free resources.');
				redirectTo(Froxlor::getLinker()->getLink($returnto));
			}
			$admin = new user((int)$admin['id']);
		}
		else
		{
			$admin = Froxlor::getUser();
		}

		$documentroot = $customer['documentroot'];
		$registration_date = $validation['safe']['registration_date'];
		if ($registration_date == '')
		{
			$registration_date = '0000-00-00';
		}

		if(Froxlor::getUser()->getData('resources', 'change_serversettings') == '1')
		{
			$isbinddomain = (int)$validation['safe']['isbinddomain'];
			$caneditdomain = (int)$validation['safe']['caneditdomain'];
			$zonefile = validate($_POST['zonefile'], 'zonefile');

			if(isset($validation['safe']['dkim']))
			{
				$dkim = (int)$validation['safe']['dkim'];
			}
			else
			{
				$dkim = '1';
			}

			$specialsettings = str_replace("\r\n", "\n", $validation['safe']['specialsettings']);

			if(isset($validation['safe']['documentroot'])
				&& $validation['safe']['documentroot'] != '')
			{
				if(substr($validation['safe']['documentroot'], 0, 1) != '/'
					&& !preg_match('/^https?\:\/\//', $validation['safe']['documentroot']))
				{
					$documentroot.= '/' . $validation['safe']['documentroot'];
				}
				else
				{
					$documentroot = $validation['safe']['documentroot'];
				}
			}
		}
		else
		{
			$isbinddomain = '1';
			$caneditdomain = '1';
			$zonefile = '';
			$dkim = '1';
			$specialsettings = '';
		}

		if(Froxlor::getUser()->getData('resources', 'caneditphpsettings') == '1'
			|| Froxlor::getUser()->getData('resources', 'change_serversettings') == '1')
		{
			$openbasedir = isset($validation['safe']['openbasedir']) ? (int)$validation['safe']['openbasedir'] : 1;
			$safemode = isset($validation['safe']['safemode']) ? (int)$validation['safe']['safemode'] : 1;

			if((int)getSetting('system', 'mod_fcgid') == 1)
			{
				$phpsettingid = (int)$validation['safe']['phpsettingid'];
				$phpsettingid_check = Froxlor::getDb()->query_first("SELECT * FROM `" . TABLE_PANEL_PHPCONFIGS . "` WHERE `id` = " . (int)$phpsettingid);

				if(!isset($phpsettingid_check['id'])
					|| $phpsettingid_check['id'] == '0'
					|| $phpsettingid_check['id'] != $phpsettingid)
				{
					$_SESSION['errormessage'] = _('A PHP Configuration with this id doesn\'t exist');
					redirectTo(Froxlor::getLinker()->getLink($returnto));
				}

				$mod_fcgid_starter = (int)$validation['safe']['openbasedir'];
				if ((int)$mod_fcgid_starter <= 0)
				{
					$mod_fcgid_starter = -1;
				}
				$mod_fcgid_maxrequests = (int)$validation['safe']['mod_fcgid_maxrequests'];
				if ((int)$mod_fcgid_maxrequests <= 0)
				{
					$mod_fcgid_maxrequests = -1;
				}
			}
			else
			{
				$phpsettingid = getSetting('system', 'mod_fcgid_defaultini');
				$mod_fcgid_starter = '-1';
				$mod_fcgid_maxrequests = '-1';
			}
		}
		else
		{
			$openbasedir = '1';
			$safemode = '1';
			$phpsettingid = getSetting('system', 'mod_fcgid_defaultini');
			$mod_fcgid_starter = '-1';
			$mod_fcgid_maxrequests = '-1';
		}

		if(Froxlor::getUser()->getData('resources', 'ip') != "-1")
		{
			$admin_ip = Froxlor::getUser()->query_first("SELECT `id`, `ip`, `port` FROM `" . TABLE_PANEL_IPSANDPORTS . "` WHERE `id`='" . (int)Froxlor::getUser()->getData('resources', 'ip') . "' ORDER BY `ip`, `port` ASC");
			$additional_ip_condition = ' AND `ip` = \'' . $admin_ip['ip'] . '\' ';
		}
		else
		{
			$additional_ip_condition = '';
		}

		$ipandport = (int)$validation['safe']['ipandport'];

		$ipandport_check = Froxlor::getDb()->query_first("SELECT `id`, `ip`, `port` FROM `" . TABLE_PANEL_IPSANDPORTS . "` WHERE `id` = '" . (int)$ipandport . "' AND `ssl` = '0'" . $additional_ip_condition);

		if(!isset($ipandport_check['id'])
			|| $ipandport_check['id'] == '0'
			|| $ipandport_check['id'] != $ipandport)
		{
			$_SESSION['errormessage'] = _('The IP:Port combination you have chosen doesn\'t exist.');
			redirectTo(Froxlor::getLinker()->getLink($returnto));
		}

		if(getSetting('system', 'use_ssl') == "1"
			&& isset($validation['safe']['ssl'])
			&& isset($validation['safe']['ssl_ipandport'])
			&& $validation['safe']['ssl'] != '0')
		{
			$ssl = (int)$validation['safe']['ssl'];
			$ssl_redirect = 0;
			if (isset($validation['safe']['ssl_redirect'])) {
				$ssl_redirect = (int)$validation['safe']['ssl_redirect'];
			}
			$ssl_ipandport = (int)$validation['safe']['ssl_ipandport'];

			$ssl_ipandport_check = Froxlor::getDb()->query_first("SELECT `id`, `ip`, `port` FROM `" . TABLE_PANEL_IPSANDPORTS . "` WHERE `id` = '" . $ssl_ipandport . "' AND `ssl` = '1'" . $additional_ip_condition);

			if(!isset($ssl_ipandport_check['id'])
				|| $ssl_ipandport_check['id'] == '0'
				|| $ssl_ipandport_check['id'] != $ssl_ipandport)
			{
				$_SESSION['errormessage'] = _('The SSL - IP:Port combination you have chosen doesn\'t exist.');
				redirectTo(Froxlor::getLinker()->getLink($returnto));
			}
		}
		else
		{
			$ssl = 0;
			$ssl_redirect = 0;
			$ssl_ipandport = 0;
		}

		if(!preg_match('/^https?\:\/\//', $documentroot))
		{
			if(strstr($documentroot, ":") !== FALSE)
			{
				$_SESSION['errormessage'] = _('The path you have entered should not contain a colon (":"). Please enter a correct path value.');
				redirectTo(Froxlor::getLinker()->getLink($returnto));
			}
			else
			{
				$documentroot = makeCorrectDir($documentroot);
			}
		}

		$domain_check = Froxlor::getDb()->query_first("SELECT `id`, `domain` FROM `" . TABLE_PANEL_DOMAINS . "` WHERE `domain` = '" . Froxlor::getDb()->escape(strtolower($domain)) . "'");
		$aliasdomain_check = array(
			'id' => 0
		);

		if($aliasdomain != 0)
		{
			// also check ip/port combination to be the same, #176
			$aliasdomain_check = Froxlor::getDb()->query_first('
			SELECT `d`.`id`
			FROM `' . TABLE_PANEL_DOMAINS . '` `d`,`users` `c`
			WHERE `d`.`customerid`=\'' . (int)$customerid . '\'
				AND `d`.`aliasdomain` IS NULL
				AND `d`.`id`<>`c`.`standardsubdomain`
				AND `c`.`id`=\'' . (int)$customerid . '\'
				AND `d`.`id`=\'' . (int)$aliasdomain . '\'
				AND `d`.`ipandport` = \''.(int)$ipandport.'\'');
		}

		foreach(array('openbasedir', 'safemode', 'speciallogfile', 'isbinddomain', 'isemaildomain', 'email_only', 'dkim', 'wwwserveralias') as $type)
		{
			if ($$type != 1)
			{
				$$type = 0;
			}
		}

		if($email_only == '1')
		{
			$isemaildomain = '1';
		}

		if($issubof <= '0')
		{
			$issubof = '0';
		}

		if($domain == '')
		{
			$_SESSION['errormessage'] = sprintf(_('Missing input in field \'%s\''), _('Domain'));
			redirectTo(Froxlor::getLinker()->getLink($returnto));
		}
		elseif($documentroot == '')
		{
			$_SESSION['errormessage'] = sprintf(_('Missing input in field \'%s\''), _('Documentroot'));
			redirectTo(Froxlor::getLinker()->getLink($returnto));
		}
		elseif($customerid == 0)
		{
			$_SESSION['errormessage'] = _('Please create a customer first');
			redirectTo(Froxlor::getLinker()->getLink($returnto));
		}
		elseif(strtolower($domain_check['domain']) == strtolower($domain))
		{
			$_SESSION['errormessage'] = sprintf(_('The domain \'%s\' is already assigned to a customer'), $idna_convert->decode($domain));
			redirectTo(Froxlor::getLinker()->getLink($returnto));
		}
		elseif($aliasdomain_check['id'] != $aliasdomain)
		{
			$_SESSION['errormessage'] = _('The selected alias domain is either itself an alias domain, has a different ip/port combination or belongs to another customer');
			redirectTo(Froxlor::getLinker()->getLink($returnto));
		}
		else
		{
			$params = array(
				'page' => $page,
				'action' => $action,
				'domain' => $domain,
				'customerid' => $customerid,
				'adminid' => $admin->getId(),
				'documentroot' => $documentroot,
				'alias' => $aliasdomain,
				'isbinddomain' => $isbinddomain,
				'isemaildomain' => $isemaildomain,
				'email_only' => $email_only,
				'subcanemaildomain' => $subcanemaildomain,
				'caneditdomain' => $caneditdomain,
				'zonefile' => $zonefile,
				'dkim' => $dkim,
				'speciallogfile' => $speciallogfile,
				'wwwserveralias' => $wwwserveralias,
				'ipandport' => $ipandport,
				'ssl' => $ssl,
				'ssl_redirect' => $ssl_redirect,
				'ssl_ipandport' => $ssl_ipandport,
				'openbasedir' => $openbasedir,
				'safemode' => $safemode,
				'phpsettingid' => $phpsettingid,
				'mod_fcgid_starter' => $mod_fcgid_starter,
				'mod_fcgid_maxrequests' => $mod_fcgid_maxrequests,
				'specialsettings' => $specialsettings,
				'registration_date' => $registration_date,
				'issubof' => $issubof
			);

			$security_questions = array(
				'reallydisablesecuritysetting' => array('text' => _('Do you really want to disable this security setting OpenBasedir?'), 'value' => ($openbasedir == '0' && Froxlor::getUser()->getData('resources', 'change_serversettings') == '1')),
				'reallydocrootoutofcustomerroot' => array('text' => _('Are you sure, you want the document root for this domain, not being within the customer root of the customer?'), 'value' => (substr($documentroot, 0, strlen($customer['documentroot'])) != $customer['documentroot'] && !preg_match('/^https?\:\/\//', $documentroot)))
			);
			$question_nr = 1;
			foreach($security_questions as $question_name => $question_launch)
			{
				if($question_launch['value'] !== false)
				{
					$params[$question_name] = $question_name;

					if(!isset($_POST[$question_name])
						|| $_POST[$question_name] != $question_name)
					{
						return ask_yesno($question_launch['text'], $returnto, $params, $question_nr);
					}
				}
				$question_nr++;
			}

			Froxlor::getDb()->query("INSERT INTO `" . TABLE_PANEL_DOMAINS . "`
				SET 
				`domain` = '" . Froxlor::getDb()->escape($domain) . "',
				`customerid` = '" . (int)$customerid . "',
				`adminid` = '" . $admin->getId() . "',
				`documentroot` = '" . Froxlor::getDb()->escape($documentroot) . "',
				`ipandport` = '" . (int)$ipandport . "',
				`aliasdomain` = " . (($aliasdomain != 0) ? '\'' . (int)$aliasdomain . '\'' : 'NULL') . ",
				`zonefile` = '" . Froxlor::getDb()->escape($zonefile) . "',
				`dkim` = '" . (bool)$dkim . "',
				`wwwserveralias` = '" . (bool)$wwwserveralias . "',
				`isbinddomain` = '" . (bool)$isbinddomain . "',
				`isemaildomain` = '" . (bool)$isemaildomain . "',
				`email_only` = '" . (bool)$email_only . "',
				`subcanemaildomain` = '" . (bool)$subcanemaildomain . "', 
				`caneditdomain` = '" . (bool)$caneditdomain . "',
				`openbasedir` = '" . (bool)$openbasedir . "',
				`safemode` = '" . (bool)$safemode . "',
				`speciallogfile` = '" . (bool)$speciallogfile . "',
				`specialsettings` = '" . Froxlor::getDb()->escape($specialsettings) . "',
				`ssl` = '" . $ssl . "',
				`ssl_redirect` = '" . $ssl_redirect . "',
				`ssl_ipandport` = '" . $ssl_ipandport . "',
				`add_date` = NOW(),
				`registration_date` = '" . Froxlor::getDb()->escape($registration_date) . "',
				`phpsettingid` = '" . (int)$phpsettingid . "',
				`mod_fcgid_starter` = '" . (int)$mod_fcgid_starter . "',
				`mod_fcgid_maxrequests` = '" . (int)$mod_fcgid_maxrequests . "',
				`ismainbutsubto` = '".(int)$issubof."'");
			$domainid = Froxlor::getDb()->insert_id();

			$admin->setData('resources', 'domains_used', $admin->getData('resources', 'domains_used') + 1);
			//$log->logAction(ADM_ACTION, LOG_INFO, "added domain '" . $domain . "'");
			inserttask('1');

			# Using nameserver, insert a task which rebuilds the server config
			if (getSetting('system', 'bind_enable'))
			{
				inserttask('4');
			}
			
			$_SESSION['successmessage'] = sprintf(_('Domain \'%s\' successfully added'), $domain);
			redirectTo(Froxlor::getLinker()->getLink(array('area' => 'admin', 'section' => 'domains', 'action' => 'index')));
		}
	}
}