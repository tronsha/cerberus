<?php

/*
 * Cerberus IRCBot
 * Copyright (C) 2008 - 2019 Stefan Hüsges
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
 * Class DbSetChannelTopic
 * @package Cerberus
 * @author Stefan Hüsges
 * @link http://www.mpcx.net/projekte/cerberus/ Project Homepage
 * @link https://github.com/tronsha/cerberus Project on GitHub
 * @license http://www.gnu.org/licenses/gpl-3.0 GNU General Public License
 */
class DbSetChannelTopic extends Db
{
    /**
     * @param string $channel
     * @param string $topic
     */
    public function setChannelTopic($channel, $topic)
    {
        $qb = $this->getDb()->getConnection()->createQueryBuilder();
        $qb ->update('channel')
            ->set('topic', '?')
            ->where('bot_id = ? AND channel = ?')
            ->setParameter(0, $topic)
            ->setParameter(1, $this->getDb()->getBotId())
            ->setParameter(2, $channel)
            ->execute();
    }
}
