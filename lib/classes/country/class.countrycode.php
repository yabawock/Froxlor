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
	public static function get($asOptions = false, $select = null) {
		global $lng;
		$cc = array();
		$output = '';
		
		foreach($lng['country'] as $key=>$val) {
			$append = "";
			if ($asOptions) {
				if (!empty($select)) {
					$append = ' selected="selected"';
				}
				$out .= '<option value="'. $key .'"'. $append .'>'. $val .'</option>';
			}
			else {
				$cc[$key] = $val;
			}
		}
		
		if ($asOptions) return $out;
		return $cc;
	}
}