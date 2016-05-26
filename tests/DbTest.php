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

class DbTest extends \PHPUnit_Framework_TestCase
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

    public function testWrite()
    {
        $db = $this->irc->getDb();
        $db->addWrite('JOIN #test');
        $result = $db->getWrite();
        $this->assertSame('JOIN #test', $result['text']);
    }

    public function testRemoveWrite()
    {
        $db = $this->irc->getDb();
        $id = $db->addWrite('PRIVMSG #test :test');
        $db->removeWrite($id);
        $this->assertFalse($db->getWrite());
    }

    public function testUserInChannel()
    {
        $db = $this->irc->getDb();
        $db->addUserToChannel('#test', 'test');
        $user = $db->getUserInChannel('#test', 'test');
        $this->assertSame('test', $user[0]['username']);
    }

    public function testUserInChannelBackslash()
    {
        $db = $this->irc->getDb();
        $db->addUserToChannel('#test', 'foo\\\\bar');
        $user = $db->getUserInChannel('#test', 'foo\\\\bar');
        $this->assertSame('foo\\\\bar', $user[0]['username']);
        $db->removeUser('foo\\\\bar');
        $user = $db->getUserInChannel('#test', 'foo\\\\bar');
        $this->assertEmpty($user);
    }

    public function testShutdownBot()
    {
        $db = $this->irc->getDb();
        $id = $db->getBotId();
        $this->assertCount(1, $db->getActiveBotList());
        $db->shutdownBot($id);
        $this->assertEmpty($db->getActiveBotList());
    }

    public function testCleanupBot()
    {
        $db = $this->irc->getDb();
        $db->addWrite('PRIVMSG #test :test');
        $db->cleanupBot();
        $this->assertFalse($db->getWrite());
    }
}
