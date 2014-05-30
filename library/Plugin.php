<?php

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
    public function __construct($irc)
    {
        $this->irc = $irc;
        $this->init();
    }

    abstract protected function init();

    public function onLoad($data)
    {
        $this->irc->notice($data['nick'], 'Load: ' . get_called_class());
        return true;
    }
}
