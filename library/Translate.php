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

/**
 * Class Translate
 * @package Cerberus
 * @author Stefan Hüsges
 * @link http://www.mpcx.net/cerberus/ Project Homepage
 * @link https://github.com/tronsha/Cerberus Project on GitHub
 * @link http://www.loc.gov/standards/iso639-2/php/English_list.php
 * @license http://www.gnu.org/licenses/gpl-3.0 GNU General Public License
 */

class Translate
{
    protected $lang;
    protected $translations = array();

    /**
     *
     */
    public function __construct()
    {
        $this->lang = 'en';
    }

    /**
     * @param string $text
     * @param mixed $lang
     * @return string
     */
    public function __($text, $lang = null)
    {
        if ($lang === null) {
            $lang = $this->lang;
        }
        if (isset($this->translations[$lang][$text])) {
            return $this->translations[$lang][$text];
        } else {
            return $text;
        }
    }

    /**
     * @param string $lang
     */
    public function setLang($lang)
    {
        $this->lang = $lang;
    }

    /**
     * @param array $translations
     */
    public function setTranslations($translations) {
        $languages = array_merge(array_keys($this->translations), array_keys($translations));
        $languages = array_unique($languages);
        foreach ($languages as $lang) {
            if (array_key_exists($lang, $this->translations) && array_key_exists($lang, $translations)) {
                $this->translations[$lang] = array_merge($this->translations[$lang], $translations[$lang]);
            } elseif (array_key_exists($lang, $translations)) {
                $this->translations[$lang] = $translations[$lang];
            }
        }
    }
}
