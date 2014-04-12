<?php

/*   Cerberus IRCBot
 *   Copyright (C) 2008 - 2012 Stefan Hüsges
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

/**
 * @author Stefan Hüsges <http://www.mpcx.net>
 * @version $Id: $
 */

class IRCBot
{
    const AUTH_NONE = 1;
    const AUTH_MEMBER = 2;
    const AUTH_ADMIN = 3;

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

    public function __construct($config = null)
    {
        $this->time['script_start'] = getmicrotime();
        $this->bot['pid'] = getmypid();
        $this->bot['nick'] = null;
        $this->server['network'] = null;
        $this->server['password'] = null;
        $this->db = null;
        $this->dbms = null;
        $this->config['db'] = array();
        $this->config['info'] = array('name' => 'Cerberus');
        $this->reconnect['channel'] = array();
        $this->config['dbms'] = array('mysql' => 'MySQL', 'pg' => 'PostgreSQL', 'sqlite' => 'SQLite');

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
                $this->setDB($config['bot']['dbms'], $config['db']);
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
        }

        $this->config['autorejoin'] = is_array(
            $config
        ) && isset($config['bot']['autorejoin']) ? ($config['bot']['autorejoin'] == 1 ? true : false) : false;

        $this->config['logfile']['error'] = is_array(
            $config
        ) && isset($config['log']['error']) ? ($config['log']['error'] == 1 ? true : false) : true;
        $this->config['logfile']['socket'] = is_array(
            $config
        ) && isset($config['log']['socket']) ? ($config['log']['socket'] == 1 ? true : false) : false;
        $this->config['logfile']['sql'] = is_array(
            $config
        ) && isset($config['log']['sql']) ? ($config['log']['sql'] == 1 ? true : false) : false;
        $this->config['dailylogfile'] = is_array(
            $config
        ) && isset($config['log']['dailylogfile']) ? ($config['log']['dailylogfile'] == 1 ? true : false) : true;

