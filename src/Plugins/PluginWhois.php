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

namespace Cerberus\Plugins;

use Cerberus\Plugin;

/**
 * Class PluginWhois
 * @package Cerberus\Plugins
 * @author Stefan Hüsges
 */
class PluginWhois extends Plugin
{
    private $cache = [];

    /**
     * @link https://www.alien.net.au/irc/irc2numerics.html IRC/2 Numeric List
     */
    protected function init()
    {
        $this->addEvent('on311');
        $this->addEvent('on319');
        $this->addEvent('on312');
        $this->addEvent('on378');
        $this->addEvent('on317');
        $this->addEvent('on330');
        $this->addEvent('on318');
        $this->addEvent('on401');
        $this->addEvent('on301');
        $this->addEvent('on671');
    }

    /**
     * RPL_AWAY
     * @param array $data
     */
    public function on301($data)
    {
    }

    /**
     * RPL_WHOISUSER
     * @param array $data
     */
    public function on311($data)
    {
        $this->cache[strtolower($data['nick'])]['time'] = time();
        $this->cache[strtolower($data['nick'])][311]['host'] = $data['host'];
        $this->cache[strtolower($data['nick'])][311]['realname'] = $data['realname'];
    }

    /**
     * RPL_WHOISSERVER
     * @param array $data
     */
    public function on312($data)
    {
    }

    /**
     * RPL_WHOISIDLE
     * @param array $data
     */
    public function on317($data)
    {
        if (true === isset($data['list']['seconds idle'])) {
            $this->cache[strtolower($data['nick'])][317]['idle'] = $data['list']['seconds idle'];
        }
        if (true === isset($data['list']['seconds idle'])) {
            $this->cache[strtolower($data['nick'])][317]['signon'] = $data['list']['signon time'];
        }
    }

    /**
     * RPL_ENDOFWHOIS
     * @param array $data
     */
    public function on318($data)
    {
        $nick = strtolower($data['nick']);
        if (false === isset($this->cache[$nick][401])) {
            $output = 'Nick: ' . $data['nick'] . PHP_EOL;
            if (true === isset($this->cache[$nick][311]['realname'])) {
                $output .= 'Realname: ' . $this->cache[$nick][311]['realname'] . PHP_EOL;
            }
            if (true === isset($this->cache[$nick][311]['host'])) {
                $hostArray = explode('@', $this->cache[$nick][311]['host']);
                $output .= 'Host: ' . $hostArray[1] . PHP_EOL;
            }
            if (true === isset($this->cache[$nick][317]['idle'])) {
                $time = $this->cache[$nick][317]['idle'];
                $d = ($time-($time%86400))/86400;
                $h = (($time-($time%3600))%86400)/3600;
                $m = (($time-($time%60))%3600)/60;
                $s = $time%60;
                $output .= 'Idle: ' . $d . 'd ' . $h . 'h ' . $m . 'm ' . $s . 's' . PHP_EOL;
            }
            if (true === isset($this->cache[$nick][317]['signon'])) {
                $output .= 'Signon: ' . date('H:i:s Y-m-d', $this->cache[$nick][317]['signon']) . PHP_EOL;
            }
            if (true === isset($this->cache[$nick][319]['channel'])) {
                $output .= 'Channel: ' . $this->cache[$nick][319]['channel'] . PHP_EOL;
            }
            $this->getDb()->addStatus('WHOIS', $output, []);
        }
    }

    /**
     * RPL_WHOISCHANNELS
     * @param array $data
     */
    public function on319($data)
    {
        $this->cache[strtolower($data['nick'])][319]['channel'] = $data['text'];
    }

    /**
     * RPL_WHOISACCOUNT
     * @param array $data
     */
    public function on330($data)
    {
    }

    /**
     * @param array $data
     */
    public function on378($data)
    {
    }

    /**
     * ERR_NOSUCHNICK
     * @param array $data
     */
    public function on401($data)
    {
        $this->cache[strtolower($data['nick'])][401] = $data['text'];
    }

    /**
     * RPL_WHOISSECURE
     * @param array $data
     */
    public function on671($data)
    {
    }
}
