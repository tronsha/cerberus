SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";

CREATE TABLE IF NOT EXISTS `bot` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `server_id` int(11) unsigned NOT NULL,
  `pid` int(11) unsigned NOT NULL,
  `nick` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `start` datetime DEFAULT NULL,
  `stop` datetime DEFAULT NULL,
  `ping` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;

CREATE TABLE IF NOT EXISTS `channel` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `bot_id` int(11) NOT NULL,
  `channel` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `topic` text COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;

CREATE TABLE IF NOT EXISTS `channellist` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `network` varchar(200) COLLATE utf8_unicode_ci NOT NULL,
  `channel` varchar(200) COLLATE utf8_unicode_ci NOT NULL,
  `usercount` int(11) unsigned NOT NULL,
  `topic` text COLLATE utf8_unicode_ci NOT NULL,
  `time` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;

CREATE TABLE IF NOT EXISTS `channel_user` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `bot_id` int(11) NOT NULL,
  `channel` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `username` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `mode` enum('','+','@') COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;

CREATE TABLE IF NOT EXISTS `log` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `bot_id` int(11) unsigned NOT NULL,
  `network` varchar(200) COLLATE utf8_unicode_ci NOT NULL,
  `nick` varchar(200) COLLATE utf8_unicode_ci NOT NULL,
  `host` varchar(200) COLLATE utf8_unicode_ci NOT NULL,
  `command` varchar(200) COLLATE utf8_unicode_ci NOT NULL,
  `rest` varchar(200) COLLATE utf8_unicode_ci NOT NULL,
  `text` text COLLATE utf8_unicode_ci NOT NULL,
  `irc` text COLLATE utf8_unicode_ci NOT NULL,
  `time` datetime NOT NULL,
  `direction` enum('in','out') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'in',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;

CREATE TABLE IF NOT EXISTS `log_privmsg` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `log_id` int(11) unsigned NOT NULL,
  `bot_id` int(11) unsigned NOT NULL,
  `channel` varchar(255) NOT NULL,
  `nick` varchar(255) NOT NULL,
  `text` text NOT NULL,
  `time` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;

CREATE TABLE IF NOT EXISTS `network` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `network` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `network` (`network`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=3 ;

INSERT INTO `network` (`id`, `network`) VALUES
(1, 'freenode'),
(2, 'quakenet');

CREATE TABLE IF NOT EXISTS `preform` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `network` varchar(200) COLLATE utf8_unicode_ci NOT NULL,
  `text` text COLLATE utf8_unicode_ci NOT NULL,
  `priority` int(11) NOT NULL DEFAULT '50',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;

CREATE TABLE IF NOT EXISTS `server` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `network_id` int(11) unsigned NOT NULL,
  `servername` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `server` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `port` varchar(5) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `server` (`server`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=3 ;

INSERT INTO `server` (`id`, `network_id`, `servername`, `server`, `port`) VALUES
(1, 1, 'Random server', 'chat.freenode.net', '6667'),
(2, 2, 'Random server', 'irc.quakenet.org', '6667');

CREATE TABLE IF NOT EXISTS `auth` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `network` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `authlevel` enum('none','user','admin') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'none',
  `authname` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;

CREATE TABLE IF NOT EXISTS `web` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `email` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `password` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `sid` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;

CREATE TABLE IF NOT EXISTS `send` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `bot_id` int(11) unsigned NOT NULL DEFAULT '0',
  `text` text COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;
