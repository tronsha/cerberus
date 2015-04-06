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
use PhpGpio\Gpio;

/**
 * Class PluginPi
 * @package Cerberus
 * @author Stefan Hüsges
 * @link http://www.mpcx.net/projekte/cerberus/ Project Homepage
 * @link https://github.com/tronsha/cerberus Project on GitHub
 * @link http://tools.ietf.org/html/rfc2812 Internet Relay Chat: Client Protocol
 * @link http://www.raspberrypi.org/ Raspberry Pi
 * @link https://github.com/ronanguilloux/php-gpio PHP GPIO
 * @license http://www.gnu.org/licenses/gpl-3.0 GNU General Public License
 */
class PluginPi extends Plugin
{
    const BLINKTIME = 50;

    protected $gpio = null;

    /**
     *
     */
    protected function init()
    {
        if (php_uname('n') === 'raspberrypi') {
            $this->gpio = new Gpio;
            $this->gpio->setup(17, "out");
            $this->gpio->setup(27, "out");
            $this->gpio->setup(22, "out");
            $this->gpio->output(17, 0);
            $this->gpio->output(27, 0);
            $this->gpio->output(22, 0);
            $this->irc->addEvent('onPrivmsg', $this);
            $this->irc->addEvent('onJoin', $this);
            $this->irc->addEvent('onPart', $this);
            $this->irc->addEvent('onQuit', $this);
        } else {
            $this->irc->sysinfo('This Plugin is only for the RaspberryPi.');
        }
    }

    /**
     *
     */
    protected function shutdown()
    {
        $this->gpio->unexportAll();
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

    protected function blink($pin)
    {
        $this->gpio->output($pin, 1);
        Cerberus::msleep(self::BLINKTIME);
        $this->gpio->output($pin, 0);
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
