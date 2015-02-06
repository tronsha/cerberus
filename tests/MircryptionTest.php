<?php

namespace Cerberus;

class MircryptionTest extends \PHPUnit_Framework_TestCase
{
    public function testEncodeDecode()
    {
        $text = 'foo';
        $key = 'bar';
        $mircryption = new Mircryption;
        $encode = $mircryption->encode($text, $key);
        $decode = $mircryption->decode($encode, $key);
        $this->assertEquals($text, $decode);
    }
}