<?php

/*
 * Cerberus IRCBot
 * Copyright (C) 2008 - 2016 Stefan Hüsges
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
 * @package Cerberus\Plugins
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
     * RPL_LIST
     * <channel> <# visible> :<topic>
     * @param string $rest
     * @param string $text
     */
    public function on322($rest, $text)
    {
        $this->event->runPluginEvent(__FUNCTION__, ['rest' => $rest, 'text' => $text]);
    }

    /**
     * RPL_LISTEND
     * :End of LIST
     */
    public function on323()
    {
        $this->event->runPluginEvent(__FUNCTION__, []);
    }

    /**
     * RPL_CHANNELMODEIS
     * <channel> <mode> <mode params>
     */
    public function on324()
    {
        $this->event->runPluginEvent(__FUNCTION__, []);
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
     * @param string $text
     */
    public function on332($rest, $text)
    {
        list($me, $channel) = explode(' ', $rest);
        unset($me);
        $this->event->onTopic($channel, $text);
        $this->event->runPluginEvent(__FUNCTION__, []);
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
        $this->event->runPluginEvent(__FUNCTION__, []);
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
}
