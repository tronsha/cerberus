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

namespace Cerberus;

/**
 * Class Helper
 * @package Cerberus
 * @author Stefan Hüsges
 * @link http://www.mpcx.net/projekte/cerberus/ Project Homepage
 * @link https://github.com/tronsha/cerberus Project on GitHub
 * @license http://www.gnu.org/licenses/gpl-3.0 GNU General Public License
 */
class Helper
{
    protected $irc = null;
    protected $namespace = null;
    protected $classes = [];

    /**
     * Event constructor.
     * @param Irc $irc
     */
    public function __construct(Irc $irc = null)
    {
        $this->irc = $irc;
    }

    /**
     * @param string $namespace
     */
    protected function setNamespace($namespace)
    {
        $this->namespace = $namespace;
    }

    /**
     * @return mixed
     */
    protected function getNamespace()
    {
        return $this->namespace;
    }

    /**
     * @param string $name
     * @param array $arguments
     * @return mixed
     */
    public function __call($name, $arguments)
    {
        return call_user_func_array([$this->getClass($name), $name], $arguments);
    }

    /**
     * @param string $name
     * @return false|object
     */
    public function getClass($name)
    {
        $key = strtolower($name);
        if (array_key_exists($key, $this->classes) === false) {
            $this->loadClass($name);
        }
        $class = $this->classes[$key];
        $className = $this->getNamespace() . ucfirst($name);
        if (is_a($class, $className) === false) {
            return false;
        }
        return $class;
    }

    /**
     * @param string $name
     */
    protected function loadClass($name)
    {
        $key = strtolower($name);
        $className = $this->getNamespace() . ucfirst($name);
        $this->classes[$key] = new $className($this);
    }
}
