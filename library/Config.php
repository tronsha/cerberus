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
 * Class Config
 * @package Cerberus
 * @author Stefan Hüsges
 * @link http://www.mpcx.net/cerberus/ Project Homepage
 * @link https://github.com/tronsha/Cerberus Project on GitHub
 * @license http://www.gnu.org/licenses/gpl-3.0 GNU General Public License
 */

class Config
{
    protected $autorejoin = false;
    protected $channel = null;
    protected $ctcp = false;
    protected $dailylogfile = true;
    protected $dbms = ['mysql' => 'MySQL', 'pg' => 'PostgreSQL', 'sqlite' => 'SQLite'];
    protected $frontend = ['url' => null, 'password' => null];
    protected $info = ['name' => 'Cerberus', 'homepage' => ''];
    protected $language;
    protected $logfiledirectory = '/tmp/';
    protected $logfile = ['error' => true, 'socket' => false, 'sql' => false];
    protected $plugins = ['autoload' => []];
    protected $version = ['bot' => null, 'os' => null, 'php' => null, 'sql' => null];

    public function __construct($config)
    {
        $this->logfiledirectory = Cerberus::getPath() . '/log/';
        if (true === is_array($config)) {
            if (false === empty($config['info']['version'])) {
                $this->setVersion('bot', $config['info']['version']);
            }
            if (false === empty($config['info']['name'])) {
                $this->setName($config['info']['name']);
            }
            if (false === empty($config['info']['homepage'])) {
                $this->setHomepage($config['info']['homepage']);
            }
            if (false === empty($config['bot']['channel'])) {
                $this->setChannel('#' . $config['bot']['channel']);
            }
            if (true === isset($config['bot']['autorejoin'])) {
                $this->setAutorejoin(1 === intval($config['bot']['autorejoin']) ? true : false);
            }
            if (true === isset($config['bot']['ctcp'])) {
                $this->setCtcp(1 === intval($config['bot']['ctcp']) ? true : false);
            }
            if (false === empty($config['log']['directory'])) {
                $this->setLogfiledirectory($config['log']['directory']);
            }
            if (true === isset($config['log']['error'])) {
                $this->setLogfile('error', 1 === intval($config['log']['error']) ? true : false);
            }
            if (true === isset($config['log']['socket'])) {
                $this->setLogfile('socket', 1 === intval($config['log']['socket']) ? true : false);
            }
            if (true === isset($config['log']['sql'])) {
                $this->setLogfile('sql', 1 === intval($config['log']['sql']) ? true : false);
            }
            if (true === isset($config['log']['dailylogfile'])) {
                $this->setDailylogfile(1 === intval($config['log']['dailylogfile']) ? true : false);
            }
            if (true === isset($config['plugins']['autoload'])) {
                $this->setPluginsAutoload($config['plugins']['autoload']);
            }
            if (true === isset($config['frontend']['url'])) {
                $this->setFrontendUrl($config['frontend']['url']);
            }
            if (true === isset($config['frontend']['password'])) {
                $this->setFrontendPassword($config['frontend']['password']);
            }
            if (true === isset($config['bot']['lang'])) {
                $this->setLanguage($config['bot']['lang']);
            }
        }
    }

    public function setVersion($var, $version)
    {
        $this->version[$var] = $version;
    }

    public function getVersion($var = null)
    {
        return null === $var ? $this->version : $this->version[$var];
    }

    public function setName($name)
    {
        $this->info['name'] = $name;
    }

    public function getName()
    {
        return $this->info['name'];
    }

    public function setHomepage($homepage)
    {
        $this->info['homepage'] = $homepage;
    }

    public function getHomepage()
    {
        return $this->info['homepage'];
    }

    public function setChannel($channel)
    {
        $this->channel = $channel;
    }

    public function getChannel()
    {
        return $this->channel;
    }

    public function setAutorejoin($autorejoin)
    {
        $this->autorejoin = boolval($autorejoin);
    }

    public function getAutorejoin()
    {
        return $this->autorejoin;
    }

    public function setCtcp($ctcp)
    {
        $this->ctcp = boolval($ctcp);
    }

    public function getCtcp()
    {
        return $this->ctcp;
    }

    public function setFrontendUrl($url)
    {
        $this->frontend['url'] = $url;
    }

    public function getFrontendUrl()
    {
        return $this->frontend['url'];
    }

    public function setFrontendPassword($password)
    {
        $this->frontend['password'] = $password;
    }

    public function getFrontendPassword()
    {
        return $this->frontend['password'];
    }

    public function setPluginsAutoload($data)
    {
        if (true === is_array($data)) {
            $this->plugins['autoload'] = $data;
        } else {
            $this->plugins['autoload'] = explode(',', $data);
        }
    }

    public function getPluginsAutoload()
    {
        return $this->plugins['autoload'];
    }

    public function setLanguage($language)
    {
        $this->language = $language;
    }

    public function getLanguage()
    {
        return $this->language;
    }

    public function setLogfile($var, $value)
    {
        $this->logfile[$var] = boolval($value);
    }

    public function getLogfile($var)
    {
        return null === $var ? $this->logfile : $this->logfile[$var];
    }

    public function setLogfiledirectory($directory)
    {
        if (true === is_dir($directory)) {
            $this->logfiledirectory = realpath($directory);
        }
    }

    public function getLogfiledirectory()
    {
        return $this->logfiledirectory;
    }

    public function setDailylogfile($value)
    {
        $this->dailylogfile = boolval($value);
    }

    public function getDailylogfile()
    {
        return $this->dailylogfile;
    }

    public function getDbms($dbms = null)
    {
        return null === $dbms ? $this->dbms : $this->dbms[$dbms];
    }
}
