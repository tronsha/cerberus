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

use DateTime;
use Doctrine\DBAL\DriverManager;
use Doctrine\DBAL\Version;
use Exception;

/**
 * Class Db
 * @package Cerberus
 * @author Stefan Hüsges
 * @link http://www.mpcx.net/projekte/cerberus/ Project Homepage
 * @link https://github.com/tronsha/cerberus Project on GitHub
 * @link http://www.doctrine-project.org/projects/dbal.html Database Abstraction Layer
 * @license http://www.gnu.org/licenses/gpl-3.0 GNU General Public License
 */
class Db
{
    protected $config = [];
    protected $irc = null;
    protected $conn = null;
    protected $botId = null;
    protected $classes = [];

    /**
     * @param array $config
     * @param Irc $irc
     */
    public function __construct($config, Irc $irc = null)
    {
        $this->irc = $irc;
        $this->config = $config;
    }

    /**
     * @return \Doctrine\DBAL\Connection
     */
    public function getConnection()
    {
        return $this->conn;
    }

    /**
     * @return int
     */
    public function getBotId()
    {
        return $this->botId;
    }

    /**
     * @param int $id
     */
    public function setBotId($id)
    {
        $this->botId = $id;
    }

    /**
     * @param string $key
     * @return mixed
     */
    public function getConfig($key)
    {
        return $this->config[$key];
    }

    /**
     * @param string $key
     * @param mixed $value
     */
    public function setConfig($key, $value)
    {
        $this->config[$key] = $value;
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
        $className = '\Cerberus\Db\Db' . ucfirst($name);
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
        $className = '\Cerberus\Db\Db' . ucfirst($name);
        $this->classes[$key] = new $className($this);
    }

    /**
     * @throws \Doctrine\DBAL\DBALException
     * @return \Doctrine\DBAL\Connection
     */
    public function connect()
    {
        return $this->conn = DriverManager::getConnection($this->config);
    }

    /**
     * @return mixed
     */
    public function close()
    {
        return $this->getConnection()->close();
    }

    /**
     * @param string $error
     * @return false
     */
    public function error($error)
    {
        if ($this->irc !== null) {
            $this->irc->sqlError($error);
        } else {
            echo $error;
        }
        return false;
    }

    /**
     * the ping method is new in doctrine dbal at version 2.5.*
     * @link http://www.doctrine-project.org/2014/01/01/dbal-242-252beta1.html
     * @link https://packagist.org/packages/doctrine/dbal
     * @return mixed
     */
    public function ping()
    {
        return Version::compare('2.5') >= 0 ? $this->getConnection()->ping() : true;
    }

    /**
     * @param string|null $dbName
     * @throws Exception
     * @return int
     */
    public function lastInsertId($dbName = null)
    {
        $lastInsertId = $this->getConnection()->lastInsertId();
        if ($lastInsertId === false && $dbName !== null) {
            $qb = $this->getConnection()->createQueryBuilder();
            $stmt = $qb
                ->select('MAX(id) AS id')
                ->from($dbName)
                ->execute();
            $row = $stmt->fetch();
            $lastInsertId = $row['id'];
        }
        return intval($lastInsertId);
    }

    /**
     * @param string $name
     * @param array $arguments
     * @return mixed
     */
    public function __call($name, $arguments)
    {
        try {
            return call_user_func_array([$this->getClass($name), $name], $arguments);
        } catch (Exception $e) {
            $this->error($e->getMessage());
        }
    }

    /**
     * @return string
     */
    public function getServerName()
    {
        try {
            $qb = $this->getConnection()->createQueryBuilder();
            $stmt = $qb
                ->select('network')
                ->from('network', 'n')
                ->innerJoin('n', 'server', 's', 's.network_id = n.id')
                ->innerJoin('s', 'bot', 'b', 'b.server_id = s.id')
                ->where('b.id = ?')
                ->setParameter(0, $this->getBotId())
                ->execute();
            $row = $stmt->fetch();
            return $row['network'];
        } catch (Exception $e) {
            $this->error($e->getMessage());
        }
    }

    /**
     * @link https://freenode.net/irc_servers.shtml
     * @link https://www.quakenet.org/servers
     * @param array $server
     * @param int $i
     * @return array
     */
    public function getServerData($server, $i = 0)
    {
        try {
            $network = strtolower($server['network']);
            $i = (string)$i;
            $qb = $this->getConnection()->createQueryBuilder();
            $stmt = $qb
                ->select('s.id', 's.server AS host', 's.port')
                ->from('server', 's')
                ->innerJoin('s', 'network', 'n', 's.network_id = n.id')
                ->where('n.network = ?')
                ->orderBy('s.id', 'ASC')
                ->addOrderBy('s.port', 'ASC')
                ->setFirstResult($i)
                ->setMaxResults(1)
                ->setParameter(0, $network)
                ->execute();
            $row = $stmt->fetch();
            try {
                $row['ip'] = gethostbyname($row['host']);
            } catch (Exception $e) {
                $this->irc->error($e->getMessage());
            }
            $qb = $this->getConnection()->createQueryBuilder();
            $qb ->update('bot')
                ->set('server_id', '?')
                ->where('id = ?')
                ->setParameter(0, $row['id'])
                ->setParameter(1, $this->getBotId())
                ->execute();
            return array_merge($server, $row);
        } catch (Exception $e) {
            $this->error($e->getMessage());
        }
    }

    /**
     * @return string
     */
    public function getDbVersion()
    {
        try {
            $sql = 'SELECT VERSION() AS version';
            $stmt = $this->getConnection()->query($sql);
            $row = $stmt->fetch();
            return $row['version'];
        } catch (Exception $e) {
            $this->error($e->getMessage());
        }
    }

    /**
     * @param string $network
     * @return array
     */
    public function getPreform($network)
    {
        try {
            $qb = $this->getConnection()->createQueryBuilder();
            $stmt = $qb
                ->select('text, priority')
                ->from('preform')
                ->where('network = ?')
                ->orderBy('priority', 'DESC')
                ->setParameter(0, $network)
                ->execute();
            $rows = $stmt->fetchAll();
            return $rows;
        } catch (Exception $e) {
            $this->error($e->getMessage());
        }
    }

    /**
     *
     */
    public function setPing()
    {
        try {
            $now = (new DateTime())->format('Y-m-d H:i:s');
            $qb = $this->getConnection()->createQueryBuilder();
            $qb ->update('bot')
                ->set('ping', '?')
                ->where('id = ?')
                ->setParameter(0, $now)
                ->setParameter(1, $this->getBotId())
                ->execute();
        } catch (Exception $e) {
            $this->error($e->getMessage());
        }
    }
}
