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
    const DBTABLE = 'plugin_news';
    const CHANNEL = '#cerberbot';

    /**
     *
     */
    protected function init()
    {
        $this->addCron('0,30 * * * *', 'run', ['id' => 0]);
        $this->addCron('15,45 * * * *', 'run', ['id' => 1]);
    }
    
    /**
     * @param Db $db
     */
    public static function install(Db $db)
    {
        $schema = $db->getConnection()->getSchemaManager();
        if (false === $schema->tablesExist(self::DBTABLE)) {
            $table = new Table(self::DBTABLE);
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
        if (true === $schema->tablesExist(self::DBTABLE)) {
            $schema->dropTable(self::DBTABLE);
        }
    }

    protected function getData($id = null)
    {
        $data = [];
        $data[0]['url'] = 'https://www.heise.de/newsticker/heise.rdf';
        $data[0]['path'] = 'item';
        $data[0]['regex'] = '/\-([\d]+)\.html/';
        $data[1]['url'] = 'https://rss.golem.de/rss.php?feed=RSS2.0';
        $data[1]['path'] = 'channel/item';
        $data[1]['regex'] = '/\-([\d]+)\-rss\.html/';
        return null === $id ? $data : [$data[$id]];
    }
    
    public function run($param = null)
    {
        $dataArray = true === isset($param['id']) ? $this->getData($param['id']) : $this->getData();
        foreach ($dataArray as $data) {
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
                preg_match('/https?\:\/\/(?:www|rss)?\.?([^\/]+)/i', $url, $match);
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
                $this->getActions()->privmsg(self::CHANNEL, $output);
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
        $qb ->insert(self::DBTABLE)
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
            ->from(self::DBTABLE)
            ->where('news_id = ? AND site_id = ?')
            ->setParameter(0, $newsId)
            ->setParameter(1, $siteId)
            ->execute();
        return $stmt->fetch();
    }
}
