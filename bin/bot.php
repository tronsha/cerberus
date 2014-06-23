#! /usr/bin/php5
<?php

if (version_compare(phpversion(), '5.3.2', '<') === true) {
    echo 'Your version of PHP is ' . phpversion() . PHP_EOL;
    echo 'PHP 5.3.2 or higher is required' . PHP_EOL;
    exit;
}

error_reporting(-1);
date_default_timezone_set('Europe/Berlin');

chdir(__DIR__);
require_once('../vendor/autoload.php');

use \Cerberus\Cerberus;

$cerberus = new Cerberus;
$cerberus->run();
