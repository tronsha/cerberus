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