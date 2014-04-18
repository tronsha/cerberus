<?php

class PluginExample extends Plugin
{
    protected function init()
    {
        $this->irc->addEvent('onPrivmsg', $this);
        $this->irc->addEvent('onJoin', $this);
    }

    public function onLoad($data)
    {
        $returnValue = parent::onLoad($data);

        $this->irc->notice($data['nick'], 'New Command: !echo');

        return $returnValue;
    }

    public function onPrivmsg($data)
    {
        $splitText = explode(' ', $data['text']);
        $command = array_shift($splitText);
        if ($command == '!echo') {
            if ($this->irc->authorizations(trim($data['host']), self::AUTH_MEMBER)) {
                $this->irc->notice($data['nick'], implode(' ', $splitText));
            }
        }
    }

    public function onJoin($data)
    {
//        if ($this->irc->authorizations(trim($data['host']), self::AUTH_MEMBER)) {
            $this->irc->privmsg($data['channel'], 'Hallo ' . $data['nick'] . ' :-)');
//        }
    }
}
