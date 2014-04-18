#! /usr/bin/php5
<?php

if (version_compare(phpversion(), '5.1', '>=') === true) {
    date_default_timezone_set('Europe/Berlin');
}

require_once(__DIR__ . '/library/cerberus.php');

$cerberus = new Cerberus;
$cerberus->irc();
