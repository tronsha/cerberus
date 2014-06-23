<?php

namespace Cerberus;

use \Doctrine\DBAL\Configuration;
use \Doctrine\DBAL\DriverManager;
use \Doctrine\DBAL\Version;

/**
 * Class Db
 * @package Cerberus
 * @author Stefan Hüsges
 * @link http://www.mpcx.net/cerberus/ Project Homepage
 * @link https://github.com/tronsha/Cerberus Project on GitHub
 * @link http://www.doctrine-project.org/projects/dbal.html Database Abstraction Layer
 * @license http://www.gnu.org/licenses/gpl-3.0 GNU General Public License
 */

class Db
{
    protected $config = array();
    protected $irc = null;
    protected $conn = null;
    protected $botId = null;

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
        return $this->conn = DriverManager::getConnection($this->config, new Configuration);
    }

    public function close()
    {
        return $this->conn->close();
    }


    public function error($error)
    {
        if($this->irc !== null) {
            $this->irc->sqlError($error);
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
            $sql = 'INSERT INTO `bot` SET `pid` = ' . $this->conn->quote($pid) . ', `start` = NOW(), `nick` = ' . $this->conn->quote($nick) . '';
            $this->conn->query($sql);
            $this->botId = $this->conn->lastInsertId();
        } catch (\Exception $e) {
            $this->error($e->getMessage());
        }
    }

    public function shutdownBot($botId = null)
    {
        try {
            $sql = 'UPDATE `bot` SET `stop` = NOW() WHERE `id` = ' . ($botId === null ? $this->botId : $botId) . '';
            $this->conn->query($sql);
            $this->close();
        } catch (\Exception $e) {
            $this->error($e->getMessage());
        }
    }

    public function cleanupBot($botId = null)
    {
        try {
            $sql = 'DELETE FROM `send` WHERE `bot_id` = ' . ($botId === null ? $this->botId : $botId) . '';
            $this->conn->query($sql);
            $sql = 'DELETE FROM `channel` WHERE `bot_id` = ' . ($botId === null ? $this->botId : $botId) . '';
            $this->conn->query($sql);
            $sql = 'DELETE FROM `channel_user` WHERE `bot_id` = ' . ($botId === null ? $this->botId : $botId) . '';
            $this->conn->query($sql);
        } catch (\Exception $e) {
            $this->error($e->getMessage());
        }
    }

    public function getActiveBotList()
    {
        try {
            $sql = 'SELECT * FROM bot WHERE stop IS NULL';
            $stmt = $this->conn->query($sql);
            $rows = $stmt->fetchAll();
            return $rows;
        } catch (\Exception $e) {
            $this->error($e->getMessage());
        }
    }

    public function getServerCount($network)
    {
        try {
            $sql = 'SELECT count(*) AS number '
                . 'FROM `server` s, `network` n '
                . 'WHERE n.`id` = s.`network_id` '
                . 'AND n.`network` = ' . $this->conn->quote($network) . '';
            $stmt = $this->conn->query($sql);
            $row = $stmt->fetch();
            return $row['number'];
        } catch (\Exception $e) {
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
            $network = $server['network'];
            $i = (string)$i;
            $sql = 'SELECT s.`id` , s.`server` AS host, s.`port` '
                . 'FROM `server` s, `network` n '
                . 'WHERE n.`id` = s.`network_id` '
                . 'AND n.`network` = ' . $this->conn->quote($network) . ' '
                . 'ORDER BY s.`id` , s.`port` '
                . 'LIMIT ' . $i . ', 1';
            $stmt = $this->conn->query($sql);
            $row = $stmt->fetch();
            $row['ip'] = @gethostbyname($row['host']);

            $sql = 'UPDATE `bot` SET `server_id` = ' . $row['id'] . ' WHERE `id` = ' . $this->botId . '';
            $this->conn->query($sql);

            return array_merge($server, $row);
        } catch (\Exception $e) {
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
        } catch (\Exception $e) {
            $this->error($e->getMessage());
        }
    }

    public function getPreform($network)
    {
        try {
            $sql = 'SELECT `text` FROM `preform` WHERE `network` = ' . $this->conn->quote($network) . ' ORDER BY `priority` DESC';
            $stmt = $this->conn->query($sql);
            $rows = $stmt->fetchAll();
            return $rows;
        } catch (\Exception $e) {
            $this->error($e->getMessage());
        }
    }

    public function setWrite($text)
    {
        try {
            $sql = 'INSERT INTO `send` SET `text` = ' . $this->conn->quote($text) . ', `bot_id` = ' . $this->botId . '';
            $this->conn->query($sql);
        } catch (\Exception $e) {
            $this->error($e->getMessage());
        }
    }

    public function getWrite()
    {
        try {
            $sql = 'SELECT `id`, `text` FROM `send` WHERE `bot_id` = ' . $this->botId . ' ORDER BY `id` LIMIT 0, 1';
            $stmt = $this->conn->query($sql);
            return $stmt->fetch();
        } catch (\Exception $e) {
            $this->error($e->getMessage());
        }
    }

    public function unsetWrite($id)
    {
        try {
            $sql = 'DELETE FROM `send` WHERE `id` = ' . $this->conn->quote($id) . '';
            $this->conn->query($sql);
        } catch (\Exception $e) {
            $this->error($e->getMessage());
        }
    }

    public function setLog($network, $all, $nick, $host, $command, $rest, $text, $direction)
    {
        try {
            $sql = 'INSERT INTO log SET '
                . '`nick`      = ' . $this->conn->quote($nick) . ', '
                . '`host`      = ' . $this->conn->quote($host) . ', '
                . '`command`   = ' . $this->conn->quote($command) . ', '
                . '`rest`      = ' . $this->conn->quote($rest) . ', '
                . '`text`      = ' . $this->conn->quote($text) . ', '
                . '`all`       = ' . $this->conn->quote($all) . ', '
                . '`network`   = ' . $this->conn->quote($network) . ', '
                . '`bot_id`    = ' . $this->botId . ', '
                . '`time`      = NOW(), '
                . '`direction` = ' . $this->conn->quote($direction) . '';
            $this->conn->query($sql);
        } catch (\Exception $e) {
            $this->error($e->getMessage());
        }
    }

    public function setPing()
    {
        try {
            $sql = 'UPDATE `bot` SET `ping` = NOW() WHERE `id` = ' . $this->botId . '';
            $this->conn->query($sql);
        } catch (\Exception $e) {
            $this->error($e->getMessage());
        }
    }

    public function setBotNick($nick)
    {
        try {
            $sql = 'UPDATE `bot` SET `nick` = ' . $this->conn->quote($nick) . ' WHERE `id` = ' . $this->botId . '';
            $this->conn->query($sql);
        } catch (\Exception $e) {
            $this->error($e->getMessage());
        }
    }

    public function addChannel($channel)
    {
        try {
            $sql = 'INSERT INTO `channel` SET `channel` = ' . $this->conn->quote($channel) . ', `bot_id` = ' . $this->botId . '';
            $this->conn->query($sql);
        } catch (\Exception $e) {
            $this->error($e->getMessage());
        }
    }

    public function removeChannel($channel)
    {
        try {
            $sql = 'DELETE FROM `channel` WHERE `channel` = ' . $this->conn->quote($channel) . ', `bot_id` = ' . $this->botId . '';
            $this->conn->query($sql);
            $this->removeUserFromChannel($channel);
        } catch (\Exception $e) {
            $this->error($e->getMessage());
        }
    }

    public function removeUser($user)
    {
        try {
            $this->removeUserFromChannel('%', $user);
        } catch (\Exception $e) {
            $this->error($e->getMessage());
        }
    }

    public function addUserToChannel($channel, $user, $mode = '')
    {
        try {
            $sql = 'INSERT INTO `channel_user` SET `user` = ' . $this->conn->quote($user) . ', `mode` = ' . $this->conn->quote($mode) . ', `channel` = ' . $this->conn->quote($channel) . ', `bot_id` = ' . $this->botId . '';
            $this->conn->query($sql);
        } catch (\Exception $e) {
            $this->error($e->getMessage());
        }
    }

    public function removeUserFromChannel($channel, $user = '%')
    {
        try {
            $sql = 'DELETE FROM `channel_user` WHERE `user` LIKE ' . $this->conn->quote($user) . ' AND `channel` LIKE ' . $this->conn->quote($channel) . ' AND `bot_id` = ' . $this->botId . '';
            $this->conn->query($sql);
        } catch (\Exception $e) {
            $this->error($e->getMessage());
        }
    }

    public function changeNick($old, $new)
    {
        try {
            $sql = 'UPDATE `channel_user` SET `user` = ' . $this->conn->quote($new) . ' WHERE `bot_id` = ' . $this->botId . ' AND `user` = ' . $this->conn->quote($old) . '';
            $this->conn->query($sql);
        } catch (\Exception $e) {
            $this->error($e->getMessage());
        }
    }

    public function setChannelTopic($channel, $topic)
    {
        try {
            $sql = 'UPDATE `channel` SET `topic` = ' . $this->conn->quote($topic) . ' WHERE `bot_id` = ' . $this->botId . ' AND `channel` = ' . $this->conn->quote($channel) . '';
            $this->conn->query($sql);
        } catch (\Exception $e) {
            $this->error($e->getMessage());
        }
    }

    public function getJoinedChannels()
    {
        try {
            $sql = 'SELECT `channel` FROM `channel` WHERE `bot_id` = ' . $this->botId . '';
            $stmt = $this->conn->query($sql);
            $rows = $stmt->fetchAll();
            return $rows;
        } catch (\Exception $e) {
            $this->error($e->getMessage());
        }
    }

    public function getAuthLevel($network, $auth)
    {
        try {
            $sql = 'SELECT `authlevel` FROM `auth` WHERE `network` = ' . $this->conn->quote($network) . ' AND `authname` = ' . $this->conn->quote($auth) . '';
            $stmt = $this->conn->query($sql);
            $row = $stmt->fetch();
            return $row['authlevel'];
        } catch (\Exception $e) {
            $this->error($e->getMessage());
        }
    }
}