#!/bin/bash

# check for php
PHP_BINARY=${PHP_BINARY:-"/usr/bin/php"}
if [ ! -e $PHP_BINARY ]; then
	echo "Sorry, could not find PHP (checked $PHP_BINARY). Set the PHP_BINARY variable to specify where your php-binary is located"
	exit -1
fi

# now run the cli thing
$PHP_BINARY -q api/froxlor-cli-starter.php $@

