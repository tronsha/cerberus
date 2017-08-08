<?php

/*
 * Cerberus IRCBot
 * Copyright (C) 2008 - 2017 Stefan Hüsges
 *
 * This program is free software; you can redistribute it and/or modify it
 * under the terms of the GNU General Public License as published by the Free
 * Software Foundation; either version 3 of the License, or (at your option)
 * any later version.
 *
 * This program is distributed in the hope that it will be useful, but
 * WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY
 * or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License
 * for more details.
 *
 * You should have received a copy of the GNU General Public License along
 * with this program; if not, see <http://www.gnu.org/licenses/>.
 */

namespace Cerberus;

use Cerberus\Plugins\PluginAuth;
use Exception;
use SQLite3;

/**
 * Class Irc
 * @package Cerberus
 * @author Stefan Hüsges
 * @link http://www.mpcx.net/projekte/cerberus/ Project Homepage
 * @link https://github.com/tronsha/cerberus Project on GitHub
 * @link https://tools.ietf.org/html/rfc1459 Internet Relay Chat: Client Protocol - RFC1459
 * @link https://tools.ietf.org/html/rfc2812 Internet Relay Chat: Client Protocol - RFC2812
 * @license http://www.gnu.org/licenses/gpl-3.0 GNU General Public License
 */
class Irc extends Cerberus
{
    protected $server = [];
    protected $bot = [];
    protected $db = null;
    protected $dbms;
    protected $fp = false;
    protected $init = false;
    protected $run;
    protected $lastping;
    protected $nowrite;
    protected $var = [];
    protected $time = [];
    protected $config = null;
    protected $reconnect = [];
    protected $loaded = [];
    protected $pluginevents = [];
    protected $auth = null;
    protected $param = null;
    protected $event = null;
    protected $action = null;
    protected $translate = null;
    protected $cron = null;

    /**
     * @param array|null $config
     */
    public function __construct($config = null)
    {
        $this->time['script_start'] = microtime(true);
        $this->bot['pid'] = getmypid();
        $this->bot['nick'] = null;
        $this->server['network'] = null;
        $this->server['password'] = null;
        $this->reconnect['channel'] = [];
        $this->loaded['classes'] = [];
        $this->config = new Config($config);
        if (true === is_array($config)) {
            if (false === empty($config['bot']['nick'])) {
                $this->setNick($config['bot']['nick']);
            }
            if (false === empty($config['irc']['network'])) {
                $this->setNetwork($config['irc']['network']);
            }
            if (false === empty($config['irc']['password'])) {
                $this->setPassword($config['irc']['password']);
            }
            if (true === isset($config['db'])) {
                $this->setDB($config['db']);
            }
        }
        $this->translate = new Translate($this->getConfig()->getLanguage());
        $this->action = new Action($this);
        $this->event = new Event($this);
        $this->cron = new Cron;
    }

    /**
     *
     */
    public function __destruct()
    {
        if (true === $this->init) {
            $this->getEvents()->onShutdown();
            if (true === is_resource($this->fp)) {
                fclose($this->fp);
            }
            $this->getDb()->cleanupBot();
            $this->getDb()->shutdownBot();
        }
        $output = vsprintf('Execute time: %.5fs', microtime(true) - $this->time['script_start']);
        $this->getConsole()->writeln();
        $this->getConsole()->writeln('<info>' . $output . '</info>');
        $this->getConsole()->writeln();
    }

    /**
     * @param string $text
     * @return mixed
     */
    public function onError($text)
    {
        $this->getEvents()->onError($text);
        return $this->error($text);
    }

    /**
     * @return array
     */
    public function getVars()
    {
        return ['var' => $this->var, 'time' => $this->time];
    }

    /**
     * @param array $argv
     */
    public function setParam($argv)
    {
        $this->param = $argv;
    }

