<?php

/*
 * Cerberus IRCBot
 * Copyright (C) 2008 - 2017 Stefan Hüsges
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

namespace Cerberus\Crypt;

/**
 * Class Pkcs7
 * @package Cerberus
 * @author Stefan Hüsges
 * @link http://www.mpcx.net/cerberus/ Project Homepage
 * @link https://github.com/tronsha/Cerberus Project on GitHub
 * @link http://tools.ietf.org/html/rfc2812 Internet Relay Chat: Client Protocol
 * @license http://www.gnu.org/licenses/gpl-3.0 GNU General Public License
 */

class Pkcs7
{
    /**
     * @param string $text
     * @return string
     */
    public static function pad($text)
    {
        $length = mb_strlen($text, '8bit');
        $mod = $length % 8;
        if ($mod !== 0) {
            $padding = 8 - $mod;
            $text .= str_repeat(chr($padding), $padding);
        }
        return $text;
    }

    /**
     * @param string $text
     * @return string
     */
    public static function unpad($text)
    {
        $last = substr($text, -1);
        $padding = ord($last);
        if ($padding > 0 && $padding < 8) {
            $text = substr($text, 0, -$padding);
        }
        return $text;
    }
}
