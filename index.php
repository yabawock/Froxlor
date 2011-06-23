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

define('AREA', 'login');

/**
 * Include our init.php, which manages Sessions, Language etc.
 */

require ("./lib/init.php");

if($action == '')
{
	$action = 'login';
}

if($action == 'login')
{
	if(isset($_POST['send'])
	&& $_POST['send'] == 'send')
	{
		$loginname = validate($_POST['loginname'], 'loginname');
		$password = validate($_POST['password'], 'password');

		try {
			$user = new user($loginname, $password);
			
			$user->setData("general", "lastlogin_succ", time());
			$user->setData("general", "loginfail_count", 0);
		}
		catch(Exception $e) {
			// login incorrect
			$user->setData("general", "lastlogin_fail", time());
			$user->setData("general", "loginfail_count", $user->getData("general", "loginfail_count")+1);
			
			redirectTo('index.php', Array('showmessage' => '2'), true);
			exit;
		}
		
		// udpate available but not an admin?
		if(hasUpdates($version) && !$user->isAdmin()
			// or update available, is admin but has not the right to do the update
			|| hasUpdates($version) && $user->isAdmin() && !$user->getData("resources", "change_serversettings"))
		{
			redirectTo('index.php');
			exit;
		}

		// too many attempts or temporary disabled?
		if($user->getData("general", "loginfail_count") >= $settings['login']['maxloginattempts']
			&& $user->getData("general", "lastlogin_fail") > (time() - $settings['login']['deactivatetime']))
		{
			redirectTo('index.php', Array('showmessage' => '3'), true);
			exit;
		}

		// create session id
		$s = md5(uniqid(microtime(), 1));

		// panel language
		if(isset($_POST['language']))
		{
			$language = validate($_POST['language'], 'language');

			if($language == 'profile')
			{
				$language =  $user->getData("general", "def_language");
			}
			elseif(!isset($languages[$language]))
			{
				$language = $settings['panel']['standardlanguage'];
			}
		}
		else
		{
			$language = $settings['panel']['standardlanguage'];
		}

		// theme selection
		$theme = $settings['panel']['default_theme'];
		if($user->getData("general", "theme") != '') {
			$theme = $user->getData("general", "theme");
		}

		if($settings['session']['allow_multiple_login'] != '1')
		{
			$db->query("DELETE FROM `" . TABLE_PANEL_SESSIONS . "` WHERE `userid` = '" . $user->getId() . "' AND `adminsession` = '" . $user->isAdmin() . "'");
		}
		
		$db->query("INSERT INTO `" . TABLE_PANEL_SESSIONS . "` (`hash`, `userid`, `ipaddress`, `useragent`, `lastactivity`, `language`, `adminsession`, `theme`) VALUES ('" . $db->escape($s) . "', '" . $user->getId(). "', '" . $db->escape($remote_addr) . "', '" . $db->escape($http_user_agent) . "', '" . time() . "', '" . $db->escape($language) . "', '" . $db->escape($user->isAdmin()) . "', '" . $db->escape($theme) . "')");
		

		if($user->isAdmin())
		{
			if(hasUpdates($version))
			{
				redirectTo('admin_updates.php', Array('s' => $s), true);
				exit;
			}
			else
			{
				redirectTo('admin_index.php', Array('s' => $s), true);
				exit;
			}
		}
		
		redirectTo('customer_index.php', Array('s' => $s), true);
		exit;
	}
	else
	{
		$language_options = '';
		$language_options.= makeoption($lng['login']['profile_lng'], 'profile', 'profile', true, true);

		while(list($language_file, $language_name) = each($languages))
		{
			$language_options.= makeoption($language_name, $language_file, 'profile', true);
		}

		$smessage = isset($_GET['showmessage']) ? (int)$_GET['showmessage'] : 0;
		$message = '';
		$successmessage = '';

		switch($smessage)
		{
			case 1:
				$successmessage = $lng['pwdreminder']['success'];
				break;
			case 2:
				$message = $lng['error']['login'];
				break;
			case 3:
				$message = $lng['error']['login_blocked'];
				break;
			case 4:
				$cmail = isset($_GET['customermail']) ? $_GET['customermail'] : 'unknown';
				$message = str_replace('%s', $cmail, $lng['error']['errorsendingmail']);
				break;
			case 5:
				$message = $lng['error']['user_banned'];
				break;
		}

		$update_in_progress = '';
		if(hasUpdates($version))
		{
			$update_in_progress = $lng['update']['updateinprogress_onlyadmincanlogin'];
		}

		eval("echo \"" . getTemplate("login") . "\";");
	}
}

