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
