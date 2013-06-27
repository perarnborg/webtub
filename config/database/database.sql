-- phpMyAdmin SQL Dump
-- version 3.5.5
-- http://www.phpmyadmin.net
--
-- Värd: localhost
-- Skapad: 27 jun 2013 kl 13:25
-- Serverversion: 5.5.29
-- PHP-version: 5.4.10

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Databas: `webtub`
--

-- --------------------------------------------------------

--
-- Tabellstruktur `accounts`
--

CREATE TABLE `accounts` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `email` varchar(252) NOT NULL,
  `fullName` varchar(252) NOT NULL,
  `accessToken` varchar(252) DEFAULT NULL,
  `accessTokenSecret` varchar(252) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=2 ;

--
-- Dumpning av Data i tabell `accounts`
--

-- --------------------------------------------------------

--
-- Tabellstruktur `tubAccountSettings`
--

CREATE TABLE `tubAccountSettings` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `email` varchar(252) NOT NULL,
  `settingKey` varchar(100) NOT NULL,
  `value` varchar(252) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Tabellstruktur `tubSettings`
--

CREATE TABLE `tubSettings` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `key` varchar(100) NOT NULL,
  `name` varchar(100) NOT NULL,
  `sourceEndpoint` varchar(252) DEFAULT NULL,
  `required` tinyint(4) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=5 ;

--
-- Dumpning av Data i tabell `tubSettings`
--

INSERT INTO `tubSettings` (`id`, `key`, `name`, `sourceEndpoint`, `required`) VALUES
(1, 'tubSensorId', 'Sensor for the tub', NULL, 1),
(2, 'tubDeviceId', 'Device for the tub', NULL, 1),
(3, 'airSensorId', 'Sensor for air temperature', NULL, 0),
(4, 'defaultTemp', 'Default bath temperature', NULL, 1);

-- --------------------------------------------------------

--
-- Tabellstruktur `tubTimes`
--

CREATE TABLE `tubTimes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `email` varchar(252) NOT NULL,
  `time` int(11) NOT NULL,
  `temp` int(11) NOT NULL,
  `activated` tinyint(4) NOT NULL DEFAULT '0',
  `deactivated` tinyint(4) NOT NULL DEFAULT '0',
  `created` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
