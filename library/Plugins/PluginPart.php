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

namespace Cerberus\Plugins;

use Cerberus\Plugin;

/**
 * Class PluginPart
 * @package Cerberus\Plugins
 * @author Stefan Hüsges
 */
class PluginPart extends Plugin
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
        if (null !== $data) {
            $this->getActions()->notice($data['nick'], 'New Command: !part [#channel]');
        }
        return $returnValue;
    }

    /**
     * @param array $data
     * @return bool|void
     */
    public function onPrivmsg($data)
    {
        if (false === $this->isAdmin($data['nick'], $data['host'])) {
            return false;
        }
        $splitText = explode(' ', $data['text']);
        $command = array_shift($splitText);
        if ('!part' === $command) {
            $channel = trim(array_shift($splitText));
            if (true === empty($channel)) {
                $channel = $data['channel'];
                $this->getActions()->part($channel);
            } else {
                do {
                    $this->getActions()->part($channel);
                } while ($channel = array_shift($splitText));
            }
            return true;
        }
    }
}
