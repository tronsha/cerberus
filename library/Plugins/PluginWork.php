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

class PluginWork extends Plugin
{
    const CHANNEL = '#company';
    const NICK_WORK = 'user^work';
    const NICK_HOME = 'user^home';

    protected function init()
    {
        $this->irc->addCron('0 8 * * 1-5', $this, 'goodmorning');
        $this->irc->addCron('0 17 * * 1-4', $this, 'niceevening');
        $this->irc->addCron('30 14 * * 5', $this, 'niceweekend');
    }

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

    public function onLoad($data)
    {
        $returnValue = parent::onLoad($data);
        return $returnValue;
    }

    public function goodmorning()
    {
        $this->irc->getAction()->nick(self::NICK_WORK);
        $this->irc->getAction()->join(self::CHANNEL);
        if ($this->irc->inChannel(self::CHANNEL)) {
            $this->irc->getAction()->privmsg(self::CHANNEL, $this->irc->__('Good morning'));
        }
    }

    public function niceevening()
    {
        if ($this->irc->inChannel(self::CHANNEL)) {
            $this->irc->getAction()->privmsg(self::CHANNEL, $this->irc->__('Have a nice evening'));
            $this->irc->getAction()->part(self::CHANNEL);
        }
        $this->irc->getAction()->nick(self::NICK_HOME);
    }

    public function niceweekend()
    {
        if ($this->irc->inChannel(self::CHANNEL)) {
            $this->irc->getAction()->privmsg(self::CHANNEL, $this->irc->__('Nice weekend'));
            $this->irc->getAction()->part(self::CHANNEL);
        }
        $this->irc->getAction()->nick(self::NICK_HOME);
    }
}
