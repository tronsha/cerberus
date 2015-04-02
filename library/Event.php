<?php

/*   Cerberus IRCBot
 *   Copyright (C) 2008 - 2015 Stefan Hüsges
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

namespace Cerberus;

/**
 * Class Event
 * @package Cerberus
 * @author Stefan Hüsges
 * @link http://www.mpcx.net/cerberus/ Project Homepage
 * @link https://github.com/tronsha/Cerberus Project on GitHub
 * @license http://www.gnu.org/licenses/gpl-3.0 GNU General Public License
 */

class Event
{
    protected $irc;
    protected $db;

    /**
     * @param Irc $irc
     * @param Db $db
     */
    public function __construct(Irc $irc, Db $db)
    {
        $this->irc = $irc;
        $this->db = $db;
    }

    /**
     * ERR_NONICKNAMEGIVEN
     * :No nickname given
     */
    public function on431()
    {
    }

    /**
     * ERR_ERRONEUSNICKNAME
     * <nick> :Erroneous nickname
     */
    public function on432()
    {
        $this->irc->otherNick();
    }

    /**
     * ERR_NICKNAMEINUSE
     * <nick> :Nickname is already in use
     */
    public function on433()
    {
        $this->irc->otherNick();
    }

    /**
     * ERR_UNAVAILRESOURCE
     * <nick/channel> :Nick/channel is temporarily unavailable
     */
    public function on437()
    {
        $this->irc->otherNick();
    }

    /**
     * RPL_LIST
     * <channel> <# visible> :<topic>
     * @param string $rest
     * @param string $text
     */
    public function on322($rest, $text)
    {
        $this->irc->runPluginEvent(__FUNCTION__, array('rest' => $rest, 'text' => $text));
    }

    /**
     * RPL_LISTEND
     * :End of LIST
     */
    public function on323()
    {
        $this->irc->runPluginEvent(__FUNCTION__, array());
    }

    /**
     * RPL_CHANNELMODEIS
     * <channel> <mode> <mode params>
     */
    public function on324()
    {
        $this->irc->runPluginEvent(__FUNCTION__, array());
    }

    /**
     * RPL_WHOISUSER
     * <nick> <user> <host> * :<real name>
     * @param string $rest
     */
    public function on311($rest)
    {
        list($me, $nick, $user, $host) = explode(' ', $rest);
        unset($me);
        $this->irc->runPluginEvent(__FUNCTION__, array('nick' => $nick, 'host' => $user . '@' . $host));
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
        $this->irc->runPluginEvent(__FUNCTION__, array('nick' => $nick, 'auth' => $auth));
    }

    /**
     * RPL_ENDOFWHOIS
     * <nick> :End of WHOIS list
     */
    public function on318()
    {
        $this->irc->runPluginEvent(__FUNCTION__, array());
    }

    /**
     * @link http://www.irchelp.org/irchelp/rfc/ctcpspec.html
     * @param string $nick
     * @param string $host
     * @param string $channel
     * @param string $text
     * @return null
     */
    public function onPrivmsg($nick, $host, $channel, $text)
    {
        if (preg_match("/\x01([A-Z]+)( [0-9\.]+)?\x01/i", $text, $matches)) {
            if ($this->irc->config['ctcp'] === false) {
                return null;
            }
            $send = '';
            switch ($matches[1]) {
                case 'ACTION':
                    break;
                case 'CLIENTINFO':
                    $send = 'CLIENTINFO PING VERSION TIME FINGER SOURCE CLIENTINFO';
                    break;
                case 'PING':
                    $send = 'PING' . $matches[2];
                    break;
                case 'VERSION':
                    $send = 'VERSION ' . $this->irc->version['bot'];
                    break;
                case 'TIME':
                    $send = 'TIME ' . date('D M d H:i:s Y T');
                    break;
                case 'FINGER':
                    $send = 'FINGER ' . $this->irc->config['info']['name'] . (isset($this->irc->config['info']['homepage']) ? ' (' . $this->irc->config['info']['homepage'] . ')' : '') . ' Idle ' . round(
                            $this->irc->getMicrotime() - $this->irc->time['irc_connect']
                        ) . ' seconds';
                    break;
                case 'SOURCE':
                    $send = 'SOURCE https://github.com/tronsha/cerberus';
                    break;
                default:
                    return null;
            }
            if (empty($send) === false) {
                $this->irc->notice($nick, "\x01" . $send . "\x01");
            }
        } else {
            $splitText = explode(' ', $text);
            switch ($splitText[0]) {
                case '!load':
                    if (empty($splitText[1]) === false) {
                        if ($this->irc->isAdmin($nick, $host) === true) {
                            if (preg_match('/^[a-z]+$/i', $splitText[1]) > 0) {
                                $this->irc->loadPlugin(
                                    $splitText[1],
                                    array('nick' => $nick, 'host' => $host, 'channel' => $channel, 'text' => $text)
                                );
                            }
                        }
                    }
                    break;
                default:
                    $this->irc->runPluginEvent(
                        __FUNCTION__,
                        array('nick' => $nick, 'host' => $host, 'channel' => $channel, 'text' => $text)
                    );
                    return null;
            }
        }
    }

