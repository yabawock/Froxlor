<?php

$dbconf = array(
		'db_driver' => 'mysql',
		'db_user' => '<MYSQL_USER>',
		'db_password' => '<MYSQL_PASSWD>',
		'dns' => array(
				'host' => 'localhost',
				'port' => '3306',
				'dbname' => '<MYSQL_DATABASE>'
		),
		'db_options' => array(
				'PDO::MYSQL_ATTR_INIT_COMMAND' => 'set names utf8'
		),
		'db_attributes' => array(
				'ATTR_ERRMODE' => 'ERRMODE_EXCEPTION'
		)
);
