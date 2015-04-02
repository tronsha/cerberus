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
use Exception;

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
    protected $return = false;
    protected $param = null;

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
     * @param array $argv
     */
    public function setParam($argv)
    {
        $this->param = $argv;
    }

    /**
     * @param string $output
     * @return mixed
     */
    public function writeln($output = '')
    {
        if ($this->return) {
            return $output;
        }
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
        if (isset($this->param) && is_array($this->param) && in_array('-noconsole', $this->param)) {
            return $escape ? $this->escape($text) : $text;
        }

        $formatter = FormatterFactory::console();
        $text = $formatter->bold($text);
        $text = $formatter->underline($text);
        $text = $formatter->color($text);

        if ($length === false) {
            return $escape ? $this->escape($text) : $text;
        }
        if ($length === null) {
            if (Cerberus::isExecAvailable() === false) {
                return $escape ? $this->escape($text) : $text;
            }
            preg_match('/columns\s([0-9]+);/', strtolower(exec('stty -a | grep columns')), $matches);
            if (isset($matches[1]) === false) {
                return $escape ? $this->escape($text) : $text;
            }
            $length = $matches[1];
        }
        $length = $length - $offset;
        if ($this->len($text) <= $length) {
            return $escape ? $this->escape($text) : $text;
        }
        $text = utf8_decode($text);
        if ($break === true) {
            if ($wordwrap === true) {
                $text = $this->wordwrap($text, $length);
            } else {
                $text = $this->split($text, $length, PHP_EOL);
            }
            $text = str_replace(PHP_EOL, PHP_EOL . str_repeat(' ', $offset), $text);
        } else {
            $text = $this->cut($text, $length - 3) . '...';
            if (strpos($text, "\033") !== false) {
                $text .= "\033[0m";
            }
        }
        $text = utf8_encode($text);
        if (substr($text, -1) == '\\') {
            $text .= ' ';
        }

        return $escape ? $this->escape($text) : $text;
    }

    /**
     * @param string $text
     * @return int
     */
    protected function len($text)
    {
        $text = preg_replace("/\033\[[0-9;]+m/", '', $text);

        return strlen($text);
    }

    /**
     * @param string $text
     * @param int $length
     * @param string $break
     * @return string
     * @throws Exception
     */
    protected function wordwrap($text, $length = 80, $break = PHP_EOL)
    {
        if ($length < 1) {
            throw new Exception('Length cannot be negative or null.');
        }
        $textArray = explode(' ', $text);
        $count = 0;
        $lineCount = 0;
        $output = array();
        $output[$lineCount] = '';
        foreach ($textArray as $word) {
            $wordLength = $this->len($word);
            if (($count + $wordLength) <= $length) {
                $count += $wordLength + 1;
                $output[$lineCount] .= $word . ' ';
            } else {
                $output[$lineCount] = trim($output[$lineCount]);
                $lineCount++;
                $count = $wordLength + 1;
                $output[$lineCount] = $word . ' ';
            }
        }

        return trim(implode($break, $output));
    }

    /**
     * @param string $text
     * @param int $length
     * @param string $end
     * @return string
     * @throws Exception
     */
    protected function split($text, $length = 80, $end = PHP_EOL)
    {
        if ($length < 1) {
            throw new Exception('Length cannot be negative or null.');
        }
        $output = '';
        $count = 0;
        $ignore = false;
        $len = strlen($text);
        for ($i = 0; $i < $len; $i++) {
            $output .= $this->count($text[$i], $count, $ignore);
            if ($count == $length) {
                $count = 0;
                $output .= $end;
            }
        }

        return $output;
    }

    /**
     * @param string $text
     * @param int $length
     * @return string
     * @throws Exception
     */
    protected function cut($text, $length)
    {
        if ($length < 1) {
            throw new Exception('Length cannot be negative or null.');
        }
        $output = '';
        $count = 0;
        $ignore = false;
        $len = strlen($text);
        for ($i = 0; $i < $len; $i++) {
            $output .= $this->count($text[$i], $count, $ignore);
            if ($count == $length) {
                break;
            }
        }

        return $output;
    }

    /**
     * @param string $char
     * @param int $count
     * @param bool $ignore
     * @return string
     */
    protected function count($char, &$count, &$ignore)
    {
        if ($char === "\033") {
            $ignore = true;
        }
        if ($ignore === false) {
            $count++;
        }
        if ($ignore === true && $char === 'm') {
            $ignore = false;
        }

        return $char;
    }

    /**
     * @param bool $output
     */
    public function output($output)
    {
        if ($output) {
            $this->return = false;
        } else {
            $this->return = true;
        }
    }
}
