SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

CREATE TABLE IF NOT EXISTS `contributions` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `user` int(10) unsigned NOT NULL,
  `project` int(10) unsigned NOT NULL,
  `time_contributed` int(10) unsigned NOT NULL DEFAULT '0',
  `editHash` varchar(160) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=141 ;

-- --------------------------------------------------------

CREATE TABLE IF NOT EXISTS `invitations` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `projectID` int(10) unsigned NOT NULL DEFAULT '0',
  `userID` int(10) unsigned NOT NULL DEFAULT '0',
  `approved` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `duplicates` (`projectID`,`userID`),
  KEY `combination` (`projectID`,`userID`,`approved`),
  KEY `membership` (`userID`,`approved`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=3 ;

-- --------------------------------------------------------

CREATE TABLE IF NOT EXISTS `login_attempts` (
  `username` varchar(160) NOT NULL DEFAULT '',
  `attempt_time` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`username`,`attempt_time`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

CREATE TABLE IF NOT EXISTS `projects` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(160) NOT NULL,
  `user` int(10) unsigned NOT NULL DEFAULT '0',
  `default_language` varchar(100) NOT NULL DEFAULT 'values',
  `visibility` enum('public','protected','private') NOT NULL DEFAULT 'public',
  `landing_html` text NOT NULL,
  PRIMARY KEY (`id`),
  KEY `user` (`user`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=25 ;

-- --------------------------------------------------------

CREATE TABLE IF NOT EXISTS `translations` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `project` int(10) unsigned NOT NULL DEFAULT '0',
  `language` varchar(100) NOT NULL,
  `ident_code` varchar(100) NOT NULL,
  `position` varchar(100) NOT NULL DEFAULT '',
  `type` enum('string','string-array','plurals') NOT NULL,
  `phrase` text NOT NULL,
  `enabled` tinyint(1) unsigned NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`),
  UNIQUE KEY `combination` (`project`,`language`,`ident_code`,`position`),
  KEY `counter` (`project`,`enabled`,`language`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=12491 ;

-- --------------------------------------------------------

CREATE TABLE IF NOT EXISTS `translations_pending` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `project` int(10) unsigned NOT NULL DEFAULT '0',
  `language` varchar(100) NOT NULL,
  `originalID` int(10) unsigned NOT NULL DEFAULT '0',
  `phrase` text NOT NULL,
  `done` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `creation_time` int(10) unsigned NOT NULL DEFAULT '0',
  `creation_user` varchar(160) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`),
  KEY `creation_user` (`creation_user`),
  KEY `combination` (`project`,`done`,`language`,`creation_user`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=9587 ;

-- --------------------------------------------------------

CREATE TABLE IF NOT EXISTS `users` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `username` varchar(160) NOT NULL,
  `password_hash` varchar(160) NOT NULL,
  `account_type` enum('developer','translator') NOT NULL DEFAULT 'developer',
  `join_time` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`),
  KEY `combination` (`username`,`password_hash`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=36 ;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
