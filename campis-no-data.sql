-- Adminer 4.2.1 MySQL dump

SET NAMES utf8;
SET time_zone = '+00:00';
SET foreign_key_checks = 0;
SET sql_mode = 'NO_AUTO_VALUE_ON_ZERO';

DROP TABLE IF EXISTS `camp`;
CREATE TABLE `camp` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `active` tinyint(1) NOT NULL,
  `year` smallint(4) NOT NULL,
  `name` varchar(100) COLLATE utf8_czech_ci NOT NULL,
  `start` varchar(20) COLLATE utf8_czech_ci DEFAULT NULL,
  `end` varchar(20) COLLATE utf8_czech_ci DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_czech_ci;

INSERT INTO `camp` (`id`, `active`, `year`, `name`, `start`, `end`) VALUES
(1,	0,	1970,	'test',	NULL,	NULL),
(2,	0,	1970,	'Test',	NULL,	NULL),
(3,	1,	2015,	'Dračí pouť',	'12.7.2015',	'25.7.2015'),
(4,	1,	2012,	'Cesta kolem světa',	'15.7.2012',	'28.7.2012'),
(5,	1,	2013,	'Stroj času',	'14.7.2013',	'27.7.2013'),
(6,	1,	2014,	'Dobyvatelé',	'13.7.2014',	'26.7.2014'),
(7,	1,	2019,	'Stroj času 2',	NULL,	NULL);

