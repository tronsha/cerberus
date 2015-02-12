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

use Symfony\Component\Console\Output\StreamOutput;
use Symfony\Component\Console\Formatter\OutputFormatter;

class ConsoleTest extends \PHPUnit_Framework_TestCase
{
    protected $stream;

    protected function setUp()
    {
        $this->stream = fopen('php://memory', 'a', false);
    }

    protected function tearDown()
    {
        $this->stream = null;
    }

    public function testEscapedFormattedOutput()
    {
        $output = new StreamOutput($this->stream, StreamOutput::VERBOSITY_NORMAL, true, null);
        $output->writeln('<info>' . OutputFormatter::escape('<error>some error</error>') . '</info>');
        rewind($output->getStream());
        $this->assertEquals(
            "\033[32m<error>some error</error>\033[0m" . PHP_EOL,
            stream_get_contents($output->getStream())
        );
    }

    public function testEscapedOutput()
    {
        $console = new Console;
        $this->assertEquals('\<error>some error\</error>', $console->escape('<error>some error</error>'));
    }

    public function testPrepareOutput()
    {
        $console = new Console;

        $input = str_repeat('x', 100);
        $output = str_repeat('x', 77) . '...';
        $this->assertEquals($output, $console->prepare($input, false, 80, false, false, 0));

        $input = str_repeat('x', 100);
        $output = str_repeat('x', 80) . PHP_EOL . str_repeat('x', 20);
        $this->assertEquals($output, $console->prepare($input, false, 80, true, false, 0));

        $input = str_repeat('x', 25) . ' ' . str_repeat('x', 25) . ' ' . str_repeat('x', 25) . ' ' . str_repeat('x', 25);
        $output = str_repeat('x', 25) . ' ' . str_repeat('x', 25) . ' ' . str_repeat('x', 25) . PHP_EOL . str_repeat('x', 25);
        $this->assertEquals($output, $console->prepare($input, false, 80, true, true, 0));
    }
}