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

namespace Cerberus;

/**
 * Class Config
 * @package Cerberus
 * @author Stefan Hüsges
 * @link http://www.mpcx.net/cerberus/ Project Homepage
 * @link https://github.com/tronsha/Cerberus Project on GitHub
 * @license http://www.gnu.org/licenses/gpl-3.0 GNU General Public License
 */

class Config
{

    /**
     * Config constructor.
     * @param $config
     */
    public function __construct($config)
    {
        $this->info = ['name' => 'Cerberus'];
        $this->dbms = ['mysql' => 'MySQL', 'pg' => 'PostgreSQL', 'sqlite' => 'SQLite'];
        $this->autorejoin = false;
        $this->ctcp = false;
        $this->logfiledirectory = Cerberus::getPath() . '/log/';
        $this->logfile['error'] = true;
        $this->logfile['socket'] = false;
        $this->logfile['sql'] = false;
        $this->dailylogfile = true;
        if (is_array($config)) {
            if (!empty($config['info']['name'])) {
                $this->info['name'] = $config['info']['name'];
            }
            if (!empty($config['info']['homepage'])) {
                $this->info['homepage'] = $config['info']['homepage'];
            }
            if (!empty($config['bot']['channel'])) {
                $this->channel = '#' . $config['bot']['channel'];
            }
            if (isset($config['bot']['autorejoin'])) {
                $this->autorejoin = $config['bot']['autorejoin'] == 1 ? true : false;
            }
            if (isset($config['bot']['ctcp'])) {
                $this->ctcp = $config['bot']['ctcp'] == 1 ? true : false;
            }
            if (!empty($config['log']['directory']) && is_dir($config['log']['directory'])) {
                $this->logfiledirectory = $config['log']['directory'];
            }
            if (isset($config['log']['error'])) {
                $this->logfile['error'] = $config['log']['error'] == 1 ? true : false;
            }
            if (isset($config['log']['socket'])) {
                $this->logfile['socket'] = $config['log']['socket'] == 1 ? true : false;
            }
            if (isset($config['log']['sql'])) {
                $this->logfile['sql'] = $config['log']['sql'] == 1 ? true : false;
            }
            if (isset($config['log']['dailylogfile'])) {
                $this->dailylogfile = $config['log']['dailylogfile'] == 1 ? true : false;
            }
            if (isset($config['plugins']['autoload'])) {
                $this->plugins['autoload'] = explode(',', $config['plugins']['autoload']);
            }
            if (isset($config['frontend']['url'])) {
                $this->frontend['url'] = $config['frontend']['url'];
            }
            if (isset($config['frontend']['password'])) {
                $this->frontend['password'] = $config['frontend']['password'];
            }
            if (isset($config['bot']['lang'])) {
                $this->language = $config['bot']['lang'];
            }
        }
    }

    public function getLanguage()
    {
        return isset($this->language) ? $this->language : 'en';
    }
}
