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
 * @package    Functions
 *
 */

function getCustomers($limit_resource = '')
{
	global $db;

	$additional_conditions = '';
	$additional_conditions_array = array();
	if(getSessionUserDetail('customers_see_all') != true)
	{
		$additional_conditions_array[] = '`id` = \'' . $user->getId() . '\'';
	}
	if($limit_resource != '')
	{
		$additional_conditions_array[] = '(`' . $limit_resource . '_used` < `' . $limit_resource . '` OR `' . $limit_resource . '` = \'-1\')';
	}
	if(!empty($additional_conditions_array))
	{
		$additional_conditions = implode(' AND ', $additional_conditions_array) . ' ';
	}

	$query = 'SELECT `u`.`id`, `u`.`loginname`, `a`.`name`, `a`.`firstname`, `a`.`company` FROM `' . TABLE_USERS . '` `u`, `'. TABLE_USER_ADDRESSES .'` `a` WHERE `u`.`isadmin`=\'0\' AND `u`.`contactid` = `a`.`id` ' . $additional_conditions  . ' ORDER BY `name` ASC';
	$result = $db->query($query);
	$customers_array = array();

	while($row = $db->fetch_array($result))
	{
		$customers_array[$row['id']] = getCorrectFullUserDetails($row) . ' (' . $row['loginname'] . ')';
	}

	return $customers_array;
}
