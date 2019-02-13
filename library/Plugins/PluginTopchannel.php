<?php

/*
 * Cerberus IRCBot
 * Copyright (C) 2008 - 2019 Stefan Hüsges
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
 * Class PluginTopchannel
 * @package Cerberus\Plugins
 * @author Stefan Hüsges
 */
class PluginTopchannel extends Plugin
{
    protected $topchannel = [];

    /**
     *
     */
    protected function init()
    {
        $this->addEvent('on322');
        $this->addEvent('on323');
    }

    /**
     * @param array $data
     */
    public function on322($data)
    {
        $this->topchannel[$data['channel']] = $data['usercount'];
    }

    /**
     * @param array $data
     */
    public function on323($data)
    {
        unset($data);
        $count = 10;
        arsort($this->topchannel);
        foreach ($this->topchannel as $channel => $userCount) {
            $this->getActions()->join($channel);
            if ($count > 0) {
                break;
            }
            $count--;
        }
    }
}
