-- phpMyAdmin SQL Dump
-- version 3.5.5
-- http://www.phpmyadmin.net
--
-- Värd: localhost
-- Skapad: 08 sep 2013 kl 20:02
-- Serverversion: 5.5.29
-- PHP-version: 5.4.10

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";

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

INSERT INTO `accounts` (`id`, `email`, `fullName`, `accessToken`, `accessTokenSecret`) VALUES
(1, 'stefan@nada.kth.se', 'Stefan Arnborg', '0232e4910e5ecf0674503e3ea2ba9953051dd6d1d', '55f90368edd81da6676c63ffa962ab0e');

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
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=5 ;

--
-- Dumpning av Data i tabell `tubAccountSettings`
--

INSERT INTO `tubAccountSettings` (`id`, `email`, `settingKey`, `value`) VALUES
(1, 'stefan@nada.kth.se', 'defaultTemp', '38,5'),
(2, 'stefan@nada.kth.se', 'tubDeviceId', '165127'),
(3, 'stefan@nada.kth.se', 'airSensorId', '771199'),
(4, 'stefan@nada.kth.se', 'tubSensorId', '948635');

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
(1, 'tubSensorId', 'Sensor for the tub', 'sensors', 1),
(2, 'tubDeviceId', 'Device for the tub', 'devices', 1),
(3, 'airSensorId', 'Sensor for air temperature', 'sensors', 0),
(4, 'defaultTemp', 'Default bath temperature (&deg;C)', NULL, 1);

-- --------------------------------------------------------

--
-- Tabellstruktur `tubTimes`
--

CREATE TABLE `tubTimes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `email` varchar(252) NOT NULL,
  `time` int(11) NOT NULL,
  `temp` float NOT NULL,
  `activated` tinyint(4) NOT NULL DEFAULT '0',
  `deactivated` tinyint(4) NOT NULL DEFAULT '0',
  `created` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=4 ;
