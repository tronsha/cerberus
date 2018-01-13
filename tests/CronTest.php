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

class CronTest extends \PHPUnit_Framework_TestCase
{
    protected $cron;

    protected function setUp()
    {
        $this->cron = new Cron();
    }

    protected function tearDown()
    {
        unset($this->cron);
    }

    /**
     * @param boolean $expected
     * @param string $cronString
     * @param integer $minute
     * @param integer $hour
     * @param integer $day_of_month
     * @param integer $month
     * @param integer $day_of_week
     *
     * @dataProvider compareProvider
     */
    public function testCompare($expected, $cronString, $minute, $hour, $day_of_month, $month, $day_of_week)
    {
        $this->assertSame($expected, $this->cron->compare($cronString, $minute, $hour, $day_of_month, $month, $day_of_week));
    }

    public function compareProvider()
    {
        return [
            [true, '* * * * *', 15, 12, 1, 1, 0],
            [true, '15 * * * *', 15, 12, 1, 1, 0],
            [true, '* 12 * * *', 15, 12, 1, 1, 0],
            [true, '* * 1 * *', 15, 12, 1, 1, 0],
            [true, '* * * 1 *', 15, 12, 1, 1, 0],
            [true, '* * * * 0', 15, 12, 1, 1, 0],
            [true, '* * * * 7', 15, 12, 1, 1, 0],
            [true, '15 12 * * *', 15, 12, 1, 1, 0],
            [false, '30 * * * *', 15, 12, 1, 1, 0],
            [false, '* 13 * * *', 15, 12, 1, 1, 0],
            [false, '* * 2 * *', 15, 12, 1, 1, 0],
            [false, '* * * 2 *', 15, 12, 1, 1, 0],
            [false, '* * * * 1', 15, 12, 1, 1, 0],
            [false, '15 13 * * *', 15, 12, 1, 1, 0],
            [false, '30 12 * * *', 15, 12, 1, 1, 0],
            [true, '* * 1 * 1', 15, 12, 1, 1, 0],
            [true, '* * 2 * 0', 15, 12, 1, 1, 0],
            [false, '* * 2 * 1', 15, 12, 1, 1, 0],
            [false, '* * 1 2 1', 15, 12, 1, 1, 0],
            [false, '* * 2 2 0', 15, 12, 1, 1, 0],
            [true, '0-29 * * * *', 15, 12, 1, 1, 0],
            [false, '30-59 * * * *', 15, 12, 1, 1, 0],
            [true, '0,15,30,45 * * * *', 15, 12, 1, 1, 0],
            [false, '0,20,40 * * * *', 15, 12, 1, 1, 0],
            [true, '10-20,30-40 * * * *', 15, 12, 1, 1, 0],
            [false, '20-30,40-50 * * * *', 15, 12, 1, 1, 0],
            [true, '*/5 * * * *', 15, 12, 1, 1, 0],
            [false, '*/10 * * * *', 15, 12, 1, 1, 0],
            [true, '0-30/5 * * * *', 15, 12, 1, 1, 0],
            [false, '30-55/5 * * * *', 15, 12, 1, 1, 0],
            [true, '2-32/5 * * * *', 17, 12, 1, 1, 0],
            [false, '2-32/5 * * * *', 15, 12, 1, 1, 0],
            [true, '1-20/3 * * * *', 1, 12, 1, 1, 0],
            [true, '1-20/3 * * * *', 4, 12, 1, 1, 0],
            [false, '1-20/3 * * * *', 3, 12, 1, 1, 0],
            [true, '0 */6 * * *', 0, 0, 1, 1, 0],
            [true, '0 */6 * * *', 0, 6, 1, 1, 0],
            [true, '0 */6 * * *', 0, 12, 1, 1, 0],
            [true, '0 */6 * * *', 0, 18, 1, 1, 0],
            [false, '0 */6 * * *', 0, 14, 1, 1, 0],
            [true, '0 12 * * SUN', 0, 12, 1, 1, 0],
            [false, '0 12 * * SAT', 0, 12, 1, 1, 0],
            [true, '0 12 * JAN-JUN *', 0, 12, 1, 1, 0],
            [false, '0 12 * JUL-DEC *', 0, 12, 1, 1, 0]
        ];
    }

    public function testAdd()
    {
        $id = $this->cron->add('* * * * *', 'foo', 'bar');
        $this->assertSame(1, $id);
    }

    public function testRemove()
    {
        $id = $this->cron->add('* * * * *', 'foo', 'bar');
        $this->assertFalse($this->cron->remove($id + 1));
        $this->assertTrue($this->cron->remove($id));
        $this->assertFalse($this->cron->remove($id));
    }

    public function testException()
    {
        try {
            $this->assertFalse($this->cron->compare('* * * *', 0, 0, 0, 0, 0));
        } catch (\Exception $e) {
            $this->assertSame('a cron has an error', $e->getMessage());
        }
        try {
            $this->assertFalse($this->cron->compare('* * * * * *', 0, 0, 0, 0, 0));
        } catch (\Exception $e) {
            $this->assertSame('a cron has an error', $e->getMessage());
        }
    }

    public function testRun()
    {
        $this->expectOutputString('test');
        $this->cron->add('0 0 0 0 0', $this, 'output');
        $this->cron->run(0, 0, 0, 0, 0);
    }

    public function output()
    {
        echo 'test';
    }
}
