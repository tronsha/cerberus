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
 * Class DbGetStatus
 * @package Cerberus
 * @author Stefan Hüsges
 * @link http://www.mpcx.net/projekte/cerberus/ Project Homepage
 * @link https://github.com/tronsha/cerberus Project on GitHub
 * @license http://www.gnu.org/licenses/gpl-3.0 GNU General Public License
 */
class DbGetStatus extends Db
{
    /**
     * @param mixed|null $status
     * @return mixed
     */
    public function getStatus($status = null)
    {
        $qb = $this->getDb()->getConnection()->createQueryBuilder();
        $qb ->select('id', 'status', 'text', 'data')
            ->from('status')
            ->where('bot_id = ?')
            ->setMaxResults(1)
            ->setParameter(0, $this->getDb()->getBotId());
        if (null === $status) {
            $qb->orderBy('id', 'ASC');
        } else {
            $qb->orderBy('id', 'DESC');
            if (true === is_array($status)) {
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
        if (true === empty($result)) {
            return null;
        }
        $result['data'] = json_decode($result['data']);
        $result['type'] = 'status';
        $this->getDb()->removeStatus($result['id']);
        return $result;
    }
}
