<?php

namespace Cerberus\Plugins;

class PluginQuit extends Plugin
{
    protected function init()
    {
        $this->irc->addEvent('onPrivmsg', $this);
    }

    public function onLoad($data)
    {
        $returnValue = parent::onLoad($data);
        if ($data !== null) {
            $this->irc->notice($data['nick'], 'New Command: !die');
        }
        return $returnValue;
    }

    public function onPrivmsg($data)
    {
        $splitText = explode(' ', $data['text']);
        $command = array_shift($splitText);
        if ($command == '!die') {
            $this->irc->quit('Client Quit');
        }
    }
}
