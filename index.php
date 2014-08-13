<?php

if(file_exists('.maintenance')) {
	require '.maintenance.php';
}

define("__ROOT_DIR__", __DIR__);

$container = require __DIR__ . '/app/bootstrap.php';

$container->getService('application')->run();
