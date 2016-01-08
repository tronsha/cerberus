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

class CronTest extends \PHPUnit_Framework_TestCase
{
    protected $cron;

    protected function setUp()
    {
        $this->cron = new Cron();
    }

    public function testCompare()
    {
        $this->assertTrue($this->cron->compare('* * * * *', 15, 12, 1, 1, 0));

        $this->assertTrue($this->cron->compare('15 * * * *', 15, 12, 1, 1, 0));
        $this->assertTrue($this->cron->compare('* 12 * * *', 15, 12, 1, 1, 0));
        $this->assertTrue($this->cron->compare('* * 1 * *', 15, 12, 1, 1, 0));
        $this->assertTrue($this->cron->compare('* * * 1 *', 15, 12, 1, 1, 0));
        $this->assertTrue($this->cron->compare('* * * * 0', 15, 12, 1, 1, 0));
        $this->assertTrue($this->cron->compare('* * * * 7', 15, 12, 1, 1, 0));
        $this->assertTrue($this->cron->compare('15 12 * * *', 15, 12, 1, 1, 0));

        $this->assertFalse($this->cron->compare('30 * * * *', 15, 12, 1, 1, 0));
        $this->assertFalse($this->cron->compare('* 13 * * *', 15, 12, 1, 1, 0));
        $this->assertFalse($this->cron->compare('* * 2 * *', 15, 12, 1, 1, 0));
        $this->assertFalse($this->cron->compare('* * * 2 *', 15, 12, 1, 1, 0));
        $this->assertFalse($this->cron->compare('* * * * 1', 15, 12, 1, 1, 0));
        $this->assertFalse($this->cron->compare('15 13 * * *', 15, 12, 1, 1, 0));
        $this->assertFalse($this->cron->compare('30 12 * * *', 15, 12, 1, 1, 0));

        $this->assertTrue($this->cron->compare('* * 1 * 1', 15, 12, 1, 1, 0));
        $this->assertTrue($this->cron->compare('* * 2 * 0', 15, 12, 1, 1, 0));

        $this->assertFalse($this->cron->compare('* * 2 * 1', 15, 12, 1, 1, 0));
        $this->assertFalse($this->cron->compare('* * 1 2 1', 15, 12, 1, 1, 0));
        $this->assertFalse($this->cron->compare('* * 2 2 0', 15, 12, 1, 1, 0));

        $this->assertTrue($this->cron->compare('0-29 * * * *', 15, 12, 1, 1, 0));
        $this->assertFalse($this->cron->compare('30-59 * * * *', 15, 12, 1, 1, 0));

        $this->assertTrue($this->cron->compare('0,15,30,45 * * * *', 15, 12, 1, 1, 0));
        $this->assertFalse($this->cron->compare('0,20,40 * * * *', 15, 12, 1, 1, 0));

        $this->assertTrue($this->cron->compare('10-20,30-40 * * * *', 15, 12, 1, 1, 0));
        $this->assertFalse($this->cron->compare('20-30,40-50 * * * *', 15, 12, 1, 1, 0));

        $this->assertTrue($this->cron->compare('*/5 * * * *', 15, 12, 1, 1, 0));
        $this->assertFalse($this->cron->compare('*/10 * * * *', 15, 12, 1, 1, 0));

        $this->assertTrue($this->cron->compare('0-30/5 * * * *', 15, 12, 1, 1, 0));
        $this->assertFalse($this->cron->compare('30-55/5 * * * *', 15, 12, 1, 1, 0));

        $this->assertTrue($this->cron->compare('2-32/5 * * * *', 17, 12, 1, 1, 0));
        $this->assertFalse($this->cron->compare('2-32/5 * * * *', 15, 12, 1, 1, 0));

        $this->assertTrue($this->cron->compare('1-20/3 * * * *', 1, 12, 1, 1, 0));
        $this->assertTrue($this->cron->compare('1-20/3 * * * *', 4, 12, 1, 1, 0));
        $this->assertFalse($this->cron->compare('1-20/3 * * * *', 3, 12, 1, 1, 0));

        $this->assertTrue($this->cron->compare('0 */6 * * *', 0, 0, 1, 1, 0));
        $this->assertTrue($this->cron->compare('0 */6 * * *', 0, 6, 1, 1, 0));
        $this->assertTrue($this->cron->compare('0 */6 * * *', 0, 12, 1, 1, 0));
        $this->assertTrue($this->cron->compare('0 */6 * * *', 0, 18, 1, 1, 0));
        $this->assertFalse($this->cron->compare('0 */6 * * *', 0, 14, 1, 1, 0));

        $this->assertTrue($this->cron->compare('0 12 * * SUN', 0, 12, 1, 1, 0));
        $this->assertFalse($this->cron->compare('0 12 * * SAT', 0, 12, 1, 1, 0));

        $this->assertTrue($this->cron->compare('0 12 * JAN-JUN *', 0, 12, 1, 1, 0));
        $this->assertFalse($this->cron->compare('0 12 * JUL-DEC *', 0, 12, 1, 1, 0));
    }
}
