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
    protected $open = false;
    protected $bg = '';

    /**
     *
     */
    public function __construct()
    {
        $this->type = 'HTML';
    }

    /**
     * @param int $id
     * @return string
     */
    protected function matchColor($id)
    {
        $matchColor = [
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
        ];

        return $matchColor[$id % 16];
    }

    /**
     * @param int|null $fg
     * @param int|null $bg
     * @return string
     */
    protected function getColor($fg = null, $bg = null)
    {
        $fgbg = [];
        if ($fg !== null) {
            $fgbg[] = 'color: ' . $this->matchColor($fg);
            if ($bg !== null) {
                $this->bg = $bg;
            }
            if ($this->bg != '') {
                $fgbg[] = 'background-color: ' . $this->matchColor($this->bg);
            }
            if ($this->open === true) {
                return '</span><span style="' . implode('; ', $fgbg) . ';">';
            }
            $this->open = true;
            return '<span style="' . implode('; ', $fgbg) . ';">';
        }
        if ($this->open === true) {
            $this->bg = '';
            $this->open = false;
            return '</span>';
        }
        return '';
    }

    /**
     * @param string $output
     * @return string
     */
    public function bold($output)
    {
        return parent::format($output, "\x02", '<b style="font-weight: bold;">', '</b>');
    }

    /**
     * @param string $output
     * @return string
     */
    public function underline($output)
    {
        return parent::format($output, "\x1F", '<u style="text-decoration: underline;">', '</u>');
    }
}
