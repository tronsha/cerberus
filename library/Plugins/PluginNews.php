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
 * Class PluginNews
 * @package Cerberus\Plugins
 * @author Stefan Hüsges
 */
class PluginNews extends Plugin
{
    const dbTable = 'plugin_news';
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
            $table->addColumn('news_id', 'integer', ['unsigned' => true]);
            $table->addColumn('site_id', 'string', ['length' => 200]);
            $table->addUniqueIndex(['news_id', 'site_id']);
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

    protected function getData()
    {
        $data = [];
        $data[0]['url'] = 'https://www.heise.de/newsticker/heise.rdf';
        $data[0]['path'] = 'item';
        $data[0]['regex'] = '/\-([\d]+)\.html/';
        return $data;
    }
    
    public function run()
    {
        foreach ($this->getData() as $data) {
            $items = [];
            $url = trim(strtolower($data['url']));
            $rdf = $this->getNews($url);
            $xmlObject = new \SimpleXMLElement($rdf);
            $path = explode('/', $data['path']);
            foreach ($path as $pathPart) {
                $xmlObject = $xmlObject->$pathPart;
            }
            foreach ($xmlObject as $item) {
                $match = [];
                preg_match('/https?\:\/\/(?:www\.)?([^\/]+)/i', $url, $match);
                $siteId = $match[1];
                preg_match($data['regex'], $item->link, $match);
                $newsId = intval($match[1]);
                if (false === $this->checkData($newsId, $siteId)) {
                    $items[$newsId]['newsId'] = $newsId;
                    $items[$newsId]['siteId'] = $siteId;
                    $items[$newsId]['title'] = trim($item->title);
                    $items[$newsId]['link'] = trim(preg_replace('/\?.*/', '', $item->link));
                    $items[$newsId]['description'] = trim($item->description);
                }
            }
            ksort($items);
            foreach ($items as $item) {
                $output = $item['title'] . ' -> ' . $item['link'];
                $this->getActions()->privmsg(self::channel, $output);
                $this->saveData($item);
            }
        }
    }

    protected function getNews($url)
    {
        return file_get_contents($url, false, stream_context_create(['http' => ['ignore_errors' => true]]));
    }

    protected function saveData($item)
    {
        $qb = $this->getDb()->getConnection()->createQueryBuilder();
        $qb ->insert(self::dbTable)
            ->values(
                [
                    'news_id' => '?',
                    'site_id' => '?',
                    'title' => '?',
                    'link' => '?',
                    'description' => '?'
                ]
            )
            ->setParameter(0, $item['newsId'])
            ->setParameter(1, $item['siteId'])
            ->setParameter(2, $item['title'])
            ->setParameter(3, $item['link'])
            ->setParameter(4, $item['description'])
            ->execute();
    }

    protected function checkData($newsId, $siteId)
    {
        $qb = $this->getDb()->getConnection()->createQueryBuilder();
        $stmt = $qb
            ->select('*')
            ->from(self::dbTable)
            ->where('news_id = ? AND site_id = ?')
            ->setParameter(0, $newsId)
            ->setParameter(1, $siteId)
            ->execute();
        return $stmt->fetch();
    }
}
