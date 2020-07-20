<?php

/*
 * Cerberus IRCBot
 * Copyright (C) 2008 - 2020 Stefan Hüsges
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

namespace Cerberus;

/**
 * Class Php
 * @package Cerberus
 * @author Stefan Hüsges
 * @link http://www.mpcx.net/projekte/cerberus/ Project Homepage
 * @link https://github.com/tronsha/cerberus Project on GitHub
 */
class Php
{
    /**
     * @param mixed $var
     * @return bool
     */
    public static function boolval($var)
    {
        return (bool)$var;
    }

    /**
     * @param int $length
     * @return string
     */
    public static function randombytes($length)
    {
        if (true === function_exists('random_bytes')) {
            return random_bytes($length);
        } elseif (true === extension_loaded('openssl') && true === function_exists('openssl_random_pseudo_bytes')) {
            return openssl_random_pseudo_bytes($length);
        } elseif (true === extension_loaded('mcrypt') && true === function_exists('mcrypt_create_iv')) {
            return mcrypt_create_iv($length);
        }
        return false;
    }

    /**
     * @param string $str
     * @return int
     */
    public static function strlen($str)
    {
        if (true === extension_loaded('mbstring')) {
            return mb_strlen($str, '8bit');
        } else {
            return strlen($str);
        }
    }
}
