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
 * Class EventErr
 * @package Cerberus\Plugins
 * @author Stefan Hüsges
 * @link http://tools.ietf.org/html/rfc2812
 */
class EventErr
{
    protected $irc = null;
    protected $event = null;

    /**
     * EventErr constructor.
     * @param Irc $irc
     * @param Event $event
     */
    public function __construct(Irc $irc, Event $event)
    {
        $this->irc = $irc;
        $this->event = $event;
    }

    /**
     * ERR_NOSUCHNICK
     * <nickname> :No such nick/channel
     * @param string $text
     */
    public function on401($text)
    {
        $this->event->getDb()->addStatus('401', $text, []);
        $this->event->runPluginEvent(__FUNCTION__, []);
    }

    /**
     * ERR_NOSUCHCHANNEL
     * <channel name> :No such channel
     * @param string $text
     */
    public function on403($text)
    {
        $this->event->getDb()->addStatus('403', $text, []);
        $this->event->runPluginEvent(__FUNCTION__, []);
    }

    /**
     * ERR_NONICKNAMEGIVEN
     * :No nickname given
     * @param string $text
     */
    public function on431($text)
    {
        $this->event->getDb()->addStatus('431', $text, []);
        $this->event->runPluginEvent(__FUNCTION__, []);
    }

    /**
     * ERR_ERRONEUSNICKNAME
     * <nick> :Erroneous nickname
     * @param string $text
     */
    public function on432($text)
    {
        $this->irc->otherNick();
        $this->event->getDb()->addStatus('432', $text, []);
        $this->event->runPluginEvent(__FUNCTION__, []);
    }

    /**
     * ERR_NICKNAMEINUSE
     * <nick> :Nickname is already in use
     * @param string $text
     */
    public function on433($text)
    {
        $this->irc->otherNick();
        $this->event->getDb()->addStatus('433', $text, []);
        $this->event->runPluginEvent(__FUNCTION__, []);
    }

    /**
     * ERR_UNAVAILRESOURCE
     * <nick/channel> :Nick/channel is temporarily unavailable
     */
    public function on437()
    {
        $this->irc->otherNick();
        $this->event->runPluginEvent(__FUNCTION__, []);
    }

    /**
     * ERR_NOTONCHANNEL
     * <channel> :You're not on that channel
     * @param string $rest
     * @param string $text
     */
    public function on442($rest, $text)
    {
        list($nick, $channel) = explode(' ', $rest);
        $this->event->getDb()->addStatus('442', $text, ['channel' => $channel, 'nick' => $nick]);
        $this->event->runPluginEvent(__FUNCTION__, []);
    }

    /**
     * ERR_USERONCHANNEL
     * <user> <channel> :is already on channel
     * @param string $rest
     * @param string $text
     */
    public function on443($rest, $text)
    {
        list($nick, $user, $channel) = explode(' ', $rest);
        $this->event->getDb()->addStatus('443', $user . ' ' . $text, ['channel' => $channel, 'nick' => $nick, 'user' => $user]);
        $this->event->runPluginEvent(__FUNCTION__, []);
    }

    /**
     * ERR_BADCHANNELKEY
     * <channel> :Cannot join channel (+k)
     * @param string $rest
     * @param string $text
     */
    public function on475($rest, $text)
    {
        list($nick, $channel) = explode(' ', $rest);
        $this->event->getDb()->addStatus('475', $text, ['channel' => $channel, 'nick' => $nick]);
        $this->event->runPluginEvent(__FUNCTION__, []);
    }

    /**
     * <channel> :Cannot join channel (+r) - you need to be identified with services
     * @param string $rest
     * @param string $text
     */
    public function on477($rest, $text)
    {
        list($nick, $channel) = explode(' ', $rest);
        $this->event->getDb()->addStatus('477', $text, ['channel' => $channel, 'nick' => $nick]);
        $this->event->runPluginEvent(__FUNCTION__, []);
    }

    /**
     * ERR_CHANOPRIVSNEEDED
     * <channel> :You're not channel operator
     * @param string $rest
     * @param string $text
     */
    public function on482($rest, $text)
    {
        list($nick, $channel) = explode(' ', $rest);
        $this->event->getDb()->addStatus('482', $text, ['channel' => $channel, 'nick' => $nick]);
        $this->event->runPluginEvent(__FUNCTION__, []);
    }
}
