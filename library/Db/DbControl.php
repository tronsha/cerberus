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

/**
 * Class DbControl
 * @package Cerberus
 * @author Stefan Hüsges
 * @link http://www.mpcx.net/projekte/cerberus/ Project Homepage
 * @link https://github.com/tronsha/cerberus Project on GitHub
 * @license http://www.gnu.org/licenses/gpl-3.0 GNU General Public License
 */
class DbControl
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
     * @param string $command
     * @param string $data
     * @return int
     */
    public function addControl($command, $data)
    {
        $qb = $this->db->getConnection()->createQueryBuilder();
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
            ->setParameter(2, $this->db->getBotId())
            ->execute();
        return $this->db->lastInsertId('control');
    }

    /**
     * @return array
     */
    public function getControl()
    {
        $qb = $this->db->getConnection()->createQueryBuilder();
        $stmt = $qb
            ->select('id', 'command', 'data')
            ->from('control')
            ->where('bot_id = ?')
            ->orderBy('id', 'ASC')
            ->setMaxResults(1)
            ->setParameter(0, $this->db->getBotId())
            ->execute();
        return $stmt->fetch();
    }

    /**
     * @param int $id
     */
    public function removeControl($id)
    {
        $qb = $this->db->getConnection()->createQueryBuilder();
        $qb ->delete('control')
            ->where('id = ?')
            ->setParameter(0, $id)
            ->execute();
    }
}
