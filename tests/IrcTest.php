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
    protected $irc;
    protected $config;
    protected $database;
    protected $db;

    protected function setUp()
    {
        date_default_timezone_set('Europe/Berlin');
        $this->config = parse_ini_file(Cerberus::getPath() . '/config.ini', true);
        $this->database = $this->config['testdb']['dbname'];
        $this->config['testdb']['dbname'] = null;
        $db = DriverManager::getConnection($this->config['testdb']);
        $sm = $db->getSchemaManager();
        $sm->dropAndCreateDatabase($this->database);
        $db->close();
        $this->config['testdb']['dbname'] = $this->database;
        $this->db = DriverManager::getConnection($this->config['testdb']);
        $this->db->query(file_get_contents(Cerberus::getPath() . '/cerberus.mysql.sql'));
        $this->config['db'] = $this->config['testdb'];
        $this->irc = new Irc($this->config);
        $this->irc->getConsole()->output(false);
        $this->irc->init();
        $this->invokeMethod($this->irc, 'loadPlugin', 'test');
    }

    protected function tearDown()
    {
        unset($this->irc);
        $sm = $this->db->getSchemaManager();
        $sm->tryMethod('dropDatabase', $this->database);
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
        $this->assertEquals($this->config['bot']['nick'], $row['nick']);
    }

    public function testCommandPrivmsg()
    {
        $input = ':foo!~bar@127.0.0.1 PRIVMSG #cerberbot :Humpty Dumpty sat on a wall, Humpty Dumpty had a great fall, All the King’s horses and all the King’s men, Couldn’t put Humpty together again.';
        $this->expectOutputString('nick:foo|host:~bar@127.0.0.1|channel:#cerberbot|text:Humpty Dumpty sat on a wall, Humpty Dumpty had a great fall, All the King’s horses and all the King’s men, Couldn’t put Humpty together again.');
        $this->invokeMethod($this->irc, 'command', $input);
    }

    public function testCommandNotice()
    {
        $input = ':foo!~bar@127.0.0.1 NOTICE Neo :follow the white rabbit';
        $this->expectOutputString('nick:foo|text:follow the white rabbit');
        $this->invokeMethod($this->irc, 'command', $input);
    }

    public function testCommandJoin()
    {
        $input = ':foo!~bar@127.0.0.1 JOIN #cerberbot';
        $this->expectOutputString('nick:foo|channel:#cerberbot');
        $this->invokeMethod($this->irc, 'command', $input);
    }

    public function testCommandPart()
    {
        $input = ':foo!~bar@127.0.0.1 PART #cerberbot';
        $this->expectOutputString('channel:#cerberbot|me:|nick:foo');
        $this->invokeMethod($this->irc, 'command', $input);
    }

    public function testCommandQuit()
    {
        $input = ':foo!~bar@127.0.0.1 QUIT :Remote host closed the connection';
        $this->expectOutputString('nick:foo');
        $this->invokeMethod($this->irc, 'command', $input);
    }
}
