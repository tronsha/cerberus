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

use Composer\Script\Event;
use Doctrine\DBAL\DriverManager;
use Exception;

/**
 * Class Installer
 * @package Cerberus
 * @author Stefan Hüsges
 * @link http://www.mpcx.net/projekte/cerberus/ Project Homepage
 * @link https://github.com/tronsha/cerberus Project on GitHub
 * @link https://getcomposer.org/doc/ Composer Documentation
 * @link http://symfony.com/doc/current/components/console/introduction.html The Console Component
 * @link http://en.wikipedia.org/wiki/ANSI_escape_code ANSI escape code
 * @license http://www.gnu.org/licenses/gpl-3.0 GNU General Public License
 */
class Installer
{
    /**
     * @param Event $event
     */
    public static function install(Event $event)
    {
        $io = $event->getIO();
        $io->write(str_repeat('-', 80));
        self::createConfig($event);
        $io->write(str_repeat('-', 80));
        self::installMysqlDb($event);
        $io->write(str_repeat('-', 80));
        self::runPhpUnit($event);
        $io->write(str_repeat('-', 80));
    }

    /**
     * @param Event $event
     */
    protected static function createConfig(Event $event)
    {
        $io = $event->getIO();
        $io->write('<info>Setup config file</info>');
        $update = 'n';
        if (file_exists(Cerberus::getPath() . '/config.ini') === true) {
            $io->write('<comment>The config file exists.</comment>');
            $update = $io->ask('<question>Create a new config? (y/n):</question> ');
        }
        if ($update == 'y' || file_exists(Cerberus::getPath() . '/config.ini') === false) {
            $config = file_get_contents(Cerberus::getPath() . '/config.sample.ini');
            $io->write('<options=bold>IRC</options=bold>');
            $botname = $io->ask('Nickname: ');
            $config = str_replace('{botname}', $botname ? $botname : 'JohnSmith', $config);
            $botchannel = $io->ask('Channel: ');
            $config = str_replace('{botchannel}', $botchannel ? trim($botchannel, " \t\n\r\0\x0B#") : 'cerberbot', $config);
            $io->write('<options=bold>Database</options=bold>');
            $dbhost = $io->ask('Host (<fg=cyan>localhost</fg=cyan>): ');
            $config = str_replace('{dbhost}', $dbhost ? $dbhost : 'localhost', $config);
            $dbport = $io->ask('Port (<fg=cyan>3306</fg=cyan>): ');
            $config = str_replace('{dbport}', $dbport ? $dbport : '3306', $config);
            $dbname = $io->ask('Name (<fg=cyan>cerberus</fg=cyan>): ');
            $config = str_replace('{dbname}', $dbname ? $dbname : 'cerberus', $config);
            $dbuser = $io->ask('User (<fg=cyan>root</fg=cyan>): ');
            $config = str_replace('{dbuser}', $dbuser ? $dbuser : 'root', $config);
            $dbpass = $io->ask('Password: ');
            $config = str_replace('{dbpassword}', $dbpass ? $dbpass : '', $config);
            $io->write('<info>Writing config file</info>');
            file_put_contents(Cerberus::getPath() . '/config.ini', $config);
        }
    }

    /**
     * @param Event $event
     */
    protected static function installMysqlDb(Event $event)
    {
        $io = $event->getIO();
        try {
            $config = parse_ini_file(Cerberus::getPath() . '/config.ini', true);
            $dbname = $config['db']['dbname'];
            $config['db']['dbname'] = null;
            $db = DriverManager::getConnection($config['db']);
            $sm = $db->getSchemaManager();
            $list = $sm->listDatabases();
            if (in_array($dbname, $list) === false) {
                $io->write('<info>Create database</info>');
                $sm->createDatabase($dbname);
            }
            $db->close();
            $config['db']['dbname'] = $dbname;
            $db = DriverManager::getConnection($config['db']);
            $io->write('<info>Create database tables</info>');
            $db->query(file_get_contents(Cerberus::getPath() . '/cerberus.mysql.sql'));
            $db->close();
        } catch (Exception $e) {
            $io->write('<error>' . $e->getMessage() . '</error>');
        }
    }

    /**
     * @param Event $event
     */
    protected static function runPhpUnit(Event $event)
    {
        $io = $event->getIO();
        if (file_exists(Cerberus::getPath() . '/vendor/bin/phpunit') === false) {
            $io->write('<error>Can\'t find "PHPUnit".</error>');
        } elseif (Cerberus::isExecAvailable() === false) {
            $io->write('<error>Can\'t run "PHPUnit", because "exec" is disabled.</error>');
        } else {
            $output = array();
            exec(Cerberus::getPath() . '/vendor/bin/phpunit', $output);
            foreach ($output as $line) {
                $io->write('<comment>' . $line . '</comment>');
            }
        }
    }
}
