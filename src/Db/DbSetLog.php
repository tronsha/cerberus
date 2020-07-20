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

use DateTime;

/**
 * Class DbSetLog
 * @package Cerberus
 * @author Stefan Hüsges
 * @link http://www.mpcx.net/projekte/cerberus/ Project Homepage
 * @link https://github.com/tronsha/cerberus Project on GitHub
 * @license http://www.gnu.org/licenses/gpl-3.0 GNU General Public License
 */
class DbSetLog extends Db
{
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
        $now = (new DateTime())->format('Y-m-d H:i:s');
        $qb = $this->getDb()->getConnection()->createQueryBuilder();
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
            ->setParameter(0, $this->getDb()->getBotId())
            ->setParameter(1, $network)
            ->setParameter(2, $command)
            ->setParameter(3, $irc)
            ->setParameter(4, $now)
            ->setParameter(5, $direction)
            ->execute();
        $logId = $this->getDb()->lastInsertId('log');
        if ('<' === $direction) {
            switch (strtolower($command)) {
                case 'privmsg':
                    $this->setPrivmsgLog($rest, $nick, $text, $now, $direction, $logId);
                    break;
                case 'notice':
                    $this->setNoticeLog($rest, $nick, $text, $now, $logId);
                    break;
                case 'join':
                    $this->setJoinLog($rest, $nick, $now, $logId);
                    break;
                case 'part':
                    $this->setPartLog($rest, $nick, $text, $now, $logId);
                    break;
                case 'quit':
                    $this->setQuitLog($nick, $text, $now, $logId);
                    break;
                case 'kick':
                    list($channel, $kicked) = explode(' ', $rest);
                    $this->setKickLog($channel, $nick, $kicked, $text, $now, $logId);
                    break;
                case 'nick':
                    $this->setNickLog($nick, $text, $now, $logId);
                    break;
                case 'topic':
                    $this->setTopicLog($rest, $nick, $text, $now, $logId);
                    break;
            }
        } elseif ('>' === $direction) {
            switch (strtolower($command)) {
                case 'privmsg':
                    $this->setPrivmsgLog($rest, $nick, $text, $now, $direction, $logId);
                    break;
                case 'notice':
                    $this->setNoticeLog($rest, $nick, $text, $now, $logId);
                    break;
            }
        }
    }

    /**
     * @param string $channel
     * @param string $nick
     * @param string $text
     * @param string $time
     * @param string $direction
     * @param string|null $logId
     * @param string|null $botId
     */
    public function setPrivmsgLog($channel, $nick, $text, $time, $direction, $logId = null, $botId = null)
    {
        $qb = $this->getDb()->getConnection()->createQueryBuilder();
        $qb ->insert('log_privmsg')
            ->values(
                [
                    'log_id' => '?',
                    'bot_id' => '?',
                    'channel' => '?',
                    'nick' => '?',
                    'text' => '?',
                    'time' => '?',
                    'direction' => '?'
                ]
            )
            ->setParameter(0, $logId)
            ->setParameter(1, (null === $botId ? $this->getDb()->getBotId() : $botId))
            ->setParameter(2, $channel)
            ->setParameter(3, $nick)
            ->setParameter(4, $text)
            ->setParameter(5, $time)
            ->setParameter(6, $direction)
            ->execute();
    }

    /**
     * @param string $target
     * @param string $nick
     * @param string $text
     * @param string $time
     * @param string|null $logId
     * @param string|null $botId
     */
    public function setNoticeLog($target, $nick, $text, $time, $logId = null, $botId = null)
    {
        $qb = $this->getDb()->getConnection()->createQueryBuilder();
        $qb ->insert('log_notice')
            ->values(
                [
                    'log_id' => '?',
                    'bot_id' => '?',
                    'target' => '?',
                    'nick' => '?',
                    'text' => '?',
                    'time' => '?'
                ]
            )
            ->setParameter(0, $logId)
            ->setParameter(1, (null === $botId ? $this->getDb()->getBotId() : $botId))
            ->setParameter(2, $target)
            ->setParameter(3, $nick)
            ->setParameter(4, $text)
            ->setParameter(5, $time)
            ->execute();
    }

    /**
     * @param string $channel
     * @param string $nick
     * @param string $time
     * @param string|null $logId
     * @param string|null $botId
     */
    public function setJoinLog($channel, $nick, $time, $logId = null, $botId = null)
    {
        $qb = $this->getDb()->getConnection()->createQueryBuilder();
        $qb ->insert('log_join')
            ->values(
                [
                    'log_id' => '?',
                    'bot_id' => '?',
                    'channel' => '?',
                    'nick' => '?',
                    'time' => '?'
                ]
            )
            ->setParameter(0, $logId)
            ->setParameter(1, (null === $botId ? $this->getDb()->getBotId() : $botId))
            ->setParameter(2, $channel)
            ->setParameter(3, $nick)
            ->setParameter(4, $time)
            ->execute();
    }

    /**
     * @param string $channel
     * @param string $nick
     * @param string $text
     * @param string $time
     * @param string|null $logId
     * @param string|null $botId
     */
    public function setPartLog($channel, $nick, $text, $time, $logId = null, $botId = null)
    {
        $qb = $this->getDb()->getConnection()->createQueryBuilder();
        $qb ->insert('log_part')
            ->values(
                [
                    'log_id' => '?',
                    'bot_id' => '?',
                    'channel' => '?',
                    'nick' => '?',
                    'text' => '?',
                    'time' => '?'
                ]
            )
            ->setParameter(0, $logId)
            ->setParameter(1, (null === $botId ? $this->getDb()->getBotId() : $botId))
            ->setParameter(2, $channel)
            ->setParameter(3, $nick)
            ->setParameter(4, $text)
            ->setParameter(5, $time)
            ->execute();
    }

    /**
     * @param string $nick
     * @param string $text
     * @param string $time
     * @param string|null $logId
     * @param string|null $botId
     */
    public function setQuitLog($nick, $text, $time, $logId = null, $botId = null)
    {
        $qb = $this->getDb()->getConnection()->createQueryBuilder();
        $qb ->insert('log_quit')
            ->values(
                [
                    'log_id' => '?',
                    'bot_id' => '?',
                    'nick' => '?',
                    'text' => '?',
                    'time' => '?'
                ]
            )
            ->setParameter(0, $logId)
            ->setParameter(1, (null === $botId ? $this->getDb()->getBotId() : $botId))
            ->setParameter(2, $nick)
            ->setParameter(3, $text)
            ->setParameter(4, $time)
            ->execute();
    }

    /**
     * @param string $channel
     * @param string $nick
     * @param string $kicked
     * @param string $text
     * @param string $time
     * @param string|null $logId
     * @param string|null $botId
     */
    public function setKickLog($channel, $nick, $kicked, $text, $time, $logId = null, $botId = null)
    {
        $qb = $this->getDb()->getConnection()->createQueryBuilder();
        $qb ->insert('log_kick')
            ->values(
                [
                    'log_id' => '?',
                    'bot_id' => '?',
                    'channel' => '?',
                    'nick' => '?',
                    'kicked' => '?',
                    'text' => '?',
                    'time' => '?'
                ]
            )
            ->setParameter(0, $logId)
            ->setParameter(1, (null === $botId ? $this->getDb()->getBotId() : $botId))
            ->setParameter(2, $channel)
            ->setParameter(3, $nick)
            ->setParameter(4, $kicked)
            ->setParameter(5, $text)
            ->setParameter(6, $time)
            ->execute();
    }

    /**
     * @param string $oldNick
     * @param string $newNick
     * @param string $time
     * @param string|null $logId
     * @param string|null $botId
     */
    public function setNickLog($oldNick, $newNick, $time, $logId = null, $botId = null)
    {
        $qb = $this->getDb()->getConnection()->createQueryBuilder();
        $qb ->insert('log_nick')
            ->values(
                [
                    'log_id' => '?',
                    'bot_id' => '?',
                    'oldnick' => '?',
                    'newnick' => '?',
                    'time' => '?'
                ]
            )
            ->setParameter(0, $logId)
            ->setParameter(1, (null === $botId ? $this->getDb()->getBotId() : $botId))
            ->setParameter(2, $oldNick)
            ->setParameter(3, $newNick)
            ->setParameter(4, $time)
            ->execute();
    }

    /**
     * @param string $channel
     * @param string $nick
     * @param string $topic
     * @param string $time
     * @param string|null $logId
     * @param string|null $botId
     */
    public function setTopicLog($channel, $nick, $topic, $time, $logId = null, $botId = null)
    {
        $qb = $this->getDb()->getConnection()->createQueryBuilder();
        $qb ->insert('log_topic')
            ->values(
                [
                    'log_id' => '?',
                    'bot_id' => '?',
                    'channel' => '?',
                    'nick' => '?',
                    'topic' => '?',
                    'time' => '?'
                ]
            )
            ->setParameter(0, $logId)
            ->setParameter(1, (null === $botId ? $this->getDb()->getBotId() : $botId))
            ->setParameter(2, $channel)
            ->setParameter(3, $nick)
            ->setParameter(4, $topic)
            ->setParameter(5, $time)
            ->execute();
    }
}
