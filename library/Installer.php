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

namespace Cerberus;

use \Composer\Script\Event;
use \Doctrine\DBAL\Configuration;
use \Doctrine\DBAL\DriverManager;

/**
 * Class Installer
 * @package Cerberus
 * @author Stefan Hüsges
 * @link http://www.mpcx.net/projekte/cerberus/ Project Homepage
 * @link https://github.com/tronsha/cerberus Project on GitHub
 * @link https://getcomposer.org/doc/articles/scripts.md Composer scripts
 * @license http://www.gnu.org/licenses/gpl-3.0 GNU General Public License
 */

class Installer
{
    public static function install(Event $event)
    {
        $composer = $event->getComposer();
        self::createConfig($event);
        self::installDb();
    }

    protected static function createConfig(Event $event)
    {
        $io = $event->getIO();
        $io->write("\x1b[1m" .'Setup config file' . "\x1b[0m");
        if (file_exists(Cerberus::getPath() . '/config.ini') === true) {
            $newConfig = $io->ask('The config file exists. Create a new config? (y/n): ');
        }
        if ($newConfig == 'y' || file_exists(Cerberus::getPath() . '/config.ini') === false) {
            $config = file_get_contents(Cerberus::getPath() . '/config.sample.ini');
            $io->write("\x1b[31m" .'IRC' . "\x1b[0m");
            $botname = $io->ask('Nickname: ');
            $config = str_replace(
                '{botname}',
                $botname ? $botname : 'JohnSmith',
                $config
            );
            $io->write("\x1b[31m" .'Database' . "\x1b[0m");
            $dbhost = $io->ask('Host (' . "\x1b[34m" . 'localhost' . "\x1b[0m" . '): ');
            $config = str_replace(
                '{dbhost}',
                $dbhost ? $dbhost : 'localhost',
                $config
            );
            $dbport = $io->ask('Port (' . "\x1b[34m" . '3306' . "\x1b[0m" . '): ');
            $config = str_replace(
                '{dbport}',
                $dbport ? $dbport : '3306',
                $config
            );
            $dbname = $io->ask('Name (' . "\x1b[34m" . 'cerberus' . "\x1b[0m" . '): ');
            $config = str_replace(
                '{dbname}',
                $dbname ? $dbname : 'cerberus',
                $config
            );
            $dbuser = $io->ask('User (' . "\x1b[34m" . 'root' . "\x1b[0m" . '): ');
            $config = str_replace(
                '{dbuser}',
                $dbuser ? $dbuser : 'root',
                $config
            );
            $dbpass = $io->ask('Password: ');
            $config = str_replace(
                '{dbpassword}',
                $dbpass ? $dbpass : '',
                $config
            );
            file_put_contents(Cerberus::getPath() . '/config.ini', $config);
        }
    }

    protected static function installDb()
    {
        $config = parse_ini_file(Cerberus::getPath() . '/config.ini', true);
        $dbname = $config['db']['dbname'];
        $config['db']['dbname'] = null;
        $db =  DriverManager::getConnection($config['db'], new Configuration);
        $sm = $db->getSchemaManager();
        $list = $sm->listDatabases();
        if (in_array($dbname, $list) === false) {
            $sm->createDatabase($dbname);
        }
        $db->close();
        $config['db']['dbname'] = $dbname;
        $db =  DriverManager::getConnection($config['db'], new Configuration);
        $db->query(file_get_contents(Cerberus::getPath() . '/cerberus.sql'));
        $db->close();
    }
}
