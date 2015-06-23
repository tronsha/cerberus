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
    protected $console;

    protected function setUp()
    {
        $this->console = new Console;
        $this->stream = fopen('php://memory', 'a', false);
    }

    protected function tearDown()
    {
        unset($this->console);
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

    public function testNestedStyles()
    {
        $formatter = new OutputFormatter(true);
        $this->assertEquals(
            "\033[32mTest \033[39m\033[37;41merror\033[39;49m\033[32m and \033[39m\033[33mcomment\033[39m\033[32m inside a info.\033[39m",
            $formatter->format('<info>Test <error>error</error> and <comment>comment</comment> inside a info.</info>')
        );
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
        $this->assertEquals('\<error>some error\</error>', $this->console->escape('<error>some error</error>'));
    }

    public function testPrepareOutput()
    {
        $input = 'abcdefghijklmnopqrstuvwxyzabcdefghijklmnopqrstuvwxyzabcdefghijklmnopqrstuvwxyzabcdefghijklmnopqrstuvwxyz';
        $output = 'abcdefghijklmnopqrstuvwxyzabcdefghijklmnopqrstuvwxyzabcdefghijklmnopqrstuvwxy...';
        $this->assertEquals($output, $this->console->prepare($input, false, 80, false, false, 0));

        $input = "abc\033[1mdefghijklmnopqrstuvwxyzabcdefghijklmnopqrstuvwxyzabcdefghijklmnopqrstuvwxyzabcdefghijklmnopqrstuvwxyz";
        $output = "abc\033[1mdefghijklmnopqrstuvwxyzabcdefghijklmnopqrstuvwxyzabcdefghijklmnopqrstuvwxy...\033[0m";
        $this->assertEquals($output, $this->console->prepare($input, false, 80, false, false, 0));

        $input = "abc\033[1mdefghijklmnopqrstuvwxyz\033[22mabcdefghijklmnopqrstuvwxyzabcdefghijklmnopqrstuvwxyzabcdefghijklmnopqrstuvwxyz";
        $output = "abc\033[1mdefghijklmnopqrstuvwxyz\033[22mabcdefghijklmnopqrstuvwxyzabcdefghijklmnopqrstuvwxy...\033[0m";
        $this->assertEquals($output, $this->console->prepare($input, false, 80, false, false, 0));

        $input = 'abcmdefghijklmnopqrstuvwxyz' . "\x03" . '1,8abcmdefghijklmnopqrstuvwxyz';
        $output = 'abcmdefghijklmnopqrstuvwxyz' . "\033[38;5;0;48;5;11m" . 'abcmdefghijklmnopqrstuvwxyz' . "\033[39;49m";
        $this->assertEquals($output, $this->console->prepare($input, false, 80, false, false, 0));

        $input = 'abcdefghijklmnopqrstuvwxyzabcdefghijklmnopqrstuvwxyzabcdefghijklmnopqrstuvwxyzabcdefghijklmnopqrstuvwxyz';
        $output = 'abcdefghijklmnopqrstuvwxyzabcdefghijklmnopqrstuvwxyzabcdefghijklmnopqrstuvwxyzab' . PHP_EOL . 'cdefghijklmnopqrstuvwxyz';
        $this->assertEquals($output, $this->console->prepare($input, false, 80, true, false, 0));

        $input = "abcdefghijklmnopqrstuvwxyz\033[1mabcdefghijklmnopqrstuvwxyzabcdefghijklmnopqrstuvwxyzabcdefghijklmnopqrstuvwxyz";
        $output = "abcdefghijklmnopqrstuvwxyz\033[1mabcdefghijklmnopqrstuvwxyzabcdefghijklmnopqrstuvwxyzab" . PHP_EOL . 'cdefghijklmnopqrstuvwxyz';
        $this->assertEquals($output, $this->console->prepare($input, false, 80, true, false, 0));

        $input = 'abcdefghijklmnopqrstuvwxyz abcdefghijklmnopqrstuvwxyz abcdefghijklmnopqrstuvwxyz abcdefghijklmnopqrstuvwxyz';
        $output = 'abcdefghijklmnopqrstuvwxyz abcdefghijklmnopqrstuvwxyz abcdefghijklmnopqrstuvwxyz' . PHP_EOL . 'abcdefghijklmnopqrstuvwxyz';
        $this->assertEquals($output, $this->console->prepare($input, false, 80, true, true, 0));

        $input = "abcdefghijklmnopqrstuvwxyzabcdefghijklmnopqrstuvwxyzabcdefghijklmnopqrstu \033[1mabcde fghijklmnopqrstuvwxyz";
        $output = "abcdefghijklmnopqrstuvwxyzabcdefghijklmnopqrstuvwxyzabcdefghijklmnopqrstu \033[1mabcde" . PHP_EOL . 'fghijklmnopqrstuvwxyz';
        $this->assertEquals($output, $this->console->prepare($input, false, 80, true, true, 0));
    }

    public function testLen()
    {
        $this->assertEquals(4, strlen('test'));
        $this->assertEquals(12, strlen("\033[1mtest\033[0m"));
        $this->assertEquals(4, $this->invokeMethod($this->console, 'len', 'test'));
        $this->assertEquals(4, $this->invokeMethod($this->console, 'len', "\033[1mtest\033[0m"));
    }

    public function testCut()
    {
        $this->assertEquals('foo', substr("foobar", 0, 3));
        $this->assertEquals('foo', $this->invokeMethod($this->console, 'cut', "foobar", 3));
        $this->assertEquals("\033[1", substr("\033[1mfoobar\033[0m", 0, 3));
        $this->assertEquals("\033[1mfoo", $this->invokeMethod($this->console, 'cut', "\033[1mfoobar\033[0m", 3));
        $this->assertEquals("\033[1mfoobar", $this->invokeMethod($this->console, 'cut', "\033[1mfoobar\033[0m", 6));
        $this->assertEquals("foo\033[1mbar", $this->invokeMethod($this->console, 'cut', "foo\033[1mbar\033[0m", 6));
    }

    public function testWordwrap()
    {
        $this->assertEquals("foo bar\nbaz", wordwrap("foo bar baz", 10, "\n"));
        $this->assertEquals("foo\n\033[1mbar\nbaz", wordwrap("foo \033[1mbar baz", 10, "\n"));
        $this->assertEquals("foo bar\nbaz", $this->invokeMethod($this->console, 'wordwrap', "foo bar baz", 10));
        $this->assertEquals("foo \033[1mbar\nbaz", $this->invokeMethod($this->console, 'wordwrap', "foo \033[1mbar baz", 10));
    }

    public function testSplit()
    {
        $this->assertEquals("foo b\nar ba\nz\n", chunk_split("foo bar baz", 5, "\n"));
        $this->assertEquals("foo \033\n[1mba\nr baz\n", chunk_split("foo \033[1mbar baz", 5, "\n"));
        $this->assertEquals("foo b\nar ba\nz", $this->invokeMethod($this->console, 'split', "foo bar baz", 5));
        $this->assertEquals("foo \033[1mb\nar ba\nz", $this->invokeMethod($this->console, 'split', "foo \033[1mbar baz", 5));
    }

    public function testException()
    {
        try {
            $this->invokeMethod($this->console, 'wordwrap', "foo", -1);
        } catch (\Exception $e) {
            $this->assertEquals("Length cannot be negative or null.", $e->getMessage());
        }
        try {
            $this->invokeMethod($this->console, 'split', "foo", -1);
        } catch (\Exception $e) {
            $this->assertEquals("Length cannot be negative or null.", $e->getMessage());
        }
        try {
            $this->invokeMethod($this->console, 'cut', "foo", -1);
        } catch (\Exception $e) {
            $this->assertEquals("Length cannot be negative or null.", $e->getMessage());
        }
    }
}