    /**
     * @param string $network
     * @return object $this
     */
    public function setNetwork($network)
    {
        $this->server['network'] = $network;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getNetwork()
    {
        if (false === isset($this->server['network'])) {
            return null;
        }
        return $this->server['network'];
    }

    /**
     * @return mixed
     */
    public function getServer()
    {
        return $this->server;
    }

    /**
     * @param string $password
     * @return object $this
     */
    public function setPassword($password)
    {
        $this->server['password'] = $password;
        return $this;
    }

    /**
     * @link http://docs.doctrine-project.org/projects/doctrine-dbal/en/latest/reference/configuration.html#driver
     * @param array $config
     * @return object $this
     */
    public function setDB($config)
    {
        switch (strtolower($config['driver'])) {
            case 'pdo_mysql':
            case 'drizzle_pdo_mysql':
            case 'mysqli':
                $this->dbms = 'mysql';
                break;
            case 'pdo_sqlite':
                $this->dbms = 'sqlite';
                break;
            case 'pdo_pgsql':
                $this->dbms = 'pg';
                break;
            case 'pdo_oci':
            case 'oci8':
                $this->dbms = 'oracle';
                break;
            case 'pdo_sqlsrv':
            case 'sqlsrv':
                $this->dbms = 'mssql';
                break;
            case 'sqlanywhere':
                $this->dbms = 'sqlanywhere';
                break;
            default:
                return false;
        }
        $this->db = new Db($config, $this);
        return $this;
    }

    /**
     * @param string|null $nick
     * @return object $this
     */
    public function setNick($nick = null)
    {
        if (null === $nick) {
            $nick = $this->randomNick();
        }
        $this->bot['nick'] = $nick;
        $this->var['me'] = $nick;
        if (true === $this->init) {
            $this->getDb()->setBotNick($nick);
        }
        return $nick;
    }

    /**
     * @return string|null
     */
    public function getNick()
    {
        if (false === isset($this->bot['nick'])) {
            return null;
        }
        return $this->bot['nick'];
    }

    /**
     * @return bool
     */
    public function init()
    {
        if (null === $this->getNetwork() || null === $this->db) {
            return false;
        }
        $this->dbConnect();
        $this->getDb()->createBot($this->bot['pid'], $this->getNick());
        if (null === $this->getConfig()->getVersion('bot')) {
            $this->getConfig()->setVersion('php', phpversion());
            $this->getConfig()->setVersion('os', php_uname('s') . ' ' . php_uname('r'));
            $this->getConfig()->setVersion('bot', 'PHP ' . $this->getConfig()->getVersion('php') . ' - ' . $this->getConfig()->getVersion('os'));
            if ('mysql' === $this->dbms || 'pg' === $this->dbms) {
                $this->getConfig()->setVersion('sql', $this->getDb()->getDbVersion());
                $this->getConfig()->setVersion('bot', $this->getConfig()->getVersion('bot') . ' - ' . $this->getConfig()->getDbms($this->dbms) . ' ' . $this->getConfig()->getVersion('sql'));
            } elseif ('sqlite' === $this->dbms) {
                $version = SQLite3::version();
                $this->getConfig()->setVersion('sql', $version['versionString']);
                $this->getConfig()->setVersion('bot', $this->getConfig()->getVersion('bot') . ' - ' . $this->getConfig()->getDbms($this->dbms) . ' ' . $this->getConfig()->getVersion('sql'));
            }
        }
        if (true === is_array($this->getConfig()->getPluginsAutoload())) {
            $this->autoloadPlugins();
        }
        $this->init = true;
        return true;
    }

    /**
     * @return string
     */
    public static function randomNick()
    {
        $consonant = str_split('bcdfghjklmnpqrstvwxyz');
        $vowel = str_split('aeiou');
        $nick = '';
        for ($i = 0; $i < 3; $i++) {
            $nick .= $consonant[mt_rand(0, 20)] . $vowel[mt_rand(0, 4)];
        }
        return ucfirst($nick);
    }

    /**
     *
     */
    protected function dbConnect()
    {
        $this->getDb()->connect();
    }

    /**
     * @return bool|void
     */
    public function connect()
    {
        if (false === $this->init) {
            if (false === $this->init()) {
                return false;
            }
        }
        $n = intval($this->getDb()->getServerCount($this->getNetwork()));
        $i = 0;
        $repeat = true;
        if (0 === $n) {
            $this->sysinfo('No ' . $this->getNetwork() . ' server');
            return false;
        }
        while ($repeat) {
            $this->server = $this->getDb()->getServerData($this->server, $i);
            $this->sysinfo('Try to connect to ' . $this->server['host'] . ':' . $this->server['port']);
            try {
                $this->fp = fsockopen($this->server['ip'], $this->server['port'], $errno, $errstr);
            } catch (Exception $e) {
                $this->error($e->getMessage());
            }
            if (false === $this->fp) {
                $this->error($errstr);
                $this->log('socket: ' . $errstr, 'error');
                $this->sysinfo('Connection failed');
                $i++;
            } else {
                $this->sysinfo('Connection success');
                $repeat = false;
            }
            if ($i === $n) {
                $this->sysinfo('All attempts failed');
                return false;
            }
        }
        $this->time['irc_connect'] = microtime(true);
        if (null !== $this->server['password']) {
            $this->write('PASS ' . $this->server['password']);
        }
        if (null === $this->getNick()) {
            $this->setNick();
        }
        $this->write('USER cerberus * * :' . $this->getConfig()->getName());
        $this->write('NICK ' . $this->getNick());
        $this->lastping = time();
        $this->nowrite = true;
        $this->run = true;
        $this->getDb()->cleanupBot(null, ['send']);
        $this->preform();
        $this->getEvents()->onConnect();
        return $this->run();
    }

    /**
     *
     */
    protected function reconnect()
    {
        $this->reconnect['channel'] = [];
        $channels = $this->getDb()->getJoinedChannels();
        foreach ($channels as $channel) {
            $this->reconnect['channel'][] = $channel['channel'];
        }
        fclose($this->fp);
        $this->connect();
    }

    /**
     *
     */
    protected function preform()
    {
        $preform = $this->getDb()->getPreform($this->getNetwork());
        foreach ($preform as $command) {
            $this->getDb()->addWrite($command['text'], $command['priority']);
            preg_match('/^join\s+(#[^\s]+)/i', $command['text'], $matches);
            if (true === isset($matches[1])) {
                unset($matches[0]);
                $this->reconnect['channel'] = array_diff($this->reconnect['channel'], $matches);
            }
        }
        foreach ($this->reconnect['channel'] as $channel) {
            $this->getActions()->join($channel);
        }
    }

    /**
     * @param string $errstr
     * @return mixed
     */
    public function sqlError($errstr)
    {
        $this->error($errstr);
        $this->log('sql: ' . $errstr, 'error');
        return $errstr;
    }

    /**
     * @param string $text
     * @param string $type
     * @return null
     */
    protected function log($text, $type)
    {
        if (true !== $this->getConfig()->getLogfile($type)) {
            return null;
        }

        if (true === $this->getConfig()->getDailylogfile()) {
            $file = $type . '_log_' . date('Ymd', time()) . '.txt';
        } else {
            $file = $type . '_log.txt';
        }

        $handle = @fopen(realpath($this->getConfig()->getLogfiledirectory()) . '/' . $file, 'a+');
        if (false !== $handle) {
            fwrite($handle, date('d.m.Y H:i:s', time()) . ' >>>> ' . $text . PHP_EOL);
            fflush($handle);
            fclose($handle);
        }
    }

    /**
     * @param string $text
     */
    protected function write($text)
    {
        $output = trim($text);
        if ('\\' === substr($output, -1)) {
            $output .= ' ';
        }
        $outputConsole = (0 === strpos($output, 'PRIVMSG NickServ :IDENTIFY') ? preg_replace('/\S/', '*', $output) : $output);
        $this->getConsole()->writeln($this->getConsole()->prepare($outputConsole, true, null, true, true, 0));
        if (true === is_resource($this->fp)) {
            fwrite($this->fp, $output . PHP_EOL);
        }
        preg_match('/^([^ ]+).*?$/i', $text, $matches);
        $command = isset($matches[1]) ? $matches[1] : '';
        if ('quit' === strtolower($command)) {
            $this->run = false;
        }
    }

    /**
     * @return string
     */
    protected function read()
    {
        stream_set_timeout($this->fp, 10);
        try {
            $input = fgets($this->fp, 4096);
        } catch (Exception $e) {
            $this->error($e->getMessage());
        }
        $text = trim($input);
        if ('' !== $text) {
            if ('\\' === substr($text, -1)) {
                $text .= ' ';
            }
            $this->log($text, 'socket');
            $this->getConsole()->writeln($this->getConsole()->prepare($text, true, null, true, true, 0));
        }
        return $input;
    }

    /**
     *
     */
    protected function send()
    {
        static $lastSend;
        if (2.0 > (microtime(true) - $lastSend)) {
            return;
        }
        $send = $this->getDb()->getWrite();
        if (false !== $send) {
            if ('' !== $send['text']) {
                preg_match_all("/\%([a-z0-9_]*)/i", $send['text'], $array, PREG_PATTERN_ORDER);
                foreach ($array[1] as $value) {
                    if (true === array_key_exists($value, $this->var)) {
                        $send['text'] = preg_replace(
                            '/%' . $value . '(\s|$)/i',
                            $this->var[$value] . '\\1',
                            $send['text']
                        );
                    }
                }
                $this->write($send['text']);
            }
            $this->getDb()->removeWrite($send['id']);
            preg_match("/^([^\ ]+)(?:\ ([^\:].*?))?(?:\ \:(.*?))?(?:\r)?$/i", $send['text'], $matches);
            $command = (true === isset($matches[1]) ? $matches[1] : '');
            $rest = (true === isset($matches[2]) ? $matches[2] : '');
            $text = (true === isset($matches[3]) ? $matches[3] : '');
            $this->getDb()->setLog(
                $send['text'],
                $command,
                $this->getNetwork(),
                $this->getNick(),
                $rest,
                $text,
                '>'
            );
            $lastSend = microtime(true);
        }
    }

    /**
     * @return bool|void
     */
    public function run()
    {
        while (!feof($this->fp)) {
            $input = $this->read();
            if ('' !== trim($input)) {
                if (false === $this->getDb()->ping()) {
                    $this->getDb()->close();
                    $this->getDb()->connect();
                }
                if (':' !== $input{0}) {
                    if (false !== strpos(strtoupper($input), 'PING')) {
                        $this->lastping = time();
                        $output = str_replace('PING', 'PONG', $input);
                        $this->write($output);
                        unset($output);
                        $this->getDb()->setPing();
                    }
                } else {
                    $this->command($input);
                }
            }
            $this->getEvents()->onTick();
            if (false === $this->nowrite && 10 < (floor(microtime(true) - $this->time['irc_connect']))) {
                $this->send();
            }
            unset($input);
            $this->msleep(8);
            if (600 < (time() - $this->lastping)) {
                break;
            }
            if (false === $this->run) {
                return true;
            }
        }
        $this->sysinfo('Connection to server lost');
        $this->msleep(20000);
        $this->reconnect();
    }

    /**
     * @param string $input
     */
    protected function command($input)
    {
        preg_match(
            "/^\:(?:([^\!\ \:]+)\!)?([^\!\ ]+)\ ([^\ ]+)(?:\ ([^\:].*?))?(?:\ \:(.*?))?(?:\r)?$/i",
            $input,
            $matches
        );
        $all = isset($matches[0]) ? $matches[0] : '';
        $nick = isset($matches[1]) ? $matches[1] : '';
        $host = isset($matches[2]) ? $matches[2] : '';
        $command = isset($matches[3]) ? $matches[3] : '';
        $rest = isset($matches[4]) ? $matches[4] : '';
        $text = isset($matches[5]) ? $matches[5] : '';
        if ('001' === $command) {
            $this->nowrite = false;
        }
        $eventName = 'on' . ucfirst(strtolower($command));
        $this->getEvents()->$eventName($nick, $host, $rest, $text);
        $this->getDb()->setLog($all, $command, $this->getNetwork(), $nick, $rest, $text, '<');
    }

    /**
     *
     */
    public function otherNick()
    {
        if (false === $this->nowrite) {
            return;
        }
        $nick = $this->setNick(null);
        $this->write('NICK ' . $nick);
    }

    /**
     *
     */
    public function channelList()
    {
        if (true === $this->loadPlugin('list')) {
            $this->getActions()->channelList();
            return true;
        }
        return false;
    }

    /**
     * @param string $channel
     * @param string|null $user
     * @return bool|null
     */
    public function inChannel($channel, $user = null)
    {
        if (null === $user) {
            foreach ($this->getDb()->getJoinedChannels() as $value) {
                if ($value['channel'] === $channel) {
                    return true;
                }
            }
            return false;
        } elseif (false === empty($channel) && false === empty($user)) {
            if (0 < count($this->getDb()->getUserInChannel($channel, $user))) {
                return true;
            }
            return false;
        }
        return null;
    }

    /**
     * @param PluginAuth $object
     */
    public function registerAuth(PluginAuth $object)
    {
        $this->auth = $object;
    }

    /**
     * @param string $auth
     * @return string
     */
    public function getAuthLevel($auth)
    {
        return $this->getDb()->getAuthLevel($this->getNetwork(), $auth);
    }

    /**
     * @param string $nick
     * @param string $host
     * @return mixed
     */
    public function isAdmin($nick, $host)
    {
        if (null !== $this->auth && true === is_a($this->auth, 'Cerberus\Plugins\PluginAuth')) {
            return $this->auth->isAdmin($nick, $host);
        }
        return false;
    }

    /**
     * @param string $nick
     * @param string $host
     * @return mixed
     */
    public function isMember($nick, $host)
    {
        if (null !== $this->auth && true === is_a($this->auth, 'Cerberus\Plugins\PluginAuth')) {
            return $this->auth->isMember($nick, $host);
        }
        return false;
    }

    /**
     *
     */
    public function autoloadPlugins()
    {
        $this->loadPlugin('auth');
        foreach ($this->getConfig()->getPluginsAutoload() as $plugin) {
            $this->loadPlugin($plugin);
        }
    }

    /**
     * @param string $name
     * @param array|null $data
     * @return bool
     */
    public function loadPlugin($name, $data = null)
    {
        $pluginClass = 'Cerberus\\Plugins\\Plugin' . ucfirst($name);

        if (true === class_exists($pluginClass)) {
            if (false === array_key_exists($pluginClass, $this->loaded['classes'])) {
                $plugin = new $pluginClass($this);
                if (true === is_subclass_of($pluginClass, 'Cerberus\\Plugin')) {
                    $this->loaded['classes'][$pluginClass] = $plugin->onLoad($data);
                    $this->sysinfo('Load Plugin: ' . $name);
                    return true;
                } else {
                    $this->sysinfo($name . ' isn\'t a PluginClass.');
                    return false;
                }
            } else {
                $this->sysinfo('Plugin "' . $name . '" is already loaded.');
                return false;
            }
        } else {
            $this->sysinfo($name . ' don\'t exists.');
            return false;
        }
    }

    /**
     * @param string $event
     * @param array $data
     * @throws Exception
     */
    public function runPluginEvent($event, $data)
    {
        if (true === array_key_exists($event, $this->pluginevents)) {
            for ($priority = 10; $priority > 0; $priority--) {
                if (true === array_key_exists($priority, $this->pluginevents[$event])) {
                    foreach ($this->pluginevents[$event][$priority] as $pluginArray) {
                        $pluginObject = $pluginArray['object'];
                        $pluginMethod = $pluginArray['method'];
                        if (true === method_exists($pluginObject, $pluginMethod)) {
                            $pluginObject->$pluginMethod($data);
                        } else {
                            throw new Exception('The Class ' . get_class($pluginObject) . ' has not the method ' . $pluginMethod . '.');
                        }
                    }
                }
            }
        }
    }

    /**
     * @param string $event
     * @param object $object
     * @param string|null $method
     * @param int $priority
     * @throws Exception
     */
    public function addPluginEvent($event, $object, $method = null, $priority = 5)
    {
        if (false === in_array($event, $this->getEvents()->getEventList(), true)) {
            throw new Exception('The event ' . $event . ' not exists.');
        }
        $method = (null === $method ? $event : $method);
        $pluginArray = ['object' => $object, 'method' => $method];
        $this->pluginevents[$event][$priority][] = $pluginArray;
    }

    /**
     * @param string $event
     * @param object $object
     * @return int
     */
    public function removePluginEvent($event, $object)
    {
        $count = 0;
        $className = get_class($object);
        if (array_key_exists($event, $this->pluginevents)) {
            foreach ($this->pluginevents[$event] as $priorityKey => $priorityValue) {
                foreach ($priorityValue as $key => $pluginArray) {
                    if (get_class($pluginArray['object']) === $className) {
                        unset($this->pluginevents[$event][$priorityKey][$key]);
                        $count++;
                    }
                }
            }
        }
        return $count;
    }

    /**
     * @return Action|null
     */
    public function getActions()
    {
        return $this->action;
    }

    /**
     * @return Event|null
     */
    public function getEvents()
    {
        return $this->event;
    }

    /**
     * @return Db|null
     */
    public function getDb()
    {
        return $this->db;
    }

    /**
     * @return Config|null
     */
    public function getConfig()
    {
        return $this->config;
    }

    /**
     * @param string $text
     * @param array $array
     * @param mixed $lang
     * @return string
     */
    public function __($text, $array = [], $lang = null)
    {
        return $this->translate->__($text, $array, $lang);
    }

    /**
     * @param string $lang
     */
    public function setLanguage($lang)
    {
        $this->translate->setLanguage($lang);
    }

    /**
     * @param array $translations
     */
    public function setTranslations($translations = [])
    {
        $this->translate->setTranslations($translations);
    }

    /**
     * @param int $minute
     * @param int $hour
     * @param int $day_of_month
     * @param int $month
     * @param int $day_of_week
     */
    public function runCron($minute, $hour, $day_of_month, $month, $day_of_week)
    {
        $this->cron->run($minute, $hour, $day_of_month, $month, $day_of_week);
    }

    /**
     * @param string $cronString
     * @param string $object
     * @param string $method
     * @return int
     */
    public function addCron($cronString, $object, $method)
    {
        return $this->cron->add($cronString, $object, $method);
    }

    /**
     * @param int $id
     * @return bool
     */
    public function removeCron($id)
    {
        return $this->cron->remove($id);
    }
}
