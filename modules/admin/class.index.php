<?php

class index
{
	public function index()
	{
		$settings = Froxlor::getSettings();
		#Froxlor::getLog()->logAction(ADM_ACTION, LOG_NOTICE, "viewed admin_index");
		$overview = Froxlor::getDb()->query_first("SELECT COUNT(*) AS `number_customers`,
					SUM(`diskspace_used`) AS `diskspace_used`,
					SUM(`mysqls_used`) AS `mysqls_used`,
					SUM(`emails_used`) AS `emails_used`,
					SUM(`email_accounts_used`) AS `email_accounts_used`,
					SUM(`email_forwarders_used`) AS `email_forwarders_used`,
					SUM(`email_quota_used`) AS `email_quota_used`,
					SUM(`email_autoresponder_used`) AS `email_autoresponder_used`,
					SUM(`ftps_used`) AS `ftps_used`,
					SUM(`tickets_used`) AS `tickets_used`,
					SUM(`subdomains_used`) AS `subdomains_used`,
					SUM(`traffic_used`) AS `traffic_used`,
					SUM(`aps_packages_used`) AS `aps_packages_used`
					FROM `" . TABLE_PANEL_CUSTOMERS . "`" . (Froxlor::getUser()->getData('resources', 'customers_see_all') ? '' : " WHERE `adminid` = '" . (int)Froxlor::getUser()->getId() . "' "));

		$overview['traffic_used'] = round($overview['traffic_used'] / (1024 * 1024), $settings['panel']['decimal_places']);
		$overview['diskspace_used'] = round($overview['diskspace_used'] / 1024, $settings['panel']['decimal_places']);
		$number_domains = Froxlor::getDb()->query_first("SELECT COUNT(*) AS `number_domains` FROM `" . TABLE_PANEL_DOMAINS . "` WHERE `parentdomainid`='0'" . (Froxlor::getUser()->getData('resources', 'customers_see_all') ? '' : " AND `adminid` = '" . (int)Froxlor::getUser()->getId() . "' "));
		$overview['number_domains'] = $number_domains['number_domains'];
		$phpmemorylimit = @ini_get("memory_limit");

		if($phpmemorylimit == "")
		{
			$phpmemorylimit = $lng['admin']['memorylimitdisabled'];
		}

		Froxlor::getSmarty()->assign('phpmemorylimit', $phpmemorylimit);
		Froxlor::getSmarty()->assign('mysqlserverversion', mysql_get_server_info());
		Froxlor::getSmarty()->assign('mysqlclientversion', mysql_get_client_info());
		Froxlor::getSmarty()->assign('webserverinterface', strtoupper(@php_sapi_name()));
		Froxlor::getSmarty()->assign('phpversion', phpversion());
		Froxlor::getSmarty()->assign('overview', $overview);

		if((isset($_GET['lookfornewversion']) && $_GET['lookfornewversion'] == 'yes'))
		{
			$update_check_uri = 'http://version.froxlor.org/Froxlor/legacy/' . $version;

			if(ini_get('allow_url_fopen'))
			{
				$latestversion = @file($update_check_uri);

				if (isset($latestversion[0]))
				{
					$latestversion = explode('|', $latestversion[0]);

					if(is_array($latestversion)
					&& count($latestversion) >= 1)
					{
						$_version = $latestversion[0];
						$_message = isset($latestversion[1]) ? $latestversion[1] : '';
						$_link = isset($latestversion[2]) ? $latestversion[2] : Froxlor::getLinker()->getLink(array('page' => $page, 'lookfornewversion' => 'yes'));

						Froxlor::getSmarty()->assign('lookfornewversion_lable', $_version);
						Froxlor::getSmarty()->assign('lookfornewversion_link', $_link);
						Froxlor::getSmarty()->assign('lookfornewversion_addinfo', $_message);

						if (version_compare($version, $_version) == -1)
						{
							Froxlor::getSmarty()->assign('isnewerversion', 1);
						}
						else
						{
							Froxlor::getSmarty()->assign('isnewerversion', 0);
						}
					}
					else
					{
						redirectTo($update_check_uri.'/pretty', NULL);
					}
				}
				else
				{
					redirectTo($update_check_uri.'/pretty', NULL);
				}
			}
			else
			{
				redirectTo($update_check_uri.'/pretty', NULL);
			}
		}
		else
		{
			Froxlor::getSmarty()->assign('lookfornewversion_lable', _('search via webservice'));
			Froxlor::getSmarty()->assign('lookfornewversion_link', Froxlor::getLinker()->getLink(array('area' => 'admin', 'section' => 'index', 'action' => 'index', 'lookfornewversion' => 'yes')));
			Froxlor::getSmarty()->assign('lookfornewversion_addinfo', '');
			Froxlor::getSmarty()->assign('isnewerversion', 0);
		}

		$userinfo['customers'] = Froxlor::getUser()->getData('resources', 'customers');
		$userinfo['domains'] = Froxlor::getUser()->getData('resources', 'domains');
		$userinfo['diskspace'] = round(Froxlor::getUser()->getData('resources', 'diskspace') / 1024, $settings['panel']['decimal_places']);
		$userinfo['diskspace_used'] = round(Froxlor::getUser()->getData('resources', 'diskspace_used') / 1024, $settings['panel']['decimal_places']);
		$userinfo['traffic'] = round(Froxlor::getUser()->getData('resources', 'traffic') / (1024 * 1024), $settings['panel']['decimal_places']);
		$userinfo['traffic_used'] = round(Froxlor::getUser()->getData('resources', 'traffic_used') / (1024 * 1024), $settings['panel']['decimal_places']);
		$userinfo['mysqls'] = Froxlor::getUser()->getData('resources', 'mysqls');
		$userinfo['mysqls_used'] = Froxlor::getUser()->getData('resources', 'mysqls_used');
		$userinfo['emails'] = Froxlor::getUser()->getData('resources', 'emails');
		$userinfo['emails_used'] = Froxlor::getUser()->getData('resources', 'emails_used');
		$userinfo['email_accounts'] = Froxlor::getUser()->getData('resources', 'email_accounts');
		$userinfo['email_accounts_used'] = Froxlor::getUser()->getData('resources', 'email_accounts_used');
		$userinfo['email_forwarders'] = Froxlor::getUser()->getData('resources', 'email_forwarders');
		$userinfo['email_forwarders_used'] = Froxlor::getUser()->getData('resources', 'email_forwarders_used');
		$userinfo['email_quota'] = Froxlor::getUser()->getData('resources', 'email_quota');
		$userinfo['email_quota_used'] = Froxlor::getUser()->getData('resources', 'email_quota_used');
		$userinfo['email_autoresponder'] = Froxlor::getUser()->getData('resources', 'email_autoresponder');
		$userinfo['email_autoresponder_used'] = Froxlor::getUser()->getData('resources', 'email_autoresponder_used');
		$userinfo['ftps'] = Froxlor::getUser()->getData('resources', 'ftps');
		$userinfo['ftps_used'] = Froxlor::getUser()->getData('resources', 'ftps_used');
		$userinfo['tickets'] = Froxlor::getUser()->getData('resources', 'tickets');
		$userinfo['tickets_used'] = Froxlor::getUser()->getData('resources', 'tickets_used');
		$userinfo['subdomains'] = Froxlor::getUser()->getData('resources', 'subdomains');
		$userinfo['subdomains_used'] = Froxlor::getUser()->getData('resources', 'subdomains_used');
		$userinfo['aps_packages'] = Froxlor::getUser()->getData('resources', 'aps_packages');
		$userinfo['aps_packages_used'] = Froxlor::getUser()->getData('resources', 'aps_packages_used');
		$userinfo = str_replace_array('-1', _('&infin;'), $userinfo, 'customers domains diskspace traffic mysqls emails email_accounts email_forwarders email_quota email_autoresponder ftps tickets subdomains aps_packages');

		Froxlor::getSmarty()->assign('userinfo', $userinfo);
		$cronjobsLastRun = getCronjobsLastRun();
		Froxlor::getSmarty()->assign('cron_last_runs', getCronjobsLastRun());
		$outstandingTasks = getOutstandingTasks();
		Froxlor::getSmarty()->assign('outstanding_tasks', getOutstandingTasks());

		$opentickets = 0;
		$opentickets = Froxlor::getDb()->query_first('SELECT COUNT(`id`) as `count` FROM `' . TABLE_PANEL_TICKETS . '`
	                                   WHERE `answerto` = "0" AND (`status` = "0" OR `status` = "1")
	                                   AND `lastreplier`="0" AND `adminid` = "' . Froxlor::getUser()->getId() . '"');
		Froxlor::getSmarty()->assign('awaitingtickets', $opentickets['count']);
		$awaitingtickets_text = '';

		if($opentickets > 0)
		{
			$awaitingtickets_text = sprintf(_('You have %s unanswered support-ticket(s)'), '<a href="' . Froxlor::getLinker()->getLink(array('area' => 'admin', 'section' => 'tickets', 'page' => 'tickets')) . '">' . $opentickets['count'] . '</a>');
		}
		Froxlor::getSmarty()->assign('awaitingtickets_text', $awaitingtickets_text);

		if(function_exists('sys_getloadavg'))
		{
			$loadArray = sys_getloadavg();
			$load = number_format($loadArray[0], 2, '.', '') . " / " . number_format($loadArray[1], 2, '.', '') . " / " . number_format($loadArray[2], 2, '.', '');
		}
		else
		{
			$load = @file_get_contents('/proc/loadavg');

			if(!$load)
			{
				$load = $lng['admin']['noloadavailable'];
			}
		}
		Froxlor::getSmarty()->assign('load', $load);

		if(function_exists('posix_uname'))
		{
			Froxlor::getSmarty()->assign('showkernel', 1);
			$kernel_nfo = posix_uname();
			Froxlor::getSmarty()->assign('kernel', $kernel_nfo['release'] . ' (' . $kernel_nfo['machine'] . ')');
		}
		else
		{
			Froxlor::getSmarty()->assign('showkernel', 0);
			Froxlor::getSmarty()->assign('kernel', '');
		}

		// Try to get the uptime
		// First: With exec (let's hope it's enabled for the Froxlor - vHost)

		$uptime_array = explode(" ", @file_get_contents("/proc/uptime"));

		if(is_array($uptime_array)
		&& isset($uptime_array[0])
		&& is_numeric($uptime_array[0]))
		{
			// Some calculatioon to get a nicly formatted display

			$seconds = round($uptime_array[0], 0);
			$minutes = $seconds / 60;
			$hours = $minutes / 60;
			$days = floor($hours / 24);
			$hours = floor($hours - ($days * 24));
			$minutes = floor($minutes - ($days * 24 * 60) - ($hours * 60));
			$seconds = floor($seconds - ($days * 24 * 60 * 60) - ($hours * 60 * 60) - ($minutes * 60));
			Froxlor::getSmarty()->assign('uptime', "{$days}d, {$hours}h, {$minutes}m, {$seconds}s");

			// Just cleanup

			unset($uptime_array, $seconds, $minutes, $hours, $days);
		}
		else
		{
			// Nothing of the above worked, show an error :/

			Froxlor::getSmarty()->assign('uptime', '');
		}

		return Froxlor::getSmarty()->fetch('admin/index/index.tpl');
	}
}