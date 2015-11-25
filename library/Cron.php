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
 * Class Cron
 * @package Cerberus
 * @author Stefan Hüsges
 * @link http://www.mpcx.net/cerberus/ Project Homepage
 * @link https://github.com/tronsha/Cerberus Project on GitHub
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
     * @return bool
     */
    public function compare($cronString, $minute, $hour, $day_of_month, $month, $day_of_week)
    {
        $cronString = trim($cronString);
        list($cronMinute, $cronHour, $cronDayOfMonth, $cronMonth, $cronDayOfWeek) = explode(' ', $cronString);
        $cronDayOfWeek = $cronDayOfWeek == 7 ? 0 : $cronDayOfWeek;
        $cronMinute = $cronMinute != '*' ? $this->prepare($cronMinute, 0, 59) : $cronMinute;
        $cronHour = $cronHour != '*' ? $this->prepare($cronHour, 0, 23) : $cronHour;
        $cronDayOfMonth = $cronDayOfMonth != '*' ? $this->prepare($cronDayOfMonth, 1, 31) : $cronDayOfMonth;
        $cronMonth = $cronMonth != '*' ? $this->prepare($cronMonth, 1, 12) : $cronMonth;
        $cronDayOfWeek = $cronDayOfWeek != '*' ? $this->prepare($cronDayOfWeek, 0, 6) : $cronDayOfWeek;
        if (
            (
                $cronMinute == '*' || in_array($minute, $cronMinute) === true
            ) && (
                $cronHour == '*' || in_array($hour, $cronHour) === true
            ) && (
                $cronMonth == '*' || in_array($month, $cronMonth) === true
            ) && (
                (
                    (
                        $cronDayOfMonth == '*' || in_array($day_of_month, $cronDayOfMonth) === true
                    ) && (
                        $cronDayOfWeek == '*' || in_array($day_of_week, $cronDayOfWeek) === true
                    )
                ) || (
                    (
                        $cronDayOfMonth != '*'
                    ) && (
                        $cronDayOfWeek != '*'
                    ) && (
                        (
                            in_array($day_of_month, $cronDayOfMonth) === true
                        ) || (
                            in_array($day_of_week, $cronDayOfWeek) === true
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
            if (strpos($value, '-') !== false) {
                list($min, $max) = explode('-', $value);
                $min = (int)$min;
                $max = (int)$max;
                for ($i = $min; $i <= $max; $i++) {
                    $array[] = $i;
                }
            } else {
                $array[] = (int)$value;
            }
        }
        return $array;
    }
}
