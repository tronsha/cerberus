<?php

/*
 * Cerberus IRCBot
 * Copyright (C) 2008 - 2016 Stefan Hüsges
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
 * Class PluginTest
 * @package Cerberus\Plugins
 * @author Stefan Hüsges
 *
 * This is a helper plugin to test functions with PHPUnit
 *
 */
class PluginTest extends Plugin
{
    /**
     *
     */
    protected function init()
    {
        $this->addEvent('onPrivmsg', 'doEcho');
        $this->addEvent('onNotice', 'doEcho');
        $this->addEvent('onJoin', 'doEcho');
        $this->addEvent('onPart', 'doEcho');
        $this->addEvent('onQuit', 'doEcho');
        $this->addEvent('onTopic', 'doEcho');
        $this->addEvent('onKick', 'doEcho');
        $this->addEvent('onInvite', 'doEcho');
        $this->addEvent('onNick', 'doEcho');
        $this->addEvent('onMode', 'doEcho');
        $this->addEvent('on401', 'doEcho');
        $this->addEvent('on403', 'doEcho');
        $this->addEvent('on404', 'doEcho');
        $this->addEvent('on431', 'doEcho');
        $this->addEvent('on432', 'doEcho');
        $this->addEvent('on433', 'doEcho');
        $this->addEvent('on442', 'doEcho');
        $this->addEvent('on443', 'doEcho');
        $this->addEvent('on470', 'doEcho');
        $this->addEvent('on471', 'doEcho');
        $this->addEvent('on473', 'doEcho');
        $this->addEvent('on474', 'doEcho');
        $this->addEvent('on475', 'doEcho');
        $this->addEvent('on477', 'doEcho');
        $this->addEvent('on479', 'doEcho');
        $this->addEvent('on482', 'doEcho');
        $this->addEvent('on301', 'doEcho');
        $this->addEvent('on305', 'doEcho');
        $this->addEvent('on306', 'doEcho');
        $this->addEvent('on311', 'doEcho');
        $this->addEvent('on312', 'doEcho');
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
    public function doEcho($data)
    {
        ksort($data);
        echo serialize($data);
    }
}
