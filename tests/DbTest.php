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

namespace Cerberus;

use DateTime;
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
        $this->irc->isUnitTest(true);
        $this->irc->getConsole()->disableOutput();
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

    public function testPing()
    {
        $db = $this->irc->getDb();
        $this->assertTrue($db->ping());
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

    public function testRemoveControl()
    {
        $db = $this->irc->getDb();
        $id = $db->addControl('foo', 'bar');
        $db->removeControl($id);
        $this->assertFalse($db->getControl());
    }

    public function testAddStatus()
    {
        $db = $this->irc->getDb();
        $db->addStatus('foo', 'foo', []);
        $db->addStatus('bar', 'bar', []);
        $db->addStatus('baz', 'baz', []);
        $status = $db->getStatus();
        $this->assertSame('foo', $status['text']);
        $status = $db->getStatus();
        $this->assertSame('bar', $status['text']);
        $status = $db->getStatus();
        $this->assertSame('baz', $status['text']);
        $this->assertNull($db->getStatus());
    }

    public function testGetStatus()
    {
        $db = $this->irc->getDb();
        $db->addStatus('foo', 'foo', []);
        $db->addStatus('bar', 'bar', []);
        $db->addStatus('baz', 'baz', []);
        $status = $db->getStatus('bar');
        $this->assertSame('bar', $status['text']);
        $this->assertNull($db->getStatus('bar'));
        $status = $db->getStatus(['foo', 'baz']);
        $this->assertSame('baz', $status['text']);
        $status = $db->getStatus(['foo', 'baz']);
        $this->assertSame('foo', $status['text']);
        $this->assertNull($db->getStatus(['foo', 'baz']));
    }

    public function testRemoveStatus()
    {
        $db = $this->irc->getDb();
        $id = $db->addStatus('foo', 'foo', []);
        $db->removeStatus($id);
        $id = $db->addStatus('bar', 'bar', []);
        $db->removeStatus($id);
        $db->addStatus('baz', 'baz', []);
        $status = $db->getStatus();
        $this->assertSame('baz', $status['text']);
        $this->assertNull($db->getStatus());
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

    public function testChannelTopic()
    {
        $db = $this->irc->getDb();
        $db->addChannel('#foo');
        $db->setChannelTopic('#foo', 'foo');
        $this->assertSame('foo', $db->getChannelTopic('#foo'));
        $db->addChannelToChannellist('', '#bar', 0, 'bar');
        $this->assertSame('bar', $db->getChannelTopic('#bar'));
        $this->assertSame('', $db->getChannelTopic('#baz'));
    }

    public function testChannellist()
    {
        $db = $this->irc->getDb();
        $db->addChannelToChannellist('', '#foo', 42, 'topic1');
        $db->addChannelToChannellist('', '#bar', 23, 'topic2');
        $db->addChannelToChannellist('', '#baz', 1337, 'topic3');
        $this->assertSame([
            ['channel' => '#baz', 'topic' => 'topic3', 'usercount' => 1337],
            ['channel' => '#foo', 'topic' => 'topic1', 'usercount' => 42],
            ['channel' => '#bar', 'topic' => 'topic2', 'usercount' => 23]
        ], $db->getChannellist());
    }

    public function testSetPing()
    {
        $db = $this->irc->getDb();
        $time1 = (new DateTime())->format('Y-m-d H:i:s');
        $db->setPing();
        $time2 = (new DateTime())->format('Y-m-d H:i:s');
        $sql = "SELECT count(*) AS x FROM bot WHERE ping >= '" . $time1 . "' AND ping <= '" . $time2 . "';";
        $conn = $db->getConnection();
        $result= $conn->query($sql)->fetch();
        $this->assertSame(1, intval($result['x']));
    }
}
