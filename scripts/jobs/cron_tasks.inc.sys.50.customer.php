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
 * @package    Cron
 *
 */

/*
 * This script creates the php.ini's used by mod_suPHP+php-cgi
 */

if(@php_sapi_name() != 'cli'
    && @php_sapi_name() != 'cgi'
    && @php_sapi_name() != 'cgi-fcgi')
{
  die('This script only works in the shell.');
}

class customer
{
  public $db = false;
  public $logger = false;
  public $debugHandler = false;
  public $settings = array();
  public $customerData = array();

  public function __construct($db, $logger, $debugHandler, $settings, $customerData) {

    $this->db = $db;
    $this->logger = $logger;
    $this->debugHandler = $debugHandler;
    $this->settings = $settings;
    $this->customerData = $customerData;

  }

  public function createHomeDir()
  {
    fwrite($debugHandler, '  cron_tasks: Task2 started - create new home' . "\n");
    $cronlog->logAction(CRON_ACTION, LOG_INFO, 'Task2 started - create new home');

    if(is_array($this->customerData) && !empty($this->customerData))
    {
      // define paths
      $userhomedir = makeCorrectDir($this->settings['system']['documentroot_prefix'] . '/' . $this->customerData['loginname'] . '/');
      $usermaildir = makeCorrectDir($this->settings['system']['vmail_homedir'] . '/' . $this->customerData['loginname'] . '/');

      // stats directory
      if($this->settings['system']['awstats_enabled'] == '1')
      {
        $cronlog->logAction(CRON_ACTION, LOG_NOTICE, 'Running: mkdir -p ' . escapeshellarg($userhomedir . 'awstats'));
        safe_exec('mkdir -p ' . escapeshellarg($userhomedir . 'awstats'));
      } else {
        $cronlog->logAction(CRON_ACTION, LOG_NOTICE, 'Running: mkdir -p ' . escapeshellarg($userhomedir . 'webalizer'));
        safe_exec('mkdir -p ' . escapeshellarg($userhomedir . 'webalizer'));
      }

      // maildir
      $cronlog->logAction(CRON_ACTION, LOG_NOTICE, 'Running: mkdir -p ' . escapeshellarg($usermaildir));
      safe_exec('mkdir -p ' . escapeshellarg($usermaildir));

      //check if admin of customer has added template for new customer directories
      if((int)$this->customerData['store_defaultindex'] == 1)
      {
        storeDefaultIndex($this->customerData['loginname'], $userhomedir, $cronlog, true);
      }

      // strip of last slash of paths to have correct chown results
      $userhomedir = (substr($userhomedir, 0, -1) == '/') ? substr($userhomedir, 0, -1) : $userhomedir;
      $usermaildir = (substr($usermaildir, 0, -1) == '/') ? substr($usermaildir, 0, -1) : $usermaildir;

      $cronlog->logAction(CRON_ACTION, LOG_NOTICE, 'Running: chown -R ' . (int)$this->customerData['uid'] . ':' . (int)$this->customerData['gid'] . ' ' . escapeshellarg($userhomedir));
      safe_exec('chown -R ' . (int)$this->customerData['uid'] . ':' . (int)$this->customerData['gid'] . ' ' . escapeshellarg($userhomedir));
      $cronlog->logAction(CRON_ACTION, LOG_NOTICE, 'Running: chown -R ' . (int)$this->settings['system']['vmail_uid'] . ':' . (int)$this->settings['system']['vmail_gid'] . ' ' . escapeshellarg($usermaildir));
      safe_exec('chown -R ' . (int)$this->settings['system']['vmail_uid'] . ':' . (int)$this->settings['system']['vmail_gid'] . ' ' . escapeshellarg($usermaildir));
    }
  }
}