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
        $this->addEvent('on322');
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
     * @param array $data
     * @return bool
     */
    public function on322($data)
    {
        $urls = $this->parseUrls($data['topic']);
        $this->writeToFile($urls);
    }

    /**
     * @param string $text
     * @return array
     */
    protected function parseUrls($text)
    {
        $urls = [];
        $pairs = ['(' => ')', '<' => '>', '[' => ']', '{' => '}'];
        preg_match_all('/(?:^|\s)([\"\'\(\<\[\{])?\s*([a-zA-Z]+:\/\/\S+)(?:\s|\xC2\xA0|$)/si', $text, $matches, PREG_SET_ORDER);
        foreach ($matches as $match) {
            $charlist = "\x00\x01\x02\x03\x04\x05\x06\x07\x08\x09\x0A\x0B\x0C\x0D\x0E\x0F\x10\x11\x12\x13\x14\x15\x16\x17\x18\x19\x1A\x1B\x1C\x1D\x1E\x1F\x20\x2C\x2E\x7F";
            $charlist .= '“”';
            $delimiter = $match[1];
            $url = $match[2];
            if (false === empty($delimiter)) {
                $charlist .= $delimiter;
                if (true === array_key_exists($delimiter, $pairs)) {
                    $charlist .= $pairs[$delimiter];
                    $delimiter = $pairs[$delimiter];
                }
                if (false !== strpos($url, $delimiter)) {
                    $parts = explode($delimiter, strrev($url), 2);
                    $url = strrev($parts[1]);
                }
            }
            $urls[] = trim($url, $charlist);
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
