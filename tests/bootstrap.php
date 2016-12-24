<?php

if (@!include __DIR__ . '/../vendor/autoload.php') {
	echo 'Install Nette Tester using `composer update --dev`';
	exit(1);
}

define('TEMP_DIR', __DIR__ . '/tmp');
date_default_timezone_set('Europe/Prague');
Tester\Environment::setup();

function test(\Closure $function)
{
	$function();
}
