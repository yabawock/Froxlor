<?php

class login
{
	public function login()
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

				redirectTo(Froxlor::getLinker()->getLink(array('showmessage' => '2')));
				exit;
			}

			// udpate available but not an admin?
			if(hasUpdates($version) && !$user->isAdmin()
				// or update available, is admin but has not the right to do the update
				|| hasUpdates($version) && $user->isAdmin() && !$user->getData("resources", "change_serversettings"))
			{
				return Froxlor::getSmarty()->fetch('login/login.tpl');
			}


			// too many attempts or temporary disabled?
			if($user->getData("general", "loginfail_count") >= $settings['login']['maxloginattempts']
				&& $user->getData("general", "lastlogin_fail") > (time() - $settings['login']['deactivatetime']))
			{
				redirectTo(Froxlor::getLinker()->getLink(array('showmessage' => '3')));;
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

			Froxlor::getSmarty()->template_dir = './templates/' . $theme . '/';

			if($settings['session']['allow_multiple_login'] != '1')
			{
				Froxlor::getDb()->query("DELETE FROM `" . TABLE_PANEL_SESSIONS . "` WHERE `userid` = '" . $user->getId() . "' AND `adminsession` = '" . $user->isAdmin() . "'");
			}

			$remote_addr = $_SERVER['REMOTE_ADDR'];

			if (empty($_SERVER['HTTP_USER_AGENT'])) {
				$http_user_agent = 'unknown';
			}
			else
			{
				$http_user_agent = $_SERVER['HTTP_USER_AGENT'];
			}
			Froxlor::getDb()->query("INSERT INTO `" . TABLE_PANEL_SESSIONS . "` (`hash`, `userid`, `ipaddress`, `useragent`, `lastactivity`, `language`, `adminsession`, `theme`) VALUES ('" . Froxlor::getDb()->escape($s) . "', '" . $user->getId(). "', '" . Froxlor::getDb()->escape($remote_addr) . "', '" . Froxlor::getDb()->escape($http_user_agent) . "', '" . time() . "', '" . Froxlor::getDb()->escape($language) . "', '" . Froxlor::getDb()->escape($user->isAdmin()) . "', '" . Froxlor::getDb()->escape($theme) . "')");

			Froxlor::getLinker()->add('s', $s);
			if($user->isAdmin())
			{
				if(hasUpdates($version))
				{
					redirectTo(Froxlor::getLinker()->getLink(array('area' => 'admin', 'section' => 'updates', 'action' => 'index')));
					exit;
				}
				else
				{
					redirectTo(Froxlor::getLinker()->getLink(array('area' => 'admin', 'section' => 'index')));
					exit;
				}
			}

			redirectTo(Froxlor::getLinker()->getLink(array('area' => 'customer', 'section' => 'index')));
			exit;
		}

		return Froxlor::getSmarty()->fetch('login/login.tpl');
	}

	public function logout()
	{
		Froxlor::getDb()->query("DELETE FROM `" . TABLE_PANEL_SESSIONS . "` WHERE `userid` = '" . Froxlor::getUser()->getId() . '"');
		Froxlor::getLinker()->delAll();
		Froxlor::getSmarty()->assign('loggedin', 0);
		redirectTo(Froxlor::getLinker()->getLink(array('area' => 'login', 'section' => 'login', 'action' => 'login', 's' => '')));
		return '';
	}
}