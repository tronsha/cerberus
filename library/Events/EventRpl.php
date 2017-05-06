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
     * RPL_UNAWAY
     * :You are no longer marked as being away
     * @param string $rest
     * @param string $text
     */
    public function on305($rest, $text)
    {
        unset($rest);
        $this->event->runPluginEvent(__FUNCTION__, ['text' => $text]);
    }

    /**
     * RPL_NOWAWAY
     * :You have been marked as being away
     * @param string $rest
     * @param string $text
     */
    public function on306($rest, $text)
    {
        unset($rest);
        $this->event->runPluginEvent(__FUNCTION__, ['text' => $text]);
    }

    /**
     * RPL_WHOISUSER
     * <nick> <user> <host> * :<real name>
     * @param string $rest
     * @param string $text
     */
    public function on311($rest, $text)
    {
        list($me, $nick, $user, $host) = explode(' ', $rest);
        unset($me);
        $this->event->runPluginEvent(__FUNCTION__, ['nick' => $nick, 'host' => $user . '@' . $host, 'realname' => $text]);
    }

    /**
     * RPL_WHOISSERVER
     * <nick> <server> :<server info>
     * @param string $rest
     * @param string $text
     */
    public function on312($rest, $text)
    {
        list($me, $nick, $server) = explode(' ', $rest);
        unset($me);
        $this->event->runPluginEvent(__FUNCTION__, ['nick' => $nick, 'server' => $server, 'serverinfo' => $text]);
    }

    /**
     * RPL_WHOISIDLE
     * <nick> <integer> :seconds idle
     * @param string $rest
     * @param string $text
     */
    public function on317($rest, $text)
    {
        $keys = explode(',', $text);
        $values = explode(' ', $rest);
        array_shift($values);
        $nick = array_shift($values);
        $keys = array_map('trim', $keys);
        $values = array_map('trim', $values);
        $data = array_combine($keys, $values);
        $this->event->runPluginEvent(__FUNCTION__, ['nick' => $nick, 'list' => $data]);
    }

    /**
     * RPL_ENDOFWHOIS
     * <nick> :End of WHOIS list
     * @param string $rest
     * @param string $text
     */
    public function on318($rest, $text)
    {
        list($me, $nick) = explode(' ', $rest);
        unset($me);
        $this->event->runPluginEvent(__FUNCTION__, ['nick' => $nick, 'text' => $text]);
    }

    /**
     * RPL_WHOISCHANNELS
     * <nick> :{[@|+]<channel><space>}
     * @param string $rest
     * @param string $text
     */
    public function on319($rest, $text)
    {
        list($me, $nick) = explode(' ', $rest);
        unset($me);
        $this->event->runPluginEvent(__FUNCTION__, ['nick' => $nick, 'text' => $text]);
    }

    /**
     * RPL_LISTSTART
     * Channel :Users  Name
     * @param string $rest
     * @param string $text
     */
    public function on321($rest, $text)
    {
        unset($rest);
        $this->event->runPluginEvent(__FUNCTION__, ['text' => $text]);
    }

    /**
     * RPL_LIST
     * <channel> <# visible> :<topic>
     * @param string $rest
     * @param string $text
     */
    public function on322($rest, $text)
    {
        list($me, $channel, $usercount) = explode(' ', $rest);
        unset($me);
        $this->event->runPluginEvent(__FUNCTION__, ['channel' => $channel, 'usercount' => $usercount, 'topic' => $text]);
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
