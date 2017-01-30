<?php

/*
 * Cerberus IRCBot
 * Copyright (C) 2008 - 2017 Stefan Hüsges
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

use Cerberus\Crypt\Ccryption;
use Cerberus\Crypt\Mircryption;

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
        $this->assertSame($text, $decoded);
        $this->assertSame('test', Mircryption::decode('*W8yZidGQfym4MCpf/aG2bA==', '123456'));
        $this->assertSame('test', Mircryption::decode('*QFVVPU8S0JsPxi3yqpM4Tg==', '1234567890123456'));
        $this->assertSame('Lorem ipsum dolor sit amet, consetetur sadipscing elitr, sed diam nonumy eirmod tempor invidunt ut labore et dolore magna aliquyam erat, sed diam voluptua.', Mircryption::decode('*/8pfMC8Y7d2I/5GqPEodgOTZDuGXpiX+lbUgELDQ1O3aztX3o3lerNXxTUJMRSIbVpYMoXERcgJFALzk4hNg0EXU4itsCr3FKIzInWM0ct87RWqxQCFBop9SDUnU2z9hZKIROpDUM31NbYVqr0kWUcnqDhPmQQKv+nOL1x+GW/i2i16/iTji4iFFDp2mexS3x1yyO04hNUgpyModfVapw94njUCLUrZH', '1234567890123456'));
    }

    public function testCcryption()
    {
        $text = 'foo';
        $key = 'bar';
        $encoded = Ccryption::encode($text, $key);
        $decoded = Ccryption::decode($encoded, $key);
        $this->assertSame(hash('crc32b', $text), substr(base64_decode($encoded, true), -8));
        $this->assertSame($text, $decoded);
        $this->assertSame('test', Ccryption::decode('pDD+su6N5kTljhLbaRSQf4hQQ8mCdOfEZDg3ZjdlMGM=', '123456'));
        $this->assertSame('Lorem ipsum dolor sit amet, consetetur sadipscing elitr, sed diam nonumy eirmod tempor invidunt ut labore et dolore magna aliquyam erat, sed diam voluptua.', Ccryption::decode('sf9CYtWGEDw+u6Xs2sRzoVTthAm+byYKXrZiOEKEuwf62+rgH4ICOyngliQHLtnwRZCnXH3FyvXHptIUT5OZdCrZ7yfynBAsGvKniBQGW/Mr2P/Zbomo9zBb8Mztfk5OMqNlb++szT55Lf9WZiPIRSTUWkmOkk0XTgHUV2qJz4NlZmVkZWU4Yw==', '123456'));
    }

    public function testCryptMircryption()
    {
        $crypt = new Crypt;
        $text = 'foo';
        $key = 'bar';
        $encoded = $crypt->encode('mircryption', $text, $key);
        $decoded = $crypt->decode('mircryption', $encoded, $key);
        $this->assertSame($text, $decoded);
    }

    public function testCryptCcryption()
    {
        $crypt = new Crypt;
        $text = 'foo';
        $key = 'bar';
        $encoded = $crypt->encode('ccryption', $text, $key);
        $decoded = $crypt->decode('ccryption', $encoded, $key);
        $this->assertSame($text, $decoded);
    }
}
