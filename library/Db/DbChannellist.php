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

namespace Cerberus\Db;

use DateTime;

/**
 * Class DbChannellist
 * @package Cerberus
 * @author Stefan Hüsges
 * @link http://www.mpcx.net/projekte/cerberus/ Project Homepage
 * @link https://github.com/tronsha/cerberus Project on GitHub
 * @license http://www.gnu.org/licenses/gpl-3.0 GNU General Public License
 */
class DbChannellist
{
    protected $db = null;

    /**
     * Log constructor.
     * @param \Cerberus\Db $db
     */
    public function __construct($db)
    {
        $this->db = $db;
    }

    /**
     */
    public function clearChannellist()
    {
        $qb = $this->db->getConnection()->createQueryBuilder();
        $qb ->delete('channellist')
            ->where('bot_id = ?')
            ->setParameter(0, $this->db->getBotId())
            ->execute();
    }

    /**
     * @param string $network
     * @param string $channel
     * @param int $usercount
     * @param string $topic
     * @return int
     */
    public function addChannelToChannellist($network, $channel, $usercount, $topic)
    {
        $now = (new DateTime())->format('Y-m-d H:i:s');
        $qb = $this->db->getConnection()->createQueryBuilder();
        $qb ->insert('channellist')
            ->values(
                [
                    'network' => '?',
                    'channel' => '?',
                    'usercount' => '?',
                    'topic' => '?',
                    'time' => '?',
                    'bot_id' => '?'
                ]
            )
            ->setParameter(0, $network)
            ->setParameter(1, $channel)
            ->setParameter(2, $usercount)
            ->setParameter(3, $topic)
            ->setParameter(4, $now)
            ->setParameter(5, $this->db->getBotId())
            ->execute();
        return $this->db->lastInsertId('channellist');
    }

    /**
     * @return mixed
     */
    public function getChannellist()
    {
        $qb = $this->db->getConnection()->createQueryBuilder();
        $stmt = $qb
            ->select('channel', 'topic', 'usercount')
            ->from('channellist')
            ->where('bot_id = ?')
            ->setMaxResults(10000)
            ->setParameter(0, $this->db->getBotId())
            ->orderBy('usercount', 'DESC')
            ->execute();
        $row = $stmt->fetchAll();
        return $row;
    }
}
