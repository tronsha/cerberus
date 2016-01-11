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

namespace Cerberus;

use DateTime;

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
    protected $vars;
    protected $minute = '';
    protected $hour = '';
    protected $day_of_month = '';
    protected $month = '';
    protected $day_of_week = '';

    /**
     * Event constructor.
     * @param Irc $irc
     */
    public function __construct(Irc $irc)
    {
        $this->irc = $irc;
        $this->vars = $this->irc->getVars();
        $this->minute = (int)(new DateTime())->format('i');
        $this->hour = (int)(new DateTime())->format('G');
        $this->day_of_month = (int)(new DateTime())->format('j');
        $this->month = (int)(new DateTime())->format('n');
        $this->day_of_week = (int)(new DateTime())->format('w');
    }

    /**
     * @return Db
     */
    protected function getDb()
    {
        return $this->irc->getDb();
    }

    /**
     *
     */
    public function onConnect()
    {
        $this->irc->runPluginEvent(__FUNCTION__, []);
    }

    /**
     *
     */
    public function onShutdown()
    {
        $this->irc->runPluginEvent(__FUNCTION__, []);
    }

    /**
     * @param string $text
     */
    public function onError($text)
    {
        $this->irc->runPluginEvent(__FUNCTION__, ['error' => $text]);
    }

    /**
     *
     */
    public function onTick()
    {
        $this->irc->runPluginEvent(__FUNCTION__, []);
        $minute = (int)(new DateTime())->format('i');
        $hour = (int)(new DateTime())->format('G');
        $day_of_month = (int)(new DateTime())->format('j');
        $month = (int)(new DateTime())->format('n');
        $day_of_week = (int)(new DateTime())->format('w');
        if ($day_of_week != $this->day_of_week) {
            $this->day_of_week = $day_of_week;
        }
        if ($month != $this->month) {
            $this->month = $month;
        }
        if ($day_of_month != $this->day_of_month) {
            $this->day_of_month = $day_of_month;
        }
        if ($hour != $this->hour) {
            $this->hour = $hour;
            $this->onHour();
        }
        if ($minute != $this->minute) {
            $this->minute = $minute;
            $this->onMinute();
        }
    }

    /**
     *
     */
    public function onMinute()
    {
        $this->irc->runPluginEvent(__FUNCTION__, []);
        $this->irc->runCron($this->minute, $this->hour, $this->day_of_month, $this->month, $this->day_of_week);
    }

    /**
     *
     */
    public function onHour()
    {
        $this->irc->runPluginEvent(__FUNCTION__, []);
    }

    /**
     * @param string $command
     * @param string $data
     */
    public function onControl($command, $data)
    {
        $data['command'] = $command;
        $this->irc->runPluginEvent(__FUNCTION__, $data);
    }

    /**
     * ERR_NONICKNAMEGIVEN
     * :No nickname given
     */
    public function on431()
    {
        $this->irc->runPluginEvent(__FUNCTION__, []);
    }

    /**
     * ERR_ERRONEUSNICKNAME
     * <nick> :Erroneous nickname
     */
    public function on432()
    {
        $this->irc->otherNick();
        $this->irc->runPluginEvent(__FUNCTION__, []);
    }

    /**
     * ERR_NICKNAMEINUSE
     * <nick> :Nickname is already in use
     */
    public function on433()
    {
        $this->irc->otherNick();
        $this->irc->runPluginEvent(__FUNCTION__, []);
    }

    /**
     * ERR_UNAVAILRESOURCE
     * <nick/channel> :Nick/channel is temporarily unavailable
     */
    public function on437()
    {
        $this->irc->otherNick();
        $this->irc->runPluginEvent(__FUNCTION__, []);
    }

    /**
     * RPL_LIST
     * <channel> <# visible> :<topic>
     * @param string $rest
     * @param string $text
     */
    public function on322($rest, $text)
    {
        $this->irc->runPluginEvent(__FUNCTION__, ['rest' => $rest, 'text' => $text]);
    }

    /**
     * RPL_LISTEND
     * :End of LIST
     */
    public function on323()
    {
        $this->irc->runPluginEvent(__FUNCTION__, []);
    }

    /**
     * RPL_CHANNELMODEIS
     * <channel> <mode> <mode params>
     */
    public function on324()
    {
        $this->irc->runPluginEvent(__FUNCTION__, []);
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
        $this->irc->runPluginEvent(__FUNCTION__, ['nick' => $nick, 'host' => $user . '@' . $host]);
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
        $this->irc->runPluginEvent(__FUNCTION__, ['nick' => $nick, 'auth' => $auth]);
    }

    /**
     * RPL_ENDOFWHOIS
     * <nick> :End of WHOIS list
     */
    public function on318()
    {
        $this->irc->runPluginEvent(__FUNCTION__, []);
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
        $this->vars = $this->irc->getVars();
        if (preg_match("/\x01([A-Z]+)( [0-9\.]+)?\x01/i", $text, $matches)) {
            if ($this->irc->getConfig()->getCtcp() === false) {
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
                    $send = 'VERSION ' . $this->irc->getConfig()->getVersion('bot');
                    break;
                case 'TIME':
                    $send = 'TIME ' . date('D M d H:i:s Y T');
                    break;
                case 'FINGER':
                    $botName = $this->irc->getConfig()->getName();
                    $botHomepage = $this->irc->getConfig()->getHomepage();
                    $send = 'FINGER ' . $botName . (empty($botHomepage) === false ? ' (' . $botHomepage . ')' : '') . ' Idle ' . round($this->irc->getMicrotime() - $this->vars['time']['irc_connect']) . ' seconds';
                    break;
                case 'SOURCE':
                    $send = 'SOURCE https://github.com/tronsha/cerberus';
                    break;
                default:
                    return null;
            }
            if (empty($send) === false) {
                $this->irc->getActions()->notice($nick, "\x01" . $send . "\x01");
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
                                    ['nick' => $nick, 'host' => $host, 'channel' => $channel, 'text' => $text]
                                );
                            }
                        }
                    }
                    break;
                default:
                    $this->irc->runPluginEvent(
                        __FUNCTION__,
                        ['nick' => $nick, 'host' => $host, 'channel' => $channel, 'text' => $text]
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
        $this->irc->runPluginEvent(__FUNCTION__, ['nick' => $nick, 'text' => $text]);
    }

    /**
     * @param string $nick
     * @param string $text
     */
    public function onNick($nick, $text)
    {
        $this->vars = $this->irc->getVars();
        if ($nick == $this->vars['var']['me']) {
            $this->irc->setNick($text);
        }
        $this->getDb()->changeNick($nick, $text);
        $this->irc->runPluginEvent(__FUNCTION__, ['nick' => $nick, 'text' => $text]);
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
        $this->irc->runPluginEvent(__FUNCTION__, []);
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
        $this->irc->runPluginEvent(__FUNCTION__, []);
    }

    /**
     * @param string $channel
     * @param string $topic
     */
    public function onTopic($channel, $topic)
    {
        $this->getDb()->setChannelTopic($channel, $topic);
        $this->irc->runPluginEvent(__FUNCTION__, ['channel' => $channel, 'topic' => $topic]);
    }

    /**
     * @param string $nick
     * @param string $channel
     */
    public function onJoin($nick, $channel)
    {
        $this->vars = $this->irc->getVars();
        if ($nick == $this->vars['var']['me']) {
            $this->getDb()->addChannel($channel);
            $this->irc->getActions()->mode($channel);
        } else {
            $this->getDb()->addUserToChannel($channel, $nick);
        }
        $this->irc->runPluginEvent(__FUNCTION__, ['nick' => $nick, 'channel' => $channel]);
    }

    /**
     * @param string $bouncer
     * @param string $rest
     */
    public function onKick($bouncer, $rest)
    {
        $this->vars = $this->irc->getVars();
        list($channel, $nick) = explode(' ', $rest);
        $me = $nick == $this->vars['var']['me'] ? true : false;
        $this->onPart($nick, $channel);
        if ($this->irc->getConfig()->getAutorejoin() === true && $me === true) {
            $this->irc->getActions()->join($channel);
        }
        $this->irc->runPluginEvent(__FUNCTION__, ['channel' => $channel, 'me' => $me, 'nick' => $nick, 'bouncer' => $bouncer]);
    }

    /**
     * @param string $nick
     * @param string $channel
     */
    public function onPart($nick, $channel)
    {
        $this->vars = $this->irc->getVars();
        $me = $nick == $this->vars['var']['me'] ? true : false;
        $this->irc->runPluginEvent(__FUNCTION__, ['channel' => $channel, 'me' => $me, 'nick' => $nick]);
        if ($me === true) {
            $this->getDb()->removeChannel($channel);
        } else {
            $this->getDb()->removeUserFromChannel($channel, $nick);
        }
    }

    /**
     * @param string $nick
     */
    public function onQuit($nick)
    {
        $this->irc->runPluginEvent(__FUNCTION__, ['nick' => $nick]);
        $this->getDb()->removeUser($nick);
    }

    /**
     * @param string $mode
     */
    public function onMode($mode)
    {
        $this->irc->runPluginEvent(__FUNCTION__, ['mode' => $mode]);
    }

    /**
     * @param string $channel
     * @param string $host
     * @param string $rest
     */
    public function onInvite($channel, $host, $rest)
    {
        $this->irc->runPluginEvent(__FUNCTION__, ['channel' => $channel, 'host' => $host, 'rest' => $rest]);
    }
}
