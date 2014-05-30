<?php

namespace Cerberus\Plugins;

use Cerberus\Plugin;

class PluginCrypt extends Plugin
{
    private $cryptkey = array();

    protected function init()
    {
        if (extension_loaded('mcrypt')) {
            $this->irc->addEvent('onPrivmsg', $this, 10);
        } else {
            $this->irc->sysinfo('Your version of PHP does NOT have the mcrypt extension loaded.');
        }
    }

    /**
     * @param array $data
     * @return bool
     */
    public function onLoad($data)
    {
        $returnValue = parent::onLoad($data);
        if ($data !== null) {
            $this->irc->notice($data['nick'], 'New Command: !cryptkey [#channel] [key]');
        }
        return $returnValue;
    }

    /**
     * @param array $data
     */
    public function onPrivmsg(&$data)
    {
        $splitText = explode(' ', $data['text']);
        $command = array_shift($splitText);
        if ($command == '+OK') {
            $key = empty($this->cryptkey[$data['channel']]) ? '123456' : $this->cryptkey[$data['channel']];
            $encodedTextBase64 = str_replace('*', '', array_shift($splitText));
            $encodedText = base64_decode($encodedTextBase64);
            $iv = substr($encodedText, 0, 8);
            $encodedText = substr($encodedText, 8);
            $plaintext = mcrypt_decrypt(MCRYPT_BLOWFISH, $key, $encodedText, MCRYPT_MODE_CBC, $iv);
            $data['text'] = trim($plaintext);
        } elseif (strtolower($command) == '!cryptkey') {
            $channel = array_shift($splitText);
            $key = array_shift($splitText);
            $this->cryptkey[$channel] = $key;
        }
    }
}
