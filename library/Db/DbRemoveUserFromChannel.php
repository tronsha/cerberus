<?php

/*
 * Cerberus IRCBot
 * Copyright (C) 2008 - 2018 Stefan Hüsges
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
 * Class DbRemoveUserFromChannel
 * @package Cerberus
 * @author Stefan Hüsges
 * @link http://www.mpcx.net/projekte/cerberus/ Project Homepage
 * @link https://github.com/tronsha/cerberus Project on GitHub
 * @license http://www.gnu.org/licenses/gpl-3.0 GNU General Public License
 */
class DbRemoveUserFromChannel extends Db
{
    /**
     * @param string|null $channel
     * @param string|null $user
     */
    public function removeUserFromChannel($channel, $user = null)
    {
        $paramCount = 0;
        $qb = $this->getDb()->getConnection()->createQueryBuilder();
        $qb->delete('channel_user');
        $qb->where('bot_id = ?');
        $qb->setParameter($paramCount, $this->getDb()->getBotId());
        if (null !== $channel) {
            $paramCount++;
            $qb->andWhere('channel = ?');
            $qb->setParameter($paramCount, $channel);
        }
        if (null !== $user) {
            $paramCount++;
            $qb->andWhere('username = ?');
            $qb->setParameter($paramCount, $user);
        }
        $qb->execute();
    }
}
