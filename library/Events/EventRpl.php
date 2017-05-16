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

use Cerberus\Event;
use Cerberus\Irc;

/**
 * Class EventRpl
 * @package Cerberus\Events
 * @author Stefan Hüsges
 * @link http://tools.ietf.org/html/rfc2812
 */
class EventRpl
{
    protected $irc = null;
    protected $event = null;

    /**
     * EventRpl constructor.
     * @param Irc $irc
     * @param Event $event
     */
    public function __construct(Irc $irc, Event $event)
    {
        $this->irc = $irc;
        $this->event = $event;
    }

    /**
     * RPL_LISTEND
     * :End of LIST
     * @param string $rest
     * @param string $text
     */
    public function on323($rest, $text)
    {
        unset($rest);
        $this->event->runPluginEvent(__FUNCTION__, ['text' => $text]);
    }

    /**
     * RPL_CHANNELMODEIS
     * <channel> <mode> <mode params>
     * @param string $rest
     * @param string $text
     */
    public function on324($rest, $text)
    {
        unset($text);
        $list = explode(' ', $rest, 4);
        $channel = empty($list[1]) === false ? $list[1] : '';
        $mode = empty($list[2]) === false ? $list[2] : '';
        $params = empty($list[3]) === false ? explode(' ', $list[3]) : [];
        $this->event->runPluginEvent(__FUNCTION__, ['channel' => $channel, 'mode' => $mode, 'params' => $params]);
    }

    /**
     * RPL_WHOISACCOUNT
     * :is logged in as
     * @param string $rest
     * @param string $text
     */
    public function on330($rest, $text)
    {
        list($me, $nick, $auth) = explode(' ', $rest);
        unset($me);
        $this->event->runPluginEvent(__FUNCTION__, ['nick' => $nick, 'auth' => $auth, 'text' => $text]);
    }

    /**
     * RPL_TOPIC
     * <channel> :<topic>
     * @param string $rest
     * @param string $topic
     */
    public function on332($rest, $topic)
    {
        list($me, $channel) = explode(' ', $rest);
        unset($me);
        $this->event->getDb()->setChannelTopic($channel, $topic);
        $this->event->runPluginEvent(__FUNCTION__, ['channel' => $channel, 'topic' => $topic]);
    }

    /**
     * RPL_NAMREPLY
     * @param string $rest
     * @param string $text
     */
    public function on353($rest, $text)
    {
        list($me, $dummy, $channel) = explode(' ', $rest);
        unset($me, $dummy);
        $user_array = explode(' ', $text);
        foreach ($user_array as $user) {
            preg_match("/^([\+\@])?([^\+\@]+)$/i", $user, $matches);
            $this->event->getDb()->addUserToChannel($channel, $matches[2], $matches[1]);
        }
        $this->event->runPluginEvent(__FUNCTION__, ['channel' => $channel, 'user' => $user_array]);
    }

    /**
     * @param string $rest
     * @param string $text
     */
    public function on378($rest, $text)
    {
        list($me, $nick) = explode(' ', $rest);
        unset($me);
        $this->event->runPluginEvent(__FUNCTION__, ['nick' => $nick, 'text' => $text]);
    }

    /**
     * @param string $rest
     * @param string $text
     */
    public function on671($rest, $text)
    {
        list($me, $nick) = explode(' ', $rest);
        unset($me);
        $this->event->runPluginEvent(__FUNCTION__, ['nick' => $nick, 'text' => $text]);
    }
}
