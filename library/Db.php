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

use Doctrine\DBAL\DriverManager;
use Doctrine\DBAL\Version;
use DateTime;
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
    protected $config = array();
    protected $irc = null;
    protected $conn = null;
    protected $botId = null;

    /**
     * @param array $config
     * @param Irc $irc
     */
    public function __construct($config, Irc $irc = null)
    {
        $this->irc = $irc;
        $this->config = $config;
    }

    public function __destruct()
    {
    }

    public function connect()
    {
        return $this->conn = DriverManager::getConnection($this->config);
    }

    public function close()
    {
        return $this->conn->close();
    }


    public function error($error)
    {
        if($this->irc !== null) {
            $this->irc->sqlError($error);
        } else {
            echo $error;
        }
    }

    /**
     * the ping method is new in doctrine dbal at version 2.5.*
     * @link http://www.doctrine-project.org/2014/01/01/dbal-242-252beta1.html
     * @link https://packagist.org/packages/doctrine/dbal
     * @return mixed
     */
    public function ping()
    {
        if (Version::compare('2.5') >= 0) {
            return $this->conn->ping();
        } else {
            return true;
        }
    }

    public function createBot($pid, $nick)
    {
        try {
            $now = (new DateTime())->format('Y-m-d H:i:s');
            $qb = $this->conn->createQueryBuilder();
            $qb ->insert('bot')
                ->values(
                    array(
                        'pid' => '?',
                        'start' => '?',
                        'nick' => '?'
                    )
                )
                ->setParameter(0, $pid)
                ->setParameter(1, $now)
                ->setParameter(2, $nick)
                ->execute();
//            $sql = 'INSERT INTO `bot` SET `pid` = ' . $this->conn->quote($pid) . ', `start` = NOW(), `nick` = ' . $this->conn->quote($nick) . '';
//            $this->conn->query($sql);
            $this->botId = $this->conn->lastInsertId();
            if ($this->botId === false && $this->config['driver'] === 'pdo_pgsql') {
                $qb = $this->conn->createQueryBuilder();
                $stmt = $qb
                    ->select('MAX(id) AS id')
                    ->from('bot')
                    ->execute();
                $row = $stmt->fetch();
                $this->botId = $row['id'];
            }
        } catch (Exception $e) {
            $this->error($e->getMessage());
        }
    }

    public function shutdownBot($botId = null)
    {
        try {
            $now = (new DateTime())->format('Y-m-d H:i:s');
            $qb = $this->conn->createQueryBuilder();
            $qb ->update('bot')
                ->set('stop', '?')
                ->where('id = ?')
                ->setParameter(0, $now)
                ->setParameter(1, ($botId === null ? $this->botId : $botId))
                ->execute();
//            $sql = 'UPDATE `bot` SET `stop` = NOW() WHERE `id` = ' . ($botId === null ? $this->botId : $botId) . '';
//            $this->conn->query($sql);
            $this->close();
        } catch (Exception $e) {
            $this->error($e->getMessage());
        }
    }

    public function cleanupBot($botId = null)
    {
        try {
            $qb = $this->conn->createQueryBuilder();
            $qb ->delete('send')
                ->where('bot_id = ?')
                ->setParameter(0, ($botId === null ? $this->botId : $botId))
                ->execute();
            $qb = $this->conn->createQueryBuilder();
            $qb ->delete('channel')
                ->where('bot_id = ?')
                ->setParameter(0, ($botId === null ? $this->botId : $botId))
                ->execute();
            $qb = $this->conn->createQueryBuilder();
            $qb ->delete('channel_user')
                ->where('bot_id = ?')
                ->setParameter(0, ($botId === null ? $this->botId : $botId))
                ->execute();
//            $sql = 'DELETE FROM `send` WHERE `bot_id` = ' . ($botId === null ? $this->botId : $botId) . '';
//            $this->conn->query($sql);
//            $sql = 'DELETE FROM `channel` WHERE `bot_id` = ' . ($botId === null ? $this->botId : $botId) . '';
//            $this->conn->query($sql);
//            $sql = 'DELETE FROM `channel_user` WHERE `bot_id` = ' . ($botId === null ? $this->botId : $botId) . '';
//            $this->conn->query($sql);
        } catch (Exception $e) {
            $this->error($e->getMessage());
        }
    }

    public function getActiveBotList()
    {
        try {
            $qb = $this->conn->createQueryBuilder();
            $stmt = $qb
                ->select('*')
                ->from('bot')
                ->where('stop IS NULL')
                ->execute();
//            $sql = 'SELECT * FROM bot WHERE stop IS NULL';
//            $stmt = $this->conn->query($sql);
            $rows = $stmt->fetchAll();
            return $rows;
        } catch (Exception $e) {
            $this->error($e->getMessage());
        }
    }

    public function getServerCount($network)
    {
        try {
            $qb = $this->conn->createQueryBuilder();
            $stmt = $qb
                ->select('COUNT(*) AS number')
                ->from('server', 's')
                ->innerJoin('s', 'network', 'n', 's.network_id = n.id')
                ->where('n.network = ?')
                ->setParameter(0, $network)
                ->execute();
//            $sql = 'SELECT count(*) AS number '
//                . 'FROM `server` s, `network` n '
//                . 'WHERE n.`id` = s.`network_id` '
//                . 'AND n.`network` = ' . $this->conn->quote($network) . '';
//            $stmt = $this->conn->query($sql);
            $row = $stmt->fetch();
            return $row['number'];
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
            $qb = $this->conn->createQueryBuilder();
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
//            $sql = 'SELECT s.`id` , s.`server` AS host, s.`port` '
//                . 'FROM `server` s, `network` n '
//                . 'WHERE n.`id` = s.`network_id` '
//                . 'AND n.`network` = ' . $this->conn->quote($network) . ' '
//                . 'ORDER BY s.`id` , s.`port` '
//                . 'LIMIT ' . $i . ', 1';
//            $stmt = $this->conn->query($sql);
            $row = $stmt->fetch();
            $row['ip'] = @gethostbyname($row['host']);

            $qb = $this->conn->createQueryBuilder();
            $qb ->update('bot')
                ->set('server_id', '?')
                ->where('id = ?')
                ->setParameter(0, $row['id'])
                ->setParameter(1, $this->botId)
                ->execute();
//            $sql = 'UPDATE `bot` SET `server_id` = ' . $row['id'] . ' WHERE `id` = ' . $this->botId . '';
//            $this->conn->query($sql);
            return array_merge($server, $row);
        } catch (Exception $e) {
            $this->error($e->getMessage());
        }
    }

    public function getDbVersion()
    {
        try {
            $sql = 'SELECT VERSION() AS version';
            $stmt = $this->conn->query($sql);
            $row = $stmt->fetch();
            return $row['version'];
        } catch (Exception $e) {
            $this->error($e->getMessage());
        }
    }

    public function getPreform($network)
    {
        try {
            $qb = $this->conn->createQueryBuilder();
            $stmt = $qb
                ->select('text')
                ->from('preform')
                ->where('network = ?')
                ->orderBy('priority', 'DESC')
                ->setParameter(0, $network)
                ->execute();
//            $sql = 'SELECT `text` FROM `preform` WHERE `network` = ' . $this->conn->quote($network) . ' ORDER BY `priority` DESC';
//            $stmt = $this->conn->query($sql);
            $rows = $stmt->fetchAll();
            return $rows;
        } catch (Exception $e) {
            $this->error($e->getMessage());
        }
    }

    public function setWrite($text)
    {
        try {
            $qb = $this->conn->createQueryBuilder();
            $qb ->insert('send')
                ->values(
                    array(
                        'text' => '?',
                        'bot_id' => '?'
                    )
                )
                ->setParameter(0, $text)
                ->setParameter(1, $this->botId)
                ->execute();
//            $sql = 'INSERT INTO `send` SET `text` = ' . $this->conn->quote($text) . ', `bot_id` = ' . $this->botId . '';
//            $this->conn->query($sql);
        } catch (Exception $e) {
            $this->error($e->getMessage());
        }
    }

    public function getWrite()
    {
        try {
            $qb = $this->conn->createQueryBuilder();
            $stmt = $qb
                ->select('id', 'text')
                ->from('send')
                ->where('bot_id = ?')
                ->orderBy('id', 'ASC')
                ->setMaxResults(1)
                ->setParameter(0, $this->botId)
                ->execute();
//            $sql = 'SELECT `id`, `text` FROM `send` WHERE `bot_id` = ' . $this->botId . ' ORDER BY `id` LIMIT 0, 1';
//            $stmt = $this->conn->query($sql);
            return $stmt->fetch();
        } catch (Exception $e) {
            $this->error($e->getMessage());
        }
    }

    public function unsetWrite($id)
    {
        try {
            $qb = $this->conn->createQueryBuilder();
            $qb ->delete('send')
                ->where('id = ?')
                ->setParameter(0, $id)
                ->execute();
//            $sql = 'DELETE FROM `send` WHERE `id` = ' . $this->conn->quote($id) . '';
//            $this->conn->query($sql);
        } catch (Exception $e) {
            $this->error($e->getMessage());
        }
    }

    public function setLog($network, $all, $nick, $host, $command, $rest, $text, $direction)
    {
        try {
            $now = (new DateTime())->format('Y-m-d H:i:s');
            $qb = $this->conn->createQueryBuilder();
            $qb ->insert('log')
                ->values(
                    array(
                        'nick' => '?',
                        'host' => '?',
                        'command' => '?',
                        'rest' => '?',
                        'text' => '?',
                        'irc' => '?',
                        'network' => '?',
                        'bot_id' => '?',
                        'time' => '?',
                        'direction' => '?'
                    )
                )
                ->setParameter(0, $nick)
                ->setParameter(1, $host)
                ->setParameter(2, $command)
                ->setParameter(3, $rest)
                ->setParameter(4, $text)
                ->setParameter(5, $all)
                ->setParameter(6, $network)
                ->setParameter(7, $this->botId)
                ->setParameter(8, $now)
                ->setParameter(9, $direction)
                ->execute();
//            $sql = 'INSERT INTO log SET '
//                . '`nick`      = ' . $this->conn->quote($nick) . ', '
//                . '`host`      = ' . $this->conn->quote($host) . ', '
//                . '`command`   = ' . $this->conn->quote($command) . ', '
//                . '`rest`      = ' . $this->conn->quote($rest) . ', '
//                . '`text`      = ' . $this->conn->quote($text) . ', '
//                . '`all`       = ' . $this->conn->quote($all) . ', '
//                . '`network`   = ' . $this->conn->quote($network) . ', '
//                . '`bot_id`    = ' . $this->botId . ', '
//                . '`time`      = NOW(), '
//                . '`direction` = ' . $this->conn->quote($direction) . '';
//            $this->conn->query($sql);
        } catch (Exception $e) {
            $this->error($e->getMessage());
        }
    }

    public function setPing()
    {
        try {
            $now = (new DateTime())->format('Y-m-d H:i:s');
            $qb = $this->conn->createQueryBuilder();
            $qb ->update('bot')
                ->set('ping', '?')
                ->where('id = ?')
                ->setParameter(0, $now)
                ->setParameter(1, $this->botId)
                ->execute();
//            $sql = 'UPDATE `bot` SET `ping` = NOW() WHERE `id` = ' . $this->botId . '';
//            $this->conn->query($sql);
        } catch (Exception $e) {
            $this->error($e->getMessage());
        }
    }

    public function setBotNick($nick)
    {
        try {
            $qb = $this->conn->createQueryBuilder();
            $qb ->update('bot')
                ->set('nick', '?')
                ->where('id = ?')
                ->setParameter(0, $nick)
                ->setParameter(1, $this->botId)
                ->execute();
//            $sql = 'UPDATE `bot` SET `nick` = ' . $this->conn->quote($nick) . ' WHERE `id` = ' . $this->botId . '';
//            $this->conn->query($sql);
        } catch (Exception $e) {
            $this->error($e->getMessage());
        }
    }

    public function addChannel($channel)
    {
        try {
            $qb = $this->conn->createQueryBuilder();
            $qb ->insert('channel')
                ->values(
                    array(
                        'channel' => '?',
                        'bot_id' => '?'
                    )
                )
                ->setParameter(0, $channel)
                ->setParameter(1, $this->botId)
                ->execute();
//            $sql = 'INSERT INTO `channel` SET `channel` = ' . $this->conn->quote($channel) . ', `bot_id` = ' . $this->botId . '';
//            $this->conn->query($sql);
        } catch (Exception $e) {
            $this->error($e->getMessage());
        }
    }

    public function removeChannel($channel)
    {
        try {
            $qb = $this->conn->createQueryBuilder();
            $qb ->delete('channel')
                ->where('channel = ? AND bot_id = ?')
                ->setParameter(0, $channel)
                ->setParameter(1, $this->botId)
                ->execute();
//            $sql = 'DELETE FROM `channel` WHERE `channel` = ' . $this->conn->quote($channel) . ' AND `bot_id` = ' . $this->botId . '';
//            $this->conn->query($sql);
            $this->removeUserFromChannel($channel);
        } catch (Exception $e) {
            $this->error($e->getMessage());
        }
    }

    public function removeUser($user)
    {
        try {
            $this->removeUserFromChannel('%', $user);
        } catch (Exception $e) {
            $this->error($e->getMessage());
        }
    }

    public function addUserToChannel($channel, $user, $mode = '')
    {
        try {
            $qb = $this->conn->createQueryBuilder();
            $qb ->insert('channel_user')
                ->values(
                    array(
                        'username' => '?',
                        'mode' => '?',
                        'channel' => '?',
                        'bot_id' => '?'
                    )
                )
                ->setParameter(0, $user)
                ->setParameter(1, $mode)
                ->setParameter(2, $channel)
                ->setParameter(3, $this->botId)
                ->execute();
//            $sql = 'INSERT INTO `channel_user` SET `user` = ' . $this->conn->quote($user) . ', `mode` = ' . $this->conn->quote($mode) . ', `channel` = ' . $this->conn->quote($channel) . ', `bot_id` = ' . $this->botId . '';
//            $this->conn->query($sql);
        } catch (Exception $e) {
            $this->error($e->getMessage());
        }
    }

    public function removeUserFromChannel($channel, $user = '%')
    {
        try {
            $qb = $this->conn->createQueryBuilder();
            $qb ->delete('channel_user')
                ->where('username LIKE ? AND channel LIKE ? AND bot_id = ?')
                ->setParameter(0, $user)
                ->setParameter(1, $channel)
                ->setParameter(2, $this->botId)
                ->execute();
//            $sql = 'DELETE FROM `channel_user` WHERE `user` LIKE ' . $this->conn->quote($user) . ' AND `channel` LIKE ' . $this->conn->quote($channel) . ' AND `bot_id` = ' . $this->botId . '';
//            $this->conn->query($sql);
        } catch (Exception $e) {
            $this->error($e->getMessage());
        }
    }

    public function changeNick($old, $new)
    {
        try {
            $qb = $this->conn->createQueryBuilder();
            $qb ->update('channel_user')
                ->set('username', '?')
                ->where('bot_id = ? AND username = ?')
                ->setParameter(0, $new)
                ->setParameter(1, $this->botId)
                ->setParameter(2, $old)
                ->execute();
//            $sql = 'UPDATE `channel_user` SET `user` = ' . $this->conn->quote($new) . ' WHERE `bot_id` = ' . $this->botId . ' AND `user` = ' . $this->conn->quote($old) . '';
//            $this->conn->query($sql);
        } catch (Exception $e) {
            $this->error($e->getMessage());
        }
    }

    public function setChannelTopic($channel, $topic)
    {
        try {
            $qb = $this->conn->createQueryBuilder();
            $qb ->update('channel')
                ->set('topic', '?')
                ->where('bot_id = ? AND channel = ?')
                ->setParameter(0, $topic)
                ->setParameter(1, $this->botId)
                ->setParameter(2, $channel)
                ->execute();
//            $sql = 'UPDATE `channel` SET `topic` = ' . $this->conn->quote($topic) . ' WHERE `bot_id` = ' . $this->botId . ' AND `channel` = ' . $this->conn->quote($channel) . '';
//            $this->conn->query($sql);
        } catch (Exception $e) {
            $this->error($e->getMessage());
        }
    }

    public function getJoinedChannels()
    {
        try {
            $qb = $this->conn->createQueryBuilder();
            $stmt = $qb
                ->select('channel')
                ->from('channel')
                ->where('bot_id = ?')
                ->setParameter(0, $this->botId)
                ->execute();
//            $sql = 'SELECT `channel` FROM `channel` WHERE `bot_id` = ' . $this->botId . '';
//            $stmt = $this->conn->query($sql);
            $rows = $stmt->fetchAll();
            return $rows;
        } catch (Exception $e) {
            $this->error($e->getMessage());
        }
    }

    public function getAuthLevel($network, $auth)
    {
        try {
            $qb = $this->conn->createQueryBuilder();
            $stmt = $qb
                ->select('authlevel')
                ->from('auth')
                ->where('network = ? AND authname = ?')
                ->setParameter(0, $network)
                ->setParameter(1, strtolower($auth))
                ->execute();
//            $sql = 'SELECT `authlevel` FROM `auth` WHERE `network` = ' . $this->conn->quote($network) . ' AND `authname` = ' . $this->conn->quote(strtolower($auth)) . '';
//            $stmt = $this->conn->query($sql);
            $row = $stmt->fetch();
            return $row['authlevel'];
        } catch (Exception $e) {
            $this->error($e->getMessage());
        }
    }
}
