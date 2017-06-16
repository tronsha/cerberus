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

namespace Cerberus\Events;

/**
 * Class EventOn324
 * @package Cerberus
 * @author Stefan Hüsges
 * @link http://www.mpcx.net/projekte/cerberus/ Project Homepage
 * @link https://github.com/tronsha/cerberus Project on GitHub
 * @link https://tools.ietf.org/html/rfc1459#page-50 Command response 323 - RFC1459
 * @link https://tools.ietf.org/html/rfc2812#page-46 Command response 323 - RFC2812
 * @license http://www.gnu.org/licenses/gpl-3.0 GNU General Public License
 */
class EventOn324 extends Event
{
    /**
     * RPL_CHANNELMODEIS
     * <channel> <mode> <mode params>
     * @param string $nick
     * @param string $host
     * @param string $rest
     * @param string $text
     */
    public function on324($nick, $host, $rest, $text)
    {
        unset($text);
        $list = explode(' ', $rest, 4);
        $channel = empty($list[1]) === false ? $list[1] : '';
        $mode = empty($list[2]) === false ? $list[2] : '';
        $params = empty($list[3]) === false ? explode(' ', $list[3]) : [];
        $this->runPluginEvent(__FUNCTION__, ['channel' => $channel, 'mode' => $mode, 'params' => $params]);
    }
}
