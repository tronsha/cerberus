<?php

namespace Cerberus;


class DbTest extends \PHPUnit_Framework_TestCase
{
    public function testConstructor()
    {
        $db = new Db("HELLO", array(1,2,3));
        $this->assertAttributeEquals("hello", "dbms", $db);
    }
}
