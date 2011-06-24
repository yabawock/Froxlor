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
			$where = " AND `u2a`.`adminid` = '" . Froxlor::getUser()->getId() . "'";
		}
		$result = Froxlor::getDb()->query("SELECT `c`.*, `r`.* FROM `users` AS c, `user_resources` AS r, `user2admin` AS u2a WHERE `c`.`id` = `r`.`id`" . $where);
		$customers = array();
		$maxdisk = 0;
		$maxtraffic = 0;
		while($row = Froxlor::getDb()->fetch_array($result))
		{
			$domains = Froxlor::getDb()->query_first("SELECT COUNT(`id`) AS `domains` " . "FROM `panel_domains` WHERE `customerid`='" . (int)$row['id'] . "' AND `parentdomainid`='0' AND `id`<> '" . (int)$row['standardsubdomain'] . "'");
			$handle = Froxlor::getDb()->query_first("SELECT `h`.* FROM `domain_handle` AS h, `user2handle` AS u2h WHERE `h`.`handleid` = `u2h`.`handleid` AND `u2h`.`userid` = '" . (int)$row['id'] . "'");
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
		$result = Froxlor::getDb()->query_first("SELECT `u`.`loginname` FROM `users` u, `user2admin` u2a WHERE `id`='" . $id . "' " . (Froxlor::getUser()->getData('resource', 'customers_see_all') ? '' : " AND `u2a`.`userid` = '" . $id . "' AND `u2a`.`adminid` = '" . Froxlor::getUser()->getId() . "'"));
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
		else
		{
			return standard_error(_('You are either not allowed to incorporate this customer or this customer does not exist'), '', array('area' => 'admin', 'section' => 'customers', 'action' => 'index'));
		}
	}
}