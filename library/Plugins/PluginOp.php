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
 * Class PluginOp
 * @package Cerberus\Plugins
 * @author Stefan Hüsges
 */
class PluginOp extends Plugin
{
    /**
     *
     */
    protected function init()
    {
        $this->addEvent('onPrivmsg');
        $this->addEvent('onJoin');
    }

    /**
     * @param array $data
     * @return bool
     */
    public function onLoad($data)
    {
        $returnValue = parent::onLoad($data);
        if (null !== $data) {
            $this->getActions()->notice($data['nick'], 'New Command: !op');
            $this->getActions()->notice($data['nick'], 'New Command: !deop');
        }
        return $returnValue;
    }

    /**
     * @param array $data
     * @return bool
     */
    public function onPrivmsg($data)
    {
        if (false === $this->isAdmin($data['nick'], $data['host'])) {
            return false;
        }
        $splitText = explode(' ', $data['text']);
        $command = array_shift($splitText);
        if ('!op' === $command) {
            $this->getActions()->op($data['channel'], $data['nick']);
            return true;
        }
        if ('!deop' === $command) {
            $this->getActions()->deop($data['channel'], $data['nick']);
            return true;
        }
    }

    /**
     * @param array $data
     * @return bool
     */
    public function onJoin($data)
    {
        $channel = $this->getConfig()->getChannel();
        if ($data['channel'] === $channel) {
            $this->getActions()->op($channel);
            return true;
        }
        return false;
    }
}
