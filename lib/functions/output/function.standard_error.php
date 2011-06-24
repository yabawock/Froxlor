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

/**
 * Prints one ore more errormessages on screen
 *
 * @param array Errormessages
 * @param string A %s in the errormessage will be replaced by this string.
 * @param array An optional array holding the paramters for the linker generating a back-link
 * @author Florian Lippert <flo@syscp.org>
 * @author Ron Brand <ron.brand@web.de>
 */

function standard_error($errors = '', $replacer = '', $link = '')
{
	$replacer = htmlentities($replacer);

	if(!is_array($errors))
	{
		$errors = array(
			$errors
		);
	}

	if (is_array($link))
	{
		Froxlor::getSmarty()->assign('link', '<a href="'.Froxlor::getLinker()->getLink($link).'">' . _('Back') . '</a>');
	}

	$error = '';
	foreach($errors as $single_error)
	{
		if ($replacer != '')
		{
			$single_error = sprintf($single_error, $replacer);
		}

		if(empty($error))
		{
			$error = $single_error;
		}
		else
		{
			$error.= ' ' . $single_error;
		}
	}

	Froxlor::getSmarty()->assign('error', $error);
	return Froxlor::getSmarty()->fetch('misc/error.tpl');
}
