<?php

/*
 * Cerberus IRCBot
 * Copyright (C) 2008 - 2016 Stefan Hüsges
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

use Exception;

/**
 * Class Action
 * @package Cerberus
 * @author Stefan Hüsges
 * @link http://www.mpcx.net/cerberus/ Project Homepage
 * @link https://github.com/tronsha/Cerberus Project on GitHub
 * @license http://www.gnu.org/licenses/gpl-3.0 GNU General Public License
 */

class Action
{
    protected $irc;
    protected $db;

    /**
     * Action constructor.
     * @param Irc|null $irc
     * @param Db|null $db
     * @throws Exception
     */
    public function __construct(Irc $irc = null, Db $db = null)
    {
        $this->irc = $irc;
        $this->db = $db;
    }

    /**
     * @throws Exception
     * @return Db|null
     */
    protected function getDb()
    {
        if ($this->irc !== null && $this->irc->getDb() instanceof Db) {
            return $this->irc->getDb();
        } elseif ($this->db !== null && $this->db instanceof Db) {
            return $this->db;
        } else {
            throw new Exception('database is not set');
        }
    }

    /**
     * @param string $pluginName
     */
    public function load($pluginName)
    {
        if ($this->irc !== null) {
            $this->irc->loadPlugin($pluginName);
        }
    }

    /**
     * @param string $command
     * @param string $param
     */
    public function control($command, $param = '')
    {
        $this->getDb()->addControl($command, $param);
    }

    /**
     * @param string $to
     * @param string $text
     * @return array
     */
    public function privmsg($to, $text)
    {
        $this->getDb()->addWrite('PRIVMSG ' . $to . ' :' . $text);
        return ['action' => 'privmsg', 'to' => $to, 'text' => $text];
    }

    /**
     * @param string $to
     * @param string $text
     * @return array
     */
    public function me($to, $text)
    {
        $this->getDb()->addWrite('PRIVMSG ' . $to . ' :' . "\x01" . 'ACTION ' . $text . "\x01");
        return ['action' => 'me', 'to' => $to, 'text' => $text];
    }

    /**
     * @param string $to
     * @param string $text
     * @return array
     */
    public function notice($to, $text)
    {
        $this->getDb()->addWrite('NOTICE ' . $to . ' :' . $text);
        return ['action' => 'notice', 'to' => $to, 'text' => $text];
    }

    /**
     * @param string $text
     * @return array
     */
    public function quit($text)
    {
        $this->getDb()->addWrite('QUIT :' . $text);
        return ['action' => 'quit', 'text' => $text];
    }

    /**
     * @param string|null $text
     * @return array
     */
    public function mode($text = null)
    {
        $this->getDb()->addWrite('MODE' . ($text === null ? '' : ' ' . $text));
        return ['action' => 'mode', 'text' => $text];
    }

    /**
     * @param string $channel
     * @return array
     */
    public function join($channel)
    {
        $this->getDb()->addWrite('JOIN ' . $channel);
        $exploded = explode(' ', trim($channel));
        $channel = explode(',', $exploded[0]);
        $password = isset($exploded[1]) === true ? explode(',', $exploded[1]) : [];
        return ['action' => 'join', 'channel' => $channel, 'password' => $password];
    }

    /**
     * @param string $channel
     * @return array
     */
    public function part($channel)
    {
        $this->getDb()->addWrite('PART ' . $channel);
        $channel = explode(',', $channel);
        return ['action' => 'part', 'channel' => $channel];
    }

    /**
     * @param string $nick
     * @return array
     */
    public function whois($nick)
    {
        $this->getDb()->addWrite('WHOIS :' . $nick);
        return ['action' => 'whois', 'nick' => $nick];
    }

    /**
     * @param string $nick
     * @return array
     */
    public function nick($nick)
    {
        if ($this->irc !== null) {
            $this->irc->setNick($nick);
        }
        $this->getDb()->addWrite('NICK :' . $nick);
        return ['action' => 'nick', 'nick' => $nick];
    }

    /**
     * @param string $channel
     * @param string $topic
     * @return array
     */
    public function topic($channel, $topic)
    {
        $this->getDb()->addWrite('TOPIC ' . $channel . ' :' . $topic);
        return ['action' => 'topic', 'channel' => $channel, 'topic' => $topic];
    }

    /**
     * @param string $channel
     * @param string $nick
     * @return array
     */
    public function invite($channel, $nick)
    {
        $this->getDb()->addWrite('INVITE ' . $nick . ' :' . $channel);
        return ['action' => 'invite', 'channel' => $channel, 'nick' => $nick];
    }

    /**
     * @param string $channel
     * @param string|null $nick
     * @return array
     * @link https://www.quakenet.org/help/q-commands/op
     */
    public function op($channel, $nick = null)
    {
        if ($this->getDb()->getServerName() === 'quakenet') {
            $master = 'Q';
        } else {
            $master = 'chanserv';
        }
        if ($nick === null) {
            $this->privmsg($master, 'OP ' . $channel);
        } else {
            $this->mode($channel . ' +o ' . $nick);
        }
        return ['action' => 'op', 'channel' => $channel, 'nick' => $nick];
    }

    /**
     * @param string $channel
     * @param string|null $nick
     * @return array
     */
    public function deop($channel, $nick = null)
    {
        $this->mode($channel . ' -o ' . $nick);
        return ['action' => 'deop', 'channel' => $channel, 'nick' => $nick];
    }

    /**
     * @param string $channel
     * @param string $user
     * @param string|null $comment
     * @return array
     */
    public function kick($channel, $user, $comment = null)
    {
        $this->getDb()->addWrite('KICK ' . $channel . ' ' . $user . ' :' . $comment);
        return ['action' => 'kick', 'channel' => $channel, 'user' => $user, 'comment' => $comment];
    }
}
