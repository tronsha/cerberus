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

namespace Cerberus;

/**
 * Class Cerberus
 *  @package Cerberus
 * @author Stefan Hüsges
 * @link http://www.mpcx.net/cerberus/ Project Homepage
 * @link https://github.com/tronsha/Cerberus Project on GitHub
 * @link http://tools.ietf.org/html/rfc2812 Internet Relay Chat: Client Protocol
 * @license http://www.gnu.org/licenses/gpl-3.0 GNU General Public License
 */

class Cerberus
{
    const AUTH_NONE = 1;
    const AUTH_MEMBER = 2;
    const AUTH_ADMIN = 3;

    protected $config;

    public function __construct()
    {
        set_time_limit(0);
        $this->config = parse_ini_file($this->getPath() . '/config.ini', true);
    }

    /**
     * run me as main method
     */
    public function run()
    {
        $irc = new Irc($this->config);
        $irc->connect();
    }

    /**
     * @return float
     */
    protected function getMicrotime()
    {
        if (version_compare(phpversion(), '5.0', '<') === true) {
            list($usec, $sec) = @explode(" ", @microtime());
            return ((float)$usec + (float)$sec);
        } else {
            return microtime(true);
        }
    }

    /**
     * @param int $milliSeconds
     */
    protected function msleep($milliSeconds)
    {
        usleep($milliSeconds * 1000);
    }

    /**
     * @return string
     */
    protected function getPath()
    {
        return realpath(dirname(__FILE__) . '/..');
    }
}
