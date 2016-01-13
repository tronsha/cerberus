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
 * Class PluginJoin
 * @package Cerberus\Plugins
 * @author Stefan Hüsges
 */
class PluginJoin extends Plugin
{
    /**
     *
     */
    protected function init()
    {
        $this->addEvent('onPrivmsg');
    }

    /**
     * @param array $data
     * @return bool
     */
    public function onLoad($data)
    {
        $returnValue = parent::onLoad($data);
        if ($data !== null) {
            $this->irc->getActions()->notice($data['nick'], 'New Command: !join [#channel]');
        }
        return $returnValue;
    }

    /**
     * @param array $data
     * @return bool
     */
    public function onPrivmsg($data)
    {
        if ($this->irc->isAdmin($data['nick'], $data['host']) === false) {
            return false;
        }
        $splitText = explode(' ', $data['text']);
        $command = array_shift($splitText);
        if ($command == '!join') {
            while ($channel = array_shift($splitText)) {
                $this->irc->getActions()->join($channel);
            }
            return true;
        }
    }
}
