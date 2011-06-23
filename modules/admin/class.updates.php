<?php

class updates
{
	public function index()
	{
		return Froxlor::getSmarty()->fetch('admin/update/index.tpl');
	}
}