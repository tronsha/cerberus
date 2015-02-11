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
        $this->output->getFormatter()->setStyle('time', new OutputFormatterStyle('yellow'));
        $this->output->getFormatter()->setStyle('in', new OutputFormatterStyle('cyan'));
        $this->output->getFormatter()->setStyle('out', new OutputFormatterStyle('magenta'));
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
     * @param mixed $maxlen
     * @param bool $break
     * @param int $offset
     * @return string
     */
    public function prepare($text, $escape = true, $maxlen = null, $break = false, $offset = 0)
    {
        if ($maxlen === false) {
            return $escape ? $this->escape($text)  : $text;
        }

        if ($maxlen === null) {
            if (Cerberus::is_exec_available() === false) {
                return $escape ? $this->escape($text)  : $text;
            }
            preg_match_all("/rows.([0-9]+);.columns.([0-9]+);/", strtolower(exec('stty -a |grep columns')), $output);
            $maxlen = $output[2][0];
        }

        $maxlen = $maxlen - $offset;

        if (strlen($text) <= $maxlen) {
            return $escape ? $this->escape($text)  : $text;
        }

        $text = utf8_decode($text);

        if ($break === true) {
            $out = substr($text, 0, $maxlen);
            $rest = substr($text, $maxlen);
            while (true) {
                if (strlen($rest) > $maxlen) {
                    $out .= PHP_EOL . str_repeat(' ', $offset) . substr($rest, 0, $maxlen);
                    $rest = substr($rest, $maxlen);
                } else {
                    $out .= PHP_EOL . str_repeat(' ', $offset) . $rest;
                    break;
                }
            }
        } else {
            $out = substr($text, 0, $maxlen - 3) . '...';
        }

        $out = utf8_encode($out);

        return $escape ? $this->escape($out)  : $out;
    }

}