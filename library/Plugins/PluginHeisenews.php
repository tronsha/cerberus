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
        $this->addCron('*/5 * * * *', 'run');
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
            $table->addColumn('description', 'text');
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

    public function run()
    {
        $items = [];
        $rdf = $this->getNews();
        $xmlObject = new \SimpleXMLElement($rdf);
        foreach ($xmlObject->item as $item) {
            $match = [];
            preg_match('/\-([\d]+)\.html/', $item->link, $match);
            $id = intval($match[1]);
            if (false === $this->checkData($id)) {
                $items[$id]['id'] = $id;
                $items[$id]['title'] = trim($item->title);
                $items[$id]['link'] = trim(preg_replace('/\?.*/', '', $item->link));
                $items[$id]['description'] = trim($item->description);
            }
        }
        ksort($items);
        foreach ($items as $id => $item) {
            $output = $item['title'] . ' -> ' . $item['link'];
            $this->getActions()->privmsg(self::channel, $output);
            $this->saveData($item);
        }
    }

    protected function getNews()
    {
        $url = 'https://www.heise.de/newsticker/heise.rdf';
        return file_get_contents($url, false, stream_context_create(['http' => ['ignore_errors' => true]]));
    }

    protected function saveData($item)
    {
        $qb = $this->getDb()->getConnection()->createQueryBuilder();
        $qb ->insert(self::dbTable)
            ->values(
                [
                    'heise_id' => '?',
                    'title' => '?',
                    'link' => '?',
                    'description' => '?'
                ]
            )
            ->setParameter(0, $item['id'])
            ->setParameter(1, $item['title'])
            ->setParameter(2, $item['link'])
            ->setParameter(3, $item['description'])
            ->execute();
    }

    protected function checkData($heiseId)
    {
        $qb = $this->getDb()->getConnection()->createQueryBuilder();
        $stmt = $qb
            ->select('*')
            ->from(self::dbTable)
            ->where('heise_id = ?')
            ->setParameter(0, $heiseId)
            ->execute();
        return $stmt->fetch();
    }
}
