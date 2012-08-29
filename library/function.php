<?php

function getmicrotime()
{
    if(version_compare(phpversion(), '5.0', '<') === true)
    {
        list($usec,$sec) = @explode(" ", @microtime());
        return ((float)$usec + (float)$sec);
    }
    else
    {
        return microtime(true);
    }
}

function hex2bin($hexstr)
{
    $binstr = "";
    for($z = 0; $z < strlen($hexstr); $z+=2)
        $binstr .= chr(hexdec(substr($hexstr, $z, 2)));
    return $binstr;
}

function define_ini_file($file, $array=NULL, $prefix='')
{
    if($file != NULL)
    {
        if(file_exists($file) === true)
            $array = parse_ini_file($file, true);
        else 
            return false;
    }

    if($prefix != '') $prefix .= '_';
        
    foreach ($array as $key => $value) 
    {
        if(is_array($value) === true)
            define_ini_file(NULL, $value, $prefix.$key);
        else
            define(strtoupper($prefix.$key), $value, true);
    }
}

function msleep($msec)
{
    usleep($msec * 1000);
}