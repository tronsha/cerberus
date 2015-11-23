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
    protected $cronjobs = array();
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
    public function addCron($cronString, $object, $method)
    {
        $this->cronId++;
        $cronString = preg_replace('/\s+/', ' ', $cronString);
        $this->cronjobs[$this->cronId] = ['cron' => $cronString, 'object' => $object, 'method' => $method];
        return $this->cronId;
    }

    /**
     * @param int $id
     */
    public function removeCron($id)
    {
        unset($this->cronjobs[$id]);
    }

    /**
     * @param int $minute
     * @param int $hour
     * @param int $day_of_month
     * @param int $month
     * @param int $day_of_week
     */
    public function cron($minute, $hour, $day_of_month, $month, $day_of_week)
    {
        foreach ($this->cronjobs as $cron) {
            if ($this->compareCron($cron['cron'], $minute, $hour, $day_of_month, $month, $day_of_week) === true) {
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
    public function compareCron($cronString, $minute, $hour, $day_of_month, $month, $day_of_week)
    {
        $cronString = trim($cronString);
        list($cronMinute, $cronHour, $cronDayOfMonth, $cronMonth, $cronDayOfWeek) = explode(' ', $cronString);
        $cronDayOfWeek = $cronDayOfWeek == 7 ? 0 : $cronDayOfWeek;
        if (
            (
                $cronMinute == '*' || $cronMinute == $minute
            ) && (
                $cronHour == '*' || $cronHour == $hour
            ) && (
                $cronMonth == '*' || $cronMonth == $month
            ) && (
                (
                    (
                        $cronDayOfMonth == '*' || $cronDayOfMonth == $day_of_month
                    ) && (
                        $cronDayOfWeek == '*' || $cronDayOfWeek == $day_of_week
                    )
                ) || (
                    (
                        $cronDayOfMonth != '*'
                    ) && (
                        $cronDayOfWeek != '*'
                    ) && (
                        (
                            $cronDayOfMonth == $day_of_month
                        ) || (
                            $cronDayOfWeek == $day_of_week
                        )
                    )
                )
            )
        ) {
            return true;
        }
        return false;
    }
}