DROP TABLE IF EXISTS `category`;
CREATE TABLE `category` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `name` varchar(20) COLLATE utf8_czech_ci NOT NULL,
  `short` varchar(5) COLLATE utf8_czech_ci NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `short` (`short`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_czech_ci;

INSERT INTO `category` (`id`, `name`, `short`) VALUES
(1,	'Nejmladší dívky',	'NMD'),
(2,	'Nejmladší chlapci',	'NMCH'),
(3,	'Mladší dívky',	'MD'),
(4,	'Mladší chlapci',	'MCH'),
(5,	'Starší dívky',	'SD'),
(6,	'Starší chlapci',	'SCH');

DROP TABLE IF EXISTS `child`;
CREATE TABLE `child` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `active` tinyint(1) NOT NULL,
  `rc` varchar(10) COLLATE utf8_czech_ci NOT NULL,
  `name` varchar(20) COLLATE utf8_czech_ci NOT NULL,
  `surname` varchar(20) COLLATE utf8_czech_ci NOT NULL,
  `adress` varchar(100) COLLATE utf8_czech_ci NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `rc` (`rc`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_czech_ci;


DROP TABLE IF EXISTS `child_camp`;
CREATE TABLE `child_camp` (
  `child_id` int(10) NOT NULL,
  `camp_id` int(10) NOT NULL,
  `team_id` int(10) NOT NULL,
  `category_id` int(10) NOT NULL,
  KEY `child` (`child_id`,`camp_id`),
  KEY `camp_id` (`camp_id`),
  KEY `team_id` (`team_id`),
  KEY `category_id` (`category_id`),
  CONSTRAINT `child_camp_ibfk_1` FOREIGN KEY (`child_id`) REFERENCES `child` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `child_camp_ibfk_2` FOREIGN KEY (`camp_id`) REFERENCES `camp` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `child_camp_ibfk_3` FOREIGN KEY (`team_id`) REFERENCES `team` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `child_camp_ibfk_4` FOREIGN KEY (`category_id`) REFERENCES `category` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_czech_ci;


DROP TABLE IF EXISTS `function`;
CREATE TABLE `function` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `name` varchar(50) COLLATE utf8_czech_ci NOT NULL,
  `short` varchar(5) COLLATE utf8_czech_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_czech_ci;

INSERT INTO `function` (`id`, `name`, `short`) VALUES
(1,	'Hlavní vedoucí tábora',	'HVT'),
(2,	'Zástupce hlavního vedoucího tábora',	'ZHVT'),
(3,	'Oddílový vedoucí',	'OV'),
(4,	'Oddílový instruktor',	'OI'),
(5,	'Kuchyň',	'KUCH'),
(6,	'Hospodář',	'HOSP'),
(7,	'Výpomoc',	'POM'),
(8,	'Zdravotník',	'ZDR');

DROP TABLE IF EXISTS `game`;
CREATE TABLE `game` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `active` tinyint(1) NOT NULL,
  `name` varchar(100) COLLATE utf8_czech_ci NOT NULL,
  `description` varchar(1000) COLLATE utf8_czech_ci NOT NULL,
  `type` int(10) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`),
  KEY `type` (`type`),
  CONSTRAINT `game_ibfk_1` FOREIGN KEY (`type`) REFERENCES `game_type` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_czech_ci;

INSERT INTO `game` (`id`, `active`, `name`, `description`, `type`) VALUES
(1,	1,	'Šátkovaná',	'Hra s šátky na hřišti/louce. Čas cca 1 hod.',	4),
(2,	1,	'Fazolačka',	'Kolik fazolí zvládnete hodit do svého hrníčku?',	4),
(3,	1,	'Pamatovací závod',	'Vyhrává ten, kdo si zapamatuje nejvíce obrázků po trase závodu.',	1),
(4,	1,	'Netradiční závod',	'Závod s netradičními úkoly na stanovištích.',	1),
(5,	1,	'Cesta peklem',	'Celotáborová hra Cesta peklem.',	3),
(6,	1,	'Kolíkovaná',	'Hledání kolíků s čísly.',	4),
(7,	1,	'Zjisti sílu nepřítele',	'',	2),
(8,	1,	'Hodnocení vlajek',	'Body za oddílové vlajky',	2),
(9,	1,	'DZBZ',	'Dukelský závod branné zdatnosti',	1),
(10,	1,	'Safari',	'Lov zvěře - sbírání lístečků',	2),
(11,	1,	'Ostatní',	'Blíže nespecifikovaná bodovanáí činnost',	4),
(12,	1,	'Nepřítel před branami',	'Dostaň se nepozorovaně do tábora, noční hra.',	5),
(13,	1,	'Kvízový závod',	'Závod jednotlivců s kvízovými otázkami.',	1),
(14,	1,	'Závod Cross',	'Závod jednotlivců, stanoviště vzduchovka, luk, prak',	1),
(15,	1,	'Osobní hodnocení',	'Hodnocení jednoltivců za aktivitu',	7),
(16,	1,	'Služba',	'Hodnocení splnění služby',	6),
(17,	1,	'CTH Itálie',	'Etapovka z cestyk kolem světa.',	3),
(34,	1,	'CTH Řecko',	'Etapovka z cestyk kolem světa.',	3),
(35,	1,	'CTH Rallye Paříž Dakar',	'Etapovka z cestyk kolem světa.',	3),
(36,	1,	'CTH Voda z oázy',	'Etapovka z cestyk kolem světa.',	3),
(37,	1,	'CTH Poklad Faraonů',	'Etapovka z cestyk kolem světa.',	3),
(38,	1,	'CTH Madagaskar',	'Etapovka z cestyk kolem světa.',	3),
(39,	1,	'CTH Austrálie',	'Etapovka z cestyk kolem světa.',	3),
(40,	1,	'CTH Vaření z asie',	'Etapovka z cestyk kolem světa.',	3),
(41,	1,	'CTH Nepál',	'Etapovka z cestyk kolem světa.',	3),
(42,	1,	'CTH Antarktida',	'Etapovka z cestyk kolem světa.',	3),
(43,	1,	'CTH Kanada',	'Etapovka z cestyk kolem světa.',	3),
(44,	1,	'CTH Golf v Americe',	'Etapovka z cestyk kolem světa.',	3),
(45,	1,	'CTH Knihovna kongresu',	'Etapovka z cestyk kolem světa.',	3),
(46,	1,	'CTH Amazonie',	'Etapovka z cestyk kolem světa.',	3),
(47,	1,	'CTH karneval v Riu',	'Etapovka z cestyk kolem světa.',	3),
(48,	1,	'Závod se střelbou',	'Blíže neurčený závod s různou střelbou,',	1),
(49,	1,	'CTH Budoucnost',	'Etapová hra Stroj času',	3),
(50,	1,	'CTH Virus',	'Etapová hra Stroj času',	3),
(51,	1,	'CTH Hon na titány',	'Etapová hra Stroj času',	3),
(52,	1,	'CTH Pravěk',	'Etapová hra Stroj času',	3),
(53,	1,	'CTH Král Artuš',	'Etapová hra Stroj času',	3),
(54,	1,	'CTH Starověké Řecko',	'Etapová hra Stroj času',	3),
(55,	1,	'CTH Útok',	'Etapovka z Dobyvatelů.',	3),
(56,	1,	'CTH Celebrity',	'Etapovka z Dobyvatelů.',	3),
(57,	1,	'CTH Cenné info',	'Etapovka z Dobyvatelů.',	3),
(58,	1,	'CTH Dědictví',	'Etapovka z Dobyvatelů.',	3),
(59,	1,	'CTH Dobytí sídla',	'Etapovka z Dobyvatelů.',	3),
(60,	1,	'CTH Majetek',	'Etapovka z Dobyvatelů.',	3),
(61,	1,	'CTH Městská štafeta',	'Etapovka z Dobyvatelů.',	3),
(62,	1,	'CTH Přestřelka',	'Etapovka z Dobyvatelů.',	3),
(63,	1,	'CTH Přesun sídla',	'Etapovka z Dobyvatelů.',	3),
(64,	1,	'CTH Tma',	'Etapovka z Dobyvatelů.',	3),
(65,	1,	'Bobříci',	'Zálesácké zkoušky',	5),
(66,	1,	'Střelecký souboj',	'Střlecký souboj, kde se soutěží ve trojicích nezávislých na oddílech.',	5),
(67,	1,	'Scénky',	'Předvádění divadelních scének',	2);

DROP TABLE IF EXISTS `game_type`;
CREATE TABLE `game_type` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `active` tinyint(1) NOT NULL,
  `name` varchar(50) COLLATE utf8_czech_ci NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_czech_ci;

INSERT INTO `game_type` (`id`, `active`, `name`) VALUES
(1,	1,	'Závod jednotlivců'),
(2,	1,	'Oddílová soutěž'),
(3,	1,	'Celotáborová hra'),
(4,	1,	'Doplňková hra'),
(5,	1,	'Soutěž jednotlivců'),
(6,	1,	'Služba'),
(7,	1,	'Osobní hodnocení');

DROP TABLE IF EXISTS `lead`;
CREATE TABLE `lead` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `active` tinyint(1) NOT NULL,
  `name` varchar(20) COLLATE utf8_czech_ci NOT NULL,
  `surname` varchar(20) COLLATE utf8_czech_ci NOT NULL,
  `rc` varchar(10) COLLATE utf8_czech_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_czech_ci;


DROP TABLE IF EXISTS `lead_camp`;
CREATE TABLE `lead_camp` (
  `lead_id` int(10) NOT NULL,
  `camp_id` int(10) NOT NULL,
  `function_id` int(10) NOT NULL,
  KEY `lead_id` (`lead_id`),
  KEY `camp_id` (`camp_id`),
  KEY `function_id` (`function_id`),
  CONSTRAINT `lead_camp_ibfk_1` FOREIGN KEY (`lead_id`) REFERENCES `lead` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `lead_camp_ibfk_2` FOREIGN KEY (`camp_id`) REFERENCES `camp` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `lead_camp_ibfk_3` FOREIGN KEY (`function_id`) REFERENCES `function` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_czech_ci;


DROP TABLE IF EXISTS `points_child`;
CREATE TABLE `points_child` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `active` tinyint(1) NOT NULL,
  `childId` int(10) NOT NULL,
  `points` int(10) NOT NULL,
  `game` int(10) NOT NULL,
  `date` varchar(30) COLLATE utf8_czech_ci NOT NULL,
  `camp` int(10) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `childId` (`childId`,`game`,`camp`),
  KEY `camp` (`camp`),
  KEY `points_child_ibfk_2` (`game`),
  CONSTRAINT `points_child_ibfk_1` FOREIGN KEY (`childId`) REFERENCES `child` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `points_child_ibfk_2` FOREIGN KEY (`game`) REFERENCES `game` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `points_child_ibfk_3` FOREIGN KEY (`camp`) REFERENCES `camp` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_czech_ci;


DROP TABLE IF EXISTS `points_team`;
CREATE TABLE `points_team` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `active` tinyint(1) NOT NULL,
  `team` int(10) NOT NULL,
  `game` int(10) NOT NULL,
  `points` int(10) NOT NULL,
  `date` varchar(30) COLLATE utf8_czech_ci NOT NULL,
  `camp` int(10) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `team` (`team`,`game`,`camp`),
  KEY `game` (`game`),
  KEY `camp` (`camp`),
  CONSTRAINT `points_team_ibfk_2` FOREIGN KEY (`game`) REFERENCES `game` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `points_team_ibfk_3` FOREIGN KEY (`camp`) REFERENCES `camp` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `points_team_ibfk_4` FOREIGN KEY (`team`) REFERENCES `team` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_czech_ci;


DROP TABLE IF EXISTS `race`;
CREATE TABLE `race` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `active` tinyint(1) NOT NULL,
  `game` int(10) NOT NULL,
  `date` varchar(30) COLLATE utf8_czech_ci NOT NULL,
  `state` tinyint(2) NOT NULL,
  `spacing` varchar(5) COLLATE utf8_czech_ci NOT NULL DEFAULT '02:00',
  `note` varchar(300) COLLATE utf8_czech_ci NOT NULL,
  `camp` int(10) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `game` (`game`,`camp`),
  KEY `camp` (`camp`),
  KEY `state` (`state`),
  CONSTRAINT `race_ibfk_1` FOREIGN KEY (`game`) REFERENCES `game` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `race_ibfk_2` FOREIGN KEY (`camp`) REFERENCES `camp` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `race_ibfk_3` FOREIGN KEY (`state`) REFERENCES `race_state` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_czech_ci;


DROP TABLE IF EXISTS `race_state`;
CREATE TABLE `race_state` (
  `id` tinyint(2) NOT NULL AUTO_INCREMENT,
  `name` varchar(15) COLLATE utf8_czech_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_czech_ci;


DROP TABLE IF EXISTS `results_team`;
CREATE TABLE `results_team` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `active` tinyint(1) NOT NULL,
  `team` int(10) NOT NULL,
  `game` int(10) NOT NULL,
  `rank` smallint(2) NOT NULL,
  `points` int(10) NOT NULL,
  `date` varchar(50) COLLATE utf8_czech_ci NOT NULL,
  `note` varchar(300) COLLATE utf8_czech_ci NOT NULL,
  `camp` int(10) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `team` (`team`,`game`,`camp`),
  KEY `game` (`game`),
  KEY `camp` (`camp`),
  CONSTRAINT `results_team_ibfk_1` FOREIGN KEY (`game`) REFERENCES `game` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `results_team_ibfk_2` FOREIGN KEY (`team`) REFERENCES `team` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `results_team_ibfk_3` FOREIGN KEY (`camp`) REFERENCES `camp` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_czech_ci;


DROP TABLE IF EXISTS `team`;
CREATE TABLE `team` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `active` tinyint(1) NOT NULL,
  `number` int(10) NOT NULL,
  `name` varchar(100) COLLATE utf8_czech_ci NOT NULL,
  `headId` int(10) NOT NULL,
  `instrId` int(10) NOT NULL,
  `camp` int(10) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `headId` (`headId`,`instrId`),
  KEY `camp` (`camp`),
  KEY `instrId` (`instrId`),
  CONSTRAINT `team_ibfk_1` FOREIGN KEY (`camp`) REFERENCES `camp` (`id`),
  CONSTRAINT `team_ibfk_2` FOREIGN KEY (`headId`) REFERENCES `lead` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `team_ibfk_3` FOREIGN KEY (`instrId`) REFERENCES `lead` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_czech_ci;


-- 2019-07-11 18:40:44