<?php

class plugin
{
    const AUTH_NONE = 1;
    const AUTH_MEMBER = 2;
    const AUTH_ADMIN = 3;

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