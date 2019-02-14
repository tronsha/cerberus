<?php

/*
 * Cerberus IRCBot
 * Copyright (C) 2008 - 2019 Stefan Hüsges
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
     *
     */
    public function __construct()
    {
        $this->type = 'CONSOLE';
    }

    /**
     * @param int $id
     * @return string
     */
    protected function matchColor($id)
    {
        $matchColor = [
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
        ];

        return $matchColor[$id % 16];
    }

    /**
     * @param int|null $fontColor
     * @param int|null $backgroundColor
     * @return string
     */
    protected function getColor($fontColor = null, $backgroundColor = null)
    {
        $colorArray = [];
        if (null !== $fontColor) {
            $colorArray[] = '38;5;' . $this->matchColor($fontColor);
            if (null !== $backgroundColor) {
                $colorArray[] = '48;5;' . $this->matchColor($backgroundColor);
            }

            return "\033[" . implode(';', $colorArray) . 'm';
        }

        return "\033[39;49m";
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
