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

/**
 * Class FormatterConsole
 * @package Cerberus
 * @author Stefan Hüsges
 * @link http://www.mpcx.net/projekte/cerberus/ Project Homepage
 * @link https://github.com/tronsha/cerberus Project on GitHub
 * @license http://www.gnu.org/licenses/gpl-3.0 GNU General Public License
 */
class FormatterConsole extends Formatter
{
    /**
     * @param int $id
     * @return string
     */
    protected function matchColor($id)
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
            $fgbg[] = '38;5;' . $this->matchColor($fg);
            if ($bg !== null) {
                $fgbg[] = '48;5;' . $this->matchColor($bg);
            }

            return "\033[" . implode(';', $fgbg) . 'm';
        }

        return "\033[39;49m";
    }

    /**
     * @param string $output
     * @return string
     */
    public function color($output)
    {
        $coloredOutput = '';
        $xx = $fg = $bg = '';
        $reset = '';
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
                    $reset = $this->getColor();
                } elseif ($fg !== '') {
                    $coloredOutput .= $this->getColor($fg);
                    $reset = $this->getColor();
                } else {
                    $coloredOutput .= $this->getColor();
                    $reset = '';
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

        return $coloredOutput . $reset;
    }

    /**
     * @param string $output
     * @return string
     */
    public function bold($output)
    {
        return parent::format($output, "\x02", "\033[1m", "\033[22m");
    }

    /**
     * @param string $output
     * @return string
     */
    public function underline($output)
    {
        return parent::format($output, "\x1F", "\033[4m", "\033[24m");
    }
}
