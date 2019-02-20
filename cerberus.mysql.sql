SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";

CREATE TABLE IF NOT EXISTS `bot` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `server_id` int(11) unsigned DEFAULT NULL,
  `pid` int(11) unsigned NOT NULL,
  `nick` varchar(255) DEFAULT NULL,
  `start` datetime DEFAULT NULL,
  `stop` datetime DEFAULT NULL,
  `ping` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;

CREATE TABLE IF NOT EXISTS `channel` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `bot_id` int(11) NOT NULL,
  `channel` varchar(255) NOT NULL,
  `topic` blob DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;

CREATE TABLE IF NOT EXISTS `channellist` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `network` varchar(255) NOT NULL,
  `channel` varchar(255) NOT NULL,
  `usercount` int(11) unsigned NOT NULL,
  `topic` blob NOT NULL,
  `time` datetime NOT NULL,
  `bot_id` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;

CREATE TABLE IF NOT EXISTS `channel_user` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `bot_id` int(11) NOT NULL,
  `channel` varchar(255) NOT NULL,
  `username` varchar(255) NOT NULL,
  `mode` varchar(255) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;

CREATE TABLE IF NOT EXISTS `log` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `bot_id` int(11) unsigned NOT NULL,
  `network` varchar(255) NOT NULL,
  `command` varchar(255) NOT NULL,
  `irc` blob NOT NULL,
  `time` datetime NOT NULL,
  `direction` enum('<','>') NOT NULL DEFAULT '<',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;

CREATE TABLE IF NOT EXISTS `log_privmsg` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `log_id` int(11) unsigned NOT NULL,
  `bot_id` int(11) unsigned NOT NULL,
  `channel` varchar(255) NOT NULL,
  `nick` varchar(255) NOT NULL,
  `text` blob NOT NULL,
  `time` datetime NOT NULL,
  `direction` enum('<','>') NOT NULL DEFAULT '<',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;

CREATE TABLE IF NOT EXISTS `log_notice` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `log_id` int(11) unsigned NOT NULL,
  `bot_id` int(11) unsigned NOT NULL,
  `target` varchar(255) NOT NULL,
  `nick` varchar(255) NOT NULL,
  `text` blob NOT NULL,
  `time` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;

CREATE TABLE IF NOT EXISTS `log_join` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `log_id` int(11) unsigned NOT NULL,
  `bot_id` int(11) unsigned NOT NULL,
  `channel` varchar(255) NOT NULL,
  `nick` varchar(255) NOT NULL,
  `time` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;

CREATE TABLE IF NOT EXISTS `log_part` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `log_id` int(11) unsigned NOT NULL,
  `bot_id` int(11) unsigned NOT NULL,
  `channel` varchar(255) NOT NULL,
  `nick` varchar(255) NOT NULL,
  `text` blob NOT NULL,
  `time` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;

CREATE TABLE IF NOT EXISTS `log_quit` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `log_id` int(11) unsigned NOT NULL,
  `bot_id` int(11) unsigned NOT NULL,
  `nick` varchar(255) NOT NULL,
  `text` blob NOT NULL,
  `time` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;

CREATE TABLE IF NOT EXISTS `log_kick` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `log_id` int(11) unsigned NOT NULL,
  `bot_id` int(11) unsigned NOT NULL,
  `channel` varchar(255) NOT NULL,
  `nick` varchar(255) NOT NULL,
  `kicked` varchar(255) NOT NULL,
  `text` blob NOT NULL,
  `time` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;

CREATE TABLE IF NOT EXISTS `log_nick` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `log_id` int(11) unsigned NOT NULL,
  `bot_id` int(11) unsigned NOT NULL,
  `oldnick` varchar(255) NOT NULL,
  `newnick` varchar(255) NOT NULL,
  `time` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;

CREATE TABLE IF NOT EXISTS `log_topic` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `log_id` int(11) unsigned NOT NULL,
  `bot_id` int(11) unsigned NOT NULL,
  `channel` varchar(255) NOT NULL,
  `nick` varchar(255) NOT NULL,
  `topic` blob NOT NULL,
  `time` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;

CREATE TABLE IF NOT EXISTS `network` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `network` varchar(255) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `network` (`network`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=3 ;

INSERT INTO `network` (`id`, `network`) VALUES
(1, 'freenode'),
(2, 'quakenet');

CREATE TABLE IF NOT EXISTS `preform` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `network` varchar(255) NOT NULL,
  `text` text NOT NULL,
  `priority` int(11) NOT NULL DEFAULT '50',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=2 ;

INSERT INTO `preform` (`id`, `network`, `text`, `priority`) VALUES
(1, 'freenode', 'JOIN #cerberbot', 50);

CREATE TABLE IF NOT EXISTS `server` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `network_id` int(11) unsigned NOT NULL,
  `servername` varchar(255) NOT NULL,
  `server` varchar(255) NOT NULL,
  `port` varchar(5) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `server` (`server`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=3 ;

INSERT INTO `server` (`id`, `network_id`, `servername`, `server`, `port`) VALUES
(1, 1, 'Random server', 'chat.freenode.net', '6667'),
(2, 2, 'Random server', 'irc.quakenet.org', '6667');

CREATE TABLE IF NOT EXISTS `auth` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `network` varchar(255) NOT NULL,
  `authlevel` enum('none','user','admin') NOT NULL DEFAULT 'none',
  `authname` varchar(255) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;

CREATE TABLE IF NOT EXISTS `web` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `email` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `sid` varchar(255) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;

CREATE TABLE IF NOT EXISTS `send` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `bot_id` int(11) unsigned NOT NULL DEFAULT '0',
  `text` text NOT NULL,
  `priority` int(11) NOT NULL DEFAULT '50',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;

CREATE TABLE IF NOT EXISTS `control` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `bot_id` int(11) unsigned NOT NULL DEFAULT '0',
  `command` varchar(255) NOT NULL,
  `data` text NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;

CREATE TABLE IF NOT EXISTS `status` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `bot_id` int(11) unsigned NOT NULL DEFAULT '0',
  `status` varchar(255) NOT NULL,
  `text` text NOT NULL,
  `data` text NOT NULL,
  `time` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;

CREATE TABLE IF NOT EXISTS `plugins` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `plugin` varchar(255) NOT NULL,
  `url` varchar(255) NOT NULL,
  `autoload` int(11) NOT NULL DEFAULT '0',
  `time` datetime NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `plugin` (`plugin`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;
