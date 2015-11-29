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

namespace Cerberus\Formatter;

use Exception;

/**
 * Class Formatter
 * @package Cerberus
 * @author Stefan Hüsges
 * @link http://www.mpcx.net/projekte/cerberus/ Project Homepage
 * @link https://github.com/tronsha/cerberus Project on GitHub
 * @license http://www.gnu.org/licenses/gpl-3.0 GNU General Public License
 */
abstract class Formatter
{
    protected $type = null;

    /**
     * @param string $output
     * @param string $delimiter
     * @param string $start
     * @param string $stop
     * @return string
     */
    protected function format($output, $delimiter, $start = null, $stop = null)
    {
        $formatArray = explode($delimiter, $output);
        $output = array_shift($formatArray);
        $open = false;
        foreach ($formatArray as $part) {
            if ($open === false) {
                $output .= $start;
                $open = true;
            } else {
                $output .= $stop;
                $open = false;
            }
            $output .= $part;
        }
        if ($open) {
            $output .= $stop;
        }

        return $output;
    }

    /**
     * @param string $output
     * @return string
     * @throws Exception
     */
    public function color($output)
    {
        if ($this->type != 'HTML' && $this->type != 'CONSOLE') {
            throw new Exception('Type must be HTML or Console.');
        }
        $coloredOutput = '';
        $xx = $fg = $bg = '';
        $reset = false;
        foreach (str_split($output) as $char) {
            if ($char === "\x03") {
                $xx = 'fg';
            } elseif ($xx === 'fg' && (strlen($fg) === 0 || strlen($fg) === 1) && ord($char) >= 48 && ord($char) <= 57) {
                $fg .= $char;
            } elseif ($xx === 'fg' && (strlen($fg) === 1 || strlen($fg) === 2) && $char === ',') {
                $xx = 'bg';
            } elseif ($xx === 'bg' && (strlen($bg) === 0 || strlen($bg) === 1) && ord($char) >= 48 && ord($char) <= 57) {
                $bg .= $char;
            } elseif ($xx === 'fg' || $xx === 'bg') {
                if ($bg !== '') {
                    $coloredOutput .= $this->getColor($fg, $bg);
                    $reset = true;
                } elseif ($fg !== '') {
                    $coloredOutput .= $this->getColor($fg);
                    $reset = true;
                } else {
                    $coloredOutput .= $this->getColor();
                    $reset = false;
                }
                if ($xx === 'bg' && $bg === '') {
                    $coloredOutput .= ',';
                }
                $xx = $fg = $bg = '';
                $coloredOutput .= $char;
            } else {
                $coloredOutput .= $char;
            }
        }
        if ($reset === true) {
            $coloredOutput .= $this->getColor();
        }

        return $coloredOutput;
    }
}
