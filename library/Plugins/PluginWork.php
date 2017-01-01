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

namespace Cerberus\Plugins;

use Cerberus\Plugin;

/**
 * Class PluginWork
 * @package Cerberus\Plugins
 * @author Stefan Hüsges
 */
class PluginWork extends Plugin
{
    const CHANNEL = '#cerberbot';
    const NICK_WORK = '';
    const NICK_HOME = '';

    /**
     *
     */
    protected function init()
    {
        $this->addCron('0 8 * * 1-5', 'goodmorning');
        $this->addCron('0 17 * * 1-4', 'niceevening');
        $this->addCron('30 14 * * 5', 'niceweekend');
    }

    /**
     * @return array
     */
    protected function translations()
    {
        $translations = [
            'de' => [
                'Good morning' => 'Guten Morgen',
                'Have a nice evening' => 'Schönen Ferierabend',
                'Nice weekend' => 'Schönes Wochenende'
            ]
        ];
        return $translations;
    }

    /**
     * @param array $data
     * @return bool
     */
    public function onLoad($data)
    {
        $returnValue = parent::onLoad($data);
        return $returnValue;
    }

    /**
     *
     */
    public function goodmorning()
    {
        $channel = self::CHANNEL;
        if (empty($channel) === false && $this->inChannel($channel)) {
            $this->getActions()->privmsg($channel, $this->__('Good morning'));
        }
    }

    /**
     *
     */
    public function niceevening()
    {
        $channel = self::CHANNEL;
        if (empty($channel) === false && $this->inChannel($channel)) {
            $this->getActions()->privmsg($channel, $this->__('Have a nice evening'));
        }
    }

    /**
     *
     */
    public function niceweekend()
    {
        $channel = self::CHANNEL;
        if (empty($channel) === false && $this->inChannel($channel)) {
            $this->getActions()->privmsg($channel, $this->__('Nice weekend'));
        }
    }
}
