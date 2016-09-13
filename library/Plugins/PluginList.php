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
 * Class PluginList
 * @package Cerberus\Plugins
 * @author Stefan Hüsges
 */
class PluginList extends Plugin
{
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
     * @return bool
     */
    public function onLoad($data)
    {
        $returnValue = parent::onLoad($data);
        return $returnValue;
    }

    /**
     * @param array $data
     * @return bool
     */
    public function on322($data)
    {
        $network = $this->getNetwork();
        $db = $this->getDb();
        $db->addChannelToChannellist($network, $data['channel'], $data['usercount'], $data['topic']);
    }

    /**
     * @param array $data
     * @return bool
     */
    public function on323($data)
    {
    }
}
