<?php

/*   Cerberus IRCBot
 *   Copyright (C) 2008 - 2016 Stefan Hüsges
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

use Cerberus\Db\DbLog;
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
    protected $config = [];
    protected $irc = null;
    protected $conn = null;
    protected $botId = null;
    protected $log = null;

    /**
     * @param array $config
     * @param Irc $irc
     */
    public function __construct($config, Irc $irc = null)
    {
        $this->irc = $irc;
        $this->config = $config;
        $this->log = new DbLog($this);
    }

    /**
     *
     */
    public function __destruct()
    {
    }

    /**
     * @return \Doctrine\DBAL\Connection
     */
    public function getConn()
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
     * @return \Doctrine\DBAL\Connection
     * @throws \Doctrine\DBAL\DBALException
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
        return $this->conn->close();
    }


    /**
     * @param string $error
     */
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

    /**
     * @param int $pid
     * @param string $nick
     */
    public function createBot($pid, $nick)
    {
        try {
            $now = (new DateTime())->format('Y-m-d H:i:s');
            $qb = $this->conn->createQueryBuilder();
            $qb ->insert('bot')
                ->values(
                    [
                        'pid' => '?',
                        'start' => '?',
                        'nick' => '?'
                    ]
                )
                ->setParameter(0, $pid)
                ->setParameter(1, $now)
                ->setParameter(2, $nick)
                ->execute();
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

    /**
     * @param int|null $botId
     */
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
            $this->close();
        } catch (Exception $e) {
            $this->error($e->getMessage());
        }
    }

    /**
     * @param int|null $botId
     */
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
            $qb = $this->conn->createQueryBuilder();
            $qb ->delete('control')
                ->where('bot_id = ?')
                ->setParameter(0, ($botId === null ? $this->botId : $botId))
                ->execute();
            $qb = $this->conn->createQueryBuilder();
            $qb ->delete('status')
                ->where('bot_id = ?')
                ->setParameter(0, ($botId === null ? $this->botId : $botId))
                ->execute();
        } catch (Exception $e) {
            $this->error($e->getMessage());
        }
    }

    /**
     * @return array
     */
    public function getActiveBotList()
    {
        try {
            $qb = $this->conn->createQueryBuilder();
            $qb ->select('*')
                ->from('bot');
            if ($this->config['driver'] === 'pdo_sqlite') {
                $qb->where('stop = \'NULL\'');
            } else {
                $qb->where('stop IS NULL');
            }
            $stmt = $qb->execute();
            $rows = $stmt->fetchAll();
            return $rows;
        } catch (Exception $e) {
            $this->error($e->getMessage());
        }
    }

    /**
     * @param string $network
     * @return int
     */
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
            $row = $stmt->fetch();
            try {
                $row['ip'] = gethostbyname($row['host']);
            } catch (Exception $e) {
                $this->irc->error($e->getMessage());
            }
            $qb = $this->conn->createQueryBuilder();
            $qb ->update('bot')
                ->set('server_id', '?')
                ->where('id = ?')
                ->setParameter(0, $row['id'])
                ->setParameter(1, $this->botId)
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
            $stmt = $this->conn->query($sql);
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
            $qb = $this->conn->createQueryBuilder();
            $stmt = $qb
                ->select('text')
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
     * @param string $text
     */
    public function addWrite($text)
    {
        try {
            $qb = $this->conn->createQueryBuilder();
            $qb ->insert('send')
                ->values(
                    [
                        'text' => '?',
                        'bot_id' => '?'
                    ]
                )
                ->setParameter(0, $text)
                ->setParameter(1, $this->botId)
                ->execute();
        } catch (Exception $e) {
            $this->error($e->getMessage());
        }
    }

    /**
     * @return array
     */
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
            return $stmt->fetch();
        } catch (Exception $e) {
            $this->error($e->getMessage());
        }
    }

    /**
     * @param int $id
     */
    public function removeWrite($id)
    {
        try {
            $qb = $this->conn->createQueryBuilder();
            $qb ->delete('send')
                ->where('id = ?')
                ->setParameter(0, $id)
                ->execute();
        } catch (Exception $e) {
            $this->error($e->getMessage());
        }
    }

    /**
     * @param string $irc
     * @param string $command
     * @param string $network
     * @param string $nick
     * @param string $rest
     * @param string $text
     * @param string $direction
     */
    public function setLog($irc, $command, $network, $nick, $rest, $text, $direction)
    {
        try {
            $now = (new DateTime())->format('Y-m-d H:i:s');
            $qb = $this->conn->createQueryBuilder();
            $qb ->insert('log')
                ->values(
                    [
                        'bot_id' => '?',
                        'network' => '?',
                        'command' => '?',
                        'irc' => '?',
                        'time' => '?',
                        'direction' => '?'
                    ]
                )
                ->setParameter(0, $this->botId)
                ->setParameter(1, $network)
                ->setParameter(2, $command)
                ->setParameter(3, $irc)
                ->setParameter(4, $now)
                ->setParameter(5, $direction)
                ->execute();
            $logId = $this->conn->lastInsertId();
            if ($direction == '<') {
                switch (strtolower($command)) {
                    case 'privmsg':
                        $this->log->setPrivmsgLog($rest, $nick, $text, $now, $direction, $logId);
                        break;
                    case 'notice':
                        $this->log->setNoticeLog($rest, $nick, $text, $now, $logId);
                        break;
                    case 'join':
                        $this->log->setJoinLog($rest, $nick, $now, $logId);
                        break;
                    case 'part':
                        $this->log->setPartLog($rest, $nick, $text, $now, $logId);
                        break;
                    case 'quit':
                        $this->log->setQuitLog($nick, $text, $now, $logId);
                        break;
                    case 'kick':
                        list($channel, $kicked) = explode(' ', $rest);
                        $this->log->setKickLog($channel, $nick, $kicked, $text, $now, $logId);
                        break;
                    case 'nick':
                        $this->log->setNickLog($nick, $text, $now, $logId);
                        break;
                    case 'topic':
                        $this->log->setTopicLog($rest, $nick, $text, $now, $logId);
                        break;
                }
            } elseif ($direction == '>') {
                switch (strtolower($command)) {
                    case 'privmsg':
                        $this->log->setPrivmsgLog($rest, $nick, $text, $now, $direction, $logId);
                        break;
                    case 'notice':
                        $this->log->setNoticeLog($rest, $nick, $text, $now, $logId);
                        break;
                }
            }
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
            $qb = $this->conn->createQueryBuilder();
            $qb ->update('bot')
                ->set('ping', '?')
                ->where('id = ?')
                ->setParameter(0, $now)
                ->setParameter(1, $this->botId)
                ->execute();
        } catch (Exception $e) {
            $this->error($e->getMessage());
        }
    }

    /**
     * @param string $nick
     */
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
        } catch (Exception $e) {
            $this->error($e->getMessage());
        }
    }

    /**
     * @param string $channel
     */
    public function addChannel($channel)
    {
        try {
            $qb = $this->conn->createQueryBuilder();
            $qb ->insert('channel')
                ->values(
                    [
                        'channel' => '?',
                        'bot_id' => '?'
                    ]
                )
                ->setParameter(0, $channel)
                ->setParameter(1, $this->botId)
                ->execute();
        } catch (Exception $e) {
            $this->error($e->getMessage());
        }
    }

    /**
     * @param string $channel
     */
    public function removeChannel($channel)
    {
        try {
            $qb = $this->conn->createQueryBuilder();
            $qb ->delete('channel')
                ->where('channel = ? AND bot_id = ?')
                ->setParameter(0, $channel)
                ->setParameter(1, $this->botId)
                ->execute();
            $this->removeUserFromChannel($channel);
        } catch (Exception $e) {
            $this->error($e->getMessage());
        }
    }

    /**
     * @param string $user
     */
    public function removeUser($user)
    {
        try {
            $this->removeUserFromChannel('%', $user);
        } catch (Exception $e) {
            $this->error($e->getMessage());
        }
    }

    /**
     * @param string $channel
     * @param string $user
     * @param string $mode
     */
    public function addUserToChannel($channel, $user, $mode = '')
    {
        try {
            $qb = $this->conn->createQueryBuilder();
            $qb ->insert('channel_user')
                ->values(
                    [
                        'username' => '?',
                        'mode' => '?',
                        'channel' => '?',
                        'bot_id' => '?'
                    ]
                )
                ->setParameter(0, $user)
                ->setParameter(1, $mode)
                ->setParameter(2, $channel)
                ->setParameter(3, $this->botId)
                ->execute();
        } catch (Exception $e) {
            $this->error($e->getMessage());
        }
    }

    /**
     * @param string $channel
     * @param string $user
     */
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
        } catch (Exception $e) {
            $this->error($e->getMessage());
        }
    }

    /**
     * @param string $channel
     * @param string $user
     */
    public function getUserInChannel($channel, $user)
    {
        try {
            $qb = $this->conn->createQueryBuilder();
            $stmt = $qb ->select('*')
                ->from('channel_user')
                ->where('username = ? AND channel = ? AND bot_id = ?')
                ->setParameter(0, $user)
                ->setParameter(1, $channel)
                ->setParameter(2, $this->botId)
                ->execute();
            $rows = $stmt->fetchAll();
            return $rows;
        } catch (Exception $e) {
            $this->error($e->getMessage());
        }
    }

    /**
     * @param string $old
     * @param string $new
     */
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
        } catch (Exception $e) {
            $this->error($e->getMessage());
        }
    }

    /**
     * @param string $channel
     * @param string $topic
     */
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
        } catch (Exception $e) {
            $this->error($e->getMessage());
        }
    }

    /**
     * @return array
     */
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
            $rows = $stmt->fetchAll();
            return $rows;
        } catch (Exception $e) {
            $this->error($e->getMessage());
        }
    }

    /**
     * @param string $network
     * @param string $auth
     * @return string
     */
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
            $row = $stmt->fetch();
            return $row['authlevel'];
        } catch (Exception $e) {
            $this->error($e->getMessage());
        }
    }

    /**
     * @param string $command
     * @param string $data
     */
    public function addControl($command, $data)
    {
        try {
            $qb = $this->conn->createQueryBuilder();
            $qb ->insert('control')
                ->values(
                    [
                        'command' => '?',
                        'data' => '?',
                        'bot_id' => '?'
                    ]
                )
                ->setParameter(0, $command)
                ->setParameter(1, $data)
                ->setParameter(2, $this->botId)
                ->execute();
        } catch (Exception $e) {
            $this->error($e->getMessage());
        }
    }

    /**
     * @return array
     */
    public function getControl()
    {
        try {
            $qb = $this->conn->createQueryBuilder();
            $stmt = $qb
                ->select('id', 'command', 'data')
                ->from('control')
                ->where('bot_id = ?')
                ->orderBy('id', 'ASC')
                ->setMaxResults(1)
                ->setParameter(0, $this->botId)
                ->execute();
            return $stmt->fetch();
        } catch (Exception $e) {
            $this->error($e->getMessage());
        }
    }

    /**
     * @param int $id
     */
    public function removeControl($id)
    {
        try {
            $qb = $this->conn->createQueryBuilder();
            $qb ->delete('control')
                ->where('id = ?')
                ->setParameter(0, $id)
                ->execute();
        } catch (Exception $e) {
            $this->error($e->getMessage());
        }
    }

    /**
     * @param string $status
     * @param string $text
     * @param array $data
     */
    public function addStatus($status, $text, $data)
    {
        try {
            $now = (new DateTime())->format('Y-m-d H:i:s');
            $qb = $this->conn->createQueryBuilder();
            $qb ->insert('status')
                ->values(
                    [
                        'status' => '?',
                        'text' => '?',
                        'data' => '?',
                        'time' => '?',
                        'bot_id' => '?'
                    ]
                )
                ->setParameter(0, $status)
                ->setParameter(1, $text)
                ->setParameter(2, json_encode($data))
                ->setParameter(3, $now)
                ->setParameter(4, $this->botId)
                ->execute();
        } catch (Exception $e) {
            $this->error($e->getMessage());
        }
    }

    /**
     * @param mixed|null $status
     * @return mixed
     */
    public function getStatus($status = null)
    {
        try {
            $qb = $this->conn->createQueryBuilder();
            $qb->select('id', 'status', 'text', 'data')
               ->from('status')
               ->where('bot_id = ?')
               ->setMaxResults(1)
               ->setParameter(0, $this->botId);
            if ($status === null) {
                $qb->orderBy('id', 'ASC');
            } else {
                $qb->orderBy('id', 'DESC')
                   ->andWhere('status = ?')
                   ->setParameter(1, (string)$status);
            }
            $stmt = $qb->execute();
            $result = $stmt->fetch();
            if (empty($result) === true) {
                return null;
            }
            $result['data'] = json_decode($result['data']);
            $result['type'] = 'status';
            $this->removeStatus($result['id']);
            return $result;
        } catch (Exception $e) {
            $this->error($e->getMessage());
        }
    }

    /**
     * @param int $id
     */
    public function removeStatus($id)
    {
        try {
            $qb = $this->conn->createQueryBuilder();
            $qb ->delete('status')
                ->where('id = ?')
                ->setParameter(0, $id)
                ->execute();
        } catch (Exception $e) {
            $this->error($e->getMessage());
        }
    }

    /**
     *
     */
    public function cleanupStatus()
    {
        try {
            $oneMinuteAgo = (new DateTime())->modify('-1 minute')->format('Y-m-d H:i:s');
            $qb = $this->conn->createQueryBuilder();
            $qb ->delete('status')
                ->where('time <= ?')
                ->setParameter(0, $oneMinuteAgo)
                ->execute();
        } catch (Exception $e) {
            $this->error($e->getMessage());
        }
    }

    /**
     *
     */
    public function cleanupLog()
    {
        try {
            $oneWeekAgo = (new DateTime())->modify('-1 week')->format('Y-m-d H:i:s');
            $qb = $this->conn->createQueryBuilder();
            $qb ->delete('log_join')
                ->where('time <= ?')
                ->setParameter(0, $oneWeekAgo)
                ->execute();
            $qb = $this->conn->createQueryBuilder();
            $qb ->delete('log_part')
                ->where('time <= ?')
                ->setParameter(0, $oneWeekAgo)
                ->execute();
            $qb = $this->conn->createQueryBuilder();
            $qb ->delete('log_quit')
                ->where('time <= ?')
                ->setParameter(0, $oneWeekAgo)
                ->execute();
            $qb = $this->conn->createQueryBuilder();
            $qb ->delete('log_kick')
                ->where('time <= ?')
                ->setParameter(0, $oneWeekAgo)
                ->execute();
            $qb = $this->conn->createQueryBuilder();
            $qb ->delete('log_nick')
                ->where('time <= ?')
                ->setParameter(0, $oneWeekAgo)
                ->execute();
            $qb = $this->conn->createQueryBuilder();
            $qb ->delete('log_topic')
                ->where('time <= ?')
                ->setParameter(0, $oneWeekAgo)
                ->execute();
        } catch (Exception $e) {
            $this->error($e->getMessage());
        }
    }
}
