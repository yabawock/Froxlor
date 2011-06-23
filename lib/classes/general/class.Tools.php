<?php

class Tools
{
	public static function cleanHeader()
	{
		/**
		 * Reverse magic_quotes_gpc=on to have clean GPC data again
		 */
		if(get_magic_quotes_gpc())
		{
			$in = array(&$_GET, &$_POST, &$_COOKIE);

			while(list($k, $v) = each($in))
			{
				foreach($v as $key => $val)
				{
					if(!is_array($val))
					{
						$in[$k][$key] = stripslashes($val);
						continue;
					}

					$in[] = & $in[$k][$key];
				}
			}

			unset($in);
		}
	}
}