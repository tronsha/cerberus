<?php

/*   Cerberus IRCBot
 *   Copyright (C) 2008 - 2014 Stefan Hüsges
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

abstract class Plugin extends Cerberus
{
    /**
     * @var Irc
     */
    protected $irc;

    /**
     * @param Irc $irc
     */
    public function __construct(Irc $irc)
    {
        $this->irc = $irc;
        $this->init();
    }

    /**
     * abstract method for consructor logic
     */
    abstract protected function init();

    /**
     * @param array $data
     * @return bool
     */
    public function onLoad($data)
    {
        if (isset($data) === true) {
            $this->irc->notice($data['nick'], 'Load: ' . get_called_class());
        }
        return true;
    }
}
