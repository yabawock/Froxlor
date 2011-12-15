<?php

/**
 * This file is part of the Froxlor project.
 * Copyright (c) 2003-2009 the SysCP Team (see authors).
 * Copyright (c) 2010 the Froxlor Team (see authors).
 *
 * For the full copyright and license information, please view the COPYING
 * file that was distributed with this source code. You can also view the
 * COPYING file online at http://files.froxlor.org/misc/COPYING.txt
 *
 * @copyright  (c) the authors
 * @author     Florian Lippert <flo@syscp.org> (2003-2009)
 * @author     Froxlor team <team@froxlor.org> (2010-)
 * @license    GPLv2 http://files.froxlor.org/misc/COPYING.txt
 * @package    Panel
 *
 */

define('AREA', 'admin');

/**
 * Include our init.php, which manages Sessions, Language etc.
 */

require ("./lib/init.php");

if(isset($_POST['id']))
{
	$id = intval($_POST['id']);
}
elseif(isset($_GET['id']))
{
	$id = intval($_GET['id']);
}

if($page == 'domains'
   || $page == 'overview')
{
	// Let's see how many customers we have

	/* done
	$countcustomers = $db->query_first(
	"SELECT COUNT(`customerid`) as `countcustomers`
	FROM `" . TABLE_PANEL_CUSTOMERS . "` " . ($user->getData('resources', 'customers_see_all') ? '' : "
	WHERE `adminid` = '" . $user->getId() . "' ") . "");
	*/
	
	if ($user->getData('resources', 'customers_see_all')) {
		// get ervy user who isn't an admin
		$countcustomers = $db->query_first('SELECT COUNT(`id`) as `countcustomers` FROM `users` WHERE `isadmin` = "0";');
	} else {
		// admin cannot see every user
		$countcustomers = $db->query_first('SELECT COUNT(`id`) as `countcustomers` FROM `users`,`user2admin` WHERE `user2admin`.`adminid` = "'.$user->getId().'" AND `user2admin`.`userid` = `users`.`id`;');
	}
	
	$countcustomers = (int)$countcustomers['countcustomers'];
	

	if($action == '')
	{
		$log->logAction(ADM_ACTION, LOG_NOTICE, "viewed admin_domains");
		// TODO paging is not touched but will be removed anyway
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
		$paging = new paging($userinfo, $db, TABLE_PANEL_DOMAINS, $fields, $settings['panel']['paging'], $settings['panel']['natsorting']);
		
		$domains = '';
		// TODO this is not the same query... but as I'm pissed off on this... it will be done later
		$result = $db->query(
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
				" . ($user->getData('resources', 'customers_see_all') ? '' : " AND `d`.`adminid` = '" . $user->getId() . "' ")
		);
/*
		$result = $db->"
		SELECT
		`d`.*,
		`c`.`loginname`, `c`.`name`, `c`.`firstname`, `c`.`company`, `c`.`standardsubdomain`,
		`ad`.`id` AS `aliasdomainid`, `ad`.`domain` AS `aliasdomain`,
		`ip`.`id` AS `ipid`, `ip`.`ip`, `ip`.`port`
		FROM `" . TABLE_PANEL_DOMAINS . "` `d`
		LEFT JOIN `" . TABLE_PANEL_CUSTOMERS . "` `c` USING(`customerid`)
		LEFT JOIN `" . TABLE_PANEL_DOMAINS . "` `ad` ON `d`.`aliasdomain`=`ad`.`id`
		LEFT JOIN `" . TABLE_PANEL_IPSANDPORTS . "` `ip` ON (`d`.`ipandport` = `ip`.`id`)
		WHERE `d`.`parentdomainid`='0'
		" . ($userinfo['customers_see_all'] ? '' : " AND `d`.`adminid` = '" . $user->getId() . "' ") . " " . $paging->getSqlWhere(true) . " " . $paging->getSqlOrderBy() . " " . $paging->getSqlLimit());
*/
		
		
		$paging->setEntries($db->num_rows($result));
		$sortcode = $paging->getHtmlSortCode($lng);
		$arrowcode = $paging->getHtmlArrowCode($filename . '?page=' . $page . '&s=' . $s);
		$searchcode = $paging->getHtmlSearchCode($lng);
		$pagingcode = $paging->getHtmlPagingCode($filename . '?page=' . $page . '&s=' . $s);
		$domain_array = array();

		while($row = $db->fetch_array($result))
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

		$domainscount = $db->num_rows($result);

		// Display the list

		eval("echo \"" . getTemplate("domains/domains") . "\";");
	}
	elseif($action == 'delete'
	       && $id != 0)
	{
		// done
		$result = $db->query_first("
		SELECT `d`.`id`, `d`.`domain`, `d`.`customerid`, `d`.`documentroot`, `d`.`isemaildomain`, `d`.`zonefile`
		FROM `" . TABLE_PANEL_DOMAINS . "` `d`, `user_resources` `c`
		WHERE `d`.`id`='" . (int)$id . "'
			AND `d`.`id` <> `c`.`standardsubdomain`
			AND `c`.`id` = `d`.`customerid`
		" . ($user->getData('resources', 'customers_see_all') ? '' : " AND `d`.`adminid` = '" . $user->getId() . "' "));
		
		// done, not touched
		$alias_check = $db->query_first('SELECT COUNT(`id`) AS `count` FROM `' . TABLE_PANEL_DOMAINS . '` WHERE `aliasdomain`=\'' . (int)$id . '\'');

		if($result['domain'] != ''
			&& $alias_check['count'] == 0
		) {
			if(isset($_POST['send'])
			   && $_POST['send'] == 'send')
			{
				/*
				 * check for APS packages used with this domain, #110
				 */
				if(domainHasApsInstances($id))
				{
					standard_error('domains_cantdeletedomainwithapsinstances');
				}

				// check for deletion of main-domains which are logically subdomains, #329
				$rsd_sql = '';
				$remove_subbutmain_domains = isset($_POST['delete_userfiles']) ? 1 : 0;
				if($remove_subbutmain_domains == 1)
				{
					$rsd_sql .= ' OR `ismainbutsubto` = "'.(int)$id.'"';
				}

				// done, not touched
				$query = 'SELECT `id` FROM `' . TABLE_PANEL_DOMAINS . '` WHERE (`id`="' . (int)$id . '" OR `parentdomainid`="' . (int)$id . '"'.$rsd_sql.')  AND  `isemaildomain`="1"';
				$subResult = $db->query($query);
				$idString = array();

				while($subRow = $db->fetch_array($subResult))
				{
					$idString[] = '`domainid` = "' . (int)$subRow['id'] . '"';
				}

				$idString = implode(' OR ', $idString);

				if($idString != '')
				{
					// done, not touched
					$query = 'DELETE FROM `' . TABLE_MAIL_USERS . '` WHERE ' . $idString;
					$db->query($query);
					
					// done, not touched
					$query = 'DELETE FROM `' . TABLE_MAIL_VIRTUAL . '` WHERE ' . $idString;
					$db->query($query);
					$log->logAction(ADM_ACTION, LOG_NOTICE, "deleted domain/s from mail-tables");
				}

				// done, not touched
				$db->query("DELETE FROM `" . TABLE_PANEL_DOMAINS . "` WHERE `id`='" . (int)$id . "' OR `parentdomainid`='" . (int)$result['id'] . "'".$rsd_sql);
				$deleted_domains = (int)$db->affected_rows();
				
				// done
				$db->query("UPDATE `user_resources` SET `subdomains_used` = `subdomains_used` - " . (int)($deleted_domains - 1) . " WHERE `id` = '" . (int)$result['customerid'] . "'");
				
				// done
				$db->query("UPDATE `user_resources_admin` SET `domains_used` = `domains_used` - 1 WHERE `id` = '" . $user->getId() . "'");
				
				// done
				$db->query('UPDATE `user_resources` SET `standardsubdomain`=\'0\' WHERE `standardsubdomain`=\'' . (int)$result['id'] . '\' AND `id`=\'' . (int)$result['customerid'] . '\'');
				
				// done, not touched
				$db->query("DELETE FROM `" . TABLE_PANEL_DOMAINREDIRECTS . "` WHERE `did` = '".(int)$id."'");
				$log->logAction(ADM_ACTION, LOG_INFO, "deleted domain/subdomains (#" . $result['id'] . ")");
				updateCounters();
				inserttask('1');

				# Using nameserver, insert a task which rebuilds the server config
				if ($settings['system']['bind_enable'])
				{
					inserttask('4');
				}
				redirectTo($filename, Array('page' => $page, 's' => $s));
			}
			elseif ($alias_check['count'] > 0) {
				standard_error('domains_cantdeletedomainwithaliases');
			}
			else
			{
				$showcheck = false;
				if(domainHasMainSubDomains($id))
				{
					$showcheck = true;
				}
				ask_yesno_withcheckbox('admin_domain_reallydelete', 'remove_subbutmain_domains', $filename, array('id' => $id, 'page' => $page, 'action' => $action), $idna_convert->decode($result['domain']), $showcheck);
			}
		}
	}
	elseif($action == 'add')
	{
		if($userinfo['domains_used'] < $userinfo['domains']
		   || $userinfo['domains'] == '-1')
		{
			if(isset($_POST['send'])
			   && $_POST['send'] == 'send')
			{
				if($_POST['domain'] == $settings['system']['hostname'])
				{
					standard_error('admin_domain_emailsystemhostname');
					exit;
				}

				$domain = $idna_convert->encode(preg_replace(Array('/\:(\d)+$/', '/^https?\:\/\//'), '', validate($_POST['domain'], 'domain')));
				$subcanemaildomain = intval($_POST['subcanemaildomain']);

				$isemaildomain = 0;
				if(isset($_POST['isemaildomain']))
				$isemaildomain = intval($_POST['isemaildomain']);

				$email_only = 0;
				if(isset($_POST['email_only']))
					$email_only = intval($_POST['email_only']);

				$wwwserveralias = 0;
				if(isset($_POST['wwwserveralias']))
					$wwwserveralias = intval($_POST['wwwserveralias']);

				$speciallogfile = 0;
				if(isset($_POST['speciallogfile']))
					$speciallogfile = intval($_POST['speciallogfile']);

				$aliasdomain = intval($_POST['alias']);
				$issubof = intval($_POST['issubof']);
				$customerid = intval($_POST['customerid']);
				
				// done
				$customer = $db->query_first("
				SELECT * FROM `users`, `user_resources`,`user2admin` WHERE `users`.`id` = `user_resources`.`id`
				WHERE `users`.`id` = '" . (int)$customerid . "'
				" . ($user->getData('resources', 'customers_see_all') ? '' : "
					AND `user2admin`.`userid` = '" . (int)$customerid . "'
					AND `user2admin`.`adminid` = '" . $user->getId() . "'
					") . " ");

				if(empty($customer)
				   || $customer['customerid'] != $customerid)
				{
					standard_error('customerdoesntexist');
				}

				if($user->getData('resources', 'customers_see_all') == '1')
				{
					$adminid = intval($_POST['adminid']);
					// done, commented out
					//$admin = $db->query_first("SELECT * FROM `" . TABLE_PANEL_ADMINS . "` WHERE `adminid`='" . (int)$adminid . "' AND ( `domains_used` < `domains` OR `domains` = '-1' )");
					$admin = $user->getId();
					
					if(empty($admin)
					   || $admin['adminid'] != $adminid)
					{
						standard_error('admindoesntexist');
					}
				}
				else
				{
					$adminid = $userinfo['adminid'];
					$admin = $userinfo;
				}

				$documentroot = $customer['documentroot'];
				$registration_date = trim($_POST['registration_date']);
				$registration_date = validate($registration_date, 'registration_date', '/^(19|20)\d\d[-](0[1-9]|1[012])[-](0[1-9]|[12][0-9]|3[01])$/', '', array('0000-00-00', '0', ''));

				if($userinfo['change_serversettings'] == '1')
				{
					$isbinddomain = intval($_POST['isbinddomain']);
					$caneditdomain = intval($_POST['caneditdomain']);
					$zonefile = validate($_POST['zonefile'], 'zonefile');

					if(isset($_POST['dkim']))
					{
						$dkim = intval($_POST['dkim']);
					}
					else
					{
						$dkim = '1';
					}

					$specialsettings = validate(str_replace("\r\n", "\n", $_POST['specialsettings']), 'specialsettings', '/^[^\0]*$/');
					validate($_POST['documentroot'], 'documentroot');

					if(isset($_POST['documentroot'])
					   && $_POST['documentroot'] != '')
					{
						if(substr($_POST['documentroot'], 0, 1) != '/'
						   && !preg_match('/^https?\:\/\//', $_POST['documentroot']))
						{
							$documentroot.= '/' . $_POST['documentroot'];
						}
						else
						{
							$documentroot = $_POST['documentroot'];
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

				if($userinfo['caneditphpsettings'] == '1'
				   || $userinfo['change_serversettings'] == '1')
				{
					$openbasedir = intval($_POST['openbasedir']);
					$safemode = intval($_POST['safemode']);

					if((int)$settings['system']['mod_fcgid'] == 1)
					{
						$phpsettingid = (int)$_POST['phpsettingid'];
						$phpsettingid_check = $db->query_first("SELECT * FROM `" . TABLE_PANEL_PHPCONFIGS . "` WHERE `id` = " . (int)$phpsettingid);

						if(!isset($phpsettingid_check['id'])
						   || $phpsettingid_check['id'] == '0'
						   || $phpsettingid_check['id'] != $phpsettingid)
						{
							standard_error('phpsettingidwrong');
						}

						$mod_fcgid_starter = validate($_POST['mod_fcgid_starter'], 'mod_fcgid_starter', '/^[0-9]*$/', '', array('-1', ''));
						$mod_fcgid_maxrequests = validate($_POST['mod_fcgid_maxrequests'], 'mod_fcgid_maxrequests', '/^[0-9]*$/', '', array('-1', ''));
					}
					else
					{
						$phpsettingid = $settings['system']['mod_fcgid_defaultini'];
						$mod_fcgid_starter = '-1';
						$mod_fcgid_maxrequests = '-1';
					}
				}
				else
				{
					$openbasedir = '1';
					$safemode = '1';
					$phpsettingid = $settings['system']['mod_fcgid_defaultini'];
					$mod_fcgid_starter = '-1';
					$mod_fcgid_maxrequests = '-1';
				}

				if($userinfo['ip'] != "-1")
				{
					// done, not touched
					$admin_ip = $db->query_first("SELECT `id`, `ip`, `port` FROM `" . TABLE_PANEL_IPSANDPORTS . "` WHERE `id`='" . (int)$userinfo['ip'] . "' ORDER BY `ip`, `port` ASC");
					$additional_ip_condition = ' AND `ip` = \'' . $admin_ip['ip'] . '\' ';
				}
				else
				{
					$additional_ip_condition = '';
				}

				$ipandport = intval($_POST['ipandport']);
				
				// done, not touched
				$ipandport_check = $db->query_first("SELECT `id`, `ip`, `port` FROM `" . TABLE_PANEL_IPSANDPORTS . "` WHERE `id` = '" . $db->escape($ipandport) . "' AND `ssl` = '0'" . $additional_ip_condition);

				if(!isset($ipandport_check['id'])
				   || $ipandport_check['id'] == '0'
				   || $ipandport_check['id'] != $ipandport)
				{
					standard_error('ipportdoesntexist');
				}

				if($settings['system']['use_ssl'] == "1"
				   && isset($_POST['ssl'])
				   /*&& isset($_POST['ssl_redirect'])*/
				   && isset($_POST['ssl_ipandport'])
				   && $_POST['ssl'] != '0')
				{
					$ssl = (int)$_POST['ssl'];
					$ssl_redirect = 0;
					if (isset($_POST['ssl_redirect'])) {
						$ssl_redirect = (int)$_POST['ssl_redirect'];
					}
					$ssl_ipandport = (int)$_POST['ssl_ipandport'];
					
					// done, not touched
					$ssl_ipandport_check = $db->query_first("SELECT `id`, `ip`, `port` FROM `" . TABLE_PANEL_IPSANDPORTS . "` WHERE `id` = '" . $db->escape($ssl_ipandport) . "' AND `ssl` = '1'" . $additional_ip_condition);

					if(!isset($ssl_ipandport_check['id'])
					   || $ssl_ipandport_check['id'] == '0'
					   || $ssl_ipandport_check['id'] != $ssl_ipandport)
					{
						standard_error('ipportdoesntexist');
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
						standard_error('pathmaynotcontaincolon');
					}
					else
					{
						$documentroot = makeCorrectDir($documentroot);
					}
				}

				// done, not touched
				$domain_check = $db->query_first("SELECT `id`, `domain` FROM `" . TABLE_PANEL_DOMAINS . "` WHERE `domain` = '" . $db->escape(strtolower($domain)) . "'");
				$aliasdomain_check = array(
					'id' => 0
				);

				if($aliasdomain != 0)
				{
					// also check ip/port combination to be the same, #176
					// done
					$aliasdomain_check = $db->query_first('
					SELECT `d`.`id`
					FROM `' . TABLE_PANEL_DOMAINS . '` `d`,`users` `c`
					WHERE `d`.`customerid`=\'' . (int)$customerid . '\'
						AND `d`.`aliasdomain` IS NULL
						AND `d`.`id`<>`c`.`standardsubdomain`
						AND `c`.`id`=\'' . (int)$customerid . '\'
						AND `d`.`id`=\'' . (int)$aliasdomain . '\'
						AND `d`.`ipandport` = \''.(int)$ipandport.'\'');
				}

				if($openbasedir != '1')
				{
					$openbasedir = '0';
				}

				if($safemode != '1')
				{
					$safemode = '0';
				}

				if($speciallogfile != '1')
				{
					$speciallogfile = '0';
				}

				if($isbinddomain != '1')
				{
					$isbinddomain = '0';
				}

				if($isemaildomain != '1')
				{
					$isemaildomain = '0';
				}

				if($email_only == '1')
				{
					$isemaildomain = '1';
				}
				else
				{
					$email_only = '0';
				}

				if($subcanemaildomain != '1'
				   && $subcanemaildomain != '2'
				   && $subcanemaildomain != '3')
				{
					$subcanemaildomain = '0';
				}

				if($dkim != '1')
				{
					$dkim = '0';
				}

				if($wwwserveralias != '1')
				{
					$wwwserveralias = '0';
				}

				if($caneditdomain != '1')
				{
					$caneditdomain = '0';
				}

				if($issubof <= '0')
				{
					$issubof = '0';
				}

				if($domain == '')
				{
					standard_error(array('stringisempty', 'mydomain'));
				}
				/* Check whether domain validation is enabled and if, validate the domain */
				elseif($settings['system']['validate_domain'] && !validateDomain($domain))
				{
					standard_error(array('stringiswrong', 'mydomain'));
				}
				elseif($documentroot == '')
				{
					standard_error(array('stringisempty', 'mydocumentroot'));
				}
				elseif($customerid == 0)
				{
					standard_error('adduserfirst');
				}
				elseif(strtolower($domain_check['domain']) == strtolower($domain))
				{
					standard_error('domainalreadyexists', $idna_convert->decode($domain));
				}
				elseif($aliasdomain_check['id'] != $aliasdomain)
				{
					standard_error('domainisaliasorothercustomer');
				}
				else
				{
					$params = array(
						'page' => $page,
						'action' => $action,
						'domain' => $domain,
						'customerid' => $customerid,
						'adminid' => $adminid,
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
						'reallydisablesecuritysetting' => ($openbasedir == '0' && $userinfo['change_serversettings'] == '1'),
						'reallydocrootoutofcustomerroot' => (substr($documentroot, 0, strlen($customer['documentroot'])) != $customer['documentroot'] && !preg_match('/^https?\:\/\//', $documentroot))
					);
					$question_nr = 1;
					foreach($security_questions as $question_name => $question_launch)
					{
						if($question_launch !== false)
						{
							$params[$question_name] = $question_name;

							if(!isset($_POST[$question_name])
							   || $_POST[$question_name] != $question_name)
							{
								ask_yesno('admin_domain_' . $question_name, $filename, $params, $question_nr);
								exit;
							}
						}
						$question_nr++;
					}

					// done, not touched
					$db->query("INSERT INTO `" . TABLE_PANEL_DOMAINS . "` (`domain`, `customerid`, `adminid`, `documentroot`, `ipandport`,`aliasdomain`, `zonefile`, `dkim`, `wwwserveralias`, `isbinddomain`, `isemaildomain`, `email_only`, `subcanemaildomain`, `caneditdomain`, `openbasedir`, `safemode`,`speciallogfile`, `specialsettings`, `ssl`, `ssl_redirect`, `ssl_ipandport`, `add_date`, `registration_date`, `phpsettingid`, `mod_fcgid_starter`, `mod_fcgid_maxrequests`, `ismainbutsubto`) VALUES ('" . $db->escape($domain) . "', '" . (int)$customerid . "', '" . (int)$adminid . "', '" . $db->escape($documentroot) . "', '" . $db->escape($ipandport) . "', " . (($aliasdomain != 0) ? '\'' . $db->escape($aliasdomain) . '\'' : 'NULL') . ", '" . $db->escape($zonefile) . "', '" . $db->escape($dkim) . "', '" . $db->escape($wwwserveralias) . "', '" . $db->escape($isbinddomain) . "', '" . $db->escape($isemaildomain) . "', '" . $db->escape($email_only) . "', '" . $db->escape($subcanemaildomain) . "', '" . $db->escape($caneditdomain) . "', '" . $db->escape($openbasedir) . "', '" . $db->escape($safemode) . "', '" . $db->escape($speciallogfile) . "', '" . $db->escape($specialsettings) . "', '" . $ssl . "', '" . $ssl_redirect . "' , '" . $ssl_ipandport . "', '" . $db->escape(time()) . "', '" . $db->escape($registration_date) . "', '" . (int)$phpsettingid . "', '" . (int)$mod_fcgid_starter . "', '" . (int)$mod_fcgid_maxrequests . "', '".(int)$issubof."')");
					$domainid = $db->insert_id();
					
					// done
					$db->query("UPDATE `user_resources_admin` SET `domains_used` = `domains_used` + 1 WHERE `id` = '" . (int)$adminid . "'");
					$log->logAction(ADM_ACTION, LOG_INFO, "added domain '" . $domain . "'");
					inserttask('1');

					# Using nameserver, insert a task which rebuilds the server config
					if ($settings['system']['bind_enable'])
					{
						inserttask('4');
					}
					redirectTo($filename, Array('page' => $page, 's' => $s));
				}
			}
			else
			{
				$customers = makeoption($lng['panel']['please_choose'], 0, 0, true);
				
				// done
				$result_customers = $db->query("
					SELECT `users`.`id`, `loginname`, `name`, `firstname`, `company`
					FROM `user_addresses`, `user2admin`, `users`
					WHERE `users`.`id` = `user2admin`.`userid`
						AND `users`.`isadmin` = '0'
						" . ($user->getData('resources', 'customers_see_all') ? '' : "
							AND `user2admin`.`adminid` = '" . $user->getId() . "'
							AND `users`.`contactid` = `user_addresses`.`id`
						") . "
					ORDER BY `name` ASC");

				while($row_customer = $db->fetch_array($result_customers))
				{
					$customers.= makeoption(getCorrectFullUserDetails($row_customer) . ' (' . $row_customer['loginname'] . ')', $row_customer['customerid']);
				}

				$admins = '';

				if($user->getData('resources', 'customers_see_all') == '1')
				{
					$result_admins = $db->query("
						SELECT `users`.`id`, `users`.`loginname`, `user_addresses`.`name`
						FROM `users`, `user_resources_admin`, `user_addresses`
						WHERE `user_resources_admin`.`domains_used` < `user_resources_admin`.`domains`
							OR `user_resources_admin`.`domains` = '-1'
							AND `users`.`id` = `user_addresses`.`id`
							AND `user_resources_admin`.`id` = `users`.`id`
						ORDER BY `user_addresses`.`name` ASC");

					while($row_admin = $db->fetch_array($result_admins))
					{
						$admins.= makeoption(getCorrectFullUserDetails($row_admin) . ' (' . $row_admin['loginname'] . ')', $row_admin['adminid'], $userinfo['adminid']);
					}
				}

				if($userinfo['ip'] == "-1")
				{
					// done, not touched
					$result_ipsandports = $db->query("SELECT `id`, `ip`, `port` FROM `" . TABLE_PANEL_IPSANDPORTS . "` WHERE `ssl`='0' ORDER BY `ip`, `port` ASC");
					
					// done, not touched
					$result_ssl_ipsandports = $db->query("SELECT `id`, `ip`, `port` FROM `" . TABLE_PANEL_IPSANDPORTS . "` WHERE `ssl`='1' ORDER BY `ip`, `port` ASC");
				}
				else
				{
					// done, not touched
					$admin_ip = $db->query_first("SELECT `id`, `ip`, `port` FROM `" . TABLE_PANEL_IPSANDPORTS . "` WHERE `id`='" . (int)$userinfo['ip'] . "' ORDER BY `ip`, `port` ASC");
					
					// done, not touched
					$result_ipsandports = $db->query("SELECT `id`, `ip`, `port` FROM `" . TABLE_PANEL_IPSANDPORTS . "` WHERE `ssl`='0' AND `ip`='" . $admin_ip['ip'] . "' ORDER BY `ip`, `port` ASC");
					
					// done, not touched
					$result_ssl_ipsandports = $db->query("SELECT `id`, `ip`, `port` FROM `" . TABLE_PANEL_IPSANDPORTS . "` WHERE `ssl`='1' AND `ip`='" . $admin_ip['ip'] . "' ORDER BY `ip`, `port` ASC");
				}

				$ipsandports = '';

				while($row_ipandport = $db->fetch_array($result_ipsandports))
				{
					if(filter_var($row_ipandport['ip'], FILTER_VALIDATE_IP, FILTER_FLAG_IPV6))
					{
						$row_ipandport['ip'] = '[' . $row_ipandport['ip'] . ']';
					}

					$ipsandports.= makeoption($row_ipandport['ip'] . ':' . $row_ipandport['port'], $row_ipandport['id'], $settings['system']['defaultip']);
				}

				$ssl_ipsandports = '';

				while($row_ssl_ipandport = $db->fetch_array($result_ssl_ipsandports))
				{
					if(filter_var($row_ssl_ipandport['ip'], FILTER_VALIDATE_IP, FILTER_FLAG_IPV6))
					{
						$row_ssl_ipandport['ip'] = '[' . $row_ssl_ipandport['ip'] . ']';
					}

					$ssl_ipsandports.= makeoption($row_ssl_ipandport['ip'] . ':' . $row_ssl_ipandport['port'], $row_ssl_ipandport['id'], $settings['system']['defaultip']);
				}

				$standardsubdomains = array();
				
				// done
				$result_standardsubdomains = $db->query('SELECT `id` FROM `' . TABLE_PANEL_DOMAINS . '` `d`, `user_resources` `c` WHERE `d`.`id`=`c`.`standardsubdomain`');

				while($row_standardsubdomain = $db->fetch_array($result_standardsubdomains))
				{
					$standardsubdomains[] = $db->escape($row_standardsubdomain['id']);
				}

				if(count($standardsubdomains) > 0)
				{
					$standardsubdomains = 'AND `d`.`id` NOT IN (' . join(',', $standardsubdomains) . ') ';
				}
				else
				{
					$standardsubdomains = '';
				}

				$domains = makeoption($lng['domains']['noaliasdomain'], 0, NULL, true);
				
				// done
				$result_domains = $db->query("
				SELECT `d`.`id`, `d`.`domain`, `c`.`loginname`
				FROM `" . TABLE_PANEL_DOMAINS . "` `d`, `users` `c`
				WHERE `d`.`aliasdomain` IS NULL
					AND `d`.`parentdomainid`=0
					" . $standardsubdomains . ($user->getData('resources', 'customers_see_all') ? '' : "
						AND `d`.`adminid` = '" . $user->getId() . "'") . "
				AND `d`.`customerid` = `c`.`id`
				ORDER BY `loginname`, `domain` ASC");

				while($row_domain = $db->fetch_array($result_domains))
				{
					$domains.= makeoption($idna_convert->decode($row_domain['domain']) . ' (' . $row_domain['loginname'] . ')', $row_domain['id']);
				}

				$subtodomains = makeoption($lng['domains']['nosubtomaindomain'], 0, NULL, true);
				$result_domains = $db->query("
				SELECT `d`.`id`, `d`.`domain`, `c`.`loginname`
				FROM `" . TABLE_PANEL_DOMAINS . "` `d`, `users` `c`
				WHERE `d`.`aliasdomain` IS NULL
					AND `d`.`parentdomainid` = 0
					AND `d`.`ismainbutsubto` = 0 " . $standardsubdomains .
						($user->getData('resources', 'customers_see_all') ? '' : "
						AND `d`.`adminid` = '" . $user->getId() . "'") . "
					AND `d`.`customerid` = `c`.`id`
					AND `c`.`isadmin` = '0'
				ORDER BY `loginname`, `domain` ASC");

				while($row_domain = $db->fetch_array($result_domains))
				{
					$subtodomains.= makeoption($idna_convert->decode($row_domain['domain']) . ' (' . $row_domain['loginname'] . ')', $row_domain['id']);
				}

				$phpconfigs = '';
				
				// done, not touched
				$configs = $db->query("SELECT * FROM `" . TABLE_PANEL_PHPCONFIGS . "`");

				while($row = $db->fetch_array($configs))
				{
					$phpconfigs.= makeoption($row['description'], $row['id'], $settings['system']['mod_fcgid_defaultini'], true, true);
				}

				#$isbinddomain = makeyesno('isbinddomain', '1', '0', '1');
				#$isemaildomain = makeyesno('isemaildomain', '1', '0', '1');
				#$email_only = makeyesno('email_only', '1', '0', '0');
				$subcanemaildomain = makeoption($lng['admin']['subcanemaildomain']['never'], '0', '0', true, true) . makeoption($lng['admin']['subcanemaildomain']['choosableno'], '1', '0', true, true) . makeoption($lng['admin']['subcanemaildomain']['choosableyes'], '2', '0', true, true) . makeoption($lng['admin']['subcanemaildomain']['always'], '3', '0', true, true);
				#$dkim = makeyesno('dkim', '1', '0', '1');
				#$wwwserveralias = makeyesno('wwwserveralias', '1', '0', '1');
				#$caneditdomain = makeyesno('caneditdomain', '1', '0', '1');
				#$openbasedir = makeyesno('openbasedir', '1', '0', '1');
				#$safemode = makeyesno('safemode', '1', '0', '1');
				#$speciallogfile = makeyesno('speciallogfile', '1', '0', '0');
				#$ssl = makeyesno('ssl', '1', '0', '0');
				#$ssl_redirect = makeyesno('ssl_redirect', '1', '0', '0');
				$add_date = date('Y-m-d');

				$domain_add_data = include_once dirname(__FILE__).'/lib/formfields/admin/domains/formfield.domains_add.php';
				$domain_add_form = htmlform::genHTMLForm($domain_add_data);

				$title = $domain_add_data['domain_add']['title'];
				$image = $domain_add_data['domain_add']['image'];

				eval("echo \"" . getTemplate("domains/domains_add") . "\";");
			}
		}
	}
	elseif($action == 'edit'
	       && $id != 0)
	{
		// done
		$result = $db->query_first("
			SELECT `d`.*, `c`.`id` as `customerid`
			FROM `" . TABLE_PANEL_DOMAINS . "` `d`,`users` `c`
			WHERE `d`.`parentdomainid`='0'
				AND `d`.`id`='" . (int)$id . "'"
				. ($user->getData('resources', 'customers_see_all') ? '' : " AND `d`.`adminid` = '" . $user->getId() . "' "));

		if($result['domain'] != '')
		{
			// done, not touched
			$subdomains = $db->query_first('SELECT COUNT(`id`) AS count FROM `' . TABLE_PANEL_DOMAINS . '` WHERE `parentdomainid`=\'' . (int)$result['id'] . '\'');
			$subdomains = $subdomains['count'];
			
			// done, not touched
			$alias_check = $db->query_first('SELECT COUNT(`id`) AS count FROM `' . TABLE_PANEL_DOMAINS . '` WHERE `aliasdomain`=\'' . (int)$result['id'] . '\'');
			$alias_check = $alias_check['count'];
			
			// done, not touched
			$domain_emails_result = $db->query('SELECT `email`, `email_full`, `destination`, `popaccountid` AS `number_email_forwarders` FROM `' . TABLE_MAIL_VIRTUAL . '` WHERE `customerid` = "' . (int)$result['customerid'] . '" AND `domainid` = "' . (int)$result['id'] . '" ');
			$emails = $db->num_rows($domain_emails_result);
			$email_forwarders = 0;
			$email_accounts = 0;

			while($domain_emails_row = $db->fetch_array($domain_emails_result))
			{
				if($domain_emails_row['destination'] != '')
				{
					$domain_emails_row['destination'] = explode(' ', makeCorrectDestination($domain_emails_row['destination']));
					$email_forwarders+= count($domain_emails_row['destination']);

					if(in_array($domain_emails_row['email_full'], $domain_emails_row['destination']))
					{
						$email_forwarders-= 1;
						$email_accounts++;
					}
				}
			}

			if(isset($_POST['send'])
			   && $_POST['send'] == 'send')
			{
				// part will be replaced with user object
				$customer = $customer_old = $db->query_first("SELECT * FROM " . TABLE_PANEL_CUSTOMERS . " WHERE `customerid`='" . (int)$result['customerid'] . "'");

				if(isset($_POST['customerid'])
				   && ($customerid = intval($_POST['customerid'])) != $result['customerid']
				   && $settings['panel']['allow_domain_change_customer'] == '1')
				{
					$customer = $db->query_first("SELECT * FROM `" . TABLE_PANEL_CUSTOMERS . "` WHERE `customerid`='" . (int)$customerid . "' AND (`subdomains_used` + " . (int)$subdomains . " <= `subdomains` OR `subdomains` = '-1' ) AND (`emails_used` + " . (int)$emails . " <= `emails` OR `emails` = '-1' ) AND (`email_forwarders_used` + " . (int)$email_forwarders . " <= `email_forwarders` OR `email_forwarders` = '-1' ) AND (`email_accounts_used` + " . (int)$email_accounts . " <= `email_accounts` OR `email_accounts` = '-1' ) " . ($user->getData('resources', 'customers_see_all') ? '' : " AND `adminid` = '" . $user->getId() . "' ") . " ");

					if(empty($customer)
					   || $customer['customerid'] != $customerid)
					{
						standard_error('customerdoesntexist');
					}
				}
				else
				{
					$customerid = $result['customerid'];
				}

				$admin = $admin_old = $db->query_first("SELECT * FROM `" . TABLE_PANEL_ADMINS . "` WHERE `adminid`='" . (int)$result['adminid'] . "' ");

				if($user->getData('resources', 'customers_see_all') == '1')
				{
					if(isset($_POST['adminid'])
					   && ($adminid = intval($_POST['adminid'])) != $result['adminid']
					   && $settings['panel']['allow_domain_change_admin'] == '1')
					{
						$admin = $db->query_first("SELECT * FROM `" . TABLE_PANEL_ADMINS . "` WHERE `adminid`='" . (int)$adminid . "' AND ( `domains_used` < `domains` OR `domains` = '-1' )");

						if(empty($admin)
						   || $admin['adminid'] != $adminid)
						{
							standard_error('admindoesntexist');
						}
					}
					else
					{
						$adminid = $result['adminid'];
					}
				}
				else
				{
					$adminid = $result['adminid'];
				}

				$aliasdomain = intval($_POST['alias']);
				$issubof = intval($_POST['issubof']);
				$subcanemaildomain = intval($_POST['subcanemaildomain']);
				$caneditdomain = intval($_POST['caneditdomain']);
				$registration_date = trim($_POST['registration_date']);
				$registration_date = validate($registration_date, 'registration_date', '/^(19|20)\d\d[-](0[1-9]|1[012])[-](0[1-9]|[12][0-9]|3[01])$/', '', array('0000-00-00', '0', ''));

				$isemaildomain = 0;
				if(isset($_POST['isemaildomain']))
				$isemaildomain = intval($_POST['isemaildomain']);

				$email_only = 0;
				if(isset($_POST['email_only']))
					$email_only = intval($_POST['email_only']);

				$wwwserveralias = 0;
				if(isset($_POST['wwwserveralias']))
					$wwwserveralias = intval($_POST['wwwserveralias']);

				if($userinfo['change_serversettings'] == '1')
				{
					$isbinddomain = intval($_POST['isbinddomain']);
					$zonefile = validate($_POST['zonefile'], 'zonefile');

					if($settings['dkim']['use_dkim'] == '1')
					{
						$dkim = intval($_POST['dkim']);
					}
					else
					{
						$dkim = $result['dkim'];
					}

					$specialsettings = validate(str_replace("\r\n", "\n", $_POST['specialsettings']), 'specialsettings', '/^[^\0]*$/');
					$documentroot = validate($_POST['documentroot'], 'documentroot');

					if($documentroot == '')
					{
						$documentroot = $customer['documentroot'];
					}

					if(!preg_match('/^https?\:\/\//', $documentroot)
						&& strstr($documentroot, ":") !== FALSE
					) {
						standard_error('pathmaynotcontaincolon');
					}
				}
				else
				{
					$isbinddomain = $result['isbinddomain'];
					$zonefile = $result['zonefile'];
					$dkim = $result['dkim'];
					$specialsettings = $result['specialsettings'];
					$documentroot = $result['documentroot'];
				}

				if($userinfo['caneditphpsettings'] == '1'
				   || $userinfo['change_serversettings'] == '1')
				{
					$openbasedir = intval($_POST['openbasedir']);
					$safemode = intval($_POST['safemode']);

					if((int)$settings['system']['mod_fcgid'] == 1)
					{
						$phpsettingid = (int)$_POST['phpsettingid'];
						
						// done, not touched
						$phpsettingid_check = $db->query_first("SELECT * FROM `" . TABLE_PANEL_PHPCONFIGS . "` WHERE `id` = " . (int)$phpsettingid);

						if(!isset($phpsettingid_check['id'])
						   || $phpsettingid_check['id'] == '0'
						   || $phpsettingid_check['id'] != $phpsettingid)
						{
							standard_error('phpsettingidwrong');
						}

						$mod_fcgid_starter = validate($_POST['mod_fcgid_starter'], 'mod_fcgid_starter', '/^[0-9]*$/', '', array('-1', ''));
						$mod_fcgid_maxrequests = validate($_POST['mod_fcgid_maxrequests'], 'mod_fcgid_maxrequests', '/^[0-9]*$/', '', array('-1', ''));
					}
					else
					{
						$phpsettingid = $result['phpsettingid'];
						$mod_fcgid_starter = $result['mod_fcgid_starter'];
						$mod_fcgid_maxrequests = $result['mod_fcgid_maxrequests'];
					}
				}
				else
				{
					$openbasedir = $result['openbasedir'];
					$safemode = $result['safemode'];
					$phpsettingid = $result['phpsettingid'];
					$mod_fcgid_starter = $result['mod_fcgid_starter'];
					$mod_fcgid_maxrequests = $result['mod_fcgid_maxrequests'];
				}

				if($userinfo['ip'] != "-1")
				{
					// done, not touched
					$admin_ip = $db->query_first("SELECT `id`, `ip`, `port` FROM `" . TABLE_PANEL_IPSANDPORTS . "` WHERE `id`='" . (int)$userinfo['ip'] . "' ORDER BY `ip`, `port` ASC");
					$additional_ip_condition = ' AND `ip` = \'' . $admin_ip['ip'] . '\' ';
				}
				else
				{
					$additional_ip_condition = '';
				}

				$ipandport = intval($_POST['ipandport']);
				
				// done, not touched
				$ipandport_check = $db->query_first("SELECT `id`, `ip`, `port` FROM `" . TABLE_PANEL_IPSANDPORTS . "` WHERE `id` = '" . $db->escape($ipandport) . "' AND `ssl` = '0'" . $additional_ip_condition);

				if(!isset($ipandport_check['id'])
				   || $ipandport_check['id'] == '0'
				   || $ipandport_check['id'] != $ipandport)
				{
					standard_error('ipportdoesntexist');
				}

				if($settings['system']['use_ssl'] == "1"
				   && isset($_POST['ssl'])
				   /*&& isset($_POST['ssl_redirect'])*/
				   && isset($_POST['ssl_ipandport'])
				   && $_POST['ssl'] != '0')
				{
					$ssl = (int)$_POST['ssl'];
					$ssl_redirect = 0;
					if (isset($_POST['ssl_redirect'])) {
						$ssl_redirect = (int)$_POST['ssl_redirect'];
					}
					$ssl_ipandport = (int)$_POST['ssl_ipandport'];
					
					// done, not touched
					$ssl_ipandport_check = $db->query_first("SELECT `id`, `ip`, `port` FROM `" . TABLE_PANEL_IPSANDPORTS . "` WHERE `id` = '" . $db->escape($ssl_ipandport) . "' AND `ssl` = '1'" . $additional_ip_condition);

					if(!isset($ssl_ipandport_check['id'])
					   || $ssl_ipandport_check['id'] == '0'
					   || $ssl_ipandport_check['id'] != $ssl_ipandport)
					{
						standard_error('ipportdoesntexist');
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
					$documentroot = makeCorrectDir($documentroot);
				}

				if($openbasedir != '1')
				{
					$openbasedir = '0';
				}

				if($safemode != '1')
				{
					$safemode = '0';
				}

				if($isbinddomain != '1')
				{
					$isbinddomain = '0';
				}

				if($isemaildomain != '1')
				{
					$isemaildomain = '0';
				}

				if($email_only == '1')
				{
					$isemaildomain = '1';
				}
				else
				{
					$email_only = '0';
				}

				if($subcanemaildomain != '1'
				   && $subcanemaildomain != '2'
				   && $subcanemaildomain != '3')
				{
					$subcanemaildomain = '0';
				}

				if($dkim != '1')
				{
					$dkim = '0';
				}

				if($caneditdomain != '1')
				{
					$caneditdomain = '0';
				}

				$aliasdomain_check = array(
					'id' => 0
				);

				if($aliasdomain != 0)
				{
					// also check ip/port combination to be the same, #176
					// done
					$aliasdomain_check = $db->query_first('
						SELECT `d`.`id`
						FROM `' . TABLE_PANEL_DOMAINS . '` `d`,`users` `c`
						WHERE `d`.`customerid`=\'' . (int)$result['customerid'] . '\'
							AND `d`.`aliasdomain` IS NULL
							AND `d`.`id`<>`c`.`standardsubdomain`
							AND `c`.`id`=\'' . (int)$result['customerid'] . '\'
							AND `d`.`id`=\'' . (int)$aliasdomain . '\'
							AND `d`.`ipandport` = \''.(int)$ipandport.'\'');
				}

				if($aliasdomain_check['id'] != $aliasdomain)
				{
					standard_error('domainisaliasorothercustomer');
				}

				if($issubof <= '0')
				{
					$issubof = '0';
				}

				$params = array(
					'id' => $id,
					'page' => $page,
					'action' => $action,
					'customerid' => $customerid,
					'adminid' => $adminid,
					'documentroot' => $documentroot,
					'alias' => $aliasdomain,
					'isbinddomain' => $isbinddomain,
					'isemaildomain' => $isemaildomain,
					'email_only' => $email_only,
					'subcanemaildomain' => $subcanemaildomain,
					'caneditdomain' => $caneditdomain,
					'zonefile' => $zonefile,
					'dkim' => $dkim,
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
					'reallydisablesecuritysetting' => ($openbasedir == '0' && $userinfo['change_serversettings'] == '1'),
					'reallydocrootoutofcustomerroot' => (substr($documentroot, 0, strlen($customer['documentroot'])) != $customer['documentroot'] && !preg_match('/^https?\:\/\//', $documentroot))
				);
				foreach($security_questions as $question_name => $question_launch)
				{
					if($question_launch !== false)
					{
						$params[$question_name] = $question_name;

						if(!isset($_POST[$question_name])
						   || $_POST[$question_name] != $question_name)
						{
							ask_yesno('admin_domain_' . $question_name, $filename, $params);
							exit;
						}
					}
				}

				if($documentroot != $result['documentroot']
				   || $ipandport != $result['ipandport']
				   || $ssl != $result['ssl']
				   || $ssl_redirect != $result['ssl_redirect']
				   || $ssl_ipandport != $result['ssl_ipandport']
				   || $wwwserveralias != $result['wwwserveralias']
				   || $openbasedir != $result['openbasedir']
				   || $safemode != $result['safemode']
				   || $phpsettingid != $result['phpsettingid']
				   || $mod_fcgid_starter != $result['mod_fcgid_starter']
				   || $mod_fcgid_maxrequests != $result['mod_fcgid_maxrequests']
				   || $specialsettings != $result['specialsettings']
				   || $aliasdomain != $result['aliasdomain']
				   || $issubof != $result['ismainbutsubto']
				   || $email_only != $result['email_only'])
				{
					inserttask('1');
				}

				if($isbinddomain != $result['isbinddomain']
				   || $zonefile != $result['zonefile']
				   || $dkim != $result['dkim']
				   || $ipandport != $result['ipandport'])
				{
					inserttask('4');
				}

				if($isemaildomain == '0'
				   && $result['isemaildomain'] == '1')
				{
					// done, not touched
					$db->query("DELETE FROM `" . TABLE_MAIL_USERS . "` WHERE `domainid`='" . (int)$id . "' ");
					
					// done, not touched
					$db->query("DELETE FROM `" . TABLE_MAIL_VIRTUAL . "` WHERE `domainid`='" . (int)$id . "' ");
					$log->logAction(ADM_ACTION, LOG_NOTICE, "deleted domain #" . $id . " from mail-tables");
				}

				$updatechildren = '';

				if($subcanemaildomain == '0'
				   && $result['subcanemaildomain'] != '0')
				{
					$updatechildren = ', `isemaildomain`=\'0\' ';
				}
				elseif($subcanemaildomain == '3'
				       && $result['subcanemaildomain'] != '3')
				{
					$updatechildren = ', `isemaildomain`=\'1\' ';
				}

				if($customerid != $result['customerid']
				   && $settings['panel']['allow_domain_change_customer'] == '1')
				{
					// done, not touched
					$db->query("UPDATE `" . TABLE_MAIL_USERS . "` SET `customerid` = '" . (int)$customerid . "' WHERE `domainid` = '" . (int)$result['id'] . "' ");
					
					// done, not touched
					$db->query("UPDATE `" . TABLE_MAIL_VIRTUAL . "` SET `customerid` = '" . (int)$customerid . "' WHERE `domainid` = '" . (int)$result['id'] . "' ");
					
					// done
					$db->query("UPDATE `user_resources` SET `subdomains_used` = `subdomains_used` + '" . (int)$subdomains . "', `emails_used` = `emails_used` + '" . (int)$emails . "', `email_forwarders_used` = `email_forwarders_used` + '" . (int)$email_forwarders . "', `email_accounts_used` = `email_accounts_used` + '" . (int)$email_accounts . "' WHERE `id` = '" . (int)$customerid . "' ");
					
					// done
					$db->query("UPDATE `user_resources` SET `subdomains_used` = `subdomains_used` - '" . (int)$subdomains . "', `emails_used` = `emails_used` - '" . (int)$emails . "', `email_forwarders_used` = `email_forwarders_used` - '" . (int)$email_forwarders . "', `email_accounts_used` = `email_accounts_used` - '" . (int)$email_accounts . "' WHERE `id` = '" . (int)$result['customerid'] . "' ");
				}

				if($adminid != $result['adminid']
				   && $settings['panel']['allow_domain_change_admin'] == '1')
				{
					// done
					$db->query("UPDATE `user_resources_admin` SET `domains_used` = `domains_used` + 1 WHERE `id` = '" . (int)$adminid . "' ");
					
					// done
					$db->query("UPDATE `user_resources_admin` SET `domains_used` = `domains_used` - 1 WHERE `id` = '" . (int)$result['adminid'] . "' ");
				}

				$ssfs = isset($_POST['specialsettingsforsubdomains']) ? intval($_POST['specialsettingsforsubdomains']) : 1;
				if($ssfs == 1)
				{
					$upd_specialsettings = ", `specialsettings`='" . $db->escape($specialsettings) . "' ";
				}
				else
				{
					$upd_specialsettings = '';
					
					// done, not touched
					$db->query("UPDATE `" . TABLE_PANEL_DOMAINS . "` SET `specialsettings`='' WHERE `parentdomainid`='" . (int)$id . "'");
					$log->logAction(ADM_ACTION, LOG_INFO, "removed specialsettings on all subdomains of domain #" . $id);
				}

				// done, not touched
				$result = $db->query("UPDATE `" . TABLE_PANEL_DOMAINS . "` SET `customerid` = '" . (int)$customerid . "', `adminid` = '" . (int)$adminid . "', `documentroot`='" . $db->escape($documentroot) . "', `ipandport`='" . $db->escape($ipandport) . "', `ssl`='" . (int)$ssl . "', `ssl_redirect`='" . (int)$ssl_redirect . "', `ssl_ipandport`='" . (int)$ssl_ipandport . "', `aliasdomain`=" . (($aliasdomain != 0 && $alias_check == 0) ? '\'' . $db->escape($aliasdomain) . '\'' : 'NULL') . ", `isbinddomain`='" . $db->escape($isbinddomain) . "', `isemaildomain`='" . $db->escape($isemaildomain) . "', `email_only`='" . $db->escape($email_only) . "', `subcanemaildomain`='" . $db->escape($subcanemaildomain) . "', `dkim`='" . $db->escape($dkim) . "', `caneditdomain`='" . $db->escape($caneditdomain) . "', `zonefile`='" . $db->escape($zonefile) . "', `wwwserveralias`='" . $db->escape($wwwserveralias) . "', `openbasedir`='" . $db->escape($openbasedir) . "', `safemode`='" . $db->escape($safemode) . "', `phpsettingid`='" . $db->escape($phpsettingid) . "', `mod_fcgid_starter`='" . $db->escape($mod_fcgid_starter) . "', `mod_fcgid_maxrequests`='" . $db->escape($mod_fcgid_maxrequests) . "', `specialsettings`='" . $db->escape($specialsettings) . "', `registration_date`='" . $db->escape($registration_date) . "', `ismainbutsubto`='" . (int)$issubof . "' WHERE `id`='" . (int)$id . "'");
				
				// done, not touched
				$result = $db->query("UPDATE `" . TABLE_PANEL_DOMAINS . "` SET `customerid` = '" . (int)$customerid . "', `adminid` = '" . (int)$adminid . "', `ipandport`='" . $db->escape($ipandport) . "', `openbasedir`='" . $db->escape($openbasedir) . "', `safemode`='" . $db->escape($safemode) . "', `phpsettingid`='" . $db->escape($phpsettingid) . "', `mod_fcgid_starter`='" . $db->escape($mod_fcgid_starter) . "', `mod_fcgid_maxrequests`='" . $db->escape($mod_fcgid_maxrequests) . "'" . $upd_specialsettings . $updatechildren . " WHERE `parentdomainid`='" . (int)$id . "'");
				$log->logAction(ADM_ACTION, LOG_INFO, "edited domain #" . $id);
				$redirect_props = Array(
					'page' => $page,
					's' => $s
				);

				redirectTo($filename, $redirect_props);
			}
			else
			{
				if($settings['panel']['allow_domain_change_customer'] == '1')
				{
					$customers = '';
					
					// done TODO
					$result_customers = $db->query("
						SELECT `users`.`id` as `customerid`, `users`.`loginname`, `name`, `firstname`, `company`
						FROM `users`,`user_addresses`, `user_resources`
						WHERE (
								(`subdomains_used` + " . (int)$subdomains . " <= `subdomains` OR `subdomains` = '-1' )
								AND
								(`emails_used` + " . (int)$emails . " <= `emails` OR `emails` = '-1' )
								AND
								(`email_forwarders_used` + " . (int)$email_forwarders . " <= `email_forwarders`
									OR `email_forwarders` = '-1' )
								AND (`email_accounts_used` + " . (int)$email_accounts . " <= `email_accounts`
									OR `email_accounts` = '-1' )
							" . ($user->getData('resources', 'customers_see_all') ? '' : " AND `user2admin`.`userid` = '" . (int)$customerid . "'
									AND `user2admin`.`adminid` = '" . $user->getId() . "' ") . ")
							OR `users`.`id` = '" . (int)$result['customerid'] . "'
							ORDER BY `name` ASC");

					while($row_customer = $db->fetch_array($result_customers))
					{
						$customers.= makeoption(getCorrectFullUserDetails($row_customer) . ' (' . $row_customer['loginname'] . ')', $row_customer['customerid'], $result['customerid']);
					}
				}
				else
				{
					// done
					$customer = $db->query_first("
						SELECT `users`.`id` as `customerid`, `users`.`loginname`, `name`, `firstname`, `company`
						FROM `users`, `user_addresses`
						WHERE `users`.`id` = '" . (int)$result['customerid'] . "'
							AND `users`.`id` = `user_addresses`.`id`
						");
					$result['customername'] = getCorrectFullUserDetails($customer) . ' (' . $customer['loginname'] . ')';
				}

				if($user->getData('resources', 'customers_see_all') == '1')
				{
					if($settings['panel']['allow_domain_change_admin'] == '1')
					{
						$admins = '';
						
						// done
						$result_admins = $db->query("
							SELECT `users`.`id` as `adminid`, `users`.`loginname`, `user_addresses`.`name`
							FROM `user_resources_admin`, `users`, `user_addresses`
							WHERE `users`.`isadmin` = '1'
								AND `users`.`id` = `user_addresses`.`id`
								AND `user_resources_admin`.`id` = `users`.`id`
								AND (
										(`user_resources_admin`.`domains_used` < `user_resources_admin`.`domains`
											OR `user_resources_admin`.`domains` = '-1'
										)
										OR `users`.`id` = '" . (int)$result['adminid'] . "'
									)
							ORDER BY `name` ASC");

						while($row_admin = $db->fetch_array($result_admins))
						{
							$admins.= makeoption(getCorrectFullUserDetails($row_admin) . ' (' . $row_admin['loginname'] . ')', $row_admin['adminid'], $result['adminid']);
						}
					}
					else
					{
						// done
						$admin = $db->query_first("SELECT ``id` as `adminid`, `loginname`, `name` FROM `users` WHERE `id` = '" . (int)$result['adminid'] . "'");
						$result['adminname'] = getCorrectFullUserDetails($admin) . ' (' . $admin['loginname'] . ')';
					}
				}

				$result['domain'] = $idna_convert->decode($result['domain']);
				$domains = makeoption($lng['domains']['noaliasdomain'], 0, NULL, true);
				
				// done
				$result_domains = $db->query("
					SELECT `d`.`id`, `d`.`domain`
					FROM `" . TABLE_PANEL_DOMAINS . "` `d`, `users` `c`
					WHERE `d`.`aliasdomain` IS NULL
						AND `d`.`parentdomainid`=0
						AND `d`.`id`<>'" . (int)$result['id'] . "'
						AND `c`.`standardsubdomain`<>`d`.`id`
						AND `d`.`customerid`='" . (int)$result['customerid'] . "'
						AND `c`.`id` = `d`.`customerid`
					ORDER BY `d`.`domain` ASC");

				while($row_domain = $db->fetch_array($result_domains))
				{
					$domains.= makeoption($idna_convert->decode($row_domain['domain']), $row_domain['id'], $result['aliasdomain']);
				}

				$subtodomains = makeoption($lng['domains']['nosubtomaindomain'], 0, NULL, true);
				
				// done
				$result_domains = $db->query("
					SELECT `d`.`id`, `d`.`domain`
					FROM `" . TABLE_PANEL_DOMAINS . "` `d`, `users` `c`
					WHERE `d`.`aliasdomain` IS NULL
						AND `d`.`parentdomainid`=0
						AND `d`.`id`<>'" . (int)$result['id'] . "'
						AND `c`.`standardsubdomain`<>`d`.`id`
						AND `c`.`id` = `d`.`customerid`".
				($user->getData('resources', 'customers_see_all') ? '' : "
					AND `d`.`adminid` = '" . $user->getId() . "'") . "
				ORDER BY `d`.`domain` ASC");

				while($row_domain = $db->fetch_array($result_domains))
				{
					$subtodomains.= makeoption($idna_convert->decode($row_domain['domain']), $row_domain['id'], $result['ismainbutsubto']);
				}

				if($userinfo['ip'] == "-1")
				{
					// done, not touched
					$result_ipsandports = $db->query("SELECT `id`, `ip`, `port` FROM `" . TABLE_PANEL_IPSANDPORTS . "` WHERE `ssl`='0' ORDER BY `ip`, `port` ASC");
					
					// done, not touched
					$result_ssl_ipsandports = $db->query("SELECT `id`, `ip`, `port` FROM `" . TABLE_PANEL_IPSANDPORTS . "` WHERE `ssl`='1' ORDER BY `ip`, `port` ASC");
				}
				else
				{
					// done, not touched
					$admin_ip = $db->query_first("SELECT `id`, `ip`, `port` FROM `" . TABLE_PANEL_IPSANDPORTS . "` WHERE `id`='" . (int)$userinfo['ip'] . "' ORDER BY `ip`, `port` ASC");
					
					// done, not touched
					$result_ipsandports = $db->query("SELECT `id`, `ip`, `port` FROM `" . TABLE_PANEL_IPSANDPORTS . "` WHERE `ssl`='0' AND `ip`='" . $admin_ip['ip'] . "' ORDER BY `ip`, `port` ASC");
					
					// done, not touched
					$result_ssl_ipsandports = $db->query("SELECT `id`, `ip`, `port` FROM `" . TABLE_PANEL_IPSANDPORTS . "` WHERE `ssl`='1' AND `ip`='" . $admin_ip['ip'] . "' ORDER BY `ip`, `port` ASC");
				}

				$ipsandports = '';

				while($row_ipandport = $db->fetch_array($result_ipsandports))
				{
					if(filter_var($row_ipandport['ip'], FILTER_VALIDATE_IP, FILTER_FLAG_IPV6))
					{
						$row_ipandport['ip'] = '[' . $row_ipandport['ip'] . ']';
					}

					$ipsandports.= makeoption($row_ipandport['ip'] . ':' . $row_ipandport['port'], $row_ipandport['id'], $result['ipandport']);
				}

				$ssl_ipsandports = '';

				while($row_ssl_ipandport = $db->fetch_array($result_ssl_ipsandports))
				{
					if(filter_var($row_ssl_ipandport['ip'], FILTER_VALIDATE_IP, FILTER_FLAG_IPV6))
					{
						$row_ssl_ipandport['ip'] = '[' . $row_ssl_ipandport['ip'] . ']';
					}

					$ssl_ipsandports.= makeoption($row_ssl_ipandport['ip'] . ':' . $row_ssl_ipandport['port'], $row_ssl_ipandport['id'], $result['ssl_ipandport']);
				}

				$result['specialsettings'] = $result['specialsettings'];
				#$isbinddomain = makeyesno('isbinddomain', '1', '0', $result['isbinddomain']);
				#$wwwserveralias = makeyesno('wwwserveralias', '1', '0', $result['wwwserveralias']);
				#$isemaildomain = makeyesno('isemaildomain', '1', '0', $result['isemaildomain']);
				#$email_only = makeyesno('email_only', '1', '0', $result['email_only']);
				#$ssl = makeyesno('ssl', '1', '0', $result['ssl']);
				#$ssl_redirect = makeyesno('ssl_redirect', '1', '0', $result['ssl_redirect']);
				$subcanemaildomain = makeoption($lng['admin']['subcanemaildomain']['never'], '0', $result['subcanemaildomain'], true, true);
				$subcanemaildomain.= makeoption($lng['admin']['subcanemaildomain']['choosableno'], '1', $result['subcanemaildomain'], true, true);
				$subcanemaildomain.= makeoption($lng['admin']['subcanemaildomain']['choosableyes'], '2', $result['subcanemaildomain'], true, true);
				$subcanemaildomain.= makeoption($lng['admin']['subcanemaildomain']['always'], '3', $result['subcanemaildomain'], true, true);
				#$dkim = makeyesno('dkim', '1', '0', $result['dkim']);
				#$caneditdomain = makeyesno('caneditdomain', '1', '0', $result['caneditdomain']);
				#$openbasedir = makeyesno('openbasedir', '1', '0', $result['openbasedir']);
				#$safemode = makeyesno('safemode', '1', '0', $result['safemode']);
				$speciallogfile = ($result['speciallogfile'] == 1 ? $lng['panel']['yes'] : $lng['panel']['no']);
				$result['add_date'] = date('Y-m-d', $result['add_date']);

				$phpconfigs = '';
				
				// done, not touched
				$phpconfigs_result = $db->query("SELECT * FROM `" . TABLE_PANEL_PHPCONFIGS . "`");

				while($phpconfigs_row = $db->fetch_array($phpconfigs_result))
				{
					$phpconfigs.= makeoption($phpconfigs_row['description'], $phpconfigs_row['id'], $result['phpsettingid'], true, true);
				}

				#$specialsettingsforsubdomains = makeyesno('specialsettingsforsubdomains', '1', '0', '1');

				$result = htmlentities_array($result);

				$domain_edit_data = include_once dirname(__FILE__).'/lib/formfields/admin/domains/formfield.domains_edit.php';
				$domain_edit_form = htmlform::genHTMLForm($domain_edit_data);

				$title = $domain_edit_data['domain_edit']['title'];
				$image = $domain_edit_data['domain_edit']['image'];

				eval("echo \"" . getTemplate("domains/domains_edit") . "\";");
			}
		}
	}
	elseif($action == 'register') {
		/*
		 * 1. check if a domain is available
		 * 2. if so show the registration form
		 * 3. send register domain command and forward to add domain
		 *
		 * steps:
		 * 		null -> show domain search
		 * 				show registered domains from rrp account but not configured
		 * 		search
		 *
		 */
		
		if (!isset($_GET['step'])) {
			$title = 'Register Domain';
			$image = 'icons/domain_add.png';
			eval("echo \"" . getTemplate("domains/domains_search") . "\";");
			
		} else {
			$step = $_GET['step'];
			$rrp = new rrp($rrp_user, $rrp_password); // @todo currently these vars are set in userdata.inc
			
			if ($step == 'search') {
				try {
					$status = $rrp->domainCheck($_POST['domain']);
					
					if ($status == 210) {
						// domain is available
						$link = array("filename" => "admin_domains.php", "page" => "domains", "action" => "register", "step" => "register");
						$_SESSION['domain_register']['domain'] = $_POST['domain'];
						standard_success("Domain is available", "", $link);
					} else { // 211 - Domain name not available
						standard_error("Domain Check", "Domain is not available");
					}
				} catch(Exception $e) {
					standard_error("Domain Check", "Domain is not valid. "/*.$e->getMessage()*/);
				}
			} elseif ($step == 'register') {
				// create formfields
				$domain_register_form = htmlform::genHTMLForm($rrp->domainRegisterFormfield($_SESSION['domain_register']['domain']));
				
				$title = 'Register Domain';
				$image = 'icons/domain_add.png';

				eval("echo \"" . getTemplate("domains/domains_register") . "\";");
			} elseif ($step == 'done') {
				// send data
			}
		}
	}
}

?>
