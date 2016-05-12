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

class TranslateTest extends \PHPUnit_Framework_TestCase
{
    protected $translate;

    protected function setUp()
    {
        $this->translate = new Translate;
    }

    protected function tearDown()
    {
        unset($this->translate);
    }

    public function testGettranslator()
    {
        $translator = $this->translate->getTranslator();
        $this->assertInstanceOf('Symfony\Component\Translation\Translator', $translator);
    }

    public function testTrans()
    {
        $this->translate->setLocale('de');
        $this->translate->setTranslations(['de' => ['world' => 'welt'], 'en' => ['world' => 'world']]);
        $this->assertSame('welt', $this->translate->trans('world'));
        $this->assertSame('world', $this->translate->__('world', [], 'en'));
        $this->translate->setTranslations(['de' => ['hello mrx' => 'hallo %var%'], 'en' => ['hello mrx' => 'hello %var%']]);
        $this->assertSame('hallo john', $this->translate->trans('hello mrx', ['%var%' => 'john']));
    }

    public function testGetlanguage()
    {
        $this->assertSame('en', $this->translate->getLanguage());
    }

    public function testSetlocale()
    {
        $this->translate->setLocale('de');
        $this->assertSame('de', $this->translate->getLanguage());
    }

}
