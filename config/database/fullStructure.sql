-- phpMyAdmin SQL Dump
-- version 3.4.10.1deb1
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Generation Time: Oct 10, 2012 at 02:51 PM
-- Server version: 5.5.24
-- PHP Version: 5.3.10-1ubuntu3.4

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Database: `webtub`
--

-- --------------------------------------------------------

--
-- Table structure for table `api_data`
--

CREATE TABLE IF NOT EXISTS `api_data` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `recommendation_id` int(11) NOT NULL,
  `key` varchar(256) NOT NULL,
  `value` text NOT NULL,
  `source` varchar(16) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `recommendation_id` (`recommendation_id`),
  KEY `key` (`key`),
  KEY `source` (`source`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `attributes`
--

CREATE TABLE IF NOT EXISTS `attributes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(252) NOT NULL,
  `code` varchar(28) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=2 ;

-- --------------------------------------------------------

--
-- Table structure for table `cache`
--

CREATE TABLE IF NOT EXISTS `cache` (
  `key` varchar(56) NOT NULL,
  `data` longtext NOT NULL,
  `updateTime` int(11) NOT NULL,
  UNIQUE KEY `key` (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `climate_data`
--

CREATE TABLE IF NOT EXISTS `climate_data` (
  `key` int(11) NOT NULL AUTO_INCREMENT,
  `airportCode` varchar(10) NOT NULL,
  `percipitation_1` double(10,0) NOT NULL,
  `temp_max_1` decimal(10,0) NOT NULL,
  `temp_min_1` decimal(10,0) NOT NULL,
  `percipitation_2` decimal(10,0) NOT NULL,
  `temp_max_2` decimal(10,0) NOT NULL,
  `temp_min_2` decimal(10,0) NOT NULL,
  `percipitation_3` decimal(10,0) NOT NULL,
  `temp_max_3` decimal(10,0) NOT NULL,
  `temp_min_3` decimal(10,0) NOT NULL,
  `percipitation_4` decimal(10,0) NOT NULL,
  `temp_max_4` decimal(10,0) NOT NULL,
  `temp_min_4` decimal(10,0) NOT NULL,
  `percipitation_5` decimal(10,0) NOT NULL,
  `temp_max_5` decimal(10,0) NOT NULL,
  `temp_min_5` decimal(10,0) NOT NULL,
  `percipitation_6` decimal(10,0) NOT NULL,
  `temp_max_6` decimal(10,0) NOT NULL,
  `temp_min_6` decimal(10,0) NOT NULL,
  `percipitation_7` decimal(10,0) NOT NULL,
  `temp_max_7` decimal(10,0) NOT NULL,
  `temp_min_7` decimal(10,0) NOT NULL,
  `percipitation_8` decimal(10,0) NOT NULL,
  `temp_max_8` decimal(10,0) NOT NULL,
  `temp_min_8` decimal(10,0) NOT NULL,
  `percipitation_9` decimal(10,0) NOT NULL,
  `temp_max_9` decimal(10,0) NOT NULL,
  `temp_min_9` decimal(10,0) NOT NULL,
  `percipitation_10` decimal(10,0) NOT NULL,
  `temp_max_10` decimal(10,0) NOT NULL,
  `temp_min_10` decimal(10,0) NOT NULL,
  `percipitation_11` decimal(10,0) NOT NULL,
  `temp_max_11` decimal(10,0) NOT NULL,
  `temp_min_11` decimal(10,0) NOT NULL,
  `percipitation_12` decimal(10,0) NOT NULL,
  `temp_max_12` decimal(10,0) NOT NULL,
  `temp_min_12` decimal(10,0) NOT NULL,
  `airportName` varchar(252) NOT NULL,
  `cityName` varchar(252) NOT NULL,
  PRIMARY KEY (`key`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=22353 ;

-- --------------------------------------------------------

--
-- Table structure for table `destinations`
--

CREATE TABLE IF NOT EXISTS `destinations` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(252) NOT NULL,
  `cityCode` varchar(3) NOT NULL,
  `countryName` varchar(252) NOT NULL,
  `countryCode` varchar(3) NOT NULL,
  `gmtOffset` varchar(10) NOT NULL,
  `airportCode` varchar(10) DEFAULT NULL,
  `isActive` tinyint(4) NOT NULL DEFAULT '0',
  `lat` varchar(20) NOT NULL,
  `lng` varchar(20) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `name` (`name`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=101 ;

-- --------------------------------------------------------

--
-- Table structure for table `destination_attributes`
--

CREATE TABLE IF NOT EXISTS `destination_attributes` (
  `key` int(11) NOT NULL AUTO_INCREMENT,
  `destinationId` int(11) NOT NULL,
  `attributeId` int(11) NOT NULL,
  `value` varchar(252) NOT NULL,
  PRIMARY KEY (`key`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=100 ;

-- --------------------------------------------------------

--
-- Table structure for table `destination_files`
--

CREATE TABLE IF NOT EXISTS `destination_files` (
  `key` int(11) NOT NULL AUTO_INCREMENT,
  `destinationId` int(11) NOT NULL,
  `fileId` int(11) NOT NULL,
  `startUsingAt` varchar(5) NOT NULL,
  PRIMARY KEY (`key`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=11 ;

-- --------------------------------------------------------

--
-- Table structure for table `destination_origins`
--

CREATE TABLE IF NOT EXISTS `destination_origins` (
  `key` int(11) NOT NULL AUTO_INCREMENT,
  `destinationId` int(11) NOT NULL,
  `originId` int(11) NOT NULL,
  PRIMARY KEY (`key`),
  KEY `destinationId` (`destinationId`),
  KEY `originId` (`originId`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `files`
--

CREATE TABLE IF NOT EXISTS `files` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `filename` varchar(100) NOT NULL,
  `uri` varchar(252) NOT NULL,
  `filemime` varchar(50) NOT NULL,
  `filesize` int(11) NOT NULL,
  `timestamp` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=11 ;

-- --------------------------------------------------------

--
-- Table structure for table `origins`
--

CREATE TABLE IF NOT EXISTS `origins` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(252) NOT NULL,
  `countryCodes` varchar(252) DEFAULT NULL,
  `isActive` tinyint(4) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=3 ;

-- --------------------------------------------------------

--
-- Table structure for table `recommendations`
--

CREATE TABLE IF NOT EXISTS `recommendations` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `destination_id` int(11) NOT NULL,
  `name` varchar(512) COLLATE utf8_unicode_ci NOT NULL,
  `created_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `destination_id` (`destination_id`),
  KEY `created_at` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;

--
-- Table structure for table `shares`
--

CREATE TABLE IF NOT EXISTS `shares` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `sessionId` varchar(256) COLLATE utf8_swedish_ci NOT NULL,
  `removedRecommendations` varchar(512) COLLATE utf8_swedish_ci NOT NULL,
  PRIMARY KEY (`id`),
  KEY `sessionId` (`sessionId`(255))
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_swedish_ci AUTO_INCREMENT=1 ;


/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
