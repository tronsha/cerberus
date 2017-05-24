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
 * Class EventRpl
 * @package Cerberus\Events
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
}
