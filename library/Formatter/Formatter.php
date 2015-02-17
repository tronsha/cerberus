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
 * Class Formatter
 * @package Cerberus
 * @author Stefan Hüsges
 * @link http://www.mpcx.net/projekte/cerberus/ Project Homepage
 * @link https://github.com/tronsha/cerberus Project on GitHub
 * @license http://www.gnu.org/licenses/gpl-3.0 GNU General Public License
 */
abstract class Formatter
{
    const HTML = 'Cerberus\Formatter\FormatterHtml';
    const CONSOLE = 'Cerberus\Formatter\FormatterConsole';

    /**
     * @param string $output
     * @return string
     */
    public function bold($output)
    {
        $type = get_class($this);
        $boldArray = explode("\x02", $output);
        $output = array_shift($boldArray);
        $open = false;
        foreach ($boldArray as $part) {
            if ($open) {
                if ($type === self::CONSOLE) {
                    $output .= "\033[22m";
                } elseif ($type === self::HTML) {
                    $output .= '</b>';
                } else {
                    $output .= "\x02";
                }
                $open = false;
            } else {
                if ($type === self::CONSOLE) {
                    $output .= "\033[1m";
                } elseif ($type === self::HTML) {
                    $output .= '<b>';
                } else {
                    $output .= "\x02";
                }
                $open = true;
            }
            $output .= $part;
        }
        if ($open) {
            if ($type === self::CONSOLE) {
                $output .= "\033[22m";
            } elseif ($type === self::HTML) {
                $output .= '</b>';
            } else {
                $output .= "\x02";
            }
        }

        return $output;
    }

    /**
     * @param string $output
     * @return string
     */
    abstract public function color($output);
}