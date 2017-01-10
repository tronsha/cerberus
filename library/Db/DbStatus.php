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
use Exception;

/**
 * Class DbStatus
 * @package Cerberus
 * @author Stefan Hüsges
 * @link http://www.mpcx.net/projekte/cerberus/ Project Homepage
 * @link https://github.com/tronsha/cerberus Project on GitHub
 * @license http://www.gnu.org/licenses/gpl-3.0 GNU General Public License
 */
class DbStatus
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
     * @param string $status
     * @param string $text
     * @param array $data
     * @return int
     */
    public function addStatus($status, $text, $data)
    {
        $now = (new DateTime())->format('Y-m-d H:i:s');
        $qb = $this->db->getConnection()->createQueryBuilder();
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
            ->setParameter(4, $this->db->getBotId())
            ->execute();
        return $this->db->lastInsertId('status');
    }

    /**
     * @param mixed|null $status
     * @return mixed
     */
    public function getStatus($status = null)
    {
        $qb = $this->db->getConnection()->createQueryBuilder();
        $qb ->select('id', 'status', 'text', 'data')
            ->from('status')
            ->where('bot_id = ?')
            ->setMaxResults(1)
            ->setParameter(0, $this->db->getBotId());
        if ($status === null) {
            $qb->orderBy('id', 'ASC');
        } else {
            $qb->orderBy('id', 'DESC');
            if (is_array($status) === true) {
                foreach ($status as &$value) {
                    $value =  '\'' . $value . '\'';
                }
                $qb->andWhere($qb->expr()->in('status', $status));
            } else {
                $qb->andWhere('status = ?');
                $qb->setParameter(1, $status);
            }
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
    }

    /**
     * @param int $id
     */
    public function removeStatus($id)
    {
        $qb = $this->db->getConnection()->createQueryBuilder();
        $qb ->delete('status')
            ->where('id = ?')
            ->setParameter(0, $id)
            ->execute();
    }

    /**
     *
     */
    public function cleanupStatus()
    {
        $oneMinuteAgo = (new DateTime())->modify('-1 minute')->format('Y-m-d H:i:s');
        $qb = $this->db->getConnection()->createQueryBuilder();
        $qb ->delete('status')
            ->where('time <= ?')
            ->setParameter(0, $oneMinuteAgo)
            ->execute();
    }
}
