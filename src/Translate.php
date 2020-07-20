<?php

/*
 * Cerberus IRCBot
 * Copyright (C) 2008 - 2020 Stefan Hüsges
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

use DirectoryIterator;
use Exception;
use Symfony\Component\Translation\Loader\ArrayLoader;
use Symfony\Component\Translation\Loader\PhpFileLoader;
use Symfony\Component\Translation\Loader\PoFileLoader;
use Symfony\Component\Translation\Translator;

/**
 * Class Translate
 * @package Cerberus
 * @author Stefan Hüsges
 * @link http://www.mpcx.net/cerberus/ Project Homepage
 * @link https://github.com/tronsha/Cerberus Project on GitHub
 * @link http://symfony.com/components/Translation
 * @link http://www.loc.gov/standards/iso639-2/php/English_list.php
 * @license http://www.gnu.org/licenses/gpl-3.0 GNU General Public License
 */

class Translate
{
    protected $translator = null;
    protected $language;

    /**
     * Translate constructor.
     * @param string|null $language
     */
    public function __construct($language = null)
    {
        if (null === $language) {
            $language = 'en';
        }
        $this->translator = new Translator($language);
        $this->translator->addLoader('array', new ArrayLoader());
        $this->translator->addLoader('php_file', new PhpFileLoader());
        $this->translator->addLoader('po_file', new PoFileLoader());
        $this->setLanguage($language);
        $this->loadTranslationFile('status');
    }

    /**
     * @param string $text
     * @param array $array
     * @param string|null $language
     * @return string
     */
    public function __($text, $array = [], $language = null)
    {
        if (true === empty($array)) {
            $array = [];
        }
        if (null !== $language) {
            $this->translator->setLocale($language);
        }
        $text = $this->translator->trans($text, $array);
        if (null !== $language) {
            $this->translator->setLocale($this->getLanguage());
        }
        return $text;
    }

    /**
     * Alias for __ without language attribute
     * @param string $text
     * @param array $array
     * @return string
     */
    public function trans($text, $array = [])
    {
        return $this->__($text, $array);
    }

    /**
     * @param string $language
     * @throws Exception
     */
    public function setLanguage($language)
    {
        if (false === empty($language)) {
            $this->language = $language;
            if (null === $this->translator) {
                throw new Exception('wait... something is wrong... the translator is not setted.');
            } else {
                $this->translator->setLocale($language);
            }
        }
    }

    /**
     * @return string
     */
    public function getLanguage()
    {
        return $this->language;
    }

    /**
     * Alias from setLanguage
     * @param string $locale
     */
    public function setLocale($locale)
    {
        $this->setLanguage($locale);
    }

    /**
     * @param array $translations
     */
    public function setTranslations($translations)
    {
        foreach ($translations as $locale => $resource) {
            $this->addResource('array', $resource, $locale);
        }
    }

    /**
     * @param string $format
     * @param array $resource
     * @param string $locale
     * @param null $domain
     */
    public function addResource($format = 'array', $resource = [], $locale = 'en', $domain = null)
    {
        $this->translator->addResource($format, $resource, $locale, $domain);
    }

    /**
     * @return Translator
     */
    public function getTranslator()
    {
        return $this->translator;
    }

    /**
     * @param string $resource
     */
    public function loadTranslationFile($resource)
    {
        $languagesPath = Cerberus::getPath() . '/languages/';
        $languagesDirectory = new DirectoryIterator($languagesPath);
        foreach ($languagesDirectory as $languageDirectory) {
            if (true === $languageDirectory->isDir() && false === $languageDirectory->isDot()) {
                $language = $languageDirectory->getFilename();
                $path = $languageDirectory->getPathname();
                if (true === file_exists($path . '/' . $resource . '.php')) {
                    $this->addResource('php_file', $path . '/' . $resource . '.php', $language);
                }
                if (true === file_exists($path . '/' . $resource . '.po')) {
                    $this->addResource('po_file', $path . '/' . $resource . '.po', $language);
                }
            }
        }
    }
}
