-- phpMyAdmin SQL Dump
-- version 3.5.1
-- http://www.phpmyadmin.net
--
-- Host: 127.0.0.1
-- Generation Time: Jan 15, 2015 at 08:58 AM
-- Server version: 5.6.17
-- PHP Version: 5.4.30

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Database: `pin_this_day`
--

-- --------------------------------------------------------

--
-- Table structure for table `bookmarks`
--

CREATE TABLE IF NOT EXISTS `bookmarks` (
  `id` int(11) NOT NULL,
  `url` mediumtext,
  `title` varchar(255) DEFAULT NULL,
  `description` mediumtext,
  `user_id` int(11) NOT NULL,
  `toread` tinyint(1) DEFAULT '0',
  `private` binary(1) DEFAULT '0',
  `slug` char(20) DEFAULT NULL,
  `code` char(3) DEFAULT NULL,
  `source` smallint(6) DEFAULT NULL,
  `added_at` datetime DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `slug` (`slug`),
  UNIQUE KEY `bookmark` (`user_id`,`url`(255)),
  KEY `created` (`created_at`),
  KEY `user` (`user_id`),
  KEY `private` (`private`),
  KEY `toread` (`toread`),
  KEY `updated` (`updated_at`),
  KEY `code` (`code`),
  KEY `multi` (`user_id`,`private`,`toread`,`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `btags`
--

CREATE TABLE IF NOT EXISTS `btags` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `bookmark_id` int(11) NOT NULL,
  `url_id` int(11) DEFAULT NULL,
  `tag` varchar(255) DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `private` tinyint(1) DEFAULT NULL,
  `seq` tinyint(4) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `btag` (`user_id`,`bookmark_id`,`tag`),
  KEY `user` (`user_id`),
  KEY `tag` (`tag`),
  KEY `bookmark` (`bookmark_id`),
  KEY `url` (`url_id`),
  KEY `private` (`private`),
  KEY `usertag` (`user_id`,`tag`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE IF NOT EXISTS `users` (
  `id` int(11) NOT NULL,
  `login` varchar(40) DEFAULT NULL,
  `password` varchar(60) DEFAULT NULL,
  `api_key` varchar(60) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `login` (`login`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
