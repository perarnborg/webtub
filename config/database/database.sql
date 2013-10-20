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
