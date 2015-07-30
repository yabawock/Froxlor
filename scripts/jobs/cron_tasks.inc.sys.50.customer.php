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

class customer {
  public $logger = false;
  public $debugHandler = false;
  public $customerData = array();

  /**
   * Constructor for the customer class
   *
   * @param FroxlorLogger $logger       Logging instance
   * @param resource $debugHandler      Debug handler
   * @param array $customerData         Custome details
   */
  public function __construct($logger, $debugHandler, $customerData) {
    $this->logger = $logger;
    $this->debugHandler = $debugHandler;
    $this->customerData = $customerData;
    $this->userHomeDir = '';
    $this->userMailDir = '';
  }

  /**
   * Creates the home and utility directories for a new customer
   * @return void
   */
  public function createHomeDir() {
    fwrite($this->debugHandler, '  cron_tasks: Task2 started - create new home' . "\n");
    $this->logger->logAction(CRON_ACTION, LOG_INFO, 'Task2 started - create new home');

    if(is_array($this->customerData) && !empty($this->customerData)) {
      $this->definePaths();
      $this->createMailDir();
      if((int)Settings::Get('phpfpm.enabled_chroot') == 0) {
        $this->copyDefaultIndex();
        $this->chownHomeDir();
      } else {
        $this->initializeChroot();
        $this->prepareChroot();
      }
      $this->createStatsDir();
    }
  }

  /**
   * Initialize the paths needed for later steps
   * @return void
   */
  protected function definePaths() {
    // define paths
    $this->userHomeDir = makeCorrectDir(Settings::Get('system.documentroot_prefix') . '/' . $this->customerData['loginname'] . '/');
    $this->userMailDir = makeCorrectDir(Settings::Get('system.vmail_homedir') . '/' . $this->customerData['loginname'] . '/');
  }

  /**
   * Create the directory for the web statistics
   * @return void
   */
  protected function createStatsDir() {
    // stats directory
    if(Settings::Get('system.awstats_enabled') == '1') {
      $this->logger->logAction(CRON_ACTION, LOG_NOTICE, 'Running: mkdir -p ' . escapeshellarg($this->userHomeDir . 'awstats'));
      safe_exec('mkdir -p ' . escapeshellarg($this->userHomeDir . 'awstats'));
      // in case we changed from the other stats -> remove old
      // (yes i know, the stats are lost - that's why you should not change all the time!)
      if (file_exists($this->userHomeDir . 'webalizer')) {
        safe_exec('rm -rf ' . escapeshellarg($this->userHomeDir . 'webalizer'));
      }
    } else {
      $this->logger->logAction(CRON_ACTION, LOG_NOTICE, 'Running: mkdir -p ' . escapeshellarg($this->userHomeDir . 'webalizer'));
      safe_exec('mkdir -p ' . escapeshellarg($this->userHomeDir . 'webalizer'));
      // in case we changed from the other stats -> remove old
      // (yes i know, the stats are lost - that's why you should not change all the time!)
      if (file_exists($this->userHomeDir . 'awstats')) {
        safe_exec('rm -rf ' . escapeshellarg($this->userHomeDir . 'awstats'));
      }
    }
  }

  /**
   * Create the mail storage dir
   * @return void
   */
  protected function createMailDir() {
    // maildir
    $this->logger->logAction(CRON_ACTION, LOG_NOTICE, 'Running: mkdir -p ' . escapeshellarg($this->userMailDir));
    safe_exec('mkdir -p ' . escapeshellarg($this->userMailDir));
  }

  /**
   * Copy the default index page to the customer directory
   * @return void
   */
  protected function copyDefaultIndex() {
    //check if admin of customer has added template for new customer directories
    if((int)$this->customerData['store_defaultindex'] == 1) {
      storeDefaultIndex($this->customerData['loginname'], $this->userHomeDir, $cronlog, true);
    }
  }

