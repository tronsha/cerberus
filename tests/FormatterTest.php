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

use Cerberus\Formatter\FormatterFactory;

class FormatterTest extends \PHPUnit_Framework_TestCase
{
    protected $consoleFormatter;
    protected $htmlFormatter;

    protected function setUp()
    {
        $this->consoleFormatter = FormatterFactory::console();
        $this->htmlFormatter = FormatterFactory::html();
    }

    protected function tearDown()
    {
        unset($this->consoleFormatter);
        unset($this->htmlFormatter);
    }

    public function testConsoleBold()
    {
        $this->assertEquals(
            "\033[1mfoo\033[22m",
            $this->consoleFormatter->bold("\x02foo\x02")
        );
        $this->assertEquals(
            "\033[1mfoo\033[22m",
            $this->consoleFormatter->bold("\x02foo")
        );
    }

    public function testConsoleUnderline()
    {
        $this->assertEquals(
            "\033[4mfoo\033[24m",
            $this->consoleFormatter->underline("\x1Ffoo\x1F")
        );
        $this->assertEquals(
            "\033[4mfoo\033[24m",
            $this->consoleFormatter->underline("\x1Ffoo")
        );
    }

    public function testConsoleColor()
    {
        $this->assertEquals(
            "\033[38;5;15mfoo\033[39;49m",
            $this->consoleFormatter->color("\x03" . '0foo' . "\x03")
        );
        $this->assertEquals(
            "\033[38;5;0mfoo\033[39;49m",
            $this->consoleFormatter->color("\x03" . '1foo' . "\x03")
        );
        $this->assertEquals(
            "\033[38;5;4mfoo\033[39;49m",
            $this->consoleFormatter->color("\x03" . '2foo' . "\x03")
        );
        $this->assertEquals(
            "\033[38;5;2mfoo\033[39;49m",
            $this->consoleFormatter->color("\x03" . '3foo' . "\x03")
        );
        $this->assertEquals(
            "\033[38;5;9mfoo\033[39;49m",
            $this->consoleFormatter->color("\x03" . '4foo' . "\x03")
        );
        $this->assertEquals(
            "\033[38;5;1mfoo\033[39;49m",
            $this->consoleFormatter->color("\x03" . '5foo' . "\x03")
        );
        $this->assertEquals(
            "\033[38;5;5mfoo\033[39;49m",
            $this->consoleFormatter->color("\x03" . '6foo' . "\x03")
        );
        $this->assertEquals(
            "\033[38;5;3mfoo\033[39;49m",
            $this->consoleFormatter->color("\x03" . '7foo' . "\x03")
        );
        $this->assertEquals(
            "\033[38;5;11mfoo\033[39;49m",
            $this->consoleFormatter->color("\x03" . '8foo' . "\x03")
        );
        $this->assertEquals(
            "\033[38;5;10mfoo\033[39;49m",
            $this->consoleFormatter->color("\x03" . '9foo' . "\x03")
        );
        $this->assertEquals(
            "\033[38;5;6mfoo\033[39;49m",
            $this->consoleFormatter->color("\x03" . '10foo' . "\x03")
        );
        $this->assertEquals(
            "\033[38;5;14mfoo\033[39;49m",
            $this->consoleFormatter->color("\x03" . '11foo' . "\x03")
        );
        $this->assertEquals(
            "\033[38;5;12mfoo\033[39;49m",
            $this->consoleFormatter->color("\x03" . '12foo' . "\x03")
        );
        $this->assertEquals(
            "\033[38;5;13mfoo\033[39;49m",
            $this->consoleFormatter->color("\x03" . '13foo' . "\x03")
        );
        $this->assertEquals(
            "\033[38;5;8mfoo\033[39;49m",
            $this->consoleFormatter->color("\x03" . '14foo' . "\x03")
        );
        $this->assertEquals(
            "\033[38;5;7mfoo\033[39;49m",
            $this->consoleFormatter->color("\x03" . '15foo' . "\x03")
        );
        $this->assertEquals(
            "\033[38;5;15;48;5;0mfoo\033[39;49m",
            $this->consoleFormatter->color("\x03" . '0,1foo' . "\x03")
        );
        $this->assertEquals(
            "\033[38;5;15;48;5;0mfoo\033[39;49m",
            $this->consoleFormatter->color("\x03" . '0,1foo')
        );
        $this->assertEquals(
            "\033[38;5;15;48;5;0mfoo\033[39;49mbar",
            $this->consoleFormatter->color("\x03" . '0,1foo' . "\x03" . 'bar')
        );
        $this->assertEquals(
            "\033[38;5;15m,foo\033[39;49m",
            $this->consoleFormatter->color("\x03" . '0,foo' . "\x03")
        );
    }

    public function testHtmlBold()
    {
        $this->assertEquals(
            '<b style="font-weight: bold;">foo</b>',
            $this->htmlFormatter->bold("\x02foo\x02")
        );
        $this->assertEquals(
            '<b style="font-weight: bold;">foo</b>',
            $this->htmlFormatter->bold("\x02foo")
        );
    }