        if (is_array($config) && isset($config['log']['directory']) && is_dir($config['log']['directory'])) {
            $this->config['logfiledirectory'] = $config['log']['directory'];
        } else {
            $this->config['logfiledirectory'] = realpath(dirname(__FILE__) . '/..') . '/log/';
        }
    }

    public function __destruct()
    {
        if ($this->init === true) {
            if ($this->fp !== false) {
                fclose($this->fp);
            }
            $this->clear();
            $this->sql_query('UPDATE `bot` SET `stop` = NOW() WHERE `id` = "' . $this->bot['id'] . '"');
            $this->db->close();
        }
        printf(PHP_EOL . PHP_EOL . "Execute time: %.5fs" . PHP_EOL, getmicrotime() - $this->time['script_start']);
    }

    public function setNetwork($network)
    {
        $this->server['network'] = $network;
        return $this;
    }

    public function setPassword($password)
    {
        $this->server['password'] = $password;
        return $this;
    }

    public function setDB($dbms, $config)
    {
        $this->dbms = strtolower($dbms);
        $this->config['db'] = $config;
        $this->db = new db($this->dbms, $this->config['db']);
        return $this;
    }

    public function setNick($nick = null)
    {
        if ($nick === null) {
            $nick = $this->randomNick();
        }
        $this->bot['nick'] = $nick;
        $this->var['me'] = $nick;
        if ($this->init === true) {
            $this->sql_query(
                'UPDATE `bot` SET `nick` = "' . $this->db->escape_string(
                    $this->bot['nick']
                ) . '" WHERE `id` = "' . $this->bot['id'] . '"'
            );
        }
        return $this;
    }

    public function init()
    {
        if (isset($this->server['network']) === false || $this->db === null) {
            return false;
        }

        $this->db_connect();
        $this->sql_query(
            'INSERT INTO `bot` SET `pid` = "' . $this->bot['pid'] . '", `start` = NOW(), `nick` = "' . $this->db->escape_string(
                $this->bot['nick']
            ) . '"'
        );
        $this->bot['id'] = $this->db->insert_id();
        if (isset($this->version['bot']) === false) {
            $this->version['php'] = phpversion();
            $this->version['os'] = php_uname('s') . ' ' . php_uname('r');
            $this->version['bot'] = 'PHP ' . $this->version['php'] . ' - ' . $this->version['os'];
            if ($this->dbms == 'mysql' || $this->dbms == 'pg') {
                $this->version['sql'] = $this->db->result($this->sql_query('SELECT VERSION()'), 0, 0);
                $this->version['bot'] .= ' - ' . $this->config['dbms'][$this->dbms] . ' ' . $this->version['sql'];
            }
        }
        $this->init = true;
        return true;
    }

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

    protected function db_connect()
    {
        $this->db->connect();
        $this->sql_error();
    }

    protected function getServer($i = 0)
    {
        $i = (string)$i;
        $sql = 'SELECT s.`id` , s.`server` AS host, sp.`port` '
            . 'FROM `server` s, `server_port` sp, `network` n '
            . 'WHERE s.`id` = sp.`server_id` '
            . 'AND n.`id` = s.`network_id` '
            . 'AND n.`network` = "' . $this->server['network'] . '" '
            . 'ORDER BY s.`id` , sp.`port` '
            . 'LIMIT ' . $i . ', 1';
        $query = $this->sql_query($sql);
        $this->server['id'] = $this->db->result($query, 0, 'id');
        $this->server['host'] = $this->db->result($query, 0, 'host');
        $this->server['ip'] = @gethostbyname($this->server['host']);
        $this->server['port'] = $this->db->result($query, 0, 'port');
        $this->sql_query(
            'UPDATE `bot` SET `server_id` = "' . $this->server['id'] . '" WHERE `id` = "' . $this->bot['id'] . '"'
        );
    }

    public function connect()
    {
        if ($this->init === false) {
            if ($this->init() === false) {
                return false;
            }
        }

        $sql = 'SELECT count(*) AS number '
            . 'FROM `server` s, `server_port` sp, `network` n '
            . 'WHERE s.`id` = sp.`server_id` '
            . 'AND n.`id` = s.`network_id` '
            . 'AND n.`network` = "' . $this->server['network'] . '"';
        $query = $this->sql_query($sql);
        $n = $this->db->result($query, 0, 'number');
        $i = 0;
        $repeat = true;
        if ($n == 0) {
            $this->sysinfo('No ' . $this->server['network'] . ' server');
            return false;
        }
        while ($repeat) {
            $this->getServer($i);
            $this->sysinfo('Try to connect to ' . $this->server['host'] . ':' . $this->server['port']);
            $this->fp = @fsockopen(@gethostbyname($this->server['host']), $this->server['port'], $errno, $errstr);
            if ($this->fp === false) {
                $this->log('socket: ' . $errstr, 'error');
                $this->sysinfo('Connection failed');
                $i++;
            } else {
                $this->sysinfo('Connection success');
                $repeat = false;
                //$this->sql_query('INSERT INTO `connection` SET `time` = NOW()');
                //$this->bot['connectionid'] = $this->db->insert_id();
            }

            if ($i == $n) {
                $this->sysinfo('All attempts failed');
                return false;
            }
        }
        $this->time['irc_connect'] = getmicrotime();
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
        $this->clear();
        $this->preform();
        return $this->run();
    }

    protected function reconnect()
    {
        $this->reconnect['channel'] = array();
        $query = $this->sql_query('SELECT `channel` FROM `channel` WHERE `bot_id` = "' . $this->bot['id'] . '"');
        while ($channel = $this->db->fetch_assoc($query)) {
            $this->reconnect['channel'][] = $channel['channel'];
        }

        fclose($this->fp);
        $this->connect();
    }

    protected function clear()
    {
        $this->sql_query('DELETE FROM `write` WHERE `bot_id` = "' . $this->bot['id'] . '"');
        $this->sql_query('DELETE FROM `channel` WHERE `bot_id` = "' . $this->bot['id'] . '"');
        $this->sql_query('DELETE FROM `channel_user` WHERE `bot_id` = "' . $this->bot['id'] . '"');
    }

    protected function preform()
    {
        $query = $this->sql_query(
            'SELECT `text` FROM `preform` WHERE `network` = "' . $this->server['network'] . '" ORDER BY `priority` DESC'
        );
        while ($copy = $this->db->fetch_assoc($query)) {
            $this->sql_query(
                'INSERT INTO `write` SET `text` = "' . $this->db->escape_string(
                    $copy['text']
                ) . '", `bot_id` = "' . $this->bot['id'] . '"'
            );
        }
        foreach ($this->reconnect['channel'] as $channel) {
            $this->join($channel);
        }
    }

    protected function sysinfo($text)
    {
        echo '**** ' . $text . ' ****' . PHP_EOL;
    }

    protected function sql_query($sql)
    {
        $this->log($sql, 'sql');
        $res = $this->db->query($sql);
        $this->sql_error();
        return $res;
    }

    protected function sql_error()
    {
        $errstr = $this->db->error();
        if ($errstr != '') {
            $this->log('sql: ' . $errstr, 'error');
        }
        return $errstr;
    }

