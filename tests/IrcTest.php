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

	protected function setUp()
	{
        date_default_timezone_set('Europe/Berlin');
        $config = parse_ini_file(Cerberus::getPath() . '/config.ini', true);
        $name = $config['testdb']['dbname'];
        $config['testdb']['dbname'] = null;
        $db = DriverManager::getConnection($config['testdb']);
        $sm = $db->getSchemaManager();
        $sm->dropAndCreateDatabase($name);
        $db->close();
        $config['testdb']['dbname'] = $name;
        $db = DriverManager::getConnection($config['testdb']);
        $db->query(file_get_contents(Cerberus::getPath() . '/cerberus.mysql.sql'));
        $db->close();
        $this->irc = new Irc($config);
		$this->irc->getConsole()->output(false);
 		$this->irc->init();
	}

	protected function tearDown()
	{
		unset($this->irc);
        $config = parse_ini_file(Cerberus::getPath() . '/config.ini', true);
        $name = $config['testdb']['dbname'];
        $config['testdb']['dbname'] = null;
        $db = DriverManager::getConnection($config['testdb']);
        $sm = $db->getSchemaManager();
        $sm->tryMethod('dropDatabase', $name);
        $db->close();
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
        $this->assertEquals('<info>**** Connection to server lost ****</info>', $this->irc->sysinfo('Connection to server lost'));
    }
}
