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

# m     h       dom     mon     dow     command
# */1   *       *       *       *       php -f /home/user/projects/cerberus/bin/autostart.php >/dev/null

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
use Cerberus\Db;

if (Cerberus::isExecAvailable() === false) {
    echo 'Can\'t run the bot, because "exec" is disabled' . PHP_EOL;
    exit;
}

if (is_dir('../.git') === true) {
    chdir(dirname(__DIR__));
    exec('git pull', $output);
    $console = Cerberus::getConsole();
    $console->writeln('<comment>git pull</comment>');
    foreach($output as $line) {
        $console->writeln('<info>' . $line . '</info>');
    }
    unset($output);
    chdir(__DIR__);
}

$path = Cerberus::getPath();
$config = parse_ini_file($path . '/config.ini', true);

if ($config['bot']['autostart']) {
    $db = new Db($config['db']);
    $db->connect();
    $bots = $db->getActiveBotList();
    $botCount = 0;
    if ($bots) {
        exec('ps -e | grep php', $output);
        $pidList = array();
        foreach ($output as $line) {
            $data = explode(' ', trim(preg_replace('/[ ]+/', ' ', $line)));
            if ($data[3] == 'php') {
                $pidList[] = $data[0];
            }
        }
        foreach ($bots as $bot) {
            if (in_array($bot['pid'], $pidList) === true) {
                $botCount++;
                Cerberus::sysinfo('Bot ' . $bot['id'] . ' is running. PID: ' . $bot['pid']);
            } else {
                $db->cleanupBot($bot['id']);
                $db->shutdownBot($bot['id']);
                Cerberus::sysinfo('Bot ' . $bot['id'] . ' is not running.');
            }
        }
    }
    if ($botCount == 0) {
        Cerberus::sysinfo('start a new bot.');
        if (is_dir($config['log']['directory']) === false) {
            mkdir($config['log']['directory']);
        }
        exec(Cerberus::getPath() . '/bin/bot.php -noconsole > ' . $config['log']['directory'] . 'log.txt 2>&1 &');
    }
}
