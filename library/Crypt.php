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

use Cerberus\Crypt\Ccryption;
use Cerberus\Crypt\Mircryption;

/**
 * Class Crypt
 * @package Cerberus
 * @author Stefan Hüsges
 * @link http://www.mpcx.net/cerberus/ Project Homepage
 * @link https://github.com/tronsha/Cerberus Project on GitHub
 * @link http://tools.ietf.org/html/rfc2812 Internet Relay Chat: Client Protocol
 * @license http://www.gnu.org/licenses/gpl-3.0 GNU General Public License
 */
class Crypt
{
    private $ccryption = null;
    private $mircryption = null;

    /**
     * @param string $cryption
     * @param string $text
     * @param string $key
     *
     * @return string
     */
    public function encode($cryption, $text, $key)
    {
        return $this->getCryption($cryption)->encode($text, $key);
    }

    /**
     * @param string $cryption
     * @param string $text
     * @param string $key
     *
     * @return null|string
     */
    public function decode($cryption, $text, $key)
    {
        return $this->getCryption($cryption)->decode($text, $key);
    }

    /**
     * @param string $cryption
     *
     * @return bool|Ccryption|Mircryption|null
     */
    public function getCryption($cryption)
    {
        switch ($cryption) {
            case 'ccryption':
                return $this->getCcryption();
                break;
            case 'mircryption':
                return $this->getMircryption();
                break;
        }
        return false;
    }

    /**
     * @return Ccryption|null
     */
    public function getCcryption()
    {
        if ($this->ccryption === null) {
            $this->ccryption = new Ccryption;
        }
        return $this->ccryption;
    }

    /**
     * @return Mircryption|null
     */
    public function getMircryption()
    {
        if ($this->mircryption === null) {
            $this->mircryption = new Mircryption;
        }
        return $this->mircryption;
    }
}
