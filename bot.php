#! /usr/bin/php5
<?php

set_time_limit(0);

if (version_compare(phpversion(), '5.1', '>=') === true) {
    date_default_timezone_set('Europe/Berlin');
}

define('PATH', dirname(__FILE__));

require_once(PATH . '/library/function.php');
require_once(PATH . '/library/db.class.php');
require_once(PATH . '/library/ircbot.class.php');

$config = parse_ini_file(PATH . '/config.ini', true);

$x = new ircbot($config);
$x->connect();