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

namespace Cerberus;

use DateTime;
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
     * @param array $param
     * @return int
     */
    public function add($cronString, $object, $method = 'run', $param = null)
    {
        $this->cronId++;
        $cronString = preg_replace('/\s+/', ' ', $cronString);
        $this->cronjobs[$this->cronId] = ['cron' => $cronString, 'object' => $object, 'method' => $method, 'param' => $param];
        return $this->cronId;
    }

    /**
     * @param int $id
     * @return bool
     */
    public function remove($id)
    {
        if (true === array_key_exists($id, $this->cronjobs)) {
            unset($this->cronjobs[$id]);
            return true;
        }
        return false;
    }

    /**
     * @param DateTime $time
     * @throws Exception
     */
    public function run(DateTime $time)
    {
        foreach ($this->cronjobs as $cron) {
            if (true === $this->compare($cron['cron'], $time)) {
                $cron['object']->{$cron['method']}($cron['param']);
            }
        }
    }

    /**
     * @param string $cronString
     * @param DateTime $time
     * @throws Exception
     * @return bool
     */
    public function compare($cronString, DateTime $time = null)
    {
        if (null === $time) {
            $time = new DateTime('now');
        }

        $cronMinute = $this->getCronMinute($cronString);
        $cronHour = $this->getCronHour($cronString);
        $cronDayOfMonth = $this->getCronDayOfMonth($cronString);
        $cronMonth = $this->getCronMonth($cronString);
        $cronDayOfWeek = $this->getCronDayOfWeek($cronString);

        if (
            (
                '*' === $cronMinute || true === in_array($this->getMinute($time), $cronMinute, true)
            ) && (
                '*' === $cronHour || true === in_array($this->getHour($time), $cronHour, true)
            ) && (
                '*' === $cronMonth || true === in_array($this->getMonth($time), $cronMonth, true)
            ) && (
                (
                    (
                        '*' === $cronDayOfMonth || true === in_array($this->getDayOfMonth($time), $cronDayOfMonth, true)
                    ) && (
                        '*' === $cronDayOfWeek || true === in_array($this->getDayOfWeek($time), $cronDayOfWeek, true)
                    )
                ) || (
                    (
                        '*' !== $cronDayOfMonth
                    ) && (
                        '*' !== $cronDayOfWeek
                    ) && (
                        (
                            true === in_array($this->getDayOfMonth($time), $cronDayOfMonth, true)
                        ) || (
                            true === in_array($this->getDayOfWeek($time), $cronDayOfWeek, true)
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
     * @param DateTime $time
     * @return int
     */
    private function getMinute(DateTime $time)
    {
        return (int) $time->format('i');
    }

    /**
     * @param DateTime $time
     * @return int
     */
    private function getHour(DateTime $time)
    {
        return (int) $time->format('G');
    }

    /**
     * @param DateTime $time
     * @return int
     */
    private function getMonth(DateTime $time)
    {
        return (int) $time->format('n');
    }

    /**
     * @param DateTime $time
     * @return int
     */
    private function getDayOfMonth(DateTime $time)
    {
        return (int) $time->format('j');
    }

    /**
     * @param DateTime $time
     * @return int
     */
    private function getDayOfWeek(DateTime $time)
    {
        return (int) $time->format('w');
    }

    /**
     * @param string $cronString
     * @return array
     */
    private function explodeCronString($cronString)
    {
        return explode(' ', trim($cronString));
    }

    /**
     * @param string $cronString
     * @return array|string
     */
    private function getCronMinute($cronString)
    {
        $cronMinute = $this->explodeCronString($cronString)[0];
        if ('*' === $cronMinute) {
            return '*';
        }
        return $this->prepare((string) $cronMinute, 0, 59);
    }

    /**
     * @param string $cronString
     * @return array|string
     */
    private function getCronHour($cronString)
    {
        $cronHour = $this->explodeCronString($cronString)[1];
        if ('*' === $cronHour) {
            return '*';
        }
        return $this->prepare((string) $cronHour, 0, 23);
    }

    /**
     * @param string $cronString
     * @return array|string
     */
    private function getCronDayOfMonth($cronString)
    {
        $cronDayOfMonth = $this->explodeCronString($cronString)[2];
        if ('*' === $cronDayOfMonth) {
            return '*';
        }
        return $this->prepare((string) $cronDayOfMonth, 1, 31);
    }

    /**
     * @param string $cronString
     * @return array|string
     */
    private function getCronMonth($cronString)
    {
        $cronMonth = $this->explodeCronString($cronString)[3];
        if ('*' === $cronMonth) {
            return '*';
        }
        $cronMonth = $this->monthNameToNumber($cronMonth);
        return $this->prepare((string) $cronMonth, 1, 12);
    }

    /**
     * @param string $cronString
     * @return array|string
     */
    private function getCronDayOfWeek($cronString)
    {
        $cronDayOfWeek = $this->explodeCronString($cronString)[4];
        if ('*' === $cronDayOfWeek) {
            return '*';
        }
        $cronDayOfWeek = $this->dowNameToNumber($cronDayOfWeek);
        $cronDayOfWeek = (7 === (int) $cronDayOfWeek ? 0 : $cronDayOfWeek);
        return $this->prepare((string) $cronDayOfWeek, 0, 6);
    }

    /**
     * @param string $string
     * @param int $a
     * @param int $b
     * @return array
     */
    private function prepare($string, $a, $b)
    {
        $values = [];
        if (false !== strpos($string, ',')) {
            $values = explode(',', $string);
        } else {
            $values[] = $string;
        }
        $array = [];
        foreach ($values as $value) {
            $steps = 1;
            if (false !== strpos($string, '/')) {
                list($value, $steps) = explode('/', $string);
            }
            if ('*' === $value) {
                $value = $a . '-' . $b;
            }
            if (false !== strpos($value, '-')) {
                list($min, $max) = explode('-', $value);
                $min = (int) $min;
                $max = (int) $max;
                for ($i = $min, $j = 0; $i <= $max; $i++, $j++) {
                    if (0 === ($j % $steps)) {
                        $array[] = $i;
                    }
                }
            } else {
                $array[] = (int) $value;
            }
        }
        return $array;
    }

    /**
     * @param string $subject
     * @return string
     */
    private function monthNameToNumber($subject)
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
    private function dowNameToNumber($subject)
    {
        $subject = strtolower($subject);
        $search = ['sun', 'mon', 'tue', 'wed', 'thu', 'fri', 'sat'];
        $replace = [0, 1, 2, 3, 4, 5, 6];
        return str_replace($search, $replace, $subject);
    }
}
