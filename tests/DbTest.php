<?php

namespace Cerberus;


class DbTest extends \PHPUnit_Framework_TestCase
{
    public function testConstructorWithLowerCase()
    {
        $db = new Db("hello", array(1,2,3));
        $this->assertAttributeEquals("hello", "dbms", $db);
    }

    public function testConstructorWithUpperCase()
    {
        $db = new Db("HELLO", array(1,2,3));
        $this->assertAttributeEquals("hello", "dbms", $db);
    }
}
