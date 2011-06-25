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
 * @author     Andreas Burchert (scarya@froxlor.org)
 * @author     Froxlor team <team@froxlor.org> (2010-)
 * @license    GPLv2 http://files.froxlor.org/misc/COPYING.txt
 *
 */

class countrycode
{
	/**
	 * This will return an array with the full list of countries.
	 *
	 * @param boolean $asOptions shall this be returned as a <option> list
	 */
	public static function get($asOptions = false, $selectname = '', $select = null) {
		$cc = array();
		$output = '';

		include_once(dirname(__FILE__) . '/countries.inc.php');
		asort($country);
		if (!$asOptions)
		{
			return $country;
		}
		foreach($country as $key=>$val) {
			$append = "";
			if ($asOptions) {
				if (!empty($select))
				{
					$append = ' selected="selected"';
				}
				if (isset($selectname))
				{
					if (isset($_SESSION['requestData'][$selectname]) && $key ==  $_SESSION['requestData'][$selectname])
					{
						$append = ' selected="selected"';
					}
					elseif(isset($_SESSION['requestData'][$selectname]) && $key !=  $_SESSION['requestData'][$selectname])
					{
						$append = '';
					}
				}
				$output .= '<option value="'. $key .'"'. $append .'>'. $val .'</option>';
			}
		}

		return $output;
	}
}