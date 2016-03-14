<?php

/*   Cerberus IRCBot
 *   Copyright (C) 2008 - 2016 Stefan Hüsges
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

use Exception;

/**
 * Class Cerberus
 * @package Cerberus
 * @author Stefan Hüsges
 * @link http://www.mpcx.net/projekte/cerberus/ Project Homepage
 * @link https://github.com/tronsha/cerberus Project on GitHub
 * @link http://tools.ietf.org/html/rfc2812 Internet Relay Chat: Client Protocol
 * @license http://www.gnu.org/licenses/gpl-3.0 GNU General Public License
 */
class Cerberus
{
    const AUTH_NONE = 1;
    const AUTH_MEMBER = 2;
    const AUTH_ADMIN = 3;

    protected static $console = null;
    protected static $path = null;
    protected static $argv = null;

    /**
     *
     */
    public function __construct()
    {
        set_time_limit(0);
    }

    /**
     * @param array $argv
     */
    public function setParam($argv)
    {
        self::$argv = $argv;
    }

    /**
     * run me as main method
     * @return Irc
     */
    public function run()
    {
        $irc = new Irc(self::loadConfig());
        $irc->connect();
        return $irc;
    }

    /**
     * @return array
     * @throws Exception
     */
    public static function loadConfig()
    {
        if (file_exists(self::getPath() . '/config.ini') === false) {
            throw new Exception('File Not Found: ' . self::getPath() . '/config.ini');
        }
        return parse_ini_file(self::getPath() . '/config.ini', true);
    }

    /**
     * @return string
     */
    public static function getPath()
    {
        if (self::$path === null) {
            self::$path = realpath(dirname(__FILE__) . '/..');
        }
        return self::$path;
    }

    /**
     * @return Console
     */
    public static function getConsole()
    {
        if (self::$console === null) {
            self::$console = new Console;
            self::$console->setParam(self::$argv);
        }
        return self::$console;
    }

    /**
     * @param string $text
     * @return mixed
     */
    public static function error($text)
    {
        return self::getConsole()->writeln('<error>' . $text . '</error>');
    }

    /**
     * @param string $text
     * @return string
     */
    public static function sysinfo($text)
    {
        return self::getConsole()->writeln('<info>**** ' . $text . ' ****</info>');
    }

    /**
     * @return float
     */
    public static function getMicrotime()
    {
        if (version_compare(phpversion(), '5.0', '<') === true) {
            try {
                list($usec, $sec) = explode(" ", microtime());
                return ((float)$usec + (float)$sec);
            } catch (Exception $e) {
                self::error($e->getMessage());
            }
        } else {
            return microtime(true);
        }
    }

    /**
     * @param int $milliSeconds
     */
    public static function msleep($milliSeconds)
    {
        usleep($milliSeconds * 1000);
    }

    /**
     * @return bool
     */
    public static function isExecAvailable()
    {
        $available = true;
        if (ini_get('safe_mode')) {
            $available = false;
        } else {
            $disable = ini_get('disable_functions');
            $blacklist = ini_get('suhosin.executor.func.blacklist');
            if ($disable . $blacklist) {
                $array = preg_split('/,\s*/', $disable . ',' . $blacklist);
                if (in_array('exec', $array, true)) {
                    $available = false;
                }
            }
        }
        return $available;
    }
}
