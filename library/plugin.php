<?php

class Plugin extends Cerberus
{
    protected $commands = null;

    public function __construct()
    {
        $this->init();
    }

    protected function init()
    {

    }

    public function getCommands()
    {
        return $this->commands;
    }
}