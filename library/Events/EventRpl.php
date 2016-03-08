<?php

/*   Cerberus IRCBot
 *   Copyright (C) 2008 - 2016 Stefan Hüsges
 *
 *   This program is free software; you can redistribute it and/or modify it
 *   under the terms of the GNU General Public License as published by the Free
 *   Software Foundation; either version 3 of the License, or (at your option)
 *   any later version.
 *
 *   This program is distributed in the hope that it will be useful, but
 *   WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY
 *   or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License
 *   for more details.
 *
 *   You should have received a copy of the GNU General Public License along
 *   with this program; if not, see <http://www.gnu.org/licenses/>.
 */

namespace Cerberus\Events;

use Cerberus\Event;

/**
 * Class EventRpl
 * @package Cerberus\Plugins
 * @author Stefan Hüsges
 * @link http://tools.ietf.org/html/rfc2812
 */
class EventRpl extends Event
{
    /**
     * RPL_WHOISUSER
     * <nick> <user> <host> * :<real name>
     * @param string $rest
     */
    public function on311($rest)
    {
        list($me, $nick, $user, $host) = explode(' ', $rest);
        unset($me);
        $this->runPluginEvent(__FUNCTION__, ['nick' => $nick, 'host' => $user . '@' . $host]);
    }

    /**
     * RPL_ENDOFWHOIS
     * <nick> :End of WHOIS list
     */
    public function on318()
    {
        $this->runPluginEvent(__FUNCTION__, []);
    }

    /**
     * RPL_WHOISCHANNELS
     * <nick> :{[@|+]<channel><space>}
     */
    public function on319($text)
    {
        $this->runPluginEvent(__FUNCTION__, ['text' => $text]);
    }

    /**
     * RPL_LIST
     * <channel> <# visible> :<topic>
     * @param string $rest
     * @param string $text
     */
    public function on322($rest, $text)
    {
        $this->runPluginEvent(__FUNCTION__, ['rest' => $rest, 'text' => $text]);
    }

    /**
     * RPL_LISTEND
     * :End of LIST
     */
    public function on323()
    {
        $this->runPluginEvent(__FUNCTION__, []);
    }

    /**
     * RPL_CHANNELMODEIS
     * <channel> <mode> <mode params>
     */
    public function on324()
    {
        $this->runPluginEvent(__FUNCTION__, []);
    }

    /**
     * RPL_WHOISACCOUNT
     * :is logged in as
     * @param string $rest
     */
    public function on330($rest)
    {
        list($me, $nick, $auth) = explode(' ', $rest);
        unset($me);
        $this->runPluginEvent(__FUNCTION__, ['nick' => $nick, 'auth' => $auth]);
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
        $this->onTopic($channel, $text);
        $this->runPluginEvent(__FUNCTION__, []);
    }

    /**
     * RPL_NAMREPLY
     * @param string $rest
     * @param string $text
     */
    public function on353($rest, $text)
    {
        list($me, $dummy, $channel) = explode(' ', $rest);
        unset($me);
        unset($dummy);
        $user_array = explode(' ', $text);
        foreach ($user_array as $user) {
            preg_match("/^([\+\@])?([^\+\@]+)$/i", $user, $matches);
            $this->getDb()->addUserToChannel($channel, $matches[2], $matches[1]);
        }
        $this->runPluginEvent(__FUNCTION__, []);
    }
}
