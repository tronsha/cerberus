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

namespace Cerberus\Plugins;

use Cerberus\Plugin;

class PluginTest extends Plugin
{
    protected function init()
    {
        $this->irc->addEvent('onPrivmsg', $this);
        $this->irc->addEvent('onNotice', $this);
        $this->irc->addEvent('onJoin', $this);
        $this->irc->addEvent('onPart', $this);
        $this->irc->addEvent('onQuit', $this);
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
}
