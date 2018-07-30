<?php
$baseDir = dirname(dirname(__FILE__));
$loader = require $baseDir . '/vendor/autoload.php';
define('AR_ROOT_PATH', realpath($baseDir) . '/');
ar\core\Ar::init($loader);
