<?php

/*
 * Cerberus IRCBot
 * Copyright (C) 2008 - 2018 Stefan Hüsges
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

namespace Cerberus\Plugins;

use Cerberus\Crypt;
use Cerberus\Plugin;

/**
 * Class PluginCrypt
 * @package Cerberus\Plugins
 * @author Stefan Hüsges
 * @link http://www.mpcx.net/projekte/cerberus/ Project Homepage
 * @link https://github.com/tronsha/cerberus Project on GitHub
 * @link http://tools.ietf.org/html/rfc2812 Internet Relay Chat: Client Protocol
 * @license http://www.gnu.org/licenses/gpl-3.0 GNU General Public License
 */
class PluginCrypt extends Plugin
{
    private $cryptkey = [];
    private $crypt = null;

    /**
     *
     */
    protected function init()
    {
        $this->addEvent('onPrivmsg', null, 10);
        $this->crypt = new Crypt;
    }

    /**
     * @param array $data
     * @param bool $hasChild
     * @return bool
     */
    public function onLoad($data, $hasChild = false)
    {
        $returnValue = parent::onLoad($data, true);
        if (true === $hasChild) {
            return $returnValue;
        }
        if (null !== $data) {
            $this->getActions()->notice($data['nick'], 'New Command: !cryptkey [#channel] [key]');
        }
        return $returnValue;
    }

    /**
     * @param array $data
     */
    public function onPrivmsg(&$data)
    {
        $splitText = explode(' ', $data['text']);
        $command = array_shift($splitText);
        if ('+OK' === $command) {
            $key = empty($this->cryptkey[$data['channel']]) ? '123456' : $this->cryptkey[$data['channel']];
            $data['text'] = $this->decodeMircryption(array_shift($splitText), $key);
        } elseif ('!cryptkey' === strtolower($command) && true === $this->isAdmin($data['nick'], $data['host'])) {
            $channel = array_shift($splitText);
            $key = array_shift($splitText);
            $this->cryptkey[$channel] = $key;
        }
    }

    /**
     * @param string $text
     * @param string $key
     * @return string
     */
    protected function decodeMircryption($text, $key = '123456')
    {
        return $this->crypt->decode('mircryption', $text, $key);
    }

    /**
     * @param string $text
     * @param string $key
     * @return string
     */
    protected function encodeMircryption($text, $key = '123456')
    {
        return '+OK ' . $this->crypt->encode('mircryption', $text, $key);
    }
}
