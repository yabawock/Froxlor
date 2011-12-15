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
		if(Froxlor::getUser()->getData('resources', 'domains_used') >= Froxlor::getUser()->getData('resources', 'domains') && Froxlor::getUser()->getData('resources', 'domains') != '-1')
		{
			$_SESSION['errormessage'] = sprintf(_('You may not add more than %s domains'), Froxlor::getUser()->getData('resources', 'domains'));
			redirectTo(Froxlor::getLinker()->getLink(array('area' => 'admin', 'section' => 'domains', 'action' => 'index')));
		}

		$customers = makeoption('customerid', _('Please choose'), 0, 0, true);

		// done
		$result_customers = Froxlor::getDb()->query("
			SELECT `users`.`id`, `loginname`, `name`, `firstname`, `company`
			FROM `user_addresses`, `user2admin`, `users`
				" . (Froxlor::getUser()->getData('resources', 'customers_see_all') ? '' : "
				WHERE `user2admin`.`adminid` = '" . Froxlor::getUser()->getId() . "'
					AND `user2admin`.`userid` = `user_addresses`.`id`
					AND `users`.`id` = `user2admin`.`userid`
				") . "
			ORDER BY `name` ASC");
		while($row_customer = Froxlor::getDb()->fetch_array($result_customers))
		{
			$customers.= makeoption('customerid', user::getCorrectFullUserDetails($row_customer) . ' (' . $row_customer['loginname'] . ')', $row_customer['id']);
		}

		$admins = '';

		if(Froxlor::getUser()->getData('resources', 'customers_see_all') == '1')
		{
			$result_admins = Froxlor::getDb()->query("SELECT `users`.`id`, `users`.`loginname`, `user_addresses`.`name`
						FROM `users`, `user_resources_admin`, `user_addresses`
						WHERE `user_resources_admin`.`domains_used` < `user_resources_admin`.`domains`
							OR `user_resources_admin`.`domains` = '-1'
							AND `users`.`id` = `user_addresses`.`id`
							AND `user_resources_admin`.`id` = `users`.`id`
						ORDER BY `user_addresses`.`name` ASC");

			while($row_admin = Froxlor::getDb()->fetch_array($result_admins))
			{
				$admins.= makeoption('adminid', user::getCorrectFullUserDetails($row_admin) . ' (' . $row_admin['loginname'] . ')', $row_admin['id'], Froxlor::getUser()->getId());
			}
		}

		if(Froxlor::getUser()->getData('resources', 'ip') == "-1")
		{
			$result_ipsandports = Froxlor::getDb()->query("SELECT `id`, `ip`, `port` FROM `" . TABLE_PANEL_IPSANDPORTS . "` WHERE `ssl`='0' ORDER BY `ip`, `port` ASC");
			$result_ssl_ipsandports = Froxlor::getDb()->query("SELECT `id`, `ip`, `port` FROM `" . TABLE_PANEL_IPSANDPORTS . "` WHERE `ssl`='1' ORDER BY `ip`, `port` ASC");
		}
		else
		{
			$admin_ip = Froxlor::getDb()->query_first("SELECT `id`, `ip`, `port` FROM `" . TABLE_PANEL_IPSANDPORTS . "` WHERE `id`='" . (int)Froxlor::getUser()->getData('resources', 'ip') . "' ORDER BY `ip`, `port` ASC");

			$result_ipsandports = Froxlor::getDb()->query("SELECT `id`, `ip`, `port` FROM `" . TABLE_PANEL_IPSANDPORTS . "` WHERE `ssl`='0' AND `ip`='" . $admin_ip['ip'] . "' ORDER BY `ip`, `port` ASC");

			$result_ssl_ipsandports = Froxlor::getDb()->query("SELECT `id`, `ip`, `port` FROM `" . TABLE_PANEL_IPSANDPORTS . "` WHERE `ssl`='1' AND `ip`='" . $admin_ip['ip'] . "' ORDER BY `ip`, `port` ASC");
		}

		$ipsandports = '';

		while($row_ipandport = Froxlor::getDb()->fetch_array($result_ipsandports))
		{
			if(filter_var($row_ipandport['ip'], FILTER_VALIDATE_IP, FILTER_FLAG_IPV6))
			{
				$row_ipandport['ip'] = '[' . $row_ipandport['ip'] . ']';
			}
			$ipsandports.= makeoption('ipandport', $row_ipandport['ip'] . ':' . $row_ipandport['port'], $row_ipandport['id'], getSetting('system', 'defaultip'));
		}

		$ssl_ipsandports = '';

		while($row_ssl_ipandport = Froxlor::getDb()->fetch_array($result_ssl_ipsandports))
		{
			if(filter_var($row_ssl_ipandport['ip'], FILTER_VALIDATE_IP, FILTER_FLAG_IPV6))
			{
				$row_ssl_ipandport['ip'] = '[' . $row_ssl_ipandport['ip'] . ']';
			}

			$ssl_ipsandports.= makeoption('ssl_ipandport', $row_ssl_ipandport['ip'] . ':' . $row_ssl_ipandport['port'], $row_ssl_ipandport['id'], getSetting('system', 'defaultip'));
		}

		$standardsubdomains = array();

		$result_standardsubdomains = Froxlor::getDb()->query('SELECT `d`.`id` FROM `' . TABLE_PANEL_DOMAINS . '` `d`, `user_resources` `c` WHERE `d`.`id`=`c`.`standardsubdomain`');

		while($row_standardsubdomain = Froxlor::getDb()->fetch_array($result_standardsubdomains))
		{
			$standardsubdomains[] = Froxlor::getDb()->escape($row_standardsubdomain['id']);
		}

		if(count($standardsubdomains) > 0)
		{
			$standardsubdomains = 'AND `d`.`id` NOT IN (' . join(',', $standardsubdomains) . ') ';
		}
		else
		{
			$standardsubdomains = '';
		}

		$domains = makeoption('alias', _('No alias domain'), 0, NULL, true);
		$idna_convert = new idna_convert_wrapper();
		// done
		$result_domains = Froxlor::getDb()->query("SELECT `d`.`id`, `d`.`domain`, `c`.`loginname`
				FROM `" . TABLE_PANEL_DOMAINS . "` `d`, `users` `c`
				WHERE `d`.`aliasdomain` IS NULL
					AND `d`.`parentdomainid`=0
					" . $standardsubdomains . (Froxlor::getUser()->getData('resources', 'customers_see_all') ? '' : "
						AND `d`.`adminid` = '" . Froxlor::getUser()->getId() . "'") . "
				AND `d`.`customerid` = `c`.`id`
				ORDER BY `loginname`, `domain` ASC");

		while($row_domain = Froxlor::getDb()->fetch_array($result_domains))
		{
			$domains.= makeoption('alias', $idna_convert->decode($row_domain['domain']) . ' (' . $row_domain['loginname'] . ')', $row_domain['id']);
		}

		$subtodomains = makeoption('issubof', _('No subdomain of a full domain'), 0, NULL, true);
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

		while($row_domain = Froxlor::getDb()->fetch_array($result_domains))
		{
			$subtodomains.= makeoption('issubof', $idna_convert->decode($row_domain['domain']) . ' (' . $row_domain['loginname'] . ')', $row_domain['id']);
		}

		$phpconfigs = '';

		$configs = Froxlor::getDb()->query("SELECT * FROM `" . TABLE_PANEL_PHPCONFIGS . "`");

		while($row = Froxlor::getDb()->fetch_array($configs))
		{
			$phpconfigs.= makeoption('phpsettingid', $row['description'], $row['id'], getSetting('system', 'mod_fcgid_defaultini'), true, true);
		}

		#$isbinddomain = makeyesno('isbinddomain', '1', '0', '1');
		#$isemaildomain = makeyesno('isemaildomain', '1', '0', '1');
		#$email_only = makeyesno('email_only', '1', '0', '0');
		$subcanemaildomain = makeoption('subcanemaildomain', _('Never'), '0', '0', true, true);
		$subcanemaildomain .= makeoption('subcanemaildomain', _('Chooseable, default no'), '1', '0', true, true);
		$subcanemaildomain .= makeoption('subcanemaildomain', _('Chooseable, default yes'), '2', '0', true, true);
		$subcanemaildomain .= makeoption('subcanemaildomain', _('Always'), '3', '0', true, true);
		#$dkim = makeyesno('dkim', '1', '0', '1');
		#$wwwserveralias = makeyesno('wwwserveralias', '1', '0', '1');
		#$caneditdomain = makeyesno('caneditdomain', '1', '0', '1');
		#$openbasedir = makeyesno('openbasedir', '1', '0', '1');
		#$safemode = makeyesno('safemode', '1', '0', '1');
		#$speciallogfile = makeyesno('speciallogfile', '1', '0', '0');
		#$ssl = makeyesno('ssl', '1', '0', '0');
		#$ssl_redirect = makeyesno('ssl_redirect', '1', '0', '0');
		$add_date = date('Y-m-d');

		$domain_add_data = include_once dirname(__FILE__).'/../../lib/formfields/admin/domains/formfield.domains_add.php';
		$domain_add_form = htmlform::genHTMLForm($domain_add_data);
		unset($_SESSION['requestData'], $_SESSION['formerror']);

		Froxlor::getSmarty()->assign('title', $domain_add_data['domain_add']['title']);
		Froxlor::getSmarty()->assign('image', $domain_add_data['domain_add']['image']);
		Froxlor::getSmarty()->assign('domain_add_form', $domain_add_form);

		return Froxlor::getSmarty()->fetch('admin/domains/domains_add.tpl');
	}
}