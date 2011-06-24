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
 * @author     Froxlor team <team@froxlor.org> (2010-)
 * @license    GPLv2 http://files.froxlor.org/misc/COPYING.txt
 * @package    Core
 *
 */

class Loader
{
	private $area = 'login';
	private $section = 'login';
	private $action = 'login';

	public function __construct()
	{
		$this->initialize();
		$this->execute();
	}

	private function initialize()
	{
		header("Content-Type: text/html; charset=iso-8859-1");

		// prevent Froxlor pages from being cached
		header("Cache-Control: no-store, no-cache, must-revalidate");
		header("Pragma: no-cache");
		header('Last-Modified: ' . gmdate( 'D, d M Y H:i:s \G\M\T', time()));
		header('Expires: ' . gmdate( 'D, d M Y H:i:s \G\M\T', time()));

		// Prevent inline - JS to be executed (i.e. XSS) in browsers which support this,
		// Inline-JS is no longer allowed and used
		// See: http://people.mozilla.org/~bsterne/content-security-policy/index.html
		header("X-Content-Security-Policy: allow 'self'; frame-ancestors 'none'");

		// Don't allow to load Froxlor in an iframe to prevent i.e. clickjacking
		header('X-Frame-Options: DENY');

		// If Froxlor was called via HTTPS -> enforce it for the next time
		if(isset( $_SERVER['HTTPS']) && (strtolower($_SERVER['HTTPS']) != 'off' ))
		{
			header('Strict-Transport-Security: max-age=500');
		}

		// Internet Explorer shall not guess the Content-Type, see:
		// http://blogs.msdn.com/ie/archive/2008/07/02/ie8-security-part-v-comprehensive-protection.aspx
		header('X-Content-Type-Options: nosniff' );

		// ensure that default timezone is set
		if(function_exists("date_default_timezone_set") && function_exists("date_default_timezone_get"))
		{
			@date_default_timezone_set(@date_default_timezone_get());
		}

		if(!file_exists('./lib/userdata.inc.php'))
		{
			$config_hint = file_get_contents('./templates/Froxlor/misc/configurehint.tpl');
			die($config_hint);
		}

		if(!is_readable('./lib/userdata.inc.php'))
		{
			die('You have to make the file "./lib/userdata.inc.php" readable for the http-process!');
		}

		require ('./lib/userdata.inc.php');

		if(!isset($sql) || !is_array($sql))
		{
			$config_hint = file_get_contents('./templates/Froxlor/misc/configurehint.tpl');
			die($config_hint);
		}

		require ('./lib/tables.inc.php');

		Froxlor::addObject('db', new db($sql['host'], $sql['user'], $sql['password'], $sql['db']));
		unset($sql['password']);
		unset(Froxlor::getDb()->password);

		Tools::cleanHeader();
		/**
		 * Selects settings from MySQL-Table
		 */

		$settings_data = loadConfigArrayDir('./actions/admin/settings/');
		$settings = loadSettings($settings_data, Froxlor::getDb());

		Froxlor::addObject('settings', $settings);
		Froxlor::addObject('version', $version);
		$remote_addr = $_SERVER['REMOTE_ADDR'];

		if (empty($_SERVER['HTTP_USER_AGENT'])) {
			$http_user_agent = 'unknown';
		}
		else
		{
			$http_user_agent = $_SERVER['HTTP_USER_AGENT'];
		}
		unset($userinfo);
		unset($userid);
		unset($customerid);
		unset($adminid);
		unset($s);

		if(isset($_POST['s']))
		{
			$s = $_POST['s'];
			$nosession = 0;
		}
		elseif(isset($_GET['s']))
		{
			$s = $_GET['s'];
			$nosession = 0;
		}
		else
		{
			$s = '';
			$nosession = 1;
		}

		$timediff = time() - $settings['session']['sessiontimeout'];
		Froxlor::getDb()->query('DELETE FROM `' . TABLE_PANEL_SESSIONS . '` WHERE `lastactivity` < "' . (int)$timediff . '"');

		if(isset($s)
		   && $s != ""
		   && $nosession != 1)
		{
			ini_set("session.name", "s");
			ini_set("url_rewriter.tags", "");
			ini_set("session.use_cookies", false);
			session_id($s);
			session_start();
			$query = 'SELECT `s`.* FROM `' . TABLE_PANEL_SESSIONS . '` `s` ';

			$query.= 'WHERE `s`.`hash`="' . Froxlor::getDb()->escape($s) . '" AND `s`.`ipaddress`="' . Froxlor::getDb()->escape($remote_addr) . '" AND `s`.`useragent`="' . Froxlor::getDb()->escape($http_user_agent) . '" AND `s`.`lastactivity` > "' . (int)$timediff . '"';
			$userinfo = Froxlor::getDb()->query_first($query);
			if (isset($userinfo['userid']) && (int)$userinfo['userid'] != 0)
			{
				try
				{
					$user = new user((int)$userinfo['userid']);
					if ($user->isDeactivated())
					{
						$nosession = 1;
						$s = '';
					}
					else
					{
						Froxlor::addObject('user', $user);
						$userinfo['newformtoken'] = strtolower(md5(uniqid(microtime(), 1)));
						$query = 'UPDATE `' . TABLE_PANEL_SESSIONS . '` SET `lastactivity`="' . time() . '", `formtoken`="' . $userinfo['newformtoken'] . '" WHERE `hash`="' . Froxlor::getDb()->escape($s) . '"';
						Froxlor::getDb()->query($query);
						$nosession = 0;
					}
				}
				catch(Exception $e)
				{
					$nosession = 1;
					$s = '';
				}
			}
			else
			{
				$nosession = 1;
				$s = '';
			}
		}
		else
		{
			$nosession = 1;
		}

		/**
		 * global Theme-variable
		 */
		$theme = isset($settings['panel']['default_theme']) ? $settings['panel']['default_theme'] : 'Froxlor';

		/**
		 * overwrite with customer/admin theme if defined
		 */
		if(isset($userinfo['theme']) && $userinfo['theme'] != $theme)
		{
			$theme = $userinfo['theme'];
		}

		# Set default options for template
		$image_path = 'images/'.$theme;
		$header_logo = $image_path.'/logo.png';
		if(file_exists($image_path.'/logo_custom.png'))
		{
			$header_logo = $image_path.'/logo_custom.png';
		}

		/**
		 * Redirects to login page if no session exists
		 */
		if($nosession == 1)
		{
			unset($userinfo);
			unset($_POST['area']);
			unset($_POST['section']);
			unset($_POST['action']);
			unset($_GET['area']);
			unset($_GET['section']);
			unset($_GET['action']);
		}

		// Look if we got an area in POST, if not look in GET, otherwise set it to empty
		$this->area = (isset($_POST['area']) ? $_POST['area'] : (isset($_GET['area']) ? $_GET['area'] : 'login'));
		// Look if we got a section in POST, if not look in GET, otherwise set it to index
		$this->section = (isset($_POST['section']) ? $_POST['section'] : (isset($_GET['section']) ? $_GET['section'] : 'login'));

		if(isset($_POST['action']))
		{
			$this->action = $_POST['action'];
		}
		elseif(isset($_GET['action']))
		{
			$this->action = $_GET['action'];
		}
		else
		{
			$this->action = '';
			// clear request data
			if (isset($_SESSION))
			{
				unset($_SESSION['requestData']);
			}
		}

		if (($this->area == 'admin' && !$user->isAdmin()) || ($this->area == 'customer' && $user->isAdmin()))
		{
			$this->area = 'login';
			$this->section = 'login';
			$this->action = '';
		}

		/**
		 * Logic moved out of lng-file
		 */
		if(Froxlor::getUser() !== false)
		{
			/**
			 * Initialize logging
			 */
			#Froxlor::addObject('log', FroxlorLogger::getInstanceOf($userinfo, Froxlor::getDb(), Froxlor::getSettings()));
		}
		# Initialize Smarty
		Froxlor::addObject('smarty', new Smarty());
		Froxlor::addObject('linker', new linker('index2.php', $s));
		Froxlor::getSmarty()->assign('linker', Froxlor::getLinker());
		Froxlor::getSmarty()->template_dir = './templates/' . $theme . '/';
		Froxlor::getSmarty()->compile_dir  = './templates_c/';
		Froxlor::getSmarty()->cache_dir    = './cache/';
		Froxlor::getSmarty()->registerPlugin("function", "link", "smarty_function_create_link");
		Froxlor::getSmarty()->registerFilter('pre', 'smarty_prefilter_t');
		Froxlor::getSmarty()->registerFilter('post', 'smarty_postfilter_t');

		# Set the language
		Froxlor::addObject('language', new languageSelect());
		Froxlor::getLanguage()->useBrowser = true;
		Froxlor::getLanguage()->setLanguage();

		# Activate gettext for smarty;
		define('HAVE_GETTEXT', true);

		Froxlor::getSmarty()->assign('header_logo', $header_logo);
		Froxlor::getSmarty()->assign('settings', $settings);
		Froxlor::getSmarty()->assign('loggedin', !$nosession);
		Froxlor::getSmarty()->assign('current_year', date('Y'));
		Froxlor::getSmarty()->assign('image_folder', $image_path);
		Froxlor::getSmarty()->assign('title', '');
		Froxlor::getSmarty()->assign('version', $version);
		Froxlor::getSmarty()->assign('branding', $branding);
		Froxlor::getSmarty()->assign('user', Froxlor::getUser());
		Froxlor::getSmarty()->debugging = true;
		/**
		 * Fills variables for navigation, header and footer
		 */
		if($this->area == 'admin' || $this->area == 'customer')
		{
			if(hasUpdates($version))
			{
				/*
				 * if froxlor-files have been updated
				 * but not yet configured by the admin
				 * we only show logout and the update-page
				 */
				$navigation_data = array (
					'admin' => array (
						'index' => array (
							'url' => Froxlor::getLinker()->getLink(array('area' => 'admin', 'section' => 'index', 'action' => 'index')),
							'label' => _('Overview'),
							'elements' => array (
								array (
									'label' => sprintf(_('Logged in as: %s'), Froxlor::getUser()->getLoginname()),
								),
								array (
									'url' => Froxlor::getLinker()->getLink(array('area' => 'login', 'section' => 'logout')),
									'label' => _('Logout'),
								),
							),
						),
						'server' => array (
							'label' => _('Server'),
							'required_resources' => 'change_serversettings',
							'elements' => array (
								array (
									'url' => Froxlor::getLinker()->getLink(array('area' => 'admin', 'section' => 'updates', 'action' => 'index', 'page' => 'overview')),
									'label' => _('Froxlor update'),
									'required_resources' => 'change_serversettings',
								),
							),
						),
					),
				);
				$navigation = buildNavigation($navigation_data['admin'], $userinfo);
			}
			else
			{
				$navigation_data = loadConfigArrayDir('./lib/navigation/');
				$navigation = buildNavigation($navigation_data[($user->isAdmin() ? 'admin' : 'customer')], $userinfo);
			}
			unset($navigation_data);
			Froxlor::getSmarty()->assign('navigation', $navigation);
		}

		/**
		 * Initialize the mailingsystem
		 */
		$mail = new PHPMailer(true);
		if(PHPMailer::ValidateAddress($settings['panel']['adminmail']) !== false)
		{
			// set return-to address and custom sender-name, see #76
			$mail->SetFrom($settings['panel']['adminmail'], $settings['panel']['adminmail_defname']);
			if ($settings['panel']['adminmail_return'] != '') {
				$mail->AddReplyTo($settings['panel']['adminmail_return'], $settings['panel']['adminmail_defname']);
			}
		}
	}

	private function execute()
	{
		$area = $this->area;
		$section = $this->section;
		$action = $this->action;
		if (!preg_match('/^[a-z0-9]+$/i', $area) || !preg_match('/^[a-z0-9]+$/i', $section) || !preg_match('/^[a-z0-9]+$/i', $action))
		{
			$area = $section = $action = 'login';
			Froxlor::getSmarty()->assign('loggedin', 0);
		}
		if (!file_exists("./modules/$area/class.$section.php"))
		{
			$area = $section = $action = 'login';
			Froxlor::getSmarty()->assign('loggedin', 0);
		}
		if (!is_readable("./modules/$area/class.$section.php"))
		{
			$area = $section = $action = 'login';
			Froxlor::getSmarty()->assign('loggedin', 0);
		}
		require_once("./modules/$area/class.$section.php");
		$module = new $section;
		if(!method_exists($module, $action))
		{
			$action = 'overview';
		}
		$body = $module->$action();
		Froxlor::getSmarty()->assign('body', $body);
		Froxlor::getSmarty()->display('index.tpl');
	}
}