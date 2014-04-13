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

    public function myecho($nick, $host, $channel, $text)
    {
        return array('do' => 'privmsg', 'content' => $text, 'userrights' => self::AUTH_MEMBER);
    }
}