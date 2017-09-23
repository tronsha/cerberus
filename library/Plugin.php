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

use Cerberus\Plugins\PluginAuth;
use Exception;

abstract class Plugin extends Cerberus
{
    /**
     * @var Irc
     */
    private $irc;

    /**
     * @param Irc $irc
     */
    public function __construct(Irc $irc)
    {
        $this->irc = $irc;
        $this->init();
    }

    /**
     *
     */
    public function __destruct()
    {
        $this->shutdown();
    }

    /**
     * abstract method for consructor logic
     */
    abstract protected function init();

    /**
     * destructor logic
     */
    protected function shutdown()
    {
    }

    /**
     * @param array $data
     * @param bool $hasChild
     * @return bool
     */
    public function onLoad($data, $hasChild = false)
    {
        $class = get_called_class();
        preg_match('/(?:^|\\\\)([^_\\\\]+)(?:_|$)/si', $class, $match);
        $className = $match[1];
        if (true === isset($data)) {
            $this->getActions()->notice($data['nick'], 'Load: ' . $className);
        }
        if (true === method_exists($class, 'install')) {
            $class::install($this->getDb());
        }
        $this->irc->setTranslations($this->translations());
        return true;
    }

    /**
     * @param PluginAuth $object
     */
    protected function registerAuth(PluginAuth $object)
    {
        $this->irc->registerAuth($object);
    }

    /**
     * @return Config|null
     */
    protected function getConfig()
    {
        return $this->irc->getConfig();
    }

    /**
     * @return Db|null
     */
    protected function getDb()
    {
        return $this->irc->getDb();
    }

    /**
     * @return Action|null
     */
    protected function getActions()
    {
        return $this->irc->getActions();
    }

    /**
     * @return Event|null
     */
    protected function getEvents()
    {
        return $this->irc->getEvents();
    }

    /**
     * @return string
     */
    protected function getNetwork()
    {
        return $this->irc->getNetwork();
    }

    /**
     * @return array
     */
    protected function translations()
    {
        return [];
    }

    /**
     * @param string $text
     * @param mixed $lang
     * @return string
     */
    protected function __($text, $lang = null)
    {
        return $this->irc->__($text, $lang);
    }

    /**
     * @param string $event
     * @param string|null $method
     * @param int $priority
     */
    protected function addEvent($event, $method = null, $priority = 5)
    {
        try {
            $method = (null === $method ? $event : $method);
            if (false === in_array($method, get_class_methods($this), true)) {
                throw new Exception('The method ' . $method . ' not exists in the class.');
            }
            $this->irc->addPluginEvent($event, $this, $method, $priority);
        } catch (Exception $e) {
            $this->error($e->getMessage());
        }
    }

    /**
     * @param string $event
     */
    protected function removeEvent($event)
    {
        $this->irc->removePluginEvent($event, $this);
    }

    /**
     * @param string $cronString
     * @param string $method
     */
    protected function addCron($cronString, $method)
    {
        $this->irc->addCron($cronString, $this, $method);
    }

    /**
     * @param int $id
     */
    protected function removeCron($id)
    {
        $this->irc->removeCron($id);
    }

    /**
     * @param string $nick
     * @param string $host
     * @return mixed
     */
    protected function isAdmin($nick, $host)
    {
        return $this->irc->isAdmin($nick, $host);
    }

    /**
     * @param string $auth
     * @return string
     */
    protected function getAuthLevel($auth)
    {
        return $this->irc->getAuthLevel($auth);
    }

    /**
     * @param string $channel
     * @param string|null $user
     * @return bool|null
     */
    protected function inChannel($channel, $user = null)
    {
        return $this->irc->inChannel($channel, $user);
    }

    /**
     * @param string $plugin
     * @param array|null $data
     */
    protected function loadPlugin($plugin, $data = null)
    {
        $this->irc->loadPlugin($plugin, $data);
    }
}
