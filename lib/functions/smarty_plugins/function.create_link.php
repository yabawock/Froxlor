<?php

function smarty_function_create_link($params, $smarty)
{
	return Froxlor::getLinker()->getLink($params);
}