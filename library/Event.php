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

namespace Cerberus;

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
class Event extends Helper
{
    protected $irc = null;
    protected $list = null;
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
        parent::__construct($irc);
        $this->setNamespace('\Cerberus\Events\Event');
        $this->vars = $this->irc->getVars();
        $this->minute = (int)(new DateTime())->format('i');
        $this->hour = (int)(new DateTime())->format('G');
        $this->day_of_month = (int)(new DateTime())->format('j');
        $this->month = (int)(new DateTime())->format('n');
        $this->day_of_week = (int)(new DateTime())->format('w');
    }

    /**
     * @param string $name
     * @return mixed
     */
    protected function loadClass($name)
    {
        if (in_array($name, $this->getEventList(), true) === false) {
            return false;
        }
        return parent::loadClass($name);
    }

    /**
     * @return EventErr|null
     */
    public function getErr()
    {
        if ($this->err === null) {
            $this->err = new EventErr($this->irc, $this);
        }
        return $this->err;
    }

    /**
     * @return Irc|null
     */
    public function getIrc()
    {
        return $this->irc;
    }

    /**
     * @return Db
     */
    public function getDb()
    {
        return $this->getIrc()->getDb();
    }

    /**
     * @return Config|null
     */
    public function getConfig()
    {
        return $this->getIrc()->getConfig();
    }

    /**
     * @return array
     */
    public function getVars()
    {
        return $this->getIrc()->getVars();
    }

    /**
     * @param string $text
     * @param array $array
     * @param mixed $lang
     * @return string
     */
    public function __($text, $array = [], $lang = null)
    {
        return $this->getIrc()->__($text, $array, $lang);
    }

    /**
     * @return Action|null
     */
    public function getActions()
    {
        return $this->getIrc()->getActions();
    }

    /**
     *
     */
    public function otherNick()
    {
        return $this->getIrc()->otherNick();
    }

    /**
     * @return array
     */
    public function getEventList()
    {
        if ($this->list !== null) {
            return $this->list;
        }
        $listClasses = [];
        $dir = Cerberus::getPath() . DIRECTORY_SEPARATOR . 'library' . DIRECTORY_SEPARATOR . 'Events' . DIRECTORY_SEPARATOR;
        $files = glob($dir . 'EventOn*.php');
        foreach ($files as $file) {
            $listClasses[] = lcfirst(str_replace([$dir . 'Event' , '.php'], '', $file));
        }
        $listThis = get_class_methods($this);
        $listErr = get_class_methods($this->getErr());
        $list = array_merge($listClasses, $listThis, $listErr);
        foreach ($list as $key => $value) {
            if (substr($value, 0, 2) !== 'on') {
                unset($list[$key]);
            }
        }
        $this->list = $list;
        return $this->list;
    }

    /**
     * @param string $event
     * @param array $data
     */
    public function runPluginEvent($event, $data)
    {
        $this->getIrc()->runPluginEvent($event, $data);
    }

    /**
     * @param string $command
     * @param string $rest
     * @param string $text
     */
    public function err($command, $rest, $text)
    {
        switch ($command) {
            case '473':
                $this->getErr()->on473($rest, $text);
                break;
            case '474':
                $this->getErr()->on474($rest, $text);
                break;
            case '475':
                $this->getErr()->on475($rest, $text);
                break;
            case '477':
                $this->getErr()->on477($rest, $text);
                break;
            case '479':
                $this->getErr()->on479($rest, $text);
                break;
            case '482':
                $this->getErr()->on482($rest, $text);
                break;
            default:
                $eventName = 'on' . $command;
                $this->$eventName($rest, $text);
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
        if ($day_of_week !== $this->day_of_week) {
            $this->day_of_week = $day_of_week;
        }
        if ($month !== $this->month) {
            $this->month = $month;
        }
        if ($day_of_month !== $this->day_of_month) {
            $this->day_of_month = $day_of_month;
        }
        if ($hour !== $this->hour) {
            $this->hour = $hour;
            $this->onHour();
        }
        if ($minute !== $this->minute) {
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
        $this->getIrc()->runCron($this->minute, $this->hour, $this->day_of_month, $this->month, $this->day_of_week);
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
        $this->vars = $this->getIrc()->getVars();
        if (preg_match("/\x01([A-Z]+)( [0-9\.]+)?\x01/i", $text, $matches)) {
            if ($this->getIrc()->getConfig()->getCtcp() === false) {
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
                    $send = 'PING' . (isset($matches[2]) ? $matches[2] : '');
                    break;
                case 'VERSION':
                    $send = 'VERSION ' . $this->getIrc()->getConfig()->getVersion('bot');
                    break;
                case 'TIME':
                    $send = 'TIME ' . date('D M d H:i:s Y T');
                    break;
                case 'FINGER':
                    $botName = $this->getIrc()->getConfig()->getName();
                    $botHomepage = $this->getIrc()->getConfig()->getHomepage();
                    $send = 'FINGER ' . $botName . (empty($botHomepage) === false ? ' (' . $botHomepage . ')' : '') . ' Idle ' . (isset($this->vars['time']['irc_connect']) ? round(microtime(true) - $this->vars['time']['irc_connect']) : 0) . ' seconds';
                    break;
                case 'SOURCE':
                    $send = 'SOURCE https://github.com/tronsha/cerberus';
                    break;
                default:
                    return null;
            }
            if (empty($send) === false) {
                $this->getIrc()->getActions()->notice($nick, "\x01" . $send . "\x01");
            }
        } else {
            $splitText = explode(' ', $text);
            switch ($splitText[0]) {
                case '!load':
                    if (empty($splitText[1]) === false) {
                        if ($this->getIrc()->isAdmin($nick, $host) === true) {
                            if (preg_match('/^[a-z]+$/i', $splitText[1]) > 0) {
                                $this->getIrc()->loadPlugin(
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
        $nick = (empty($nick) === true)? '*' : $nick;
        $data = ['nick' => $nick, 'text' => $text];
        $this->getDb()->addStatus($nick, $text, $data);
        $this->runPluginEvent(__FUNCTION__, $data);
    }

    /**
     * @param string $nick
     * @param string $text
     */
    public function onNick($nick, $text)
    {
        $this->vars = $this->getIrc()->getVars();
        if ($nick === $this->vars['var']['me']) {
            $this->getIrc()->setNick($text);
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
}
