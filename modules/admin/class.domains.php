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
		$countcustomers = Froxlor::getDb()->query_first("SELECT COUNT(`customerid`) as `countcustomers` FROM `" . TABLE_PANEL_CUSTOMERS . "` " . (Froxlor::getUser()->getData("resources", "customers_see_all") ? '' : " WHERE `adminid` = '" . Froxlor::getUser()->getId() . "' ") . "");
		$countcustomers = (int)$countcustomers['countcustomers'];
		// $log->logAction(ADM_ACTION, LOG_NOTICE, "viewed admin_domains");
		$fields = array(
			'd.domain' => $lng['domains']['domainname'],
			'ip.ip' => $lng['admin']['ipsandports']['ip'],
			'ip.port' => $lng['admin']['ipsandports']['port'],
			'c.name' => $lng['customer']['name'],
			'c.firstname' => $lng['customer']['firstname'],
			'c.company' => $lng['customer']['company'],
			'c.loginname' => $lng['login']['username'],
			'd.aliasdomain' => $lng['domains']['aliasdomain']
		);
		// $paging = new paging($userinfo, $db, TABLE_PANEL_DOMAINS, $fields, $settings['panel']['paging'], $settings['panel']['natsorting']);
		$domains = '';
		$result = Froxlor::getDb()->query("SELECT `d`.*, `c`.`loginname`, `c`.`name`, `c`.`firstname`,
		`c`.`company`, `c`.`standardsubdomain`, `ad`.`id` AS `aliasdomainid`, `ad`.`domain` AS `aliasdomain`,
		`ip`.`id` AS `ipid`, `ip`.`ip`, `ip`.`port` " . "FROM `" . TABLE_PANEL_DOMAINS . "` `d` " .
		"LEFT JOIN `" . TABLE_PANEL_CUSTOMERS . "` `c` USING(`customerid`) " .
		"LEFT JOIN `" . TABLE_PANEL_DOMAINS . "` `ad` ON `d`.`aliasdomain`=`ad`.`id` " .
		"LEFT JOIN `" . TABLE_PANEL_IPSANDPORTS . "` `ip` ON (`d`.`ipandport` = `ip`.`id`) " .
		"WHERE `d`.`parentdomainid`='0' " . (Froxlor::getUser()->getData("resources", "customers_see_all") ? '' : " AND `d`.`adminid` = '" . Froxlor::getUser()->getId() . "' "));
		//$paging->setEntries(Froxlor::getDb()->num_rows($result));
		//$sortcode = $paging->getHtmlSortCode($lng);
		//$arrowcode = $paging->getHtmlArrowCode($filename . '?page=' . $page . '&s=' . $s);
		//$searchcode = $paging->getHtmlSearchCode($lng);
		//$pagingcode = $paging->getHtmlPagingCode($filename . '?page=' . $page . '&s=' . $s);
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

		/**
		 * We need ksort/krsort here to make sure idna-domains are also sorted correctly
		 */

		if($paging->sortfield == 'd.domain'
		   && $paging->sortorder == 'asc')
		{
			ksort($domain_array);
		}
		elseif($paging->sortfield == 'd.domain'
		       && $paging->sortorder == 'desc')
		{
			krsort($domain_array);
		}

		$i = 0;
		$count = 0;
		foreach($domain_array as $row)
		{
			if(isset($row['domain']) && $row['domain'] != '' && $paging->checkDisplay($i))
			{
				$row['customername'] = getCorrectFullUserDetails($row);
				$row = htmlentities_array($row);
				eval("\$domains.=\"" . getTemplate("domains/domains_domain") . "\";");
				$count++;
			}

			$i++;
		}

		$domainscount = Froxlor::getDb()->num_rows($result);

		// Display the list

		eval("echo \"" . getTemplate("domains/domains") . "\";");
	}
}