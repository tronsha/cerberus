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
    
    abstract protected function matchColor($id);

    abstract protected function getColor($fontColor = null, $backgroundColor = null);

    abstract protected function bold($output);

    abstract protected function underline($output);

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
            if (false === $open) {
                $output .= $start;
                $open = true;
            } else {
                $output .= $stop;
                $open = false;
            }
            $output .= $part;
        }
        if (true === $open) {
            $output .= $stop;
        }

        return $output;
    }

    /**
     * @param string $output
     * @throws Exception
     * @return string
     */
    public function color($output)
    {
        if ('HTML' !== $this->type && 'CONSOLE' !== $this->type) {
            throw new Exception('Type must be HTML or Console.');
        }
        $coloredOutput = '';
        $colorType = $fontColor = $backgroundColor = '';
        $reset = false;
        foreach (str_split($output) as $char) {
            if ("\x03" === $char) {
                $colorType = 'font';
            } elseif ('font' === $colorType && (0 === strlen($fontColor) || 1 === strlen($fontColor)) && 48 <= ord($char) && 57 >= ord($char)) {
                $fontColor .= $char;
            } elseif ('font' === $colorType && (1 === strlen($fontColor) || 2 === strlen($fontColor)) && ',' === $char) {
                $colorType = 'background';
            } elseif ('background' === $colorType && (0 === strlen($backgroundColor) || 1 === strlen($backgroundColor)) && 48 <= ord($char) && 57 >= ord($char)) {
                $backgroundColor .= $char;
            } elseif ('font' === $colorType || 'background' === $colorType) {
                if ('' !== $backgroundColor) {
                    $coloredOutput .= $this->getColor($fontColor, $backgroundColor);
                    $reset = true;
                } elseif ('' !== $fontColor) {
                    $coloredOutput .= $this->getColor($fontColor);
                    $reset = true;
                } else {
                    $coloredOutput .= $this->getColor();
                    $reset = false;
                }
                if ('background' === $colorType && '' === $backgroundColor) {
                    $coloredOutput .= ',';
                }
                $colorType = $fontColor = $backgroundColor = '';
                $coloredOutput .= $char;
            } else {
                $coloredOutput .= $char;
            }
        }
        if (true === $reset) {
            $coloredOutput .= $this->getColor();
        }

        return $coloredOutput;
    }
}
