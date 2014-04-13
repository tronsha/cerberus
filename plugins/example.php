<?php

class pluginExample extends plugin
{
    protected function init()
    {
        parent::init();
        $this->commands = array(
            array(
                'command' => '!echo',
                'method' => 'myecho',
            ),
        );
    }

    public function myecho($text)
    {
        return array('do' => 'privmsg', 'content' => $text);
    }
}