//    public function useLog($type)
//    {
//        if (array_key_exists($type, $this->config['logfile'])) {
//            $this->config['logfile'][$type] = true;
//        }
//    }

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

    protected function write($text)
    {
        $output = trim($text) . PHP_EOL;
        echo '[' . date("H:i:s") . '] => ' . $output;
        fwrite($this->fp, $output);
        preg_match("/^([^ ]+).*?$/i", $text, $matches);
        $command = isset($matches[1]) ? $matches[1] : '';
        if (strtolower($command) == 'quit') {
            $this->run = false;
        }
    }

    protected function read()
    {
        stream_set_timeout($this->fp, 10);
        $input = @fgets($this->fp, 4096);
        $text = trim($input);
        if ($text != '') {
            $this->log($text, 'socket');
            echo '[' . date("H:i:s") . '] <= ' . $text . PHP_EOL;
        }
        return $input;
    }

    protected function send()
    {
        $sql = 'SELECT `id`, `text` FROM `write` WHERE `bot_id` = "' . $this->bot['id'] . '" ORDER BY `id` LIMIT 0, 1';
        $query = $this->sql_query($sql);
        $send = $this->db->fetch_assoc($query);
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
            $sql_delete = 'DELETE FROM `write` WHERE `id` = "' . $send['id'] . '"';
            $this->sql_query($sql_delete);

            preg_match("/^([^\ ]+)(?:\ ([^\:].*?))?(?:\ \:(.*?))?(?:\r)?$/i", $send['text'], $matches);
            $command = isset($matches[1]) ? $matches[1] : '';
            $rest = isset($matches[2]) ? $matches[2] : '';
            $text = isset($matches[3]) ? $matches[3] : '';

            $sql_log = 'INSERT INTO log SET '
                . '`nick`      = "' . $this->db->escape_string($this->bot['nick']) . '", '
                . '`command`   = "' . $this->db->escape_string($command) . '", '
                . '`rest`      = "' . $this->db->escape_string($rest) . '", '
                . '`text`      = "' . $this->db->escape_string($text) . '", '
                . '`all`       = "' . $this->db->escape_string($send['text']) . '", '
                . '`network`   = "' . $this->db->escape_string($this->server['network']) . '", '
                . '`bot_id`    = "' . $this->bot['id'] . '", '
                //. '`connection_id` = "'.$this->bot['connectionid'].'", '
                . '`time`      = NOW(), '
                . '`direction` = "out"';
            $this->sql_query($sql_log);
        }
    }

    protected function run()
    {
        while (!feof($this->fp)) {
            $input = $this->read();

            if (trim($input) != '') {
                if ($this->db->ping() === false) {
                    $this->db->close();
                    $this->db_connect();
                }
                if ($input{0} != ':') {
                    if (strpos(strtoupper($input), 'PING') !== false) {
                        $this->lastping = time();
                        $output = $input;
                        $output{1} = 'O';
                        $this->write($output);
                        unset($output);
                        $this->sql_query('UPDATE `bot` SET `ping` = NOW() WHERE `id` = "' . $this->bot['id'] . '"');
                    }
                } else {
                    $this->command($input);
                }
            }
            if ($this->nowrite === false && floor(getmicrotime() - $this->time['irc_connect']) > 10) {
                $this->send();
            }
            unset($input);
            msleep(8);
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
            case '322':
                $this->on322($rest, $text);
                break;
            case '324':
                break; //(Channel-Modes)
            case '332':
                $this->on332($rest, $text);
                break;
            case '353':
                $this->on353($rest, $text);
                break;
            case '432':
                $this->on432();
            case '433':
                $this->on433();
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
                $this->onInvite($host, $rest);
                break;
        }

        $sql_log = 'INSERT INTO log SET '
            . '`nick`      = "' . $this->db->escape_string($nick) . '", '
            . '`host`      = "' . $this->db->escape_string($host) . '", '
            . '`command`   = "' . $this->db->escape_string($command) . '", '
            . '`rest`      = "' . $this->db->escape_string($rest) . '", '
            . '`text`      = "' . $this->db->escape_string($text_) . '", '
            . '`all`       = "' . $this->db->escape_string($all) . '", '
            . '`network`   = "' . $this->db->escape_string($this->server['network']) . '", '
            . '`bot_id`    = "' . $this->bot['id'] . '", '
            //. '`connection_id` = "'.$this->bot['connectionid'].'", '
            . '`time`      = NOW(), '
            . '`direction` = "in"';
        $this->sql_query($sql_log);
    }

    protected function on432()
    {
        $this->otherNick();
    }

    protected function on433()
    {
        $this->otherNick();
    }

    protected function otherNick()
    {
        if ($this->nowrite === false) {
            return;
        }
        $this->setNick(null);
        $this->write('NICK ' . $this->bot['nick']);
    }

    protected function on322($rest, $text)
    {
        list($dummy, $channel, $anz_user) = explode(' ', $rest);
        $sql_list = 'INSERT INTO channellist SET '
            . '`channel` = "' . $this->db->escape_string($channel) . '", '
            . '`user`    = "' . $this->db->escape_string($anz_user) . '", '
            . '`topic`   = "' . $this->db->escape_string($text) . '", '
            . '`network` = "' . $this->db->escape_string($this->server['network']) . '", '
            . '`time`    = NOW()';
        $this->sql_query($sql_list);
    }

    protected function onPrivmsg($nick, $host, $channel, $text)
    {
        if (preg_match("/\x01([A-Z]+)( [0-9]+)?\x01/i", $text, $matches)) {
            $send = '';
            switch ($matches[1]) {
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
                    $send = 'FINGER ' . $this->config['info']['name'] . ' (' . $this->config['info']['homepage'] . ') Idle ' . round(
                        getmicrotime() - $this->time['irc_connect']
                    ) . ' seconds';
                    break;
            }
            if (empty($send) === false) {
                $this->notice($nick, "\x01" . $send . "\x01");
            }
        } elseif ($text == '!die') {
            $isadmin = $this->authorizations(trim($host), self::AUTH_ADMIN);
            if ($isadmin) {
                $this->quit('Client Quit');
            }
        }
    }

    protected function onNotice($nick, $text)
    {
    }

    protected function onNick($nick, $text)
    {
        if ($nick == $this->var['me']) {
            $this->setNick($text);
        }
        $sql = 'UPDATE `channel_user` SET `user` = "' . $this->db->escape_string(
            $text
        ) . '" WHERE `bot_id` = "' . $this->bot['id'] . '" AND `user` = "' . $this->db->escape_string($nick) . '"';
        $this->sql_query($sql);
    }

    protected function on353($rest, $text)
    {
        list($me, $dummy, $channel) = explode(' ', $rest);
        $user_array = explode(' ', $text);
        foreach ($user_array as $user) {
            preg_match("/^([\+\@])?([^\+\@]+)$/i", $user, $matches);
            $this->sql_query(
                'INSERT INTO `channel_user` SET `user` = "' . $this->db->escape_string(
                    $matches[2]
                ) . '", `mode` = "' . $this->db->escape_string(
                    $matches[1]
                ) . '", `channel` = "' . $this->db->escape_string($channel) . '", `bot_id` = "' . $this->bot['id'] . '"'
            );
        }
    }

    protected function on332($rest, $text)
    {
        list($me, $channel) = explode(' ', $rest);
        $this->onTopic($channel, $text);
    }

    protected function onTopic($channel, $topic)
    {
        $sql = 'UPDATE `channel` SET `topic` = "' . $this->db->escape_string(
            $topic
        ) . '" WHERE `bot_id` = "' . $this->bot['id'] . '" AND `channel` = "' . $this->db->escape_string(
            $channel
        ) . '"';
        $this->sql_query($sql);
    }

    protected function onJoin($nick, $channel)
    {
        if ($nick == $this->var['me']) {
            $this->sql_query(
                'INSERT INTO `channel` SET `channel` = "' . $this->db->escape_string(
                    $channel
                ) . '", `bot_id` = "' . $this->bot['id'] . '"'
            );
            $this->mode($channel);
        } else {
            $this->sql_query(
                'INSERT INTO `channel_user` SET `user` = "' . $this->db->escape_string(
                    $nick
                ) . '", `channel` = "' . $this->db->escape_string($channel) . '", `bot_id` = "' . $this->bot['id'] . '"'
            );
        }
    }

    protected function onKick($bouncer, $rest)
    {
        list($channel, $nick) = explode(' ', $rest);
        $this->onPart($nick, $channel);

        if ($this->config['autorejoin'] === true && $nick == $this->var['me']) {
            $this->join($channel);
        }
    }

    protected function onPart($nick, $channel)
    {
        if ($nick == $this->var['me']) {
            $this->sql_query(
                'DELETE FROM `channel` WHERE `channel` = "' . $this->db->escape_string(
                    $channel
                ) . '" AND `bot_id` = "' . $this->bot['id'] . '"'
            );
            $this->sql_query(
                'DELETE FROM `channel_user` WHERE `channel` = "' . $this->db->escape_string(
                    $channel
                ) . '" AND `bot_id` = "' . $this->bot['id'] . '"'
            );
        } else {
            $this->sql_query(
                'DELETE FROM `channel_user` WHERE `user` = "' . $this->db->escape_string(
                    $nick
                ) . '" AND `channel` = "' . $this->db->escape_string(
                    $channel
                ) . '" AND `bot_id` = "' . $this->bot['id'] . '"'
            );
        }
    }

    protected function onQuit($nick)
    {
        $this->sql_query(
            'DELETE FROM `channel_user` WHERE `user` = "' . $this->db->escape_string(
                $nick
            ) . '" AND `bot_id` = "' . $this->bot['id'] . '"'
        );
    }

    protected function onMode($mode)
    {
        $array = explode(' ', $mode);
        $channel = $array[0];
        /* TODO */
    }

    protected function onInvite($host, $rest)
    {
        $isadmin = $this->authorizations(trim($host), self::AUTH_ADMIN);
        list($me, $channel) = explode(' ', $rest);
        if ($isadmin) {
            $this->join($channel);
        }
    }

    protected function privmsg($to, $text)
    {
        $this->sql_query(
            'INSERT INTO `write` SET `text` = "' . $this->db->escape_string(
                'PRIVMSG ' . $to . ' :' . $text
            ) . '", `bot_id` = "' . $this->bot['id'] . '"'
        );
    }

    protected function notice($to, $text)
    {
        $this->sql_query(
            'INSERT INTO `write` SET `text` = "' . $this->db->escape_string(
                'NOTICE ' . $to . ' :' . $text
            ) . '", `bot_id` = "' . $this->bot['id'] . '"'
        );
    }

    protected function quit($text)
    {
        $this->sql_query(
            'INSERT INTO `write` SET `text` = "' . $this->db->escape_string(
                'QUIT :' . $text
            ) . '", `bot_id` = "' . $this->bot['id'] . '"'
        );
    }

    protected function mode($text = null)
    {
        $this->sql_query(
            'INSERT INTO `write` SET `text` = "' . $this->db->escape_string(
                'MODE' . ($text === null ? '' : ' ' . $text)
            ) . '", `bot_id` = "' . $this->bot['id'] . '"'
        );
    }

    protected function join($channel)
    {
        $this->sql_query(
            'INSERT INTO `write` SET `text` = "' . $this->db->escape_string(
                'JOIN ' . $channel
            ) . '", `bot_id` = "' . $this->bot['id'] . '"'
        );
    }

    protected function channellist()
    {
        $this->sql_query(
            'DELETE FROM `channellist` WHERE `network` = "' . $this->db->escape_string($this->server['network']) . '"'
        );
        $this->sql_query(
            'INSERT INTO `write` SET `text` = "' . $this->db->escape_string(
                'LIST'
            ) . '", `bot_id` = "' . $this->bot['id'] . '"'
        );
    }

    protected function authorizations($host, $level = 0)
    {
        $sql = 'SELECT `host` FROM `user` WHERE `network` = "' . $this->server['network'] . '" AND `authorizations` >= ' . $level . '';
        $query = $this->sql_query($sql);
        while ($user = $this->db->fetch_assoc($query)) {
            if (preg_match('/' . str_replace('*', '.*?', $user['host']) . '/', $host) == 1) {
                return true;
            }
        }
        return false;
    }
}