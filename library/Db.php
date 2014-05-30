<?php

namespace Cerberus;


class Db
{
    protected $dbms = null;
    protected $config = array();
    protected $link = null;

    public function __construct($dbms, $config)
    {
        $this->dbms = strtolower($dbms);
        $this->config = $config;
    }

    public function __destruct()
    {
    }

    public function connect()
    {
        $host = $this->config['host'];
        $port = isset($this->config['port']) ? $this->config['port'] : 3306;
        $username = $this->config['username'];
        $password = $this->config['password'];
        $dbname = $this->config['dbname'];

        $this->link = mysql_connect($host . ":" . $port, $username, $password);
        $this->select_db($dbname);
        return $this->link;
    }

    public function select_db($dbname = null)
    {
        if ($dbname === null) {
            $dbname = $this->config['dbname'];
        }

        return mysql_select_db($dbname, $this->link);
    }

    public function close()
    {
        return mysql_close($this->link);
    }

    public function query($query)
    {
        return mysql_query($query, $this->link);
    }

    public function fetch_array($result)
    {
        return mysql_fetch_array($result);
    }

    public function fetch_row($result)
    {
        return mysql_fetch_row($result);
    }

    public function fetch_assoc($result)
    {
        return mysql_fetch_assoc($result);
    }

    public function result($result, $row, $field = null)
    {
        return ($field === null ? mysql_result($result, $row) : mysql_result($result, $row, $field));
    }

    public function insert_id()
    {
        return mysql_insert_id($this->link);
    }

    public function ping()
    {
        return mysql_ping($this->link);
    }

    public function error()
    {
        return mysql_error($this->link);
    }

    public function escape_string($data)
    {
        return mysql_real_escape_string($data, $this->link);
    }
}