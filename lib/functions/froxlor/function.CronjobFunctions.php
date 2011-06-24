<?php

/**
 * This file is part of the Froxlor project.
 * Copyright (c) 2010 the Froxlor Team (see authors).
 *
 * For the full copyright and license information, please view the COPYING
 * file that was distributed with this source code. You can also view the
 * COPYING file online at http://files.froxlor.org/misc/COPYING.txt
 *
 * @copyright  (c) the authors
 * @author     Froxlor team <team@froxlor.org> (2010-)
 * @license    GPLv2 http://files.froxlor.org/misc/COPYING.txt
 * @package    Functions
 *
 */

/*
 * Function getNextCronjobs
 *
 * checks which cronjobs have to be executed
 *
 * @return	array	array of cron-files which are to be executed
 */
function getNextCronjobs()
{
	global $db;

	$query = "SELECT `id`, `cronfile` FROM `".TABLE_PANEL_CRONRUNS."` WHERE `interval` <> '0' AND `isactive` = '1' AND (";

	$intervals = getIntervalOptions();

	$x = 0;
	foreach($intervals as $name => $ival)
	{
		if($name == '0') continue;

		if($x == 0) {
			$query.= '(UNIX_TIMESTAMP(DATE_ADD(FROM_UNIXTIME(`lastrun`), INTERVAL '.$ival.')) <= UNIX_TIMESTAMP() AND `interval`=\''.$ival.'\')';
		} else {
			$query.= ' OR (UNIX_TIMESTAMP(DATE_ADD(FROM_UNIXTIME(`lastrun`), INTERVAL '.$ival.')) <= UNIX_TIMESTAMP() AND `interval`=\''.$ival.'\')';
		}
		$x++;
	}

	$query.= ');';

	$result = $db->query($query);

	$cron_files = array();
	while($row = $db->fetch_array($result))
	{
		$cron_files[] = $row['cronfile'];
		$db->query("UPDATE `".TABLE_PANEL_CRONRUNS."` SET `lastrun` = UNIX_TIMESTAMP() WHERE `id` ='".(int)$row['id']."';");
	}

	return $cron_files;
}


function includeCronjobs($debugHandler, $pathtophpfiles)
{
	global $settings;

	$cronjobs = getNextCronjobs();

	$jobs_to_run = array();
	$cron_path = makeCorrectDir($pathtophpfiles.'/scripts/jobs/');

	if($cronjobs !== false
	&& is_array($cronjobs)
	&& isset($cronjobs[0]))
	{
		foreach($cronjobs as $cronjob)
		{
			$cron_file = makeCorrectFile($cron_path.$cronjob);
			$jobs_to_run[] = $cron_file;
		}
	}

	return $jobs_to_run;
}


function getIntervalOptions()
{
	global $db, $lng, $cronlog;

	$query = "SELECT DISTINCT `interval` FROM `" . TABLE_PANEL_CRONRUNS . "` ORDER BY `interval` ASC;";
	$result = $db->query($query);
	$cron_intervals = array();

	$cron_intervals['0'] = $lng['panel']['off'];

	while($row = $db->fetch_array($result))
	{
		if(validateSqlInterval($row['interval']))
		{
			$cron_intervals[$row['interval']] = $row['interval'];
		}
		else
		{
			$cronlog->logAction(CRON_ACTION, LOG_ERROR, "Invalid SQL-Interval ".$row['interval']." detected. Please fix this in the database.");
		}
	}

	return $cron_intervals;
}


