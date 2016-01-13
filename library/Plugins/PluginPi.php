<?php

/*   Cerberus IRCBot
 *   Copyright (C) 2008 - 2016 Stefan Hüsges
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
 * @package Cerberus\Plugins
 * @author Stefan Hüsges
 * @link http://www.mpcx.net/projekte/cerberus/ Project Homepage
 * @link https://github.com/tronsha/cerberus Project on GitHub
 * @link http://tools.ietf.org/html/rfc2812 Internet Relay Chat: Client Protocol
 * @link http://www.raspberrypi.org/ Raspberry Pi
 * @link http://wiringpi.com/ WiringPi
 * @link https://github.com/tronsha/wiringPi WiringPi GIT Fork
 * @link https://github.com/technion/lol_dht22 DHT 22
 * @license http://www.gnu.org/licenses/gpl-3.0 GNU General Public License
 */
class PluginPi extends Plugin
{
    const IN = 'in';
    const OUT = 'out';
    const HIGH = 1;
    const LOW = 0;
    const TIME = 50;

    const LED_BLUE = 17;
    const LED_GREEN = 27;
    const LED_RED = 22;

    const DHT = 7;
    const LOLDHT = '/home/pi/projects/lol_dht22/loldht';

    protected $vars = null;
    protected $info = [];

    /**
     *
     */
    protected function init()
    {
        if (Cerberus::isExecAvailable()) {
            exec('gpio -v', $outputArray);
            $output = implode(' ', $outputArray);
            $pos = strpos($output, 'Raspberry Pi');
            if ($pos !== false) {
                $this->vars = $this->irc->getVars();
                $info = substr($output, $pos);
                preg_match_all('/Type:\s*([^,:]+),\s*Revision:\s*([^,:]+),\s*Memory:\s*([^,:]+),\s*Maker:\s*([^,:]+)\s*$/i', $info, $matches, PREG_SET_ORDER);
                $this->info['type'] = $matches[0][1];
                $this->info['revision'] = $matches[0][2];
                $this->info['memory'] = $matches[0][3];
                $this->info['maker'] = $matches[0][4];
                $this->setOut(self::LED_GREEN);
                $this->setOut(self::LED_BLUE);
                $this->setOut(self::LED_RED);
                $this->blink(self::LED_GREEN);
                $this->blink(self::LED_BLUE);
                $this->blink(self::LED_RED);
                $this->setIn(self::DHT);
                $this->addEvent('onPrivmsg');
                $this->addEvent('onJoin');
                $this->addEvent('onPart');
                $this->addEvent('onQuit');
                $this->addEvent('onShutdown');
                $this->addEvent('onControl');
                $this->addCron('0 * * * *', 'privmsgCpuTemp');
            } else {
                $this->sysinfo('This Plugin is only for the RaspberryPi with WiringPi.');
                $this->sysinfo('http://www.raspberrypi.org');
                $this->sysinfo('http://wiringpi.com');
            }
        } else {
            $this->sysinfo('Can\'t use this Plugin, because "exec" is disabled');
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

    /**
     * @param int $pin
     */
    protected function blink($pin)
    {
        $this->setHigh($pin);
        $this->wait();
        $this->setLow($pin);
    }

    /**
     * @param int $pin
     */
    protected function setIn($pin)
    {
        exec('gpio -g mode ' . $pin . ' ' . self::IN);
    }

    /**
     * @param int $pin
     */
    protected function setOut($pin)
    {
        exec('gpio -g mode ' . $pin . ' ' . self::OUT);
    }

    /**
     * @param int $pin
     */
    protected function setHigh($pin)
    {
        exec('gpio -g write ' . $pin . ' ' . self::HIGH);
    }

    /**
     * @param int $pin
     */
    protected function setLow($pin)
    {
        exec('gpio -g write ' . $pin . ' ' . self::LOW);
    }

    /**
     *
     */
    protected function wait()
    {
        Cerberus::msleep(self::TIME);
    }

    /**
     * @return float
     */
    protected function getCpuTemp()
    {
        preg_match('/[0-9\.]+/', exec('vcgencmd measure_temp'), $matches);
        return (float)$matches[0];
    }

    /**
     * @return string
     */
    protected function getCpuTempCelsius()
    {
        return sprintf('%.1f°C', $this->getCpuTemp());
    }

    /**
     * @return string
     */
    protected function getCpuTempFahrenheit()
    {
        return sprintf('%.1f°F', $this->getCpuTemp() * 1.8 + 32);
    }

    /**
     * @return float
     */
    protected function getTemp()
    {
        $output = exec('sudo ' . self::LOLDHT . ' ' . self::DHT . ' | grep Temperature');
        preg_match('/Temperature = ([0-9\.]+)/', $output, $matches);
        return (float)$matches[1];
    }

    /**
     * @return string
     */
    protected function getTempCelsius()
    {
        return sprintf('%.1f°C', $this->getTemp());
    }

    /**
     * @return string
     */
    protected function getTempFahrenheit()
    {
        return sprintf('%.1f°F', $this->getTemp() * 1.8 + 32);
    }

    /**
     * @param array $data
     */
    public function onPrivmsg($data)
    {
        $splitText = explode(' ', $data['text']);
        $command = array_shift($splitText);
        if ($command == '!temp' && $data['channel'] == $this->getConfig()->getChannel()) {
            $this->privmsgTemp($data['channel']);
        }
        $this->blink(self::LED_BLUE);
    }

    /**
     * @param array $data
     */
    public function onJoin($data)
    {
        $this->blink(self::LED_GREEN);
    }

    /**
     * @param array $data
     */
    public function onPart($data)
    {
        $this->blink(self::LED_RED);
    }

    /**
     * @param array $data
     */
    public function onQuit($data)
    {
        $this->blink(self::LED_RED);
    }

    /**
     * @param string|null $channel
     */
    public function privmsgCpuTemp($channel = null)
    {
        $channel = $channel === null ? $this->getConfig()->getChannel() : $channel;
        $this->getActions()->privmsg($channel, $this->getCpuTempCelsius());
    }

    /**
     * @param string|null $channel
     */
    public function privmsgTemp($channel = null)
    {
        $channel = $channel === null ? $this->getConfig()->getChannel() : $channel;
        $this->getActions()->privmsg($channel, $this->getTempCelsius());
    }

    /**
     * @param array $data
     */
    public function onControl($data)
    {
        if ($data['command'] == 'pi') {
            switch ($data['param']) {
                case 'temp':
                    $this->getActions()->privmsg($data['channel'], $this->getTempCelsius());
                    break;
                case 'cputemp':
                    $this->getActions()->privmsg($data['channel'], $this->getCpuTempCelsius());
                    break;
                default:
                    break;
            }
        }
    }
}
