#!/usr/bin/env php
<?php

/*
 * Cerberus IRCBot
 * Copyright (C) 2008 - 2017 Stefan Hüsges
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

# m     h       dom     mon     dow     command
# */1   *       *       *       *       php -f /home/user/projects/cerberus/bin/autostart.php >/dev/null

chdir(__DIR__);
if (true === file_exists('../vendor/autoload.php')) {
    require_once '../vendor/autoload.php';
} else {
    echo 'You must set up the project dependencies, run the following commands:' . PHP_EOL;
    echo 'curl -s https://getcomposer.org/installer | php' . PHP_EOL;
    echo 'php composer.phar install' . PHP_EOL;
    exit;
}

use Cerberus\Cerberus;
use Cerberus\Db;

if (false === Cerberus::isExecAvailable()) {
    echo 'Can\'t run the bot, because "exec" is disabled' . PHP_EOL;
    exit;
}

$config = Cerberus::loadConfig();

if ('1' === $config['bot']['autostart']) {
    $db = new Db($config['db']);
    $db->connect();
    $bots = $db->getActiveBotList();
    $botCount = 0;
    if (0 < count($bots)) {
        exec('ps -e | grep php', $output);
        $pidList = [];
        foreach ($output as $line) {
            $data = explode(' ', trim(preg_replace('/[ ]+/', ' ', $line)));
            if ('php' === $data[3]) {
                $pidList[] = $data[0];
            }
        }
        foreach ($bots as $bot) {
            if (true === in_array($bot['pid'], $pidList, true)) {
                $botCount++;
                Cerberus::sysinfo('Bot ' . $bot['id'] . ' is running. PID: ' . $bot['pid']);
            } else {
                $db->cleanupBot($bot['id']);
                $db->shutdownBot($bot['id']);
                Cerberus::sysinfo('Bot ' . $bot['id'] . ' is not running.');
            }
        }
    }
    if (0 === $botCount) {
        Cerberus::pullGit();
        Cerberus::runUnittest();
        Cerberus::sysinfo('start a new bot.');
        $logDirectory = rtrim(trim($config['log']['directory']), '/');
        if (false === is_dir($logDirectory)) {
            mkdir($logDirectory);
        }
        exec(Cerberus::getPath() . '/bin/bot.php -noconsole > ' . $logDirectory . '/log.txt 2>&1 &');
    }
}
