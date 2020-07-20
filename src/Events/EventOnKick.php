<?php

/*
 * Cerberus IRCBot
 * Copyright (C) 2008 - 2020 Stefan Hüsges
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
 * Class EventOnKick
 * @package Cerberus
 * @author Stefan Hüsges
 * @link http://www.mpcx.net/projekte/cerberus/ Project Homepage
 * @link https://github.com/tronsha/cerberus Project on GitHub
 * @license http://www.gnu.org/licenses/gpl-3.0 GNU General Public License
 */
class EventOnKick extends Event
{
    /**
     * @param string $bouncer
     * @param string $host
     * @param string $rest
     * @param string $text
     */
    public function onKick($bouncer, $host, $rest, $text)
    {
        $vars = $this->getVars();
        list($channel, $nick) = explode(' ', $rest);
        $me = ($vars['var']['me'] === $nick) ? true : false;
        $this->runPluginEvent(__FUNCTION__, ['channel' => $channel, 'me' => $me, 'nick' => $nick, 'bouncer' => $bouncer, 'comment' => $text]);
        if (true === $me) {
            $this->getDb()->removeChannel($channel);
            $this->getDb()->addStatus('KICK', 'User ' . $bouncer . ' kicked you from channel ' . $channel . ' (' . $text . ')', ['channel' => $channel, 'nick' => $nick]);
            if (true === $this->getConfig()->getAutorejoin()) {
                $this->getActions()->join($channel);
            }
        } else {
            $this->getDb()->removeUserFromChannel($channel, $nick);
        }
    }
}
