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
    protected $server = array();
    protected $bot = array();
    protected $db = array();
    protected $dbms;
    protected $fp = false;
    protected $init = false;
    protected $run;
    protected $lastping;
    protected $nowrite;
    protected $var = array();
    protected $time = array();
    protected $version = array();
    protected $config = array();
    protected $reconnect = array();
    protected $loaded = array();
    protected $pluginevents = array();
    protected $auth = null;

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
        $this->config['info'] = array('name' => 'Cerberus');
        $this->reconnect['channel'] = array();
        $this->loaded['classes'] = array();
        $this->config['dbms'] = array('mysql' => 'MySQL', 'pg' => 'PostgreSQL', 'sqlite' => 'SQLite');
        $this->config['autorejoin'] = false;
        $this->config['ctcp'] = false;
        $this->config['logfiledirectory'] = $this->getPath() . '/log/';
        $this->config['logfile']['error'] = true;
        $this->config['logfile']['socket'] = false;
        $this->config['logfile']['sql'] = false;
        $this->config['dailylogfile'] = true;

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
            if (isset($config['bot']['autorejoin'])) {
                $this->config['autorejoin'] = $config['bot']['autorejoin'] == 1 ? true : false;
            }
            if (isset($config['bot']['ctcp'])) {
                $this->config['ctcp'] = $config['bot']['ctcp'] == 1 ? true : false;
            }
            if (isset($config['log']['directory']) && is_dir($config['log']['directory'])) {
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
        }
    }

    /**
     *
     */
    public function __destruct()
    {
        if ($this->init === true) {
            if ($this->fp !== false) {
                fclose($this->fp);
            }
            $this->db->cleanupBot();
            $this->db->shutdownBot();
        }
        printf(
            PHP_EOL . PHP_EOL . "Execute time: %.5fs" . PHP_EOL,
            $this->getMicrotime() - $this->time['script_start']
        );
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
        $konsonant = 'bcdfghjklmnpqrstvwxyz';
        $vokal = 'aeiou';
        $nick = '';
        for ($i = 0; $i < 3; $i++) {
            $nick .= $konsonant{rand(0, 20)} . $vokal{rand(0, 4)};
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
            $this->fp = @fsockopen(($this->server['ip']), $this->server['port'], $errno, $errstr);
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
        //stream_set_blocking($this->fp, 0);
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
        return $this->run();
    }

    /**
     *
     */
    protected function reconnect()
    {
        $this->reconnect['channel'] = array();
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
        }
        foreach ($this->reconnect['channel'] as $channel) {
            $this->join($channel);
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

        $handle = @fopen($this->config['logfiledirectory'] . $file, 'a+');
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
        $this->getConsole()->writeln(
            '<time>[' . date("H:i:s") . ']</time> => <out>' . $this->getConsole()->prepare($output, true, null, false, 14) . '</out>'
        );
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
        $input = @fgets($this->fp, 4096);
        $text = trim($input);
        if ($text != '') {
            $this->log($text, 'socket');
            $this->getConsole()->writeln(
                '<time>[' . date("H:i:s") . ']</time> <= <in>' . $this->getConsole()->prepare($text, true, null, false, 14) . '</in>'
            );
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
                $this->server['network'],
                $send['text'],
                $this->bot['nick'],
                '',
                $command,
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
        sleep(20);
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
                $this->on311($rest);
                break;
            case '318':
                $this->on318();
                break;
            case '322':
                $this->on322($rest, $text);
                break;
            case '323':
                $this->on323();
                break;
            case '324':
                $this->on324();
                break;
            case '330':
                $this->on330($rest);
                break;
            case '332':
                $this->on332($rest, $text);
                break;
            case '353':
                $this->on353($rest, $text);
                break;
            case '431':
                $this->on431();
                break;
            case '432':
                $this->on432();
                break;
            case '433':
                $this->on433();
                break;
            case '437':
                $this->on437();
                break;
            case 'PRIVMSG':
                $this->onPrivmsg($nick, $host, $rest, $text);
                break;
            case 'NOTICE':
                $this->onNotice($nick, $text);
                break;
            case 'JOIN':
                $this->onJoin($nick, ($rest != '' ? $rest : $text));
                break;
            case 'PART':
                $this->onPart($nick, $rest);
                break;
            case 'QUIT':
                $this->onQuit($nick);
                break;
            case 'KICK':
                $this->onKick($nick, $rest);
                break;
            case 'NICK':
                $this->onNick($nick, $text);
                break;
            case 'MODE':
                $this->onMode($rest);
                break;
            case 'TOPIC':
                $this->onTopic($rest, $text);
                break;
            case 'INVITE':
                $this->onInvite($text, $host, $rest);
                break;
        }
        $this->db->setLog($this->server['network'], $all, $nick, $host, $command, $rest, $text, 'in');
    }

    /**
     * ERR_NONICKNAMEGIVEN
     * :No nickname given
     */
    protected function on431()
    {
    }

    /**
     * ERR_ERRONEUSNICKNAME
     * <nick> :Erroneous nickname
     */
    protected function on432()
    {
        $this->otherNick();
    }

    /**
     * ERR_NICKNAMEINUSE
     * <nick> :Nickname is already in use
     */
    protected function on433()
    {
        $this->otherNick();
    }

    /**
     * ERR_UNAVAILRESOURCE
     * <nick/channel> :Nick/channel is temporarily unavailable
     */
    protected function on437()
    {
        $this->otherNick();
    }

    /**
     *
     */
    protected function otherNick()
    {
        if ($this->nowrite === false) {
            return;
        }
        $nick = $this->setNick(null);
        $this->write('NICK ' . $nick);
    }

    /**
     * RPL_LIST
     * <channel> <# visible> :<topic>
     * @param string $rest
     * @param string $text
     */
    protected function on322($rest, $text)
    {
        /* TODO */
    }

    /**
     * RPL_LISTEND
     * :End of LIST
     */
    protected function on323()
    {
        $this->runPluginEvent(__FUNCTION__, array());
    }

    /**
     * RPL_CHANNELMODEIS
     * <channel> <mode> <mode params>
     */
    protected function on324()
    {
        /* TODO */
    }

    /**
     * RPL_WHOISUSER
     * <nick> <user> <host> * :<real name>
     * @param string $rest
     */
    protected function on311($rest)
    {
        list($me, $nick, $user, $host) = explode(' ', $rest);
        $this->runPluginEvent(__FUNCTION__, array('nick' => $nick, 'host' => $user . '@' . $host));
    }

    /**
     * RPL_WHOISACCOUNT
     * :is logged in as
     * @param string $rest
     */
    protected function on330($rest)
    {
        list($me, $nick, $auth) = explode(' ', $rest);
        $this->runPluginEvent(__FUNCTION__, array('nick' => $nick, 'auth' => $auth));
    }

    /**
     * RPL_ENDOFWHOIS
     * <nick> :End of WHOIS list
     */
    protected function on318()
    {
        $this->runPluginEvent(__FUNCTION__, array());
    }

    /**
     * @link http://www.irchelp.org/irchelp/rfc/ctcpspec.html
     * @param string $nick
     * @param string $host
     * @param string $channel
     * @param string $text
     * @return null
     */
    protected function onPrivmsg($nick, $host, $channel, $text)
    {
        if (preg_match("/\x01([A-Z]+)( [0-9\.]+)?\x01/i", $text, $matches)) {
            if ($this->config['ctcp'] === false) {
                return null;
            }
            $send = '';
            switch ($matches[1]) {
                case 'ACTION':
                    break;
                case 'CLIENTINFO':
                    $send = 'CLIENTINFO PING VERSION TIME FINGER SOURCE CLIENTINFO';
                    break;
                case 'PING':
                    $send = 'PING' . $matches[2];
                    break;
                case 'VERSION':
                    $send = 'VERSION ' . $this->version['bot'];
                    break;
                case 'TIME':
                    $send = 'TIME ' . date('D M d H:i:s Y T');
                    break;
                case 'FINGER':
                    $send = 'FINGER ' . $this->config['info']['name'] . (isset($this->config['info']['homepage']) ? ' (' . $this->config['info']['homepage'] . ')' : '') . ' Idle ' . round(
                        $this->getMicrotime() - $this->time['irc_connect']
                    ) . ' seconds';
                    break;
                case 'SOURCE':
                    $send = 'SOURCE https://github.com/tronsha/cerberus';
                    break;
                default:
                    return null;
            }
            if (empty($send) === false) {
                $this->notice($nick, "\x01" . $send . "\x01");
            }
        } else {
            $splitText = explode(' ', $text);
            switch ($splitText[0]) {
                case '!load':
                    if (empty($splitText[1]) === false) {
                        if ($this->isAdmin($nick, $host) === true) {
                            if (preg_match('/^[a-z]+$/i', $splitText[1]) > 0) {
                                $this->loadPlugin(
                                    $splitText[1],
                                    array('nick' => $nick, 'host' => $host, 'channel' => $channel, 'text' => $text)
                                );
                            }
                        }
                    }
                    break;
                default:
                    $this->runPluginEvent(
                        __FUNCTION__,
                        array('nick' => $nick, 'host' => $host, 'channel' => $channel, 'text' => $text)
                    );
                    return null;
            }
        }
    }

    /**
     * @param string $nick
     * @param string $text
     */
    protected function onNotice($nick, $text)
    {
        $this->runPluginEvent(__FUNCTION__, array('nick' => $nick, 'text' => $text));
    }

    /**
     * @param string $nick
     * @param string $text
     */
    protected function onNick($nick, $text)
    {
        if ($nick == $this->var['me']) {
            $this->setNick($text);
        }
        $this->db->changeNick($nick, $text);
        $this->runPluginEvent(__FUNCTION__, array('nick' => $nick, 'text' => $text));
    }

    /**
     * RPL_NAMREPLY
     * @param string $rest
     * @param string $text
     */
    protected function on353($rest, $text)
    {
        list($me, $dummy, $channel) = explode(' ', $rest);
        $user_array = explode(' ', $text);
        foreach ($user_array as $user) {
            preg_match("/^([\+\@])?([^\+\@]+)$/i", $user, $matches);
            $this->db->addUserToChannel($channel, $matches[2], $matches[1]);
        }
    }

    /**
     * RPL_TOPIC
     * <channel> :<topic>
     * @param string $rest
     * @param string $text
     */
    protected function on332($rest, $text)
    {
        list($me, $channel) = explode(' ', $rest);
        $this->onTopic($channel, $text);
    }

    /**
     * @param string $channel
     * @param string $topic
     */
    protected function onTopic($channel, $topic)
    {
        $this->db->setChannelTopic($channel, $topic);
    }

    /**
     * @param string $nick
     * @param string $channel
     */
    protected function onJoin($nick, $channel)
    {
        if ($nick == $this->var['me']) {
            $this->db->addChannel($channel);
            $this->mode($channel);
        } else {
            $this->db->addUserToChannel($channel, $nick);
        }
        $this->runPluginEvent(__FUNCTION__, array('nick' => $nick, 'channel' => $channel));
    }

    /**
     * @param string $bouncer
     * @param string $rest
     */
    protected function onKick($bouncer, $rest)
    {
        list($channel, $nick) = explode(' ', $rest);
        $me = $nick == $this->var['me'] ? true : false;
        $this->onPart($nick, $channel);
        if ($this->config['autorejoin'] === true && $me === true) {
            $this->join($channel);
        }
        $this->runPluginEvent(
            __FUNCTION__,
            array('channel' => $channel, 'me' => $me, 'nick' => $nick, 'bouncer' => $bouncer)
        );
    }

    /**
     * @param string $nick
     * @param string $channel
     */
    protected function onPart($nick, $channel)
    {
        $me = $nick == $this->var['me'] ? true : false;
        if ($me === true) {
            $this->db->removeChannel($channel);
        } else {
            $this->db->removeUserFromChannel($channel, $nick);
        }
        $this->runPluginEvent(__FUNCTION__, array('channel' => $channel, 'me' => $me, 'nick' => $nick));
    }

    /**
     * @param string $nick
     */
    protected function onQuit($nick)
    {
        $this->db->removeUser($nick);
        $this->runPluginEvent(__FUNCTION__, array('nick' => $nick));
    }

    /**
     * @param string $mode
     */
    protected function onMode($mode)
    {
        $array = explode(' ', $mode);
        $channel = $array[0];
        /* TODO */
    }

    /**
     * @param string $channel
     * @param string $host
     * @param string $rest
     */
    protected function onInvite($channel, $host, $rest)
    {
        /* TODO */
    }

    /**
     * @param string $to
     * @param string $text
     */
    public function privmsg($to, $text)
    {
        $this->db->setWrite('PRIVMSG ' . $to . ' :' . $text);
    }

    /**
     * @param string $to
     * @param string $text
     */
    public function notice($to, $text)
    {
        $this->db->setWrite('NOTICE ' . $to . ' :' . $text);
    }

    /**
     * @param string $text
     */
    public function quit($text)
    {
        $this->db->setWrite('QUIT :' . $text);
    }

    /**
     * @param string|null $text
     */
    public function mode($text = null)
    {
        $this->db->setWrite('MODE' . ($text === null ? '' : ' ' . $text));
    }

    /**
     * @param string $channel
     */
    public function join($channel)
    {
        $this->db->setWrite('JOIN ' . $channel);
    }

    /**
     * @param string $channel
     */
    public function part($channel)
    {
        $this->db->setWrite('PART ' . $channel);
    }

    /**
     * @param string $nick
     */
    public function whois($nick)
    {
        $this->db->setWrite('WHOIS :' . $nick);
    }

    /**
     * @param string $nick
     */
    public function nick($nick)
    {
        $this->setNick($nick);
        $this->db->setWrite('NICK :' . $nick);
    }

    /**
     *
     */
    public function channellist()
    {
        /* TODO */
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
    protected function autoloadPlugins()
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
    protected function loadPlugin($name, $data = null)
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
    protected function runPluginEvent($event, $data)
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
}
