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
use Exception;

/**
 * Class PluginInit
 * @package Cerberus\Plugins
 * @author Stefan Hüsges
 */
class PluginInit extends Plugin
{
    /**
     *
     */
    protected function init()
    {
        $this->addEvent('onConnect');
    }

    /**
     * @param array $data
     * @return bool
     */
    public function onLoad($data)
    {
        $returnValue = parent::onLoad($data);
        if (null !== $data) {
        }
        return $returnValue;
    }

    /**
     * @param array $data
     */
    public function onConnect($data)
    {
        if (null !== $this->getConfig()->getFrontendUrl()) {
            $url = trim($this->getConfig()->getFrontendUrl(), " \t\n\r\0\x0B/") . '/sethost.php';
            if (null !== $this->getConfig()->getFrontendPassword()) {
                $url .= '?pw=' . md5($this->getConfig()->getFrontendPassword());
            }
            try {
                file_get_contents($url);
            } catch (Exception $e) {
                $this->error($e->getMessage());
            }
        }
    }
}
