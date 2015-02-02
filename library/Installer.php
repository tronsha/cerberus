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
use \Cerberus\Cerberus;
use \Cerberus\Db;

/**
 * Class Installer
 * @package Cerberus
 * @author Stefan Hüsges
 * @link http://www.mpcx.net/projekte/cerberus/ Project Homepage
 * @link https://github.com/tronsha/cerberus Project on GitHub
 * @link http://tools.ietf.org/html/rfc2812 Internet Relay Chat: Client Protocol
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

    protected static function createConfig($event)
    {
        $io = $event->getIO();
        $config = file_get_contents(Cerberus::getPath() . '/config.sample.ini');
        $dbname = $io->ask('Database Name: ');
        $config = str_replace(
            '{dbname}',
            $dbname ? $dbname : 'cerberus',
            $config
        );
        $dbuser = $io->ask('Database User: ');
        $config = str_replace(
            '{dbuser}',
            $dbuser ? $dbuser : 'root',
            $config
        );
        $dbpass = $io->ask('Database Password: ');
        $config = str_replace(
            '{dbpassword}',
            $dbpass ? $dbpass : '',
            $config
        );
        file_put_contents(Cerberus::getPath() . '/config.ini', $config);
    }

    protected static function installDb()
    {
        $config = parse_ini_file(Cerberus::getPath() . '/config.ini', true);
        $db = new Db($config['db']);
        $db->connect();
        $db->query(file_get_contents(Cerberus::getPath() . '/cerberus.sql'));
    }
}
