<?php

/*   Cerberus IRCBot
 *   Copyright (C) 2008 - 2016 Stefan Hüsges
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

class MircryptionTest extends \PHPUnit_Framework_TestCase
{
    protected function setUp()
    {
        if (extension_loaded('mcrypt') === false) {
            $this->markTestSkipped('The mcrypt extension is not available.');
        }
    }

    public function testMircryption()
    {
        $text = 'foo';
        $key = 'bar';
        $encoded = Mircryption::encode($text, $key);
        $decoded = Mircryption::decode($encoded, $key);
        $this->assertEquals($text, $decoded);
    }

    public function testCcryption()
    {
        $text = 'foo';
        $key = 'bar';
        $encoded = Ccryption::encode($text, $key);
        $decoded = Ccryption::decode($encoded, $key);
        $this->assertEquals(hash('crc32b', $text), substr(base64_decode($encoded), -8));
        $this->assertEquals($text, $decoded);
    }
}