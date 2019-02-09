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
     * @param \DateTime $time
     *
     * @dataProvider compareProvider
     */
    public function testCompare($expected, $cronString, $time)
    {
        $this->assertSame($expected, $this->cron->compare($cronString, $time));
    }

    public function compareProvider()
    {
        return [
            [true, '* * * * *', new \DateTime('2015-10-21 16:29:00')],
            [true, '29 * * * *', new \DateTime('2015-10-21 16:29:00')],
            [true, '* 16 * * *', new \DateTime('2015-10-21 16:29:00')],
            [true, '29 16 * * *', new \DateTime('2015-10-21 16:29:00')],
            [true, '* * 21 * *', new \DateTime('2015-10-21 16:29:00')],
            [true, '29 * 21 * *', new \DateTime('2015-10-21 16:29:00')],
            [true, '* 16 21 * *', new \DateTime('2015-10-21 16:29:00')],
            [true, '29 16 21 * *', new \DateTime('2015-10-21 16:29:00')],
            [true, '* * * 10 *', new \DateTime('2015-10-21 16:29:00')],
            [true, '29 * * 10 *', new \DateTime('2015-10-21 16:29:00')],
            [true, '* 16 * 10 *', new \DateTime('2015-10-21 16:29:00')],
            [true, '29 16 * 10 *', new \DateTime('2015-10-21 16:29:00')],
            [true, '* * 21 10 *', new \DateTime('2015-10-21 16:29:00')],
            [true, '29 * 21 10 *', new \DateTime('2015-10-21 16:29:00')],
            [true, '* 16 21 10 *', new \DateTime('2015-10-21 16:29:00')],
            [true, '29 16 21 10 *', new \DateTime('2015-10-21 16:29:00')],
            [true, '* * * * 3', new \DateTime('2015-10-21 16:29:00')],
            [true, '29 * * * 3', new \DateTime('2015-10-21 16:29:00')],
            [true, '* 16 * * 3', new \DateTime('2015-10-21 16:29:00')],
            [true, '29 16 * * 3', new \DateTime('2015-10-21 16:29:00')],
            [true, '* * 21 * 3', new \DateTime('2015-10-21 16:29:00')],
            [true, '29 * 21 * 3', new \DateTime('2015-10-21 16:29:00')],
            [true, '* 16 21 * 3', new \DateTime('2015-10-21 16:29:00')],
            [true, '29 16 21 * 3', new \DateTime('2015-10-21 16:29:00')],
            [true, '* * * 10 3', new \DateTime('2015-10-21 16:29:00')],
            [true, '29 * * 10 3', new \DateTime('2015-10-21 16:29:00')],
            [true, '* 16 * 10 3', new \DateTime('2015-10-21 16:29:00')],
            [true, '29 16 * 10 3', new \DateTime('2015-10-21 16:29:00')],
            [true, '* * 21 10 3', new \DateTime('2015-10-21 16:29:00')],
            [true, '29 * 21 10 3', new \DateTime('2015-10-21 16:29:00')],
            [true, '* 16 21 10 3', new \DateTime('2015-10-21 16:29:00')],
            [true, '29 16 21 10 3', new \DateTime('2015-10-21 16:29:00')],
            [true, '* * * * 0', new \DateTime('1985-10-27 12:00:00')],
            [true, '* * * * 7', new \DateTime('1985-10-27 12:00:00')],
            [false, '30 * * * *', new \DateTime('2015-10-21 16:29:00')],
            [false, '* 13 * * *', new \DateTime('2015-10-21 16:29:00')],
            [false, '30 16 * * *', new \DateTime('2015-10-21 16:29:00')],
            [false, '29 4 * * *', new \DateTime('2015-10-21 16:29:00')],
            [false, '* * 1 * *', new \DateTime('2015-10-21 16:29:00')],
            [false, '30 * 21 * *', new \DateTime('2015-10-21 16:29:00')],
            [false, '29 * 1 * *', new \DateTime('2015-10-21 16:29:00')],
            [false, '* 4 21 * *', new \DateTime('2015-10-21 16:29:00')],
            [false, '* 16 1 * *', new \DateTime('2015-10-21 16:29:00')],
            [false, '30 16 21 * *', new \DateTime('2015-10-21 16:29:00')],
            [false, '29 4 21 * *', new \DateTime('2015-10-21 16:29:00')],
            [false, '29 16 1 * *', new \DateTime('2015-10-21 16:29:00')],
            [false, '* * * 1 *', new \DateTime('2015-10-21 16:29:00')],
            [false, '30 * * 10 *', new \DateTime('2015-10-21 16:29:00')],
            [false, '29 * * 1 *', new \DateTime('2015-10-21 16:29:00')],
            [false, '* 4 * 10 *', new \DateTime('2015-10-21 16:29:00')],
            [false, '* 16 * 1 *', new \DateTime('2015-10-21 16:29:00')],
            [false, '30 16 * 10 *', new \DateTime('2015-10-21 16:29:00')],
            [false, '29 4 * 10 *', new \DateTime('2015-10-21 16:29:00')],
            [false, '29 16 * 1 *', new \DateTime('2015-10-21 16:29:00')],
            [false, '* * 1 10 *', new \DateTime('2015-10-21 16:29:00')],
            [false, '* * 21 1 *', new \DateTime('2015-10-21 16:29:00')],
            [false, '30 * 21 10 *', new \DateTime('2015-10-21 16:29:00')],
            [false, '29 * 1 10 *', new \DateTime('2015-10-21 16:29:00')],
            [false, '29 * 21 1 *', new \DateTime('2015-10-21 16:29:00')],
            [false, '* 4 21 10 *', new \DateTime('2015-10-21 16:29:00')],
            [false, '* 16 1 10 *', new \DateTime('2015-10-21 16:29:00')],
            [false, '* 16 21 1 *', new \DateTime('2015-10-21 16:29:00')],
            [false, '30 16 21 10 *', new \DateTime('2015-10-21 16:29:00')],
            [false, '29 4 21 10 *', new \DateTime('2015-10-21 16:29:00')],
            [false, '29 16 1 10 *', new \DateTime('2015-10-21 16:29:00')],
            [false, '29 16 21 1 *', new \DateTime('2015-10-21 16:29:00')],
            [false, '* * * * 1', new \DateTime('2015-10-21 16:29:00')],
            [false, '30 * * * 3', new \DateTime('2015-10-21 16:29:00')],
            [false, '29 * * * 1', new \DateTime('2015-10-21 16:29:00')],
            [false, '* 4 * * 3', new \DateTime('2015-10-21 16:29:00')],
            [false, '* 16 * * 1', new \DateTime('2015-10-21 16:29:00')],
            [false, '30 16 * * 3', new \DateTime('2015-10-21 16:29:00')],
            [false, '29 4 * * 3', new \DateTime('2015-10-21 16:29:00')],
            [false, '29 16 * * 1', new \DateTime('2015-10-21 16:29:00')],
            [true, '* * 1 * 3', new \DateTime('2015-10-21 16:29:00')],
            [true, '* * 21 * 1', new \DateTime('2015-10-21 16:29:00')],
            [false, '* * 1 * 1', new \DateTime('2015-10-21 16:29:00')],
            [false, '30 * 21 * 3', new \DateTime('2015-10-21 16:29:00')],
            [false, '30 * 1 * 3', new \DateTime('2015-10-21 16:29:00')],
            [false, '30 * 21 * 1', new \DateTime('2015-10-21 16:29:00')],
            [true, '29 * 1 * 3', new \DateTime('2015-10-21 16:29:00')],
            [true, '29 * 21 * 1', new \DateTime('2015-10-21 16:29:00')],
            [false, '29 * 1 * 1', new \DateTime('2015-10-21 16:29:00')],
            [false, '* 4 21 * 3', new \DateTime('2015-10-21 16:29:00')],
            [false, '* 4 1 * 3', new \DateTime('2015-10-21 16:29:00')],
            [false, '* 4 21 * 1', new \DateTime('2015-10-21 16:29:00')],
            [true, '* 16 1 * 3', new \DateTime('2015-10-21 16:29:00')],
            [true, '* 16 21 * 1', new \DateTime('2015-10-21 16:29:00')],
            [false, '* 16 1 * 1', new \DateTime('2015-10-21 16:29:00')],
            [false, '30 4 21 * 3', new \DateTime('2015-10-21 16:29:00')],
            [false, '30 4 1 * 3', new \DateTime('2015-10-21 16:29:00')],
            [false, '30 4 21 * 1', new \DateTime('2015-10-21 16:29:00')],
            [false, '30 16 1 * 3', new \DateTime('2015-10-21 16:29:00')],
            [false, '30 16 21 * 1', new \DateTime('2015-10-21 16:29:00')],
            [false, '30 16 1 * 1', new \DateTime('2015-10-21 16:29:00')],
            [false, '29 4 21 * 3', new \DateTime('2015-10-21 16:29:00')],
            [false, '29 4 1 * 3', new \DateTime('2015-10-21 16:29:00')],
            [false, '29 4 21 * 1', new \DateTime('2015-10-21 16:29:00')],
            [true, '29 16 1 * 3', new \DateTime('2015-10-21 16:29:00')],
            [true, '29 16 21 * 1', new \DateTime('2015-10-21 16:29:00')],
            [false, '29 16 1 * 1', new \DateTime('2015-10-21 16:29:00')],
            [false, '* * * 10 1', new \DateTime('2015-10-21 16:29:00')],
            [false, '* * * 1 3', new \DateTime('2015-10-21 16:29:00')],
            [false, '30 * * 10 3', new \DateTime('2015-10-21 16:29:00')],
            [false, '29 * * 1 3', new \DateTime('2015-10-21 16:29:00')],
            [false, '29 * * 10 1', new \DateTime('2015-10-21 16:29:00')],
            [false, '* 4 * 10 3', new \DateTime('2015-10-21 16:29:00')],
            [false, '* 16 * 1 3', new \DateTime('2015-10-21 16:29:00')],
            [false, '* 16 * 10 1', new \DateTime('2015-10-21 16:29:00')],
            [false, '30 16 * 10 3', new \DateTime('2015-10-21 16:29:00')],
            [false, '29 4 * 10 3', new \DateTime('2015-10-21 16:29:00')],
            [false, '29 16 * 1 3', new \DateTime('2015-10-21 16:29:00')],
            [false, '29 16 * 10 1', new \DateTime('2015-10-21 16:29:00')],
            [true, '* * 21 10 1', new \DateTime('2015-10-21 16:29:00')],
            [true, '* * 1 10 3', new \DateTime('2015-10-21 16:29:00')],
            [false, '* * 21 1 3', new \DateTime('2015-10-21 16:29:00')],
            [false, '* * 21 1 1', new \DateTime('2015-10-21 16:29:00')],
            [false, '* * 1 1 3', new \DateTime('2015-10-21 16:29:00')],
            [false, '30 * 21 10 3', new \DateTime('2015-10-21 16:29:00')],
            [true, '29 * 1 10 3', new \DateTime('2015-10-21 16:29:00')],
            [false, '29 * 21 1 3', new \DateTime('2015-10-21 16:29:00')],
            [true, '29 * 21 10 1', new \DateTime('2015-10-21 16:29:00')],
            [false, '29 * 1 10 1', new \DateTime('2015-10-21 16:29:00')],
            [false, '* 4 21 10 3', new \DateTime('2015-10-21 16:29:00')],
            [true, '* 16 1 10 3', new \DateTime('2015-10-21 16:29:00')],
            [false, '* 16 21 1 3', new \DateTime('2015-10-21 16:29:00')],
            [true, '* 16 21 10 1', new \DateTime('2015-10-21 16:29:00')],
            [false, '* 16 1 10 1', new \DateTime('2015-10-21 16:29:00')],
            [false, '30 16 21 10 3', new \DateTime('2015-10-21 16:29:00')],
            [false, '29 4 21 10 3', new \DateTime('2015-10-21 16:29:00')],
            [true, '29 16 1 10 3', new \DateTime('2015-10-21 16:29:00')],
            [false, '29 16 21 1 3', new \DateTime('2015-10-21 16:29:00')],
            [true, '29 16 21 10 1', new \DateTime('2015-10-21 16:29:00')],
            [false, '29 16 1 10 1', new \DateTime('2015-10-21 16:29:00')],
            [true, '0-29 * * * *', new \DateTime('2018-01-01 12:15:00')],
            [false, '30-59 * * * *', new \DateTime('2018-01-01 12:15:00')],
            [true, '0,15,30,45 * * * *', new \DateTime('2018-01-01 12:15:00')],
            [false, '0,20,40 * * * *', new \DateTime('2018-01-01 12:15:00')],
            [true, '10-20,30-40 * * * *', new \DateTime('2018-01-01 12:15:00')],
            [false, '20-30,40-50 * * * *', new \DateTime('2018-01-01 12:15:00')],
            [true, '*/5 * * * *', new \DateTime('2018-01-01 12:15:00')],
            [false, '*/10 * * * *', new \DateTime('2018-01-01 12:15:00')],
            [true, '0-30/5 * * * *', new \DateTime('2018-01-01 12:15:00')],
            [false, '30-55/5 * * * *', new \DateTime('2018-01-01 12:15:00')],
            [true, '2-32/5 * * * *', new \DateTime('2018-01-01 12:17:00')],
            [false, '2-32/5 * * * *', new \DateTime('2018-01-01 12:15:00')],
            [true, '1-20/3 * * * *', new \DateTime('2018-01-01 12:01:00')],
            [true, '1-20/3 * * * *', new \DateTime('2018-01-01 12:04:00')],
            [false, '1-20/3 * * * *', new \DateTime('2018-01-01 12:03:00')],
            [true, '0 */6 * * *', new \DateTime('2018-01-01 00:00:00')],
            [true, '0 */6 * * *', new \DateTime('2018-01-01 06:00:00')],
            [true, '0 */6 * * *', new \DateTime('2018-01-01 12:00:00')],
            [true, '0 */6 * * *', new \DateTime('2018-01-01 18:00:00')],
            [false, '0 */6 * * *', new \DateTime('2018-01-01 14:00:00')],
            [true, '* * */2 * *', new \DateTime('2018-01-01 14:00:00')],
            [false, '* * */2 * *', new \DateTime('2018-01-02 14:00:00')],
            [true, '* * */2 * *', new \DateTime('2018-01-15 14:00:00')],
            [false, '* * */2 * *', new \DateTime('2018-01-30 14:00:00')],
            [false, '* * * */2 *', new \DateTime('2018-04-15 14:00:00')],
            [true, '* * * */2 *', new \DateTime('2018-05-15 14:00:00')],
            [true, '* * * */2 *', new \DateTime('2018-11-15 14:00:00')],
            [false, '* * * */2 *', new \DateTime('2018-12-15 14:00:00')],
            [true, '* * * * */2', new \DateTime('2018-01-07 12:00:00')],
            [false, '* * * * */2', new \DateTime('2018-01-08 12:00:00')],
            [true, '0 12 * * SUN', new \DateTime('2018-01-07 12:00:00')],
            [false, '0 12 * * SAT', new \DateTime('2018-01-07 12:00:00')],
            [true, '0 12 * JAN-JUN *', new \DateTime('2018-01-01 12:00:00')],
            [false, '0 12 * JUL-DEC *', new \DateTime('2018-01-01 12:00:00')],
            [true, '0 12 * JUL-DEC *', new \DateTime('2018-10-01 12:00:00')],
            [true, '* * * JAN *', new \DateTime('2018-01-01 12:00:00')],
            [false, '* * * JAN *', new \DateTime('2018-10-01 12:00:00')],
            [true, '* * * FEB *', new \DateTime('2018-02-01 12:00:00')],
            [false, '* * * FEB *', new \DateTime('2018-10-01 12:00:00')],
            [true, '* * * MAR *', new \DateTime('2018-03-01 12:00:00')],
            [false, '* * * MAR *', new \DateTime('2018-10-01 12:00:00')],
            [true, '* * * APR *', new \DateTime('2018-04-01 12:00:00')],
            [false, '* * * APR *', new \DateTime('2018-10-01 12:00:00')],
            [true, '* * * MAY *', new \DateTime('2018-05-01 12:00:00')],
            [false, '* * * MAY *', new \DateTime('2018-10-01 12:00:00')],
            [true, '* * * JUN *', new \DateTime('2018-06-01 12:00:00')],
            [false, '* * * JUN *', new \DateTime('2018-10-01 12:00:00')],
            [true, '* * * JUL *', new \DateTime('2018-07-01 12:00:00')],
            [false, '* * * JUL *', new \DateTime('2018-10-01 12:00:00')],
            [true, '* * * AUG *', new \DateTime('2018-08-01 12:00:00')],
            [false, '* * * AUG *', new \DateTime('2018-10-01 12:00:00')],
            [true, '* * * SEP *', new \DateTime('2018-09-01 12:00:00')],
            [false, '* * * SEP *', new \DateTime('2018-10-01 12:00:00')],
            [true, '* * * OCT *', new \DateTime('2018-10-01 12:00:00')],
            [false, '* * * OCT *', new \DateTime('2018-01-01 12:00:00')],
            [true, '* * * NOV *', new \DateTime('2018-11-01 12:00:00')],
            [false, '* * * NOV *', new \DateTime('2018-10-01 12:00:00')],
            [true, '* * * DEC *', new \DateTime('2018-12-01 12:00:00')],
            [false, '* * * DEC *', new \DateTime('2018-10-01 12:00:00')],
            [true, '0 12 * JAN SUN', new \DateTime('2018-01-07 12:00:00')],
            [false, '0 12 * FEB SUN', new \DateTime('2018-01-07 12:00:00')],
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

    public function testRun()
    {
        $this->expectOutputString('test');
        $this->cron->add('* * * * *', $this, 'output');
        $this->cron->run(new \DateTime());
    }

    public function output()
    {
        echo 'test';
    }
}
