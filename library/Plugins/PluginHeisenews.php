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

namespace Cerberus\Plugins;

use Cerberus\Db;
use Cerberus\Plugin;
use Doctrine\DBAL\Schema\Table;

/**
 * Class PluginHeisenews
 * @package Cerberus\Plugins
 * @author Stefan Hüsges
 */
class PluginHeisenews extends Plugin
{
    const dbTable = 'plugin_heise';
    const channel = '#cerberbot';

    /**
     *
     */
    protected function init()
    {
        $this->addCron('*/5 * * * *', 'getNews');
    }
    
    /**
     * @param Db $db
     */
    public static function install(Db $db)
    {
        $schema = $db->getConnection()->getSchemaManager();
        if (false === $schema->tablesExist(self::dbTable)) {
            $table = new Table(self::dbTable);
            $table->addColumn('id', 'integer', ['unsigned' => true, 'autoincrement' => true]);
            $table->setPrimaryKey(['id']);
            $table->addColumn('heise_id', 'integer', ['unsigned' => true]);
            $table->addUniqueIndex(['heise_id']);
            $table->addColumn('title', 'string', ['length' => 255]);
            $table->addColumn('link', 'string', ['length' => 255]);
            $schema->createTable($table);
        }
    }

    /**
     * @param Db $db
     */
    public static function uninstall(Db $db)
    {
        $schema = $db->getConnection()->getSchemaManager();
        if (true === $schema->tablesExist(self::dbTable)) {
            $schema->dropTable(self::dbTable);
        }
    }

    public function getNews()
    {
        $match = [];
        $url = 'https://www.heise.de/newsticker/heise.rdf';
        $rdf = file_get_contents($url, false, stream_context_create(['http' => ['ignore_errors' => true]]));
        $xmlObject = new \SimpleXMLElement($rdf);
        foreach ($xmlObject->item as $item) {
            $title = trim($item->title);
            $link = trim(preg_replace('/\?.*/', '', $item->link));
            preg_match('/\-([\d]+)\.html/', $link, $match);
            $heiseId = intval($match[1]);
            $qb = $this->getDb()->getConnection()->createQueryBuilder();
            $stmt = $qb
                ->select('*')
                ->from(self::dbTable)
                ->where('heise_id = ?')
                ->setParameter(0, $heiseId)
                ->execute();
            if (false === $stmt->fetch()) {
                $output = $title . ' -> ' . $link;
                $this->getActions()->privmsg(self::channel, $output);
                $qb ->insert(self::dbTable)
                    ->values(
                        [
                            'heise_id' => '?',
                            'title' => '?',
                            'link' => '?'
                        ]
                    )
                    ->setParameter(0, $heiseId)
                    ->setParameter(1, $title)
                    ->setParameter(2, $link)
                    ->execute();
            }
        }
    }
}
