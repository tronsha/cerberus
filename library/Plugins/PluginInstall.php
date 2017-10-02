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
 * Class PluginInstall
 * @package Cerberus\Plugins
 * @author Stefan Hüsges
 */
class PluginInstall extends Plugin
{
    protected function init()
    {
        $this->addEvent('onPrivmsg');
    }

    /**
     * @param array $data
     * @return mixed|void
     */
    public function onPrivmsg($data)
    {
        $splitText = explode(' ', $data['text']);
        $command = array_shift($splitText);
        $plugin = array_shift($splitText);
        if ('!install' === $command && false === empty($plugin)) {
        }
    }

    /**
     * @param string $url
     * @return string
     */
    public function download($url)
    {
        return file_get_contents($url);
    }

    /**
     * @param string $file
     * @return bool
     */
    public function createFile($file)
    {
        if (false === is_writable(__DIR__)) {
            return false;
        }
    }
}
