<?php

/*
 * Cerberus IRCBot
 * Copyright (C) 2008 - 2020 Stefan Hüsges
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
 * Class DbCleanupBot
 * @package Cerberus
 * @author Stefan Hüsges
 * @link http://www.mpcx.net/projekte/cerberus/ Project Homepage
 * @link https://github.com/tronsha/cerberus Project on GitHub
 * @license http://www.gnu.org/licenses/gpl-3.0 GNU General Public License
 */
class DbCleanupBot extends Db
{
    /**
     * @param int|null $botId
     * @param array $exclude
     */
    public function cleanupBot($botId = null, $exclude = [])
    {
        if (false === in_array('send', $exclude, true)) {
            $qb = $this->getDb()->getConnection()->createQueryBuilder();
            $qb ->delete('send')
                ->where('bot_id = ?')
                ->setParameter(0, (null === $botId ? $this->getDb()->getBotId() : $botId))
                ->execute();
        }
        if (false === in_array('channel', $exclude, true)) {
            $qb = $this->getDb()->getConnection()->createQueryBuilder();
            $qb ->delete('channel')
                ->where('bot_id = ?')
                ->setParameter(0, (null === $botId ? $this->getDb()->getBotId() : $botId))
                ->execute();
        }
        if (false === in_array('channel_user', $exclude, true)) {
            $qb = $this->getDb()->getConnection()->createQueryBuilder();
            $qb ->delete('channel_user')
                ->where('bot_id = ?')
                ->setParameter(0, (null === $botId ? $this->getDb()->getBotId() : $botId))
                ->execute();
        }
        if (false === in_array('control', $exclude, true)) {
            $qb = $this->getDb()->getConnection()->createQueryBuilder();
            $qb ->delete('control')
                ->where('bot_id = ?')
                ->setParameter(0, (null === $botId ? $this->getDb()->getBotId() : $botId))
                ->execute();
        }
        if (false === in_array('status', $exclude, true)) {
            $qb = $this->getDb()->getConnection()->createQueryBuilder();
            $qb ->delete('status')
                ->where('bot_id = ?')
                ->setParameter(0, (null === $botId ? $this->getDb()->getBotId() : $botId))
                ->execute();
        }
    }
}