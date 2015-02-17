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
use Cerberus\Formatter\FormatterFactory;

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
        $formatter = FormatterFactory::console();
        $text = $formatter->bold($text);

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
        if ($this->len($text) <= $length) {
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
            $text = $this->cut($text, $length - 3) . '...';
            if (strpos($text, "\033") !== false) {
                $text .= "\033[0m";
            }
        }
        $text = utf8_encode($text);

        return $escape ? $this->escape($text) : $text;
    }

    /**
     * @param string $string
     * @param int $length
     * @return string
     * @throws \Exception
     */
    protected function cut($string, $length)
    {
        if ($length < 0) {
            throw new \Exception('Length cannot be negative.');
        }
        $ignore = false;
        if ($length !== null) {
            for ($i = 0; $i <= $length && $i < strlen($string); $i++) {
                if ($string[$i] === "\033") {
                    $ignore = true;
                }
                if ($ignore) {
                    $length++;
                }
                if ($string[$i] === 'm') {
                    $ignore = false;
                }
            }
        }

        return substr($string, 0, $length);
    }

    /**
     * @param string $string
     * @return int
     */
    protected function len($string)
    {
        $string = preg_replace("/\033\[[0-9;]+m/", '', $string);

        return strlen($string);
    }
}