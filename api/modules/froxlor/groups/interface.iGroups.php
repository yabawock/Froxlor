<?php

/**
 * Froxlor API Groups-Module interface
 *
 * PHP version 5
 *
 * This file is part of the Froxlor project.
 * Copyright (c) 2010- the Froxlor Team (see authors).
 *
 * For the full copyright and license information, please view the COPYING
 * file that was distributed with this source code. You can also view the
 * COPYING file online at http://files.froxlor.org/misc/COPYING.txt
 *
 * @copyright  (c) the authors
 * @author     Froxlor team <team@froxlor.org> (2010-)
 * @license    GPLv2 http://files.froxlor.org/misc/COPYING.txt
 * @category   Modules
 * @package    API
 * @since      0.99.0
 */

/**
 * Interface iGroups
 *
 * @copyright  (c) the authors
 * @author     Froxlor team <team@froxlor.org> (2010-)
 * @license    GPLv2 http://files.froxlor.org/misc/COPYING.txt
 * @category   Modules
 * @package    API
 * @since      0.99.0
 */
interface iGroups {

	/**
	 * Output all available groups with all their information.
	 * Please note tha passwords and apikeys of the group-users
	 * are *not* output of course.
	 *
	 * @return array the groups-bean-data as array
	 */
	public static function listGroups();

	/**
	 * output information about a specific group, given by name
	 *
	 * @param string $name name of the group
	 *
	 * @throws GroupsException if the group does not exist
	 * @return array groups-bean array of the group
	*/
	public static function statusGroup();

	/**
	 * adds a new group to the system. Groupnames start with
	 * an @-sign. If no @-sign is given it will be prefixed
	 *
	 * @param string $name name of the group
	 *
	 * @throws GroupsException if the group already exists
	 * @return array groups-bean array of the new group
	*/
	public static function addGroup();

	/**
	 * adds a new group but copys the permissions and possible
	 * existing child-group (sharedGroups) from another group
	 * so they don't have to be added again
	 *
	 * @param string $name name of the group
	 * @param string $copyfrom name of the group which is copied
	 *
	 * @throws GroupsException if the new group already exists or the other one doesn't
	 * @return array groups-bean array of the new group
	*/
	public static function copyGroup();

	/**
	 * nests one or more groups into another group. Means: group given via $name will be
	 * connected to group(s) given via $with_group - but *not* vice versa.
	 * (simply said: you put $with_group in $name and $name is then a parent of the given groups)
	 *
	 * @param string $name name of group to add to
	 * @param string|array $with_group name (string or array) of the group(s) to add
	 *
	 * @throws GroupsException if the group already is subgroup of the given group
	 *                         or either of the groups does not exist
	 * @return array groups-bean array of the group given by name
	*/
	public static function nestGroups();

	/**
	 * connects one or more groups to a user
	 *
	 * @param name|array $groups name or list of names of groups to put the user in
	 * @param string $user name of the user
	 *
	 * @throwsGroupsException
	 * @return bool success = true
	*/
	public static function addGroupsToUser();

	/**
	 * modifies a group's name
	 *
	 * @param int $id id of the group
	 * @param sting $name new group name
	 *
	 * @throws GroupsException if group does not exists
	 * @return array exported groups-bean of the updated group-entry
	*/
	public static function modifyGroup();

	/**
	 * deletes a group from the database (only if not in use)
	 *
	 * @param string $name e.g. @customer
	 *
	 * @throws GroupsException if still in use or not found
	 * @return bool success = true
	*/
	public static function deleteGroup();

}
