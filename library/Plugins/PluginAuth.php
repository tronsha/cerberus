<?php

namespace Cerberus\Plugins;

use Cerberus\Plugin;

/**
 * Class PluginAuth
 * @package Cerberus\Plugins
 * @author Stefan Hüsges
 * @link https://freenode.net/faq.shtml#registering
 * @link https://www.quakenet.org/help/q-commands/auth
 * @link http://tools.ietf.org/html/rfc2812
 */

class PluginAuth extends Plugin
{
    private $auth = array();

    protected function init()
    {
        $this->irc->addEvent('on311', $this);
        $this->irc->addEvent('on330', $this);
        $this->irc->addEvent('onPrivmsg', $this);
        $this->irc->addEvent('onNick', $this);
        $this->irc->addEvent('onQuit', $this);
//        $this->irc->addEvent('onJoin', $this, 10);
    }

    /**
     * @param array $data
     * @return bool
     */
    public function onLoad($data)
    {
        $returnValue = parent::onLoad($data);
        $this->irc->registerAuth($this);
        return $returnValue;
    }

    /**
     * @param string $nick
     * @param string $host
     * @return bool
     */
    public function isAdmin($nick, $host)
    {
        if (isset($this->auth[$nick]) === true) {
            if (isset($this->auth[$nick]['host']) === true && $this->auth[$nick]['host'] == $host) {
                if (isset($this->auth[$nick]['level']) === true && $this->auth[$nick]['level'] >= self::AUTH_ADMIN) {
                    return true;
                }
            }
        }
        return false;
    }

    /**
     * @param string $nick
     * @param string $host
     * @return bool
     */
    public function isMember($nick, $host)
    {
        if (isset($this->auth[$nick]) === true) {
            if (isset($this->auth[$nick]['host']) === true && $this->auth[$nick]['host'] == $host) {
                if (isset($this->auth[$nick]['level']) === true && $this->auth[$nick]['level'] >= self::AUTH_MEMBER) {
                    return true;
                }
            }
        }
        return false;
    }

    /**
     * @param array $data
     * @return mixed|void
     */
    public function onPrivmsg($data)
    {
        $splitText = explode(' ', $data['text']);
        $command = array_shift($splitText);
        if ($command == '!auth') {
            return $this->irc->whois($data['nick']);
        }
        if ($command == '!debug') {
            return print_r($this, true);
        }
    }

    /**
     * @param array $data
     */
    public function onNick($data)
    {
        if (array_key_exists($data['nick'], $this->auth) === true) {
            $this->auth[$data['text']] = $this->auth[$data['nick']];
        }
        unset($this->auth[$data['nick']]);
    }

    /**
     * @param array $data
     */
    public function onQuit($data)
    {
        unset($this->auth[$data['nick']]);
    }

    /**
     * @param array $data
     */
    public function on311($data)
    {
        $this->auth[$data['nick']]['host'] = $data['host'];
    }

    /**
     * @param array $data
     */
    public function on330($data)
    {
        $authLevel = $this->irc->getAuthLevel($data['auth']);
        if ($authLevel == 'admin') {
            $this->auth[$data['nick']]['level'] = self::AUTH_ADMIN;
        } elseif ($authLevel == 'user') {
            $this->auth[$data['nick']]['level'] = self::AUTH_MEMBER;
        }
    }
}