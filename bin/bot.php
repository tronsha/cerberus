#!/usr/bin/env php
<?php

/*
 * Cerberus IRCBot
 * Copyright (C) 2008 - 2016 Stefan Hüsges
 *
 * This program is free software; you can redistribute it and/or modify it
 * under the terms of the GNU General Public License as published by the Free
 * Software Foundation; either version 3 of the License, or (at your option)
 * any later version.
 *
 * This program is distributed in the hope that it will be useful, but
 * WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY
 * or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License
 * for more details.
 *
 * You should have received a copy of the GNU General Public License along
 * with this program; if not, see <http://www.gnu.org/licenses/>.
 */

if (version_compare(phpversion(), '5.4.0', '<') === true) {
    echo 'Your version of PHP is ' . phpversion() . PHP_EOL;
    echo 'PHP 5.4.0 or higher is required' . PHP_EOL;
    exit;
}

error_reporting(-1);
date_default_timezone_set('Europe/Berlin');

set_error_handler(
    function ($errno, $errstr, $errfile, $errline, array $errcontext) {
        throw new ErrorException($errstr, 0, $errno, $errfile, $errline);
    }
);

chdir(__DIR__);
if (file_exists('../vendor/autoload.php')) {
    require_once '../vendor/autoload.php';
} else {
    echo 'You must set up the project dependencies, run the following commands:' . PHP_EOL;
    echo 'curl -s https://getcomposer.org/installer | php' . PHP_EOL;
    echo 'php composer.phar install' . PHP_EOL;
    exit;
}

use Cerberus\Cerberus;

try {
    $cerberus = new Cerberus;
    $cerberus->setParam($argv);
    $cerberus->run();
} catch (Exception $e) {
    Cerberus::error($e->getMessage());
}
