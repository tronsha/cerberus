<?php

namespace Cerberus\Plugins;

class PluginJoin extends Plugin
{
    protected function init()
    {
        $this->irc->addEvent('onPrivmsg', $this);
    }

    public function onLoad($data)
    {
        $returnValue = parent::onLoad($data);
        if ($data !== null) {
            $this->irc->notice($data['nick'], 'New Command: !join [#channel]');
        }
        return $returnValue;
    }

    public function onPrivmsg($data)
    {
        $splitText = explode(' ', $data['text']);
        $command = array_shift($splitText);
        if ($command == '!join') {
            $this->irc->join(array_shift($splitText));
        }
    }
}
