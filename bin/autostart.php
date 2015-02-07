#! /usr/bin/php5
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
require_once('../vendor/autoload.php');

use Cerberus\Cerberus;
use Cerberus\Db;

if (Cerberus::is_exec_available() === false) {
    echo 'Can\'t run the bot, because "exec" is disabled' . PHP_EOL;
    exit;
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
        exec('php -f ' . Cerberus::getPath() . '/bin/bot.php > ' . $config['log']['directory'] . 'log.txt 2>&1 &');
    }
}
