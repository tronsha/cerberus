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

use Cerberus\Events\EventRpl;
use Cerberus\Events\EventErr;
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
    protected $rpl = null;
    protected $err = null;
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
     * @return EventRpl|null
     */
    protected function getRpl()
    {
        if ($this->rpl === null) {
            $this->rpl = new EventRpl($this->irc);
        }
        return $this->rpl;
    }

    /**
     * @return EventErr|null
     */
    protected function getErr()
    {
        if ($this->err === null) {
            $this->err = new EventErr($this->irc);
        }
        return $this->err;
    }

    /**
     * @return Db
     */
    protected function getDb()
    {
        return $this->irc->getDb();
    }

    /**
     * @param string $event
     * @param array $data
     */
    protected function runPluginEvent($event, $data)
    {
        $this->irc->runPluginEvent($event, $data);
    }

    /**
     * @param string $command
     * @param string $rest
     * @param string $text
     */
    public function rpl($command, $rest, $text)
    {
        switch ($command) {
            case '311':
                $this->getRpl()->on311($rest);
                break;
            case '318':
                $this->getRpl()->on318();
                break;
            case '319':
                $this->getRpl()->on319($text);
                break;
            case '322':
                $this->getRpl()->on322($rest, $text);
                break;
            case '323':
                $this->getRpl()->on323();
                break;
            case '324':
                $this->getRpl()->on324();
                break;
            case '330':
                $this->getRpl()->on330($rest);
                break;
            case '332':
                $this->getRpl()->on332($rest, $text);
                break;
            case '353':
                $this->getRpl()->on353($rest, $text);
                break;
        }
    }

    /**
     * @param string $command
     * @param string $rest
     * @param string $text
     */
    public function err($command, $rest, $text)
    {
        switch ($command) {
            case '401':
                $this->getErr()->on401($text);
                break;
            case '403':
                $this->getErr()->on403($text);
                break;
            case '431':
                $this->getErr()->on431($text);
                break;
            case '432':
                $this->getErr()->on432($text);
                break;
            case '433':
                $this->getErr()->on433($text);
                break;
            case '437':
                $this->getErr()->on437();
                break;
            case '442':
                $this->getErr()->on442($rest, $text);
                break;
            case '443':
                $this->getErr()->on443($rest, $text);
                break;
            case '475':
                $this->getErr()->on475($rest, $text);
                break;
            case '477':
                $this->getErr()->on477($rest, $text);
                break;
            case '482':
                $this->getErr()->on482($rest, $text);
                break;
        }
    }

    /**
     *
     */
    public function onConnect()
    {
        $this->runPluginEvent(__FUNCTION__, []);
    }

    /**
     *
     */
    public function onShutdown()
    {
        $this->runPluginEvent(__FUNCTION__, []);
    }

    /**
     * @param string $text
     */
    public function onError($text)
    {
        $this->runPluginEvent(__FUNCTION__, ['error' => $text]);
    }

    /**
     *
     */
    public function onTick()
    {
        $this->runPluginEvent(__FUNCTION__, []);
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
        $this->runPluginEvent(__FUNCTION__, []);
        $this->irc->runCron($this->minute, $this->hour, $this->day_of_month, $this->month, $this->day_of_week);
    }

    /**
     *
     */
    public function onHour()
    {
        $this->runPluginEvent(__FUNCTION__, []);
    }

    /**
     * @param string $command
     * @param string $data
     */
    public function onControl($command, $data)
    {
        $data['command'] = $command;
        $this->runPluginEvent(__FUNCTION__, $data);
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
                    $this->runPluginEvent(
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
        $this->runPluginEvent(__FUNCTION__, ['nick' => $nick, 'text' => $text]);
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
        $this->runPluginEvent(__FUNCTION__, ['nick' => $nick, 'text' => $text]);
    }

    /**
     * @param string $channel
     * @param string $topic
     */
    public function onTopic($channel, $topic)
    {
        $this->getDb()->setChannelTopic($channel, $topic);
        $this->runPluginEvent(__FUNCTION__, ['channel' => $channel, 'topic' => $topic]);
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
        $this->runPluginEvent(__FUNCTION__, ['nick' => $nick, 'channel' => $channel]);
    }

    /**
     * @param string $bouncer
     * @param string $rest
     * @param string $text
     */
    public function onKick($bouncer, $rest, $text)
    {
        $this->vars = $this->irc->getVars();
        list($channel, $nick) = explode(' ', $rest);
        $me = $nick == $this->vars['var']['me'] ? true : false;
        $this->onPart($nick, $channel);
        if ($this->irc->getConfig()->getAutorejoin() === true && $me === true) {
            $this->irc->getActions()->join($channel);
        }
        if ($me === true) {
            $this->getDb()->addStatus('KICK', 'User ' . $bouncer . ' kicked you from channel ' . $channel . ' (' . $text . ')', ['channel' => $channel, 'nick' => $nick]);
        }
        $this->runPluginEvent(__FUNCTION__, ['channel' => $channel, 'me' => $me, 'nick' => $nick, 'bouncer' => $bouncer, 'comment' => $text]);
    }

    /**
     * @param string $nick
     * @param string $channel
     */
    public function onPart($nick, $channel)
    {
        $this->vars = $this->irc->getVars();
        $me = $nick == $this->vars['var']['me'] ? true : false;
        $this->runPluginEvent(__FUNCTION__, ['channel' => $channel, 'me' => $me, 'nick' => $nick]);
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
        $this->runPluginEvent(__FUNCTION__, ['nick' => $nick]);
        $this->getDb()->removeUser($nick);
    }

    /**
     * @param string $mode
     */
    public function onMode($mode)
    {
        $this->runPluginEvent(__FUNCTION__, ['mode' => $mode]);
    }

    /**
     * @param string $nick
     * @param string $host
     * @param string $rest
     * @param string $channel
     */
    public function onInvite($nick, $host, $rest, $channel)
    {
        $this->getDb()->addStatus('INVITE', 'User ' . $nick . ' inviting you to channel ' . $channel, ['channel' => $channel, 'nick' => $nick]);
        $this->runPluginEvent(__FUNCTION__, ['channel' => $channel, 'nick' => $nick, 'host' => $host, 'rest' => $rest]);
    }
}
