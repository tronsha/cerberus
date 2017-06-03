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
 * Class EventErr
 * @package Cerberus\Events
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
     * ERR_INVALIDUSERNAME
     * <user> :<text>
     * @param string $rest
     * @param string $text
     */
    public function on468($rest, $text)
    {
        $data = ['user' => $rest, 'text' => $text];
        $this->event->runPluginEvent(__FUNCTION__, $data);
    }

    /**
     * <channel> <forwarding> :Forwarding to another channel
     * @param string $rest
     * @param string $text
     */
    public function on470($rest, $text)
    {
        list($me, $channel, $forwarding) = explode(' ', $rest);
        unset($me);
        $data = ['channel' => $channel, 'forwarding' => $forwarding, 'text' => $text];
        $this->event->getDb()->addStatus('470', $this->irc->__($text . ': %channel%', ['%channel%' => $forwarding]), $data);
        $this->event->runPluginEvent(__FUNCTION__, $data);
    }

    /**
     * ERR_CHANNELISFULL
     * <channel> :Cannot join channel (+l)
     * @param string $rest
     * @param string $text
     */
    public function on471($rest, $text)
    {
        list($nick, $channel) = explode(' ', $rest);
        $data = ['channel' => $channel, 'nick' => $nick, 'text' => $text];
        $this->event->getDb()->addStatus('471', $this->irc->__($text), $data);
        $this->event->runPluginEvent(__FUNCTION__, $data);
    }

    /**
     * ERR_INVITEONLYCHAN
     * <channel> :Cannot join channel (+i)
     * @param string $rest
     * @param string $text
     */
    public function on473($rest, $text)
    {
        list($nick, $channel) = explode(' ', $rest);
        $data = ['channel' => $channel, 'nick' => $nick, 'text' => $text];
        $this->event->getDb()->addStatus('473', $this->irc->__($text), $data);
        $this->event->runPluginEvent(__FUNCTION__, $data);
    }

    /**
     * ERR_BANNEDFROMCHAN
     * <channel> :Cannot join channel (+b)
     * @param string $rest
     * @param string $text
     */
    public function on474($rest, $text)
    {
        list($nick, $channel) = explode(' ', $rest);
        $data = ['channel' => $channel, 'nick' => $nick, 'text' => $text];
        $this->event->getDb()->addStatus('474', $this->irc->__($text), $data);
        $this->event->runPluginEvent(__FUNCTION__, $data);
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
        $data = ['channel' => $channel, 'nick' => $nick, 'text' => $text];
        $this->event->getDb()->addStatus('475', $this->irc->__($text), $data);
        $this->event->runPluginEvent(__FUNCTION__, $data);
    }

    /**
     * <channel> :Cannot join channel (+r) - you need to be identified with services
     * @param string $rest
     * @param string $text
     */
    public function on477($rest, $text)
    {
        list($nick, $channel) = explode(' ', $rest);
        $data = ['channel' => $channel, 'nick' => $nick, 'text' => $text];
        $this->event->getDb()->addStatus('477', $this->irc->__($text), $data);
        $this->event->runPluginEvent(__FUNCTION__, $data);
    }

    /**
     * <channel> :Illegal channel name
     * @param string $rest
     * @param string $text
     */
    public function on479($rest, $text)
    {
        list($nick, $channel) = explode(' ', $rest);
        $data = ['channel' => $channel, 'nick' => $nick, 'text' => $text];
        $this->event->getDb()->addStatus('479', $this->irc->__($text), $data);
        $this->event->runPluginEvent(__FUNCTION__, $data);
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
        $data = ['channel' => $channel, 'nick' => $nick, 'text' => $text];
        $this->event->getDb()->addStatus('482', $this->irc->__($text), $data);
        $this->event->runPluginEvent(__FUNCTION__, $data);
    }
}
