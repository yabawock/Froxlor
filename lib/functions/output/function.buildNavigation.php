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
 * Build Navigation Sidebar
 * @param array navigation data
 * @param array userinfo the userinfo of the user
 * @return string the content of the navigation bar
 *
 * @author Florian Lippert <flo@syscp.org>
 */

function buildNavigation($navigation, $userinfo)
{
	$returnvalue = '';

	$user = Froxlor::getUser();
	foreach($navigation as $box)
	{
		if((!isset($box['show_element']) || $box['show_element'] === true) &&
			(!isset($box['required_resources']) || $box['required_resources'] == '' || (((int)$user->getData('resources', $box['required_resources']) > 0 || $user->getData('resources', $box['required_resources']) == '-1'))))
		{
			$navigation_links = '';
			foreach($box['elements'] as $element_id => $element)
			{
				if((!isset($element['show_element']) || $element['show_element'] === true) &&
					(!isset($element['required_resources']) || $element['required_resources'] == '' || (((int)$user->getData('resources', $element['required_resources']) > 0 || $user->getData('resources', $element['required_resources']) == '-1'))))
				{
					if(isset($element['url']))
					{
						if(is_array($element['url']))
						{
							$completeLink = Froxlor::getLinker()->getLink($element['url']);
						}

						$target = '';

						if(isset($element['new_window']) && $element['new_window'] == true)
						{
							$target = ' target="_blank"';
						}

						$completeLink = '<a href="' . htmlspecialchars($completeLink) . '"' . $target . ' class="menu">' . htmlspecialchars($element['label']) . '</a>';
					}
					else
					{
						$completeLink = htmlspecialchars($element['label']);
					}

					Froxlor::getSmarty()->assign('completeLink', $completeLink);
					$navigation_links .= Froxlor::getSmarty()->fetch('navigation_link.tpl');
				}
			}

			if($navigation_links != '')
			{
				if(isset($box['url']))
				{
					if(is_array($box['url']))
					{
						$completeLink = Froxlor::getLinker()->getLink($box['url']);
					}

					$target = '';

					if(isset($box['new_window']) && $box['new_window'] == true)
					{
						$target = ' target="_blank"';
					}

					$completeLink = '<a href="' . htmlspecialchars($completeLink) . '"' . $target . ' class="menu">' . htmlspecialchars($box['label']) . '</a>';
				}
				else
				{
					$completeLink = htmlspecialchars($box['label']);
				}

				Froxlor::getSmarty()->assign('completeLink', $completeLink);
				Froxlor::getSmarty()->assign('navigation_links', $navigation_links);
				$returnvalue .= Froxlor::getSmarty()->fetch('navigation_element.tpl');
			}
		}
	}

	return $returnvalue;
}
