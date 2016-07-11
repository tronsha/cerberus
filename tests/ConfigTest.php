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
}
