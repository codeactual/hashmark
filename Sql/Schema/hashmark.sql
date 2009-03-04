-- MySQL dump 10.11
--
-- Host: localhost    Database: hashmark
-- ------------------------------------------------------
-- Server version	5.0.67-0ubuntu6-log

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
-- Table structure for table `categories`
--

DROP TABLE IF EXISTS `categories`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `categories` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `name` varchar(40) collate utf8_unicode_ci NOT NULL default '',
  `description` varchar(100) collate utf8_unicode_ci NOT NULL default '',
  PRIMARY KEY  (`id`),
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
  `category_id` int(10) unsigned NOT NULL default '0',
  `milestone_id` int(10) unsigned NOT NULL default '0',
  PRIMARY KEY  (`category_id`,`milestone_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='Category-milestone, many-to-many';
SET character_set_client = @saved_cs_client;

--
-- Table structure for table `categories_scalars`
--

DROP TABLE IF EXISTS `categories_scalars`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `categories_scalars` (
  `category_id` int(10) unsigned NOT NULL default '0',
  `scalar_id` int(10) unsigned NOT NULL default '0',
  PRIMARY KEY  (`category_id`,`scalar_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='Category-scalar, many-to-many';
SET character_set_client = @saved_cs_client;

--
-- Table structure for table `jobs`
--

DROP TABLE IF EXISTS `jobs`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `jobs` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `end` datetime NOT NULL default '0000-00-00 00:00:00',
  `start` datetime NOT NULL default '0000-00-00 00:00:00',
  PRIMARY KEY  (`id`),
  KEY `idx_end_start` (`end`,`start`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='Cron job runs; group samples';
SET character_set_client = @saved_cs_client;

--
-- Table structure for table `milestones`
--

DROP TABLE IF EXISTS `milestones`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `milestones` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `when` datetime NOT NULL default '0000-00-00 00:00:00',
  `name` varchar(40) collate utf8_unicode_ci NOT NULL default '',
  PRIMARY KEY  (`id`),
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
  `id` int(10) unsigned NOT NULL auto_increment COMMENT 'Sequence seeded w/ `scalars`.`sample_count`',
  `end` datetime NOT NULL default '0000-00-00 00:00:00',
  `value` varchar(128) collate utf8_unicode_ci NOT NULL default '',
  `start` datetime NOT NULL default '0000-00-00 00:00:00',
  `job_id` int(10) unsigned NOT NULL default '0',
  PRIMARY KEY  (`id`),
  KEY `idx_analyst` (`end`,`start`,`value`),
  KEY `idx_job` (`job_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='Model for partitions of scalar string samples';
SET character_set_client = @saved_cs_client;

--
-- Table structure for table `samples_decimal`
--

DROP TABLE IF EXISTS `samples_decimal`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `samples_decimal` (
  `id` int(10) unsigned NOT NULL auto_increment COMMENT 'Sequence seeded w/ `scalars`.`sample_count`',
  `end` datetime NOT NULL default '0000-00-00 00:00:00',
  `value` decimal(20,4) NOT NULL default '0.0000',
  `start` datetime NOT NULL default '0000-00-00 00:00:00',
  `job_id` int(10) unsigned NOT NULL default '0',
  PRIMARY KEY  (`id`),
  KEY `idx_analyst` (`end`,`start`,`value`),
  KEY `idx_job` (`job_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='Model for partitions of scalar decimal samples';
SET character_set_client = @saved_cs_client;

--
-- Table structure for table `samples_analyst_temp`
--

DROP TABLE IF EXISTS `samples_analyst_temp`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `samples_analyst_temp` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `x` datetime NOT NULL default '0000-00-00 00:00:00',
  `y` decimal(20,4) NOT NULL default '0.0000',
  `y2` decimal(24,4) NOT NULL default '0.0000' COMMENT 'Ex. change, moving aggregate, etc.',
  `grp` varchar(10) NOT NULL default '' COMMENT 'Ex. DATE_FORMAT() string',
  PRIMARY KEY  (`id`),
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
  `id` int(10) unsigned NOT NULL auto_increment,
  `name` varchar(255) collate utf8_unicode_ci NOT NULL default '',
  `value` varchar(255) collate utf8_unicode_ci NOT NULL default '',
  `type` enum('decimal','string') collate utf8_unicode_ci NOT NULL default 'decimal',
  `description` varchar(100) collate utf8_unicode_ci NOT NULL default '',
  `last_inline_change` datetime NOT NULL default '0000-00-00 00:00:00' COMMENT 'Last value change from client module use',
  `last_sample_change` datetime NOT NULL default '0000-00-00 00:00:00' COMMENT 'Last update from cron/sampler result',
  `sampler_error` varchar(255) collate utf8_unicode_ci NOT NULL default '',
  `sampler_status` enum('Unscheduled','Scheduled','Running') collate utf8_unicode_ci NOT NULL default 'Unscheduled',
  `sampler_handler` varchar(30) collate utf8_unicode_ci NOT NULL default '' COMMENT 'Ex. PHP class name',
  `sampler_frequency` int(10) unsigned NOT NULL default '0' COMMENT 'Minutes',
  `sampler_start` datetime NOT NULL default '0000-00-00 00:00:00',
  `sample_count` int(10) unsigned NOT NULL default '0',
  PRIMARY KEY  (`id`),
  KEY `idx_get` (`name`,`value`),
  KEY `idx_scheduled` (`sampler_handler`,`sampler_status`,`sampler_frequency`,`sampler_start`,`last_sample_change`)
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

-- Dump completed on 2009-02-17 14:25:29
