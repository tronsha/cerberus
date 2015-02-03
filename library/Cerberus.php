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

/**
 * Class Cerberus
 * @package Cerberus
 * @author Stefan Hüsges
 * @link http://www.mpcx.net/projekte/cerberus/ Project Homepage
 * @link https://github.com/tronsha/cerberus Project on GitHub
 * @link http://tools.ietf.org/html/rfc2812 Internet Relay Chat: Client Protocol
 * @license http://www.gnu.org/licenses/gpl-3.0 GNU General Public License
 */

use \Symfony\Component\Console\Output\ConsoleOutput;

class Cerberus
{
    const AUTH_NONE = 1;
    const AUTH_MEMBER = 2;
    const AUTH_ADMIN = 3;

    protected $config;

    protected static $path;

    protected static $output = null;

    public function __construct()
    {
        set_time_limit(0);
        $this->config = parse_ini_file($this->getPath() . '/config.ini', true);
        self::$output =  new ConsoleOutput;
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
     * @param $text
     */
    public static function sysinfo($text)
    {
        if (self::$output === null) {
            self::$output = new ConsoleOutput;
        }
        self::$output->writeln('<info>**** ' . $text . ' ****</info>');
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
    public static function getPath()
    {
        if (empty(self::$path) === true) {
            self::$path = realpath(dirname(__FILE__) . '/..');
        }
        return self::$path;
    }

    /**
     * @return bool
     */
    public static function is_exec_available()
    {
        $available = true;
        if (ini_get('safe_mode')) {
            $available = false;
        } else {
            $disable = ini_get('disable_functions');
            $blacklist = ini_get('suhosin.executor.func.blacklist');
            if ($disable . $blacklist) {
                $array = preg_split('/,\s*/', $disable . ',' . $blacklist);
                if (in_array('exec', $array)) {
                    $available = false;
                }
            }
        }
        return $available;
    }
}
