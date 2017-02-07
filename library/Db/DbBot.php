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
 * Class DbBot
 * @package Cerberus
 * @author Stefan Hüsges
 * @link http://www.mpcx.net/projekte/cerberus/ Project Homepage
 * @link https://github.com/tronsha/cerberus Project on GitHub
 * @license http://www.gnu.org/licenses/gpl-3.0 GNU General Public License
 */
class DbBot extends Db
{
    /**
     * @param int $pid
     * @param string $nick
     * @return int|false
     */
    public function createBot($pid, $nick)
    {
        $now = (new DateTime())->format('Y-m-d H:i:s');
        $qb = $this->getDb()->getConnection()->createQueryBuilder();
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
        $this->getDb()->setBotId($this->getDb()->lastInsertId('bot'));
        return $this->getDb()->getBotId();
    }

    /**
     * @param int|null $botId
     */
    public function shutdownBot($botId = null)
    {
        $now = (new DateTime())->format('Y-m-d H:i:s');
        $qb = $this->getDb()->getConnection()->createQueryBuilder();
        $qb ->update('bot')
            ->set('stop', '?')
            ->where('id = ?')
            ->setParameter(0, $now)
            ->setParameter(1, ($botId === null ? $this->getDb()->getBotId() : $botId))
            ->execute();
        $this->getDb()->close();
    }

    /**
     * @param int|null $botId
     * @param array $exclude
     */
    public function cleanupBot($botId = null, $exclude = [])
    {
        if (in_array('send', $exclude, true) === false) {
            $qb = $this->getDb()->getConnection()->createQueryBuilder();
            $qb ->delete('send')
                ->where('bot_id = ?')
                ->setParameter(0, ($botId === null ? $this->getDb()->getBotId() : $botId))
                ->execute();
        }
        if (in_array('channel', $exclude, true) === false) {
            $qb = $this->getDb()->getConnection()->createQueryBuilder();
            $qb ->delete('channel')
                ->where('bot_id = ?')
                ->setParameter(0, ($botId === null ? $this->getDb()->getBotId() : $botId))
                ->execute();
        }
        if (in_array('channel_user', $exclude, true) === false) {
            $qb = $this->getDb()->getConnection()->createQueryBuilder();
            $qb ->delete('channel_user')
                ->where('bot_id = ?')
                ->setParameter(0, ($botId === null ? $this->getDb()->getBotId() : $botId))
                ->execute();
        }
        if (in_array('control', $exclude, true) === false) {
            $qb = $this->getDb()->getConnection()->createQueryBuilder();
            $qb ->delete('control')
                ->where('bot_id = ?')
                ->setParameter(0, ($botId === null ? $this->getDb()->getBotId() : $botId))
                ->execute();
        }
        if (in_array('status', $exclude, true) === false) {
            $qb = $this->getDb()->getConnection()->createQueryBuilder();
            $qb ->delete('status')
                ->where('bot_id = ?')
                ->setParameter(0, ($botId === null ? $this->getDb()->getBotId() : $botId))
                ->execute();
        }
    }
}
