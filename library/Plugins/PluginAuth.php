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

namespace Cerberus\Plugins;

use Cerberus\Plugin;

/**
 * Class PluginAuth
 * @package Cerberus\Plugins
 * @author Stefan Hüsges
 * @link https://freenode.net/faq.shtml#registering
 * @link https://www.quakenet.org/help/q-commands/auth
 * @link http://tools.ietf.org/html/rfc2812
 */
class PluginAuth extends Plugin
{
    private $auth = [];

    /**
     *
     */
    protected function init()
    {
        $this->addEvent('on311');
        $this->addEvent('on330');
        $this->addEvent('onPrivmsg');
        $this->addEvent('onNick');
        $this->addEvent('onQuit');
    }

    /**
     * @param array $data
     * @return bool
     */
    public function onLoad($data)
    {
        $returnValue = parent::onLoad($data);
        $this->irc->registerAuth($this);
        return $returnValue;
    }

    /**
     * @param string $nick
     * @param string $host
     * @return bool
     */
    public function isAdmin($nick, $host)
    {
        if (isset($this->auth[$nick]) === true) {
            if (isset($this->auth[$nick]['host']) === true && $this->auth[$nick]['host'] == $host) {
                if (isset($this->auth[$nick]['level']) === true && $this->auth[$nick]['level'] >= self::AUTH_ADMIN) {
                    return true;
                }
            }
        }
        return false;
    }

    /**
     * @param string $nick
     * @param string $host
     * @return bool
     */
    public function isMember($nick, $host)
    {
        if (isset($this->auth[$nick]) === true) {
            if (isset($this->auth[$nick]['host']) === true && $this->auth[$nick]['host'] == $host) {
                if (isset($this->auth[$nick]['level']) === true && $this->auth[$nick]['level'] >= self::AUTH_MEMBER) {
                    return true;
                }
            }
        }
        return false;
    }

    /**
     * @param array $data
     * @return mixed|void
     */
    public function onPrivmsg($data)
    {
        $splitText = explode(' ', $data['text']);
        $command = array_shift($splitText);
        if ($command == '!auth') {
            return $this->getActions()->whois($data['nick']);
        }
        if ($command == '!debug') {
            return print_r($this, true);
        }
    }

    /**
     * @param array $data
     */
    public function onNick($data)
    {
        if (array_key_exists($data['nick'], $this->auth) === true) {
            $this->auth[$data['text']] = $this->auth[$data['nick']];
        }
        unset($this->auth[$data['nick']]);
    }

    /**
     * @param array $data
     */
    public function onQuit($data)
    {
        unset($this->auth[$data['nick']]);
    }

    /**
     * @param array $data
     */
    public function on311($data)
    {
        $this->auth[$data['nick']]['host'] = $data['host'];
    }

    /**
     * @param array $data
     */
    public function on330($data)
    {
        $authLevel = $this->getAuthLevel($data['auth']);
        if ($authLevel == 'admin') {
            $this->auth[$data['nick']]['level'] = self::AUTH_ADMIN;
        } elseif ($authLevel == 'user') {
            $this->auth[$data['nick']]['level'] = self::AUTH_MEMBER;
        }
    }
}
