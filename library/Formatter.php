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


/**
 * Class Formatter
 * @package Cerberus
 * @author Stefan Hüsges
 * @link http://www.mpcx.net/projekte/cerberus/ Project Homepage
 * @link https://github.com/tronsha/cerberus Project on GitHub
 * @license http://www.gnu.org/licenses/gpl-3.0 GNU General Public License
 */
class Formatter
{
    const CONSOLE = 1;
    const HTML = 2;

    protected $type = 0;

    /**
     * @param int $type
     */
    public function __construct($type)
    {
        $this->type = $type;
    }

    /**
     * @param string $output
     * @return string
     */
    public function bold($output)
    {
        $boldArray = explode("\x02", $output);
        $output = array_shift($boldArray);
        $open = false;
        foreach ($boldArray as $part) {
            if ($open) {
                $output .= ($this->type === self::CONSOLE ? "\033[22m" : ($this->type === self::HTML ? '</b>' : "\x02"));
                $open = false;
            } else {
                $output .= ($this->type === self::CONSOLE ? "\033[1m" : ($this->type === self::HTML ? '<b>' : "\x02"));
                $open = true;
            }
            $output .= $part;
        }
        if ($open) {
            $output .= ($this->type === self::CONSOLE ? "\033[22m" : ($this->type === self::HTML ? '</b>' : ''));
        }

        return $output;
    }

    /**
     * @param string $output
     * @return string
     */
    public function color($output)
    {
        if ($this->type === self::CONSOLE) {
            return $this->colorConsole($output);
        } elseif ($this->type === self::HTML) {
            return $this->colorHtml($output);
        } else {
            return $output;
        }
    }

    /**
     * @param int $id
     * @return string
     */
    protected function matchColorConsole($id)
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
    protected function getConsoleColor($fg = null, $bg = null)
    {
        $fgbg = array();
        if ($fg !== null) {
            $fgbg[] = '38;5;' . matchColorConsole($fg);
            if ($bg !== null) {
                $fgbg[] = '48;5;' . matchColorConsole($bg);
            }

            return "\033[" . implode(';', $fgbg) . 'm';
        }

        return "\033[39;49m";
    }

    /**
     * @param string $output
     * @return string
     */
    protected function colorConsole($output)
    {
        return $output;
    }

    /**
     * @param int $id
     * @return string
     */
    protected function matchColorHtml($id)
    {
        $matchColor = array(
            0 => '#FFFFFF',
            1 => '#000000',
            2 => '#00007F',
            3 => '#009300',
            4 => '#FF0000',
            5 => '#7F0000',
            6 => '#9C009C',
            7 => '#FC7F00',
            8 => '#FFFF00',
            9 => '#00FC00',
            10 => '#009393',
            11 => '#00FFFF',
            12 => '#0000FC',
            13 => '#FF00FF',
            14 => '#7F7F7F',
            15 => '#D2D2D2'
        );

        return $matchColor[$id % 16];
    }

    /**
     * @param string $output
     * @return string
     */
    protected function colorHtml($output)
    {
        return $output;
    }
}