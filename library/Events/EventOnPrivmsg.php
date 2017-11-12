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
 * Class EventOnPrivmsg
 * @package Cerberus
 * @author Stefan Hüsges
 * @link http://www.mpcx.net/projekte/cerberus/ Project Homepage
 * @link https://github.com/tronsha/cerberus Project on GitHub
 * @license http://www.gnu.org/licenses/gpl-3.0 GNU General Public License
 */
class EventOnPrivmsg extends Event
{
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
        $vars = $this->getVars();
        if (preg_match("/\x01([A-Z]+)( [0-9\.]+)?\x01/i", $text, $matches)) {
            if (false === $this->getConfig()->getCtcp()) {
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
                    $send = 'VERSION ' . $this->getConfig()->getVersion('bot');
                    break;
                case 'TIME':
                    $send = 'TIME ' . date('D M d H:i:s Y T');
                    break;
                case 'FINGER':
                    $botName = $this->getConfig()->getName();
                    $botHomepage = $this->getConfig()->getHomepage();
                    $send = 'FINGER ' . $botName . (false === empty($botHomepage) ? ' (' . $botHomepage . ')' : '') . ' Idle ' . (true === isset($vars['time']['irc_connect']) ? round(microtime(true) - $vars['time']['irc_connect']) : 0) . ' seconds';
                    break;
                case 'SOURCE':
                    $send = 'SOURCE https://github.com/tronsha/cerberus';
                    break;
                default:
                    return null;
            }
            if (false === empty($send)) {
                $this->getActions()->notice($nick, "\x01" . $send . "\x01");
            }
        } else {
            $splitText = explode(' ', $text);
            switch ($splitText[0]) {
                case '!load':
                    if (false === empty($splitText[1])) {
                        if (true === $this->getIrc()->isAdmin($nick, $host)) {
                            if (preg_match('/^[a-z]+$/i', $splitText[1]) > 0) {
                                $this->getIrc()->loadPlugin(
                                    $splitText[1],
                                    ['nick' => $nick, 'host' => $host, 'channel' => $channel, 'text' => $text]
                                );
                            }
                        }
                    }
                    break;
                case '!unload':
                    if (false === empty($splitText[1])) {
                        if (true === $this->getIrc()->isAdmin($nick, $host)) {
                            if (preg_match('/^[a-z]+$/i', $splitText[1]) > 0) {
                                $this->getIrc()->unloadPlugin(
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
}
