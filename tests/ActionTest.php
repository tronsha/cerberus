<?php

/*
 * Cerberus IRCBot
 * Copyright (C) 2008 - 2016 Stefan Hüsges
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

namespace Cerberus;

use Doctrine\DBAL\DriverManager;

class ActionTest extends \PHPUnit_Framework_TestCase
{
    protected static $config;
    protected static $database;
    protected $db;
    protected $irc;

    public static function setUpBeforeClass()
    {
        ini_set('zend.enable_gc', 0);
        date_default_timezone_set('Europe/Berlin');
        self::$config = parse_ini_file(Cerberus::getPath() . '/config.ini', true);
        self::$database = self::$config['testdb']['dbname'];
    }

    protected function setUp()
    {
        self::$config['testdb']['dbname'] = null;
        $db = DriverManager::getConnection(self::$config['testdb']);
        $sm = $db->getSchemaManager();
        $sm->dropAndCreateDatabase(self::$database);
        $db->close();
        self::$config['testdb']['dbname'] = self::$database;
        $this->db = DriverManager::getConnection(self::$config['testdb']);
        $driver = str_replace('pdo_', '', self::$config['testdb']['driver']);
        $sqlFile = file_get_contents(Cerberus::getPath() . '/cerberus.' . $driver . '.sql');
        $sqlArray = explode(';', $sqlFile);
        foreach ($sqlArray as $sqlCommand) {
            $sqlCommand = trim($sqlCommand);
            if (empty($sqlCommand) === false) {
                $this->db->query($sqlCommand . ';');
            }
        }
        self::$config['db'] = self::$config['testdb'];
        $this->irc = new Irc(self::$config);
        $this->irc->getConsole()->output(false);
        $this->irc->init();
        $this->invokeMethod($this->irc, 'loadPlugin', 'test');
    }

    protected function tearDown()
    {
        unset($this->irc);
        if ($this->db === null) {
            $this->fail('No connection to database...');
        }
        $sm = $this->db->getSchemaManager();
        $sm->tryMethod('dropDatabase', self::$database);
        $this->db->close();
    }

    public function invokeMethod(&$object, $methodName)
    {
        $reflection = new \ReflectionClass(get_class($object));
        $method = $reflection->getMethod($methodName);
        $method->setAccessible(true);
        $parameters = array_slice(func_get_args(), 2);

        return $method->invokeArgs($object, $parameters);
    }

    public function testControl()
    {
        $actions = $this->irc->getActions();
        $db = $this->irc->getDb();
        $actions->control('foo', 'bar');
        $result = $db->getControl();
        $this->assertSame('foo', $result['command']);
        $this->assertSame('bar', $result['data']);
    }

    public function testPrivmsg()
    {
        $actions = $this->irc->getActions();
        $db = $this->irc->getDb();
        $return = $actions->privmsg('#channel', 'text');
        $result = $db->getWrite();
        $this->assertSame('PRIVMSG #channel :text', $result['text']);
        $this->assertSame(['action' => 'privmsg', 'to' => '#channel', 'text' => 'text'], $return);
    }

    public function testMe()
    {
        $actions = $this->irc->getActions();
        $db = $this->irc->getDb();
        $return = $actions->me('#channel', 'text');
        $result = $db->getWrite();
        $this->assertSame('PRIVMSG #channel :' . "\x01" . 'ACTION text'. "\x01", $result['text']);
        $this->assertSame(['action' => 'me', 'to' => '#channel', 'text' => 'text'], $return);
    }
}
