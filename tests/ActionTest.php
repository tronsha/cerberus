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
            if (false === empty($sqlCommand)) {
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
        if (null === $this->db) {
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
        $this->assertSame('PRIVMSG #channel :' . "\x01" . 'ACTION text' . "\x01", $result['text']);
        $this->assertSame(['action' => 'me', 'to' => '#channel', 'text' => 'text'], $return);
    }

    public function testNotice()
    {
        $actions = $this->irc->getActions();
        $db = $this->irc->getDb();
        $return = $actions->notice('#channel', 'text');
        $result = $db->getWrite();
        $this->assertSame('NOTICE #channel :text', $result['text']);
        $this->assertSame(['action' => 'notice', 'to' => '#channel', 'text' => 'text'], $return);
    }

    public function testQuit()
    {
        $actions = $this->irc->getActions();
        $db = $this->irc->getDb();
        $return = $actions->quit('text');
        $result = $db->getWrite();
        $this->assertSame('QUIT :text', $result['text']);
        $this->assertSame(['action' => 'quit', 'text' => 'text'], $return);
    }

    public function testMode()
    {
        $actions = $this->irc->getActions();
        $db = $this->irc->getDb();
        $return = $actions->mode('#channel');
        $result = $db->getWrite();
        $this->assertSame('MODE #channel', $result['text']);
        $this->assertSame(['action' => 'mode', 'text' => '#channel'], $return);
    }

    public function testJoin()
    {
        $actions = $this->irc->getActions();
        $db = $this->irc->getDb();
        $return = $actions->join('#channel');
        $result = $db->getWrite();
        $this->assertSame('JOIN #channel', $result['text']);
        $this->assertSame(['action' => 'join', 'channel' => ['#channel'], 'key' => []], $return);
        $db->removeWrite($result['id']);
        $return = $actions->join(['#channel1', '#channel2']);
        $result = $db->getWrite();
        $this->assertSame('JOIN #channel1,#channel2', $result['text']);
        $this->assertSame(['action' => 'join', 'channel' => ['#channel1', '#channel2'], 'key' => []], $return);
    }

    public function testJoinKey()
    {
        $actions = $this->irc->getActions();
        $db = $this->irc->getDb();
        $return = $actions->join('#channel', 'key');
        $result = $db->getWrite();
        $this->assertSame('JOIN #channel key', $result['text']);
        $this->assertSame(['action' => 'join', 'channel' => ['#channel'], 'key' => ['key']], $return);
        $db->removeWrite($result['id']);
        $return = $actions->join(['#channel1', '#channel2'], ['key1', 'key2']);
        $result = $db->getWrite();
        $this->assertSame('JOIN #channel1,#channel2 key1,key2', $result['text']);
        $this->assertSame(['action' => 'join', 'channel' => ['#channel1', '#channel2'], 'key' => ['key1', 'key2']], $return);
    }

    public function testPart()
    {
        $actions = $this->irc->getActions();
        $db = $this->irc->getDb();
        $return = $actions->part('#channel');
        $result = $db->getWrite();
        $this->assertSame('PART #channel', $result['text']);
        $this->assertSame(['action' => 'part', 'channel' => ['#channel']], $return);
    }

    public function testWhois()
    {
        $actions = $this->irc->getActions();
        $db = $this->irc->getDb();
        $return = $actions->whois('user');
        $result = $db->getWrite();
        $this->assertSame('WHOIS :user', $result['text']);
        $this->assertSame(['action' => 'whois', 'nick' => 'user'], $return);
    }

    public function testNick()
    {
        $actions = $this->irc->getActions();
        $db = $this->irc->getDb();
        $return = $actions->nick('user');
        $result = $db->getWrite();
        $this->assertSame('NICK :user', $result['text']);
        $this->assertSame(['action' => 'nick', 'nick' => 'user'], $return);
    }

    public function testTopic()
    {
        $actions = $this->irc->getActions();
        $db = $this->irc->getDb();
        $return = $actions->topic('#channel', 'topic');
        $result = $db->getWrite();
        $this->assertSame('TOPIC #channel :topic', $result['text']);
        $this->assertSame(['action' => 'topic', 'channel' => '#channel', 'topic' => 'topic'], $return);
    }

    public function testInvite()
    {
        $actions = $this->irc->getActions();
        $db = $this->irc->getDb();
        $return = $actions->invite('#channel', 'user');
        $result = $db->getWrite();
        $this->assertSame('INVITE user :#channel', $result['text']);
        $this->assertSame(['action' => 'invite', 'channel' => '#channel', 'nick' => 'user'], $return);
    }

    public function testOp()
    {
        $actions = $this->irc->getActions();
        $db = $this->irc->getDb();
        $return = $actions->op('#channel', 'user');
        $result = $db->getWrite();
        $this->assertSame('MODE #channel +o user', $result['text']);
        $this->assertSame(['action' => 'op', 'channel' => '#channel', 'nick' => 'user'], $return);
    }

    public function testOpMe()
    {
        $actions = $this->irc->getActions();
        $db = $this->irc->getDb();
        $return = $actions->op('#channel');
        $result = $db->getWrite();
        $this->assertSame('PRIVMSG chanserv :OP #channel', $result['text']);
        $this->assertSame(['action' => 'op', 'channel' => '#channel', 'nick' => null], $return);
    }

    public function testOpMeQuakenet()
    {
        self::$config['irc']['network'] = 'quakenet';
        $this->irc = new Irc(self::$config);
        $this->irc->isUnitTest(true);
        $this->irc->getConsole()->disableOutput();
        $this->irc->init();
        $this->invokeMethod($this->irc, 'loadPlugin', 'test');
        $actions = $this->irc->getActions();
        $db = $this->irc->getDb();
        $db->getServerData($this->irc->getServer());
        $return = $actions->op('#channel');
        $result = $db->getWrite();
        $this->assertSame('PRIVMSG Q :OP #channel', $result['text']);
        $this->assertSame(['action' => 'op', 'channel' => '#channel', 'nick' => null], $return);
    }

    public function testDeop()
    {
        $actions = $this->irc->getActions();
        $db = $this->irc->getDb();
        $return = $actions->deop('#channel', 'user');
        $result = $db->getWrite();
        $this->assertSame('MODE #channel -o user', $result['text']);
        $this->assertSame(['action' => 'deop', 'channel' => '#channel', 'nick' => 'user'], $return);
    }

    public function testKick()
    {
        $actions = $this->irc->getActions();
        $db = $this->irc->getDb();
        $return = $actions->kick('#channel', 'user');
        $result = $db->getWrite();
        $this->assertSame('KICK #channel user :', $result['text']);
        $this->assertSame(['action' => 'kick', 'channel' => '#channel', 'user' => 'user', 'comment' => null], $return);
    }

    public function testList()
    {
        $actions = $this->irc->getActions();
        $db = $this->irc->getDb();
        $return = $actions->channelList();
        $result = $db->getWrite();
        $this->assertSame('LIST', $result['text']);
        $this->assertSame(['action' => 'list'], $return);
    }
}
