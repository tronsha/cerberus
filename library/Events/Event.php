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

/**
 * Class Event
 * @package Cerberus
 * @author Stefan Hüsges
 * @link http://www.mpcx.net/projekte/cerberus/ Project Homepage
 * @link https://github.com/tronsha/cerberus Project on GitHub
 * @license http://www.gnu.org/licenses/gpl-3.0 GNU General Public License
 */
abstract class Event
{
    protected $event = null;

    /**
     * @param \Cerberus\Event $event
     */
    public function __construct($event)
    {
        $this->setEvent($event);
    }

    /**
     * @return \Cerberus\Event|null
     */
    public function getEvent()
    {
        return $this->event;
    }

    /**
     * @param \Cerberus\Event $event
     */
    public function setEvent($event)
    {
        $this->event = $event;
    }

    /**
     * @return \Cerberus\Db|null
     */
    public function getDb()
    {
        return $this->getEvent()->getDb();
    }

    /**
     * @param string $event
     * @param array $data
     */
    public function runPluginEvent($event, $data)
    {
        return $this->getEvent()->runPluginEvent($event, $data);
    }
}
