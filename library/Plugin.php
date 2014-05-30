<?php

namespace Cerberus;

class Plugin extends Cerberus
{
    protected $irc = null;

    public function __construct(&$irc)
    {
        $this->irc =& $irc;
        $this->init();
    }

    protected function init()
    {
    }

    public function onLoad($data)
    {
        $this->irc->notice($data['nick'], 'Load: ' . get_called_class());
        return true;
    }
}
