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

namespace Cerberus\Plugins;

use Cerberus\Plugin;

/**
 * Class PluginUrl
 * @package Cerberus\Plugins
 * @author Stefan Hüsges
 */
class PluginUrl extends Plugin
{
    private $file = null;

    /**
     *
     */
    protected function init()
    {
        $this->addEvent('onPrivmsg');
        $this->file = realpath($this->getConfig()->getLogfiledirectory()) . '/url.txt';
    }

    /**
     * @param array $data
     * @return bool
     */
    public function onLoad($data)
    {
        $returnValue = parent::onLoad($data);
        if (null !== $data) {
        }
        return $returnValue;
    }

    /**
     * @param array $data
     * @return bool
     */
    public function onPrivmsg($data)
    {
        $urls = $this->parseUrls($data['text']);
        $this->writeToFile($urls);
    }

    /**
     * @param string $text
     * @return array
     */
    protected function parseUrls($text)
    {
        $urls = [];
        preg_match_all('/(?:^|\s)([^\s\w])?([a-zA-Z]+:\/\/\S+)(?:\s|$)/si', $text, $matches, PREG_SET_ORDER);
        foreach ($matches as $matche) {
            $trim = " \t\n\r\0\x0B";
            if (false === empty($matche[1])) {
                $trim .= $matche[1];
                if ('(' === $matche[1]) {
                    $trim .= ')';
                }
            }
            $urls[] = trim($matche[2], $trim);
        }
        return $urls;
    }

    /**
     * @param array|null $urls
     */
    protected function writeToFile($urls = null)
    {
        if (null !== $urls) {
            $handle = @fopen($this->file, 'a+');
            if (false !== $handle) {
                foreach ($urls as $url) {
                    fwrite($handle, $url . "\n");
                    fflush($handle);
                }
                fclose($handle);
            }
        }
    }
}
