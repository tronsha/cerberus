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

namespace Cerberus\Plugins;

use Cerberus\Plugin;

/**
 * Class PluginTest
 * @package Cerberus\Plugins
 * @author Stefan Hüsges
 *
 * This is a helper plugin to test functions with PHPUnit
 *
 */
class PluginTest extends Plugin
{
    /**
     *
     */
    protected function init()
    {
        $this->addEvent('onPrivmsg');
        $this->addEvent('onNotice');
        $this->addEvent('onJoin');
        $this->addEvent('onPart');
        $this->addEvent('onQuit');
        $this->addEvent('onTopic');
        $this->addEvent('onKick');
        $this->addEvent('onInvite');
        $this->addEvent('onNick');
        $this->addEvent('onMode');
        $this->addEvent('on401');
        $this->addEvent('on403');
        $this->addEvent('on404');
    }

    /**
     * @param array $data
     * @return bool
     */
    public function onLoad($data)
    {
        $returnValue = parent::onLoad($data);
        if ($data !== null) {
        }
        return $returnValue;
    }

    /**
     * @param array $data
     */
    protected function doEcho($data)
    {
        ksort($data);
        echo serialize($data);
    }

    /**
     * @param array $data
     */
    public function onPrivmsg($data)
    {
        $this->doEcho($data);
    }

    /**
     * @param array $data
     */
    public function onNotice($data)
    {
        $this->doEcho($data);
    }

    /**
     * @param array $data
     */
    public function onJoin($data)
    {
        $this->doEcho($data);
    }

    /**
     * @param array $data
     */
    public function onPart($data)
    {
        $this->doEcho($data);
    }

    /**
     * @param array $data
     */
    public function onQuit($data)
    {
        $this->doEcho($data);
    }

    /**
     * @param array $data
     */
    public function onTopic($data)
    {
        $this->doEcho($data);
    }

    /**
     * @param array $data
     */
    public function onKick($data)
    {
        $this->doEcho($data);
    }

    /**
     * @param array $data
     */
    public function onInvite($data)
    {
        $this->doEcho($data);
    }

    /**
     * @param array $data
     */
    public function onNick($data)
    {
        $this->doEcho($data);
    }

    /**
     * @param array $data
     */
    public function onMode($data)
    {
        $this->doEcho($data);
    }

    /**
     * @param array $data
     */
    public function on401($data)
    {
        $this->doEcho($data);
    }

    /**
     * @param array $data
     */
    public function on403($data)
    {
        $this->doEcho($data);
    }

    /**
     * @param array $data
     */
    public function on404($data)
    {
        $this->doEcho($data);
    }
}
