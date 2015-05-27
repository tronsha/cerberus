<?php

/*   Cerberus IRCBot
 *   Copyright (C) 2008 - 2015 Stefan Hüsges
 *
 *   This program is free software; you can redistribute it and/or modify it
 *   under the terms of the GNU General Public License as published by the Free
 *   Software Foundation; either version 3 of the License, or (at your option)
 *   any later version.
 *
 *   This program is distributed in the hope that it will be useful, but
 *   WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY
 *   or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License
 *   for more details.
 *
 *   You should have received a copy of the GNU General Public License along
 *   with this program; if not, see <http://www.gnu.org/licenses/>.
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
        date_default_timezone_set('Europe/Berlin');
        self::$config = parse_ini_file(Cerberus::getPath() . '/config.ini', true);
        self::$database = self::$config['testdb']['dbname'];
    }

    protected function setUp()
    {
        self::$config['testdb']['dbname'] = null;
        $db = DriverManager::getConnection(self::$config['testdb']);
        $this->checkDatabase($db);
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
        $this->checkDatabase($this->db);
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

    protected function checkDatabase($db)
    {
        if ($db === null || $db->isConnected() === false) {
            $this->fail('No connection to database...');
        }
    }

    public function testRandomNick()
    {
        $this->assertEquals(6, strlen($this->irc->randomNick()));
    }

    public function testSysinfo()
    {
        $this->assertEquals(
            '<info>**** Connection to server lost ****</info>',
            $this->irc->sysinfo('Connection to server lost')
        );
    }

    public function testCreateBot()
    {
        $sql = 'SELECT * FROM bot WHERE id = 1';
        $stmt = $this->db->query($sql);
        $row = $stmt->fetch();
        $this->assertEquals(self::$config['bot']['nick'], $row['nick']);
    }

    public function testTranslation()
    {
        $this->irc->setLang('en');
        $this->irc->setTranslations(array('de' => array('hello' => 'hallo'), 'en' => array('hello' => 'hello')));
        $this->irc->setTranslations(array('de' => array('world' => 'welt'), 'en' => array('world' => 'world')));
        $this->assertEquals('unknown', $this->irc->__('unknown'));
        $this->assertEquals('hello', $this->irc->__('hello'));
        $this->assertEquals('hallo', $this->irc->__('hello', 'de'));
        $this->irc->setLang('de');
        $this->assertEquals('hallo', $this->irc->__('hello'));
        $this->assertEquals('welt', $this->irc->__('world'));
    }

    public function testCommandPrivmsg()
    {
        $input = ':foo!~bar@127.0.0.1 PRIVMSG #cerberbot :Humpty Dumpty sat on a wall, Humpty Dumpty had a great fall, All the King’s horses and all the King’s men, Couldn’t put Humpty together again.';
        $array = array('nick' => 'foo', 'host' => '~bar@127.0.0.1', 'channel' => '#cerberbot', 'text' => 'Humpty Dumpty sat on a wall, Humpty Dumpty had a great fall, All the King’s horses and all the King’s men, Couldn’t put Humpty together again.');
        ksort($array);
        $this->expectOutputString(serialize($array));
        $this->invokeMethod($this->irc, 'command', $input);
    }

    public function testCommandNotice()
    {
        $input = ':foo!~bar@127.0.0.1 NOTICE Neo :follow the white rabbit';
        $array = array('nick' => 'foo', 'text' => 'follow the white rabbit');
        ksort($array);
        $this->expectOutputString(serialize($array));
        $this->invokeMethod($this->irc, 'command', $input);
    }

    public function testCommandJoin()
    {
        $input = ':foo!~bar@127.0.0.1 JOIN #cerberbot';
        $array = array('nick' => 'foo', 'channel' => '#cerberbot');
        ksort($array);
        $this->expectOutputString(serialize($array));
        $this->invokeMethod($this->irc, 'command', $input);
    }

    public function testCommandPart()
    {
        $input = ':foo!~bar@127.0.0.1 PART #cerberbot';
        $array = array('channel' => '#cerberbot', 'nick' => 'foo', 'me' => false);
        ksort($array);
        $this->expectOutputString(serialize($array));
        $this->invokeMethod($this->irc, 'command', $input);
    }

    public function testCommandQuit()
    {
        $input = ':foo!~bar@127.0.0.1 QUIT :Remote host closed the connection';
        $array = array('nick' => 'foo');
        ksort($array);
        $this->expectOutputString(serialize($array));
        $this->invokeMethod($this->irc, 'command', $input);
    }
}
