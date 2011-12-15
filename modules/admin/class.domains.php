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
			$row['customername'] = user::getCorrectFullUserDetails($row);

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
		Froxlor::getSmarty()->assign('domainscount', Froxlor::getDb()->num_rows($result));

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
					FROM `user_addresses`, `user2admin`, `users`
					WHERE `users`.`id` = `user2admin`.`userid`
						AND `users`.`isadmin` = '0'
						" . (Froxlor::getUser()->getData('resources', 'customers_see_all') ? '' : "
							AND `user2admin`.`adminid` = '" . Froxlor::getUser()->getId() . "'
							AND `users`.`contactid` = `user_addresses`.`id`
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
}