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
        $this->irc->getConsole()->setOutputPrint(false);
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

    public function testSetNick()
    {
        $this->assertSame('foo', $this->irc->setNick('foo'));
        $this->assertSame(6, strlen($this->irc->setNick()));
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

    public function testGetActiveBotList()
    {
        $list = $this->irc->getDb()->getActiveBotList();
        $this->assertSame(self::$config['bot']['nick'], $list[0]['nick']);
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

    public function testLoadPlugin()
    {
        $this->assertFalse($this->irc->loadPlugin('join'));
        $this->assertFalse($this->irc->loadPlugin('foo'));
    }

    public function testInChannel()
    {
        $this->assertNull($this->irc->inChannel('', ''));
        $this->assertFalse($this->irc->inChannel('#cerberbot'));
        $this->irc->getDb()->addChannel('#cerberbot');
        $this->assertTrue($this->irc->inChannel('#cerberbot'));
        $this->assertFalse($this->irc->inChannel('#cerberbot', 'foo'));
        $this->irc->getDb()->addUserToChannel('#cerberbot', 'foo');
        $this->assertTrue($this->irc->inChannel('#cerberbot', 'foo'));
    }

    public function testPluginEvent()
    {
        $this->expectOutputString('ontest');
        $this->irc->addPluginEvent('onTest', $this);
        $this->irc->runPluginEvent('onTest', []);
    }

    public function testRemovePluginEvent()
    {
        $this->expectOutputString('');
        $this->irc->addPluginEvent('onTest', $this);
        $this->assertSame(0, $this->irc->removePluginEvent('onTest', new \stdClass()));
        $this->assertSame(1, $this->irc->removePluginEvent('onTest', $this));
        $this->assertSame(0, $this->irc->removePluginEvent('onTest', $this));
        $this->irc->runPluginEvent('onTest', []);
    }

    public function testPluginEventClassHasNotTheMethod()
    {
        $this->expectOutputString(serialize([]));
        $this->irc->addPluginEvent('onNotice', $this);
        try {
            $this->irc->runPluginEvent('onNotice', []);
        } catch (\Exception $e) {
            $this->assertSame('The Class Cerberus\IrcTest has not the method onNotice.', $e->getMessage());
        }
    }

    public function testPluginEventNotExists()
    {
        try {
            $this->irc->addPluginEvent('foo', $this);
        } catch (\Exception $e) {
            $this->assertSame('The event foo not exists.', $e->getMessage());
        }
    }

    public function testCron()
    {
        $this->expectOutputString('test');
        $cronId = $this->irc->addCron('* * * * *', $this, 'output');
        $this->irc->runCron(0, 12, 1, 1, 1);
        $this->assertTrue($this->irc->removeCron($cronId));
        $this->assertFalse($this->irc->removeCron($cronId));
        $this->irc->runCron(0, 12, 1, 1, 1);
    }

    public function onTest()
    {
        echo 'ontest';
    }

    public function output()
    {
        echo 'test';
    }
}
