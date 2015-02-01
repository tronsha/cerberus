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

use \Cerberus\Cerberus;
use \Cerberus\Db;

/**
 * Class Install
 * @package Cerberus
 * @author Stefan Hüsges
 * @link http://www.mpcx.net/projekte/cerberus/ Project Homepage
 * @link https://github.com/tronsha/cerberus Project on GitHub
 * @link http://tools.ietf.org/html/rfc2812 Internet Relay Chat: Client Protocol
 * @license http://www.gnu.org/licenses/gpl-3.0 GNU General Public License
 */

class Event
{
    public static function install()
    {
        $path = Cerberus::getPath();
        Event::createConfig();
        $config = parse_ini_file($path . '/config.ini', true);
        Event::installDatabase($config);
    }

    public static function createConfig()
    {
        /* @todo */
    }

    public static function installDatabase($config)
    {
        $db = new Db($config['db']);
        $db->connect();
        $db->query(file_get_contents('cerberus.sql'));
    }
}