function getCronjobsLastRun()
{
	$query = "SELECT `lastrun`, `desc_lng_key` FROM `".TABLE_PANEL_CRONRUNS."` ORDER BY `cronfile` ASC";
	$result = Froxlor::getDb()->query($query);

	$cronjobs_last_run = array();

	while($row = Froxlor::getDb()->fetch_array($result))
	{
		$lastrun = _('No execution yet');

		if($row['lastrun'] > 0)
		{
			$lastrun = $row['lastrun'];
		}
		$desc = '';
		switch($row['desc_lng_key'])
		{
			case 'cron_apsinstaller': $desc = _('APS installer'); break;
			case 'cron_apsupdater': $desc = _('Updating APS packages'); break;
			case 'cron_autoresponder': $desc = _('E-mail autoresponder'); break;
			case 'cron_backup': $desc = _('Backing up files'); break;
			case 'cron_tasks': $desc = _('Generating of configfiles'); break;
			case 'cron_ticketarchive': $desc = _('Archiving old tickets'); break;
			case 'cron_traffic': $desc = _('Traffic calculation'); break;
			case 'cron_usage_report': $desc = _('Send reports about web- and traffic-usage'); break;
			case 'cron_ticketsreset': $desc = _('Resetting ticket counter'); break;
		}
		$cronjobs_last_run[$row['desc_lng_key']] = array('text' => $desc, 'lastrun' => $lastrun);
	}

	return $cronjobs_last_run;
}

function toggleCronStatus($module = null, $isactive = 0)
{
	global $db;

	if($isactive != 1) {
		$isactive = 0;
	}

	$query = "UPDATE `".TABLE_PANEL_CRONRUNS."` SET `isactive` = '".(int)$isactive."' WHERE `module` = '".$module."'";
	$db->query($query);

}

function getOutstandingTasks()
{
	$query = "SELECT * FROM `".TABLE_PANEL_TASKS."` ORDER BY `type` ASC";
	$result = Froxlor::getDb()->query($query);

	$tasks = array();
	while($row = Froxlor::getDb()->fetch_array($result))
	{
		if($row['data'] != '')
		{
			$row['data'] = unserialize($row['data']);
		}

		/*
		 * rebuilding webserver-configuration
		 */
		if($row['type'] == '1')
		{
			$tasks[] = _('Rebuilding webserver-configuration');
		}
		/*
		 * adding new user
		 */
		elseif($row['type'] == '2')
		{
			$loginname = '';
			if(is_array($row['data']))
			{
				$loginname = $row['data']['loginname'];
			}
			$tasks[] = sprintf(_('Adding new customer %s'), $loginname);
		}
		/*
		 * rebuilding bind-configuration
		 */
		elseif($row['type'] == '4')
		{
			$tasks[] = _('Rebuilding bind-configuration');
		}
		/*
		 * creating ftp-user directory
		 */
		elseif($row['type'] == '5')
		{
			$tasks[] = _('Creating directory for new ftp-user');
		}
		/*
		 * deleting user-files
		 */
		elseif($row['type'] == '6')
		{
			$loginname = '';
			if(is_array($row['data']))
			{
				$loginname = $row['data']['loginname'];
			}
			$tasks[] = sprintf(_('Deleting files of customer %s'), $loginname);
		}
		/*
		 * Set FS - quota
		 */
		elseif($row['type'] == '10')
		{
			$tasks[] = _('Set quota on filesystem');
		}
	}

	$query2 = "SELECT DISTINCT `Task` FROM `".TABLE_APS_TASKS."` ORDER BY `Task` ASC";
	$result2 = Froxlor::getDb()->query($query2);

	while($row2 = Froxlor::getDb()->fetch_array($result2))
	{
		/*
		 * install
		 */
		if($row2['Task'] == '1')
		{
			$tasks[] = _('Installing one or more APS packages');
		}
		/*
		 * remove
		 */
		elseif($row2['Task'] == '2')
		{
			$tasks[] = _('Removing one or more APS packages');
		}
		/*
		 * reconfigure
		 */
		elseif($row2['Task'] == '3')
		{
			$tasks[] = _('Reconfigurating one or more APS packages');
		}
		/*
		 * upgrade
		 */
		elseif($row2['Task'] == '4')
		{
			$tasks[] = _('Upgrading one or more APS packages');
		}
		/*
		 * system update
		 */
		elseif($row2['Task'] == '5')
		{
			$tasks[] = _('Updating all APS packages');
		}
		/*
		 * system download
		 */
		elseif($row2['Task'] == '6')
		{
			$tasks[] = _('Downloading new APS packages');
		}
	}

	return $tasks;
}
