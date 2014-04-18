<?php

/*   Cerberus IRCBot
 *   Copyright (C) 2008 - 2014 Stefan Hüsges
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

/**
 * @author Stefan Hüsges <http://www.mpcx.net>
 */

define('PATH', realpath(dirname(__FILE__) . '/..'));

require_once(PATH . '/library/irc.php');
require_once(PATH . '/library/db.php');
require_once(PATH . '/library/plugin.php');

class Cerberus
{
    const AUTH_NONE = 1;
    const AUTH_MEMBER = 2;
    const AUTH_ADMIN = 3;

    protected $config;

    public function __construct()
    {
        set_time_limit(0);
        $this->config = parse_ini_file(PATH . '/config.ini', true);
    }

    public function irc()
    {
        $irc = new irc($this->config);
        $irc->connect();
    }

    protected function getmicrotime()
    {
        if (version_compare(phpversion(), '5.0', '<') === true) {
            list($usec, $sec) = @explode(" ", @microtime());
            return ((float)$usec + (float)$sec);
        } else {
            return microtime(true);
        }
    }

    protected function msleep($msec)
    {
        usleep($msec * 1000);
    }
}