if($action == 'forgotpwd')
{
	$adminchecked = false;
	$message = '';

	if(isset($_POST['send'])
	&& $_POST['send'] == 'send')
	{
		$loginname = validate($_POST['loginname'], 'loginname');
		$email = validateEmail($_POST['loginemail'], 'email');

		try
		{
			$user = new user(true, $loginname, $email);
			
			/* Check whether user is banned */
			if($user->isDeactivated())
			{
				$message = $lng['pwdreminder']['notallowed'];
				redirectTo('index.php', Array('showmessage' => '5'), true);
			}

			if(($user->isAdmin() && $settings['panel']['allow_preset_admin'] == '1')
				|| $user->isAdmin() == false)
			{
				if($user !== false)
				{
					if ($settings['panel']['password_min_length'] <= 6) {
						$password = substr(md5(uniqid(microtime(), 1)), 12, 6);
					} else {
						// make it two times larger than password_min_length
						$rnd = '';
						$minlength = $settings['panel']['password_min_length'];
						while (strlen($rnd) < ($minlength * 2))
						{
							$rnd .= md5(uniqid(microtime(), 1));
						}
						$password = substr($rnd, (int)($minlength / 2), $minlength);
					}

					$user->setData("general", "password", md5($password));

					$rstlog = FroxlorLogger::getInstanceOf(array('loginname' => 'password_reset'), $db, $settings);
					$rstlog->logAction(USR_ACTION, LOG_WARNING, "Password for user '" . $user->getData("general", "loginname") . "' has been reset!");

					$replace_arr = array(
						'SALUTATION' => getCorrectUserSalutation($user),
						'USERNAME' => $user->getData("general", "loginname"),
						'PASSWORD' => $password
					);

					$body = strtr($lng['pwdreminder']['body'], array('%s' => $user->getData("address", "firstname") . ' ' . $user->getData("address", "name"), '%p' => $password));

					$def_language = ($user->getData("general", "def_language")!= '') ? $user->getData("general", "def_language") : $settings['panel']['standardlanguage'];
					$result = $db->query_first('SELECT `value` FROM `' . TABLE_PANEL_TEMPLATES . '` WHERE `adminid`=\'' . (int)$user->getId() . '\' AND `language`=\'' . $db->escape($def_language) . '\' AND `templategroup`=\'mails\' AND `varname`=\'password_reset_subject\'');
					$mail_subject = html_entity_decode(replace_variables((($result['value'] != '') ? $result['value'] : $lng['pwdreminder']['subject']), $replace_arr));
					$result = $db->query_first('SELECT `value` FROM `' . TABLE_PANEL_TEMPLATES . '` WHERE `adminid`=\'' . (int)$user->getId()  . '\' AND `language`=\'' . $db->escape($def_language) . '\' AND `templategroup`=\'mails\' AND `varname`=\'password_reset_mailbody\'');
					$mail_body = html_entity_decode(replace_variables((($result['value'] != '') ? $result['value'] : $body), $replace_arr));
						
					$_mailerror = false;
					try {
						$mail->Subject = $mail_subject;
						$mail->AltBody = $mail_body;
						$mail->MsgHTML(str_replace("\n", "<br />", $mail_body));
						$mail->AddAddress($user->getData("address", "email"), $user->getData("address", "firstname") . ' ' . $user->getData("address", "name"));
						$mail->Send();
					} catch(phpmailerException $e) {
						$mailerr_msg = $e->errorMessage();
						$_mailerror = true;
					} catch (Exception $e) {
						$mailerr_msg = $e->getMessage();
						$_mailerror = true;
					}

					if ($_mailerror) {
						$rstlog = FroxlorLogger::getInstanceOf(array('loginname' => 'password_reset'), $db, $settings);
						$rstlog->logAction(ADM_ACTION, LOG_ERR, "Error sending mail: " . $mailerr_msg);
						redirectTo('index.php', Array('showmessage' => '4', 'customermail' => $user->getData("address", "email")), true);
						exit;
					}

					$mail->ClearAddresses();
					redirectTo('index.php', Array('showmessage' => '1'), true);
					exit;
				}
				else
				{
					$rstlog = FroxlorLogger::getInstanceOf(array('loginname' => 'password_reset'), $db, $settings);
					$rstlog->logAction(USR_ACTION, LOG_WARNING, "User '" . $loginname . "' tried to reset pwd but wasn't found in database!");
					$message = $lng['login']['combination_not_found'];
				}

				unset($user);
			}
		}
		catch(Exception $e)
		{
			$message = $lng['login']['usernotfound'];
		}
	}

	if($adminchecked)
	{
		if($settings['panel']['allow_preset_admin'] != '1')
		{
			$message = $lng['pwdreminder']['notallowed'];
			unset ($adminchecked);
		}
	}
	else
	{
		if($settings['panel']['allow_preset'] != '1')
		{
			$message = $lng['pwdreminder']['notallowed'];
		}
	}

	eval("echo \"" . getTemplate("fpwd") . "\";");
}
