<?php

/*
 * Cerberus IRCBot
 * Copyright (C) 2008 - 2018 Stefan Hüsges
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
    protected $vars;
    protected $minute = '';
    protected $hour = '';
    protected $day_of_month = '';
    protected $month = '';
    protected $day_of_week = '';
    protected $dateTime = null;

    /**
     * Event constructor.
     * @param Irc $irc
     */
    public function __construct(Irc $irc)
    {
        parent::__construct($irc);
        $this->setNamespace('\\Cerberus\\Events\\Event');
        $this->vars = $this->irc->getVars();
        $this->minute = intval((new DateTime())->format('i'));
        $this->hour = intval((new DateTime())->format('G'));
        $this->day_of_month = intval((new DateTime())->format('j'));
        $this->month = intval((new DateTime())->format('n'));
        $this->day_of_week = intval((new DateTime())->format('w'));
        $this->dateTime = new DateTime();
    }

    /**
     * @param string $name
     * @return mixed
     */
    protected function loadClass($name)
    {
        if (false === in_array($name, $this->getEventList(), true)) {
            return false;
        }
        return parent::loadClass($name);
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
        if (null !== $this->list) {
            return $this->list;
        }
        $listClasses = [];
        $dir = Cerberus::getPath() . DIRECTORY_SEPARATOR . 'library' . DIRECTORY_SEPARATOR . 'Events' . DIRECTORY_SEPARATOR;
        $files = glob($dir . 'EventOn*.php');
        foreach ($files as $file) {
            $listClasses[] = lcfirst(str_replace([$dir . 'Event' , '.php'], '', $file));
        }
        $listThis = get_class_methods($this);
        $list = array_merge($listClasses, $listThis);
        foreach ($list as $key => $value) {
            if ('on' !== substr($value, 0, 2)) {
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
        $minute = intval((new DateTime())->format('i'));
        $hour = intval((new DateTime())->format('G'));
        $day_of_month = intval((new DateTime())->format('j'));
        $month = intval((new DateTime())->format('n'));
        $day_of_week = intval((new DateTime())->format('w'));
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
        $this->getIrc()->runCron($this->dateTime);
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
}
