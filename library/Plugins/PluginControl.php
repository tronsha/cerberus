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
 * Class PluginControl
 * @package Cerberus\Plugins
 * @author Stefan Hüsges
 */
class PluginControl extends Plugin
{
    /**
     *
     */
    protected function init()
    {
        $this->addEvent('onTick');
    }

    /**
     * @return bool
     */
    public function onTick()
    {
        $result = $this->getDb()->getControl();
        $this->getDb()->removeControl($result['id']);
        if (empty($result) === false) {
            $data = json_decode($result['data'], true);
            $this->getEvents()->onControl($result['command'], $data);
            switch ($result['command']) {
                case 'load':
                    $this->load($data['param']);
                    break;
                case 'list':
                    $this->channellist();
                    break;
                case 'whois':
                    $this->whois($data['param']);
                    break;
            }
        }
    }

    /**
     * @param string $param
     */
    protected function load($param)
    {
        $plugins = preg_split('/[, ]+/', $param);
        foreach ($plugins as $plugin) {
            $this->loadPlugin($plugin);
        }
    }

    /**
     *
     */
    protected function channellist()
    {
    }

    /**
     * @param string $param
     */
    protected function whois($param)
    {
        $this->getActions()->whois($param);
    }
}
