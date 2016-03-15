<?php

/*
 * Cerberus IRCBot
 * Copyright (C) 2008 - 2016 Stefan Hüsges
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
        $this->time['script_start'] = $this->getMicrotime();
        $this->bot['pid'] = getmypid();
        $this->bot['nick'] = null;
        $this->server['network'] = null;
        $this->server['password'] = null;
        $this->reconnect['channel'] = [];
        $this->loaded['classes'] = [];
        $this->config = new Config($config);
        if (is_array($config)) {
            if (!empty($config['bot']['nick'])) {
                $this->setNick($config['bot']['nick']);
            }
            if (!empty($config['irc']['network'])) {
                $this->setNetwork($config['irc']['network']);
            }
            if (!empty($config['irc']['password'])) {
                $this->setPassword($config['irc']['password']);
            }
            if (isset($config['db'])) {
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
        if ($this->init === true) {
            $this->getEvents()->onShutdown();
            if (is_resource($this->fp) === true) {
                fclose($this->fp);
            }
            $this->getDb()->cleanupBot();
            $this->getDb()->shutdownBot();
        }
        $output = vsprintf('Execute time: %.5fs', $this->getMicrotime() - $this->time['script_start']);
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
        if (isset($this->server['network']) === false) {
            return null;
        }
        return $this->server['network'];
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
        if ($nick === null) {
            $nick = $this->randomNick();
        }
        $this->bot['nick'] = $nick;
        $this->var['me'] = $nick;
        if ($this->init === true) {
            $this->getDb()->setBotNick($nick);
        }
        return $nick;
    }

    /**
     * @return string|null
     */
    public function getNick()
    {
        if (isset($this->bot['nick']) === false) {
            return null;
        }
        return $this->bot['nick'];
    }

    /**
     * @return bool
     */
    public function init()
    {
        if ($this->getNetwork() === null || $this->db === null) {
            return false;
        }
        $this->dbConnect();
        $this->getDb()->createBot($this->bot['pid'], $this->getNick());
        if ($this->getConfig()->getVersion('bot') === null) {
            $this->getConfig()->setVersion('php', phpversion());
            $this->getConfig()->setVersion('os', php_uname('s') . ' ' . php_uname('r'));
            $this->getConfig()->setVersion('bot', 'PHP ' . $this->getConfig()->getVersion('php') . ' - ' . $this->getConfig()->getVersion('os'));
            if ($this->dbms === 'mysql' || $this->dbms === 'pg') {
                $this->getConfig()->setVersion('sql', $this->getDb()->getDbVersion());
                $this->getConfig()->setVersion('bot', $this->getConfig()->getVersion('bot') . ' - ' . $this->getConfig()->getDbms($this->dbms) . ' ' . $this->getConfig()->getVersion('sql'));
            } elseif ($this->dbms === 'sqlite') {
                $version = SQLite3::version();
                $this->getConfig()->setVersion('sql', $version['versionString']);
                $this->getConfig()->setVersion('bot', $this->getConfig()->getVersion('bot') . ' - ' . $this->getConfig()->getDbms($this->dbms) . ' ' . $this->getConfig()->getVersion('sql'));
            }
        }
        if (is_array($this->getConfig()->getPluginsAutoload()) === true) {
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
        if ($this->init === false) {
            if ($this->init() === false) {
                return false;
            }
        }
        $n = $this->getDb()->getServerCount($this->getNetwork());
        $i = 0;
        $repeat = true;
        if ($n === 0) {
            $this->sysinfo('No ' . $this->getNetwork() . ' server');
            return false;
        }
        while ($repeat) {
            $this->server = $this->getDb()->getServerData($this->server, $i);
            $this->sysinfo('Try to connect to ' . $this->server['host'] . ':' . $this->server['port']);
            try {
                $this->fp = fsockopen(($this->server['ip']), $this->server['port'], $errno, $errstr);
            } catch (Exception $e) {
                $this->error($e->getMessage());
            }
            if ($this->fp === false) {
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
        $this->time['irc_connect'] = $this->getMicrotime();
        if ($this->server['password'] !== null) {
            $this->write('PASS ' . $this->server['password']);
        }
        if ($this->getNick() === null) {
            $this->setNick();
        }
        $this->write('USER PHP' . str_replace('.', '', phpversion()) . ' * * :' . $this->getConfig()->getName());
        $this->write('NICK ' . $this->getNick());
        $this->lastping = time();
        $this->nowrite = true;
        $this->run = true;
        $this->getDb()->cleanupBot();
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
            $this->getDb()->addWrite($command['text']);
            preg_match('/join\s+(#[^\s]+)/i', $command['text'], $matches);
            if (isset($matches[1])) {
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
        if ($this->getConfig()->getLogfile($type) !== true) {
            return null;
        }

        if ($this->getConfig()->getDailylogfile() === true) {
            $file = $type . '_log_' . date('Ymd', time()) . '.txt';
        } else {
            $file = $type . '_log.txt';
        }

        $handle = @fopen(realpath($this->getConfig()->getLogfiledirectory()) . '/' . $file, 'a+');
        if ($handle !== false) {
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
        if (substr($output, -1) === '\\') {
            $output .= ' ';
        }
        $this->getConsole()->writeln($this->getConsole()->prepare($output, true, null, true, true, 0));
        fwrite($this->fp, $output . PHP_EOL);
        preg_match('/^([^ ]+).*?$/i', $text, $matches);
        $command = isset($matches[1]) ? $matches[1] : '';
        if (strtolower($command) === 'quit') {
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
        if ($text !== '') {
            if (substr($text, -1) === '\\') {
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
        $send = $this->getDb()->getWrite();
        if ($send !== false) {
            if ($send['text'] !== '') {
                preg_match_all("/\%([a-z0-9_]*)/i", $send['text'], $array, PREG_PATTERN_ORDER);
                foreach ($array[1] as $value) {
                    if (array_key_exists($value, $this->var)) {
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
            $command = isset($matches[1]) ? $matches[1] : '';
            $rest = isset($matches[2]) ? $matches[2] : '';
            $text = isset($matches[3]) ? $matches[3] : '';

            $this->getDb()->setLog(
                $send['text'],
                $command,
                $this->getNetwork(),
                $this->getNick(),
                $rest,
                $text,
                '>'
            );
        }
    }

    /**
     * @return bool|void
     */
    public function run()
    {
        while (!feof($this->fp)) {
            $input = $this->read();
            if (trim($input) !== '') {
                if ($this->getDb()->ping() === false) {
                    $this->getDb()->close();
                    $this->getDb()->connect();
                }
                if ($input{0} !== ':') {
                    if (strpos(strtoupper($input), 'PING') !== false) {
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
            if ($this->nowrite === false && floor($this->getMicrotime() - $this->time['irc_connect']) > 10) {
                $this->send();
            }
            unset($input);
            $this->msleep(8);
            if ((time() - $this->lastping) > 600) {
                break;
            }
            if ($this->run === false) {
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
        if (preg_match('/^([2345])[0-9][0-9]$/', $command, $matches)) {
            if (intval($matches[1]) === 2 || intval($matches[1]) === 3) {
                $this->getEvents()->rpl($command, $rest, $text);
            }
            if (intval($matches[1]) === 4 || intval($matches[1]) === 5) {
                $this->getEvents()->err($command, $rest, $text);
            }
        } else {
            switch ($command) {
                case '001':
                    $this->nowrite = false;
                    break;
                case 'PRIVMSG':
                    $this->getEvents()->onPrivmsg($nick, $host, $rest, $text);
                    break;
                case 'NOTICE':
                    $this->getEvents()->onNotice($nick, $text);
                    break;
                case 'JOIN':
                    $this->getEvents()->onJoin($nick, ($rest !== '' ? $rest : $text));
                    break;
                case 'PART':
                    $this->getEvents()->onPart($nick, $rest);
                    break;
                case 'QUIT':
                    $this->getEvents()->onQuit($nick);
                    break;
                case 'KICK':
                    $this->getEvents()->onKick($nick, $rest, $text);
                    break;
                case 'NICK':
                    $this->getEvents()->onNick($nick, $text);
                    break;
                case 'MODE':
                    $this->getEvents()->onMode($rest);
                    break;
                case 'TOPIC':
                    $this->getEvents()->onTopic($rest, $text);
                    break;
                case 'INVITE':
                    $this->getEvents()->onInvite($nick, $rest, $text);
                    break;
            }
        }
        $this->getDb()->setLog($all, $command, $this->getNetwork(), $nick, $rest, $text, '<');
    }

    /**
     *
     */
    public function otherNick()
    {
        if ($this->nowrite === false) {
            return;
        }
        $nick = $this->setNick(null);
        $this->write('NICK ' . $nick);
    }

    /**
     *
     */
    public function channellist()
    {
    }

    /**
     * @param string $channel
     * @param string|null $user
     * @return bool|null
     */
    public function inChannel($channel, $user = null)
    {
        if ($user === null) {
            foreach ($this->getDb()->getJoinedChannels() as $value) {
                if ($value['channel'] === $channel) {
                    return true;
                }
                return false;
            }
        } elseif (empty($channel) === false && empty($user) === false) {
            if (count($this->getDb()->getUserInChannel($channel, $user)) > 0) {
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
        if ($this->auth !== null && is_a($this->auth, 'Cerberus\Plugins\PluginAuth')) {
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
        if ($this->auth !== null && is_a($this->auth, 'Cerberus\Plugins\PluginAuth')) {
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
     */
    public function loadPlugin($name, $data = null)
    {
        $pluginClass = 'Cerberus\\Plugins\\Plugin' . ucfirst($name);

        if (class_exists($pluginClass) === true) {
            if (array_key_exists($pluginClass, $this->loaded['classes']) === false) {
                $plugin = new $pluginClass($this);
                if (is_subclass_of($pluginClass, 'Cerberus\\Plugin') === true) {
                    $this->sysinfo('Load Plugin: ' . $name);
                    $this->loaded['classes'][$pluginClass] = $plugin->onLoad($data);
                } else {
                    $this->sysinfo($name . ' isn\'t a PluginClass.');
                }
            } else {
                $this->sysinfo('Plugin "' . $name . '" is already loaded.');
            }
        } else {
            $this->sysinfo($name . ' don\'t exists.');
        }
    }

    /**
     * @param string $event
     * @param array $data
     */
    public function runPluginEvent($event, $data)
    {
        if (array_key_exists($event, $this->pluginevents)) {
            for ($priority = 10; $priority > 0; $priority--) {
                if (array_key_exists($priority, $this->pluginevents[$event])) {
                    foreach ($this->pluginevents[$event][$priority] as $pluginClass) {
                        $pluginClass->$event($data);
                    }
                }
            }
        }
    }

    /**
     * @param string $event
     * @param object $object
     * @param int $priority
     */
    public function addEvent($event, $object, $priority = 5)
    {
        $this->pluginevents[$event][$priority][] = $object;
    }

    /**
     * @param string $event
     * @param object $object
     * @return int
     */
    public function removeEvent($event, $object)
    {
        $count = 0;
        $className = get_class($object);
        if (array_key_exists($event, $this->pluginevents)) {
            foreach ($this->pluginevents[$event] as $priorityKey => $priorityValue) {
                foreach ($priorityValue as $key => $eventObject) {
                    if (get_class($eventObject) === $className) {
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
     * @param mixed $lang
     * @param array $array
     * @return string
     */
    public function __($text, $lang = null, $array = [])
    {
        return $this->translate->__($text, $lang, $array);
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