    /**
     * @param string $nick
     * @param string $text
     */
    public function onNotice($nick, $text)
    {
        $this->irc->runPluginEvent(__FUNCTION__, array('nick' => $nick, 'text' => $text));
    }

    /**
     * @param string $nick
     * @param string $text
     */
    public function onNick($nick, $text)
    {
        if ($nick == $this->irc->var['me']) {
            $this->irc->setNick($text);
        }
        $this->db->changeNick($nick, $text);
        $this->irc->runPluginEvent(__FUNCTION__, array('nick' => $nick, 'text' => $text));
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
            $this->db->addUserToChannel($channel, $matches[2], $matches[1]);
        }
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
    }

    /**
     * @param string $channel
     * @param string $topic
     */
    public function onTopic($channel, $topic)
    {
        $this->db->setChannelTopic($channel, $topic);
        $this->irc->runPluginEvent(__FUNCTION__, array('channel' => $channel, 'topic' => $topic, ));
    }

    /**
     * @param string $nick
     * @param string $channel
     */
    public function onJoin($nick, $channel)
    {
        if ($nick == $this->irc->var['me']) {
            $this->db->addChannel($channel);
            $this->irc->mode($channel);
        } else {
            $this->db->addUserToChannel($channel, $nick);
        }
        $this->irc->runPluginEvent(__FUNCTION__, array('nick' => $nick, 'channel' => $channel));
    }

    /**
     * @param string $bouncer
     * @param string $rest
     */
    public function onKick($bouncer, $rest)
    {
        list($channel, $nick) = explode(' ', $rest);
        $me = $nick == $this->irc->var['me'] ? true : false;
        $this->onPart($nick, $channel);
        if ($this->irc->config['autorejoin'] === true && $me === true) {
            $this->irc->join($channel);
        }
        $this->irc->runPluginEvent(
            __FUNCTION__,
            array('channel' => $channel, 'me' => $me, 'nick' => $nick, 'bouncer' => $bouncer)
        );
    }

    /**
     * @param string $nick
     * @param string $channel
     */
    public function onPart($nick, $channel)
    {
        $me = $nick == $this->irc->var['me'] ? true : false;
        if ($me === true) {
            $this->db->removeChannel($channel);
        } else {
            $this->db->removeUserFromChannel($channel, $nick);
        }
        $this->irc->runPluginEvent(__FUNCTION__, array('channel' => $channel, 'me' => $me, 'nick' => $nick));
    }

    /**
     * @param string $nick
     */
    public function onQuit($nick)
    {
        $this->db->removeUser($nick);
        $this->irc->runPluginEvent(__FUNCTION__, array('nick' => $nick));
    }

    /**
     * @param string $mode
     */
    public function onMode($mode)
    {
        $this->irc->runPluginEvent(__FUNCTION__, array('mode' => $mode));
    }

    /**
     * @param string $channel
     * @param string $host
     * @param string $rest
     */
    public function onInvite($channel, $host, $rest)
    {
        $this->irc->runPluginEvent(__FUNCTION__, array('channel' => $channel, 'host' => $host, 'rest' => $rest));
    }
}
