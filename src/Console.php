<?php

/*
 * Cerberus IRCBot
 * Copyright (C) 2008 - 2020 Stefan Hüsges
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

use Cerberus\Formatter\FormatterFactory;
use Exception;
use Symfony\Component\Console\Formatter\OutputFormatter;
use Symfony\Component\Console\Formatter\OutputFormatterStyle;
use Symfony\Component\Console\Output\ConsoleOutput;

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
        if (true === $this->return) {
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
        if (true === isset($this->param) && true === is_array($this->param) && true === in_array('-noconsole', $this->param, true)) {
            return true === $escape ? $this->escape($text) : $text;
        }

        $formatter = FormatterFactory::console();
        $text = $formatter->bold($text);
        $text = $formatter->underline($text);
        $text = $formatter->color($text);

        if (false === $length) {
            $text .= ('\\' === substr($text, -1)) ? ' ' : '';

            return true === $escape ? $this->escape($text) : $text;
        }
        if (null === $length) {
            if (false === Cerberus::isExecAvailable()) {
                return true === $escape ? $this->escape($text) : $text;
            }
            preg_match('/columns\s([0-9]+);/', strtolower(exec('stty -a | grep columns')), $matches);
            if (false === isset($matches[1]) || 0 >= intval($matches[1])) {
                return true === $escape ? $this->escape($text) : $text;
            }
            $length = intval($matches[1]);
        }
        $length = $length - $offset;
        if ($this->len($text) <= $length) {
            $text .= ('\\' === substr($text, -1)) ? ' ' : '';

            return true === $escape ? $this->escape($text) : $text;
        }
        $text = utf8_decode($text);
        if (true === $break) {
            if (true === $wordwrap) {
                $text = $this->wordwrap($text, $length);
            } else {
                $text = $this->split($text, $length, PHP_EOL);
            }
            $text = str_replace(PHP_EOL, PHP_EOL . str_repeat(' ', $offset), $text);
        } else {
            $text = $this->cut($text, $length - 3) . '...';
            if (false !== strpos($text, "\033")) {
                $text .= "\033[0m";
            }
        }
        $text = utf8_encode($text);
        $text .= ('\\' === substr($text, -1)) ? ' ' : '';

        return true === $escape ? $this->escape($text) : $text;
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
     * @param bool $cut
     * @throws Exception
     * @return string
     */
    protected function wordwrap($text, $length = 80, $break = PHP_EOL, $cut = true)
    {
        if (1 > $length) {
            throw new Exception('Length cannot be negative or null.');
        }
        $textArray = explode(' ', $text);
        $count = 0;
        $lineCount = 0;
        $output = [];
        $output[$lineCount] = '';
        foreach ($textArray as $word) {
            $wordLength = $this->len($word);
            if (($count + $wordLength) <= $length) {
                $count += $wordLength + 1;
                $output[$lineCount] .= $word . ' ';
            } elseif (true === $cut && $wordLength > $length) {
                $wordArray = explode(' ', $this->split($word, $length, ' '));
                foreach ($wordArray as $word) {
                    $wordLength = $this->len($word);
                    $output[$lineCount] = trim($output[$lineCount]);
                    $lineCount++;
                    $count = $wordLength + 1;
                    $output[$lineCount] = $word . ' ';
                }
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
     * @throws Exception
     * @return string
     */
    protected function split($text, $length = 80, $end = PHP_EOL)
    {
        if (1 > $length) {
            throw new Exception('Length cannot be negative or null.');
        }
        $output = '';
        $count = 0;
        $ignore = false;
        $len = strlen($text);
        for ($i = 0; $i < $len; $i++) {
            $output .= $this->count($text[$i], $count, $ignore);
            if ($count === $length) {
                $count = 0;
                $output .= $end;
            }
        }

        return $output;
    }

    /**
     * @param string $text
     * @param int $length
     * @throws Exception
     * @return string
     */
    protected function cut($text, $length)
    {
        if (1 > $length) {
            throw new Exception('Length cannot be negative or null.');
        }
        $output = '';
        $count = 0;
        $ignore = false;
        $len = strlen($text);
        for ($i = 0; $i < $len; $i++) {
            $output .= $this->count($text[$i], $count, $ignore);
            if ($count === $length) {
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
        if ("\033" === $char) {
            $ignore = true;
        }
        if (false === $ignore) {
            $count++;
        }
        if (true === $ignore && 'm' === $char) {
            $ignore = false;
        }

        return $char;
    }

    /**
     *
     */
    public function enableOutput()
    {
        $this->return = false;
    }

    /**
     *
     */
    public function disableOutput()
    {
        $this->return = true;
    }
}
