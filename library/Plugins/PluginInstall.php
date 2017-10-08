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
        if (false === $this->isAdmin($data['nick'], $data['host'])) {
            return false;
        }
        $splitText = explode(' ', $data['text']);
        $command = array_shift($splitText);
        $plugin = array_shift($splitText);
        if ('!install' === $command && false === empty($plugin)) {
            $file = $this->download($plugin);
            $pluginName = $this->createFile($file);
            $this->runInstall($pluginName);
        } elseif ('!uninstall' === $command && false === empty($plugin)) {
        }
    }

    /**
     * @param string $url
     * @return mixed
     */
    public function download($url)
    {
        if (false === filter_var($url, FILTER_VALIDATE_URL)) {
            return false;
        }
        return @file_get_contents($url);
    }

    /**
     * @param string $file
     * @return mixed
     */
    public function createFile($file)
    {
        if (false === $file) {
            return false;
        }
        if (false === is_writable(__DIR__)) {
            return false;
        }
        if (1 !== preg_match('/class\s+(Plugin[A-Z][a-z]+)\s+extends\s+Plugin/is', $file, $matches)) {
            return false;
        }
        $pluginName = $matches[1];
        $fileName = __DIR__ . DIRECTORY_SEPARATOR . $pluginName . '.php';
        if (true === file_exists($fileName)) {
            return false;
        }
        file_put_contents($fileName, $file);
        return $pluginName;
    }
    
    /**
     * @param string $pluginName
     * @return bool
     */
    public function runInstall($pluginName)
    {
        if (false === $pluginName) {
            return false;
        }
        $class = 'Cerberus\\Plugins\\' . $pluginName;
        if (true === method_exists($class, 'install')) {
            $class::install($this->getDb());
            $className = $this->getClassName($class);
            $this->getDb()->addPlugin($className);
        }
        return true;
    }
}
