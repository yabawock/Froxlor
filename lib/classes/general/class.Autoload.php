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

class classAutoload
{
	public static function autoload($classname)
	{
		self::findIncludeClass(dirname(__FILE__) . '/../', $classname);
	}

	private static function findIncludeClass($dirname, $classname)
	{
		$dirhandle = opendir($dirname);
		while(false !== ($filename = readdir($dirhandle)))
		{
			if($filename != '.' && $filename != '..' && $filename != '')
			{
				if($filename == 'class.' . $classname . '.php' || $filename == 'abstract.' . $classname . '.php' || $filename == $classname . '.class.php')
				{
					include_once($dirname . $filename);
					return;
				}

				if(is_dir($dirname . $filename))
				{
					self::findIncludeClass($dirname . $filename . '/', $classname);
				}
			}
		}
	}
}

spl_autoload_register(array('classAutoload', 'autoload'));

/**
 * This is just for backword compability
 */
includeFunctions(dirname(__FILE__) . '/../../functions/');

function includeFunctions($dirname)
{
	$dirhandle = opendir($dirname);
	while(false !== ($filename = readdir($dirhandle)))
	{
		if($filename != '.' && $filename != '..' && $filename != '')
		{
			if((substr($filename, 0, 9) == 'function.' || substr($filename, 0, 9) == 'constant.') && substr($filename, -4 ) == '.php')
			{
				include($dirname . $filename);
			}

			if(is_dir($dirname . $filename))
			{
				includeFunctions($dirname . $filename . '/');
			}
		}
	}
	closedir($dirhandle);
}