    public function testHtmlUnderline()
    {
        $this->assertEquals(
            '<u style="text-decoration: underline;">foo</u>',
            $this->htmlFormatter->underline("\x1Ffoo\x1F")
        );
        $this->assertEquals(
            '<u style="text-decoration: underline;">foo</u>',
            $this->htmlFormatter->underline("\x1Ffoo")
        );
    }

    public function testHtmlColor()
    {
        $this->assertEquals(
            '<span style="color: #FFFFFF;">foo</span>',
            $this->htmlFormatter->color("\x03" . '0foo' . "\x03")
        );
        $this->assertEquals(
            '<span style="color: #000000;">foo</span>',
            $this->htmlFormatter->color("\x03" . '1foo' . "\x03")
        );
        $this->assertEquals(
            '<span style="color: #00007F;">foo</span>',
            $this->htmlFormatter->color("\x03" . '2foo' . "\x03")
        );
        $this->assertEquals(
            '<span style="color: #009300;">foo</span>',
            $this->htmlFormatter->color("\x03" . '3foo' . "\x03")
        );
        $this->assertEquals(
            '<span style="color: #FF0000;">foo</span>',
            $this->htmlFormatter->color("\x03" . '4foo' . "\x03")
        );
        $this->assertEquals(
            '<span style="color: #7F0000;">foo</span>',
            $this->htmlFormatter->color("\x03" . '5foo' . "\x03")
        );
        $this->assertEquals(
            '<span style="color: #9C009C;">foo</span>',
            $this->htmlFormatter->color("\x03" . '6foo' . "\x03")
        );
        $this->assertEquals(
            '<span style="color: #FC7F00;">foo</span>',
            $this->htmlFormatter->color("\x03" . '7foo' . "\x03")
        );
        $this->assertEquals(
            '<span style="color: #FFFF00;">foo</span>',
            $this->htmlFormatter->color("\x03" . '8foo' . "\x03")
        );
        $this->assertEquals(
            '<span style="color: #00FC00;">foo</span>',
            $this->htmlFormatter->color("\x03" . '9foo' . "\x03")
        );
        $this->assertEquals(
            '<span style="color: #009393;">foo</span>',
            $this->htmlFormatter->color("\x03" . '10foo' . "\x03")
        );
        $this->assertEquals(
            '<span style="color: #00FFFF;">foo</span>',
            $this->htmlFormatter->color("\x03" . '11foo' . "\x03")
        );
        $this->assertEquals(
            '<span style="color: #0000FC;">foo</span>',
            $this->htmlFormatter->color("\x03" . '12foo' . "\x03")
        );
        $this->assertEquals(
            '<span style="color: #FF00FF;">foo</span>',
            $this->htmlFormatter->color("\x03" . '13foo' . "\x03")
        );
        $this->assertEquals(
            '<span style="color: #7F7F7F;">foo</span>',
            $this->htmlFormatter->color("\x03" . '14foo' . "\x03")
        );
        $this->assertEquals(
            '<span style="color: #D2D2D2;">foo</span>',
            $this->htmlFormatter->color("\x03" . '15foo' . "\x03")
        );
        $this->assertEquals(
            '<span style="color: #FFFFFF; background-color: #000000;">foo</span>',
            $this->htmlFormatter->color("\x03" . '0,1foo' . "\x03")
        );
        $this->assertEquals(
            '<span style="color: #FFFFFF; background-color: #000000;">foo</span>',
            $this->htmlFormatter->color("\x03" . '0,1foo')
        );
        $this->assertEquals(
            '<span style="color: #FFFFFF; background-color: #000000;">foo</span>bar',
            $this->htmlFormatter->color("\x03" . '0,1foo' . "\x03" . 'bar')
        );
        $this->assertEquals(
            '<span style="color: #FFFFFF;">,foo</span>',
            $this->htmlFormatter->color("\x03" . '0,foo' . "\x03")
        );
        $this->assertEquals(
            '<span style="color: #00007F; background-color: #00FFFF;">foo</span>bar<span style="color: #7F0000;">baz</span>',
            $this->htmlFormatter->color("\x03" . '2,11foo' . "\x03" . 'bar' . "\x03" . '5baz' . "\x03")
        );
        $this->assertEquals(
            '<span style="color: #00007F;">foo</span><span style="color: #7F0000;">bar</span>',
            $this->htmlFormatter->color("\x03" . '2foo' . "\x03" . "\x03" . '5bar' . "\x03")
        );
        $this->assertEquals(
            '<span style="color: #00007F; background-color: #00FFFF;">foo</span><span style="color: #7F0000; background-color: #00FFFF;">bar</span>',
            $this->htmlFormatter->color("\x03" . '2,11foo' . "\x03" . '5bar' . "\x03")
        );
    }
}
