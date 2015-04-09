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

namespace Cerberus\Plugins;

use Cerberus\Cerberus;
use Cerberus\Plugin;

/**
 * Class PluginPi
 * @package Cerberus
 * @author Stefan Hüsges
 * @link http://www.mpcx.net/projekte/cerberus/ Project Homepage
 * @link https://github.com/tronsha/cerberus Project on GitHub
 * @link http://tools.ietf.org/html/rfc2812 Internet Relay Chat: Client Protocol
 * @link http://www.raspberrypi.org/ Raspberry Pi
 * @link https://projects.drogon.net/raspberry-pi/wiringpi/ WiringPi
 * @license http://www.gnu.org/licenses/gpl-3.0 GNU General Public License
 */
class PluginPi extends Plugin
{
    const HIGH = 1;
    const LOW = 0;
    const TIME = 50;

    protected $gpio = null;

    /**
     *
     */
    protected function init()
    {
        if (Cerberus::isExecAvailable()) {
            if (php_uname('n') === 'raspberrypi') {
                exec('gpio -g write 17 ' . self::LOW);
                exec('gpio -g write 22 ' . self::LOW);
                exec('gpio -g write 27 ' . self::LOW);
                $this->irc->addEvent('onPrivmsg', $this);
                $this->irc->addEvent('onJoin', $this);
                $this->irc->addEvent('onPart', $this);
                $this->irc->addEvent('onQuit', $this);
                $this->irc->addEvent('onShutdown', $this);
            } else {
                $this->irc->sysinfo('This Plugin is only for the RaspberryPi.');
            }
        } else {

        }
    }

    /**
     * @param array $data
     * @return bool
     */
    public function onLoad($data)
    {
        $returnValue = parent::onLoad($data);
        if ($data !== null) {
        }
        return $returnValue;
    }

    /**
     * @param array $data
     */
    public function onShutdown($data)
    {
    }

    protected function blink($pin)
    {
        exec('gpio -g write ' . $pin . ' ' . self::HIGH);
        Cerberus::msleep(self::TIME);
        exec('gpio -g write ' . $pin . ' ' . self::LOW);
    }

    /**
     * @param array $data
     */
    public function onPrivmsg($data)
    {
        $splitText = explode(' ', $data['text']);
        $command = array_shift($splitText);
        if ($command == '!temp') {
            $output = exec('vcgencmd measure_temp');
            $output = (float)str_replace('temp=', '', $output);
            $this->irc->privmsg($data['channel'], $output);
        }
        $this->blink(17);
    }

    /**
     * @param array $data
     */
    public function onJoin($data)
    {
        $this->blink(27);
    }

    /**
     * @param array $data
     */
    public function onPart($data)
    {
        $this->blink(22);
    }

    /**
     * @param array $data
     */
    public function onQuit($data)
    {
        $this->blink(22);
    }
}
