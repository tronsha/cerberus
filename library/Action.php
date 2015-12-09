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
        if ($this->irc !== null) {
            $this->db = $this->irc->getDB();
        } elseif ($db !== null) {
            $this->db = $db;
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
     * @param string $to
     * @param string $text
     */
    public function privmsg($to, $text)
    {
        $this->db->setWrite('PRIVMSG ' . $to . ' :' . $text);
    }

    /**
     * @param string $to
     * @param string $text
     */
    public function me($to, $text)
    {
        $this->db->setWrite('PRIVMSG ' . $to . ' :' . "\x01" . 'ACTION ' . $text . "\x01");
    }

    /**
     * @param string $to
     * @param string $text
     */
    public function notice($to, $text)
    {
        $this->db->setWrite('NOTICE ' . $to . ' :' . $text);
    }

    /**
     * @param string $text
     */
    public function quit($text)
    {
        $this->db->setWrite('QUIT :' . $text);
    }

    /**
     * @param string|null $text
     */
    public function mode($text = null)
    {
        $this->db->setWrite('MODE' . ($text === null ? '' : ' ' . $text));
    }

    /**
     * @param string $channel
     * @return array
     */
    public function join($channel)
    {
        $this->db->setWrite('JOIN ' . $channel);
        return ['action' => 'join', 'channel' => $channel];
    }

    /**
     * @param string $channel
     * @return array
     */
    public function part($channel)
    {
        $this->db->setWrite('PART ' . $channel);
        return ['action' => 'part', 'channel' => $channel];
    }

    /**
     * @param string $nick
     */
    public function whois($nick)
    {
        $this->db->setWrite('WHOIS :' . $nick);
    }

    /**
     * @param string $nick
     */
    public function nick($nick)
    {
        if ($this->irc !== null) {
            $this->irc->setNick($nick);
        }
        $this->db->setWrite('NICK :' . $nick);
    }
}
