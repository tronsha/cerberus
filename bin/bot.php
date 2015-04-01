#!/usr/bin/env php
<?php

/*   Cerberus IRCBot
 *   Copyright (C) 2008 - 2015 Stefan Hüsges
 *
 *   This program is free software; you can redistribute it and/or modify it
 *   under the terms of the GNU General Public License as published by the Free
 *   Software Foundation; either version 3 of the License, or (at your option)
 *   any later version.
 *
 *   This program is distributed in the hope that it will be useful, but
 *   WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY
 *   or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License
 *   for more details.
 *
 *   You should have received a copy of the GNU General Public License along
 *   with this program; if not, see <http://www.gnu.org/licenses/>.
 */

if (version_compare(phpversion(), '5.4.0', '<') === true) {
    echo 'Your version of PHP is ' . phpversion() . PHP_EOL;
    echo 'PHP 5.4.0 or higher is required' . PHP_EOL;
    exit;
}

error_reporting(-1);
date_default_timezone_set('Europe/Berlin');

chdir(__DIR__);
require_once('../vendor/autoload.php');

use Cerberus\Cerberus;

try {
    $cerberus = new Cerberus;
    $cerberus->run();
} catch (Exception $e) {
    Cerberus::error($e->getMessage());
}
