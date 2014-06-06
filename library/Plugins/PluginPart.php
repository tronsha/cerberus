<?php

namespace Cerberus\Plugins;

use Cerberus\Plugin;

class PluginPart extends Plugin
{
    protected function init()
    {
        $this->irc->addEvent('onPrivmsg', $this);
    }

    /**
     * @param array $data
     * @return bool
     */
    public function onLoad($data)
    {
        $returnValue = parent::onLoad($data);
        if ($data !== null) {
            $this->irc->notice($data['nick'], 'New Command: !part [#channel]');
        }
        return $returnValue;
    }

    /**
     * @param array $data
     * @return bool|void
     */
    public function onPrivmsg($data)
    {
        if ($this->irc->isAdmin($data['nick'], $data['host']) === false) {
            return false;
        }
        $splitText = explode(' ', $data['text']);
        $command = array_shift($splitText);
        if ($command == '!part') {
            $channel = trim(array_shift($splitText));
            if (empty($channel) === true) {
                $channel = $data['channel'];
            }
            return $this->irc->part($channel);
        }
    }
}
