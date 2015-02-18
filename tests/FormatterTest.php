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
        $this->assertEquals("\033[1mfoo\033[22m", $this->consoleFormatter->bold("\x02foo\x02"));
        $this->assertEquals("\033[1mfoo\033[22m", $this->consoleFormatter->bold("\x02foo"));
    }

    public function testConsoleUnderline()
    {
        $this->assertEquals("\033[4mfoo\033[24m", $this->consoleFormatter->underline("\x1Ffoo\x1F"));
        $this->assertEquals("\033[4mfoo\033[24m", $this->consoleFormatter->underline("\x1Ffoo"));
    }

    public function testConsoleColor()
    {
        $this->assertEquals("\033[38;5;0;48;5;11mfoobar\033[39;49m", $this->consoleFormatter->color("\x03" . '1,8foobar' . "\x03"));
        $this->assertEquals("\033[38;5;0;48;5;11mfoobar\033[39;49m", $this->consoleFormatter->color("\x03" . '1,8foobar'));
        $this->assertEquals("\033[38;5;0;48;5;11mfoo\033[39;49mbar", $this->consoleFormatter->color("\x03" . '1,8foo' . "\x03" . 'bar'));
    }
}