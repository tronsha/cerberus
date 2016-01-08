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
 * Class Mircryption
 * @package Cerberus
 * @author Stefan Hüsges
 * @link http://www.mpcx.net/cerberus/ Project Homepage
 * @link https://github.com/tronsha/Cerberus Project on GitHub
 * @link http://tools.ietf.org/html/rfc2812 Internet Relay Chat: Client Protocol
 * @license http://www.gnu.org/licenses/gpl-3.0 GNU General Public License
 */

class Mircryption
{
    /**
     * @throws Exception
     */
    public function __construct()
    {
        if (extension_loaded('mcrypt') === false) {
            throw new Exception('The mcrypt extension is not available.');
        }
    }

    /**
     * @param string $text
     * @param string $key
     * @return string
     * @link http://php.net/manual/en/function.mcrypt-decrypt.php
     */
    public static function decode($text, $key)
    {
        $encodedTextIvBase64 = str_replace('*', '', $text);
        $encodedTextIv = base64_decode($encodedTextIvBase64);
        $iv = substr($encodedTextIv, 0, 8);
        $encodedText = substr($encodedTextIv, 8);
        $plaintext = mcrypt_decrypt(MCRYPT_BLOWFISH, $key, $encodedText, MCRYPT_MODE_CBC, $iv);
        return trim($plaintext);
    }

    /**
     * @param string $text
     * @param string $key
     * @return string
     * @link http://php.net/manual/en/function.mcrypt-encrypt.php
     */
    public static function encode($text, $key)
    {
        $iv = mcrypt_create_iv(mcrypt_get_iv_size(MCRYPT_BLOWFISH, MCRYPT_MODE_CBC), MCRYPT_RAND);
        $encodedText = mcrypt_encrypt(MCRYPT_BLOWFISH, $key, $text, MCRYPT_MODE_CBC, $iv);
        $encodedTextIv = $iv . $encodedText;
        $decodedTextBaseIv64 = base64_encode($encodedTextIv);
        return '*' . $decodedTextBaseIv64;
    }
}
