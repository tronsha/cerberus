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
        if (function_exists('random_bytes') === true) {
            return random_bytes($length);
        } elseif (function_exists('openssl_random_pseudo_bytes') === true) {
            return openssl_random_pseudo_bytes($length);
        }
        return false;
    }
}
