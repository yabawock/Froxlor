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
class adminIndex
{
	/**
	 * index
	 *
	 * The dashboard for the administrator. It will show the current
	 * account status as well as the current system status.
	 * We also display the status of the cronjobs
	 * @return string The complete rendered body
	 */
	public function index()
	{
		#Froxlor::getLog()->logAction(ADM_ACTION, LOG_NOTICE, "viewed admin_index");

		// Get the global resource-usage of the customers this user is allowed to see
		// @TODO: Move to new users - table - structure
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

		// Convert traffic and diskspace - usage to GB / MB etc
		$overview['traffic_used'] = round($overview['traffic_used'] / (1024 * 1024), getSetting('panel', 'decimal_places'));
		$overview['diskspace_used'] = round($overview['diskspace_used'] / 1024, getSetting('panel', 'decimal_places'));
		$number_domains = Froxlor::getDb()->query_first("SELECT COUNT(*) AS `number_domains` FROM `" . TABLE_PANEL_DOMAINS . "` WHERE `parentdomainid`='0'" . (Froxlor::getUser()->getData('resources', 'customers_see_all') ? '' : " AND `adminid` = '" . (int)Froxlor::getUser()->getId() . "' "));
		$overview['number_domains'] = $number_domains['number_domains'];

		// Let smarty know about the resource - usage
		Froxlor::getSmarty()->assign('overview', $overview);

		// Get the PHP memorylimit
		$phpmemorylimit = @ini_get("memory_limit");

		// The memorylimit-setting is empty, it seems to be disabled
		if ($phpmemorylimit == "")
		{
			$phpmemorylimit = _('Disabled');
		}

		// Assign various system values to smarty
		Froxlor::getSmarty()->assign('phpmemorylimit', $phpmemorylimit);
		Froxlor::getSmarty()->assign('mysqlserverversion', mysql_get_server_info());
		Froxlor::getSmarty()->assign('mysqlclientversion', mysql_get_client_info());
		Froxlor::getSmarty()->assign('webserverinterface', strtoupper(@php_sapi_name()));
		Froxlor::getSmarty()->assign('phpversion', phpversion());

		// Do we want to search for the latest Froxlor - version?
		if((isset($_GET['lookfornewversion']) && $_GET['lookfornewversion'] == 'yes'))
		{
			// Construct the basic URL
			$update_check_uri = 'http://version.froxlor.org/Froxlor/legacy/' . Froxlor::getVersion();

			// We may use file to get the latest version
			if(ini_get('allow_url_fopen'))
			{
				// Fetch the file containing the latest version information of Froxlor
				$latestversion = @file($update_check_uri);

				// Was the retrival successful?
				if (isset($latestversion[0]))
				{
					// The file should be composed as follows:
					// <latest version>|<description of latest version>[|<link to announcement of latest version>]
					// The last part is only available if we do not have the latest version
					$latestversion = explode('|', $latestversion[0]);

					// Is the version - file wellformed?
					if(is_array($latestversion)
					&& count($latestversion) >= 1)
					{
						// Yes, it's wellformed, assign information to smarty
						Froxlor::getSmarty()->assign('lookfornewversion_lable', $latestversion[0]);
						Froxlor::getSmarty()->assign('lookfornewversion_addinfo', isset($latestversion[1]) ? $latestversion[1] : '');

						// If there was a link to the announcement, generate it, otherwise just link to the current page
						Froxlor::getSmarty()->assign('lookfornewversion_link', isset($latestversion[2]) ? $latestversion[2] : Froxlor::getLinker()->getLink(array('area' => 'admin', 'section' => 'index', 'action' => 'index', 'lookfornewversion' => 'yes')));

						// Now let's compare our version to the current version of Froxlor
						if (version_compare(Froxlor::getVersion(), $_version) == -1)
						{
							// There is a newer version if Froxklor available
							Froxlor::getSmarty()->assign('isnewerversion', 1);
						}
						else
						{
							// We are up-to-date, yay
							Froxlor::getSmarty()->assign('isnewerversion', 0);
						}
					}
					else
					{
						// The format of the file we got is invalid - redirect to the pretty page on the Froxlor server
						redirectTo($update_check_uri.'/pretty', NULL);
					}
				}
				else
				{
					// The retrival of the current Froxlor version failed - redirect to the pretty page on the Froxlor server
					redirectTo($update_check_uri.'/pretty', NULL);
				}
			}
			else
			{
				// allow_url_fopen is disabled - redirect to the pretty page on the Froxlor server
				redirectTo($update_check_uri.'/pretty', NULL);
			}
		}
		else
		{
			// No search for latest version, just show a link to the search
			Froxlor::getSmarty()->assign('lookfornewversion_lable', _('Search via webservice'));
			Froxlor::getSmarty()->assign('lookfornewversion_link', Froxlor::getLinker()->getLink(array('area' => 'admin', 'section' => 'index', 'action' => 'index', 'lookfornewversion' => 'yes')));
			Froxlor::getSmarty()->assign('lookfornewversion_addinfo', '');
			Froxlor::getSmarty()->assign('isnewerversion', 0);
		}

		// Reformat the used resources where necessary
		$userinfo['diskspace'] = round(Froxlor::getUser()->getData('resources', 'diskspace') / 1024, getSetting('panel', 'decimal_places'));
		$userinfo['diskspace_used'] = round(Froxlor::getUser()->getData('resources', 'diskspace_used') / 1024, getSetting('panel', 'decimal_places'));
		$userinfo['traffic'] = round(Froxlor::getUser()->getData('resources', 'traffic') / (1024 * 1024), getSetting('panel', 'decimal_places'));
		$userinfo['traffic_used'] = round(Froxlor::getUser()->getData('resources', 'traffic_used') / (1024 * 1024), getSetting('panel', 'decimal_places'));
		Froxlor::getSmarty()->assign('userinfo', $userinfo);

		// Fetch the latest data about the Froxlor cronjobs
		Froxlor::getSmarty()->assign('cron_last_runs', getCronjobsLastRun());
		Froxlor::getSmarty()->assign('outstanding_tasks', getOutstandingTasks());

		// Do we have tickets where the user needs to reply?
		$opentickets = 0;
		$opentickets = Froxlor::getDb()->query_first('SELECT COUNT(`id`) as `count` FROM `' . TABLE_PANEL_TICKETS . '`
	                                   WHERE `answerto` = "0" AND (`status` = "0" OR `status` = "1")
	                                   AND `lastreplier`="0" AND `adminid` = "' . Froxlor::getUser()->getId() . '"');
		Froxlor::getSmarty()->assign('awaitingtickets', $opentickets['count']);

		$awaitingtickets_text = '';
		if($opentickets > 0)
		{
			// There are unanswered tickets asking for attention, create a link to the ticket system for faster access
			$awaitingtickets_text = sprintf(_('You have %s unanswered support-ticket(s)'), '<a href="' . Froxlor::getLinker()->getLink(array('area' => 'admin', 'section' => 'tickets', 'page' => 'tickets')) . '">' . $opentickets['count'] . '</a>');
		}
		Froxlor::getSmarty()->assign('awaitingtickets_text', $awaitingtickets_text);

		// Try to get the system load via various functions
		if(function_exists('sys_getloadavg'))
		{
			$loadArray = sys_getloadavg();
			// Format the load according to the language we currently use
			$load = number_format($loadArray[0], 2, nl_langinfo(RADIXCHAR), nl_langinfo(THOUSEP)) . " / " . number_format($loadArray[1], 2, nl_langinfo(RADIXCHAR), nl_langinfo(THOUSEP)) . " / " . number_format($loadArray[2], 2, nl_langinfo(RADIXCHAR), nl_langinfo(THOUSEP));
		}
		else
		{
			$load = @file_get_contents('/proc/loadavg');

			if(!$load)
			{
				$load = _('not available');
			}
		}
		Froxlor::getSmarty()->assign('load', $load);

		// Now let's see what kernel the system currently uses using posix
		if(function_exists('posix_uname'))
		{
			Froxlor::getSmarty()->assign('showkernel', 1);
			$kernel_nfo = posix_uname();
			Froxlor::getSmarty()->assign('kernel', $kernel_nfo['release'] . ' (' . $kernel_nfo['machine'] . ')');
		}
		else
		{
			// We failed to get the kernel version since posix is not available
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
			// Some calculation to get a nicly formatted display
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

		// Render and return the current page
		return Froxlor::getSmarty()->fetch('admin/index/index.tpl');
	}

	/**
	 * changePassword
	 *
	 * Allow the administrator to change its password, either show
	 * the form or really change it
	 * @return string The rendered body
	 */
	public function changePassword()
	{
		return Froxlor::getSmarty()->fetch('admin/index/change_password.tpl');
	}

	public function changePasswordPost()
	{
		$old_password = validate($_POST['old_password'], 'old password');

		if(md5($old_password) != Froxlor::getUser()->getData('general', 'password'))
		{
			return standard_error(_('The old password is not correct'), '', array('area' => 'admin', 'section' => 'index', 'action' => 'changePassword'));
		}

		$new_password = validate($_POST['new_password'], 'new password');
		$new_password_confirm = validate($_POST['new_password_confirm'], 'new password confirm');

		if($old_password == '')
		{
			return standard_error(_('Missing input in field &quot;%s&quot;'), _('Old password'), array('area' => 'admin', 'section' => 'index', 'action' => 'changePassword'));
		}
		elseif($new_password == '')
		{
			return standard_error(_('Missing input in field &quot;%s&quot;'), _('New password'), array('area' => 'admin', 'section' => 'index', 'action' => 'changePassword'));
		}
		elseif($new_password_confirm == '')
		{
			return standard_error(_('Missing input in field &quot;%s&quot;'), _('New password (confirm)'), array('area' => 'admin', 'section' => 'index', 'action' => 'changePassword'));
		}
		elseif($new_password != $new_password_confirm)
		{
			return standard_error(_('New password and confirmation do not match'), '', array('area' => 'admin', 'section' => 'index', 'action' => 'changePassword'));
		}
		else
		{
			Froxlor::getUser()->setData('general', 'password', md5($new_password));
			#Froxlor::getLog()->logAction(ADM_ACTION, LOG_NOTICE, 'changed password');
			redirectTo(Froxlor::getLinker()->getLink(array('area' => 'admin', 'section' => 'index', 'action' => 'index')));
		}
	}

	/**
	 * change_language
	 *
	 * Allow the administrator to change its language,
	 * we only show working languages on the system
	 * @return string The rendered body
	 */
	public function changeLanguage()
	{
		$language_options = '';
		$languages = Froxlor::getLanguage()->getWorkingLanguages();

		$default_lang = getSetting('panel', 'standardlanguage');
		if(Froxlor::getUser()->getData('general', 'def_language') != '') {
			$default_lang = Froxlor::getUser()->getData('general', 'def_language');
		}

		while(list($language_file, $language_name) = each($languages))
		{
			$language_options.= makeoption($language_name, $language_file, $default_lang, true);
		}

		Froxlor::getSmarty()->assign('language_options', $language_options);
		return Froxlor::getSmarty()->fetch('admin/index/change_language.tpl');

	}

	public function changeLanguagePost()
	{
		$def_language = validate($_POST['def_language'], _('Language'));
		$languages = Froxlor::getLanguage()->getWorkingLanguages();

		if(isset($languages[$def_language]))
		{
			Froxlor::getDb()->query("UPDATE `users` SET `def_language`='" . Froxlor::getDb()->escape($def_language) . "' WHERE `id`='" . (int)Froxlor::getUser()->getId() . "'");
			Froxlor::getDb()->query("UPDATE `panel_sessions` SET `language`='" . Froxlor::getDb()->escape($def_language) . "' WHERE `userid`='" . Froxlor::getUser()->getId() . "'");
		}
		else
		{
			return standard_error(_('The selected language is not supported by this system'), '', array('area' => 'admin', 'section' => 'index', 'action' => 'changeLanguage'));
		}

		#Froxlor::getLog()->logAction(ADM_ACTION, LOG_NOTICE, "changed his/her default language to '" . $def_language . "'");
		redirectTo(Froxlor::getLinker()->getLink(array('area' => 'admin', 'section' => 'index', 'action' => 'index')));
	}


	/**
	 * change_theme
	 *
	 * Allow the administrator to change the used theme.
	 * @return string The rendered body
	 */
	public function changeTheme()
	{
		$theme_options = '';

		$default_theme = getSetting('panel', 'default_theme');
		if(Froxlor::getUser()->getData('general', 'theme') != '') {
			$default_theme = Froxlor::getUser()->getData('general', 'theme');
		}

		$themes_avail = getThemes();
		foreach($themes_avail as $t)
		{
			$theme_options.= makeoption($t, $t, $default_theme, true);
		}

		Froxlor::getSmarty()->assign('theme_options', $theme_options);
		return Froxlor::getSmarty()->fetch('admin/index/change_theme.tpl');
	}

	public function changeThemePost()
	{
		$theme = validate($_POST['theme'], _('Theme'));

		if (!in_array($theme, getThemes()))
		{
			return standard_error(_('The selected theme does not exist'), '', array('area' => 'admin', 'section' => 'index', 'action' => 'changeTheme'));
		}

		$db->query("UPDATE `users` SET `theme`='" . $db->escape($theme) . "' WHERE `id`='" . (int)Froxlor::getUser()->getId() . "'");
		$db->query("UPDATE `panel_sessions` SET `theme`='" . Froxlor::getDb()->escape($theme) . "' WHERE `hash`='" . Froxlor::getDb()->escape($s) . "'");

		#Froxlor::getLog()->logAction(ADM_ACTION, LOG_NOTICE, "changed his/her theme to '" . $theme . "'");
		redirectTo(Froxlor::getLinker()->getLink(array('area' => 'admin', 'section' => 'index', 'action' => 'index')));
	}
}