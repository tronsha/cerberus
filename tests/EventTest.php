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

class EventTest extends \PHPUnit_Framework_TestCase
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

    public function testPrivmsg()
    {
        $input = ':foo!~bar@127.0.0.1 PRIVMSG #cerberbot :Humpty Dumpty sat on a wall, Humpty Dumpty had a great fall, All the King’s horses and all the King’s men, Couldn’t put Humpty together again.';
        $array = ['nick' => 'foo', 'host' => '~bar@127.0.0.1', 'channel' => '#cerberbot', 'text' => 'Humpty Dumpty sat on a wall, Humpty Dumpty had a great fall, All the King’s horses and all the King’s men, Couldn’t put Humpty together again.'];
        ksort($array);
        $this->expectOutputString(serialize($array));
        $this->invokeMethod($this->irc, 'command', $input);
    }

    public function testNotice()
    {
        $input = ':foo!~bar@127.0.0.1 NOTICE Neo :follow the white rabbit';
        $array = ['nick' => 'foo', 'text' => 'follow the white rabbit'];
        ksort($array);
        $this->expectOutputString(serialize($array));
        $this->invokeMethod($this->irc, 'command', $input);
    }

    public function testJoin()
    {
        $input = ':foo!~bar@127.0.0.1 JOIN #cerberbot';
        $array = ['nick' => 'foo', 'channel' => '#cerberbot'];
        ksort($array);
        $this->expectOutputString(serialize($array));
        $this->invokeMethod($this->irc, 'command', $input);
    }

    public function testJoinMe()
    {
        $this->irc->setNick('foo');
        $input = ':foo!~bar@127.0.0.1 JOIN #cerberbot';
        $array = ['nick' => 'foo', 'channel' => '#cerberbot'];
        ksort($array);
        $this->expectOutputString(serialize($array));
        $this->invokeMethod($this->irc, 'command', $input);
    }

    public function testPart()
    {
        $input = ':foo!~bar@127.0.0.1 PART #cerberbot';
        $array = ['channel' => '#cerberbot', 'nick' => 'foo', 'me' => false];
        ksort($array);
        $this->expectOutputString(serialize($array));
        $this->invokeMethod($this->irc, 'command', $input);
    }

    public function testPartMe()
    {
        $this->irc->setNick('foo');
        $input = ':foo!~bar@127.0.0.1 PART #cerberbot';
        $array = ['channel' => '#cerberbot', 'nick' => 'foo', 'me' => true];
        ksort($array);
        $this->expectOutputString(serialize($array));
        $this->invokeMethod($this->irc, 'command', $input);
    }

    public function testQuit()
    {
        $input = ':foo!~bar@127.0.0.1 QUIT :Remote host closed the connection';
        $array = ['nick' => 'foo'];
        ksort($array);
        $this->expectOutputString(serialize($array));
        $this->invokeMethod($this->irc, 'command', $input);
    }

    public function testTopic()
    {
        $input = ':foo!~bar@127.0.0.1 TOPIC #cerberbot :bar';
        $array = ['channel' => '#cerberbot', 'topic' => 'bar'];
        ksort($array);
        $this->expectOutputString(serialize($array));
        $this->invokeMethod($this->irc, 'command', $input);
    }

    public function testKick()
    {
        $input = ':foo!~bar@127.0.0.1 KICK #cerberbot Noob :goodbye';
        $array = ['channel' => '#cerberbot', 'me' => false, 'nick' => 'Noob', 'bouncer' => 'foo', 'comment' => 'goodbye'];
        ksort($array);
        $this->expectOutputString(serialize($array));
        $this->invokeMethod($this->irc, 'command', $input);
    }

    public function testKickMeAutorejoin()
    {
        $this->irc->getConfig()->setAutorejoin(true);
        $this->irc->setNick('Noob');
        $input = ':foo!~bar@127.0.0.1 KICK #cerberbot Noob :goodbye';
        $array = ['channel' => '#cerberbot', 'me' => true, 'nick' => 'Noob', 'bouncer' => 'foo', 'comment' => 'goodbye'];
        ksort($array);
        $this->expectOutputString(serialize($array));
        $this->invokeMethod($this->irc, 'command', $input);
        $result = $this->irc->getDb()->getWrite();
        $this->assertSame('JOIN #cerberbot', $result['text']);
    }

    public function testInvite()
    {
        $input = ':foo!~bar@127.0.0.1 INVITE Neo :#cerberbot';
        $array = ['channel' => '#cerberbot', 'nick' => 'Neo', 'user' => 'foo'];
        ksort($array);
        $this->expectOutputString(serialize($array));
        $this->invokeMethod($this->irc, 'command', $input);
    }

    public function testNick()
    {
        $input = ':old!~bar@127.0.0.1 NICK :new';
        $array = ['nick' => 'old', 'text' => 'new'];
        ksort($array);
        $this->expectOutputString(serialize($array));
        $this->invokeMethod($this->irc, 'command', $input);
    }

    public function testNickMe()
    {
        $this->irc->setNick('old');
        $input = ':old!~bar@127.0.0.1 NICK :new';
        $array = ['nick' => 'old', 'text' => 'new'];
        ksort($array);
        $this->expectOutputString(serialize($array));
        $this->invokeMethod($this->irc, 'command', $input);
        $this->assertSame('new', $this->irc->getNick());
    }

    public function testMode()
    {
        $input = ':foo!~bar@127.0.0.1 MODE #cerberbot +s';
        $array = ['channel' => '#cerberbot', 'mode' => '+s', 'param' => null, 'text' => null];
        ksort($array);
        $this->expectOutputString(serialize($array));
        $this->invokeMethod($this->irc, 'command', $input);
    }

    public function testModeTwo()
    {
        $input = ':Cerberus MODE Cerberus :+i';
        $array = ['channel' => 'Cerberus', 'mode' => null, 'param' => null, 'text' => '+i'];
        ksort($array);
        $this->expectOutputString(serialize($array));
        $this->invokeMethod($this->irc, 'command', $input);
    }

    public function testPrivmsgActionClientinfo()
    {
        $this->irc->getConfig()->setCtcp(true);
        $input = ':foo!~bar@127.0.0.1 PRIVMSG cerberus :' . "\x01" . 'CLIENTINFO' . "\x01";
        $this->invokeMethod($this->irc, 'command', $input);
        $result = $this->irc->getDb()->getWrite();
        $this->assertSame('NOTICE foo :' . "\x01" . 'CLIENTINFO PING VERSION TIME FINGER SOURCE CLIENTINFO' . "\x01", $result['text']);
    }

    public function testPrivmsgActionPing()
    {
        $this->irc->getConfig()->setCtcp(true);
        $input = ':foo!~bar@127.0.0.1 PRIVMSG cerberus :' . "\x01" . 'PING 1467554714.293876' . "\x01";
        $this->invokeMethod($this->irc, 'command', $input);
        $result = $this->irc->getDb()->getWrite();
        $this->assertSame('NOTICE foo :' . "\x01" . 'PING 1467554714.293876' . "\x01", $result['text']);
    }

    public function testPrivmsgActionTime()
    {
        $this->irc->getConfig()->setCtcp(true);
        $input = ':foo!~bar@127.0.0.1 PRIVMSG cerberus :' . "\x01" . 'TIME' . "\x01";
        $timeStart = time();
        $this->invokeMethod($this->irc, 'command', $input);
        $timeStop = time();
        $values = [];
        for ($time = $timeStart; $time <= $timeStop; $time++) {
            $values[] = 'NOTICE foo :' . "\x01" . 'TIME ' . date('D M d H:i:s Y T', $time) . "\x01";
        }
        $result = $this->irc->getDb()->getWrite();
        $this->assertContains($result['text'], $values);
    }

    public function testPrivmsgActionSource()
    {
        $this->irc->getConfig()->setCtcp(true);
        $input = ':foo!~bar@127.0.0.1 PRIVMSG cerberus :' . "\x01" . 'SOURCE' . "\x01";
        $this->invokeMethod($this->irc, 'command', $input);
        $result = $this->irc->getDb()->getWrite();
        $this->assertSame('NOTICE foo :' . "\x01" . 'SOURCE https://github.com/tronsha/cerberus' . "\x01", $result['text']);
    }
}
