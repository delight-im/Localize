SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;


CREATE TABLE IF NOT EXISTS `contributions` (
  `userID` int(10) unsigned NOT NULL,
  `repositoryID` int(10) unsigned NOT NULL,
  `editCount` int(10) unsigned NOT NULL DEFAULT '1',
  PRIMARY KEY (`userID`,`repositoryID`),
  KEY `selection` (`repositoryID`,`editCount`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `edits` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `repositoryID` int(11) unsigned NOT NULL,
  `languageID` int(11) unsigned NOT NULL,
  `referencedPhraseID` int(11) unsigned NOT NULL,
  `phraseSubKey` varchar(255) NOT NULL,
  `userID` int(10) unsigned NOT NULL,
  `suggestedValue` text NOT NULL,
  `submit_time` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `combination` (`repositoryID`,`languageID`,`referencedPhraseID`,`phraseSubKey`,`userID`),
  KEY `userID` (`userID`),
  KEY `selection` (`repositoryID`,`languageID`,`submit_time`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `groups` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `repositoryID` int(10) unsigned NOT NULL,
  `name` varchar(255) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `combination` (`repositoryID`,`name`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `invitations` (
  `repositoryID` int(10) unsigned NOT NULL,
  `userID` int(10) unsigned NOT NULL,
  `request_time` int(10) unsigned NOT NULL,
  `accepted` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`repositoryID`,`userID`),
  KEY `selection` (`userID`,`request_time`),
  KEY `reviewing` (`repositoryID`,`accepted`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `native_languages` (
  `userID` int(10) unsigned NOT NULL,
  `languageID` int(10) unsigned NOT NULL,
  PRIMARY KEY (`userID`,`languageID`),
  KEY `languageID` (`languageID`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `phrases` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `repositoryID` int(10) unsigned NOT NULL,
  `languageID` int(10) unsigned NOT NULL,
  `phraseKey` varchar(255) NOT NULL,
  `groupID` int(10) unsigned NOT NULL DEFAULT '0',
  `enabled` tinyint(1) unsigned NOT NULL,
  `payload` blob NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `combination` (`repositoryID`,`languageID`,`phraseKey`),
  KEY `groupID` (`groupID`),
  KEY `phraseCount` (`repositoryID`,`languageID`,`groupID`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `repositories` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `visibility` tinyint(1) unsigned NOT NULL,
  `defaultLanguage` int(10) unsigned NOT NULL,
  `creation_date` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `name` (`name`),
  FULLTEXT KEY `name_2` (`name`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `roles` (
  `userID` int(10) unsigned NOT NULL,
  `repositoryID` int(10) unsigned NOT NULL,
  `role` int(10) unsigned NOT NULL,
  PRIMARY KEY (`userID`,`repositoryID`),
  KEY `repositoryID` (`repositoryID`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `translationSessions` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `repositoryID` int(10) unsigned NOT NULL,
  `languageID` int(10) unsigned NOT NULL,
  `userID` int(10) unsigned NOT NULL,
  `secret` varchar(255) NOT NULL,
  `timeStart` int(10) unsigned NOT NULL DEFAULT '0',
  `timeEnd` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `combination` (`repositoryID`,`languageID`,`secret`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `users` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `username` varchar(255) NOT NULL,
  `password` blob NOT NULL,
  `real_name` varchar(255) NOT NULL DEFAULT '',
  `type` tinyint(1) unsigned NOT NULL,
  `join_date` int(11) NOT NULL,
  `last_login` int(10) unsigned NOT NULL DEFAULT '0',
  `localeCountry` varchar(255) NOT NULL DEFAULT '',
  `localeTimezone` varchar(255) NOT NULL DEFAULT '',
  `email` varchar(255) NOT NULL DEFAULT '',
  `email_lastVerificationAttempt` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `verificationTokens` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `userID` int(10) unsigned NOT NULL,
  `token` varchar(255) NOT NULL,
  `validUntil` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `token` (`token`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `watchers` (
  `repositoryID` int(10) unsigned NOT NULL,
  `eventID` tinyint(1) unsigned NOT NULL,
  `userID` int(10) unsigned NOT NULL,
  `lastNotification` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`repositoryID`,`eventID`,`userID`),
  KEY `selection` (`repositoryID`,`userID`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
