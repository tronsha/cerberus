<?php

/*   Cerberus IRCBot
 *   Copyright (C) 2008 - 2015 Stefan Hüsges
 *
 *   This program is free software; you can redistribute it and/or modify it 
 *   under the terms of the GNU General Public License as published by the Free 
 *   Software Foundation; either version 3 of the License, or (at your option) 
 *   any later version.
 *
 *   This program is distributed in the hope that it will be useful, but
 *   WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY 
 *   or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License 
 *   for more details.
 *
 *   You should have received a copy of the GNU General Public License along 
 *   with this program; if not, see <http://www.gnu.org/licenses/>.
 */

namespace Cerberus;

use Cerberus\Plugins\PluginAuth;
use SQLite3;
use Exception;

/**
 * Class Irc
 * @package Cerberus
 * @author Stefan Hüsges
 * @link http://www.mpcx.net/projekte/cerberus/ Project Homepage
 * @link https://github.com/tronsha/cerberus Project on GitHub
 * @link http://tools.ietf.org/html/rfc2812 Internet Relay Chat: Client Protocol
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
    protected $version = [];
    protected $config = [];
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
        $this->config['info'] = ['name' => 'Cerberus'];
        $this->reconnect['channel'] = [];
        $this->loaded['classes'] = [];
        $this->config['dbms'] = ['mysql' => 'MySQL', 'pg' => 'PostgreSQL', 'sqlite' => 'SQLite'];
        $this->config['autorejoin'] = false;
        $this->config['ctcp'] = false;
        $this->config['logfiledirectory'] = $this->getPath() . '/log/';
        $this->config['logfile']['error'] = true;
        $this->config['logfile']['socket'] = false;
        $this->config['logfile']['sql'] = false;
        $this->config['dailylogfile'] = true;
        $this->translate = new Translate;
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
            if (!empty($config['info']['name'])) {
                $this->config['info']['name'] = $config['info']['name'];
            }
            if (!empty($config['info']['homepage'])) {
                $this->config['info']['homepage'] = $config['info']['homepage'];
            }
            if (!empty($config['info']['version'])) {
                $this->version['bot'] = $config['info']['version'];
            }
            if (!empty($config['bot']['channel'])) {
                $this->config['channel'] = '#' . $config['bot']['channel'];
            }
            if (isset($config['bot']['autorejoin'])) {
                $this->config['autorejoin'] = $config['bot']['autorejoin'] == 1 ? true : false;
            }
            if (isset($config['bot']['ctcp'])) {
                $this->config['ctcp'] = $config['bot']['ctcp'] == 1 ? true : false;
            }
            if (!empty($config['log']['directory']) && is_dir($config['log']['directory'])) {
                $this->config['logfiledirectory'] = $config['log']['directory'];
            }
            if (isset($config['log']['error'])) {
                $this->config['logfile']['error'] = $config['log']['error'] == 1 ? true : false;
            }
            if (isset($config['log']['socket'])) {
                $this->config['logfile']['socket'] = $config['log']['socket'] == 1 ? true : false;
            }
            if (isset($config['log']['sql'])) {
                $this->config['logfile']['sql'] = $config['log']['sql'] == 1 ? true : false;
            }
            if (isset($config['log']['dailylogfile'])) {
                $this->config['dailylogfile'] = $config['log']['dailylogfile'] == 1 ? true : false;
            }
            if (isset($config['plugins']['autoload'])) {
                $this->config['plugins']['autoload'] = explode(',', $config['plugins']['autoload']);
            }
            if (isset($config['frontend']['url'])) {
                $this->config['frontend']['url'] = $config['frontend']['url'];
            }
            if (isset($config['frontend']['password'])) {
                $this->config['frontend']['password'] = $config['frontend']['password'];
            }
            if (isset($config['bot']['lang'])) {
                $this->translate->setLang($config['bot']['lang']);
            }
        }
        $this->action = new Action($this, $this->db);
        $this->event = new Event($this, $this->db);
        $this->cron = new Cron();
    }

    /**
     *
     */
    public function __destruct()
    {
        if ($this->init === true) {
            $this->event->onShutdown();
            if ($this->fp !== false) {
                fclose($this->fp);
            }
            $this->db->cleanupBot();
            $this->db->shutdownBot();
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
        $this->event->onError($text);
        return $this->error($text);
    }

    /**
     * @return array
     */
    public function getVars()
    {
        return ['config' => $this->config, 'version' => $this->version, 'var' => $this->var, 'time' => $this->time];
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
            case "pdo_mysql" :
            case "drizzle_pdo_mysql" :
            case "mysqli" :
                $this->dbms = "mysql";
                break;
            case "pdo_sqlite" :
                $this->dbms = "sqlite";
                break;
            case "pdo_pgsql" :
                $this->dbms = "pg";
                break;
            case "pdo_oci" :
            case "oci8" :
                $this->dbms = "oracle";
                break;
            case "pdo_sqlsrv" :
            case "sqlsrv" :
                $this->dbms = "mssql";
                break;
            case "sqlanywhere" :
                $this->dbms = "sqlanywhere";
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
            $this->db->setBotNick($nick);
        }
        return $nick;
    }

    /**
     * @return bool
     */
    public function init()
    {
        if (isset($this->server['network']) === false || $this->db === null) {
            return false;
        }
        $this->dbConnect();
        $this->db->createBot($this->bot['pid'], $this->bot['nick']);
        if (isset($this->version['bot']) === false) {
            $this->version['php'] = phpversion();
            $this->version['os'] = php_uname('s') . ' ' . php_uname('r');
            $this->version['bot'] = 'PHP ' . $this->version['php'] . ' - ' . $this->version['os'];
            if ($this->dbms == 'mysql' || $this->dbms == 'pg') {
                $this->version['sql'] = $this->db->getDbVersion();
                $this->version['bot'] .= ' - ' . $this->config['dbms'][$this->dbms] . ' ' . $this->version['sql'];
            } elseif ($this->dbms == 'sqlite') {
                $version = SQLite3::version();
                $this->version['sql'] = $version['versionString'];
                $this->version['bot'] .= ' - ' . $this->config['dbms'][$this->dbms] . ' ' . $this->version['sql'];
            }
        }
        if (is_array($this->config['plugins']['autoload']) === true) {
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
        $consonant = 'bcdfghjklmnpqrstvwxyz';
        $vowel = 'aeiou';
        $nick = '';
        for ($i = 0; $i < 3; $i++) {
            $nick .= $consonant{mt_rand(0, 20)} . $vowel{mt_rand(0, 4)};
        }
        return ucfirst($nick);
    }

    /**
     *
     */
    protected function dbConnect()
    {
        $this->db->connect();
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
        $n = $this->db->getServerCount($this->server['network']);
        $i = 0;
        $repeat = true;
        if ($n == 0) {
            $this->sysinfo('No ' . $this->server['network'] . ' server');
            return false;
        }
        while ($repeat) {
            $this->server = $this->db->getServerData($this->server, $i);
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
            if ($i == $n) {
                $this->sysinfo('All attempts failed');
                return false;
            }
        }
        $this->time['irc_connect'] = $this->getMicrotime();
        if ($this->server['password'] !== null) {
            $this->write('PASS ' . $this->server['password']);
        }
        if ($this->bot['nick'] === null) {
            $this->setNick();
        }
        $this->write('USER PHP' . str_replace('.', '', phpversion()) . ' * * :' . $this->config['info']['name']);
        $this->write('NICK ' . $this->bot['nick']);
        $this->lastping = time();
        $this->nowrite = true;
        $this->run = true;
        $this->db->cleanupBot();
        $this->preform();
        $this->event->onConnect();
        return $this->run();
    }

    /**
     *
     */
    protected function reconnect()
    {
        $this->reconnect['channel'] = [];
        $channels = $this->db->getJoinedChannels();
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
        $preform = $this->db->getPreform($this->server['network']);
        foreach ($preform as $command) {
            $this->db->setWrite($command['text']);
            preg_match('/join\s+(#[^\s]+)/i', $command['text'], $matches);
            if (isset($matches[1])) {
                unset($matches[0]);
                $this->reconnect['channel'] = array_diff($this->reconnect['channel'], $matches);
            }
        }
        foreach ($this->reconnect['channel'] as $channel) {
            $this->getAction()->join($channel);
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
        if ($this->config['logfile'][$type] !== true) {
            return null;
        }

        if ($this->config['dailylogfile'] === true) {
            $file = $type . '_log_' . date("Ymd", time()) . '.txt';
        } else {
            $file = $type . '_log.txt';
        }

        $handle = @fopen(realpath($this->config['logfiledirectory']) . '/' . $file, 'a+');
        if ($handle !== false) {
            fputs($handle, date("d.m.Y H:i:s", time()) . ' >>>> ' . $text . PHP_EOL);
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
        if (substr($output, -1) == '\\') {
            $output .= ' ';
        }
        $this->getConsole()->writeln($this->getConsole()->prepare($output, true, null, true, true, 0));
        fwrite($this->fp, $output . PHP_EOL);
        preg_match("/^([^ ]+).*?$/i", $text, $matches);
        $command = isset($matches[1]) ? $matches[1] : '';
        if (strtolower($command) == 'quit') {
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
        if ($text != '') {
            if (substr($text, -1) == '\\') {
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
        $send = $this->db->getWrite();
        if ($send !== false) {
            if ($send['text'] != '') {
                preg_match_all("/\%([a-z0-9_]*)/i", $send['text'], $array, PREG_PATTERN_ORDER);
                foreach ($array[1] as $value) {
                    if (array_key_exists($value, $this->var)) {
                        $send['text'] = preg_replace(
                            '/%' . $value . '(\s|$)/i',
                            $this->var[$value] . "\\1",
                            $send['text']
                        );
                    }
                }
                $this->write($send['text']);
            }
            $this->db->unsetWrite($send['id']);

            preg_match("/^([^\ ]+)(?:\ ([^\:].*?))?(?:\ \:(.*?))?(?:\r)?$/i", $send['text'], $matches);
            $command = isset($matches[1]) ? $matches[1] : '';
            $rest = isset($matches[2]) ? $matches[2] : '';
            $text = isset($matches[3]) ? $matches[3] : '';

            $this->db->setLog(
                $send['text'],
                $command,
                $this->server['network'],
                $this->bot['nick'],
                $rest,
                $text,
                'out'
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
            if (trim($input) != '') {
                if ($this->db->ping() === false) {
                    $this->db->close();
                    $this->db->connect();
                }
                if ($input{0} != ':') {
                    if (strpos(strtoupper($input), 'PING') !== false) {
                        $this->lastping = time();
                        $output = $input;
                        $output{1} = 'O';
                        $this->write($output);
                        unset($output);
                        $this->db->setPing();
                    }
                } else {
                    $this->command($input);
                }
            }
            $this->event->onTick();
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
        $text_ = isset($matches[5]) ? $matches[5] : '';
        $text = trim($text_);
        switch ($command) {
            case '001':
                $this->nowrite = false;
                break;
            case '311':
                $this->event->on311($rest);
                break;
            case '318':
                $this->event->on318();
                break;
            case '322':
                $this->event->on322($rest, $text);
                break;
            case '323':
                $this->event->on323();
                break;
            case '324':
                $this->event->on324();
                break;
            case '330':
                $this->event->on330($rest);
                break;
            case '332':
                $this->event->on332($rest, $text);
                break;
            case '353':
                $this->event->on353($rest, $text);
                break;
            case '431':
                $this->event->on431();
                break;
            case '432':
                $this->event->on432();
                break;
            case '433':
                $this->event->on433();
                break;
            case '437':
                $this->event->on437();
                break;
            case 'PRIVMSG':
                $this->event->onPrivmsg($nick, $host, $rest, $text);
                break;
            case 'NOTICE':
                $this->event->onNotice($nick, $text);
                break;
            case 'JOIN':
                $this->event->onJoin($nick, ($rest != '' ? $rest : $text));
                break;
            case 'PART':
                $this->event->onPart($nick, $rest);
                break;
            case 'QUIT':
                $this->event->onQuit($nick);
                break;
            case 'KICK':
                $this->event->onKick($nick, $rest);
                break;
            case 'NICK':
                $this->event->onNick($nick, $text);
                break;
            case 'MODE':
                $this->event->onMode($rest);
                break;
            case 'TOPIC':
                $this->event->onTopic($rest, $text);
                break;
            case 'INVITE':
                $this->event->onInvite($text, $host, $rest);
                break;
        }
        $this->db->setLog($all, $command, $this->server['network'], $nick, $rest, $text, 'in');
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
            $channels = $this->db->getJoinedChannels();
            if (in_array($channel, $channels) === true) {
                return true;
            } else {
                return false;
            }
        } else {
            return null;
        }
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
     * @return bool
     */
    public function getAuthLevel($auth)
    {
        return $this->db->getAuthLevel($this->server['network'], $auth);
    }

    /**
     * @param string $nick
     * @param string $host
     * @return mixed
     */
    public function isAdmin($nick, $host)
    {
        return $this->auth->isAdmin($nick, $host);
    }

    /**
     * @param string $nick
     * @param string $host
     * @return mixed
     */
    public function isMember($nick, $host)
    {
        return $this->auth->isMember($nick, $host);
    }

    /**
     *
     */
    public function autoloadPlugins()
    {
        $this->loadPlugin('auth');
        foreach ($this->config['plugins']['autoload'] as $plugin) {
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
     * @return Action|null
     */
    public function getAction()
    {
        return $this->action;
    }

    /**
     * @param string $text
     * @param mixed $lang
     * @return string
     */
    public function __($text, $lang = null)
    {
        return $this->translate->__($text, $lang);
    }

    /**
     * @param string $lang
     */
    public function setLang($lang)
    {
        $this->translate->setLang($lang);
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
