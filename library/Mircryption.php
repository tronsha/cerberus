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

use Exception;

/**
 * Class Mircryption
 * @package Cerberus
 * @author Stefan Hüsges
 * @link http://www.mpcx.net/cerberus/ Project Homepage
 * @link https://github.com/tronsha/Cerberus Project on GitHub
 * @link http://tools.ietf.org/html/rfc2812 Internet Relay Chat: Client Protocol
 * @link https://www.donationcoder.com/Software/Mouser/mircryption/index.php Mircryption
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
     * @return string
     */
    public static function padPKCS7($text)
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
    public static function unpadPKCS7($text)
    {
        $last = substr($text, -1);
        $padding = ord($last);
        if ($padding > 0 && $padding < 8) {
            $text = substr($text, 0, -$padding);
        }
        return $text;
    }

    /**
     * @param string $text
     * @param string $key
     * @return string
     * @link http://php.net/manual/en/function.mcrypt-encrypt.php
     */
    public static function encode($text, $key)
    {
        $iv = random_bytes(8);
        $encodedText = mcrypt_encrypt(MCRYPT_BLOWFISH, $key, self::padPKCS7($text), MCRYPT_MODE_CBC, $iv);
        $encodedTextIv = $iv . $encodedText;
        $decodedTextBaseIv64 = base64_encode($encodedTextIv);
        return '*' . $decodedTextBaseIv64;
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
        $encodedTextIv = base64_decode($encodedTextIvBase64, true);
        $iv = substr($encodedTextIv, 0, 8);
        $encodedText = substr($encodedTextIv, 8);
        $plaintext = self::unpadPKCS7(mcrypt_decrypt(MCRYPT_BLOWFISH, $key, $encodedText, MCRYPT_MODE_CBC, $iv));
        return trim($plaintext);
    }

    /**
     * @param string $text
     * @param string $key
     * @return string
     * @link http://php.net/manual/en/function.openssl-encrypt.php
     */
    public static function _encode($text, $key)
    {
        $iv = random_bytes(8);
        $encodedText = openssl_encrypt($text, 'bf-cbc', $key, OPENSSL_RAW_DATA, $iv);
        $encodedTextIv = $iv . $encodedText;
        $decodedTextBaseIv64 = base64_encode($encodedTextIv);
        return '*' . $decodedTextBaseIv64;
    }

    /**
     * @param string $text
     * @param string $key
     * @return string
     * @link http://php.net/manual/en/function.openssl-decrypt.php
     */
    public static function _decode($text, $key)
    {
        $encodedTextIvBase64 = str_replace('*', '', $text);
        $encodedTextIv = base64_decode($encodedTextIvBase64, true);
        $iv = substr($encodedTextIv, 0, 8);
        $encodedText = substr($encodedTextIv, 8);
        $plaintext = openssl_decrypt($encodedText, 'bf-cbc', $key, OPENSSL_RAW_DATA, $iv);
        return trim($plaintext);
    }
}
