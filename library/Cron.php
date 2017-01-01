<?php

/*
 * Cerberus IRCBot
 * Copyright (C) 2008 - 2017 Stefan Hüsges
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

namespace Cerberus;

use Exception;

/**
 * Class Cron
 * @package Cerberus
 * @author Stefan Hüsges
 * @link http://www.mpcx.net/cerberus/ Project Homepage
 * @link https://github.com/tronsha/Cerberus Project on GitHub
 * @link https://en.wikipedia.org/wiki/Cron Cron
 * @license http://www.gnu.org/licenses/gpl-3.0 GNU General Public License
 */
class Cron
{
    protected $irc;
    protected $cronjobs = [];
    protected $cronId = 0;

    /**
     *
     */
    public function __construct()
    {
    }

    /**
     * @param string $cronString
     * @param string $object
     * @param string $method
     * @return int
     */
    public function add($cronString, $object, $method)
    {
        $this->cronId++;
        $cronString = preg_replace('/\s+/', ' ', $cronString);
        $this->cronjobs[$this->cronId] = ['cron' => $cronString, 'object' => $object, 'method' => $method];
        return $this->cronId;
    }

    /**
     * @param int $id
     * @return bool
     */
    public function remove($id)
    {
        if (array_key_exists($id, $this->cronjobs)) {
            unset($this->cronjobs[$id]);
            return true;
        }
        return false;
    }

    /**
     * @param int $minute
     * @param int $hour
     * @param int $day_of_month
     * @param int $month
     * @param int $day_of_week
     */
    public function run($minute, $hour, $day_of_month, $month, $day_of_week)
    {
        foreach ($this->cronjobs as $cron) {
            if ($this->compare($cron['cron'], $minute, $hour, $day_of_month, $month, $day_of_week) === true) {
                $cron['object']->{$cron['method']}();
            }
        }
    }

    /**
     * @param string $cronString
     * @param int $minute
     * @param int $hour
     * @param int $day_of_month
     * @param int $month
     * @param int $day_of_week
     * @throws Exception
     * @return bool
     */
    public function compare($cronString, $minute, $hour, $day_of_month, $month, $day_of_week)
    {
        $cronString = trim($cronString);
        $cronArray = explode(' ', $cronString);
        if (count($cronArray) !== 5) {
            throw new Exception('a cron has an error');
        }
        list($cronMinute, $cronHour, $cronDayOfMonth, $cronMonth, $cronDayOfWeek) = $cronArray;
        $cronDayOfWeek = $this->dowNameToNumber($cronDayOfWeek);
        $cronMonth = $this->monthNameToNumber($cronMonth);
        $cronDayOfWeek = intval($cronDayOfWeek) === 7 ? 0 : $cronDayOfWeek;
        $cronMinute = $cronMinute !== '*' ? $this->prepare($cronMinute, 0, 59) : $cronMinute;
        $cronHour = $cronHour !== '*' ? $this->prepare($cronHour, 0, 23) : $cronHour;
        $cronDayOfMonth = $cronDayOfMonth !== '*' ? $this->prepare($cronDayOfMonth, 1, 31) : $cronDayOfMonth;
        $cronMonth = $cronMonth !== '*' ? $this->prepare($cronMonth, 1, 12) : $cronMonth;
        $cronDayOfWeek = $cronDayOfWeek !== '*' ? $this->prepare($cronDayOfWeek, 0, 6) : $cronDayOfWeek;
        if (
            (
                $cronMinute === '*' || in_array($minute, $cronMinute, true) === true
            ) && (
                $cronHour === '*' || in_array($hour, $cronHour, true) === true
            ) && (
                $cronMonth === '*' || in_array($month, $cronMonth, true) === true
            ) && (
                (
                    (
                        $cronDayOfMonth === '*' || in_array($day_of_month, $cronDayOfMonth, true) === true
                    ) && (
                        $cronDayOfWeek === '*' || in_array($day_of_week, $cronDayOfWeek, true) === true
                    )
                ) || (
                    (
                        $cronDayOfMonth !== '*'
                    ) && (
                        $cronDayOfWeek !== '*'
                    ) && (
                        (
                            in_array($day_of_month, $cronDayOfMonth, true) === true
                        ) || (
                            in_array($day_of_week, $cronDayOfWeek, true) === true
                        )
                    )
                )
            )
        ) {
            return true;
        }
        return false;
    }

    /**
     * @param string $string
     * @param int $a
     * @param int $b
     * @return array
     */
    public function prepare($string, $a, $b)
    {
        $values = [];
        if (strpos($string, ',') !== false) {
            $values = explode(',', $string);
        } else {
            $values[] = $string;
        }
        $array = [];
        foreach ($values as $value) {
            $steps = 1;
            if (strpos($string, '/') !== false) {
                list($value, $steps) = explode('/', $string);
            }
            if ($value === '*') {
                $value = $a . '-' . $b;
            }
            if (strpos($value, '-') !== false) {
                list($min, $max) = explode('-', $value);
                $min = (int)$min;
                $max = (int)$max;
                for ($i = $min, $j = 0; $i <= $max; $i++, $j++) {
                    if ($j % $steps === 0) {
                        $array[] = $i;
                    }
                }
            } else {
                $array[] = (int)$value;
            }
        }
        return $array;
    }

    /**
     * @param string $subject
     * @return string
     */
    public function monthNameToNumber($subject)
    {
        $subject = strtolower($subject);
        $search = ['jan', 'feb', 'mar', 'apr', 'may', 'jun', 'jul', 'aug', 'sep', 'oct', 'nov', 'dec'];
        $replace = [1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12];
        return str_replace($search, $replace, $subject);
    }

    /**
     * @param string $subject
     * @return string
     */
    public function dowNameToNumber($subject)
    {
        $subject = strtolower($subject);
        $search = ['sun', 'mon', 'tue', 'wed', 'thu', 'fri', 'sat'];
        $replace = [0, 1, 2, 3, 4, 5, 6];
        return str_replace($search, $replace, $subject);
    }
}
