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
 * Class EventOn432
 * @package Cerberus
 * @author Stefan Hüsges
 * @link http://www.mpcx.net/projekte/cerberus/ Project Homepage
 * @link https://github.com/tronsha/cerberus Project on GitHub
 * @link https://tools.ietf.org/html/rfc1459#page-45 Error Replies 432 - RFC1459
 * @link https://tools.ietf.org/html/rfc2812#page-55 Error Replies 432 - RFC2812
 * @license http://www.gnu.org/licenses/gpl-3.0 GNU General Public License
 */
class EventOn432 extends Event
{
    /**
     * ERR_ERRONEUSNICKNAME
     * <nick> :Erroneous nickname
     * @param string $rest
     * @param string $text
     */
    public function on432($rest, $text)
    {
        list($me, $nick) = explode(' ', $rest);
        unset($me);
        $data = ['nick' => $nick, 'text' => $text];
        $this->getIrc()->otherNick();
        $this->getDb()->addStatus('432', $this->__($text), $data);
        $this->runPluginEvent(__FUNCTION__, $data);
    }
}
