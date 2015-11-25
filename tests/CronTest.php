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

class CronTest extends \PHPUnit_Framework_TestCase
{
    protected $cron;

    protected function setUp()
    {
        $this->cron = new Cron();
    }

    public function testCompareCron()
    {
        $this->assertTrue($this->cron->compareCron('* * * * *', 15, 12, 1, 1, 0));

        $this->assertTrue($this->cron->compareCron('15 * * * *', 15, 12, 1, 1, 0));
        $this->assertTrue($this->cron->compareCron('* 12 * * *', 15, 12, 1, 1, 0));
        $this->assertTrue($this->cron->compareCron('* * 1 * *', 15, 12, 1, 1, 0));
        $this->assertTrue($this->cron->compareCron('* * * 1 *', 15, 12, 1, 1, 0));
        $this->assertTrue($this->cron->compareCron('* * * * 0', 15, 12, 1, 1, 0));
        $this->assertTrue($this->cron->compareCron('* * * * 7', 15, 12, 1, 1, 0));
        $this->assertTrue($this->cron->compareCron('15 12 * * *', 15, 12, 1, 1, 0));

        $this->assertFalse($this->cron->compareCron('30 * * * *', 15, 12, 1, 1, 0));
        $this->assertFalse($this->cron->compareCron('* 13 * * *', 15, 12, 1, 1, 0));
        $this->assertFalse($this->cron->compareCron('* * 2 * *', 15, 12, 1, 1, 0));
        $this->assertFalse($this->cron->compareCron('* * * 2 *', 15, 12, 1, 1, 0));
        $this->assertFalse($this->cron->compareCron('* * * * 1', 15, 12, 1, 1, 0));
        $this->assertFalse($this->cron->compareCron('15 13 * * *', 15, 12, 1, 1, 0));
        $this->assertFalse($this->cron->compareCron('30 12 * * *', 15, 12, 1, 1, 0));

        $this->assertTrue($this->cron->compareCron('* * 1 * 1', 15, 12, 1, 1, 0));
        $this->assertTrue($this->cron->compareCron('* * 2 * 0', 15, 12, 1, 1, 0));

        $this->assertFalse($this->cron->compareCron('* * 2 * 1', 15, 12, 1, 1, 0));
        $this->assertFalse($this->cron->compareCron('* * 1 2 1', 15, 12, 1, 1, 0));
        $this->assertFalse($this->cron->compareCron('* * 2 2 0', 15, 12, 1, 1, 0));

        $this->assertTrue($this->cron->compareCron('0-29 * * * *', 15, 12, 1, 1, 0));
        $this->assertFalse($this->cron->compareCron('30-59 * * * *', 15, 12, 1, 1, 0));

        $this->assertTrue($this->cron->compareCron('0,15,30,45 * * * *', 15, 12, 1, 1, 0));
        $this->assertFalse($this->cron->compareCron('0,20,40 * * * *', 15, 12, 1, 1, 0));

        $this->assertTrue($this->cron->compareCron('10-20,30-40 * * * *', 15, 12, 1, 1, 0));
        $this->assertFalse($this->cron->compareCron('20-30,40-50 * * * *', 15, 12, 1, 1, 0));

//        $this->assertTrue($this->cron->compareCron('*/5 * * * *', 15, 12, 1, 1, 0));
//        $this->assertFalse($this->cron->compareCron('*/10 * * * *', 15, 12, 1, 1, 0));

//        $this->assertTrue($this->cron->compareCron('0-30/5 * * * *', 15, 12, 1, 1, 0));
//        $this->assertFalse($this->cron->compareCron('30-55/5 * * * *', 15, 12, 1, 1, 0));
//        $this->assertFalse($this->cron->compareCron('1-20/5 * * * *', 15, 12, 1, 1, 0));
    }
}
