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

class IrcTest extends \PHPUnit_Framework_TestCase
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
        $this->db->query(file_get_contents(Cerberus::getPath() . '/cerberus.mysql.sql'));
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

    public function testRandomNick()
    {
        $this->assertSame(6, strlen($this->irc->randomNick()));
    }

    public function testSysinfo()
    {
        $this->assertSame(
            '<info>**** Connection to server lost ****</info>',
            $this->irc->sysinfo('Connection to server lost')
        );
    }

    public function testError()
    {
        $this->assertSame(
            '<error>Some Error</error>',
            $this->irc->error('Some Error')
        );
    }

    public function testCreateBot()
    {
        $sql = 'SELECT * FROM bot WHERE id = 1';
        $stmt = $this->db->query($sql);
        $row = $stmt->fetch();
        $this->assertSame(self::$config['bot']['nick'], $row['nick']);
    }

    public function testTranslation()
    {
        $this->irc->setLanguage('en');
        $this->irc->setTranslations(['de' => ['hello' => 'hallo'], 'en' => ['hello' => 'hello']]);
        $this->irc->setTranslations(['de' => ['world' => 'welt'], 'en' => ['world' => 'world']]);
        $this->assertSame('unknown', $this->irc->__('unknown'));
        $this->assertSame('hello', $this->irc->__('hello'));
        $this->assertSame('hallo', $this->irc->__('hello', null, 'de'));
        $this->irc->setLanguage('de');
        $this->assertSame('hallo', $this->irc->__('hello'));
        $this->assertSame('welt', $this->irc->__('world'));
    }

    public function testCommandPrivmsg()
    {
        $input = ':foo!~bar@127.0.0.1 PRIVMSG #cerberbot :Humpty Dumpty sat on a wall, Humpty Dumpty had a great fall, All the King’s horses and all the King’s men, Couldn’t put Humpty together again.';
        $array = ['nick' => 'foo', 'host' => '~bar@127.0.0.1', 'channel' => '#cerberbot', 'text' => 'Humpty Dumpty sat on a wall, Humpty Dumpty had a great fall, All the King’s horses and all the King’s men, Couldn’t put Humpty together again.'];
        ksort($array);
        $this->expectOutputString(serialize($array));
        $this->invokeMethod($this->irc, 'command', $input);
    }

    public function testCommandNotice()
    {
        $input = ':foo!~bar@127.0.0.1 NOTICE Neo :follow the white rabbit';
        $array = ['nick' => 'foo', 'text' => 'follow the white rabbit'];
        ksort($array);
        $this->expectOutputString(serialize($array));
        $this->invokeMethod($this->irc, 'command', $input);
    }

    public function testCommandJoin()
    {
        $input = ':foo!~bar@127.0.0.1 JOIN #cerberbot';
        $array = ['nick' => 'foo', 'channel' => '#cerberbot'];
        ksort($array);
        $this->expectOutputString(serialize($array));
        $this->invokeMethod($this->irc, 'command', $input);
    }

    public function testCommandPart()
    {
        $input = ':foo!~bar@127.0.0.1 PART #cerberbot';
        $array = ['channel' => '#cerberbot', 'nick' => 'foo', 'me' => false];
        ksort($array);
        $this->expectOutputString(serialize($array));
        $this->invokeMethod($this->irc, 'command', $input);
    }

    public function testCommandQuit()
    {
        $input = ':foo!~bar@127.0.0.1 QUIT :Remote host closed the connection';
        $array = ['nick' => 'foo'];
        ksort($array);
        $this->expectOutputString(serialize($array));
        $this->invokeMethod($this->irc, 'command', $input);
    }

    public function testCommandTopic()
    {
        $input = ':foo!~bar@127.0.0.1 TOPIC #cerberbot :bar';
        $array = ['channel' => '#cerberbot', 'topic' => 'bar'];
        ksort($array);
        $this->expectOutputString(serialize($array));
        $this->invokeMethod($this->irc, 'command', $input);
    }

    public function testCommandKick()
    {
        $input = ':foo!~bar@127.0.0.1 KICK #cerberbot Noob :goodbye';
        $array = ['channel' => '#cerberbot', 'me' => false, 'nick' => 'Noob', 'bouncer' => 'foo', 'comment' => 'goodbye'];
        ksort($array);
        $this->expectOutputString(serialize($array));
        $this->invokeMethod($this->irc, 'command', $input);
    }

    public function testCommandInvite()
    {
        $input = ':foo!~bar@127.0.0.1 INVITE Neo :#cerberbot';
        $array = ['channel' => '#cerberbot', 'nick' => 'Neo', 'user' => 'foo'];
        ksort($array);
        $this->expectOutputString(serialize($array));
        $this->invokeMethod($this->irc, 'command', $input);
    }

    public function testCommandNick()
    {
        $input = ':old!~bar@127.0.0.1 NICK :new';
        $array = ['nick' => 'old', 'text' => 'new'];
        ksort($array);
        $this->expectOutputString(serialize($array));
        $this->invokeMethod($this->irc, 'command', $input);
    }
}
