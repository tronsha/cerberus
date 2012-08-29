#! /usr/bin/php5
<?php

set_time_limit(0);

if(version_compare(phpversion(), '5.1', '>=') === true)
    date_default_timezone_set('Europe/Berlin');

require_once('./library/function.php');
require_once('./library/db.class.php');
require_once('./library/ircbot.class.php');

$config = parse_ini_file('./config.ini', true);

$x = new ircbot($config);
$x->connect();