-- MySQL dump 10.13  Distrib 5.1.31, for debian-linux-gnu (i486)
--
-- Host: localhost    Database: hashmark_test
-- ------------------------------------------------------
-- Server version	5.1.31-1ubuntu2-log

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `agents`
--

DROP TABLE IF EXISTS `agents`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `agents` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(30) COLLATE utf8_unicode_ci NOT NULL DEFAULT '' COMMENT 'PHP class',
  PRIMARY KEY (`id`),
  UNIQUE KEY `idx_name` (`name`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='Classes available to visit scalars';
SET character_set_client = @saved_cs_client;

--
-- Table structure for table `agents_scalars`
--

DROP TABLE IF EXISTS `agents_scalars`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `agents_scalars` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `agent_id` int(10) unsigned NOT NULL DEFAULT '0',
  `scalar_id` int(10) unsigned NOT NULL DEFAULT '0',
  `config` text COLLATE utf8_unicode_ci NOT NULL COMMENT 'Serialized PHP array',
  `error` varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `status` enum('Unscheduled','Scheduled','Running') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'Unscheduled',
  `frequency` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'Minutes',
  `start` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `lastrun` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`id`),
  KEY `agent_id` (`agent_id`),
  KEY `scalar_id` (`scalar_id`),
  KEY `idx_scheduled` (`agent_id`,`scalar_id`,`status`,`frequency`,`start`),
  CONSTRAINT `ibfk_agents_scalars_agent_id` FOREIGN KEY (`agent_id`) REFERENCES `agents` (`id`) ON DELETE CASCADE,
  CONSTRAINT `ibfk_agents_scalars_scalar_id` FOREIGN KEY (`scalar_id`) REFERENCES `scalars` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='Scalar visitors';
SET character_set_client = @saved_cs_client;

--
-- Table structure for table `categories`
--

DROP TABLE IF EXISTS `categories`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `categories` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(40) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `description` varchar(100) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  PRIMARY KEY (`id`),
  KEY `idx_uniq_name` (`name`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='Group scalars, milestones, etc';
SET character_set_client = @saved_cs_client;

--
-- Table structure for table `categories_milestones`
--

DROP TABLE IF EXISTS `categories_milestones`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `categories_milestones` (
  `category_id` int(10) unsigned NOT NULL DEFAULT '0',
  `milestone_id` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`category_id`,`milestone_id`),
  KEY `category_id` (`category_id`),
  KEY `milestone_id` (`milestone_id`),
  CONSTRAINT `ibfk_categories_milestones_category_id` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE CASCADE,
  CONSTRAINT `ibfk_categories_milestones_milestone_id` FOREIGN KEY (`milestone_id`) REFERENCES `milestones` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='Category-milestone, many-to-many';
SET character_set_client = @saved_cs_client;

--
-- Table structure for table `categories_scalars`
--

DROP TABLE IF EXISTS `categories_scalars`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `categories_scalars` (
  `category_id` int(10) unsigned NOT NULL DEFAULT '0',
  `scalar_id` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`category_id`,`scalar_id`),
  KEY `category_id` (`category_id`),
  KEY `scalar_id` (`scalar_id`),
  CONSTRAINT `ibfk_categories_scalars_category_id` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE CASCADE,
  CONSTRAINT `ibfk_categories_scalars_scalar_id` FOREIGN KEY (`scalar_id`) REFERENCES `scalars` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='Category-scalar, many-to-many';
SET character_set_client = @saved_cs_client;

--
-- Table structure for table `milestones`
--

DROP TABLE IF EXISTS `milestones`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `milestones` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `when` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `name` varchar(40) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  PRIMARY KEY (`id`),
  KEY `idx_when_name` (`when`,`name`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='Named times for graphs';
SET character_set_client = @saved_cs_client;

--
-- Table structure for table `samples_string`
--

DROP TABLE IF EXISTS `samples_string`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `samples_string` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Sequence seeded w/ `scalars`.`sample_count`',
  `end` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `value` varchar(128) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `start` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`id`),
  KEY `idx_analyst` (`end`,`start`,`value`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='Model for partitions of scalar string samples';
SET character_set_client = @saved_cs_client;

--
-- Table structure for table `samples_decimal`
--

DROP TABLE IF EXISTS `samples_decimal`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `samples_decimal` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Sequence seeded w/ `scalars`.`sample_count`',
  `end` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `value` decimal(20,4) NOT NULL DEFAULT '0.0000',
  `start` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`id`),
  KEY `idx_analyst` (`end`,`start`,`value`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='Model for partitions of scalar decimal samples';
SET character_set_client = @saved_cs_client;

--
-- Table structure for table `samples_analyst_temp`
--

DROP TABLE IF EXISTS `samples_analyst_temp`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `samples_analyst_temp` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `x` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `y` decimal(20,4) NOT NULL DEFAULT '0.0000',
  `y2` decimal(24,4) NOT NULL DEFAULT '0.0000' COMMENT 'Ex. change, moving aggregate, etc.',
  `grp` varchar(10) NOT NULL DEFAULT '' COMMENT 'Ex. DATE_FORMAT() string',
  PRIMARY KEY (`id`),
  KEY `idx_analyst` (`x`,`y`,`y2`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='Model for temp. tables for decimal samples analysis';
SET character_set_client = @saved_cs_client;

--
-- Table structure for table `scalars`
--

DROP TABLE IF EXISTS `scalars`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `scalars` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `value` varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `type` enum('decimal','string') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'decimal',
  `description` varchar(100) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `last_inline_change` datetime NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT 'Last update from client module use',
  `last_agent_change` datetime NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT 'Last update from cron-triggered agent',
  `sample_count` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `idx_get` (`name`,`value`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='Tracked data points';
SET character_set_client = @saved_cs_client;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2009-05-17 17:48:37
