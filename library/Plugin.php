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

namespace Cerberus;

abstract class Plugin extends Cerberus
{
    /**
     * @var Irc
     */
    protected $irc;

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
    protected function shutdown() {}

    /**
     * @param array $data
     * @return bool
     */
    public function onLoad($data)
    {
        if (isset($data) === true) {
            $this->irc->getActions()->notice($data['nick'], 'Load: ' . get_called_class());
        }
        $this->irc->setTranslations($this->translations());
        return true;
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
     * @param int $priority
     */
    protected function addEvent($event, $priority = 5)
    {
        $this->irc->addEvent($event, $this, $priority);
    }

    /**
     * @param string $event
     */
    protected function removeEvent($event)
    {
        $this->irc->removeEvent($event, $this);
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
}
