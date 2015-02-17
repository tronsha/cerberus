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

    public function invokeMethod(&$object, $methodName)
    {
        $reflection = new \ReflectionClass(get_class($object));
        $method = $reflection->getMethod($methodName);
        $method->setAccessible(true);
        $parameters = array_slice(func_get_args(), 2);
        return $method->invokeArgs($object, $parameters);
    }

    public function testFormattedEscapedOutput()
    {
        $output = new StreamOutput($this->stream, StreamOutput::VERBOSITY_NORMAL, true, null);
        $output->writeln('<info>' . OutputFormatter::escape('<error>some error</error>') . '</info>');
        rewind($output->getStream());
        $this->assertEquals(
            "\033[32m<error>some error</error>\033[39m" . PHP_EOL,
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

        $input = 'abcdefghijklmnopqrstuvwxyzabcdefghijklmnopqrstuvwxyzabcdefghijklmnopqrstuvwxyzabcdefghijklmnopqrstuvwxyz';
        $output = 'abcdefghijklmnopqrstuvwxyzabcdefghijklmnopqrstuvwxyzabcdefghijklmnopqrstuvwxy...';
        $this->assertEquals($output, $console->prepare($input, false, 80, false, false, 0));

        $input = 'abcdefghijklmnopqrstuvwxyzabcdefghijklmnopqrstuvwxyzabcdefghijklmnopqrstuvwxyzabcdefghijklmnopqrstuvwxyz';
        $output = 'abcdefghijklmnopqrstuvwxyzabcdefghijklmnopqrstuvwxyzabcdefghijklmnopqrstuvwxyzab' . PHP_EOL . 'cdefghijklmnopqrstuvwxyz';
        $this->assertEquals($output, $console->prepare($input, false, 80, true, false, 0));

        $input = 'abcdefghijklmnopqrstuvwxyz abcdefghijklmnopqrstuvwxyz abcdefghijklmnopqrstuvwxyz abcdefghijklmnopqrstuvwxyz';
        $output = 'abcdefghijklmnopqrstuvwxyz abcdefghijklmnopqrstuvwxyz abcdefghijklmnopqrstuvwxyz' . PHP_EOL . 'abcdefghijklmnopqrstuvwxyz';
        $this->assertEquals($output, $console->prepare($input, false, 80, true, true, 0));

        $input = "abc\033[1mdefghijklmnopqrstuvwxyzabcdefghijklmnopqrstuvwxyzabcdefghijklmnopqrstuvwxyzabcdefghijklmnopqrstuvwxyz";
        $output = "abc\033[1mdefghijklmnopqrstuvwxyzabcdefghijklmnopqrstuvwxyzabcdefghijklmnopqrstuvwxy...\033[0m";
        $this->assertEquals($output, $console->prepare($input, false, 80, false, false, 0));
    }

    public function testLen()
    {
        $this->assertEquals(4, strlen('test'));
        $this->assertEquals(12, strlen("\033[1mtest\033[0m"));
        $this->assertEquals(4, $this->invokeMethod(new Console, 'len', 'test'));
        $this->assertEquals(4, $this->invokeMethod(new Console, 'len', "\033[1mtest\033[0m"));
    }

    public function testCut()
    {
        $this->assertEquals('foo', substr("foobar", 0, 3));
        $this->assertEquals('foo', $this->invokeMethod(new Console, 'cut', "foobar", 3));
        $this->assertEquals("\033[1", substr("\033[1mfoobar\033[0m", 0, 3));
        $this->assertEquals("\033[1mfoo", $this->invokeMethod(new Console, 'cut', "\033[1mfoobar\033[0m", 3));
        $this->assertEquals("\033[1mfoobar\033[0m", $this->invokeMethod(new Console, 'cut', "\033[1mfoobar\033[0m", 6));
        $this->assertEquals("foo\033[1mbar\033[0m", $this->invokeMethod(new Console, 'cut', "foo\033[1mbar\033[0m", 6));
    }
}