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
 * Class FormatterHtml
 * @package Cerberus
 * @author Stefan Hüsges
 * @link http://www.mpcx.net/projekte/cerberus/ Project Homepage
 * @link https://github.com/tronsha/cerberus Project on GitHub
 * @license http://www.gnu.org/licenses/gpl-3.0 GNU General Public License
 */
class FormatterHtml extends Formatter
{
    /**
     * @param int $id
     * @return string
     */
    protected function matchColor($id)
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
     * @param int|null $fg
     * @param int|null $bg
     * @return string
     */
    protected function getColor($fg = null, $bg = null)
    {
        $fgbg = array();
        if ($fg !== null) {
            $fgbg[] = 'color: ' . $this->matchColor($fg);
            if ($bg !== null) {
                $fgbg[] = 'background-color: ' . $this->matchColor($bg);
            }

            return '<span style="' . implode('; ', $fgbg) . ';">';
        }

        return "</span>";
    }

    /**
     * @param string $output
     * @return string
     */
    public function color($output)
    {
        $coloredOutput = '';
        $xx = $fg = $bg = '';
        $reset = 0;
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
                    $reset++;
                } elseif ($fg !== '') {
                    $coloredOutput .= $this->getColor($fg);
                    $reset++;
                } else {
                    $coloredOutput .= $this->getColor();
                    $reset--;
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
        for ($i = 0; $i < $reset; $i++) {
            $coloredOutput .= $this->getColor();
        }

        return $coloredOutput;
    }

    /**
     * @param string $output
     * @return string
     */
    public function bold($output)
    {
        return parent::format($output, "\x02", '<span style="font-weight: bold">', '</span>');
    }

    /**
     * @param string $output
     * @return string
     */
    public function underline($output)
    {
        return parent::format($output, "\x1F", '<span style="text-decoration: underline">', '</span>');
    }
}