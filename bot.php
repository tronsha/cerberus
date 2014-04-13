#! /usr/bin/php5
<?php

set_time_limit(0);

if (version_compare(phpversion(), '5.1', '>=') === true) {
    date_default_timezone_set('Europe/Berlin');
}

define('PATH', dirname(__FILE__));

require_once(PATH . '/library/function.php');
require_once(PATH . '/library/db.php');
require_once(PATH . '/library/irc.php');
require_once(PATH . '/library/plugin.php');

$config = parse_ini_file(PATH . '/config.ini', true);

$x = new irc($config);
$x->connect();