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
		if (Froxlor::getUser()->getData('resources', 'customers_see_all') == 1) {
			// get every user who isn't an admin
			$countcustomers = Froxlor::getDb()->query_first('SELECT COUNT(`id`) as `countcustomers` FROM `users` WHERE `isadmin` = "0";');
		} else {
			// admin cannot see every user
			$countcustomers = Froxlor::getDb()->query_first('SELECT COUNT(`id`) as `countcustomers` FROM `users`,`user2admin` WHERE `user2admin`.`adminid` = "'.Froxlor::getUser()->getId().'" AND `user2admin`.`userid` = `users`.`id`;');
		}
		Froxlor::getSmarty()->assign('countcustomers', (int)$countcustomers['countcustomers']);

		// $log->logAction(ADM_ACTION, LOG_NOTICE, "viewed admin_domains");
		$domains = '';
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
		$domain_array = array();

		while($row = Froxlor::getDb()->fetch_array($result))
		{
			$row['domain'] = $idna_convert->decode($row['domain']);
			$row['aliasdomain'] = $idna_convert->decode($row['aliasdomain']);

			if(filter_var($row['ip'], FILTER_VALIDATE_IP, FILTER_FLAG_IPV6))
			{
				$row['ipandport'] = '[' . $row['ip'] . ']:' . $row['port'];
			}
			else
			{
				$row['ipandport'] = $row['ip'] . ':' . $row['port'];
			}

			if(!isset($domain_array[$row['domain']]))
			{
				$domain_array[$row['domain']] = $row;
			}
			else
			{
				$domain_array[$row['domain']] = array_merge($row, $domain_array[$row['domain']]);
			}

			if(isset($row['aliasdomainid']) && $row['aliasdomainid'] != NULL && isset($row['aliasdomain']) && $row['aliasdomain'] != '')
			{
				if(!isset($domain_array[$row['aliasdomain']]))
				{
					$domain_array[$row['aliasdomain']] = array();
				}

				$domain_array[$row['aliasdomain']]['domainaliasid'] = $row['id'];
				$domain_array[$row['aliasdomain']]['domainalias'] = $row['domain'];
			}

		}

		Froxlor::getSmarty()->assign('domains', $domain_array);
		$i = 0;
		$count = 0;
		foreach($domain_array as $row)
		{
			if(isset($row['domain']) && $row['domain'] != '')
			{
				#$row['customername'] =
				$row = htmlentities_array($row);
				#eval("\$domains.=\"" . getTemplate("domains/domains_domain") . "\";");
				$count++;
			}

			$i++;
		}

		Froxlor::getSmarty()->assign('domainscount', Froxlor::getDb()->num_rows($result));

		// Render and return the current page
		return Froxlor::getSmarty()->fetch('admin/domains/index.tpl');
	}
}