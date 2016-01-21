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
 * Class Ccryption
 * compressed and encrypted conversation
 * @package Cerberus
 * @author Stefan Hüsges
 * @link http://www.mpcx.net/cerberus/ Project Homepage
 * @link https://github.com/tronsha/Cerberus Project on GitHub
 * @link http://tools.ietf.org/html/rfc2812 Internet Relay Chat: Client Protocol
 * @license http://www.gnu.org/licenses/gpl-3.0 GNU General Public License
 */
class Ccryption
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
     * @link http://php.net/manual/en/function.mcrypt-encrypt.php
     * @link http://php.net/manual/en/function.gzcompress.php
     * @link http://php.net/manual/en/function.crc32.php
     */
    public static function encode($text, $key)
    {
        $hash = hash('sha256', $key, true);
        $crc = hash('crc32b', $text);
        $compressed = gzcompress($text, 9);
        $iv = mcrypt_create_iv(mcrypt_get_iv_size(MCRYPT_BLOWFISH, MCRYPT_MODE_CBC), MCRYPT_RAND);
        $compressedEncodedText = mcrypt_encrypt(MCRYPT_BLOWFISH, $hash, $compressed, MCRYPT_MODE_CBC, $iv);
        $compressedEncodedTextIv = $iv . $compressedEncodedText;
        $compressedEncodedTextIv64 = base64_encode($compressedEncodedTextIv);
        $compressedEncodedTextIv64Crc = $crc . $compressedEncodedTextIv64;
        return $compressedEncodedTextIv64Crc;
    }

    /**
     * @param string $text
     * @param string $key
     * @return string|null
     * @link http://php.net/manual/en/function.mcrypt-decrypt.php
     * @link http://php.net/manual/en/function.gzuncompress.php
     * @link http://php.net/manual/en/function.crc32.php
     */
    public static function decode($text, $key)
    {
        $hash = hash('sha256', $key, true);
        $checkValue = substr($text, 0, 8);
        $compressedEncodedTextIv64 = substr($text, 8);
        $compressedEncodedTextIv = base64_decode($compressedEncodedTextIv64);
        $iv = substr($compressedEncodedTextIv, 0, 8);
        $compressedEncodedText = substr($compressedEncodedTextIv, 8);
        $compressed = mcrypt_decrypt(MCRYPT_BLOWFISH, $hash, $compressedEncodedText, MCRYPT_MODE_CBC, $iv);
        $plaintext = gzuncompress($compressed);
        return ($checkValue === hash('crc32b', $plaintext)) ? $plaintext : null;
    }
}
