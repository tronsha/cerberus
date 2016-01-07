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

use Cerberus\Cerberus;
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
        $this->irc->addEvent('onTick', $this);
    }

    /**
     * @return bool
     */
    public function onTick()
    {
        $result = $this->irc->getDb()->getControl();
        $this->irc->getDb()->removeControl($result['id']);
        if (empty($result) === false) {
            switch ($result['command']) {
                case 'load':
                    $this->load($result['data']);
                    break;
            }
        }
    }

    /**
     * @param string $data
     */
    protected function load($data)
    {
        $plugins = preg_split('/[, ]+/', $data);
        foreach ($plugins as $plugin) {
            $this->irc->loadPlugin($plugin);
        }
    }
}
