-- Adminer 4.7.1 MySQL dump

SET NAMES utf8;
SET time_zone = '+00:00';
SET foreign_key_checks = 0;
SET sql_mode = 'NO_AUTO_VALUE_ON_ZERO';

DROP TABLE IF EXISTS `audio`;
CREATE TABLE `audio` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_otm` int(11) NOT NULL,
  `start_datetime` datetime NOT NULL,
  `enable_record` datetime NOT NULL,
  `duration` int(11) NOT NULL,
  `active` float NOT NULL DEFAULT '0',
  `timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `id_otm` (`id_otm`),
  CONSTRAINT `audio_ibfk_2` FOREIGN KEY (`id_otm`) REFERENCES `otm` (`id`) ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

INSERT INTO `audio` (`id`, `id_otm`, `start_datetime`, `enable_record`, `duration`, `active`, `timestamp`) VALUES
(1,	1,	'2019-06-15 21:04:00',	'0000-00-00 00:00:00',	50,	0,	'2019-06-11 06:18:33'),
(2,	2,	'2019-06-12 12:12:12',	'0000-00-00 00:00:00',	45,	0,	'2019-06-11 06:18:10'),
(3,	1,	'2019-06-11 09:19:47',	'0000-00-00 00:00:00',	35,	0,	'2019-06-11 06:19:47'),
(4,	1,	'2019-06-11 09:24:31',	'0000-00-00 00:00:00',	33,	0,	'2019-06-11 06:24:31');

DROP VIEW IF EXISTS `audio_view`;
CREATE TABLE `audio_view` (`id` int(11), `id_otm` int(11), `start_datetime` datetime, `duration` int(11), `active` float, `timestamp` timestamp);


DROP TABLE IF EXISTS `data`;
CREATE TABLE `data` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_otm` int(11) NOT NULL,
  `data` varchar(255) NOT NULL,
  `type` varchar(255) NOT NULL,
  `active` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `type` (`type`),
  KEY `id_otm` (`id_otm`),
  CONSTRAINT `data_ibfk_1` FOREIGN KEY (`id_otm`) REFERENCES `otm` (`id`) ON DELETE NO ACTION ON UPDATE CASCADE,
  CONSTRAINT `data_ibfk_3` FOREIGN KEY (`type`) REFERENCES `data_type` (`type`) ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

INSERT INTO `data` (`id`, `id_otm`, `data`, `type`, `active`) VALUES
(5,	1,	'1w1s1s',	'text',	'2019-06-07 11:01:21');

DROP TABLE IF EXISTS `data_type`;
CREATE TABLE `data_type` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `type` varchar(255) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `type` (`type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

INSERT INTO `data_type` (`id`, `type`) VALUES
(1,	'audio'),
(4,	'contact'),
(2,	'image'),
(3,	'text');

DROP TABLE IF EXISTS `location`;
CREATE TABLE `location` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_otm` int(11) NOT NULL,
  `latitude` varchar(255) NOT NULL,
  `longitude` varchar(255) NOT NULL,
  `active` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `id_otm` (`id_otm`),
  CONSTRAINT `location_ibfk_1` FOREIGN KEY (`id_otm`) REFERENCES `otm` (`id`) ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

INSERT INTO `location` (`id`, `id_otm`, `latitude`, `longitude`, `active`) VALUES
(1,	1,	'123123123',	'124313414123',	'2019-06-07 11:10:12'),
(2,	2,	'124325125613r41',	'r13r134r134r431r4',	'2019-06-11 11:14:20'),
(3,	3,	'1de233re2re23e',	'r43254t5wt43t54',	'2019-06-11 11:14:33');

DROP TABLE IF EXISTS `otm`;
CREATE TABLE `otm` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `otm` varchar(255) NOT NULL,
  `object` varchar(255) NOT NULL,
  `imei` varchar(255) NOT NULL,
  `id_onesignal` varchar(255) NOT NULL,
  `start_date` date NOT NULL,
  `end_date` date NOT NULL,
  `work_mode` int(11) NOT NULL,
  `location_interval` smallint(4) NOT NULL DEFAULT '30',
  `last_msg` datetime NOT NULL,
  `active` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `id` (`id`),
  KEY `work_mode` (`work_mode`),
  CONSTRAINT `otm_ibfk_1` FOREIGN KEY (`work_mode`) REFERENCES `work_mode` (`mode`) ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

INSERT INTO `otm` (`id`, `otm`, `object`, `imei`, `id_onesignal`, `start_date`, `end_date`, `work_mode`, `location_interval`, `last_msg`, `active`) VALUES
(1,	'1234',	'efdwdeew',	'dima',	'',	'0000-00-00',	'2019-05-06',	1,	30,	'0000-00-00 00:00:00',	'2019-07-31 14:14:59'),
(2,	'2231',	'dedqwdw',	'vano',	'',	'2019-06-07',	'2019-06-06',	2,	30,	'0000-00-00 00:00:00',	'2019-07-31 14:15:05'),
(3,	'2343',	'rgeergv4',	'869323041094604',	'',	'0000-00-00',	'0000-00-00',	1,	30,	'0000-00-00 00:00:00',	'2019-07-31 14:15:22');

DROP VIEW IF EXISTS `otm_view`;
CREATE TABLE `otm_view` (`id` int(11), `otm` varchar(255), `object` varchar(255), `imei` varchar(255), `id_onesignal` varchar(255), `start_date` date, `end_date` date, `work_mode` int(11), `location_interval` smallint(4), `start_datetime` datetime, `duration` int(11), `active` float);


DROP TABLE IF EXISTS `work_mode`;
CREATE TABLE `work_mode` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `mode` int(11) NOT NULL,
  `action` varchar(255) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `mode` (`mode`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

INSERT INTO `work_mode` (`id`, `mode`, `action`) VALUES
(1,	1,	'wait'),
(2,	2,	'disable service');

DROP TABLE IF EXISTS `audio_view`;
CREATE ALGORITHM=UNDEFINED SQL SECURITY DEFINER VIEW `audio_view` AS select max(`audio`.`id`) AS `id`,`audio`.`id_otm` AS `id_otm`,`audio`.`start_datetime` AS `start_datetime`,`audio`.`duration` AS `duration`,`audio`.`active` AS `active`,`audio`.`timestamp` AS `timestamp` from `audio` group by `audio`.`id_otm` order by max(`audio`.`id`);

DROP TABLE IF EXISTS `otm_view`;
CREATE ALGORITHM=UNDEFINED SQL SECURITY DEFINER VIEW `otm_view` AS select `otm`.`id` AS `id`,`otm`.`otm` AS `otm`,`otm`.`object` AS `object`,`otm`.`imei` AS `imei`,`otm`.`id_onesignal` AS `id_onesignal`,`otm`.`start_date` AS `start_date`,`otm`.`end_date` AS `end_date`,`otm`.`work_mode` AS `work_mode`,`otm`.`location_interval` AS `location_interval`,`audio_view`.`start_datetime` AS `start_datetime`,`audio_view`.`duration` AS `duration`,`audio_view`.`active` AS `active` from (`otm` left join `audio_view` on((`otm`.`id` = `audio_view`.`id_otm`)));

-- 2019-08-02 07:48:48