  /**
   * Change the owner of the customer homedir
   * @return void
   */
  protected function chownHomeDir() {
    // strip of last slash of paths to have correct chown results
    $this->userHomeDir = (substr($this->userHomeDir, 0, -1) == '/') ? substr($this->userHomeDir, 0, -1) : $this->userHomeDir;
    $this->userMailDir = (substr($this->userMailDir, 0, -1) == '/') ? substr($this->userMailDir, 0, -1) : $this->userMailDir;

    $cronlog->logAction(CRON_ACTION, LOG_NOTICE, 'Running: chown -R ' . (int)$this->customerData['uid'] . ':' . (int)$this->customerData['gid'] . ' ' . escapeshellarg($this->userHomeDir));
    safe_exec('chown -R ' . (int)$this->customerData['uid'] . ':' . (int)$this->customerData['gid'] . ' ' . escapeshellarg($this->userHomeDir));
    $this->logger->logAction(CRON_ACTION, LOG_NOTICE, 'Running: chown -R ' . (int)Settings::Get('system.vmail_uid') . ':' . (int)Settings::Get('system.vmail_gid') . ' ' . escapeshellarg($this->userMailDir));
    safe_exec('chown -R ' . (int)Settings::Get('system.vmail_uid') . ':' . (int)Settings::Get('system.vmail_gid') . ' ' . escapeshellarg($this->userMailDir));
  }

  protected function initializeChroot() {
    $baseChrootDir = realpath(Settings::Get('system.documentroot_prefix') . '/../basechroot');

    $this->logger->logAction(CRON_ACTION, LOG_NOTICE, 'Running: rm -rf ' . escapeshellarg($this->userHomeDir));
    if(strpos($this->userHomeDir, Settings::Get('system.documentroot_prefix')) === 0) {
      safe_exec('rm -rf ' . escapeshellarg($this->userHomeDir));
    }

    # Copy the chroot structure
    $this->logger->logAction(CRON_ACTION, LOG_NOTICE, 'Running: cp -R ' . escapeshellarg($baseChrootDir) . ' ' . escapeshellarg($this->userHomeDir));
    safe_exec('cp -R ' . escapeshellarg($baseChrootDir) . ' ' . escapeshellarg($this->userHomeDir));

    # Creating the customer homedir in the chroot
    $this->logger->logAction(CRON_ACTION, LOG_NOTICE, 'Running: mkdir -p ' . escapeshellarg($this->userHomeDir . '/' . $this->userHomeDir));
    safe_exec('mkdir -p ' . escapeshellarg($this->userHomeDir . '/' . $this->userHomeDir));

    # Creating the websites folder in the chroot
    $this->logger->logAction(CRON_ACTION, LOG_NOTICE, 'Running: mkdir -p ' . escapeshellarg($this->userHomeDir . '/websites'));
    safe_exec('mkdir -p ' . escapeshellarg($this->userHomeDir . '/websites'));

    # Linking the websites folder
    $this->logger->logAction(CRON_ACTION, LOG_NOTICE, 'Running: ln -s /websites ' . escapeshellarg($this->userHomeDir . '/' . $this->userHomeDir . '/websites'));
    safe_exec('ln -s /websites ' . escapeshellarg($this->userHomeDir . '/' . $this->userHomeDir . '/websites'));

    # Make sure all files belong to root initially
    $this->logger->logAction(CRON_ACTION, LOG_NOTICE, 'Running: chown -R 0:0 ' . escapeshellarg($this->userHomeDir));
    safe_exec('chown -R 0:0 ' . escapeshellarg($this->userHomeDir));
  }

  protected function prepareChroot() {
    # Fix permissions on temp dirs
    $this->logger->logAction(CRON_ACTION, LOG_NOTICE, 'Running: chmod 1777 ' . escapeshellarg($this->userHomeDir . '/tmp'));
    safe_exec('chmod 1777 ' . escapeshellarg($this->userHomeDir . '/tmp'));

    # check if admin of customer has added template for new customer directories
    if((int)$this->customerData['store_defaultindex'] == 1) {
      storeDefaultIndex($this->customerData['loginname'], $this->userHomeDir . '/websites', $this->logger, true);
    }

    # Fix permissions on websites folder
    $this->logger->logAction(CRON_ACTION, LOG_NOTICE, 'Running: chown -R ' . (int)$this->customerData['uid'] . ':' . (int)$this->customerData['gid'] . ' ' . escapeshellarg($this->userHomeDir . '/websites'));
    safe_exec('chown -R ' . (int)$this->customerData['uid'] . ':' . (int)$this->customerData['gid'] . ' ' . escapeshellarg($this->userHomeDir . '/websites'));
    $this->logger->logAction(CRON_ACTION, LOG_NOTICE, 'Running: chmod 0775 ' . escapeshellarg($this->userHomeDir . '/websites'));
    safe_exec('chmod 0775 ' . escapeshellarg($this->userHomeDir . '/websites'));
  }
}
