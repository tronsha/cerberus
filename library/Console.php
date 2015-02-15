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

use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\Console\Formatter\OutputFormatter;
use Symfony\Component\Console\Formatter\OutputFormatterStyle;

/**
 * Class Console
 * @package Cerberus
 * @author Stefan Hüsges
 * @link http://www.mpcx.net/projekte/cerberus/ Project Homepage
 * @link https://github.com/tronsha/cerberus Project on GitHub
 * @link http://symfony.com/doc/current/components/console/introduction.html The Console Component
 * @license http://www.gnu.org/licenses/gpl-3.0 GNU General Public License
 */
class Console
{
    protected $output;

    /**
     *
     */
    public function __construct()
    {
        $this->output = new ConsoleOutput;
        $this->output->getFormatter()->setStyle('timestamp', new OutputFormatterStyle('yellow'));
        $this->output->getFormatter()->setStyle('input', new OutputFormatterStyle('cyan'));
        $this->output->getFormatter()->setStyle('output', new OutputFormatterStyle('magenta'));
    }

    /**
     * @param string $output
     * @return object $this
     */
    public function writeln($output)
    {
//        $output = $this->irc2consoleBold($output);
        $this->output->writeln($output);

        return $this;
    }

    /**
     * @param string $text
     * @return mixed|string
     */
    public function escape($text)
    {
        return OutputFormatter::escape($text);
    }

    /**
     * @param string $text
     * @param bool $escape
     * @param mixed $length
     * @param bool $break
     * @param bool $wordwrap
     * @param int $offset
     * @return string
     */
    public function prepare($text, $escape = true, $length = null, $break = true, $wordwrap = true, $offset = 0)
    {
        if ($length === false) {
            return $escape ? $this->escape($text) : $text;
        }
        if ($length === null) {
            if (Cerberus::is_exec_available() === false) {
                return $escape ? $this->escape($text) : $text;
            }
            preg_match('/columns\s([0-9]+);/', strtolower(exec('stty -a | grep columns')), $matches);
            $length = $matches[1];
        }
        $length = $length - $offset;
        if (strlen($text) <= $length) {
            return $escape ? $this->escape($text) : $text;
        }
        $text = utf8_decode($text);
        if ($break === true) {
            if ($wordwrap === true) {
                $text = wordwrap($text, $length, PHP_EOL, true);
            } else {
                $text = trim(chunk_split($text, $length, PHP_EOL));
            }
            $text = str_replace(PHP_EOL, PHP_EOL . str_repeat(' ', $offset), $text);
        } else {
            $text = substr($text, 0, $length - 3) . '...';
        }
        $text = utf8_encode($text);

        return $escape ? $this->escape($text) : $text;
    }

    /**
     * @param string $output
     * @return string
     */
    public function irc2consoleBold($output)
    {
        $boldArray = explode("\x02", $output);
        $output = array_shift($boldArray);
        $open = false;
        foreach ($boldArray as $part) {
            if ($open) {
                $output .= "\x1b[22m";
                $output .= "\x02";
                $open = false;
            } else {
                $output .= "\x02";
                $output .= "\x1b[1m";
                $open = true;
            }
            $output .= $part;
        }
        if ($open) {
            $output .= "\x1b[22m";
        }

        return $output;
    }

    /**
     * @param int $id
     * @return string
     */
    protected function matchColorIrc2Console($id)
    {
        $matchColor = array(
            0 => '15',
            1 => '0',
            2 => '4',
            3 => '2',
            4 => '9',
            5 => '1',
            6 => '5',
            7 => '3',
            8 => '11',
            9 => '10',
            10 => '6',
            11 => '14',
            12 => '12',
            13 => '13',
            14 => '8',
            15 => '7'
        );

        return $matchColor[$id % 16];
    }

    /**
     * @param int|null $fg
     * @param int|null $bg
     * @return string
     */
    protected function getColor($fg = null, $bg = null)
    {
        $fgbg = array();
        if ($fg !== null) {
            $fgbg[] = '38;5;' . matchColorIrc2Console($fg);
            if ($bg !== null) {
                $fgbg[] = '48;5;' . matchColorIrc2Console($bg);
            }

            return "\x1b[" . implode(';', $fgbg) . 'm';
        }

        return "\x1b[39;49m";
    }

    /**
     * @param string $output
     * @return string
     */
    public function irc2consoleColor($output)
    {
        return $output;
    }

}