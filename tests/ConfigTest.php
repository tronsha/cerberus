<?php

/*
 * Cerberus IRCBot
 * Copyright (C) 2008 - 2018 Stefan Hüsges
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

class ConfigTest extends \PHPUnit_Framework_TestCase
{
    protected $config;

    protected function setUp()
    {
        $this->config = new Config(null);
    }

    protected function tearDown()
    {
        unset($this->config);
    }

    public function testConstruct()
    {
        $config = [];
        $config['info']['version'] = '6000';
        $config['info']['name'] = 'Cerberus';
        $config['info']['homepage'] = 'http://www.example.org';
        $config['bot']['channel'] = 'cerberbot';
        $config['bot']['autorejoin'] = '0';
        $config['bot']['ctcp'] = '1';
        $config['log']['directory'] = __DIR__;
        $config['log']['error'] = '1';
        $config['log']['socket'] = '0';
        $config['log']['sql'] = '0';
        $config['log']['dailylogfile'] = '1';
        $config['plugins']['autoload'] = 'join,part,quit';
        $config['frontend']['url'] = 'http://127.0.0.1/chat/';
        $config['frontend']['password'] = 'password';
        $config['bot']['lang'] = 'de';
        $this->config = new Config($config);
        $this->assertSame('6000', $this->config->getVersion('bot'));
        $this->assertSame('Cerberus', $this->config->getName());
        $this->assertSame('http://www.example.org', $this->config->getHomepage());
        $this->assertSame('#cerberbot', $this->config->getChannel());
        $this->assertFalse($this->config->getAutorejoin());
        $this->assertTrue($this->config->getCtcp());
        $this->assertSame(realpath(__DIR__), $this->config->getLogfiledirectory());
        $this->assertTrue($this->config->getLogfile('error'));
        $this->assertFalse($this->config->getLogfile('socket'));
        $this->assertFalse($this->config->getLogfile('sql'));
        $this->assertTrue($this->config->getDailylogfile());
        $this->assertSame(['join', 'part', 'quit'], $this->config->getPluginsAutoload());
        $this->assertSame('http://127.0.0.1/chat/', $this->config->getFrontendUrl());
        $this->assertSame('password', $this->config->getFrontendPassword());
        $this->assertSame('de', $this->config->getLanguage());
    }

    public function testVersion()
    {
        $this->config->setVersion('bot', 'Cerberus 6000');
        $this->config->setVersion('os', 'Linux');
        $this->config->setVersion('php', 'PHP 7.0.0');
        $this->config->setVersion('sql', 'MySQL 5.5');
        $this->assertSame('Cerberus 6000', $this->config->getVersion('bot'));
        $this->assertSame('Linux', $this->config->getVersion('os'));
        $this->assertSame('PHP 7.0.0', $this->config->getVersion('php'));
        $this->assertSame('MySQL 5.5', $this->config->getVersion('sql'));
        $this->assertSame(['bot' => 'Cerberus 6000', 'os' => 'Linux', 'php' => 'PHP 7.0.0', 'sql' => 'MySQL 5.5'], $this->config->getVersion());
    }

    public function testName()
    {
        $this->config->setName('foo');
        $this->assertSame('foo', $this->config->getName());
    }

    public function testHomepage()
    {
        $this->config->setHomepage('http://www.example.org');
        $this->assertSame('http://www.example.org', $this->config->getHomepage());
    }

    public function testChannel()
    {
        $this->config->setChannel('#foo');
        $this->assertSame('#foo', $this->config->getChannel());
    }

    public function testAutorejoin()
    {
        $this->config->setAutorejoin(true);
        $this->assertTrue($this->config->getAutorejoin());
        $this->config->setAutorejoin(false);
        $this->assertFalse($this->config->getAutorejoin());
        $this->config->setAutorejoin(1);
        $this->assertTrue($this->config->getAutorejoin());
        $this->config->setAutorejoin(0);
        $this->assertFalse($this->config->getAutorejoin());
    }

    public function testCtcp()
    {
        $this->config->setCtcp(true);
        $this->assertTrue($this->config->getCtcp());
        $this->config->setCtcp(false);
        $this->assertFalse($this->config->getCtcp());
        $this->config->setCtcp(1);
        $this->assertTrue($this->config->getCtcp());
        $this->config->setCtcp(0);
        $this->assertFalse($this->config->getCtcp());
    }

    public function testFrontendUrl()
    {
        $this->config->setFrontendUrl('http://127.0.0.1/cerberus/');
        $this->assertSame('http://127.0.0.1/cerberus/', $this->config->getFrontendUrl());
    }

    public function testFrontendPassword()
    {
        $this->config->setFrontendPassword('password');
        $this->assertSame('password', $this->config->getFrontendPassword());
    }

    public function testPluginsAutoload()
    {
        $this->config->setPluginsAutoload('foo,bar');
        $this->assertSame(['foo', 'bar'], $this->config->getPluginsAutoload());
        $this->config->setPluginsAutoload(['foo', 'baz']);
        $this->assertSame(['foo', 'baz'], $this->config->getPluginsAutoload());
    }

    public function testLanguage()
    {
        $this->config->setLanguage('de');
        $this->assertSame('de', $this->config->getLanguage());
    }

    public function testLogfile()
    {
        $this->config->setLogfile('error', true);
        $this->assertTrue($this->config->getLogfile('error'));
        $this->config->setLogfile('error', false);
        $this->assertFalse($this->config->getLogfile('error'));
    }

    public function testLogfiledirectory()
    {
        $this->config->setLogfiledirectory(__DIR__);
        $this->assertSame(realpath(__DIR__), $this->config->getLogfiledirectory());
    }

    public function testDailylogfile()
    {
        $this->config->setDailylogfile(true);
        $this->assertTrue($this->config->getDailylogfile());
        $this->config->setDailylogfile(false);
        $this->assertFalse($this->config->getDailylogfile());
        $this->config->setDailylogfile(1);
        $this->assertTrue($this->config->getDailylogfile());
        $this->config->setDailylogfile(0);
        $this->assertFalse($this->config->getDailylogfile());
    }
}
