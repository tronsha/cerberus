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
 * Class DbGetServerData
 * @package Cerberus
 * @author Stefan Hüsges
 * @link http://www.mpcx.net/projekte/cerberus/ Project Homepage
 * @link https://github.com/tronsha/cerberus Project on GitHub
 * @license http://www.gnu.org/licenses/gpl-3.0 GNU General Public License
 */
class DbGetServerData extends Db
{
    /**
     * @link https://freenode.net/irc_servers.shtml
     * @link https://www.quakenet.org/servers
     * @param array $server
     * @param int $i
     * @return array
     */
    public function getServerData($server, $i = 0)
    {
        $network = strtolower($server['network']);
        $i = (string)$i;
        $qb = $this->getDb()->getConnection()->createQueryBuilder();
        $stmt = $qb
            ->select('s.id', 's.server AS host', 's.port')
            ->from('server', 's')
            ->innerJoin('s', 'network', 'n', 's.network_id = n.id')
            ->where('n.network = ?')
            ->orderBy('s.id', 'ASC')
            ->addOrderBy('s.port', 'ASC')
            ->setFirstResult($i)
            ->setMaxResults(1)
            ->setParameter(0, $network)
            ->execute();
        $row = $stmt->fetch();
        $row['ip'] = gethostbyname($row['host']);
        $qb = $this->getDb()->getConnection()->createQueryBuilder();
        $qb ->update('bot')
            ->set('server_id', '?')
            ->where('id = ?')
            ->setParameter(0, $row['id'])
            ->setParameter(1, $this->getDb()->getBotId())
            ->execute();
        return array_merge($server, $row);
    }
}
