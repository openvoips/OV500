/*M!999999\- enable the sandbox mode */ 
-- MariaDB dump 10.19  Distrib 10.11.14-MariaDB, for debian-linux-gnu (x86_64)
--
-- Host: localhost    Database: switch
-- ------------------------------------------------------
-- Server version	10.11.14-MariaDB-0+deb12u2

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `account`
--

DROP TABLE IF EXISTS `account`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `account` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `account_id` varchar(30) DEFAULT NULL,
  `parent_account_id` varchar(30) NOT NULL,
  `status_id` enum('1','0','-1','-2','-3','-4') DEFAULT '-1' COMMENT '-1=not approved, 0=inactive, -2=suspended,-3=Stop Billing; -4=Account Closed',
  `account_type` enum('CUSTOMER','RESELLER') DEFAULT 'CUSTOMER',
  `account_level` smallint(5) unsigned DEFAULT NULL COMMENT '0',
  `recording` int(11) DEFAULT 1,
  `dp` tinyint(1) DEFAULT 4,
  `currency_id` varchar(10) DEFAULT 'USD',
  `account_cc` int(11) DEFAULT 1,
  `account_cps` int(11) DEFAULT NULL,
  `tax_number` varchar(30) DEFAULT NULL,
  `tax_type` enum('inclusive','exclusive') DEFAULT 'exclusive',
  `vat_flag` enum('NONE','TAX','VAT') DEFAULT 'NONE',
  `tax1` double(8,4) DEFAULT NULL,
  `tax2` double(8,4) DEFAULT NULL,
  `tax3` double(8,4) DEFAULT NULL,
  `cli_check` int(11) DEFAULT 1,
  `dialpattern_check` enum('1','0') DEFAULT '1',
  `llr_check` enum('1','0') DEFAULT '0',
  `account_codecs` varchar(255) DEFAULT 'G729,PCMU,PCMA,G722',
  `media_transcoding` enum('1','0') DEFAULT '1',
  `media_rtpproxy` enum('1','0') DEFAULT '0',
  `force_dst_src_cli_prefix` enum('1','0') DEFAULT '0',
  `codecs_force` enum('1','0') DEFAULT '0',
  `max_callduration` int(11) DEFAULT 30,
  `round_logic` enum('CEIL','ROUND') DEFAULT NULL,
  `language` varchar(30) DEFAULT NULL,
  `location` varchar(30) DEFAULT NULL,
  `appnotification` enum('1','0') DEFAULT '1',
  `create_dt` datetime NOT NULL,
  `create_by` varchar(30) NOT NULL,
  `update_dt` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp(),
  `update_by` varchar(30) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci ROW_FORMAT=COMPACT;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `account`
--

LOCK TABLES `account` WRITE;
/*!40000 ALTER TABLE `account` DISABLE KEYS */;
INSERT INTO `account` VALUES
(1,'OV500','','1','CUSTOMER',1,1,4,'USD',100,10,'','exclusive','NONE',0.0000,0.0000,0.0000,1,'1','0','G729,PCMU,PCMA,G722','1','1','0','0',30,NULL,NULL,NULL,'1','2025-10-02 00:00:00','',NULL,'');
/*!40000 ALTER TABLE `account` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `account_am`
--

DROP TABLE IF EXISTS `account_am`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `account_am` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `customer_account_id` varchar(30) DEFAULT NULL,
  `account_manager` varchar(30) DEFAULT NULL,
  `account_id` varchar(30) DEFAULT NULL,
  `created_dt` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `customer_account_id` (`account_id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci ROW_FORMAT=COMPACT;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `account_am`
--

LOCK TABLES `account_am` WRITE;
/*!40000 ALTER TABLE `account_am` DISABLE KEYS */;
/*!40000 ALTER TABLE `account_am` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `account_card_details`
--

DROP TABLE IF EXISTS `account_card_details`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `account_card_details` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `account_id` varchar(30) NOT NULL,
  `card_name` varchar(30) NOT NULL,
  `card_data` text NOT NULL,
  `dt_updated` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci ROW_FORMAT=COMPACT;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `account_card_details`
--

LOCK TABLES `account_card_details` WRITE;
/*!40000 ALTER TABLE `account_card_details` DISABLE KEYS */;
/*!40000 ALTER TABLE `account_card_details` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `account_cli_dst_rules`
--

DROP TABLE IF EXISTS `account_cli_dst_rules`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `account_cli_dst_rules` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `account_id` varchar(30) DEFAULT NULL,
  `pstn_cli_usage_option` enum('1','0') DEFAULT '0',
  `pstn_max_calls_per_cli_in_aday` int(11) DEFAULT 100,
  `pstn_max_call_per_cli_live` int(11) DEFAULT 10,
  `pstn_max_calls_in_day` int(11) DEFAULT 2,
  `pstn_max_cli_length` int(11) DEFAULT 13,
  `pstn_min_cli_length` int(11) DEFAULT 10,
  `pstn_cli_malfunction` enum('0','1') DEFAULT '0',
  `pstn_min_dst_number_length_option` enum('1','0') DEFAULT '0',
  `pstn_min_dst_number_length` int(11) DEFAULT 10,
  `pstn_max_dst_number_length` int(11) DEFAULT NULL,
  `did_cli_usage_option` enum('1','0') DEFAULT '0',
  `did_max_calls_per_cli_in_aday` int(11) DEFAULT 0,
  `did_max_call_per_cli_live` int(11) DEFAULT 0,
  `did_max_calls_in_day` int(11) DEFAULT 0,
  `did_max_cli_length` int(11) DEFAULT 0,
  `did_min_cli_length` int(11) DEFAULT 0,
  `created_by` varchar(30) DEFAULT NULL,
  `created_dt` datetime DEFAULT NULL,
  `update_by` varchar(30) DEFAULT NULL,
  `update_dt` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `key_carrier_id_name` (`account_id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci ROW_FORMAT=COMPACT;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `account_cli_dst_rules`
--

LOCK TABLES `account_cli_dst_rules` WRITE;
/*!40000 ALTER TABLE `account_cli_dst_rules` DISABLE KEYS */;
/*!40000 ALTER TABLE `account_cli_dst_rules` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `account_notification`
--

DROP TABLE IF EXISTS `account_notification`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `account_notification` (
  `notification_id` int(11) NOT NULL AUTO_INCREMENT,
  `account_id` varchar(30) NOT NULL,
  `notify_name` enum('low-balance','daily-balance') NOT NULL,
  `notify_emails` varchar(255) NOT NULL,
  `notify_amount` varchar(50) NOT NULL,
  `status` enum('Y','N') NOT NULL,
  `email_status` enum('1','0') DEFAULT '0',
  `update_dt` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp(),
  `last_email` datetime DEFAULT NULL,
  PRIMARY KEY (`notification_id`),
  UNIQUE KEY `account_id` (`account_id`,`notify_name`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci ROW_FORMAT=COMPACT;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `account_notification`
--

LOCK TABLES `account_notification` WRITE;
/*!40000 ALTER TABLE `account_notification` DISABLE KEYS */;
/*!40000 ALTER TABLE `account_notification` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `activeuuids`
--

DROP TABLE IF EXISTS `activeuuids`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `activeuuids` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `uuid` varchar(150) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci ROW_FORMAT=COMPACT;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `activeuuids`
--

LOCK TABLES `activeuuids` WRITE;
/*!40000 ALTER TABLE `activeuuids` DISABLE KEYS */;
INSERT INTO `activeuuids` VALUES
(7,'uuid'),
(8,'9a34adb6-f1e2-469e-8b2d-d005b7529d7c'),
(9,'1 total.');
/*!40000 ALTER TABLE `activeuuids` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `activity_log`
--

DROP TABLE IF EXISTS `activity_log`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `activity_log` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `activity_id` int(11) NOT NULL,
  `activity_type` varchar(20) NOT NULL,
  `sql_table` varchar(50) NOT NULL,
  `sql_key` varchar(255) DEFAULT NULL,
  `sql_query` text NOT NULL,
  `account_id` varchar(30) NOT NULL,
  `dt_created` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=38 DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci ROW_FORMAT=COMPACT;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `activity_log`
--

LOCK TABLES `activity_log` WRITE;
/*!40000 ALTER TABLE `activity_log` DISABLE KEYS */;
INSERT INTO `activity_log` VALUES
(1,1,'insert','sys_currencies_conversions','','INSERT INTO `sys_currencies_conversions` (`currency_id`, `ratio`, `date`) VALUES (\'USD\', \'1\', \'2025-10-02 03:33:38\')','UA000345178','2025-10-02 13:33:38'),
(2,2,'insert','ratecard','','INSERT INTO `ratecard` (`ratecard_name`, `ratecard_currency_id`, `ratecard_type`, `account_id`, `created_by`, `ratecard_id`, `ratecard_for`) VALUES (\'Carrier\', \'USD\', \'CARRIER\', \'SYSTEM\', \'UA000345178\', \'CARR30\', \'OUTGOING\')','UA000345178','2025-10-02 13:37:40'),
(3,3,'insert','ratecard','','INSERT INTO `ratecard` (`ratecard_name`, `ratecard_currency_id`, `ratecard_type`, `account_id`, `created_by`, `ratecard_id`, `ratecard_for`) VALUES (\'Carrier\', \'USD\', \'CARRIER\', \'SYSTEM\', \'UA000345178\', \'CARR38\', \'INCOMING\')','UA000345178','2025-10-02 13:37:52'),
(4,4,'insert','ratecard','','INSERT INTO `ratecard` (`ratecard_name`, `ratecard_currency_id`, `ratecard_type`, `account_id`, `created_by`, `ratecard_id`, `ratecard_for`) VALUES (\'Sale Outgoing\', \'USD\', \'CUSTOMER\', \'SYSTEM\', \'UA000345178\', \'SAOU43\', \'OUTGOING\')','UA000345178','2025-10-02 13:38:06'),
(5,5,'insert','ratecard','','INSERT INTO `ratecard` (`ratecard_name`, `ratecard_currency_id`, `ratecard_type`, `account_id`, `created_by`, `ratecard_id`, `ratecard_for`) VALUES (\'Sale Incoming\', \'USD\', \'CUSTOMER\', \'SYSTEM\', \'UA000345178\', \'SAIN46\', \'INCOMING\')','UA000345178','2025-10-02 13:38:26'),
(6,6,'insert','tariff','','INSERT INTO `tariff` (`tariff_name`, `tariff_type`, `tariff_currency_id`, `tariff_description`, `tariff_status`, `tariff_id`, `account_id`, `created_by`, `create_dt`, `update_dt`) VALUES (\'Buy Tariff\', \'CARRIER\', \'USD\', \'\', \'1\', \'BUTA58\', \'SYSTEM\', \'UA000345178\', \'2025-10-02 15:38:50\', \'2025-10-02 15:38:50\')','UA000345178','2025-10-02 13:38:50'),
(7,7,'insert','tariff','','INSERT INTO `tariff` (`tariff_name`, `tariff_type`, `tariff_currency_id`, `tariff_description`, `tariff_status`, `tariff_id`, `account_id`, `created_by`, `create_dt`, `update_dt`) VALUES (\'Sale Tariff\', \'CUSTOMER\', \'USD\', \'\', \'1\', \'SATA45\', \'SYSTEM\', \'UA000345178\', \'2025-10-02 15:39:03\', \'2025-10-02 15:39:03\')','UA000345178','2025-10-02 13:39:03'),
(8,8,'insert','tariff_ratecard_map','','INSERT INTO `tariff_ratecard_map` (`ratecard_id`, `tariff_id`, `start_day`, `start_time`, `end_day`, `end_time`, `priority`, `status`, `ratecard_for`) VALUES (\'SAOU43\', \'SATA45\', \'0\', \'00:00:00\', \'6\', \'23:59:59\', \'1\', \'1\', \'OUTGOING\')','UA000345178','2025-10-02 13:39:12'),
(9,9,'insert','tariff_ratecard_map','','INSERT INTO `tariff_ratecard_map` (`ratecard_id`, `tariff_id`, `start_day`, `start_time`, `end_day`, `end_time`, `priority`, `status`, `ratecard_for`) VALUES (\'SAIN46\', \'SATA45\', \'0\', \'00:00:00\', \'6\', \'23:59:59\', \'1\', \'1\', \'INCOMING\')','UA000345178','2025-10-02 13:39:17'),
(10,10,'insert','tariff_ratecard_map','','INSERT INTO `tariff_ratecard_map` (`ratecard_id`, `tariff_id`, `start_day`, `start_time`, `end_day`, `end_time`, `priority`, `status`, `ratecard_for`) VALUES (\'CARR30\', \'BUTA58\', \'0\', \'00:00:00\', \'6\', \'23:59:59\', \'1\', \'1\', \'OUTGOING\')','UA000345178','2025-10-02 13:39:27'),
(11,11,'insert','tariff_ratecard_map','','INSERT INTO `tariff_ratecard_map` (`ratecard_id`, `tariff_id`, `start_day`, `start_time`, `end_day`, `end_time`, `priority`, `status`, `ratecard_for`) VALUES (\'CARR38\', \'BUTA58\', \'0\', \'00:00:00\', \'6\', \'23:59:59\', \'1\', \'1\', \'INCOMING\')','UA000345178','2025-10-02 13:39:32'),
(12,12,'insert','customer_rates','','INSERT INTO `customer_rates` (`ratecard_id`, `prefix`, `destination`, `rate`, `connection_charge`, `minimal_time`, `resolution_time`, `grace_period`, `rate_multiplier`, `rate_addition`, `rates_status`, `rental`, `setup_charge`, `inclusive_channel`, `exclusive_per_channel_rental`, `create_dt`, `update_dt`) VALUES (\'SAIN46\', \'1\', \'USA Numbers\', \'0.006\', \'0.000000\', \'6\', \'6\', \'0\', \'1.00\', \'0.00\', \'1\', \'0.5\', \'0.5\', \'10\', \'0.000000\', \'2025-10-02 15:40:40\', \'2025-10-02 15:40:40\') ON DUPLICATE KEY UPDATE destination=\'USA Numbers\',connection_charge=\'0.000000\',minimal_time=\'6\',resolution_time=\'6\',grace_period=\'0\',rate_multiplier=\'1.00\',rate_addition=\'0.00\',rates_status=\'1\',rental=\'0.5\',setup_charge=\'0.5\',inclusive_channel=\'10\',exclusive_per_channel_rental=\'0.000000\'','UA000345178','2025-10-02 13:40:40'),
(13,13,'insert','carrier_rates','','INSERT INTO `carrier_rates` (`ratecard_id`, `prefix`, `destination`, `rate`, `connection_charge`, `minimal_time`, `resolution_time`, `grace_period`, `rate_multiplier`, `rate_addition`, `rates_status`, `rental`, `setup_charge`, `inclusive_channel`, `exclusive_per_channel_rental`, `create_dt`, `update_dt`) VALUES (\'CARR38\', \'1\', \'USA Numbers\', \'0.005\', \'0.000000\', \'6\', \'6\', \'0\', \'1.00\', \'0.00\', \'1\', \'0.25\', \'0.25\', \'10\', \'0.000000\', \'2025-10-02 15:41:40\', \'2025-10-02 15:41:40\') ON DUPLICATE KEY UPDATE destination=\'USA Numbers\',connection_charge=\'0.000000\',minimal_time=\'6\',resolution_time=\'6\',grace_period=\'0\',rate_multiplier=\'1.00\',rate_addition=\'0.00\',rates_status=\'1\',rental=\'0.25\',setup_charge=\'0.25\',inclusive_channel=\'10\',exclusive_per_channel_rental=\'0.000000\'','UA000345178','2025-10-02 13:41:40'),
(14,14,'update','ratecard','ratecard_id=\'CARR38\'','UPDATE `ratecard` SET `ratecard_name` = \'Carrier Incoming\'\nWHERE `ratecard_id` = \'CARR38\'','UA000345178','2025-10-02 13:42:11'),
(15,15,'update','ratecard','ratecard_id=\'CARR30\'','UPDATE `ratecard` SET `ratecard_name` = \'Carrier Outgoing\'\nWHERE `ratecard_id` = \'CARR30\'','UA000345178','2025-10-02 13:42:35'),
(16,16,'insert','carrier_rates','','INSERT INTO `carrier_rates` (`ratecard_id`, `prefix`, `destination`, `rate`, `connection_charge`, `minimal_time`, `resolution_time`, `grace_period`, `rate_multiplier`, `rate_addition`, `rates_status`, `rental`, `setup_charge`, `inclusive_channel`, `exclusive_per_channel_rental`, `create_dt`, `update_dt`) VALUES (\'CARR30\', \'1\', \'USA Route\', \'0.027\', \'0.000000\', \'6\', \'6\', \'0\', \'1.00\', \'0.00\', \'1\', \'0.00\', \'0.00\', \'1\', \'0.000000\', \'2025-10-02 15:43:31\', \'2025-10-02 15:43:31\') ON DUPLICATE KEY UPDATE destination=\'USA Route\',connection_charge=\'0.000000\',minimal_time=\'6\',resolution_time=\'6\',grace_period=\'0\',rate_multiplier=\'1.00\',rate_addition=\'0.00\',rates_status=\'1\',rental=\'0.00\',setup_charge=\'0.00\',inclusive_channel=\'1\',exclusive_per_channel_rental=\'0.000000\'','UA000345178','2025-10-02 13:43:31'),
(17,17,'insert','customer_rates','','INSERT INTO `customer_rates` (`ratecard_id`, `prefix`, `destination`, `rate`, `connection_charge`, `minimal_time`, `resolution_time`, `grace_period`, `rate_multiplier`, `rate_addition`, `rates_status`, `rental`, `setup_charge`, `inclusive_channel`, `exclusive_per_channel_rental`, `create_dt`, `update_dt`) VALUES (\'SAOU43\', \'1\', \'USA Route\', \'0.03\', \'0.000000\', \'6\', \'6\', \'0\', \'1.00\', \'0.00\', \'1\', \'0.00\', \'0.00\', \'1\', \'0.000000\', \'2025-10-02 15:44:05\', \'2025-10-02 15:44:05\') ON DUPLICATE KEY UPDATE destination=\'USA Route\',connection_charge=\'0.000000\',minimal_time=\'6\',resolution_time=\'6\',grace_period=\'0\',rate_multiplier=\'1.00\',rate_addition=\'0.00\',rates_status=\'1\',rental=\'0.00\',setup_charge=\'0.00\',inclusive_channel=\'1\',exclusive_per_channel_rental=\'0.000000\'','UA000345178','2025-10-02 13:44:05'),
(18,18,'add','carrier','OVDC23','INSERT INTO `carrier` (`carrier_id`, `carrier_name`, `carrier_cc`, `carrier_cps`, `dp`, `carrier_currency_id`, `tariff_id`, `carrier_progress_timeout`, `carrier_ring_timeout`, `carrier_status`, `cli_prefer`, `diversion_header_as_comingcli_db`, `diversion_header_option`, `diversion_header_format`, `carrier_codecs`, `vat_flag`, `tax_type`, `tax1`, `tax2`, `tax3`) VALUES (\'OVDC23\', \'OV Demo Carrier\', \'10\', \'10\', \'4\', \'USD\', \'BUTA58\', \'5\', \'60\', \'1\', \'rpid\', \'1\', \'0\', \'<sip:${RDN}@${network_addr}>;reason=no-answer;counter=1;privacy=off\', \'PCMU,PCMA\', \'NONE\', \'exclusive\', \'0.0\', \'0.0\', \'0.0\')','UA000345178','2025-10-02 13:45:47'),
(19,18,'add','carrier_callerid','OVDC23','INSERT INTO `carrier_callerid` (`carrier_id`, `display_string`, `maching_string`, `remove_string`, `add_string`, `route`, `action_type`) VALUES (\'OVDC23\', \'%=>%\', \'%\', \'\', \'%\', \'OUTBOUND\', \'1\')','UA000345178','2025-10-02 13:45:47'),
(20,18,'add','carrier_callerid','OVDC23','INSERT INTO `carrier_callerid` (`carrier_id`, `display_string`, `maching_string`, `remove_string`, `add_string`, `route`, `action_type`) VALUES (\'OVDC23\', \'%=>%\', \'%\', \'\', \'%\', \'INBOUND\', \'1\')','UA000345178','2025-10-02 13:45:47'),
(21,18,'add','carrier_prefix','OVDC23','INSERT INTO `carrier_prefix` (`carrier_id`, `display_string`, `maching_string`, `remove_string`, `route`, `add_string`) VALUES (\'OVDC23\', \'%=>%\', \'%\', \'\', \'OUTBOUND\', \'%\')','UA000345178','2025-10-02 13:45:47'),
(22,18,'add','carrier_prefix','OVDC23','INSERT INTO `carrier_prefix` (`carrier_id`, `display_string`, `maching_string`, `remove_string`, `route`, `add_string`) VALUES (\'OVDC23\', \'%=>%\', \'%\', \'\', \'INBOUND\', \'%\')','UA000345178','2025-10-02 13:45:47'),
(23,19,'insert','carrier_ips','','INSERT INTO `carrier_ips` (`carrier_id`, `ip_status`, `ipaddress_name`, `ipaddress`, `auth_type`, `load_share`, `passwd`, `username`, `carrier_ip_id`) VALUES (\'OVDC23\', \'1\', \'10.10.10.2\', \'10.10.10.2\', \'IP\', \'100\', \'\', \'\', \'10101021759412764\')','UA000345178','2025-10-02 13:46:04'),
(24,20,'insert','dialplan','','INSERT INTO `dialplan` (`dialplan_id`, `dialplan_name`, `dialplan_description`, `failover_sipcause_list`, `dialplan_status`, `update_dt`, `create_dt`) VALUES (\'DERO33\', \'Default Route\', \'\', \'\', \'1\', \'2025-10-02 15:46:46\', \'2025-10-02 15:46:46\')','UA000345178','2025-10-02 13:46:46'),
(25,21,'insert','dialplan_prefix_list','','INSERT INTO `dialplan_prefix_list` (`dial_prefix`, `dialplan_id`, `carrier_id`, `priority`, `start_day`, `start_time`, `end_day`, `end_time`, `load_share`, `route_status`, `create_dt`, `update_dt`) VALUES (\'$\', \'DERO33\', \'OVDC23\', \'1\', \'0\', \'00:00:00\', \'6\', \'23:59:59\', \'100\', \'1\', \'2025-10-02 15:46:58\', \'2025-10-02 15:46:58\') ON DUPLICATE KEY UPDATE dial_prefix=\'$\',dialplan_id=\'DERO33\',carrier_id=\'OVDC23\',priority=\'1\',start_day=\'0\',start_time=\'00:00:00\',end_day=\'6\',end_time=\'23:59:59\',load_share=\'100\',route_status=\'1\',create_dt=\'2025-10-02 15:46:58\',update_dt=\'2025-10-02 15:46:58\'','UA000345178','2025-10-02 13:46:58'),
(26,22,'add','customer_voipminuts',NULL,'INSERT INTO `customer_voipminuts` (`account_id`, `tariff_id`, `account_type`, `billingcode`, `customer_voipminute_id`, `created_by`, `created_dt`) VALUES (\'OV500\', \'SATA45\', \'CUSTOMER\', NULL, \'CVM000001624\', \'UA000345178\', \'2025-10-02 15:49:16\')','UA000345178','2025-10-02 13:49:16'),
(27,22,'add','customer_callerid','OV500','INSERT INTO `customer_callerid` (`account_id`, `display_string`, `maching_string`, `remove_string`, `add_string`, `action_type`, `route`, `created_by`, `created_dt`) VALUES (\'OV500\', \'%=>%\', \'%\', \'\', \'%\', \'1\', \'INBOUND\', \'UA000345178\', \'2025-10-02 15:49:16\') ON DUPLICATE KEY UPDATE maching_string=values(maching_string)','UA000345178','2025-10-02 13:49:16'),
(28,22,'add','customer_callerid','OV500','INSERT INTO `customer_callerid` (`account_id`, `display_string`, `maching_string`, `remove_string`, `add_string`, `action_type`, `route`, `created_by`, `created_dt`) VALUES (\'OV500\', \'%=>%\', \'%\', \'\', \'%\', \'1\', \'OUTBOUND\', \'UA000345178\', \'2025-10-02 15:49:16\') ON DUPLICATE KEY UPDATE maching_string=values(maching_string)','UA000345178','2025-10-02 13:49:16'),
(29,22,'add','customer_dialpattern','OV500','INSERT INTO `customer_dialpattern` (`account_id`, `display_string`, `maching_string`, `remove_string`, `add_string`, `route`, `created_by`, `created_dt`) VALUES (\'OV500\', \'%=>%\', \'%\', \'\', \'%\', \'OUTBOUND\', \'UA000345178\', \'2025-10-02 15:49:16\') ON DUPLICATE KEY UPDATE maching_string=values(maching_string)','UA000345178','2025-10-02 13:49:16'),
(30,22,'add','customer_dialpattern','OV500','INSERT INTO `customer_dialpattern` (`account_id`, `display_string`, `maching_string`, `remove_string`, `add_string`, `route`, `created_by`, `created_dt`) VALUES (\'OV500\', \'%=>%\', \'%\', \'\', \'%\', \'INBOUND\', \'UA000345178\', \'2025-10-02 15:49:16\') ON DUPLICATE KEY UPDATE maching_string=values(maching_string)','UA000345178','2025-10-02 13:49:16'),
(31,22,'add','customer_balance','OV500','INSERT INTO `customer_balance` (`credit_limit`, `balance`, `account_id`, `maxcredit_limit`) VALUES (0, 0, \'OV500\', \'0.000000\') ON DUPLICATE KEY UPDATE account_id=values(account_id)','UA000345178','2025-10-02 13:49:16'),
(32,23,'insert','customer_devices',NULL,'INSERT INTO `customer_devices` (`account_id`, `username`, `secret`, `ipaddress`, `sip_cc`, `sip_cps`, `status`, `user_type`, `extension_id`, `voicemail_enabled`, `voicemail`) VALUES (\'OV500\', \'openvoipsTest\', \'openvoips@Test1\', \'\', \'10\', \'2\', \'1\', \'SWITCH\', \'EXT000001736\', \'Y\', \'EXT000001736\')','UA000345178','2025-10-02 13:49:52'),
(33,24,'update','customer_devices',' id =\'1\' AND account_id=\'OV500\' ','UPDATE `customer_devices` SET `username` = \'openvoipsTest\', `secret` = \'OV@Test1\', `ipaddress` = \'\', `ipauthfrom` = \'NO\', `sip_cc` = \'10\', `sip_cps` = \'2\', `status` = \'1\', `voicemail_enabled` = \'Y\', `voicemail` = \'EXT000001736\'\nWHERE `id` = \'1\' AND `account_id` = \'OV500\' ','UA000345178','2025-10-02 14:01:38'),
(34,25,'SDRAPI','ADDBALANCE',NULL,'Array\n(\n    [ACCOUNTID] => OV500\n    [USERTYPE] => \n    [SERVICENUMBER] => Cash\n    [COLLECTIONOPTION] => Cash\n    [AMMOUNT] => 10\n    [PAIDON] => 2025-10-03 08:20:37\n    [NOTES] => for test by OV Team\n    [CREATEDBY] => UA000345178\n    [REQUEST] => ADDBALANCE\n)\n','UA000345178','2025-10-03 08:20:57'),
(35,26,'delete','dialplan_prefix_list','1','DELETE FROM `dialplan_prefix_list`\nWHERE `id` = \'1\'','UA000345178','2025-10-03 08:21:51'),
(36,27,'insert','dialplan_prefix_list','','INSERT INTO `dialplan_prefix_list` (`dial_prefix`, `dialplan_id`, `carrier_id`, `priority`, `start_day`, `start_time`, `end_day`, `end_time`, `load_share`, `route_status`, `create_dt`, `update_dt`) VALUES (\'%\', \'DERO33\', \'OVDC23\', \'1\', \'0\', \'00:00:00\', \'6\', \'23:59:59\', \'100\', \'1\', \'2025-10-03 10:21:59\', \'2025-10-03 10:21:59\') ON DUPLICATE KEY UPDATE dial_prefix=\'%\',dialplan_id=\'DERO33\',carrier_id=\'OVDC23\',priority=\'1\',start_day=\'0\',start_time=\'00:00:00\',end_day=\'6\',end_time=\'23:59:59\',load_share=\'100\',route_status=\'1\',create_dt=\'2025-10-03 10:21:59\',update_dt=\'2025-10-03 10:21:59\'','UA000345178','2025-10-03 08:21:59'),
(37,28,'update','customer_devices',' extension_id =\'EOVHEY7900\' and account_id=\'OV500\'','UPDATE `customer_devices` SET `name` = \'OV Help\', `phone_number` = \'\', `email_address` = \'\', `status` = \'1\', `dnd` = \'N\', `ring_timeout` = \'60\', `call_recording` = \'0\', `voicemail_enabled` = \'N\', `voicemail` = \'\', `moh_sound` = \'default\', `caller_id` = \'12345678900\', `extensionplan_package_id` = \'DFE31P\', `call_forward_all` = \'N\', `cfall_destination_type` = \'\', `cfall_destination` = \'\', `call_forward_no_answer` = \'N\', `cfnoans_destination_type` = \'\', `cfnoans_destination` = \'\', `cfnoans_timeout` = \'0\', `call_forward_busy` = \'N\', `cfbusy_destination_type` = \'\', `cfbusy_destination` = \'\', `username` = \'C18tA02m\', `secret` = \'pKM&v2)#1W\', `ipaddress` = \'\', `updated_by` = \'UC000003333\', `updated_dt` = \'2025-10-03 17:50:25\'\nWHERE `extension_id` = \'EOVHEY7900\' and `account_id` = \'OV500\'','UC000003333','2025-10-03 15:50:25');
/*!40000 ALTER TABLE `activity_log` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `activity_site_log`
--

DROP TABLE IF EXISTS `activity_site_log`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `activity_site_log` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `event` enum('track','insert','update','delete') NOT NULL DEFAULT 'track',
  `session_id` varchar(100) DEFAULT NULL,
  `user_name` varchar(128) DEFAULT NULL,
  `account_id` varchar(30) DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `remote_address` varchar(255) DEFAULT NULL,
  `page_url` varchar(255) DEFAULT NULL,
  `referrer_url` varchar(255) DEFAULT NULL,
  `user_agent` varchar(255) DEFAULT NULL,
  `ci_class_method` varchar(255) DEFAULT NULL,
  `created_dt` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci ROW_FORMAT=COMPACT;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `activity_site_log`
--

LOCK TABLES `activity_site_log` WRITE;
/*!40000 ALTER TABLE `activity_site_log` DISABLE KEYS */;
/*!40000 ALTER TABLE `activity_site_log` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `agent_status_log`
--

DROP TABLE IF EXISTS `agent_status_log`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `agent_status_log` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `agent_code` varchar(30) DEFAULT NULL,
  `agent_name` varchar(300) DEFAULT NULL,
  `account_id` varchar(30) DEFAULT NULL,
  `status_name` varchar(30) DEFAULT NULL,
  `status_start` datetime DEFAULT NULL,
  `status_stop` datetime DEFAULT NULL,
  `action_time_sec` int(11) DEFAULT NULL,
  `extension_id` varchar(30) DEFAULT NULL,
  `extension_no` varchar(30) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci ROW_FORMAT=COMPACT;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `agent_status_log`
--

LOCK TABLES `agent_status_log` WRITE;
/*!40000 ALTER TABLE `agent_status_log` DISABLE KEYS */;
INSERT INTO `agent_status_log` VALUES
(1,'1001','Demo','OV500','OnBreak','2025-10-03 11:50:01','2025-10-03 11:50:08',7,'EOVHEY7900','1001'),
(2,'1001','Demo','OV500','OnBreak','2025-10-03 12:17:16','2025-10-03 12:18:52',136,'EOVHEY7900','1001'),
(3,'1001','Demo','OV500','OnBreak','2025-10-03 12:18:52','2025-10-03 12:24:40',588,'EOVHEY7900','1001'),
(4,'1001','Demo','OV500','LOGIN','2025-10-03 12:23:59',NULL,NULL,'EOVHEY7900','1001'),
(5,'1001','Demo','OV500','OnBreak','2025-10-03 12:24:40',NULL,NULL,'EOVHEY7900','1001');
/*!40000 ALTER TABLE `agent_status_log` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `agents`
--

DROP TABLE IF EXISTS `agents`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `agents` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `agent_code` varchar(255) DEFAULT NULL,
  `agent_displayname` varchar(255) DEFAULT NULL,
  `agent_type` enum('dynamic','static') NOT NULL DEFAULT 'static',
  `name` varchar(255) DEFAULT NULL,
  `system` varchar(255) DEFAULT 'single_box',
  `uuid` varchar(255) DEFAULT NULL,
  `type` varchar(255) DEFAULT 'callback',
  `contact` varchar(255) DEFAULT 'default',
  `status` varchar(255) DEFAULT 'Available (On Demand)',
  `state` varchar(255) DEFAULT 'Waiting',
  `max_no_answer` int(11) NOT NULL DEFAULT 1000000000,
  `wrap_up_time` int(11) NOT NULL DEFAULT 10,
  `reject_delay_time` int(11) NOT NULL DEFAULT 10,
  `busy_delay_time` int(11) NOT NULL DEFAULT 10,
  `no_answer_delay_time` int(11) NOT NULL DEFAULT 10,
  `last_bridge_start` int(11) NOT NULL DEFAULT 0,
  `last_bridge_end` int(11) NOT NULL DEFAULT 0,
  `last_offered_call` int(11) NOT NULL DEFAULT 0,
  `last_status_change` int(11) NOT NULL DEFAULT 0,
  `no_answer_count` int(11) NOT NULL DEFAULT 1,
  `calls_answered` int(11) NOT NULL DEFAULT 0,
  `talk_time` int(11) NOT NULL DEFAULT 0,
  `account_id` varchar(30) NOT NULL,
  `ready_time` int(11) NOT NULL DEFAULT 0,
  `last_update` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp(),
  `extension_no` varchar(30) DEFAULT NULL,
  `extension_id` varchar(30) DEFAULT NULL,
  `status_id` int(11) DEFAULT 1,
  `external_calls_count` int(11) NOT NULL DEFAULT 0,
  `instance_id` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `name` (`name`) USING BTREE,
  KEY `status` (`status`) USING BTREE,
  KEY `uuid` (`uuid`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci ROW_FORMAT=COMPACT;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `agents`
--

LOCK TABLES `agents` WRITE;
/*!40000 ALTER TABLE `agents` DISABLE KEYS */;
INSERT INTO `agents` VALUES
(2,'1001','Demo','dynamic','C18tA02m','single_box','','callback','sofia/internal/C18tA02m@173.208.52.222','On Break','Waiting',1000000000,10,10,10,10,1759508672,1759508674,1759508670,1759508680,0,2,6,'OV500',0,'2025-10-03 16:24:40','1001','EOVHEY7900',1,0,'single_box');
/*!40000 ALTER TABLE `agents` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Temporary table structure for view `allnumbers`
--

DROP TABLE IF EXISTS `allnumbers`;
/*!50001 DROP VIEW IF EXISTS `allnumbers`*/;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8mb4;
/*!50001 CREATE VIEW `allnumbers` AS SELECT
 1 AS `username`,
  1 AS `account_id`,
  1 AS `name`,
  1 AS `extension_id`,
  1 AS `extension_no`,
  1 AS `dst_type` */;
SET character_set_client = @saved_cs_client;

--
-- Temporary table structure for view `allnumbersall`
--

DROP TABLE IF EXISTS `allnumbersall`;
/*!50001 DROP VIEW IF EXISTS `allnumbersall`*/;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8mb4;
/*!50001 CREATE VIEW `allnumbersall` AS SELECT
 1 AS `username`,
  1 AS `account_id`,
  1 AS `name`,
  1 AS `extension_id`,
  1 AS `extension_no`,
  1 AS `dst_type` */;
SET character_set_client = @saved_cs_client;

--
-- Table structure for table `announcement`
--

DROP TABLE IF EXISTS `announcement`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `announcement` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `announcement_id` varchar(30) DEFAULT NULL,
  `annumcement_name` varchar(50) DEFAULT NULL,
  `account_id` varchar(30) DEFAULT NULL,
  `announcement_desc` varchar(1000) DEFAULT NULL,
  `route` varchar(30) DEFAULT 'HANGUP',
  `route_endpoint` varchar(100) DEFAULT NULL,
  `status_id` int(11) DEFAULT 1,
  `announcement_no` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `announcement_id` (`announcement_id`) USING BTREE,
  KEY `account_id` (`account_id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci ROW_FORMAT=COMPACT;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `announcement`
--

LOCK TABLES `announcement` WRITE;
/*!40000 ALTER TABLE `announcement` DISABLE KEYS */;
/*!40000 ALTER TABLE `announcement` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `announcement_audiofiles`
--

DROP TABLE IF EXISTS `announcement_audiofiles`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `announcement_audiofiles` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `announcement_id` varchar(30) DEFAULT NULL,
  `audiofile_id` varchar(30) DEFAULT NULL,
  `priority` int(11) DEFAULT NULL,
  `account_id` varchar(30) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `announcement_id` (`announcement_id`) USING BTREE,
  KEY `audiofile_id` (`audiofile_id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci ROW_FORMAT=COMPACT;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `announcement_audiofiles`
--

LOCK TABLES `announcement_audiofiles` WRITE;
/*!40000 ALTER TABLE `announcement_audiofiles` DISABLE KEYS */;
/*!40000 ALTER TABLE `announcement_audiofiles` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `api_credentials`
--

DROP TABLE IF EXISTS `api_credentials`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `api_credentials` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `account_id` varchar(50) NOT NULL,
  `username` varchar(100) NOT NULL,
  `secret` varchar(255) NOT NULL COMMENT 'md5',
  `allowed_ip` text DEFAULT NULL,
  `created_dt` datetime DEFAULT NULL,
  `created_by` varchar(30) DEFAULT NULL,
  `status_id` enum('0','1') DEFAULT '1',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci ROW_FORMAT=COMPACT;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `api_credentials`
--

LOCK TABLES `api_credentials` WRITE;
/*!40000 ALTER TABLE `api_credentials` DISABLE KEYS */;
/*!40000 ALTER TABLE `api_credentials` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `api_log`
--

DROP TABLE IF EXISTS `api_log`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `api_log` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `ip` varchar(50) NOT NULL,
  `api_username` varchar(50) NOT NULL,
  `get_data` text NOT NULL,
  `post_data` text NOT NULL,
  `response` text NOT NULL,
  `account_id` varchar(30) NOT NULL,
  `create_dt` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci ROW_FORMAT=COMPACT;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `api_log`
--

LOCK TABLES `api_log` WRITE;
/*!40000 ALTER TABLE `api_log` DISABLE KEYS */;
/*!40000 ALTER TABLE `api_log` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `apisdr_log`
--

DROP TABLE IF EXISTS `apisdr_log`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `apisdr_log` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `request_data` text NOT NULL,
  `response_data` text NOT NULL,
  `function_return` text NOT NULL,
  `create_date` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci ROW_FORMAT=COMPACT;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `apisdr_log`
--

LOCK TABLES `apisdr_log` WRITE;
/*!40000 ALTER TABLE `apisdr_log` DISABLE KEYS */;
INSERT INTO `apisdr_log` VALUES
(1,'{\"ACCOUNTID\":\"OV500\",\"REQUEST\":\"OPENINGBALANCE\",\"SERVICENUMBER\":\"\",\"CREATEDBY\":\"OV500\"}','{\"status\":\"SUCCESS\",\"message\":\"Opening Balance added\",\"error\":0}','{\"status\":\"SUCCESS\",\"message\":\"Opening Balance added\",\"error\":0}','2025-10-02 13:49:07'),
(2,'{\"RULETYPE\":\"OPENINGBALANCE\",\"ACCOUNTID\":\"OV500\",\"QUANTITY\":1,\"SERVICENUMBER\":\"SATA45\",\"SERVICEKEY\":null,\"REQUEST\":\"TARIFFCHARGES\"}','{\"status\":\"SUCCESS\",\"message\":\"Tariff addedd\",\"error\":0}','{\"status\":\"SUCCESS\",\"message\":\"Tariff addedd\",\"error\":0}','2025-10-02 13:49:16'),
(3,'{\"ACCOUNTID\":\"OV500\",\"USERTYPE\":null,\"SERVICENUMBER\":\"Cash\",\"COLLECTIONOPTION\":\"Cash\",\"AMMOUNT\":\"10\",\"PAIDON\":\"2025-10-03 08:20:37\",\"NOTES\":\"for test by OV Team\",\"CREATEDBY\":\"UA000345178\",\"REQUEST\":\"ADDBALANCE\"}','{\"status\":\"SUCCESS\",\"message\":\"Added successfully\",\"error\":0}','{\"status\":\"SUCCESS\",\"message\":\"Added successfully\",\"error\":0}','2025-10-03 08:20:57'),
(4,'{\"REQUEST\":\"EXTENSION\",\"account_id\":\"OV500\",\"service_number\":\"EOVHEY7900\",\"extension_no\":\"1001\",\"service_id\":\"DFE31P\",\"account_type\":\"CUSTOMER\",\"account_level\":null}','{\"status\":\"SUCCESS\",\"message\":\"Extension addedd\",\"error\":0}','{\"status\":\"SUCCESS\",\"message\":\"Extension addedd\",\"error\":0}','2025-10-03 08:26:12');
/*!40000 ALTER TABLE `apisdr_log` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `audiofiles`
--

DROP TABLE IF EXISTS `audiofiles`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `audiofiles` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `audiofile_id` varchar(30) DEFAULT NULL,
  `account_id` varchar(30) NOT NULL,
  `audio_desc` varchar(100) DEFAULT NULL,
  `audio_name` varchar(100) NOT NULL,
  `file_name` varchar(250) NOT NULL,
  `status_id` int(10) unsigned NOT NULL,
  `created_by` varchar(30) NOT NULL,
  `created_dt` datetime NOT NULL,
  `modified_by` varchar(30) DEFAULT NULL,
  `modified_dt` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `file` (`audiofile_id`,`account_id`) USING BTREE,
  KEY `account_id` (`account_id`) USING BTREE,
  KEY `audiofile_id` (`audiofile_id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci ROW_FORMAT=COMPACT;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `audiofiles`
--

LOCK TABLES `audiofiles` WRITE;
/*!40000 ALTER TABLE `audiofiles` DISABLE KEYS */;
/*!40000 ALTER TABLE `audiofiles` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `bill_account_sdr`
--

DROP TABLE IF EXISTS `bill_account_sdr`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `bill_account_sdr` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `account_id` varchar(30) DEFAULT NULL,
  `rule_type` varchar(30) DEFAULT NULL,
  `service_number` varchar(1000) DEFAULT '',
  `billing_date` date DEFAULT NULL,
  `unit` int(11) DEFAULT 0,
  `rate` double(20,10) DEFAULT 0.0000000000,
  `cost` double(20,10) DEFAULT 0.0000000000,
  `totalcost` double(20,10) DEFAULT 0.0000000000,
  `sallerunit` int(11) DEFAULT 0,
  `sallerrate` double(20,10) DEFAULT 0.0000000000,
  `sallercost` double(20,10) DEFAULT 0.0000000000,
  `totalsallercost` double(20,10) DEFAULT 0.0000000000,
  `startdate` date DEFAULT NULL,
  `enddate` date DEFAULT NULL,
  `createdate` datetime DEFAULT NULL,
  `invoice_id` varchar(50) DEFAULT NULL,
  `dategeneratedby` enum('service','api') DEFAULT 'service',
  `service_id` varchar(30) DEFAULT NULL,
  `service_device` varchar(50) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci ROW_FORMAT=COMPACT;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `bill_account_sdr`
--

LOCK TABLES `bill_account_sdr` WRITE;
/*!40000 ALTER TABLE `bill_account_sdr` DISABLE KEYS */;
INSERT INTO `bill_account_sdr` VALUES
(1,'OV500','OPENINGBALANCE','','2025-10-02',1,0.0000000000,0.0000000000,0.0000000000,1,0.0000000000,0.0000000000,0.0000000000,'2025-10-02','2025-10-02','2025-10-02 09:49:07',NULL,'service',NULL,NULL),
(2,'OV500','TARIFFCHARGES','SATA45','2025-10-02',1,0.0000000000,0.0000000000,0.0000000000,1,0.0000000000,0.0000000000,0.0000000000,'2025-10-02','2025-10-02','2025-10-02 09:49:16',NULL,'service',NULL,NULL),
(3,'OV500','ADDBALANCE','for test by OV Team','2025-10-03',1,0.0000000000,10.0000000000,10.0000000000,1,0.0000000000,0.0000000000,0.0000000000,'2025-10-03','2025-10-03','2025-10-03 04:20:57',NULL,'service',NULL,NULL),
(4,'OV500','EXTENSION','1001','2025-10-03',1,0.0000000000,0.0000000000,0.0000000000,1,0.0000000000,0.0000000000,0.0000000000,'2025-10-03','2025-11-02','2025-10-03 04:26:12',NULL,'service','DFE31P','EOVHEY7900');
/*!40000 ALTER TABLE `bill_account_sdr` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `bill_billing_event`
--

DROP TABLE IF EXISTS `bill_billing_event`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `bill_billing_event` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `billingeventid` varchar(50) DEFAULT NULL,
  `account_id` varchar(30) DEFAULT NULL,
  `item_id` varchar(30) DEFAULT NULL,
  `price_id` varchar(30) DEFAULT NULL,
  `item_product_id` varchar(30) DEFAULT NULL,
  `quantity` int(11) DEFAULT 1,
  `start_dt` date DEFAULT NULL,
  `status_id` enum('0','1','2','-1') DEFAULT '1',
  `stop_dt` date DEFAULT NULL,
  `lastbilldate` date DEFAULT NULL,
  `record_type` varchar(30) DEFAULT NULL,
  `lastbill_execute_date` date DEFAULT NULL,
  `r1lastbilldate` date DEFAULT NULL,
  `r2lastbilldate` date DEFAULT NULL,
  `r3lastbilldate` date DEFAULT NULL,
  `r1lastbill_execute_date` date DEFAULT NULL,
  `r2lastbill_execute_date` date DEFAULT NULL,
  `r3lastbill_execute_date` date DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `account_id` (`billingeventid`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci ROW_FORMAT=COMPACT;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `bill_billing_event`
--

LOCK TABLES `bill_billing_event` WRITE;
/*!40000 ALTER TABLE `bill_billing_event` DISABLE KEYS */;
/*!40000 ALTER TABLE `bill_billing_event` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `bill_carrier_sdr`
--

DROP TABLE IF EXISTS `bill_carrier_sdr`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `bill_carrier_sdr` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `carrier_id` varchar(30) DEFAULT NULL,
  `carrier_name` varchar(100) DEFAULT NULL,
  `account_id` varchar(30) DEFAULT NULL,
  `currency_id` varchar(10) DEFAULT 'USD',
  `currency_name` varchar(20) DEFAULT NULL,
  `account_currency_id` varchar(10) DEFAULT 'USD',
  `currency_ratio` decimal(12,6) NOT NULL DEFAULT 1.000000,
  `rule_type` varchar(30) DEFAULT NULL,
  `prefix` varchar(30) DEFAULT NULL,
  `destination` varchar(150) DEFAULT NULL,
  `unit` int(11) DEFAULT 0,
  `rate` double(20,10) DEFAULT 0.0000000000,
  `carriercost` double(20,10) DEFAULT 0.0000000000,
  `carriercost_customer_currency` double(20,10) DEFAULT 0.0000000000,
  `calls_date` date DEFAULT NULL,
  `customer_cost` double(20,10) DEFAULT NULL,
  `customer_rate` double(20,10) DEFAULT NULL,
  `calls` int(11) DEFAULT 0,
  `billing_date` date DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci ROW_FORMAT=COMPACT;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `bill_carrier_sdr`
--

LOCK TABLES `bill_carrier_sdr` WRITE;
/*!40000 ALTER TABLE `bill_carrier_sdr` DISABLE KEYS */;
/*!40000 ALTER TABLE `bill_carrier_sdr` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `bill_customer_priceplan`
--

DROP TABLE IF EXISTS `bill_customer_priceplan`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `bill_customer_priceplan` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `account_id` varchar(30) DEFAULT NULL,
  `billing_cycle` enum('DAILY','WEEKLY','MONTHLY') DEFAULT NULL,
  `payment_terms` int(11) DEFAULT NULL,
  `itemised_billing` enum('1','0') DEFAULT '1',
  `invoice_via_email` enum('1','0') DEFAULT '1',
  `emails` varchar(150) DEFAULT NULL,
  `invoice_generation_status` enum('1','0') DEFAULT '1',
  `invoice_generation_status_update` datetime DEFAULT NULL,
  `last_invoice_date` datetime DEFAULT NULL,
  `next_invoice_date` datetime DEFAULT NULL,
  `invoice_id` varchar(50) DEFAULT NULL,
  `created_by` varchar(30) NOT NULL,
  `updated_by` varchar(30) NOT NULL,
  `created_dt` datetime NOT NULL,
  `updated_dt` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp(),
  `priceplan_id` varchar(30) DEFAULT NULL,
  `status_message` text DEFAULT NULL,
  `monthly_charges_day` smallint(6) DEFAULT 1,
  `billing_day` smallint(6) DEFAULT 1,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci ROW_FORMAT=COMPACT;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `bill_customer_priceplan`
--

LOCK TABLES `bill_customer_priceplan` WRITE;
/*!40000 ALTER TABLE `bill_customer_priceplan` DISABLE KEYS */;
INSERT INTO `bill_customer_priceplan` VALUES
(1,'OV500','MONTHLY',1,'1','1',NULL,'1',NULL,NULL,NULL,NULL,'UA000345178','','0000-00-00 00:00:00','2025-10-02 13:49:28',NULL,NULL,26,1);
/*!40000 ALTER TABLE `bill_customer_priceplan` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `bill_email_templates`
--

DROP TABLE IF EXISTS `bill_email_templates`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `bill_email_templates` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `account_id` varchar(30) DEFAULT NULL,
  `email_name` varchar(30) DEFAULT NULL,
  `template_for` varchar(30) DEFAULT NULL,
  `email_subject` text DEFAULT NULL,
  `email_body` text DEFAULT NULL,
  `email_bcc` text DEFAULT NULL,
  `email_cc` text DEFAULT NULL,
  `email_daemon` enum('PHPMAIL','SMTP') DEFAULT 'PHPMAIL',
  `smtp_id` varchar(30) DEFAULT NULL,
  `created_by` varchar(30) NOT NULL,
  `updated_by` varchar(30) NOT NULL,
  `created_dt` datetime NOT NULL,
  `updated_dt` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `account_id` (`account_id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci ROW_FORMAT=COMPACT;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `bill_email_templates`
--

LOCK TABLES `bill_email_templates` WRITE;
/*!40000 ALTER TABLE `bill_email_templates` DISABLE KEYS */;
/*!40000 ALTER TABLE `bill_email_templates` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `bill_invoice`
--

DROP TABLE IF EXISTS `bill_invoice`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `bill_invoice` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `invoice_id` varchar(30) DEFAULT NULL,
  `account_id` varchar(30) DEFAULT NULL,
  `contact_name` varchar(100) DEFAULT NULL,
  `company_name` varchar(50) DEFAULT '',
  `company_address` text DEFAULT NULL,
  `email_address` varchar(50) DEFAULT NULL,
  `phone_number` varchar(50) DEFAULT NULL,
  `tax_number` varchar(50) DEFAULT NULL,
  `tax1` double(20,10) DEFAULT 0.0000000000,
  `tax2` double(20,10) DEFAULT 0.0000000000,
  `tax3` double(20,10) DEFAULT 0.0000000000,
  `bill_date` date DEFAULT NULL,
  `billing_cycle` enum('MONTHLY','DAILY','WEEKLY') DEFAULT 'MONTHLY',
  `payment_terms` int(11) DEFAULT 1,
  `itemised_billing` enum('1','0') NOT NULL DEFAULT '0',
  `billing_date_from` date DEFAULT NULL,
  `billing_date_to` date DEFAULT NULL,
  `currency_symbol` varchar(5) DEFAULT NULL,
  `currency_name` varchar(15) DEFAULT NULL,
  `create_dt` datetime DEFAULT NULL,
  `update_dt` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp(),
  `last_bill_amount` double(20,10) DEFAULT 0.0000000000,
  `bill_amount` double(25,10) DEFAULT NULL,
  `status_id` enum('no-mail','mail-sent','failed','generated') NOT NULL DEFAULT 'generated',
  `status_message` varchar(255) DEFAULT NULL,
  `account_manager` varchar(30) DEFAULT NULL,
  `due_status` enum('PAID','UNPAID','OVERDUE') DEFAULT 'UNPAID',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci ROW_FORMAT=COMPACT;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `bill_invoice`
--

LOCK TABLES `bill_invoice` WRITE;
/*!40000 ALTER TABLE `bill_invoice` DISABLE KEYS */;
/*!40000 ALTER TABLE `bill_invoice` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `bill_invoice_config`
--

DROP TABLE IF EXISTS `bill_invoice_config`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `bill_invoice_config` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `account_id` varchar(30) DEFAULT NULL,
  `logo` varchar(300) DEFAULT NULL,
  `company_name` varchar(300) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `bank_detail` text DEFAULT NULL,
  `footer_text` text DEFAULT NULL,
  `support_text` text DEFAULT NULL,
  `created_by` varchar(30) NOT NULL,
  `updated_by` varchar(30) NOT NULL,
  `created_dt` datetime NOT NULL,
  `updated_dt` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `account_id` (`account_id`) USING BTREE,
  UNIQUE KEY `account_id_2` (`account_id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci ROW_FORMAT=COMPACT;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `bill_invoice_config`
--

LOCK TABLES `bill_invoice_config` WRITE;
/*!40000 ALTER TABLE `bill_invoice_config` DISABLE KEYS */;
/*!40000 ALTER TABLE `bill_invoice_config` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `bill_itemlist`
--

DROP TABLE IF EXISTS `bill_itemlist`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `bill_itemlist` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `service_id` varchar(30) DEFAULT NULL,
  `item_id` varchar(30) DEFAULT NULL,
  `item_name` varchar(150) DEFAULT NULL,
  `item_name_invoice_display` varchar(150) DEFAULT NULL,
  `can_set_price` enum('0','1') DEFAULT '1',
  `created_by` varchar(30) NOT NULL,
  `updated_by` varchar(30) NOT NULL,
  `created_dt` datetime NOT NULL,
  `updated_dt` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `item_id` (`item_id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci ROW_FORMAT=COMPACT;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `bill_itemlist`
--

LOCK TABLES `bill_itemlist` WRITE;
/*!40000 ALTER TABLE `bill_itemlist` DISABLE KEYS */;
/*!40000 ALTER TABLE `bill_itemlist` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `bill_services`
--

DROP TABLE IF EXISTS `bill_services`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `bill_services` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `service_id` varchar(30) DEFAULT NULL,
  `service_name` varchar(50) DEFAULT NULL,
  `created_by` varchar(30) NOT NULL,
  `updated_by` varchar(30) NOT NULL,
  `created_dt` datetime NOT NULL,
  `updated_dt` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `service_id` (`service_id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci ROW_FORMAT=COMPACT;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `bill_services`
--

LOCK TABLES `bill_services` WRITE;
/*!40000 ALTER TABLE `bill_services` DISABLE KEYS */;
/*!40000 ALTER TABLE `bill_services` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `bill_smtp_config`
--

DROP TABLE IF EXISTS `bill_smtp_config`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `bill_smtp_config` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `account_id` varchar(30) DEFAULT '',
  `smtp_config_id` varchar(200) DEFAULT NULL,
  `smtp_auth` enum('0','1') DEFAULT NULL,
  `smtp_secure` enum('SSL','TSL') DEFAULT NULL,
  `smtp_host` varchar(100) DEFAULT NULL,
  `smtp_port` varchar(30) DEFAULT NULL,
  `smtp_username` varchar(30) DEFAULT NULL,
  `smtp_password` varchar(30) DEFAULT NULL,
  `smtp_from` varchar(100) DEFAULT NULL,
  `smtp_from_name` varchar(30) DEFAULT NULL,
  `smtp_xmailer` varchar(100) DEFAULT NULL,
  `smtp_host_name` varchar(100) DEFAULT NULL,
  `created_by` varchar(30) NOT NULL,
  `updated_by` varchar(30) NOT NULL,
  `created_dt` datetime NOT NULL,
  `updated_dt` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp(),
  `smtp_status` enum('1','0') DEFAULT '1',
  PRIMARY KEY (`id`),
  UNIQUE KEY `smtp_config_id` (`smtp_config_id`) USING BTREE,
  UNIQUE KEY `smtp_config` (`smtp_config_id`,`account_id`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci ROW_FORMAT=COMPACT;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `bill_smtp_config`
--

LOCK TABLES `bill_smtp_config` WRITE;
/*!40000 ALTER TABLE `bill_smtp_config` DISABLE KEYS */;
INSERT INTO `bill_smtp_config` VALUES
(1,'SYSTEM','ANAN48SYSTEM','1','SSL','openvoips.com','25','anand','kumar','openvoips@openvoips.com','openvoips','openvoips@openvoips.com','openvoips.com','SYSTEM','','0000-00-00 00:00:00',NULL,'1');
/*!40000 ALTER TABLE `bill_smtp_config` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `block_cli`
--

DROP TABLE IF EXISTS `block_cli`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `block_cli` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `cli` varchar(30) DEFAULT NULL,
  `account_id` varchar(30) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `cli` (`cli`),
  KEY `cli_2` (`cli`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci ROW_FORMAT=COMPACT;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `block_cli`
--

LOCK TABLES `block_cli` WRITE;
/*!40000 ALTER TABLE `block_cli` DISABLE KEYS */;
/*!40000 ALTER TABLE `block_cli` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `block_dst`
--

DROP TABLE IF EXISTS `block_dst`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `block_dst` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `dstnumber` varchar(30) DEFAULT NULL,
  `account_id` varchar(30) DEFAULT NULL,
  `create_dt` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `dstnumber` (`dstnumber`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci ROW_FORMAT=COMPACT;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `block_dst`
--

LOCK TABLES `block_dst` WRITE;
/*!40000 ALTER TABLE `block_dst` DISABLE KEYS */;
/*!40000 ALTER TABLE `block_dst` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `bundle_package`
--

DROP TABLE IF EXISTS `bundle_package`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `bundle_package` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `bundle_package_id` varchar(30) DEFAULT '',
  `bundle_package_name` varchar(30) DEFAULT '',
  `bundle_package_currency_id` varchar(11) DEFAULT '1',
  `bundle_package_status` enum('1','0') DEFAULT '1',
  `bundle_package_description` varchar(50) DEFAULT '',
  `created_by` varchar(30) NOT NULL,
  `create_dt` datetime DEFAULT NULL,
  `update_dt` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp(),
  `bundle_for` enum('IN','OUT') DEFAULT 'OUT',
  `package_option` enum('1','0') DEFAULT '0',
  `monthly_charges` double DEFAULT 0,
  `bundle_option` enum('1','0') DEFAULT '0',
  `bundle1_type` enum('MINUTE','COST') DEFAULT 'MINUTE',
  `bundle1_value` double(12,6) DEFAULT NULL,
  `bundle2_type` enum('MINUTE','COST') DEFAULT 'MINUTE',
  `bundle2_value` double(12,6) DEFAULT NULL,
  `bundle3_type` enum('MINUTE','COST') DEFAULT 'MINUTE',
  `bundle3_value` double(12,6) DEFAULT NULL,
  `updated_by` varchar(30) DEFAULT NULL,
  `account_id` varchar(30) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `bundle_package_id` (`bundle_package_id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci ROW_FORMAT=COMPACT;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `bundle_package`
--

LOCK TABLES `bundle_package` WRITE;
/*!40000 ALTER TABLE `bundle_package` DISABLE KEYS */;
/*!40000 ALTER TABLE `bundle_package` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `bundle_package_prefixes`
--

DROP TABLE IF EXISTS `bundle_package_prefixes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `bundle_package_prefixes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `bundle_package_id` varchar(30) NOT NULL,
  `bundle_id` enum('1','2','3') NOT NULL DEFAULT '1',
  `prefix` varchar(30) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `bundle_package_id` (`bundle_package_id`,`bundle_id`,`prefix`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci ROW_FORMAT=COMPACT;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `bundle_package_prefixes`
--

LOCK TABLES `bundle_package_prefixes` WRITE;
/*!40000 ALTER TABLE `bundle_package_prefixes` DISABLE KEYS */;
/*!40000 ALTER TABLE `bundle_package_prefixes` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `carrier`
--

DROP TABLE IF EXISTS `carrier`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `carrier` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `carrier_id` varchar(30) DEFAULT NULL,
  `carrier_name` varchar(30) NOT NULL,
  `tariff_id` varchar(30) NOT NULL,
  `carrier_type` enum('INBOUND','OUTBOUND') DEFAULT 'OUTBOUND',
  `carrier_status` int(11) DEFAULT 1,
  `carrier_cps` int(11) DEFAULT 10,
  `carrier_cc` int(11) DEFAULT 10,
  `carrier_currency_id` varchar(10) DEFAULT 'USD',
  `vendor_id` varchar(30) DEFAULT NULL,
  `carrier_progress_timeout` int(11) DEFAULT 5,
  `carrier_ring_timeout` int(11) DEFAULT 30,
  `cli_prefer` enum('rpid','pid','no') DEFAULT 'rpid',
  `carrier_codecs` varchar(50) DEFAULT 'G729,PCMU,PCMA',
  `gateway_withmedia` enum('1','0') DEFAULT '0',
  `tax1` float DEFAULT 0,
  `tax2` float DEFAULT 0,
  `tax3` float DEFAULT 0,
  `tax_type` enum('inclusive','exclusive') DEFAULT 'inclusive',
  `dp` int(11) DEFAULT 4,
  `vat_flag` enum('TAX','VAT','NONE','SZE','REVERSE') DEFAULT 'NONE',
  `tax_number` varchar(30) DEFAULT NULL,
  `account_id` varchar(30) DEFAULT NULL,
  `diversion_header_as_comingcli_db` enum('1','0') DEFAULT '1' COMMENT '1=as incoming  CLI; 0= from DB',
  `diversion_header_format` varchar(300) DEFAULT '<sip:${RDN}@${network_addr}>;reason=no-answer;counter=1;privacy=off',
  `diversion_header_option` enum('1','0') DEFAULT '1',
  `extend_call_duration` enum('0','1') DEFAULT '0',
  `minimumcallduration` int(11) DEFAULT 0,
  `created_by` varchar(30) DEFAULT NULL,
  `updated_by` varchar(30) DEFAULT NULL,
  `created_dt` datetime DEFAULT NULL,
  `updated_dt` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `key_carrier_id_name` (`carrier_id`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci ROW_FORMAT=COMPACT;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `carrier`
--

LOCK TABLES `carrier` WRITE;
/*!40000 ALTER TABLE `carrier` DISABLE KEYS */;
INSERT INTO `carrier` VALUES
(1,'OVDC23','OV Demo Carrier','BUTA58','OUTBOUND',1,10,10,'USD',NULL,5,60,'rpid','PCMU,PCMA','0',0,0,0,'exclusive',4,'NONE',NULL,NULL,'1','<sip:${RDN}@${network_addr}>;reason=no-answer;counter=1;privacy=off','0','0',0,NULL,NULL,NULL,NULL);
/*!40000 ALTER TABLE `carrier` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `carrier_block_numbers`
--

DROP TABLE IF EXISTS `carrier_block_numbers`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `carrier_block_numbers` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `carrier_id` varchar(30) DEFAULT NULL,
  `account_id` varchar(30) DEFAULT NULL,
  `block_number` varchar(30) DEFAULT NULL,
  `create_dt` datetime DEFAULT NULL,
  `update_dt` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp(),
  `created_by` varchar(30) DEFAULT NULL,
  `updated_by` varchar(30) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci ROW_FORMAT=COMPACT;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `carrier_block_numbers`
--

LOCK TABLES `carrier_block_numbers` WRITE;
/*!40000 ALTER TABLE `carrier_block_numbers` DISABLE KEYS */;
/*!40000 ALTER TABLE `carrier_block_numbers` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `carrier_callerid`
--

DROP TABLE IF EXISTS `carrier_callerid`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `carrier_callerid` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `maching_string` varchar(30) DEFAULT NULL,
  `remove_string` varchar(15) DEFAULT '%',
  `add_string` varchar(15) DEFAULT NULL,
  `carrier_id` varchar(30) DEFAULT NULL,
  `display_string` varchar(60) DEFAULT NULL,
  `action_type` enum('0','1') DEFAULT '1',
  `route` enum('INBOUND','OUTBOUND') DEFAULT 'OUTBOUND',
  `account_id` varchar(30) DEFAULT NULL,
  `created_by` varchar(30) DEFAULT NULL,
  `updated_by` varchar(30) DEFAULT NULL,
  `created_dt` datetime DEFAULT NULL,
  `updated_dt` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `carrier_callerid_key` (`carrier_id`,`maching_string`,`route`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci ROW_FORMAT=COMPACT;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `carrier_callerid`
--

LOCK TABLES `carrier_callerid` WRITE;
/*!40000 ALTER TABLE `carrier_callerid` DISABLE KEYS */;
INSERT INTO `carrier_callerid` VALUES
(1,'%','','%','OVDC23','%=>%','1','OUTBOUND',NULL,NULL,NULL,NULL,NULL),
(2,'%','','%','OVDC23','%=>%','1','INBOUND',NULL,NULL,NULL,NULL,NULL);
/*!40000 ALTER TABLE `carrier_callerid` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `carrier_diversion_number`
--

DROP TABLE IF EXISTS `carrier_diversion_number`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `carrier_diversion_number` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `diversion_number` varchar(30) DEFAULT NULL,
  `carrier_id` varchar(30) DEFAULT NULL,
  `account_id` varchar(30) DEFAULT NULL,
  `diversion_lot` varchar(30) DEFAULT NULL,
  `number_status` enum('0','1') DEFAULT '1',
  `assign_date` datetime DEFAULT NULL,
  `created_dt` datetime DEFAULT NULL,
  `created_by` varchar(30) DEFAULT NULL,
  `updated_by` varchar(30) DEFAULT NULL,
  `updated_dt` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `diversion_number_carrier` (`diversion_number`,`carrier_id`) USING BTREE,
  KEY `carrier_id` (`carrier_id`) USING BTREE,
  KEY `diversion_number` (`diversion_number`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci ROW_FORMAT=COMPACT;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `carrier_diversion_number`
--

LOCK TABLES `carrier_diversion_number` WRITE;
/*!40000 ALTER TABLE `carrier_diversion_number` DISABLE KEYS */;
/*!40000 ALTER TABLE `carrier_diversion_number` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `carrier_ips`
--

DROP TABLE IF EXISTS `carrier_ips`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `carrier_ips` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `carrier_ip_id` varchar(30) DEFAULT NULL,
  `carrier_id` varchar(30) DEFAULT NULL,
  `ipaddress_name` varchar(30) NOT NULL,
  `ipaddress` varchar(30) DEFAULT NULL,
  `load_share` int(11) NOT NULL DEFAULT 100,
  `priority` smallint(6) DEFAULT 1,
  `ip_status` enum('1','0') DEFAULT '1',
  `auth_type` enum('IP','CUSTOMER') DEFAULT 'IP',
  `username` varchar(50) DEFAULT NULL,
  `passwd` varchar(50) DEFAULT NULL,
  `account_id` varchar(30) DEFAULT NULL,
  `created_by` varchar(30) DEFAULT NULL,
  `updated_by` varchar(30) DEFAULT NULL,
  `created_dt` datetime DEFAULT NULL,
  `updated_dt` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `carrier_id` (`carrier_id`,`ipaddress_name`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci ROW_FORMAT=COMPACT;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `carrier_ips`
--

LOCK TABLES `carrier_ips` WRITE;
/*!40000 ALTER TABLE `carrier_ips` DISABLE KEYS */;
INSERT INTO `carrier_ips` VALUES
(1,'10101021759412764','OVDC23','10.10.10.2','10.10.10.2',100,1,'1','IP','','',NULL,NULL,NULL,NULL,NULL);
/*!40000 ALTER TABLE `carrier_ips` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `carrier_prefix`
--

DROP TABLE IF EXISTS `carrier_prefix`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `carrier_prefix` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `carrier_id` varchar(30) DEFAULT NULL,
  `maching_string` varchar(30) DEFAULT NULL,
  `remove_string` varchar(30) DEFAULT NULL,
  `add_string` varchar(30) DEFAULT NULL,
  `display_string` varchar(35) DEFAULT NULL,
  `route` enum('INBOUND','OUTBOUND') DEFAULT 'INBOUND',
  `account_id` varchar(30) DEFAULT NULL,
  `created_by` varchar(30) DEFAULT NULL,
  `updated_by` varchar(30) DEFAULT NULL,
  `created_dt` datetime DEFAULT NULL,
  `updated_dt` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `carrier_prefix_id_key` (`carrier_id`,`maching_string`,`route`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci ROW_FORMAT=COMPACT;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `carrier_prefix`
--

LOCK TABLES `carrier_prefix` WRITE;
/*!40000 ALTER TABLE `carrier_prefix` DISABLE KEYS */;
INSERT INTO `carrier_prefix` VALUES
(1,'OVDC23','%','','%','%=>%','OUTBOUND',NULL,NULL,NULL,NULL,NULL),
(2,'OVDC23','%','','%','%=>%','INBOUND',NULL,NULL,NULL,NULL,NULL);
/*!40000 ALTER TABLE `carrier_prefix` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `carrier_randomcli`
--

DROP TABLE IF EXISTS `carrier_randomcli`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `carrier_randomcli` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `clirule_id` varchar(30) DEFAULT '',
  `clirule_name` varchar(200) DEFAULT '',
  `carrier_id` varchar(30) DEFAULT '',
  `destination_prefix` varchar(30) DEFAULT '',
  `cli_fixprefix` varchar(10) DEFAULT '',
  `cli_length` int(11) DEFAULT 1,
  `cli_status` enum('1','0') DEFAULT '1',
  `account_id` varchar(30) DEFAULT NULL,
  `created_by` varchar(30) DEFAULT '',
  `updated_by` varchar(30) DEFAULT NULL,
  `created_dt` datetime DEFAULT NULL,
  `update_dt` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00' ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `clirule_id` (`clirule_id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci ROW_FORMAT=COMPACT;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `carrier_randomcli`
--

LOCK TABLES `carrier_randomcli` WRITE;
/*!40000 ALTER TABLE `carrier_randomcli` DISABLE KEYS */;
/*!40000 ALTER TABLE `carrier_randomcli` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `carrier_rates`
--

DROP TABLE IF EXISTS `carrier_rates`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `carrier_rates` (
  `rate_id` int(11) NOT NULL AUTO_INCREMENT,
  `ratecard_id` varchar(30) NOT NULL,
  `prefix` varchar(25) NOT NULL,
  `destination` varchar(150) NOT NULL,
  `setup_charge` double(12,6) NOT NULL DEFAULT 0.000000,
  `rental` double(12,6) NOT NULL DEFAULT 0.000000,
  `rate` double(12,6) NOT NULL DEFAULT 0.000000,
  `connection_charge` double DEFAULT 0,
  `minimal_time` int(11) NOT NULL DEFAULT 1,
  `resolution_time` int(11) DEFAULT 1,
  `grace_period` int(11) DEFAULT 0,
  `rate_multiplier` decimal(5,2) DEFAULT 1.00,
  `rate_addition` decimal(5,2) DEFAULT 0.00,
  `rates_status` enum('0','1') NOT NULL DEFAULT '1',
  `exclusive_per_channel_rental` double(12,6) DEFAULT 0.000000,
  `inclusive_channel` int(11) DEFAULT 1,
  `account_id` varchar(30) DEFAULT NULL,
  `minimal_charge` double(12,6) DEFAULT NULL,
  `ani_prefix` varchar(30) DEFAULT NULL,
  `updated_by` varchar(30) DEFAULT NULL,
  `created_by` varchar(30) DEFAULT NULL,
  `create_dt` timestamp NULL DEFAULT NULL,
  `update_dt` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp(),
  PRIMARY KEY (`rate_id`),
  UNIQUE KEY `pt` (`ratecard_id`,`prefix`) USING BTREE,
  KEY `prefix` (`prefix`) USING BTREE,
  KEY `tariff_id` (`ratecard_id`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci ROW_FORMAT=COMPACT;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `carrier_rates`
--

LOCK TABLES `carrier_rates` WRITE;
/*!40000 ALTER TABLE `carrier_rates` DISABLE KEYS */;
INSERT INTO `carrier_rates` VALUES
(1,'CARR38','1','USA Numbers',0.250000,0.250000,0.005000,0,6,6,0,1.00,0.00,'1',0.000000,10,NULL,NULL,NULL,NULL,NULL,'2025-10-02 19:41:40','2025-10-02 19:41:40'),
(2,'CARR30','1','USA Route',0.000000,0.000000,0.027000,0,6,6,0,1.00,0.00,'1',0.000000,1,NULL,NULL,NULL,NULL,NULL,'2025-10-02 19:43:31','2025-10-02 19:43:31');
/*!40000 ALTER TABLE `carrier_rates` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ci_cookies`
--

DROP TABLE IF EXISTS `ci_cookies`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `ci_cookies` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `cookie_id` varchar(255) DEFAULT NULL,
  `netid` varchar(255) DEFAULT NULL,
  `ip_address` varchar(255) DEFAULT NULL,
  `user_agent` varchar(255) DEFAULT NULL,
  `orig_page_requested` varchar(120) DEFAULT NULL,
  `php_session_id` varchar(40) DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci ROW_FORMAT=COMPACT;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ci_cookies`
--

LOCK TABLES `ci_cookies` WRITE;
/*!40000 ALTER TABLE `ci_cookies` DISABLE KEYS */;
/*!40000 ALTER TABLE `ci_cookies` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ci_sessions`
--

DROP TABLE IF EXISTS `ci_sessions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `ci_sessions` (
  `session_id` varchar(40) NOT NULL DEFAULT '0',
  `ip_address` varchar(45) NOT NULL DEFAULT '0',
  `user_agent` varchar(120) NOT NULL,
  `last_activity` int(10) unsigned NOT NULL DEFAULT 0,
  `user_data` text NOT NULL,
  PRIMARY KEY (`session_id`),
  KEY `last_activity_idx` (`last_activity`) USING BTREE
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ci_sessions`
--

LOCK TABLES `ci_sessions` WRITE;
/*!40000 ALTER TABLE `ci_sessions` DISABLE KEYS */;
/*!40000 ALTER TABLE `ci_sessions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `conferences`
--

DROP TABLE IF EXISTS `conferences`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `conferences` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `conference_id` varchar(30) NOT NULL,
  `account_id` varchar(30) NOT NULL,
  `conference_name` varchar(100) NOT NULL,
  `conference_no` varchar(20) NOT NULL,
  `conference_pin` varchar(20) DEFAULT NULL,
  `max_parties` int(10) unsigned DEFAULT 5,
  `conf_recording` enum('Y','N') DEFAULT 'N',
  `email_recording` enum('Y','N') NOT NULL DEFAULT 'N',
  `email_address` varchar(200) NOT NULL,
  `moh` varchar(30) DEFAULT NULL,
  `status_id` int(11) DEFAULT 1,
  `created_by` varchar(30) DEFAULT NULL,
  `created_dt` datetime DEFAULT NULL,
  `modified_by` varchar(30) DEFAULT NULL,
  `modified_dt` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `pbx_conference_id` (`conference_id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci ROW_FORMAT=COMPACT;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `conferences`
--

LOCK TABLES `conferences` WRITE;
/*!40000 ALTER TABLE `conferences` DISABLE KEYS */;
/*!40000 ALTER TABLE `conferences` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `credit_scheduler`
--

DROP TABLE IF EXISTS `credit_scheduler`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `credit_scheduler` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `account_id` varchar(30) NOT NULL,
  `credit_amount` double(12,6) NOT NULL,
  `execution_date` datetime NOT NULL,
  `is_emergency_credit` enum('Y','N') NOT NULL DEFAULT 'N',
  `status_id` enum('0','1','2') NOT NULL DEFAULT '0' COMMENT '0=acive,1=executed,2=cancelled',
  `created_by` varchar(30) NOT NULL,
  `create_date` datetime NOT NULL,
  `modify_date` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci ROW_FORMAT=COMPACT;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `credit_scheduler`
--

LOCK TABLES `credit_scheduler` WRITE;
/*!40000 ALTER TABLE `credit_scheduler` DISABLE KEYS */;
/*!40000 ALTER TABLE `credit_scheduler` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `customer_balance`
--

DROP TABLE IF EXISTS `customer_balance`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `customer_balance` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `credit_limit` double(12,6) DEFAULT 0.000000,
  `balance` double(12,6) DEFAULT 0.000000,
  `account_id` varchar(30) DEFAULT NULL,
  `maxcredit_limit` double(12,6) DEFAULT 0.000000,
  `update_dt` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp(),
  `service_type` enum('SWITCH','PBX') DEFAULT 'SWITCH',
  PRIMARY KEY (`id`),
  UNIQUE KEY `account_id` (`account_id`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci ROW_FORMAT=COMPACT;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `customer_balance`
--

LOCK TABLES `customer_balance` WRITE;
/*!40000 ALTER TABLE `customer_balance` DISABLE KEYS */;
INSERT INTO `customer_balance` VALUES
(1,0.000000,-10.000000,'OV500',0.000000,'2025-10-03 08:20:57','SWITCH');
/*!40000 ALTER TABLE `customer_balance` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `customer_bundle_sdr`
--

DROP TABLE IF EXISTS `customer_bundle_sdr`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `customer_bundle_sdr` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `account_id` varchar(30) DEFAULT NULL,
  `account_bundle_key` varchar(50) DEFAULT '',
  `bundle_package_id` varchar(30) DEFAULT '',
  `bundle_package_name` varchar(150) DEFAULT '',
  `total_allowed` double(18,0) DEFAULT 0,
  `total_allowed_sec` double(16,0) DEFAULT NULL,
  `monthly_charges` double(16,2) DEFAULT 0.00,
  `bundle_type` varchar(300) DEFAULT '',
  `bundle_for` enum('IN','OUT') DEFAULT 'OUT',
  `sdr_consumption` double(20,0) DEFAULT NULL,
  `action_date` datetime DEFAULT NULL,
  `rule_type` varchar(30) DEFAULT NULL,
  `yearmonth` varchar(30) DEFAULT NULL,
  `service_startdate` date DEFAULT NULL,
  `service_stopdate` date DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `package_id` (`account_id`,`account_bundle_key`,`bundle_package_id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci ROW_FORMAT=COMPACT;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `customer_bundle_sdr`
--

LOCK TABLES `customer_bundle_sdr` WRITE;
/*!40000 ALTER TABLE `customer_bundle_sdr` DISABLE KEYS */;
/*!40000 ALTER TABLE `customer_bundle_sdr` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `customer_callerid`
--

DROP TABLE IF EXISTS `customer_callerid`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `customer_callerid` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `maching_string` varchar(30) DEFAULT NULL,
  `match_length` smallint(6) DEFAULT NULL,
  `remove_string` varchar(15) DEFAULT '%',
  `add_string` varchar(15) DEFAULT NULL,
  `account_id` varchar(30) DEFAULT NULL,
  `display_string` varchar(60) DEFAULT NULL,
  `action_type` enum('0','1') DEFAULT '1',
  `service_type` varchar(30) DEFAULT 'SWITCH',
  `route` enum('INBOUND','OUTBOUND','DTSBASEDCLI') DEFAULT 'OUTBOUND',
  `created_by` varchar(30) DEFAULT NULL,
  `updated_by` varchar(30) DEFAULT NULL,
  `created_dt` datetime DEFAULT NULL,
  `updated_dt` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `user_callerid_key` (`account_id`,`maching_string`,`route`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci ROW_FORMAT=COMPACT;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `customer_callerid`
--

LOCK TABLES `customer_callerid` WRITE;
/*!40000 ALTER TABLE `customer_callerid` DISABLE KEYS */;
INSERT INTO `customer_callerid` VALUES
(1,'%',NULL,'','%','OV500','%=>%','1','SWITCH','INBOUND','UA000345178',NULL,'2025-10-02 15:49:16',NULL),
(2,'%',NULL,'','%','OV500','%=>%','1','SWITCH','OUTBOUND','UA000345178',NULL,'2025-10-02 15:49:16',NULL);
/*!40000 ALTER TABLE `customer_callerid` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `customer_devices`
--

DROP TABLE IF EXISTS `customer_devices`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `customer_devices` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `account_id` varchar(30) DEFAULT NULL,
  `extension_id` varchar(30) NOT NULL,
  `extensionplan_package_id` varchar(30) DEFAULT NULL,
  `extension_no` int(11) DEFAULT NULL,
  `name` varchar(100) NOT NULL,
  `username` varchar(30) DEFAULT NULL,
  `secret` varchar(30) DEFAULT NULL,
  `ipaddress` varchar(30) DEFAULT NULL,
  `status` enum('1','0') DEFAULT '1',
  `sip_cc` int(11) DEFAULT 1,
  `sip_cps` int(11) DEFAULT 1,
  `ipauthfrom` enum('YES','NO') DEFAULT 'NO',
  `voicemail_enabled` enum('Y','N') DEFAULT 'N',
  `voicemail` varchar(30) DEFAULT NULL,
  `display_name` varchar(30) DEFAULT NULL,
  `caller_id` varchar(150) DEFAULT NULL,
  `cli_prefer` enum('rpid','pid','no') DEFAULT 'rpid',
  `codecs` varchar(50) DEFAULT 'G729,PCMU,PCMA',
  `moh_sound` varchar(255) NOT NULL DEFAULT 'default',
  `email_address` varchar(150) NOT NULL,
  `phone_number` varchar(20) NOT NULL,
  `ring_timeout` int(11) DEFAULT 30,
  `call_forward_all` enum('Y','N') DEFAULT 'N',
  `cfall_destination_type` enum('NA','CUSTOMURI','PSTN','IP','EXTEN','HANGUP','IVR','TIMECONDITION','VOICEMAIL','ANNOUNCEMENT','QUEUE','RINGGROUP') NOT NULL DEFAULT 'HANGUP',
  `cfall_destination` varchar(30) DEFAULT NULL,
  `call_forward_no_answer` enum('Y','N') DEFAULT 'N',
  `cfnoans_destination_type` enum('NA','CUSTOMURI','PSTN','IP','EXTEN','HANGUP','IVR','TIMECONDITION','VOICEMAIL','ANNOUNCEMENT','QUEUE','RINGGROUP') NOT NULL DEFAULT 'HANGUP',
  `cfnoans_destination` varchar(30) DEFAULT NULL,
  `call_forward_busy` enum('Y','N') DEFAULT 'N',
  `cfbusy_destination_type` enum('NA','CUSTOMURI','PSTN','IP','EXTEN','HANGUP','IVR','TIMECONDITION','VOICEMAIL','ANNOUNCEMENT','QUEUE','RINGGROUP') NOT NULL DEFAULT 'HANGUP',
  `cfbusy_destination` varchar(30) DEFAULT NULL,
  `cfnoans_timeout` smallint(6) DEFAULT NULL,
  `call_recording` enum('1','0') NOT NULL DEFAULT '0',
  `dnd` enum('Y','N') DEFAULT 'N',
  `created_by` varchar(30) NOT NULL,
  `created_by_account_id` varchar(30) NOT NULL,
  `updated_by` varchar(30) NOT NULL,
  `created_dt` datetime NOT NULL,
  `updated_dt` datetime DEFAULT NULL,
  `user_type` enum('SWITCH','PBX') DEFAULT 'SWITCH',
  `pushtoken` varchar(150) DEFAULT NULL,
  `firebasetoken` varchar(500) DEFAULT NULL,
  `appos` varchar(50) DEFAULT NULL,
  `pushkittoken` varchar(500) DEFAULT NULL,
  `user_id` varchar(30) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`) USING BTREE,
  UNIQUE KEY `account_exten` (`extension_no`,`account_id`) USING BTREE,
  KEY `account_id` (`account_id`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci ROW_FORMAT=COMPACT;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `customer_devices`
--

LOCK TABLES `customer_devices` WRITE;
/*!40000 ALTER TABLE `customer_devices` DISABLE KEYS */;
INSERT INTO `customer_devices` VALUES
(1,'OV500','EXT000001736',NULL,NULL,'','openvoipsTest','OV@Test1','','1',10,2,'NO','Y','EXT000001736',NULL,NULL,'rpid','G729,PCMU,PCMA','default','','',30,'N','HANGUP',NULL,'N','HANGUP',NULL,'N','HANGUP',NULL,NULL,'0','N','','','','0000-00-00 00:00:00',NULL,'SWITCH',NULL,NULL,NULL,NULL,NULL),
(2,'OV500','EOVHEY7900','DFE31P',1001,'OV Help','C18tA02m','pKM&v2)#1W','','1',1,1,'NO','N','',NULL,'12345678900','rpid','G729,PCMU,PCMA','default','','',60,'N','','','N','','','N','','',0,'0','N','UC000003333','','UC000003333','2025-10-03 10:26:10','2025-10-03 17:50:25','PBX',NULL,NULL,NULL,NULL,'Ue000004114');
/*!40000 ALTER TABLE `customer_devices` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `customer_dialpattern`
--

DROP TABLE IF EXISTS `customer_dialpattern`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `customer_dialpattern` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `account_id` varchar(30) DEFAULT NULL,
  `maching_string` varchar(30) DEFAULT NULL,
  `match_length` smallint(6) DEFAULT NULL,
  `remove_string` varchar(20) DEFAULT NULL,
  `add_string` varchar(20) DEFAULT NULL,
  `display_string` varchar(30) DEFAULT '1',
  `action_type` enum('1','0') DEFAULT '1',
  `route` enum('INBOUND','OUTBOUND') DEFAULT 'OUTBOUND',
  `created_by` varchar(30) DEFAULT NULL,
  `updated_by` varchar(30) DEFAULT NULL,
  `created_dt` datetime DEFAULT NULL,
  `updated_dt` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp(),
  `service_type` varchar(30) DEFAULT 'SWITCH',
  PRIMARY KEY (`id`),
  UNIQUE KEY `user_dialplan_key` (`account_id`,`maching_string`,`route`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci ROW_FORMAT=COMPACT;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `customer_dialpattern`
--

LOCK TABLES `customer_dialpattern` WRITE;
/*!40000 ALTER TABLE `customer_dialpattern` DISABLE KEYS */;
INSERT INTO `customer_dialpattern` VALUES
(1,'OV500','%',NULL,'','%','%=>%','1','OUTBOUND','UA000345178',NULL,'2025-10-02 15:49:16',NULL,'SWITCH'),
(2,'OV500','%',NULL,'','%','%=>%','1','INBOUND','UA000345178',NULL,'2025-10-02 15:49:16',NULL,'SWITCH');
/*!40000 ALTER TABLE `customer_dialpattern` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `customer_dialplan`
--

DROP TABLE IF EXISTS `customer_dialplan`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `customer_dialplan` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `account_id` varchar(30) NOT NULL,
  `dialplan_id` varchar(30) NOT NULL DEFAULT '1',
  `maching_string` varchar(30) DEFAULT NULL,
  `display_string` varchar(30) DEFAULT NULL,
  `remove_string` varchar(30) DEFAULT NULL,
  `add_string` varchar(30) DEFAULT NULL,
  `created_by` varchar(30) DEFAULT NULL,
  `updated_by` varchar(30) DEFAULT NULL,
  `created_dt` datetime DEFAULT NULL,
  `updated_dt` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `user_carrier_dialplan_key` (`account_id`,`maching_string`) USING BTREE,
  KEY `maching_string_key` (`maching_string`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci ROW_FORMAT=COMPACT;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `customer_dialplan`
--

LOCK TABLES `customer_dialplan` WRITE;
/*!40000 ALTER TABLE `customer_dialplan` DISABLE KEYS */;
INSERT INTO `customer_dialplan` VALUES
(1,'OV500','DERO33','%','%=>DERO33%','',NULL,NULL,NULL,NULL,NULL);
/*!40000 ALTER TABLE `customer_dialplan` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `customer_ips`
--

DROP TABLE IF EXISTS `customer_ips`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `customer_ips` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `account_id` varchar(30) DEFAULT '',
  `ipaddress` varchar(30) DEFAULT NULL,
  `ip_status` enum('1','0') DEFAULT '1',
  `ip_cc` int(11) DEFAULT 10,
  `ip_cps` int(11) DEFAULT 1,
  `description` varchar(30) DEFAULT NULL,
  `dialprefix` varchar(30) DEFAULT NULL,
  `ipauthfrom` enum('SRC','FROM','NO') DEFAULT 'SRC',
  `billingcode` varchar(30) DEFAULT NULL,
  `created_by` varchar(30) DEFAULT NULL,
  `updated_by` varchar(30) DEFAULT NULL,
  `created_dt` datetime DEFAULT NULL,
  `updated_dt` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `user_ips_ipaddress_key` (`ipaddress`,`dialprefix`,`billingcode`) USING BTREE,
  KEY `account_id` (`account_id`) USING BTREE,
  KEY `ipaddress` (`ipaddress`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci ROW_FORMAT=COMPACT;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `customer_ips`
--

LOCK TABLES `customer_ips` WRITE;
/*!40000 ALTER TABLE `customer_ips` DISABLE KEYS */;
/*!40000 ALTER TABLE `customer_ips` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `customer_otp`
--

DROP TABLE IF EXISTS `customer_otp`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `customer_otp` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `phone_number` varchar(30) DEFAULT NULL,
  `otp` int(11) NOT NULL,
  `token` varchar(50) DEFAULT NULL,
  `status` enum('1','2','0') DEFAULT '1',
  `appos` varchar(50) DEFAULT NULL,
  `firebasetoken` varchar(300) DEFAULT NULL,
  `expiary_time` datetime NOT NULL,
  `created_dt` datetime NOT NULL,
  `updated_dt` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci ROW_FORMAT=COMPACT;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `customer_otp`
--

LOCK TABLES `customer_otp` WRITE;
/*!40000 ALTER TABLE `customer_otp` DISABLE KEYS */;
/*!40000 ALTER TABLE `customer_otp` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `customer_permissions`
--

DROP TABLE IF EXISTS `customer_permissions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `customer_permissions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `account_id` varchar(30) NOT NULL,
  `permissions` text NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `account_id` (`account_id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci ROW_FORMAT=COMPACT;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `customer_permissions`
--

LOCK TABLES `customer_permissions` WRITE;
/*!40000 ALTER TABLE `customer_permissions` DISABLE KEYS */;
/*!40000 ALTER TABLE `customer_permissions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `customer_rates`
--

DROP TABLE IF EXISTS `customer_rates`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `customer_rates` (
  `rate_id` int(11) NOT NULL AUTO_INCREMENT,
  `ratecard_id` varchar(30) NOT NULL,
  `prefix` varchar(25) NOT NULL,
  `destination` varchar(150) NOT NULL,
  `setup_charge` double(12,6) NOT NULL DEFAULT 0.000000,
  `rental` double(12,6) NOT NULL DEFAULT 0.000000,
  `rate` double(12,6) NOT NULL DEFAULT 0.000000,
  `connection_charge` double DEFAULT 0,
  `minimal_time` int(11) NOT NULL DEFAULT 1,
  `resolution_time` int(11) DEFAULT 1,
  `grace_period` int(11) DEFAULT 0,
  `rate_multiplier` decimal(5,2) DEFAULT 1.00,
  `rate_addition` decimal(5,2) DEFAULT 0.00,
  `rates_status` enum('0','1') NOT NULL DEFAULT '1',
  `exclusive_per_channel_rental` double(12,6) DEFAULT 0.000000,
  `inclusive_channel` int(11) DEFAULT 1,
  `account_id` varchar(30) DEFAULT NULL,
  `minimal_charge` double(12,6) DEFAULT NULL,
  `ani_prefix` varchar(30) DEFAULT NULL,
  `updated_by` varchar(30) DEFAULT NULL,
  `created_by` varchar(30) DEFAULT NULL,
  `create_dt` datetime DEFAULT NULL,
  `update_dt` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp(),
  PRIMARY KEY (`rate_id`),
  UNIQUE KEY `pt` (`ratecard_id`,`prefix`) USING BTREE,
  KEY `prefix` (`prefix`) USING BTREE,
  KEY `tariff_id` (`ratecard_id`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci ROW_FORMAT=COMPACT;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `customer_rates`
--

LOCK TABLES `customer_rates` WRITE;
/*!40000 ALTER TABLE `customer_rates` DISABLE KEYS */;
INSERT INTO `customer_rates` VALUES
(1,'SAIN46','1','USA Numbers',0.500000,0.500000,0.006000,0,6,6,0,1.00,0.00,'1',0.000000,10,NULL,NULL,NULL,NULL,NULL,'2025-10-02 15:40:40','2025-10-02 19:40:40'),
(2,'SAOU43','1','USA Route',0.000000,0.000000,0.030000,0,6,6,0,1.00,0.00,'1',0.000000,1,NULL,NULL,NULL,NULL,NULL,'2025-10-02 15:44:05','2025-10-02 19:44:05');
/*!40000 ALTER TABLE `customer_rates` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `customer_type_permissions`
--

DROP TABLE IF EXISTS `customer_type_permissions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `customer_type_permissions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_type` enum('RESELLER','CUSTOMER','ADMIN','SUBADMIN','NOC','CARRIER','ACCOUNTMANAGER','CREDITCONTROL','SALESMANAGER') NOT NULL,
  `permissions` text NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci ROW_FORMAT=COMPACT;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `customer_type_permissions`
--

LOCK TABLES `customer_type_permissions` WRITE;
/*!40000 ALTER TABLE `customer_type_permissions` DISABLE KEYS */;
/*!40000 ALTER TABLE `customer_type_permissions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `customer_voipminuts`
--

DROP TABLE IF EXISTS `customer_voipminuts`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `customer_voipminuts` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `customer_voipminute_id` varchar(30) DEFAULT NULL,
  `account_id` varchar(30) DEFAULT NULL,
  `billingcode` varchar(30) DEFAULT NULL,
  `account_type` varchar(30) DEFAULT NULL,
  `tariff_id` varchar(30) DEFAULT NULL,
  `status` enum('1','0') DEFAULT '1',
  `created_by` varchar(30) NOT NULL,
  `updated_by` varchar(30) NOT NULL,
  `created_dt` datetime NOT NULL,
  `updated_dt` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `voip_id` (`customer_voipminute_id`) USING BTREE,
  UNIQUE KEY `account_id_2` (`account_id`,`tariff_id`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci ROW_FORMAT=COMPACT;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `customer_voipminuts`
--

LOCK TABLES `customer_voipminuts` WRITE;
/*!40000 ALTER TABLE `customer_voipminuts` DISABLE KEYS */;
INSERT INTO `customer_voipminuts` VALUES
(1,'CVM000001624','OV500',NULL,'CUSTOMER','SATA45','1','UA000345178','','2025-10-02 15:49:16',NULL);
/*!40000 ALTER TABLE `customer_voipminuts` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `customers`
--

DROP TABLE IF EXISTS `customers`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `customers` (
  `customer_id` int(11) NOT NULL AUTO_INCREMENT,
  `account_id` varchar(50) DEFAULT NULL,
  `company_name` varchar(50) NOT NULL,
  `contact_name` varchar(150) DEFAULT NULL,
  `name` varchar(30) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `country_id` int(11) DEFAULT NULL,
  `state_code_id` mediumint(9) DEFAULT NULL,
  `phone` varchar(30) DEFAULT NULL,
  `emailaddress` varchar(50) DEFAULT NULL,
  `billing_type` enum('prepaid','postpaid','netoff') NOT NULL DEFAULT 'prepaid',
  `billing_cycle` enum('weekly','monthly') NOT NULL DEFAULT 'monthly',
  `payment_terms` int(11) NOT NULL DEFAULT 30,
  `next_billing_date` date DEFAULT NULL,
  `pincode` varchar(15) DEFAULT NULL,
  `view_ipdevices` enum('1','0') DEFAULT '1',
  `view_sipdevice` enum('1','0') DEFAULT '1',
  `view_src_out` enum('1','0') DEFAULT '1',
  `view_dst_out` enum('1','0') DEFAULT '1',
  `view_src_did` enum('1','0') DEFAULT '1',
  `view_dst_did` enum('1','0') DEFAULT '1',
  `broadcasting_dialer` enum('1','0') DEFAULT '0',
  `attest_in_checking` enum('1','0') DEFAULT '0',
  `attest_in_a` enum('1','0') DEFAULT '0',
  `attest_in_b` enum('1','0') DEFAULT '0',
  `attest_in_c` enum('1','0') DEFAULT NULL,
  `created_by` varchar(30) DEFAULT NULL,
  `updated_by` varchar(30) DEFAULT NULL,
  `created_dt` datetime DEFAULT NULL,
  `updated_dt` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp(),
  PRIMARY KEY (`customer_id`),
  UNIQUE KEY `accountid` (`account_id`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci ROW_FORMAT=COMPACT;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `customers`
--

LOCK TABLES `customers` WRITE;
/*!40000 ALTER TABLE `customers` DISABLE KEYS */;
INSERT INTO `customers` VALUES
(1,'OV500','Openvoips Technologies','Openvoips Help',NULL,'',0,0,'','openvoips.help@gmail.com','prepaid','monthly',30,NULL,'','1','1','1','1','1','1','0','0','0','0',NULL,NULL,NULL,NULL,NULL);
/*!40000 ALTER TABLE `customers` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `db_data`
--

DROP TABLE IF EXISTS `db_data`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `db_data` (
  `hostname` varchar(255) DEFAULT NULL,
  `realm` varchar(255) DEFAULT NULL,
  `data_key` varchar(255) DEFAULT NULL,
  `data` varchar(255) DEFAULT NULL,
  UNIQUE KEY `dd_data_key_realm` (`data_key`,`realm`) USING BTREE,
  KEY `dd_realm` (`realm`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci ROW_FORMAT=COMPACT;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `db_data`
--

LOCK TABLES `db_data` WRITE;
/*!40000 ALTER TABLE `db_data` DISABLE KEYS */;
/*!40000 ALTER TABLE `db_data` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `delete_history`
--

DROP TABLE IF EXISTS `delete_history`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `delete_history` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `delete_type` varchar(30) NOT NULL,
  `delete_status` varchar(30) NOT NULL,
  `delete_date` timestamp NOT NULL DEFAULT current_timestamp(),
  `delete_code` varchar(30) NOT NULL,
  `deleted_by` varchar(30) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci ROW_FORMAT=COMPACT;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `delete_history`
--

LOCK TABLES `delete_history` WRITE;
/*!40000 ALTER TABLE `delete_history` DISABLE KEYS */;
/*!40000 ALTER TABLE `delete_history` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `dialer_campaigns`
--

DROP TABLE IF EXISTS `dialer_campaigns`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `dialer_campaigns` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `campaign_id` varchar(30) DEFAULT NULL,
  `account_id` varchar(30) DEFAULT NULL,
  `campaign_number` int(11) DEFAULT NULL,
  `campaign_name` varchar(500) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `maximumcalls` int(11) DEFAULT NULL,
  `route` varchar(30) DEFAULT NULL,
  `route_endpoint` varchar(30) DEFAULT NULL,
  `status_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `campaign_id` (`campaign_id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci ROW_FORMAT=COMPACT;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `dialer_campaigns`
--

LOCK TABLES `dialer_campaigns` WRITE;
/*!40000 ALTER TABLE `dialer_campaigns` DISABLE KEYS */;
/*!40000 ALTER TABLE `dialer_campaigns` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `dialer_lead_group`
--

DROP TABLE IF EXISTS `dialer_lead_group`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `dialer_lead_group` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `lead_group_id` varchar(30) DEFAULT NULL,
  `lead_group_name` varchar(50) DEFAULT NULL,
  `account_id` varchar(30) DEFAULT NULL,
  `campaign_id` varchar(30) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci ROW_FORMAT=COMPACT;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `dialer_lead_group`
--

LOCK TABLES `dialer_lead_group` WRITE;
/*!40000 ALTER TABLE `dialer_lead_group` DISABLE KEYS */;
/*!40000 ALTER TABLE `dialer_lead_group` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `dialer_leads`
--

DROP TABLE IF EXISTS `dialer_leads`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `dialer_leads` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `campaign_id` varchar(30) DEFAULT NULL,
  `lead_group_id` varchar(30) DEFAULT NULL,
  `account_id` varchar(30) DEFAULT NULL,
  `create_dt` datetime DEFAULT NULL,
  `call_status` varchar(30) DEFAULT NULL,
  `call_date` datetime DEFAULT NULL,
  `call_try_count` int(11) DEFAULT 0,
  `calling_status` varchar(30) DEFAULT NULL,
  `lead_comments` varchar(500) DEFAULT NULL,
  `call_uuid` varchar(300) DEFAULT NULL,
  `first_name` varchar(50) DEFAULT NULL,
  `last_name` varchar(50) DEFAULT NULL,
  `emailid` varchar(100) DEFAULT NULL,
  `phone1` varchar(50) DEFAULT NULL,
  `zip` varchar(50) DEFAULT NULL,
  `location` varchar(100) DEFAULT NULL,
  `status_id` enum('1','0') DEFAULT '1',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci ROW_FORMAT=COMPACT;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `dialer_leads`
--

LOCK TABLES `dialer_leads` WRITE;
/*!40000 ALTER TABLE `dialer_leads` DISABLE KEYS */;
/*!40000 ALTER TABLE `dialer_leads` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `dialplan`
--

DROP TABLE IF EXISTS `dialplan`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `dialplan` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `account_id` varchar(30) DEFAULT NULL,
  `dialplan_id` varchar(30) DEFAULT NULL,
  `dialplan_name` varchar(20) DEFAULT NULL,
  `dialplan_status` enum('1','0') DEFAULT '1',
  `failover_sipcause_list` varchar(300) DEFAULT 'NO_ROUTE_DESTINATION,CHANNEL_UNACCEPTABLE,410,483,503,488,501,504,401,402,403,404',
  `dialplan_description` varchar(50) DEFAULT NULL,
  `created_by` varchar(30) DEFAULT NULL,
  `updated_by` varchar(30) DEFAULT NULL,
  `create_dt` timestamp NULL DEFAULT NULL,
  `update_dt` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `dialplan_id_name` (`dialplan_id`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci ROW_FORMAT=COMPACT;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `dialplan`
--

LOCK TABLES `dialplan` WRITE;
/*!40000 ALTER TABLE `dialplan` DISABLE KEYS */;
INSERT INTO `dialplan` VALUES
(1,NULL,'DERO33','Default Route','1','','',NULL,NULL,'2025-10-02 19:46:46','2025-10-02 19:46:46');
/*!40000 ALTER TABLE `dialplan` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `dialplan_prefix_list`
--

DROP TABLE IF EXISTS `dialplan_prefix_list`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `dialplan_prefix_list` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `account_id` varchar(30) DEFAULT NULL,
  `dialplan_id` varchar(30) CHARACTER SET latin1 COLLATE latin1_swedish_ci DEFAULT NULL,
  `dial_prefix` varchar(30) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,
  `priority` smallint(6) NOT NULL DEFAULT 1,
  `route_status` enum('0','1') CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL DEFAULT '1',
  `carrier_id` varchar(30) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,
  `start_day` smallint(6) DEFAULT 0,
  `start_time` varchar(8) CHARACTER SET latin1 COLLATE latin1_swedish_ci DEFAULT '00:00:00',
  `end_day` smallint(6) DEFAULT 6,
  `end_time` varchar(8) CHARACTER SET latin1 COLLATE latin1_swedish_ci DEFAULT '24:00:00',
  `load_share` int(11) DEFAULT 100,
  `created_by` varchar(30) DEFAULT NULL,
  `updated_by` varchar(30) DEFAULT NULL,
  `create_dt` datetime DEFAULT NULL,
  `update_dt` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `dialplan_list_name` (`dial_prefix`,`carrier_id`,`dialplan_id`) USING BTREE,
  KEY `dialplan_id_name` (`dialplan_id`) USING BTREE,
  KEY `dial_prefix` (`dial_prefix`) USING BTREE,
  KEY `route_status` (`route_status`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=latin7 COLLATE=latin7_general_ci ROW_FORMAT=COMPACT;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `dialplan_prefix_list`
--

LOCK TABLES `dialplan_prefix_list` WRITE;
/*!40000 ALTER TABLE `dialplan_prefix_list` DISABLE KEYS */;
INSERT INTO `dialplan_prefix_list` VALUES
(2,NULL,'DERO33','%',1,'1','OVDC23',0,'00:00:00',6,'23:59:59',100,NULL,NULL,'2025-10-03 10:21:59','2025-10-03 14:21:59');
/*!40000 ALTER TABLE `dialplan_prefix_list` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `did`
--

DROP TABLE IF EXISTS `did`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `did` (
  `did_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `did_number` varchar(30) DEFAULT NULL,
  `did_status` varchar(30) DEFAULT 'NEW',
  `carrier_id` varchar(30) DEFAULT NULL,
  `account_id` varchar(30) DEFAULT NULL,
  `assign_date` datetime DEFAULT NULL,
  `reseller1_account_id` varchar(30) DEFAULT NULL,
  `reseller1_assign_date` datetime DEFAULT NULL,
  `reseller2_account_id` varchar(30) DEFAULT NULL,
  `reseller2_assign_date` datetime DEFAULT NULL,
  `reseller3_account_id` varchar(30) DEFAULT NULL,
  `reseller3_assign_date` datetime DEFAULT NULL,
  `create_date` datetime DEFAULT NULL,
  `channels` int(11) DEFAULT 1,
  `did_name` varchar(150) DEFAULT NULL,
  `number_type` enum('TFN','DID') DEFAULT 'DID',
  `lastbilldate` date DEFAULT NULL,
  `r1lastbilldate` date DEFAULT NULL,
  `r2lastbilldate` date DEFAULT NULL,
  `r3lastbilldate` date DEFAULT NULL,
  PRIMARY KEY (`did_id`),
  UNIQUE KEY `did_number` (`did_number`) USING BTREE,
  KEY `account_id` (`account_id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci ROW_FORMAT=COMPACT;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `did`
--

LOCK TABLES `did` WRITE;
/*!40000 ALTER TABLE `did` DISABLE KEYS */;
/*!40000 ALTER TABLE `did` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `did_dst`
--

DROP TABLE IF EXISTS `did_dst`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `did_dst` (
  `did_dst_id` int(11) NOT NULL AUTO_INCREMENT,
  `did_number` varchar(30) DEFAULT NULL,
  `account_id` varchar(30) DEFAULT NULL,
  `dst_type` varchar(30) DEFAULT 'IP',
  `dst_destination` varchar(30) DEFAULT NULL,
  `create_date` datetime DEFAULT NULL,
  `update_date` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp(),
  `dst_type2` enum('IP','CUSTOMER','PSTN') DEFAULT 'IP',
  `dst_destination2` varchar(30) DEFAULT NULL,
  PRIMARY KEY (`did_dst_id`),
  UNIQUE KEY `UK_did_dst_did_number` (`did_number`),
  KEY `IDX_did_dst_account_id` (`account_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci ROW_FORMAT=COMPACT;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `did_dst`
--

LOCK TABLES `did_dst` WRITE;
/*!40000 ALTER TABLE `did_dst` DISABLE KEYS */;
/*!40000 ALTER TABLE `did_dst` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `didclifilter`
--

DROP TABLE IF EXISTS `didclifilter`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `didclifilter` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `account_id` varchar(30) DEFAULT NULL,
  `callerid` varchar(200) DEFAULT NULL,
  `cli_status` enum('1','0','2') DEFAULT '1',
  `created_by` varchar(30) DEFAULT NULL,
  `updated_by` varchar(30) DEFAULT NULL,
  `created_dt` datetime DEFAULT NULL,
  `updated_dt` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `account_id` (`account_id`),
  KEY `callerid` (`callerid`),
  KEY `cli_status` (`cli_status`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci ROW_FORMAT=COMPACT;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `didclifilter`
--

LOCK TABLES `didclifilter` WRITE;
/*!40000 ALTER TABLE `didclifilter` DISABLE KEYS */;
/*!40000 ALTER TABLE `didclifilter` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `dtone_history`
--

DROP TABLE IF EXISTS `dtone_history`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `dtone_history` (
  `payment_id` int(11) NOT NULL AUTO_INCREMENT,
  `account_id` varchar(30) NOT NULL,
  `product_id` varchar(30) NOT NULL,
  `amount` decimal(12,6) NOT NULL,
  `paid_on` datetime NOT NULL,
  `notes` text DEFAULT NULL,
  `transaction_id` varchar(50) NOT NULL,
  `amount_customer` decimal(12,6) NOT NULL,
  `amount_dtone` decimal(12,6) NOT NULL,
  `currency` varchar(30) NOT NULL,
  `phone_number` varchar(30) NOT NULL,
  PRIMARY KEY (`payment_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci ROW_FORMAT=COMPACT;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `dtone_history`
--

LOCK TABLES `dtone_history` WRITE;
/*!40000 ALTER TABLE `dtone_history` DISABLE KEYS */;
/*!40000 ALTER TABLE `dtone_history` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `dtone_tracking`
--

DROP TABLE IF EXISTS `dtone_tracking`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `dtone_tracking` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `account_id` varchar(30) NOT NULL,
  `product_id` varchar(100) NOT NULL,
  `external_id` varchar(50) DEFAULT NULL,
  `order_status` enum('initiated','failed','success') NOT NULL DEFAULT 'initiated',
  `send_string` text NOT NULL,
  `response_string` text NOT NULL,
  `order_date` timestamp NOT NULL DEFAULT current_timestamp(),
  `notes` text DEFAULT NULL,
  `amount_customer` decimal(12,6) NOT NULL,
  `amount_dtone` decimal(12,6) NOT NULL,
  `converted_amount_customer` decimal(12,6) NOT NULL,
  `converted_amount_dtone` decimal(12,6) NOT NULL,
  `currency` varchar(30) NOT NULL,
  `converted_currency` varchar(30) NOT NULL,
  `phone_number` varchar(30) NOT NULL,
  `sender` varchar(30) DEFAULT NULL,
  `operator` varchar(30) DEFAULT NULL,
  `country` varchar(30) DEFAULT NULL,
  `received_value` varchar(30) DEFAULT NULL,
  `product_name` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci ROW_FORMAT=COMPACT;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `dtone_tracking`
--

LOCK TABLES `dtone_tracking` WRITE;
/*!40000 ALTER TABLE `dtone_tracking` DISABLE KEYS */;
/*!40000 ALTER TABLE `dtone_tracking` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `emaillog`
--

DROP TABLE IF EXISTS `emaillog`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `emaillog` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `account_id` varchar(30) DEFAULT NULL,
  `action_date` datetime DEFAULT NULL,
  `subject` varchar(300) DEFAULT NULL,
  `body` text DEFAULT NULL,
  `attachement` blob DEFAULT NULL,
  `actionfrom` varchar(500) DEFAULT NULL,
  `email_to` varchar(500) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci ROW_FORMAT=COMPACT;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `emaillog`
--

LOCK TABLES `emaillog` WRITE;
/*!40000 ALTER TABLE `emaillog` DISABLE KEYS */;
/*!40000 ALTER TABLE `emaillog` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `extensionplan_account`
--

DROP TABLE IF EXISTS `extensionplan_account`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `extensionplan_account` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `extensionplan_package_id` varchar(30) DEFAULT NULL,
  `extensionplan_package_name` varchar(50) DEFAULT NULL,
  `account_id` varchar(30) DEFAULT NULL,
  `assign_dt` date DEFAULT NULL,
  `extensionplan_bundle_key` varchar(30) DEFAULT NULL,
  `extensionplan_package_desc` varchar(50) DEFAULT NULL,
  `lastbill_execute_date` date DEFAULT NULL,
  `lastbilldate` date DEFAULT NULL,
  `rental` double(20,10) DEFAULT NULL,
  `created_by` varchar(30) DEFAULT NULL,
  `updated_by` varchar(30) DEFAULT NULL,
  `created_dt` datetime DEFAULT NULL,
  `updated_dt` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `account_bundle_key` (`extensionplan_bundle_key`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci ROW_FORMAT=COMPACT;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `extensionplan_account`
--

LOCK TABLES `extensionplan_account` WRITE;
/*!40000 ALTER TABLE `extensionplan_account` DISABLE KEYS */;
INSERT INTO `extensionplan_account` VALUES
(1,'DFE31P',NULL,'OV500','2025-10-03',NULL,NULL,NULL,NULL,NULL,'UA000345178',NULL,'2025-10-03 10:23:12',NULL);
/*!40000 ALTER TABLE `extensionplan_account` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `extensionplan_package`
--

DROP TABLE IF EXISTS `extensionplan_package`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `extensionplan_package` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `parent_extensionplan_package_id` varchar(30) DEFAULT NULL,
  `extensionplan_package_id` varchar(30) DEFAULT '',
  `extensionplan_package_name` varchar(30) DEFAULT '',
  `extensionplan_package_currency_id` varchar(10) DEFAULT 'USD',
  `extensionplan_package_status` enum('1','0') DEFAULT '1',
  `extensionplan_package_description` varchar(50) DEFAULT '',
  `package_option` enum('1','0') DEFAULT '0',
  `monthly_charges` double DEFAULT 0,
  `extensionplan_option` enum('1','0') DEFAULT '0',
  `extensionplan1_type` enum('MINUTE','COST') DEFAULT 'MINUTE',
  `extensionplan1_value` double(12,6) DEFAULT NULL,
  `updated_by` varchar(30) DEFAULT NULL,
  `account_id` varchar(30) DEFAULT NULL,
  `create_dt` datetime DEFAULT NULL,
  `created_by` varchar(30) NOT NULL,
  `update_dt` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `bundle_package_id` (`extensionplan_package_id`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci ROW_FORMAT=COMPACT;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `extensionplan_package`
--

LOCK TABLES `extensionplan_package` WRITE;
/*!40000 ALTER TABLE `extensionplan_package` DISABLE KEYS */;
INSERT INTO `extensionplan_package` VALUES
(1,NULL,'DFE31P','Default Free Extension','USD','1','','0',0,'0',NULL,NULL,NULL,'SYSTEM','2025-10-03 10:22:55','UA000345178','2025-10-03 14:22:55');
/*!40000 ALTER TABLE `extensionplan_package` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `extensionplan_package_prefixes`
--

DROP TABLE IF EXISTS `extensionplan_package_prefixes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `extensionplan_package_prefixes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `extensionplan_package_id` varchar(30) NOT NULL,
  `extensionplan_id` enum('1','2','3') NOT NULL DEFAULT '1',
  `prefix` varchar(30) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `bundle_package_id` (`extensionplan_package_id`,`extensionplan_id`,`prefix`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci ROW_FORMAT=COMPACT;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `extensionplan_package_prefixes`
--

LOCK TABLES `extensionplan_package_prefixes` WRITE;
/*!40000 ALTER TABLE `extensionplan_package_prefixes` DISABLE KEYS */;
/*!40000 ALTER TABLE `extensionplan_package_prefixes` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `feature_code`
--

DROP TABLE IF EXISTS `feature_code`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `feature_code` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `dialcode` varchar(20) NOT NULL,
  `account_id` varchar(30) NOT NULL,
  `feature_id` varchar(10) NOT NULL,
  `feature_desc` varchar(300) DEFAULT NULL,
  `created_by` varchar(30) NOT NULL,
  `created_by_account_id` varchar(30) NOT NULL,
  `updated_by` varchar(30) NOT NULL,
  `created_dt` datetime NOT NULL,
  `updated_dt` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `dialcode` (`account_id`,`feature_id`) USING BTREE,
  UNIQUE KEY `feature_id` (`account_id`,`dialcode`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=21 DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci ROW_FORMAT=COMPACT;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `feature_code`
--

LOCK TABLES `feature_code` WRITE;
/*!40000 ALTER TABLE `feature_code` DISABLE KEYS */;
INSERT INTO `feature_code` VALUES
(1,'*30','OV500','CFAE','Call Forward All On','','','','0000-00-00 00:00:00',NULL),
(2,'*31','OV500','CFAD','Call Forward All Off','','','','0000-00-00 00:00:00',NULL),
(3,'*32','OV500','CFNAE','Call Forward No Answer On','','','','0000-00-00 00:00:00',NULL),
(4,'*33','OV500','CFNAD','Call Forward No Answer Off','','','','0000-00-00 00:00:00',NULL),
(5,'*34','OV500','CFBE','Call Forward Busy On','','','','0000-00-00 00:00:00',NULL),
(6,'*35','OV500','CFBD','Call Forward Busy Off','','','','0000-00-00 00:00:00',NULL),
(7,'*10','OV500','UVMA','Secure Voice Mail Access (Code + PIN) from your extension','','','','0000-00-00 00:00:00',NULL),
(8,'*11','OV500','DNDE','Do Not Disturb On','','','','0000-00-00 00:00:00',NULL),
(9,'*12','OV500','DNDD','Do Not Disturb Off','','','','0000-00-00 00:00:00',NULL),
(10,'*88','OV500','PICKUP','Call Pick Up (Code + Extension Number)','','','','0000-00-00 00:00:00',NULL),
(11,'*21','OV500','CCLOGIN','Call Center Log In','','','','0000-00-00 00:00:00',NULL),
(12,'*22','OV500','CCLOGOUT','Call Center Log Out','','','','0000-00-00 00:00:00',NULL),
(13,'*23','OV500','CCPAUSE','Agent Pause','','','','0000-00-00 00:00:00',NULL),
(14,'*24','OV500','CCUNPAUSE','Agent Un-Pause','','','','0000-00-00 00:00:00',NULL),
(15,'*89','OV500','SPY','Call Barge (Code + Extension Number)','','','','0000-00-00 00:00:00',NULL),
(16,'*98','OV500','VMANOPAS','Fast Voice Mail Access (Code only) from your extension','','','','0000-00-00 00:00:00',NULL),
(17,'*15','OV500','RECORD','Record Announcement','','','','0000-00-00 00:00:00',NULL),
(18,'*5','OV500','ONDEMAND','Recording Start / Stop','','','','0000-00-00 00:00:00',NULL),
(19,'*13','OV500','RPAUSE','Recording Pause','','','','0000-00-00 00:00:00',NULL),
(20,'*14','OV500','RUNPAUSE','Recording Un-Pause','','','','0000-00-00 00:00:00',NULL);
/*!40000 ALTER TABLE `feature_code` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `feature_code_default`
--

DROP TABLE IF EXISTS `feature_code_default`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `feature_code_default` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `feature_desc` varchar(150) NOT NULL,
  `dialcode` varchar(5) NOT NULL,
  `feature_id` varchar(10) NOT NULL,
  `group` int(11) DEFAULT NULL,
  `help_text` text DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=60 DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci ROW_FORMAT=COMPACT;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `feature_code_default`
--

LOCK TABLES `feature_code_default` WRITE;
/*!40000 ALTER TABLE `feature_code_default` DISABLE KEYS */;
INSERT INTO `feature_code_default` VALUES
(20,'Secure Voice Mail Access (Code + PIN) from your extension','*10','UVMA',1,'NULL'),
(22,'Do Not Disturb On','*11','DNDE',5,'NULL'),
(26,'Do Not Disturb Off','*12','DNDD',5,'NULL'),
(28,'Call Pick Up (Code + Extension Number)','*88','PICKUP',7,'NULL'),
(30,'Call Center Log In','*21','CCLOGIN',6,'NULL'),
(32,'Call Center Log Out','*22','CCLOGOUT',6,'NULL'),
(34,'Agent Pause','*23','CCPAUSE',6,'NULL'),
(36,'Agent Un-Pause','*24','CCUNPAUSE',6,'NULL'),
(44,'Call Barge (Code + Extension Number)','*89','SPY',8,'NULL'),
(47,'Fast Voice Mail Access (Code only) from your extension','*98','VMANOPAS',4,'NULL'),
(50,'Record Announcement','*15','RECORD',3,NULL),
(53,'Recording Start / Stop','*5','ONDEMAND',2,'NULL'),
(56,'Recording Pause','*13','RPAUSE',2,'NULL'),
(59,'Recording Un-Pause','*14','RUNPAUSE',2,'NULL');
/*!40000 ALTER TABLE `feature_code_default` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `followme`
--

DROP TABLE IF EXISTS `followme`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `followme` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `account_id` varchar(30) DEFAULT NULL,
  `extension_id` varchar(30) DEFAULT NULL,
  `dst_extension_no` varchar(30) DEFAULT NULL,
  `calldelay` int(11) DEFAULT 15,
  `timeout` int(11) DEFAULT 15,
  `dialorder` int(11) DEFAULT 1,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci ROW_FORMAT=COMPACT;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `followme`
--

LOCK TABLES `followme` WRITE;
/*!40000 ALTER TABLE `followme` DISABLE KEYS */;
/*!40000 ALTER TABLE `followme` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `forgot_password_otp`
--

DROP TABLE IF EXISTS `forgot_password_otp`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `forgot_password_otp` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` varchar(30) NOT NULL,
  `emailaddress` varchar(150) NOT NULL,
  `otp` varchar(10) NOT NULL,
  `is_verified` enum('1','0') NOT NULL DEFAULT '0',
  `created_dt` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci ROW_FORMAT=COMPACT;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `forgot_password_otp`
--

LOCK TABLES `forgot_password_otp` WRITE;
/*!40000 ALTER TABLE `forgot_password_otp` DISABLE KEYS */;
/*!40000 ALTER TABLE `forgot_password_otp` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `group_data`
--

DROP TABLE IF EXISTS `group_data`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `group_data` (
  `hostname` varchar(255) DEFAULT NULL,
  `groupname` varchar(255) DEFAULT NULL,
  `url` varchar(255) DEFAULT NULL,
  KEY `gd_groupname` (`groupname`) USING BTREE,
  KEY `gd_url` (`url`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci ROW_FORMAT=COMPACT;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `group_data`
--

LOCK TABLES `group_data` WRITE;
/*!40000 ALTER TABLE `group_data` DISABLE KEYS */;
/*!40000 ALTER TABLE `group_data` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `hangupcause`
--

DROP TABLE IF EXISTS `hangupcause`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `hangupcause` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `sip` int(11) DEFAULT NULL,
  `q850` int(11) DEFAULT NULL,
  `cause` varchar(50) DEFAULT NULL,
  `notes` varchar(500) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci ROW_FORMAT=COMPACT;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `hangupcause`
--

LOCK TABLES `hangupcause` WRITE;
/*!40000 ALTER TABLE `hangupcause` DISABLE KEYS */;
/*!40000 ALTER TABLE `hangupcause` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `htable`
--

DROP TABLE IF EXISTS `htable`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `htable` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `key_name` varchar(64) NOT NULL DEFAULT '',
  `key_type` int(11) NOT NULL DEFAULT 0,
  `value_type` int(11) NOT NULL DEFAULT 0,
  `key_value` varchar(128) NOT NULL DEFAULT '',
  `expires` int(11) NOT NULL DEFAULT 0,
  `ip` varchar(30) DEFAULT NULL,
  `htime` datetime DEFAULT NULL,
  `custom_field` varchar(128) NOT NULL DEFAULT '',
  `email_status` int(11) DEFAULT 0,
  `serverid` int(11) DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `ov_htable_keyname_ind` (`key_name`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci ROW_FORMAT=COMPACT;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `htable`
--

LOCK TABLES `htable` WRITE;
/*!40000 ALTER TABLE `htable` DISABLE KEYS */;
/*!40000 ALTER TABLE `htable` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `htabledump`
--

DROP TABLE IF EXISTS `htabledump`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `htabledump` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `key_name` varchar(64) NOT NULL DEFAULT '',
  `key_type` int(11) NOT NULL DEFAULT 0,
  `value_type` int(11) NOT NULL DEFAULT 0,
  `key_value` varchar(128) NOT NULL DEFAULT '',
  `expires` int(11) NOT NULL DEFAULT 0,
  `ip` varchar(30) DEFAULT NULL,
  `htime` datetime DEFAULT NULL,
  `custom_field` varchar(128) NOT NULL DEFAULT '',
  `email_status` int(11) DEFAULT 0,
  `serverid` int(11) DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `htable_keyname_ind` (`key_name`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci ROW_FORMAT=COMPACT;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `htabledump`
--

LOCK TABLES `htabledump` WRITE;
/*!40000 ALTER TABLE `htabledump` DISABLE KEYS */;
/*!40000 ALTER TABLE `htabledump` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ip_blocker`
--

DROP TABLE IF EXISTS `ip_blocker`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `ip_blocker` (
  `ip_id` int(11) NOT NULL AUTO_INCREMENT,
  `checking_type` enum('allow','disallow','inactive') NOT NULL,
  PRIMARY KEY (`ip_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci ROW_FORMAT=COMPACT;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ip_blocker`
--

LOCK TABLES `ip_blocker` WRITE;
/*!40000 ALTER TABLE `ip_blocker` DISABLE KEYS */;
/*!40000 ALTER TABLE `ip_blocker` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ip_blocker_details`
--

DROP TABLE IF EXISTS `ip_blocker_details`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `ip_blocker_details` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `ip` varchar(50) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci ROW_FORMAT=COMPACT;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ip_blocker_details`
--

LOCK TABLES `ip_blocker_details` WRITE;
/*!40000 ALTER TABLE `ip_blocker_details` DISABLE KEYS */;
/*!40000 ALTER TABLE `ip_blocker_details` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ivroptions`
--

DROP TABLE IF EXISTS `ivroptions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `ivroptions` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `ivr_id` varchar(30) NOT NULL,
  `account_id` varchar(30) DEFAULT NULL,
  `dtmf_option` int(10) unsigned NOT NULL,
  `dtmf_route` varchar(30) DEFAULT 'HANGUP',
  `dtmf_route_endpoint` varchar(50) DEFAULT NULL,
  `created_by` varchar(30) NOT NULL,
  `created_dt` datetime NOT NULL,
  `modified_by` varchar(30) DEFAULT NULL,
  `modified_dt` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `ivr_id` (`ivr_id`) USING BTREE,
  KEY `account_id` (`account_id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci ROW_FORMAT=COMPACT;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ivroptions`
--

LOCK TABLES `ivroptions` WRITE;
/*!40000 ALTER TABLE `ivroptions` DISABLE KEYS */;
/*!40000 ALTER TABLE `ivroptions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ivrs`
--

DROP TABLE IF EXISTS `ivrs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `ivrs` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `ivr_id` varchar(30) NOT NULL,
  `account_id` varchar(30) NOT NULL,
  `ivr_no` int(11) DEFAULT NULL,
  `ivr_name` varchar(200) NOT NULL,
  `ivr_description` text DEFAULT NULL,
  `status_id` enum('1','0') DEFAULT NULL,
  `welcome_voice` varchar(50) NOT NULL,
  `invalid_input_voice` varchar(50) DEFAULT NULL,
  `retries` int(10) unsigned DEFAULT 3,
  `input_timeout` int(10) unsigned DEFAULT 10,
  `timeout_route` varchar(30) DEFAULT 'HANGUP',
  `timeout_route_endpoint` varchar(50) DEFAULT NULL,
  `timeout_voice` varchar(50) DEFAULT NULL,
  `option_length` int(11) NOT NULL DEFAULT 1,
  `subscription_id` varchar(30) DEFAULT 'DEFAULT',
  `created_by` varchar(30) NOT NULL,
  `created_dt` datetime NOT NULL,
  `modified_by` varchar(30) DEFAULT NULL,
  `modified_dt` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `ivr_id` (`ivr_id`) USING BTREE,
  KEY `account` (`account_id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci ROW_FORMAT=COMPACT;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ivrs`
--

LOCK TABLES `ivrs` WRITE;
/*!40000 ALTER TABLE `ivrs` DISABLE KEYS */;
/*!40000 ALTER TABLE `ivrs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `languages`
--

DROP TABLE IF EXISTS `languages`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `languages` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `language` varchar(50) DEFAULT NULL,
  `shortcode` varchar(30) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci ROW_FORMAT=COMPACT;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `languages`
--

LOCK TABLES `languages` WRITE;
/*!40000 ALTER TABLE `languages` DISABLE KEYS */;
/*!40000 ALTER TABLE `languages` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `limit_data`
--

DROP TABLE IF EXISTS `limit_data`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `limit_data` (
  `hostname` varchar(255) DEFAULT NULL,
  `realm` varchar(255) DEFAULT NULL,
  `id` varchar(255) DEFAULT NULL,
  `uuid` varchar(255) DEFAULT NULL,
  KEY `ld_hostname` (`hostname`) USING BTREE,
  KEY `ld_uuid` (`uuid`) USING BTREE,
  KEY `ld_realm` (`realm`) USING BTREE,
  KEY `ld_id` (`id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci ROW_FORMAT=COMPACT;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `limit_data`
--

LOCK TABLES `limit_data` WRITE;
/*!40000 ALTER TABLE `limit_data` DISABLE KEYS */;
/*!40000 ALTER TABLE `limit_data` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `livecalls`
--

DROP TABLE IF EXISTS `livecalls`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `livecalls` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `carrier_ratecard_id` varchar(30) DEFAULT NULL,
  `carrier_tariff_id` varchar(30) DEFAULT NULL,
  `carrier_prefix` varchar(15) DEFAULT NULL,
  `carrier_destination` varchar(50) DEFAULT NULL,
  `carrier_rate` float(10,6) DEFAULT NULL,
  `carrier_id` varchar(30) DEFAULT NULL,
  `carrier_name` varchar(30) DEFAULT NULL,
  `carrier_ipaddress` varchar(30) DEFAULT NULL,
  `carrier_ipaddress_name` varchar(30) DEFAULT NULL,
  `carrier_currency_id` varchar(10) DEFAULT 'USD',
  `carrier_src_caller` varchar(30) DEFAULT NULL,
  `carrier_src_callee` varchar(30) DEFAULT NULL,
  `carrier_dst_caller` varchar(30) DEFAULT NULL,
  `carrier_dst_callee` varchar(30) DEFAULT NULL,
  `dialplan_id` varchar(30) DEFAULT NULL,
  `customer_account_id` varchar(30) DEFAULT NULL,
  `customer_tariff_id` varchar(30) DEFAULT NULL,
  `customer_currency_id` varchar(10) DEFAULT 'USD',
  `customer_ipaddress` varchar(30) DEFAULT NULL,
  `customer_ratecard_id` varchar(30) DEFAULT NULL,
  `customer_prefix` varchar(15) DEFAULT NULL,
  `customer_destination` varchar(50) DEFAULT NULL,
  `customer_rate` float(10,6) DEFAULT NULL,
  `customer_src_caller` varchar(30) DEFAULT NULL,
  `customer_src_callee` varchar(30) DEFAULT NULL,
  `customer_src_ip` varchar(30) DEFAULT NULL,
  `reseller1_account_id` varchar(30) DEFAULT NULL,
  `reseller1_tariff_id` varchar(30) DEFAULT NULL,
  `reseller1_ratecard_id` varchar(30) DEFAULT NULL,
  `reseller1_prefix` varchar(15) DEFAULT NULL,
  `reseller1_destination` varchar(50) DEFAULT NULL,
  `reseller1_rate` float(10,6) DEFAULT NULL,
  `reseller2_account_id` varchar(30) DEFAULT NULL,
  `reseller2_tariff_id` varchar(30) DEFAULT NULL,
  `reseller2_ratecard_id` varchar(30) DEFAULT NULL,
  `reseller2_prefix` varchar(15) DEFAULT NULL,
  `reseller2_destination` varchar(50) DEFAULT NULL,
  `reseller2_rate` float(10,6) DEFAULT NULL,
  `reseller3_account_id` varchar(30) DEFAULT NULL,
  `reseller3_tariff_id` varchar(30) DEFAULT NULL,
  `reseller3_ratecard_id` varchar(50) DEFAULT NULL,
  `reseller3_prefix` varchar(50) DEFAULT NULL,
  `reseller3_destination` varchar(50) DEFAULT NULL,
  `reseller3_rate` float(10,6) DEFAULT NULL,
  `start_time` timestamp NULL DEFAULT NULL,
  `answer_time` timestamp NULL DEFAULT NULL,
  `end_time` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp(),
  `fscause` varchar(50) DEFAULT NULL,
  `Q850CODE` varchar(30) DEFAULT NULL,
  `SIPCODE` varchar(30) DEFAULT NULL,
  `caller_callid` varchar(150) DEFAULT NULL,
  `callee_callid` varchar(150) DEFAULT NULL,
  `common_uuid` varchar(150) DEFAULT NULL,
  `fs_host` varchar(30) DEFAULT NULL,
  `in_useragent` varchar(150) DEFAULT NULL,
  `callstatus` varchar(20) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `customer_company` varchar(150) DEFAULT NULL,
  `loadbalancer` varchar(30) DEFAULT NULL,
  `call_flow` enum('PSTN','DID','EXTEN') DEFAULT 'PSTN',
  `campaign_name` varchar(30) DEFAULT NULL,
  `campaign_id` varchar(30) DEFAULT NULL,
  `buyer_number` varchar(30) DEFAULT NULL,
  `buyer_name` varchar(30) DEFAULT NULL,
  `did_number` varchar(30) DEFAULT NULL,
  `calltype` varchar(30) DEFAULT NULL,
  `src_extension_id` varchar(30) DEFAULT NULL,
  `src_extension_no` varchar(30) DEFAULT NULL,
  `src_extension_name` varchar(30) DEFAULT NULL,
  `dst_extension_id` varchar(30) DEFAULT NULL,
  `dst_app` varchar(30) DEFAULT NULL,
  `dst_app_number` varchar(30) DEFAULT NULL,
  `dst_app_name` varchar(30) DEFAULT NULL,
  `endpoint_app` varchar(30) DEFAULT NULL,
  `endpoint_name` varchar(30) DEFAULT NULL,
  `endpoint_number` varchar(30) DEFAULT NULL,
  `endpoint_extension_no` varchar(30) DEFAULT NULL,
  `endpoint_uuid` varchar(500) DEFAULT NULL,
  `endpoint_extension_id` varchar(30) DEFAULT NULL,
  `member_uuid` varchar(500) DEFAULT NULL,
  `hangup` int(11) DEFAULT 0,
  `ext_all_ring` varchar(500) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `carrier_destination` (`carrier_destination`) USING BTREE,
  KEY `carrier_gateway_ipaddress` (`carrier_ipaddress`) USING BTREE,
  KEY `carrier_carrier_id_name` (`carrier_id`) USING BTREE,
  KEY `user_ipaddress` (`customer_ipaddress`) USING BTREE,
  KEY `user_account_id` (`customer_account_id`) USING BTREE,
  KEY `common_uuid` (`common_uuid`) USING BTREE,
  KEY `live_call_status` (`callstatus`) USING BTREE,
  KEY `reseller1_account_id` (`reseller1_account_id`) USING BTREE,
  KEY `reseller2_account_id` (`reseller2_account_id`) USING BTREE,
  KEY `reseller3_account_id` (`reseller3_account_id`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=67 DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci ROW_FORMAT=COMPACT;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `livecalls`
--

LOCK TABLES `livecalls` WRITE;
/*!40000 ALTER TABLE `livecalls` DISABLE KEYS */;
/*!40000 ALTER TABLE `livecalls` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `members`
--

DROP TABLE IF EXISTS `members`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `members` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `queue` varchar(255) DEFAULT NULL,
  `instance_id` varchar(255) DEFAULT NULL,
  `uuid` varchar(255) NOT NULL DEFAULT '',
  `session_uuid` varchar(255) NOT NULL DEFAULT '',
  `cid_number` varchar(255) DEFAULT NULL,
  `cid_name` varchar(255) DEFAULT NULL,
  `account_id` varchar(30) DEFAULT NULL,
  `did_number` varchar(30) DEFAULT NULL,
  `extension_id` varchar(30) DEFAULT NULL,
  `calltype` varchar(30) NOT NULL DEFAULT 'QUEUE',
  `system_epoch` int(11) NOT NULL DEFAULT 0,
  `joined_epoch` int(11) NOT NULL DEFAULT 0,
  `rejoined_epoch` int(11) NOT NULL DEFAULT 0,
  `bridge_epoch` int(11) NOT NULL DEFAULT 0,
  `abandoned_epoch` int(11) NOT NULL DEFAULT 0,
  `endtime_epoch` int(11) DEFAULT NULL,
  `base_score` int(11) NOT NULL DEFAULT 0,
  `skill_score` int(11) NOT NULL DEFAULT 0,
  `serving_agent` varchar(255) DEFAULT NULL,
  `serving_system` varchar(255) DEFAULT NULL,
  `recordning_file` varchar(255) DEFAULT NULL,
  `state` varchar(255) DEFAULT NULL,
  `agent_code` varchar(30) DEFAULT NULL,
  `agent_displayname` varchar(255) DEFAULT NULL,
  `extension_no` varchar(30) DEFAULT NULL,
  `common_uuid` varchar(300) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=36 DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci ROW_FORMAT=COMPACT;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `members`
--

LOCK TABLES `members` WRITE;
/*!40000 ALTER TABLE `members` DISABLE KEYS */;
/*!40000 ALTER TABLE `members` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `members_log`
--

DROP TABLE IF EXISTS `members_log`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `members_log` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `queue` varchar(255) DEFAULT NULL,
  `instance_id` varchar(255) DEFAULT NULL,
  `uuid` varchar(255) NOT NULL DEFAULT '',
  `session_uuid` varchar(255) NOT NULL DEFAULT '',
  `cid_number` varchar(255) DEFAULT NULL,
  `cid_name` varchar(255) DEFAULT NULL,
  `account_id` varchar(30) DEFAULT NULL,
  `did_number` varchar(30) DEFAULT NULL,
  `extension_id` varchar(30) DEFAULT NULL,
  `calltype` varchar(30) NOT NULL DEFAULT 'QUEUE',
  `system_epoch` int(11) NOT NULL DEFAULT 0,
  `joined_epoch` int(11) NOT NULL DEFAULT 0,
  `rejoined_epoch` int(11) NOT NULL DEFAULT 0,
  `bridge_epoch` int(11) NOT NULL DEFAULT 0,
  `abandoned_epoch` int(11) NOT NULL DEFAULT 0,
  `endtime_epoch` int(11) DEFAULT NULL,
  `base_score` int(11) NOT NULL DEFAULT 0,
  `skill_score` int(11) NOT NULL DEFAULT 0,
  `serving_agent` varchar(255) DEFAULT NULL,
  `serving_system` varchar(255) DEFAULT NULL,
  `recordning_file` varchar(255) DEFAULT NULL,
  `state` varchar(255) DEFAULT NULL,
  `agent_code` varchar(30) DEFAULT NULL,
  `agent_displayname` varchar(255) DEFAULT NULL,
  `extension_no` varchar(30) DEFAULT NULL,
  `common_uuid` varchar(300) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=39 DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci ROW_FORMAT=COMPACT;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `members_log`
--

LOCK TABLES `members_log` WRITE;
/*!40000 ALTER TABLE `members_log` DISABLE KEYS */;
INSERT INTO `members_log` VALUES
(1,'QDEMOQUEU9TQE0NVF400','single_box','f829f4d1-e9db-438b-95c6-a3595b1e1e77','fcfb5b33-d536-4d89-be85-6d2795c9316a','1001','C18tA02m','OV500','(NULL)','EOVHEY7900','QUEUE',1759503054,1759503054,0,1759503090,0,1759503094,0,0,'C18tA02m','single_box','(NULL)','Answered','1001','Demo',NULL,NULL),
(2,'QDEMOQUEU9TQE0NVF400','single_box','3731b492-38d8-4b16-be8e-f9f35520ca77','68ca7393-83ab-420e-9ebf-3a1943887e1b','1001','C18tA02m','OV500','(NULL)','EOVHEY7900','QUEUE',1759503114,1759503114,0,1759503117,0,1759503122,0,0,'C18tA02m','single_box','(NULL)','Answered','1001','Demo',NULL,NULL),
(3,'QDEMOQUEU9TQE0NVF400','single_box','48baa39e-dc00-4167-b39c-7124e70659eb','283832e7-2a3f-4937-8f93-82e97aa15f70','1001','C18tA02m','OV500','(NULL)','EOVHEY7900','QUEUE',1759503431,1759503431,0,1759503433,0,1759503436,0,0,'C18tA02m','single_box','(NULL)','Answered','1001','Demo',NULL,NULL),
(4,'QDEMOQUEU9TQE0NVF400','single_box','1e7c5d89-b569-4334-85f4-c3a374100a9f','388bc45a-17ae-4394-8178-3dd17a1fbb21','1001','C18tA02m','OV500','(NULL)','EOVHEY7900','QUEUE',1759503529,1759503529,0,1759503531,0,1759503535,0,0,'C18tA02m','single_box','(NULL)','Answered','1001','Demo',NULL,NULL),
(5,'QDEMOQUEU9TQE0NVF400','single_box','507827dd-0cb9-4f48-8ec8-a339d8ee2d9d','4435b142-4659-4868-b1eb-14fa4ea77202','1001','C18tA02m','OV500','(NULL)','EOVHEY7900','QUEUE',1759503569,1759503569,0,1759503576,0,1759503584,0,0,'C18tA02m','single_box','(NULL)','Answered','1001','Demo',NULL,NULL),
(6,'QDEMOQUEU9TQE0NVF400','single_box','fb32dadc-824a-406d-b468-f7b95c4eb3cd','69db2e78-1287-4c63-a8da-ebb5f2e1a5ae','1001','C18tA02m','OV500','(NULL)','EOVHEY7900','QUEUE',1759504650,1759504650,0,1759504652,0,1759504653,0,0,'C18tA02m','single_box','(NULL)','Answered','1001','Demo',NULL,NULL),
(7,'QDEMOQUEU9TQE0NVF400','single_box','931841ab-36b9-49e2-be8d-6e2da8232008','0a6bc750-fc8e-4c66-91d6-1009ad60bc87','1001','C18tA02m','OV500','(NULL)','EOVHEY7900','QUEUE',1759504821,1759504821,0,1759504823,0,1759504824,0,0,'C18tA02m','single_box','(NULL)','Answered','1001','Demo',NULL,NULL),
(8,'QDEMOQUEU9TQE0NVF400','single_box','e1d5d8f7-d53a-4d45-a48a-0460fd6e2666','7e83f856-de2c-40d9-b60f-baa0ec64ca13','1001','C18tA02m','OV500','(NULL)','EOVHEY7900','QUEUE',1759504830,1759504830,0,1759504837,0,1759504839,0,0,'C18tA02m','single_box','(NULL)','Answered','1001','Demo',NULL,NULL),
(9,'QDEMOQUEU9TQE0NVF400','single_box','70f8431c-53a9-434d-b869-568b14cdc6a5','28c086da-a327-4489-aab6-0a3cb2e3aedb','1001','C18tA02m','OV500','(NULL)','EOVHEY7900','QUEUE',1759504957,1759504957,0,1759504959,0,1759504964,0,0,'C18tA02m','single_box','(NULL)','Answered','1001','Demo',NULL,NULL),
(10,'QDEMOQUEU9TQE0NVF400','single_box','7676953c-7cd1-4d73-b7c2-fe286bad4774','','1001','C18tA02m','(NULL)','(NULL)','(NULL)','QUEUE',1759505599,1759505599,0,0,1759505626,1759505626,0,0,'ring-all','','(NULL)','Abandoned','(NULL)','(NULL)',NULL,NULL),
(11,'QDEMOQUEU9TQE0NVF400','single_box','4264a7d5-d908-46b6-a509-c65cdd2bd2d3','','1001','C18tA02m','(NULL)','(NULL)','(NULL)','QUEUE',1759505628,1759505628,0,0,1759505688,1759505688,0,0,'ring-all','','(NULL)','Abandoned','(NULL)','(NULL)',NULL,NULL),
(12,'QDEMOQUEU9TQE0NVF400','single_box','f7025353-16de-43e0-84a0-38a9f1c52bfb','','1001','C18tA02m','(NULL)','(NULL)','(NULL)','QUEUE',1759505628,1759505688,0,0,1759505748,1759505748,60,0,'ring-all','','(NULL)','Abandoned','(NULL)','(NULL)',NULL,NULL),
(13,'QDEMOQUEU9TQE0NVF400','single_box','f76fb8be-4c2c-4df9-b9a6-56a01f56b819','','1001','C18tA02m','(NULL)','(NULL)','(NULL)','QUEUE',1759505628,1759505748,0,0,1759505808,1759505808,120,0,'ring-all','','(NULL)','Abandoned','(NULL)','(NULL)',NULL,NULL),
(14,'QDEMOQUEU9TQE0NVF400','single_box','d304c882-8a90-4865-afd7-5f8afcb1f2eb','','1001','C18tA02m','(NULL)','(NULL)','(NULL)','QUEUE',1759505628,1759505808,0,0,1759505868,1759505868,180,0,'ring-all','','(NULL)','Abandoned','(NULL)','(NULL)',NULL,NULL),
(15,'QDEMOQUEU9TQE0NVF400','single_box','c56e73e4-09b6-49d9-be3c-5145d187951c','','1001','C18tA02m','(NULL)','(NULL)','(NULL)','QUEUE',1759505628,1759505868,0,0,1759505892,1759505892,240,0,'ring-all','','(NULL)','Abandoned','(NULL)','(NULL)',NULL,NULL),
(16,'QDEMOQUEU9TQE0NVF400','single_box','14206138-b042-4217-9cb7-15d0d42ff309','','1001','C18tA02m','(NULL)','(NULL)','(NULL)','QUEUE',1759506446,1759506446,0,0,1759506502,1759506502,0,0,'ring-all','','(NULL)','Abandoned','(NULL)','(NULL)',NULL,NULL),
(17,'QDEMOQUEU9TQE0NVF400','single_box','e9fff82f-106e-48f3-9f11-dec81a0293c6','','1001','C18tA02m','(NULL)','(NULL)','(NULL)','QUEUE',1759506546,1759506546,0,0,1759506574,1759506574,0,0,'ring-all','','(NULL)','Abandoned','(NULL)','(NULL)',NULL,NULL),
(18,'QDEMOQUEU9TQE0NVF400','single_box','62b92765-1746-4d04-a04e-c997a6d90be9','','1001','C18tA02m','(NULL)','(NULL)','(NULL)','QUEUE',1759506637,1759506637,0,0,1759506697,1759506697,0,0,'ring-all','','(NULL)','Abandoned','(NULL)','(NULL)',NULL,NULL),
(19,'QDEMOQUEU9TQE0NVF400','single_box','8a04786e-b9d6-42fb-a231-f083270da3c9','','1001','C18tA02m','(NULL)','(NULL)','(NULL)','QUEUE',1759506637,1759506697,0,0,1759506718,1759506718,60,0,'ring-all','','(NULL)','Abandoned','(NULL)','(NULL)',NULL,NULL),
(20,'QDEMOQUEU9TQE0NVF400','single_box','bd7aa6ea-8471-4edd-af79-1d6be521d1ac','740a9bcd-0687-4d32-9212-6f0d99db85b6','1001','C18tA02m','OV500','(NULL)','EOVHEY7900','QUEUE',1759506721,1759506721,0,1759506743,0,1759506746,0,0,'C18tA02m','single_box','(NULL)','Answered','1001','Demo',NULL,NULL),
(21,'QDEMOQUEU9TQE0NVF400','single_box','dbc7af2e-8922-4574-b9ef-2040cff06935','','1001','C18tA02m','(NULL)','(NULL)','(NULL)','QUEUE',1759506833,1759506833,0,0,1759506893,1759506893,0,0,'ring-all','','(NULL)','Abandoned','(NULL)','(NULL)',NULL,NULL),
(22,'QDEMOQUEU9TQE0NVF400','single_box','caf92b22-c73d-45e7-88e5-55393d4853d2','ae2c2fea-24df-41fb-be8e-38551bb9239e','1001','C18tA02m','OV500','(NULL)','EOVHEY7900','QUEUE',1759506833,1759506893,0,1759506925,0,1759506926,60,0,'C18tA02m','single_box','(NULL)','Answered','1001','Demo',NULL,NULL),
(23,'QDEMOQUEU9TQE0NVF400','single_box','7ece7a9c-ebf6-4f5e-8a95-cb81af68789d','ea36667c-8321-42c8-be7f-984ea33443bf','1001','C18tA02m','OV500','(NULL)','EOVHEY7900','QUEUE',1759507819,1759507819,0,1759507838,0,1759507841,0,0,'C18tA02m','single_box','(NULL)','Answered','1001','Demo',NULL,NULL),
(24,'QDEMOQUEU9TQE0NVF400','single_box','e5601121-4f7e-4404-ac72-29a37cada91a','fc30c345-d906-43c9-8ab4-376d38ba8676','1001','C18tA02m','OV500','(NULL)','EOVHEY7900','QUEUE',1759508182,1759508182,0,1759508184,0,1759508187,0,0,'C18tA02m','single_box','(NULL)','Answered','1001','Demo',NULL,NULL),
(25,'QDEMOQUEU9TQE0NVF400','single_box','febc5f01-d6d5-4f25-be2f-9c905c46b3d0','06239030-873d-4da8-9593-f9e6dd6abd11','1001','C18tA02m','OV500','(NULL)','EOVHEY7900','QUEUE',1759508212,1759508212,0,1759508215,0,1759508216,0,0,'C18tA02m','single_box','(NULL)','Answered','1001','Demo',NULL,NULL),
(26,'QDEMOQUEU9TQE0NVF400','single_box','5c975efb-0e86-41fa-b368-63785a440dfc','61d4b835-7eca-4993-99bb-a78edf95699c','1001','C18tA02m','OV500','(NULL)','EOVHEY7900','QUEUE',1759508240,1759508240,0,1759508242,0,1759508246,0,0,'C18tA02m','single_box','(NULL)','Answered','1001','Demo',NULL,NULL),
(27,'QDEMOQUEU9TQE0NVF400','single_box','a472223f-5f22-4fab-9faa-22a38c9506eb','7ad551ab-c3bd-410e-8892-b9b118cdbfdf','1001','C18tA02m','OV500','(NULL)','EOVHEY7900','QUEUE',1759508272,1759508272,0,1759508274,0,1759508278,0,0,'C18tA02m','single_box','(NULL)','Answered','1001','Demo',NULL,NULL),
(28,'QDEMOQUEU9TQE0NVF400','single_box','9b8d9dfa-8d03-429d-a8a7-00cbd0467089','48615bfc-bcba-4dd2-8745-a7bc5609998f','1001','C18tA02m','OV500','(NULL)','EOVHEY7900','QUEUE',1759508280,1759508280,0,1759508293,0,1759508298,0,0,'C18tA02m','single_box','(NULL)','Answered','1001','Demo',NULL,NULL),
(29,'QDEMOQUEU9TQE0NVF400','single_box','1ed4af00-a430-4ad1-93fc-57cb33a222bd','2a14a3c0-d94f-41a5-8e1a-730f7b1b60b8','1001','C18tA02m','OV500','(NULL)','EOVHEY7900','QUEUE',1759508299,1759508299,0,1759508315,0,1759508318,0,0,'C18tA02m','single_box','(NULL)','Answered','1001','Demo',NULL,NULL),
(30,'QDEMOQUEU9TQE0NVF400','single_box','cc2f2622-172a-4b49-b454-da221e67ef49','f4c9b9c6-6300-48e6-9e78-b3f654fd58e9','1001','C18tA02m','OV500','(NULL)','EOVHEY7900','QUEUE',1759508336,1759508336,0,1759508338,0,1759508344,0,0,'C18tA02m','single_box','(NULL)','Answered','1001','Demo',NULL,NULL),
(31,'QDEMOQUEU9TQE0NVF400','single_box','2a543da6-33f0-4596-9d84-714f195285c3','','1001','C18tA02m','(NULL)','(NULL)','(NULL)','QUEUE',1759508381,1759508381,0,0,1759508406,1759508406,0,0,'ring-all','','(NULL)','Abandoned','(NULL)','(NULL)',NULL,NULL),
(32,'QDEMOQUEU9TQE0NVF400','single_box','9c1eb1bf-8459-49b1-811a-8329bbdaeac2','','1001','C18tA02m','(NULL)','(NULL)','(NULL)','QUEUE',1759508408,1759508408,0,0,1759508455,1759508455,0,0,'','','(NULL)','Abandoned','(NULL)','(NULL)',NULL,NULL),
(33,'QDEMOQUEU9TQE0NVF400','single_box','7fcd5e3f-fc85-4c69-8ac0-d7b04abfcb26','','1001','C18tA02m','(NULL)','(NULL)','(NULL)','QUEUE',1759508456,1759508456,0,0,1759508458,1759508458,0,0,'','','(NULL)','Abandoned','(NULL)','(NULL)',NULL,NULL),
(34,'QDEMOQUEU9TQE0NVF400','single_box','971272f3-8564-4777-9955-be3b60eccd1f','d86383f2-e5ee-4f4f-8bd7-b7c97b734647','1001','C18tA02m','OV500','(NULL)','EOVHEY7900','QUEUE',1759508651,1759508651,0,1759508655,0,1759508659,0,0,'C18tA02m','single_box','(NULL)','Answered','1001','Demo',NULL,NULL),
(35,'QDEMOQUEU9TQE0NVF400','single_box','e0e35e63-2b17-4399-88af-2b6be7eb0ad6','55177b2d-464d-4b48-87f1-e78036c1ab55','1001','C18tA02m','OV500','(NULL)','EOVHEY7900','QUEUE',1759508663,1759508663,0,1759508672,0,1759508674,0,0,'C18tA02m','single_box','(NULL)','Answered','1001','Demo',NULL,NULL),
(36,'QDEMOQUEU9TQE0NVF400','single_box','28840ad8-ae87-47c8-9822-43175cb02756','','1001','C18tA02m','(NULL)','(NULL)','(NULL)','QUEUE',1759508684,1759508684,0,0,1759508744,1759508744,0,0,'','','(NULL)','Abandoned','(NULL)','(NULL)',NULL,NULL),
(37,'QDEMOQUEU9TQE0NVF400','single_box','8e27b46d-2ceb-4da2-a7a6-ab1ccae9d995','','1001','C18tA02m','(NULL)','(NULL)','(NULL)','QUEUE',1759508684,1759508744,0,0,1759508749,1759508749,60,0,'','','(NULL)','Abandoned','(NULL)','(NULL)',NULL,NULL),
(38,'QDEMOQUEU9TQE0NVF400','single_box','3e134e6c-448a-434b-b057-af71879d038b','','1001','C18tA02m','(NULL)','(NULL)','(NULL)','QUEUE',1759508752,1759508752,0,0,1759508801,1759508801,0,0,'','','(NULL)','Abandoned','(NULL)','(NULL)',NULL,NULL);
/*!40000 ALTER TABLE `members_log` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `menus`
--

DROP TABLE IF EXISTS `menus`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `menus` (
  `menu_id` int(11) NOT NULL AUTO_INCREMENT,
  `account_id` varchar(50) NOT NULL,
  `menu` text NOT NULL,
  `update_by` varchar(50) NOT NULL,
  `update_dt` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`menu_id`),
  UNIQUE KEY `account_id` (`account_id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci ROW_FORMAT=COMPACT;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `menus`
--

LOCK TABLES `menus` WRITE;
/*!40000 ALTER TABLE `menus` DISABLE KEYS */;
/*!40000 ALTER TABLE `menus` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Temporary table structure for view `new_did`
--

DROP TABLE IF EXISTS `new_did`;
/*!50001 DROP VIEW IF EXISTS `new_did`*/;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8mb4;
/*!50001 CREATE VIEW `new_did` AS SELECT
 1 AS `did_id`,
  1 AS `did_number`,
  1 AS `did_status`,
  1 AS `carrier_id`,
  1 AS `account_id`,
  1 AS `assign_date`,
  1 AS `reseller1_account_id`,
  1 AS `reseller1_assign_date`,
  1 AS `reseller2_account_id`,
  1 AS `reseller2_assign_date`,
  1 AS `reseller3_account_id`,
  1 AS `reseller3_assign_date`,
  1 AS `create_date`,
  1 AS `channels`,
  1 AS `did_name`,
  1 AS `number_type`,
  1 AS `area_code`,
  1 AS `city`,
  1 AS `state` */;
SET character_set_client = @saved_cs_client;

--
-- Temporary table structure for view `openipaddress`
--

DROP TABLE IF EXISTS `openipaddress`;
/*!50001 DROP VIEW IF EXISTS `openipaddress`*/;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8mb4;
/*!50001 CREATE VIEW `openipaddress` AS SELECT
 1 AS `ipaddress`,
  1 AS `fromip` */;
SET character_set_client = @saved_cs_client;

--
-- Table structure for table `payment_history`
--

DROP TABLE IF EXISTS `payment_history`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `payment_history` (
  `payment_id` int(11) NOT NULL AUTO_INCREMENT,
  `account_id` varchar(30) NOT NULL,
  `payment_option_id` varchar(30) NOT NULL,
  `payment_collection_id` varchar(30) DEFAULT NULL,
  `amount` decimal(12,6) NOT NULL,
  `paid_on` datetime NOT NULL,
  `notes` text DEFAULT NULL,
  `transaction_id` varchar(50) NOT NULL,
  `file_name` varchar(20) NOT NULL,
  `other_data` text NOT NULL,
  `invoice_data` text NOT NULL,
  `created_by` varchar(30) NOT NULL,
  `create_dt` datetime NOT NULL,
  PRIMARY KEY (`payment_id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci ROW_FORMAT=COMPACT;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `payment_history`
--

LOCK TABLES `payment_history` WRITE;
/*!40000 ALTER TABLE `payment_history` DISABLE KEYS */;
INSERT INTO `payment_history` VALUES
(1,'OV500','ADDBALANCE','Cash',10.000000,'2025-10-03 08:20:37','for test by OV Team','','','','','UA000345178','2025-10-03 10:57:20');
/*!40000 ALTER TABLE `payment_history` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `payment_tracking`
--

DROP TABLE IF EXISTS `payment_tracking`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `payment_tracking` (
  `payment_id` int(11) NOT NULL AUTO_INCREMENT,
  `order_id` varchar(100) NOT NULL,
  `amount` decimal(12,6) NOT NULL,
  `tracking_id` varchar(50) NOT NULL,
  `order_status` enum('initiated','failed','success','not_accepted','card_attempt') NOT NULL DEFAULT 'initiated',
  `payment_method` varchar(30) NOT NULL,
  `bank_ref_no` varchar(30) NOT NULL,
  `account_id` varchar(30) NOT NULL,
  `send_string` text NOT NULL,
  `response_string` text NOT NULL,
  `order_date` timestamp NOT NULL DEFAULT current_timestamp(),
  `attempt_check` enum('Y','N') NOT NULL DEFAULT 'Y',
  PRIMARY KEY (`payment_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci ROW_FORMAT=COMPACT;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `payment_tracking`
--

LOCK TABLES `payment_tracking` WRITE;
/*!40000 ALTER TABLE `payment_tracking` DISABLE KEYS */;
/*!40000 ALTER TABLE `payment_tracking` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `pbx_customer_permissions`
--

DROP TABLE IF EXISTS `pbx_customer_permissions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `pbx_customer_permissions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `account_id` varchar(30) NOT NULL,
  `permissions` text NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `account_id` (`account_id`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci ROW_FORMAT=COMPACT;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pbx_customer_permissions`
--

LOCK TABLES `pbx_customer_permissions` WRITE;
/*!40000 ALTER TABLE `pbx_customer_permissions` DISABLE KEYS */;
INSERT INTO `pbx_customer_permissions` VALUES
(1,'OV500','[\"EXTEN\",\"EXTEN_DELETE\",\"IVR\",\"ANNOUNCEMENT\",\"TIMEROUTE\",\"RINGGROUP\",\"QUEUE\",\"CONFERENCE\",\"VOICEMAIL\",\"VOICEMESSAGE\",\"AGENT\",\"FEATURECODE\"]');
/*!40000 ALTER TABLE `pbx_customer_permissions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `plugins`
--

DROP TABLE IF EXISTS `plugins`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `plugins` (
  `plugin_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `plugin_system_name` varchar(255) NOT NULL,
  `plugin_name` varchar(255) NOT NULL,
  `plugin_uri` varchar(120) DEFAULT NULL,
  `plugin_version` varchar(30) NOT NULL,
  `plugin_description` text DEFAULT NULL,
  `plugin_author` varchar(120) DEFAULT NULL,
  `plugin_author_uri` varchar(120) DEFAULT NULL,
  `plugin_data` longtext DEFAULT NULL,
  PRIMARY KEY (`plugin_id`),
  UNIQUE KEY `plugin_index` (`plugin_system_name`) USING BTREE
) ENGINE=MyISAM AUTO_INCREMENT=138 DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `plugins`
--

LOCK TABLES `plugins` WRITE;
/*!40000 ALTER TABLE `plugins` DISABLE KEYS */;
INSERT INTO `plugins` VALUES
(124,'ipblocker','IP Blocker','http://openvoips.org/','1.0','Allow Or Block IP....<br>1. Set \'$config[\'enable_hooks\'] to TRUE in application\\config.php<br>2.	Put this code in application\\hooks.php <br> $hook[\'post_controller_constructor\'] = array(<br>	\'class\'    => \'Ipblockerhook\',<br>	\'function\' => \'post_controller_constructor\',<br>	\'filename\' => \'ipblocker-hook.php\',<br>	\'filepath\' => \'modules/ipblocker\',<br>	\'params\'   => \"\"<br>);','Anand Kumar & Sanjay','http://ov500.openvoips.org/',NULL),
(122,'activitylog','Activity Log','http://openvoips.org/','1.0','Check Site Activity Log, Block IP....<br>1. Set \'$config[\'enable_hooks\'] to TRUE in application\\config.php<br>2.	Put this code in application\\hooks.php <br> $hook[\'post_controller_constructor\'] = array(<br>	\'class\'    => \'Activityloghook\',<br>	\'function\' => \'post_controller_constructor\',<br>	\'filename\' => \'activitylog-hook.php\',<br>	\'filepath\' => \'modules/activitylog\',<br>	\'params\'   => \"\"<br>);','Anand Kumar & Sanjay','http://ov500.openvoips.org/',NULL),
(128,'billing','Billing & Invoice Management','http://openvoips.org/','1.0','Billing & Invoice Related 1....','Anand Kumar & Sanjay','http://ov500.openvoips.org/',NULL),
(133,'paypal','PayPal Payment Gateway','http://openvoips.org/','1.0','PayPal Payment Gateway','Anand Kumar & Sanjay','http://ov500.openvoips.org/',NULL),
(130,'crs','CRS Module','http://openvoips.org/','1.0','CRS Module','Anand Kumar & Sanjay','http://ov500.openvoips.org/',NULL),
(134,'pbx','PBX Module','http://openvoips.org/','1.0','PBX Module with odder IVR & Announcement, Time Routing, Group calling and Extension','Seema Anand','http://ov500.openvoips.org/',NULL),
(135,'endpoints','Endpoints Module','http://openvoips.org/','1.0','Endpoints Module','Seema Anand openvoips@gmail.com','http://ov500.openvoips.org/',NULL),
(136,'report','Report','http://openvoips.org/','1.0','Report For Profit and Loss;','Sanjay gautam','http://ov500.openvoips.org/',NULL),
(137,'ovapi','ovapi','http://openvoips.org/','1.0','API for Cistomer and Palance selection','Seema Anand','http://ov500.openvoips.org/',NULL);
/*!40000 ALTER TABLE `plugins` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `pstnclifilter`
--

DROP TABLE IF EXISTS `pstnclifilter`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `pstnclifilter` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `account_id` varchar(30) DEFAULT NULL,
  `callerid` varchar(200) DEFAULT NULL,
  `cli_status` enum('1','0','2') DEFAULT '1',
  `created_by` varchar(30) DEFAULT NULL,
  `updated_by` varchar(30) DEFAULT NULL,
  `created_dt` datetime DEFAULT NULL,
  `updated_dt` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `account_id` (`account_id`),
  KEY `callerid` (`callerid`),
  KEY `cli_status` (`cli_status`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci ROW_FORMAT=COMPACT;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pstnclifilter`
--

LOCK TABLES `pstnclifilter` WRITE;
/*!40000 ALTER TABLE `pstnclifilter` DISABLE KEYS */;
/*!40000 ALTER TABLE `pstnclifilter` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `queue`
--

DROP TABLE IF EXISTS `queue`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `queue` (
  `queue_id` int(11) NOT NULL AUTO_INCREMENT,
  `queue_fs_name` varchar(30) NOT NULL,
  `queue_name` varchar(150) NOT NULL,
  `queue_strategy` enum('ring-all','longest-idle-agent','round-robin','top-down','agent-with-least-talk-time','agent-with-fewest-calls','sequentially-by-agent-order','ring-progressively','random') NOT NULL DEFAULT 'top-down',
  `queue_moh_sound` varchar(255) NOT NULL,
  `queue_time_base_score` enum('system','queue') NOT NULL DEFAULT 'system',
  `queue_max_wait_time` int(11) NOT NULL DEFAULT 0,
  `queue_max_wait_time_with_no_agent` int(11) NOT NULL DEFAULT 0,
  `queue_max_wait_time_with_no_agent_time_reached` int(11) NOT NULL DEFAULT 20,
  `queue_tier_rules_apply` enum('true','false') NOT NULL DEFAULT 'false',
  `queue_tier_rule_wait_second` int(11) NOT NULL DEFAULT 20,
  `queue_tier_rule_wait_multiply_level` enum('true','false') NOT NULL DEFAULT 'false',
  `queue_tier_rule_no_agent_no_wait` enum('true','false') NOT NULL DEFAULT 'false',
  `queue_discard_abandoned_after` int(11) NOT NULL DEFAULT 60,
  `queue_abandoned_resume_allowed` enum('true','false') NOT NULL DEFAULT 'false',
  `queue_record` enum('0','1') NOT NULL DEFAULT '1',
  `queue_record_template` varchar(300) DEFAULT '/var/www/html/rec/${account_id}/${uuid}_${destination_number}_${caller_id_number}.wav',
  `account_id` varchar(30) NOT NULL,
  `queue_number` int(11) DEFAULT NULL,
  `status_id` int(11) DEFAULT NULL,
  `noans_destination_type_id` varchar(50) DEFAULT NULL,
  `noans_destination_id` varchar(50) DEFAULT NULL,
  `queue_cli_prefix` varchar(20) DEFAULT NULL,
  `timeout_message_id` varchar(50) DEFAULT '0',
  `welcome_message` varchar(50) DEFAULT '0',
  `queue_busy_message` varchar(50) DEFAULT '0',
  `queue_type` enum('1','2') DEFAULT '2',
  `busy_message_delay` int(11) DEFAULT 30,
  `created_by` varchar(30) NOT NULL,
  `created_dt` datetime NOT NULL,
  `modified_by` varchar(30) DEFAULT NULL,
  `modified_dt` datetime DEFAULT NULL,
  PRIMARY KEY (`queue_id`),
  UNIQUE KEY `queue_fs_name` (`queue_fs_name`) USING BTREE,
  UNIQUE KEY `queue_number` (`account_id`,`queue_number`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci ROW_FORMAT=COMPACT;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `queue`
--

LOCK TABLES `queue` WRITE;
/*!40000 ALTER TABLE `queue` DISABLE KEYS */;
INSERT INTO `queue` VALUES
(1,'QDEMOQUEU9TQE0NVF400','DemoQueue','round-robin','','system',60,0,20,'false',20,'false','false',60,'false','1','/home/OV500/portal/uploads/recording/OV500/${strftime(%Y%m%d)}/${uuid}_${destination_number}_${caller_id_number}.mp3','OV500',4001,1,'HANGUP',NULL,'','0','','','2',30,'UC000003333','2025-10-03 16:08:29','UC000003333','2025-10-03 18:20:00');
/*!40000 ALTER TABLE `queue` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `quick_notes`
--

DROP TABLE IF EXISTS `quick_notes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `quick_notes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `note` text NOT NULL,
  `account_id` varchar(30) NOT NULL,
  `account_name` varchar(100) NOT NULL,
  `dt_created` timestamp NOT NULL DEFAULT current_timestamp(),
  `created_by` varchar(30) DEFAULT NULL,
  `created_by_name` varchar(50) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci ROW_FORMAT=COMPACT;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `quick_notes`
--

LOCK TABLES `quick_notes` WRITE;
/*!40000 ALTER TABLE `quick_notes` DISABLE KEYS */;
/*!40000 ALTER TABLE `quick_notes` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `random_cli`
--

DROP TABLE IF EXISTS `random_cli`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `random_cli` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `account_id` varchar(30) DEFAULT NULL,
  `cli_prefix` varchar(200) DEFAULT NULL,
  `cli_digit` int(11) DEFAULT NULL,
  `cli_rules` varchar(200) DEFAULT NULL,
  `name` varchar(30) DEFAULT NULL,
  `cli_status` enum('1','0') DEFAULT '0',
  `created_by` varchar(30) DEFAULT NULL,
  `updated_by` varchar(30) DEFAULT NULL,
  `created_dt` datetime DEFAULT NULL,
  `updated_dt` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci ROW_FORMAT=COMPACT;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `random_cli`
--

LOCK TABLES `random_cli` WRITE;
/*!40000 ALTER TABLE `random_cli` DISABLE KEYS */;
/*!40000 ALTER TABLE `random_cli` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `randomcli_cust`
--

DROP TABLE IF EXISTS `randomcli_cust`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `randomcli_cust` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `rule_id` varchar(30) DEFAULT '',
  `rule_name` varchar(200) DEFAULT '',
  `account_id` varchar(30) DEFAULT '',
  `destination_prefix` varchar(30) DEFAULT '',
  `cli_fixprefix` varchar(20) DEFAULT '',
  `cli_length` int(11) DEFAULT 1,
  `cli_status` enum('1','0') DEFAULT '1',
  `created_by` varchar(30) DEFAULT '',
  `updated_by` varchar(30) DEFAULT NULL,
  `created_dt` datetime DEFAULT NULL,
  `update_dt` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00' ON UPDATE current_timestamp(),
  `rule_type` enum('1','2') DEFAULT '1',
  PRIMARY KEY (`id`),
  UNIQUE KEY `rule_id` (`rule_id`) USING BTREE,
  KEY `destination_prefix` (`destination_prefix`),
  KEY `account_id` (`account_id`),
  KEY `rule_type` (`rule_type`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci ROW_FORMAT=COMPACT;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `randomcli_cust`
--

LOCK TABLES `randomcli_cust` WRITE;
/*!40000 ALTER TABLE `randomcli_cust` DISABLE KEYS */;
/*!40000 ALTER TABLE `randomcli_cust` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ratecard`
--

DROP TABLE IF EXISTS `ratecard`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `ratecard` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `ratecard_id` varchar(30) DEFAULT NULL,
  `ratecard_name` varchar(30) DEFAULT NULL,
  `ratecard_type` enum('CARRIER','CUSTOMER') DEFAULT 'CARRIER',
  `account_id` varchar(30) NOT NULL,
  `ratecard_currency_id` varchar(10) DEFAULT 'USD',
  `ratecard_for` enum('INCOMING','OUTGOING') DEFAULT 'OUTGOING',
  `created_by` varchar(30) DEFAULT NULL,
  `updated_by` varchar(30) DEFAULT NULL,
  `created_dt` datetime DEFAULT NULL,
  `updated_dt` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `ratecard_id` (`ratecard_id`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci ROW_FORMAT=COMPACT;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ratecard`
--

LOCK TABLES `ratecard` WRITE;
/*!40000 ALTER TABLE `ratecard` DISABLE KEYS */;
INSERT INTO `ratecard` VALUES
(1,'CARR30','Carrier Outgoing','CARRIER','SYSTEM','USD','OUTGOING','UA000345178',NULL,NULL,'2025-10-02 13:42:35'),
(2,'CARR38','Carrier Incoming','CARRIER','SYSTEM','USD','INCOMING','UA000345178',NULL,NULL,'2025-10-02 13:42:11'),
(3,'SAOU43','Sale Outgoing','CUSTOMER','SYSTEM','USD','OUTGOING','UA000345178',NULL,NULL,NULL),
(4,'SAIN46','Sale Incoming','CUSTOMER','SYSTEM','USD','INCOMING','UA000345178',NULL,NULL,NULL);
/*!40000 ALTER TABLE `ratecard` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `reseller_dialplan`
--

DROP TABLE IF EXISTS `reseller_dialplan`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `reseller_dialplan` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `account_id` varchar(30) DEFAULT NULL,
  `dialplan_id` varchar(30) DEFAULT NULL,
  `create_dt` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `reseller_dialplan_key` (`account_id`,`dialplan_id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci ROW_FORMAT=COMPACT;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `reseller_dialplan`
--

LOCK TABLES `reseller_dialplan` WRITE;
/*!40000 ALTER TABLE `reseller_dialplan` DISABLE KEYS */;
/*!40000 ALTER TABLE `reseller_dialplan` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `resellers`
--

DROP TABLE IF EXISTS `resellers`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `resellers` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `account_id` varchar(30) NOT NULL,
  `company_name` varchar(50) NOT NULL,
  `contact_name` varchar(50) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `country_id` int(11) DEFAULT NULL,
  `state_code_id` mediumint(9) DEFAULT NULL,
  `phone` varchar(30) DEFAULT NULL,
  `emailaddress` varchar(1000) DEFAULT NULL,
  `pincode` varchar(15) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `account_id` (`account_id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci ROW_FORMAT=COMPACT;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `resellers`
--

LOCK TABLES `resellers` WRITE;
/*!40000 ALTER TABLE `resellers` DISABLE KEYS */;
/*!40000 ALTER TABLE `resellers` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ringgroup`
--

DROP TABLE IF EXISTS `ringgroup`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `ringgroup` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `ringgroup_id` varchar(30) DEFAULT NULL,
  `account_id` varchar(30) NOT NULL,
  `ringgroup_number` int(11) DEFAULT NULL,
  `ringgroup_name` varchar(50) DEFAULT NULL,
  `ringgroup_timeout` int(11) NOT NULL,
  `created_by` char(36) DEFAULT NULL,
  `created_dt` datetime DEFAULT NULL,
  `modified_by` char(36) DEFAULT NULL,
  `modified_dt` datetime DEFAULT NULL,
  `failover_route` varchar(30) DEFAULT 'HANGUP',
  `failover_route_endpoint` varchar(50) DEFAULT NULL,
  `welcome_audiofile_id` varchar(50) DEFAULT NULL,
  `busy_audiofile_id` varchar(50) DEFAULT NULL,
  `timeout_audiofile_id` varchar(50) DEFAULT NULL,
  `ringgroup_moh_sound` varchar(50) DEFAULT NULL,
  `ringgroup_cli_prefix` varchar(50) DEFAULT NULL,
  `ringgroup_record` enum('0','1') DEFAULT '0',
  `status` int(11) DEFAULT 1,
  PRIMARY KEY (`id`),
  UNIQUE KEY `ringgroup_id` (`ringgroup_id`) USING BTREE,
  UNIQUE KEY `account_id_2` (`account_id`,`ringgroup_number`) USING BTREE,
  KEY `tenant_id` (`account_id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci ROW_FORMAT=COMPACT;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ringgroup`
--

LOCK TABLES `ringgroup` WRITE;
/*!40000 ALTER TABLE `ringgroup` DISABLE KEYS */;
/*!40000 ALTER TABLE `ringgroup` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ringgroup_extension`
--

DROP TABLE IF EXISTS `ringgroup_extension`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `ringgroup_extension` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `ringgroup_id` varchar(30) NOT NULL,
  `account_id` varchar(30) NOT NULL,
  `extension_id` varchar(30) NOT NULL,
  `created_by` char(36) DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `modified_by` char(36) DEFAULT NULL,
  `modified_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `ringgroup_id` (`ringgroup_id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci ROW_FORMAT=COMPACT;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ringgroup_extension`
--

LOCK TABLES `ringgroup_extension` WRITE;
/*!40000 ALTER TABLE `ringgroup_extension` DISABLE KEYS */;
/*!40000 ALTER TABLE `ringgroup_extension` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `signup_log`
--

DROP TABLE IF EXISTS `signup_log`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `signup_log` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(30) DEFAULT NULL,
  `secret` varchar(30) DEFAULT NULL,
  `customer_id` int(11) DEFAULT NULL,
  `user_id` varchar(30) NOT NULL,
  `user_type` varchar(30) NOT NULL,
  `user_fullname` varchar(100) NOT NULL,
  `user_emailaddress` varchar(100) NOT NULL,
  `user_phone` varchar(15) NOT NULL,
  `user_address` varchar(256) NOT NULL,
  `country` char(6) NOT NULL,
  `status` enum('1','0') NOT NULL,
  `signplan_id` varchar(30) NOT NULL,
  `signplan_name` varchar(30) NOT NULL,
  `account_id` varchar(30) DEFAULT NULL,
  `account_status` enum('1','0','-1','-2','-3','-4') DEFAULT '-1' COMMENT '-1=not approved, 0=inactive, -2=suspended,-3=Stop Billing; -4=Account Closed',
  `account_type` enum('CUSTOMER','RESELLER') DEFAULT 'CUSTOMER',
  `currency_id` varchar(30) NOT NULL,
  `billing_type` varchar(30) NOT NULL,
  `dp` tinyint(1) DEFAULT 4,
  `tariff_id` varchar(30) DEFAULT '0',
  `account_cc` int(11) DEFAULT 10,
  `account_cps` int(11) DEFAULT 1,
  `tax_number` varchar(30) DEFAULT NULL,
  `tax1` double(6,2) DEFAULT 0.00,
  `tax2` double(6,2) DEFAULT 0.00,
  `tax3` double(6,2) DEFAULT 0.00,
  `tax_type` enum('inclusive','exclusive') DEFAULT 'exclusive',
  `max_callduration` int(11) DEFAULT 120,
  `account_codecs` varchar(150) DEFAULT 'G729,PCMU,PCMA,G722',
  `media_transcoding` enum('1','0') DEFAULT '0',
  `media_rtpproxy` enum('1','0') DEFAULT '0',
  `dialplan_id` varchar(100) NOT NULL,
  `created_by_user_id` varchar(30) DEFAULT NULL,
  `created_by_account_id` varchar(30) DEFAULT NULL,
  `created_by_user_browser` varchar(50) NOT NULL,
  `created_by_user_ip` varchar(50) NOT NULL,
  `create_dt` datetime DEFAULT NULL,
  `update_dt` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `create_dt` (`create_dt`) USING BTREE,
  KEY `signplan_id` (`signplan_id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci ROW_FORMAT=COMPACT;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `signup_log`
--

LOCK TABLES `signup_log` WRITE;
/*!40000 ALTER TABLE `signup_log` DISABLE KEYS */;
/*!40000 ALTER TABLE `signup_log` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `signup_plan`
--

DROP TABLE IF EXISTS `signup_plan`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `signup_plan` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `signplan_id` varchar(30) NOT NULL,
  `signplan_name` varchar(30) NOT NULL,
  `tariff_id` varchar(30) NOT NULL,
  `billing_type` varchar(30) DEFAULT 'PREPAID',
  `max_callduration` int(11) DEFAULT 120,
  `account_type` enum('CUSTOMER','RESELLER') DEFAULT 'CUSTOMER',
  `currency_id` int(11) NOT NULL DEFAULT 1,
  `dp` tinyint(1) DEFAULT 4,
  `account_cc` int(11) DEFAULT 10,
  `account_cps` int(11) DEFAULT 1,
  `tax1` double(6,2) DEFAULT 0.00,
  `tax2` double(6,2) DEFAULT 0.00,
  `tax3` double(6,2) DEFAULT 0.00,
  `tax_type` enum('inclusive','exclusive') DEFAULT 'exclusive',
  `account_codecs` varchar(150) DEFAULT 'G729,PCMU,PCMA,G722',
  `media_transcoding` enum('1','0') DEFAULT '1',
  `media_rtpproxy` enum('1','0') DEFAULT '1',
  `dialplan_id` varchar(100) NOT NULL,
  `account_level` int(11) DEFAULT 1,
  `create_dt` datetime DEFAULT NULL,
  `update_dt` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp(),
  `created_by_user_id` varchar(30) DEFAULT NULL,
  `created_by_account_id` varchar(30) DEFAULT 'SYSTEM',
  PRIMARY KEY (`id`),
  UNIQUE KEY `signplan_id` (`signplan_id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci ROW_FORMAT=COMPACT;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `signup_plan`
--

LOCK TABLES `signup_plan` WRITE;
/*!40000 ALTER TABLE `signup_plan` DISABLE KEYS */;
/*!40000 ALTER TABLE `signup_plan` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `subdomain`
--

DROP TABLE IF EXISTS `subdomain`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `subdomain` (
  `subdomain_id` int(11) NOT NULL AUTO_INCREMENT,
  `subdomain` varchar(30) NOT NULL,
  `account_id` varchar(30) NOT NULL,
  `logo` varchar(255) NOT NULL,
  `status_id` enum('1','0') NOT NULL DEFAULT '1',
  PRIMARY KEY (`subdomain_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci ROW_FORMAT=COMPACT;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `subdomain`
--

LOCK TABLES `subdomain` WRITE;
/*!40000 ALTER TABLE `subdomain` DISABLE KEYS */;
/*!40000 ALTER TABLE `subdomain` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `sys_countries`
--

DROP TABLE IF EXISTS `sys_countries`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `sys_countries` (
  `country_id` int(11) NOT NULL AUTO_INCREMENT,
  `country_abbr` char(3) NOT NULL,
  `country_iso` varchar(2) DEFAULT NULL,
  `country_prefix` int(11) NOT NULL,
  `country_name` varchar(100) NOT NULL,
  `status_id` int(10) unsigned NOT NULL DEFAULT 2,
  `display_sequence` smallint(6) NOT NULL DEFAULT 0,
  PRIMARY KEY (`country_id`)
) ENGINE=InnoDB AUTO_INCREMENT=249 DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci ROW_FORMAT=COMPACT;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `sys_countries`
--

LOCK TABLES `sys_countries` WRITE;
/*!40000 ALTER TABLE `sys_countries` DISABLE KEYS */;
INSERT INTO `sys_countries` VALUES
(1,'AFG','AF',93,'Afghanistan',1,0),
(2,'ALB','AL',355,'Albania',1,0),
(3,'DZA','DZ',213,'Algeria',1,0),
(4,'ASM','AS',1684,'American Samoa',1,0),
(5,'AND','AD',376,'Andorra',1,0),
(6,'AGO','AO',244,'Angola',1,0),
(7,'AIA','AI',1264,'Anguilla',1,0),
(8,'ATA','AQ',672,'Antarctica',1,0),
(9,'ATG','AG',1268,'Antigua & Barbuda',1,0),
(10,'ARG','AR',54,'Argentina',1,0),
(11,'ARM','AM',374,'Armenia',1,0),
(12,'ABW','AW',297,'Aruba',1,0),
(13,'AUS','AU',61,'Australia',1,0),
(14,'AUT','AT',43,'Austria',1,0),
(15,'AZE','AZ',994,'Azerbaijan',1,0),
(16,'BHS','BS',1242,'Bahamas',1,0),
(17,'BHR','BH',973,'Bahrain',1,0),
(18,'BGD','BD',880,'Bangladesh',1,0),
(19,'BRB','BB',1246,'Barbados',1,0),
(20,'BLR','BY',375,'Belarus',1,0),
(21,'BEL','BE',32,'Belgium',1,0),
(22,'BLZ','BZ',501,'Belize',1,0),
(23,'BEN','BJ',229,'Benin',1,0),
(24,'BMU','BM',1441,'Bermuda',1,0),
(25,'BTN','BT',975,'Bhutan',1,0),
(26,'BOL','BO',591,'Bolivia',1,0),
(27,'BIH','BA',387,'Bosnia & Herzegovina',1,0),
(28,'BWA','BW',267,'Botswana',1,0),
(29,'BVT','BV',55,'Bouvet island',2,0),
(30,'BRA','BR',55,'Brazil',1,0),
(31,'IOT','IO',246,'British Indian Ocean Territory',2,0),
(32,'BRN','BN',673,'Brunei',1,0),
(33,'BGR','BG',359,'Bulgaria',1,0),
(34,'BFA','BF',226,'Burkina Faso',1,0),
(35,'BDI','BI',257,'Burundi',1,0),
(36,'KHM','KH',855,'Cambodia',1,0),
(37,'CMR','CM',237,'Cameroon',1,0),
(38,'CAN','CA',1,'Canada',1,0),
(39,'CPV','CV',238,'Cape Verde',1,0),
(40,'CYM','KY',1345,'Cayman Islands',1,0),
(41,'CAF','CF',236,'Central African Republic',1,0),
(42,'TCD','TD',235,'Chad',1,0),
(43,'CHL','CL',56,'Chile',1,0),
(44,'CHN','CN',86,'China',1,0),
(45,'CXR','CX',618916,'Christmas Island',2,0),
(46,'CCK','CC',61891,'Cocos Islands',2,0),
(47,'COL','CO',57,'Colombia',1,0),
(48,'COM','KM',269,'Comoros',1,0),
(49,'COD','CD',243,'Democratic Republic of Congo',1,0),
(50,'COG','CG',242,'Republic of the Congo',1,0),
(51,'COK','CK',682,'Cook Islands',1,0),
(52,'CRI','CR',506,'Costa Rica',1,0),
(53,'CIV','CI',225,'Cote D\'ivoire',1,0),
(54,'HRV','HR',385,'Croatia',1,0),
(55,'CUB','CU',53,'Cuba',1,0),
(56,'CYP','CY',357,'Cyprus',1,0),
(57,'CZE','CZ',420,'Czech Republic',1,0),
(58,'DNK','DK',45,'Denmark',1,0),
(59,'DJI','DJ',253,'Djibouti',1,0),
(60,'DMA','DM',1767,'Dominica',1,0),
(61,'DOM','DO',1809,'Dominican Republic',1,0),
(62,'TLS','TP',670,'East Timor',2,0),
(63,'ECU','EC',593,'Ecuador',1,0),
(64,'EGY','EG',20,'Egypt',1,0),
(65,'SLV','SV',503,'El salvador',1,0),
(66,'GNQ','GQ',240,'Equatorial Guinea',1,0),
(67,'ERI','ER',291,'Eritrea',1,0),
(68,'EST','EE',372,'Estonia',1,0),
(69,'ETH','ET',251,'Ethiopia',1,0),
(70,'FLK','FK',500,'Falkland Islands',1,0),
(71,'FRO','FO',298,'Faeroe Islands',1,0),
(72,'FJI','FJ',679,'Fiji',1,0),
(73,'FIN','FI',358,'Finland',1,0),
(74,'FRA','FR',33,'France',1,0),
(75,'FXX','FX',0,'France Metropolitan',2,0),
(76,'GUF','GF',594,'French Guiana',1,0),
(77,'PYF','PF',689,'French Polynesia',1,0),
(78,'ATF','TF',0,'French Southern Territories',2,0),
(79,'GAB','GA',241,'Gabon',1,0),
(80,'GMB','GM',220,'Gambia',1,0),
(81,'GEO','GE',995,'Georgia',1,0),
(82,'DEU','DE',49,'Germany',1,0),
(83,'GHA','GH',233,'Ghana',1,0),
(84,'GIB','GI',350,'Gibraltar',1,0),
(85,'GRC','GR',30,'Greece',1,0),
(86,'GRL','GL',299,'Greenland',1,0),
(87,'GRD','GD',1473,'Grenada',1,0),
(88,'GLP','GP',590,'Guadeloupe',1,0),
(89,'GUM','GU',1671,'Guam',1,0),
(90,'GTM','GT',502,'Guatemala',1,0),
(91,'GIN','GN',224,'Guinea',1,0),
(92,'GNB','GW',245,'Guinea Bissau',1,0),
(93,'GUY','GY',592,'Guyana',1,0),
(94,'HTI','HT',509,'Haiti',1,0),
(95,'HMD','HM',0,'Heard & Mc Donald Islands',2,0),
(96,'HND','HN',504,'Honduras',1,0),
(97,'HKG','HK',852,'Hong kong',1,0),
(98,'HUN','HU',36,'Hungary',1,0),
(99,'ISL','IS',354,'Iceland',1,0),
(100,'IND','IN',91,'India',1,499),
(101,'IDN','ID',62,'Indonesia',1,0),
(102,'IRN','IR',98,'Iran',1,0),
(103,'IRQ','IQ',964,'Iraq',1,0),
(104,'IRL','IE',353,'Ireland',1,0),
(105,'ISR','IL',972,'Israel',1,0),
(106,'ITA','IT',39,'Italy',1,0),
(107,'JAM','JM',1876,'Jamaica',1,0),
(108,'JPN','JP',81,'Japan',1,0),
(109,'JOR','JO',962,'Jordan',1,0),
(110,'KAZ','KZ',7,'Kazakhstan',1,0),
(111,'KEN','KE',254,'Kenya',1,0),
(112,'KIR','KI',686,'Kiribati',1,0),
(113,'PRK','KP',850,'North Korea',1,0),
(114,'KOR','KR',82,'South Korea',1,0),
(115,'KWT','KW',965,'Kuwait',1,0),
(116,'KGZ','KG',996,'Kyrgyzstan',1,0),
(117,'LAO','LA',856,'Laos',1,0),
(118,'LVA','LV',371,'Latvia',1,0),
(119,'LBN','LB',961,'Lebanon',1,0),
(120,'LSO','LS',266,'Lesotho',1,0),
(121,'LBR','LR',231,'Liberia',1,0),
(122,'LBY','LY',218,'Libya',1,0),
(123,'LIE','LI',423,'Liechtenstein',1,0),
(124,'LTU','LT',370,'Lithuania',1,0),
(125,'LUX','LU',352,'Luxembourg',1,0),
(126,'MAC','MO',853,'Macau',1,0),
(127,'MKD','MK',389,'Macedonia',1,0),
(128,'MDG','MG',261,'Madagascar',1,0),
(129,'MWI','MW',265,'Malawi',1,0),
(130,'MYS','MY',60,'Malaysia',1,0),
(131,'MDV','MV',960,'Maldives',1,0),
(132,'MLI','ML',223,'Mali',1,0),
(133,'MLT','MT',356,'Malta',1,0),
(134,'MHL','MH',692,'Marshall Islands',1,0),
(135,'MTQ','MQ',596,'Martinique',1,0),
(136,'MRT','MR',222,'Mauritania',1,0),
(137,'MUS','MU',230,'Mauritius',1,0),
(138,'MYT','YT',262,'Mayotte',1,0),
(139,'MEX','MX',52,'Mexico',1,0),
(140,'FSM','FM',691,'Micronesia',1,0),
(141,'MDA','MD',373,'Moldova',1,0),
(142,'MCO','MC',377,'Monaco',1,0),
(143,'MNG','MN',976,'Mongolia',1,0),
(144,'MSR','MS',1664,'Montserrat',1,0),
(145,'MAR','MA',212,'Morocco',1,0),
(146,'MOZ','MZ',258,'Mozambique',1,0),
(147,'MMR','MM',95,'Myanmar',1,0),
(148,'NAM','NA',264,'Namibia',1,0),
(149,'NRU','NR',674,'Nauru',1,0),
(150,'NPL','NP',977,'Nepal',1,0),
(151,'NLD','NL',31,'Netherlands',1,0),
(152,'ANT','AN',599,'Netherlands Antilles',1,0),
(153,'NCL','NC',687,'New Caledonia',1,0),
(154,'NZL','NZ',64,'New Zealand',1,0),
(155,'NIC','NI',505,'Nicaragua',1,0),
(156,'NER','NE',227,'Niger',1,0),
(157,'NGA','NG',234,'Nigeria',1,0),
(158,'NIU','NU',683,'Niue',1,0),
(159,'NFK','NF',672,'Norfolk Islands',1,0),
(160,'MNP','MP',1670,'Mariana Islands',1,0),
(161,'NOR','NO',47,'Norway',1,0),
(162,'OMN','OM',968,'Oman',1,0),
(163,'PAK','PK',92,'Pakistan',1,0),
(164,'PLW','PW',680,'Palau',1,0),
(165,'PSE','PS',970,'Palestine',1,0),
(166,'PAN','PA',507,'Panama',1,0),
(167,'PNG','PG',675,'Papua New Guinea',1,0),
(168,'PRY','PY',595,'Paraguay',1,0),
(169,'PER','PE',51,'Peru',1,0),
(170,'PHL','PH',63,'Philippines',1,0),
(171,'PCN','PN',870,'Pitcairn',1,0),
(172,'POL','PL',48,'Poland',1,0),
(173,'PRT','PT',351,'Portugal',1,0),
(174,'PRI','PR',1,'Puerto Rico',1,0),
(175,'QAT','QA',974,'Qatar',1,0),
(176,'REU','RE',262,'Reunion Island',1,0),
(177,'ROU','RO',40,'Romania',1,0),
(178,'RUS','RU',7,'Russia',1,0),
(179,'RWA','RW',250,'Rwanda',1,0),
(180,'KNA','KN',1869,'St. Kitts',1,0),
(181,'LCA','LC',1758,'St. Lucia',1,0),
(182,'VCT','VC',1784,'St. Vincent',1,0),
(183,'WSM','WS',685,'Samoa',1,0),
(184,'SMR','SM',378,'San Marino',1,0),
(185,'STP','ST',239,'Sao Tome',1,0),
(186,'SAU','SA',966,'Saudi Arabia',1,0),
(187,'SEN','SN',221,'Senegal',1,0),
(188,'SYC','SC',248,'Seychelles',1,0),
(189,'SLE','SL',232,'Sierra Leone',1,0),
(190,'SGP','SG',65,'Singapore',1,0),
(191,'SVK','SK',421,'Slovakia',1,0),
(192,'SVN','SI',386,'Slovenia',1,0),
(193,'SLB','SB',677,'Solomon Islands',1,0),
(194,'SOM','SO',252,'Somalia',1,0),
(195,'ZAF','ZA',27,'South africa',1,0),
(196,'SGS','GS',500,'South Georgia and the South Sandwich Islands',1,0),
(197,'ESP','ES',34,'Spain',1,0),
(198,'LKA','LK',94,'Sri Lanka',1,0),
(199,'SHN','SH',290,'St. Helena',1,0),
(200,'SPM','PM',508,'St. Pierre & Miquelon',1,0),
(201,'SDN','SD',249,'Sudan',1,0),
(202,'SUR','SR',597,'Suriname',1,0),
(203,'SJM','SJ',47,'Svalbard and Jan Mayen Islands',1,0),
(204,'SWZ','SZ',268,'Swaziland',1,0),
(205,'SWE','SE',46,'Sweden',1,0),
(206,'CHE','CH',41,'Switzerland',1,0),
(207,'SYR','SY',963,'Syria',1,0),
(208,'TWN','TW',886,'Taiwan',1,0),
(209,'TJK','TJ',992,'Tajikistan',1,0),
(210,'TZA','TZ',255,'Tanzania',1,0),
(211,'THA','TH',66,'Thailand',1,0),
(212,'TGO','TG',228,'Togo',1,0),
(213,'TKL','TK',690,'Tokelau',1,0),
(214,'TON','TO',676,'Tonga',1,0),
(215,'TTO','TT',1868,'Trinidad & Tobago',1,0),
(216,'TUN','TN',216,'Tunisia',1,0),
(217,'TUR','TR',90,'Turkey',1,0),
(218,'TKM','TM',993,'Turkmenistan',1,0),
(219,'TCA','TC',1649,'Turks & Caicos Islands',1,0),
(220,'TUV','TV',688,'Tuvalu',1,0),
(221,'UGA','UG',256,'Uganda',1,0),
(222,'UKR','UA',380,'Ukraine',1,0),
(223,'ARE','AE',971,'United Arab Emirates',1,0),
(224,'GBR','GB',44,'United Kingdom',1,500),
(225,'USA','US',1,'United States of America',1,498),
(226,'UMI','UM',581,'United States Minor Outlying Islands',1,0),
(227,'URY','UY',598,'Uruguay',1,0),
(228,'UZB','UZ',998,'Uzbekistan',1,0),
(229,'VUT','VU',678,'Vanuatu',1,0),
(230,'VAT','VA',39,'Vatican',1,0),
(231,'VEN','VE',58,'Venezuela',1,0),
(232,'VNM','VN',84,'Viet nam',1,0),
(233,'VGB','VG',1284,'British Virgin Islands',1,0),
(234,'VIR','VI',1340,'US Virgin Islands',1,0),
(235,'WLF','WF',681,'Wallis & Futuna Islands',1,0),
(236,'ESH','EH',212,'Western Sahara',1,0),
(237,'YEM','YE',967,'Yemen',1,0),
(238,'YUG','YU',891,'Yugoslavia',2,0),
(239,'ZMB','ZM',260,'Zambia',1,0),
(240,'ZWE','ZW',263,'Zimbabwe',1,0),
(241,'SRB','RS',381,'Serbia',1,0),
(242,'MNE','ME',382,'Montenegro',1,0),
(243,'YAR','YE',0,'North Yemen',2,0),
(244,'SSD','SD',211,'South Sudan',1,0),
(245,'SCG','CS',381,'Kosovo',1,0),
(246,'MAF','MF',1599,'St. Martin',1,0),
(247,'ASC','AC',247,'Ascension Island',2,0),
(248,'ACT','',672,'Australian Territories',2,0);
/*!40000 ALTER TABLE `sys_countries` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `sys_currencies`
--

DROP TABLE IF EXISTS `sys_currencies`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `sys_currencies` (
  `idc` int(11) NOT NULL AUTO_INCREMENT,
  `currency_id` varchar(10) DEFAULT 'USD',
  `symbol` varchar(20) NOT NULL DEFAULT '',
  `detail_name` varchar(500) DEFAULT NULL,
  `name` varchar(50) DEFAULT NULL,
  `status_id` enum('0','1') DEFAULT '1',
  `display_sequence` smallint(6) DEFAULT NULL,
  PRIMARY KEY (`idc`)
) ENGINE=InnoDB AUTO_INCREMENT=112 DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci ROW_FORMAT=COMPACT;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `sys_currencies`
--

LOCK TABLES `sys_currencies` WRITE;
/*!40000 ALTER TABLE `sys_currencies` DISABLE KEYS */;
INSERT INTO `sys_currencies` VALUES
(1,'USD','$',NULL,'United States Dollar','1',1),
(2,'GBP','£',NULL,'British Pound','1',2),
(3,'INR','₹',NULL,'Indian Rupee','0',2),
(4,'SUSD','$S',NULL,'Singapore Dollar','0',2),
(5,'EUR','€',NULL,'Euro','0',2),
(6,'ALL','Lek',NULL,'Albania Lek','0',2),
(7,'AFN','؋',NULL,'Afghanistan Afghani','0',2),
(8,'ARS','$',NULL,'Argentina Peso','0',2),
(9,'AWG','ƒ',NULL,'Aruba Guilder','0',2),
(10,'AUD','$',NULL,'Australia Dollar','0',2),
(11,'AZN','₼',NULL,'Azerbaijan Manat','0',2),
(12,'BSD','$',NULL,'Bahamas Dollar','0',2),
(13,'BBD','$',NULL,'Barbados Dollar','0',2),
(14,'BYN','Br',NULL,'Belarus Ruble','0',2),
(15,'BZD','BZ$',NULL,'Belize Dollar','0',2),
(16,'BMD','$',NULL,'Bermuda Dollar','0',2),
(17,'BOB','$b',NULL,'Bolivia Bolíviano','0',2),
(18,'BAM','KM',NULL,'Bosnia and Herzegovina Convertible Mark','0',2),
(19,'BWP','P',NULL,'Botswana Pula','0',2),
(20,'BGN','лв',NULL,'Bulgaria Lev','0',2),
(21,'BRL','R$',NULL,'Brazil Real','0',2),
(22,'BND','$',NULL,'Brunei Darussalam Dollar','0',2),
(23,'KHR','៛',NULL,'Cambodia Riel','0',2),
(24,'CAD','$',NULL,'Canada Dollar','0',2),
(25,'KYD','$',NULL,'Cayman Islands Dollar','0',2),
(26,'CLP','$',NULL,'Chile Peso','0',2),
(27,'CNY','¥',NULL,'China Yuan Renminbi','0',2),
(28,'COP','$',NULL,'Colombia Peso','0',2),
(29,'CRC','₡',NULL,'Costa Rica Colon','0',2),
(30,'HRK','kn',NULL,'Croatia Kuna','0',2),
(31,'CUP','₱',NULL,'Cuba Peso','0',2),
(32,'CZK','Kč',NULL,'Czech Republic Koruna','0',2),
(33,'DKK','kr',NULL,'Denmark Krone','0',2),
(34,'DOP','RD$',NULL,'Dominican Republic Peso','0',2),
(35,'XCD','$',NULL,'East Caribbean Dollar','0',2),
(36,'EGP','£',NULL,'Egypt Pound','0',2),
(37,'SVC','$',NULL,'El Salvador Colon','0',2),
(38,'FKP','£',NULL,'Falkland Islands (Malvinas) Pound','0',2),
(39,'FJD','$',NULL,'Fiji Dollar','0',2),
(40,'GHS','¢',NULL,'Ghana Cedi','0',2),
(41,'GIP','£',NULL,'Gibraltar Pound','0',2),
(42,'GTQ','Q',NULL,'Guatemala Quetzal','0',2),
(43,'GGP','£',NULL,'Guernsey Pound','0',2),
(44,'GYD','$',NULL,'Guyana Dollar','0',2),
(45,'HNL','L',NULL,'Honduras Lempira','0',2),
(46,'HKD','$',NULL,'Hong Kong Dollar','0',2),
(47,'HUF','Ft',NULL,'Hungary Forint','0',2),
(48,'ISK','kr',NULL,'Iceland Krona','0',2),
(49,'ZWD','Z$',NULL,'Zimbabwe Dollar','0',2),
(50,'IDR','Rp',NULL,'Indonesia Rupiah','0',2),
(51,'IRR','﷼',NULL,'Iran Rial','0',2),
(52,'IMP','£',NULL,'Isle of Man Pound','0',2),
(53,'ILS','₪',NULL,'Israel Shekel','0',2),
(54,'JMD','J$',NULL,'Jamaica Dollar','0',2),
(55,'JPY','¥',NULL,'Japan Yen','0',2),
(56,'JEP','£',NULL,'Jersey Pound','0',2),
(57,'KZT','лв',NULL,'Kazakhstan Tenge','0',2),
(58,'KPW','₩',NULL,'Korea (North) Won','0',2),
(59,'YER','﷼',NULL,'Yemen Rial','0',2),
(60,'KGS','лв',NULL,'Kyrgyzstan Som','0',2),
(61,'LAK','₭',NULL,'Laos Kip','0',2),
(62,'LBP','£',NULL,'Lebanon Pound','0',2),
(63,'LRD','$',NULL,'Liberia Dollar','0',2),
(64,'MKD','ден',NULL,'Macedonia Denar','0',2),
(65,'MYR','RM',NULL,'Malaysia Ringgit','0',2),
(66,'MUR','₨',NULL,'Mauritius Rupee','0',2),
(67,'MXN','$',NULL,'Mexico Peso','0',2),
(68,'MNT','₮',NULL,'Mongolia Tughrik','0',2),
(69,'VND','₫',NULL,'Viet Nam Dong','0',2),
(70,'MZN','MT',NULL,'Mozambique Metical','0',2),
(71,'NAD','$',NULL,'Namibia Dollar','0',2),
(72,'NPR','₨',NULL,'Nepal Rupee','0',2),
(73,'ANG','ƒ',NULL,'Netherlands Antilles Guilder','0',2),
(74,'NZD','$',NULL,'New Zealand Dollar','0',2),
(75,'NIO','C$',NULL,'Nicaragua Cordoba','0',2),
(76,'NGN','₦',NULL,'Nigeria Naira','0',2),
(77,'NOK','kr',NULL,'Norway Krone','0',2),
(78,'OMR','﷼',NULL,'Oman Rial','0',2),
(79,'PKR','₨',NULL,'Pakistan Rupee','0',2),
(80,'PAB','B/.',NULL,'Panama Balboa','0',2),
(81,'PYG','Gs',NULL,'Paraguay Guarani','0',2),
(82,'PEN','S/.',NULL,'Peru Sol','0',2),
(83,'PHP','₱',NULL,'Philippines Peso','0',2),
(84,'PLN','zł',NULL,'Poland Zloty','0',2),
(85,'QAR','﷼',NULL,'Qatar Riyal','0',2),
(86,'RON','lei',NULL,'Romania Leu','0',2),
(87,'RUB','₽',NULL,'Russia Ruble','0',2),
(88,'SHP','£',NULL,'Saint Helena Pound','0',2),
(89,'SAR','﷼',NULL,'Saudi Arabia Riyal','0',2),
(90,'RSD','Дин.',NULL,'Serbia Dinar','0',2),
(91,'SCR','₨',NULL,'Seychelles Rupee','0',2),
(92,'SGD','$',NULL,'Singapore Dollar','0',2),
(93,'SBD','$',NULL,'Solomon Islands Dollar','0',2),
(94,'SOS','S',NULL,'Somalia Shilling','0',2),
(95,'KRW','₩',NULL,'South Korean Won','0',2),
(96,'ZAR','R',NULL,'South Africa Rand','0',5),
(97,'LKR','₨',NULL,'Sri Lanka Rupee','0',2),
(98,'SEK','kr',NULL,'Sweden Krona','0',2),
(99,'CHF','CHF',NULL,'Switzerland Franc','0',2),
(100,'SRD','$',NULL,'Suriname Dollar','0',2),
(101,'SYP','£',NULL,'Syria Pound','0',2),
(102,'TWD','NT$',NULL,'Taiwan New Dollar','0',2),
(103,'THB','฿',NULL,'Thailand Baht','0',2),
(104,'TTD','TT$',NULL,'Trinidad and Tobago Dollar','0',2),
(105,'TRY','₺',NULL,'Turkey Lira','0',2),
(106,'TVD','$',NULL,'Tuvalu Dollar','0',2),
(107,'UAH','₴',NULL,'Ukraine Hryvnia','0',2),
(108,'AED',' د.إ',NULL,'UAE-Dirham','0',2),
(109,'UYU','$U',NULL,'Uruguay Peso','0',2),
(110,'UZS','лв',NULL,'Uzbekistan Som','0',2),
(111,'VEF','Bs',NULL,'Venezuela Bolívar','0',2);
/*!40000 ALTER TABLE `sys_currencies` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `sys_currencies_conversions`
--

DROP TABLE IF EXISTS `sys_currencies_conversions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `sys_currencies_conversions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `ratio` decimal(12,4) NOT NULL DEFAULT 1.0000,
  `currency_id` varchar(10) DEFAULT 'USD',
  `date` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `id_currency` (`currency_id`,`date`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci ROW_FORMAT=COMPACT;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `sys_currencies_conversions`
--

LOCK TABLES `sys_currencies_conversions` WRITE;
/*!40000 ALTER TABLE `sys_currencies_conversions` DISABLE KEYS */;
INSERT INTO `sys_currencies_conversions` VALUES
(2,1.0000,'USD','2025-10-02 03:33:38');
/*!40000 ALTER TABLE `sys_currencies_conversions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `sys_email_templates`
--

DROP TABLE IF EXISTS `sys_email_templates`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `sys_email_templates` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `account_id` varchar(30) DEFAULT NULL,
  `email_name` varchar(30) DEFAULT NULL,
  `template_for` varchar(30) DEFAULT NULL,
  `email_subject` text DEFAULT NULL,
  `email_body` text DEFAULT NULL,
  `email_bcc` text DEFAULT NULL,
  `email_cc` text DEFAULT NULL,
  `email_daemon` enum('PHPMAIL','SMTP') DEFAULT 'PHPMAIL',
  `smtp_id` varchar(30) DEFAULT NULL,
  `created_by` varchar(30) NOT NULL,
  `updated_by` varchar(30) NOT NULL,
  `created_dt` datetime NOT NULL,
  `updated_dt` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `account_id` (`account_id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci ROW_FORMAT=COMPACT;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `sys_email_templates`
--

LOCK TABLES `sys_email_templates` WRITE;
/*!40000 ALTER TABLE `sys_email_templates` DISABLE KEYS */;
/*!40000 ALTER TABLE `sys_email_templates` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `sys_invoice_config`
--

DROP TABLE IF EXISTS `sys_invoice_config`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `sys_invoice_config` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `account_id` varchar(30) DEFAULT NULL,
  `logo` varchar(300) DEFAULT NULL,
  `company_name` varchar(300) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `bank_detail` text DEFAULT NULL,
  `footer_text` text DEFAULT NULL,
  `support_text` text DEFAULT NULL,
  `create_dt` datetime DEFAULT NULL,
  `update_dt` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `account_id` (`account_id`) USING BTREE,
  UNIQUE KEY `account_id_2` (`account_id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci ROW_FORMAT=COMPACT;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `sys_invoice_config`
--

LOCK TABLES `sys_invoice_config` WRITE;
/*!40000 ALTER TABLE `sys_invoice_config` DISABLE KEYS */;
/*!40000 ALTER TABLE `sys_invoice_config` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `sys_payment_credentials`
--

DROP TABLE IF EXISTS `sys_payment_credentials`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `sys_payment_credentials` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `account_id` varchar(30) DEFAULT NULL,
  `payment_method` varchar(30) DEFAULT NULL,
  `credentials` text DEFAULT NULL,
  `status` enum('Y','N') DEFAULT 'Y',
  PRIMARY KEY (`id`),
  UNIQUE KEY `account_id` (`account_id`,`payment_method`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=44 DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci ROW_FORMAT=COMPACT;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `sys_payment_credentials`
--

LOCK TABLES `sys_payment_credentials` WRITE;
/*!40000 ALTER TABLE `sys_payment_credentials` DISABLE KEYS */;
INSERT INTO `sys_payment_credentials` VALUES
(43,'SYSTEM','paypal','{\"business\":\"openvoips@gmail.com\"}','Y');
/*!40000 ALTER TABLE `sys_payment_credentials` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `sys_rule_options`
--

DROP TABLE IF EXISTS `sys_rule_options`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `sys_rule_options` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `option_id` varchar(100) NOT NULL,
  `option_name` varchar(100) NOT NULL,
  `option_group` varchar(50) NOT NULL,
  `status_id` enum('1','0') NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci ROW_FORMAT=COMPACT;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `sys_rule_options`
--

LOCK TABLES `sys_rule_options` WRITE;
/*!40000 ALTER TABLE `sys_rule_options` DISABLE KEYS */;
INSERT INTO `sys_rule_options` VALUES
(1,'ADDBALANCE','Add Balance','payment','1'),
(2,'ADDCREDIT','Add Credit','payment','1'),
(3,'REMOVEBALANCE','Refund Balance','payment','1'),
(4,'REMOVECREDIT','Reduce Credit','payment','1'),
(5,'daily-balance','Daily Email','notification','1'),
(6,'low-balance','Low Balance','notification','1');
/*!40000 ALTER TABLE `sys_rule_options` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `sys_sdr_terms`
--

DROP TABLE IF EXISTS `sys_sdr_terms`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `sys_sdr_terms` (
  `term_id` int(11) NOT NULL AUTO_INCREMENT,
  `term_group` varchar(30) NOT NULL,
  `term` varchar(30) NOT NULL,
  `display_text` varchar(255) NOT NULL,
  `cost_calculation_formula` varchar(10) NOT NULL,
  `service_id` varchar(30) DEFAULT NULL,
  PRIMARY KEY (`term_id`),
  UNIQUE KEY `term` (`term`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=17 DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci ROW_FORMAT=COMPACT;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `sys_sdr_terms`
--

LOCK TABLES `sys_sdr_terms` WRITE;
/*!40000 ALTER TABLE `sys_sdr_terms` DISABLE KEYS */;
INSERT INTO `sys_sdr_terms` VALUES
(1,'balance','ADDBALANCE','Payment Received','+',''),
(2,'balance','ADDCREDIT','Credit Added','',''),
(3,'balance','REMOVEBALANCE','Payment Refund','-',''),
(4,'balance','REMOVECREDIT','Credit Reduced','',''),
(5,'usage','DIDEXTRACHRENTAL','Extra Channels in DID/Line Charge','-',''),
(6,'usage','DIDRENTAL','Number Rental','-',''),
(7,'usage','DIDSETUP','Number Setup Charge','-',''),
(8,'usage','OUT','PSTN Calls Call usages','-',''),
(9,'usage','IN','DID Calls usages','-',''),
(10,'usage','DIDCANCEL','Number Cancellation','-',''),
(11,'opening','OPENINGBALANCE','Opening Balance','+',''),
(12,'usage','MOBILETOPUP','International Mobile Recharge','-',''),
(13,'usage','BUNDLECHARGES','Bundle Subscription','-',''),
(14,'balance','VOUCHER','Voucher Card Recharge','+',''),
(15,'usage','EXTENSION','SIP Phone','-',''),
(16,'usage','EXTENSIONCANCEL','SIP Phone cancelation','-','');
/*!40000 ALTER TABLE `sys_sdr_terms` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `sys_signup`
--

DROP TABLE IF EXISTS `sys_signup`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `sys_signup` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `signupkey` varchar(50) DEFAULT NULL,
  `tariff_id` varchar(30) DEFAULT NULL,
  `dialplan_id` varchar(30) DEFAULT NULL,
  `business_holder` enum('ADMIN','RESELLER1','RESELLER2','RESELLER3') DEFAULT NULL,
  `business_holder_account_id` varchar(30) DEFAULT NULL,
  `default_balance` double(16,6) DEFAULT NULL,
  `status_id` enum('0','1') DEFAULT '1',
  `signup_plan` varchar(150) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci ROW_FORMAT=COMPACT;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `sys_signup`
--

LOCK TABLES `sys_signup` WRITE;
/*!40000 ALTER TABLE `sys_signup` DISABLE KEYS */;
/*!40000 ALTER TABLE `sys_signup` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `sys_sitesetup`
--

DROP TABLE IF EXISTS `sys_sitesetup`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `sys_sitesetup` (
  `sitesetup_id` int(11) NOT NULL AUTO_INCREMENT,
  `site_name` varchar(500) DEFAULT NULL,
  `mail_sent_from` varchar(500) DEFAULT NULL,
  `mail_sent_to` varchar(500) DEFAULT NULL,
  `admin_logo` varchar(500) DEFAULT NULL,
  `invoice_count` int(11) DEFAULT 1,
  `default_currency` varchar(10) DEFAULT 'USD',
  PRIMARY KEY (`sitesetup_id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci ROW_FORMAT=COMPACT;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `sys_sitesetup`
--

LOCK TABLES `sys_sitesetup` WRITE;
/*!40000 ALTER TABLE `sys_sitesetup` DISABLE KEYS */;
INSERT INTO `sys_sitesetup` VALUES
(1,NULL,NULL,NULL,NULL,1,'USD');
/*!40000 ALTER TABLE `sys_sitesetup` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `sys_smtp_config`
--

DROP TABLE IF EXISTS `sys_smtp_config`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `sys_smtp_config` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `account_id` varchar(30) DEFAULT '',
  `smtp_config_id` varchar(200) DEFAULT NULL,
  `smtp_auth` enum('0','1') DEFAULT NULL,
  `smtp_secure` enum('SSL','TSL') DEFAULT NULL,
  `smtp_host` varchar(100) DEFAULT NULL,
  `smtp_port` varchar(30) DEFAULT NULL,
  `smtp_username` varchar(30) DEFAULT NULL,
  `smtp_password` varchar(30) DEFAULT NULL,
  `smtp_from` varchar(100) DEFAULT NULL,
  `smtp_from_name` varchar(30) DEFAULT NULL,
  `smtp_xmailer` varchar(100) DEFAULT NULL,
  `smtp_host_name` varchar(100) DEFAULT NULL,
  `created_by` varchar(30) NOT NULL,
  `updated_by` varchar(30) NOT NULL,
  `created_dt` datetime NOT NULL,
  `updated_dt` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `smtp_config_id` (`smtp_config_id`) USING BTREE,
  UNIQUE KEY `smtp_config` (`smtp_config_id`,`account_id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci ROW_FORMAT=COMPACT;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `sys_smtp_config`
--

LOCK TABLES `sys_smtp_config` WRITE;
/*!40000 ALTER TABLE `sys_smtp_config` DISABLE KEYS */;
/*!40000 ALTER TABLE `sys_smtp_config` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `sys_system_config`
--

DROP TABLE IF EXISTS `sys_system_config`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `sys_system_config` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `default_variable` varchar(50) DEFAULT NULL,
  `values` varchar(500) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `default_variable` (`default_variable`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=18 DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci ROW_FORMAT=COMPACT;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `sys_system_config`
--

LOCK TABLES `sys_system_config` WRITE;
/*!40000 ALTER TABLE `sys_system_config` DISABLE KEYS */;
INSERT INTO `sys_system_config` VALUES
(1,'SITE_SUBDOMAIN','UCM'),
(2,'SITE_NAME','UCM CRM Portal'),
(3,'SITE_FULL_NAME','UCM Billing Solution'),
(4,'SDR_API_URL','https://localhost/portal/api/sdrapi.php'),
(5,'LOGO_IMAGE','logo.png'),
(6,'MOMENT_TIMEZONE','Asia/Kolkata'),
(7,'CUSTOMERCODEPREFIX','STC'),
(8,'RESELLERCODEPREFIX','STR'),
(9,'CUSTOMERCOMPANY','SureTel CC'),
(10,'SITE_MAIL_FROM','sajjanmallik@voxvalley.com'),
(11,'SITE_MAIL_TO','sajjanmallik@voxvalley.com'),
(12,'CREDIT_MAIL_TO','sajjanmallik@voxvalley.com'),
(13,'PAYPAL_LINK','https://www.paypal.com/cgi-bin/webscr'),
(14,'OV_SUPPORT_SUBJECTL','TICKET'),
(15,'OV_SUPPORT_URL','http://localhost'),
(16,'SITE_TITEL','UCM  Billing Solution'),
(17,'LB','92.42.108.41');
/*!40000 ALTER TABLE `sys_system_config` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `sys_system_config_default`
--

DROP TABLE IF EXISTS `sys_system_config_default`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `sys_system_config_default` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `sys_def` varchar(50) DEFAULT NULL,
  `sys_def_values` varchar(30) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci ROW_FORMAT=COMPACT;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `sys_system_config_default`
--

LOCK TABLES `sys_system_config_default` WRITE;
/*!40000 ALTER TABLE `sys_system_config_default` DISABLE KEYS */;
/*!40000 ALTER TABLE `sys_system_config_default` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `tariff`
--

DROP TABLE IF EXISTS `tariff`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `tariff` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `tariff_id` varchar(30) DEFAULT NULL,
  `tariff_name` varchar(30) DEFAULT NULL,
  `tariff_currency_id` varchar(10) DEFAULT 'USD',
  `tariff_status` enum('1','0') DEFAULT '1',
  `tariff_description` varchar(50) DEFAULT NULL,
  `tariff_type` enum('CARRIER','CUSTOMER') NOT NULL DEFAULT 'CARRIER',
  `account_id` varchar(30) NOT NULL,
  `package_option` enum('1','0') DEFAULT '0',
  `monthly_charges` double DEFAULT 0,
  `bundle_option` enum('1','0') DEFAULT '0',
  `bundle1_type` enum('MINUTE','COST') DEFAULT 'MINUTE',
  `bundle1_value` double(12,6) DEFAULT NULL,
  `bundle2_type` enum('MINUTE','COST') DEFAULT 'MINUTE',
  `bundle2_value` double(12,6) DEFAULT NULL,
  `bundle3_type` enum('MINUTE','COST') DEFAULT 'MINUTE',
  `bundle3_value` double(12,6) DEFAULT NULL,
  `created_by` varchar(30) DEFAULT NULL,
  `updated_by` varchar(30) NOT NULL,
  `create_dt` datetime DEFAULT NULL,
  `update_dt` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `tariff_id_name` (`tariff_id`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci ROW_FORMAT=COMPACT;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tariff`
--

LOCK TABLES `tariff` WRITE;
/*!40000 ALTER TABLE `tariff` DISABLE KEYS */;
INSERT INTO `tariff` VALUES
(1,'BUTA58','Buy Tariff','USD','1','','CARRIER','SYSTEM','0',0,'0','MINUTE',NULL,'MINUTE',NULL,'MINUTE',NULL,'UA000345178','','2025-10-02 15:38:50','2025-10-02 15:38:50'),
(2,'SATA45','Sale Tariff','USD','1','','CUSTOMER','SYSTEM','0',0,'0','MINUTE',NULL,'MINUTE',NULL,'MINUTE',NULL,'UA000345178','','2025-10-02 15:39:03','2025-10-02 15:39:03');
/*!40000 ALTER TABLE `tariff` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `tariff_ratecard_map`
--

DROP TABLE IF EXISTS `tariff_ratecard_map`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `tariff_ratecard_map` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `ratecard_id` varchar(30) DEFAULT NULL,
  `tariff_id` varchar(30) DEFAULT NULL,
  `start_day` int(11) DEFAULT NULL,
  `start_time` varchar(8) DEFAULT '00:00:00',
  `end_day` int(11) DEFAULT NULL,
  `end_time` varchar(8) DEFAULT '24:00:00',
  `priority` int(11) DEFAULT 1,
  `status` enum('1','0') DEFAULT '1',
  `ratecard_for` enum('INCOMING','OUTGOING') DEFAULT 'OUTGOING',
  `account_id` varchar(30) DEFAULT NULL,
  `created_by` varchar(30) DEFAULT NULL,
  `updated_by` varchar(30) DEFAULT NULL,
  `created_dt` datetime DEFAULT NULL,
  `updated_dt` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `ratecard_id` (`ratecard_id`) USING BTREE,
  KEY `tariff_id` (`tariff_id`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci ROW_FORMAT=COMPACT;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tariff_ratecard_map`
--

LOCK TABLES `tariff_ratecard_map` WRITE;
/*!40000 ALTER TABLE `tariff_ratecard_map` DISABLE KEYS */;
INSERT INTO `tariff_ratecard_map` VALUES
(1,'SAOU43','SATA45',0,'00:00:00',6,'23:59:59',1,'1','OUTGOING',NULL,NULL,NULL,NULL,NULL),
(2,'SAIN46','SATA45',0,'00:00:00',6,'23:59:59',1,'1','INCOMING',NULL,NULL,NULL,NULL,NULL),
(3,'CARR30','BUTA58',0,'00:00:00',6,'23:59:59',1,'1','OUTGOING',NULL,NULL,NULL,NULL,NULL),
(4,'CARR38','BUTA58',0,'00:00:00',6,'23:59:59',1,'1','INCOMING',NULL,NULL,NULL,NULL,NULL);
/*!40000 ALTER TABLE `tariff_ratecard_map` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ticket_assigned_to`
--

DROP TABLE IF EXISTS `ticket_assigned_to`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `ticket_assigned_to` (
  `assigned_to_id` int(11) NOT NULL AUTO_INCREMENT,
  `assigned_to_name` varchar(50) NOT NULL,
  PRIMARY KEY (`assigned_to_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci ROW_FORMAT=COMPACT;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ticket_assigned_to`
--

LOCK TABLES `ticket_assigned_to` WRITE;
/*!40000 ALTER TABLE `ticket_assigned_to` DISABLE KEYS */;
/*!40000 ALTER TABLE `ticket_assigned_to` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ticket_attachments`
--

DROP TABLE IF EXISTS `ticket_attachments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `ticket_attachments` (
  `attachment_id` int(11) NOT NULL AUTO_INCREMENT,
  `ticket_id` int(11) NOT NULL,
  `file_name` varchar(100) NOT NULL,
  `file_name_display` varchar(100) NOT NULL,
  PRIMARY KEY (`attachment_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci ROW_FORMAT=COMPACT;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ticket_attachments`
--

LOCK TABLES `ticket_attachments` WRITE;
/*!40000 ALTER TABLE `ticket_attachments` DISABLE KEYS */;
/*!40000 ALTER TABLE `ticket_attachments` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ticket_categories`
--

DROP TABLE IF EXISTS `ticket_categories`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `ticket_categories` (
  `category_id` int(11) NOT NULL AUTO_INCREMENT,
  `category_parent_id` int(11) NOT NULL,
  `category_name` varchar(50) NOT NULL,
  `status` enum('Y','N') NOT NULL,
  PRIMARY KEY (`category_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci ROW_FORMAT=COMPACT;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ticket_categories`
--

LOCK TABLES `ticket_categories` WRITE;
/*!40000 ALTER TABLE `ticket_categories` DISABLE KEYS */;
/*!40000 ALTER TABLE `ticket_categories` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `tickets`
--

DROP TABLE IF EXISTS `tickets`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `tickets` (
  `ticket_id` int(11) NOT NULL AUTO_INCREMENT,
  `parent_id` int(11) NOT NULL,
  `ticket_number` varchar(20) NOT NULL,
  `subject` varchar(255) NOT NULL,
  `content` text NOT NULL,
  `account_id` varchar(30) NOT NULL,
  `company_name` varchar(100) NOT NULL,
  `category_id` int(11) DEFAULT NULL,
  `assigned_to_id` int(11) DEFAULT NULL,
  `assigned_to_user_id` varchar(30) NOT NULL,
  `assigned_to_user_name` varchar(100) NOT NULL,
  `status` enum('open','closed','assigned','working','waiting-confirmation','not-fixed') NOT NULL DEFAULT 'open',
  `hide_from_customer` enum('Y','N') NOT NULL DEFAULT 'N',
  `created_by_ip` varchar(30) NOT NULL,
  `created_by` varchar(30) NOT NULL,
  `created_by_name` varchar(30) NOT NULL,
  `create_date` timestamp NOT NULL DEFAULT current_timestamp(),
  `close_date` datetime DEFAULT NULL,
  `author_name` varchar(30) NOT NULL,
  `author_email` varchar(50) NOT NULL,
  `author_email_subscribe` enum('Y','N') NOT NULL DEFAULT 'N',
  PRIMARY KEY (`ticket_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci ROW_FORMAT=COMPACT;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tickets`
--

LOCK TABLES `tickets` WRITE;
/*!40000 ALTER TABLE `tickets` DISABLE KEYS */;
/*!40000 ALTER TABLE `tickets` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `tiers`
--

DROP TABLE IF EXISTS `tiers`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `tiers` (
  `agent_code` varchar(255) DEFAULT NULL,
  `queue` varchar(255) DEFAULT NULL,
  `agent` varchar(255) DEFAULT NULL,
  `state` varchar(255) DEFAULT 'Ready',
  `account_id` varchar(30) NOT NULL,
  `level` int(11) NOT NULL DEFAULT 1,
  `position` int(11) NOT NULL DEFAULT 1,
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `extension_no` varchar(30) DEFAULT NULL,
  `extension_id` varchar(30) DEFAULT NULL,
  `extension_name` varchar(50) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `agent_code_2` (`agent_code`,`queue`) USING BTREE,
  KEY `agent` (`agent`) USING BTREE,
  KEY `queue` (`queue`) USING BTREE,
  KEY `agent_code` (`agent_code`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci ROW_FORMAT=COMPACT;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tiers`
--

LOCK TABLES `tiers` WRITE;
/*!40000 ALTER TABLE `tiers` DISABLE KEYS */;
INSERT INTO `tiers` VALUES
('1001','QDEMOQUEU9TQE0NVF400','C18tA02m','Ready','OV500',1,1,2,'1001','EOVHEY7900','OV Help');
/*!40000 ALTER TABLE `tiers` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `timeconditions`
--

DROP TABLE IF EXISTS `timeconditions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `timeconditions` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `account_id` varchar(30) NOT NULL,
  `timeconditions_id` varchar(30) DEFAULT NULL,
  `timeconditions_name` varchar(50) DEFAULT NULL,
  `timeconditions_number` int(11) DEFAULT NULL,
  `route` varchar(30) DEFAULT 'HANGUP',
  `route_endpoint` varchar(50) DEFAULT NULL,
  `timeconditions_desc` varchar(1000) DEFAULT NULL,
  `status_id` int(11) DEFAULT 1,
  `cancelled_on` datetime DEFAULT NULL,
  `created_by` int(10) unsigned NOT NULL,
  `created_dt` datetime NOT NULL,
  `modified_by` int(10) unsigned DEFAULT NULL,
  `modified_dt` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `timeconditions_id` (`timeconditions_id`) USING BTREE,
  KEY `account_id` (`account_id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci ROW_FORMAT=COMPACT;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `timeconditions`
--

LOCK TABLES `timeconditions` WRITE;
/*!40000 ALTER TABLE `timeconditions` DISABLE KEYS */;
/*!40000 ALTER TABLE `timeconditions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `timeconditions_rules`
--

DROP TABLE IF EXISTS `timeconditions_rules`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `timeconditions_rules` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `account_id` varchar(30) NOT NULL,
  `timeconditions_id` varchar(30) DEFAULT NULL,
  `tc_rule` enum('IN','OUT') DEFAULT 'IN',
  `tc_dayofweek` int(10) unsigned NOT NULL,
  `start_time` varchar(5) NOT NULL,
  `end_time` varchar(5) NOT NULL,
  `route` varchar(30) DEFAULT 'HANGUP',
  `route_endpoint` varchar(50) DEFAULT NULL,
  `status_id` int(11) DEFAULT NULL,
  `cancelled_on` datetime DEFAULT NULL,
  `created_by` int(10) unsigned NOT NULL,
  `created_dt` datetime NOT NULL,
  `modified_by` int(10) unsigned DEFAULT NULL,
  `modified_dt` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `account_id` (`account_id`) USING BTREE,
  KEY `timeconditions_id` (`timeconditions_id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci ROW_FORMAT=COMPACT;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `timeconditions_rules`
--

LOCK TABLES `timeconditions_rules` WRITE;
/*!40000 ALTER TABLE `timeconditions_rules` DISABLE KEYS */;
/*!40000 ALTER TABLE `timeconditions_rules` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `usa_area_codes`
--

DROP TABLE IF EXISTS `usa_area_codes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `usa_area_codes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `area_code` varchar(50) DEFAULT NULL,
  `city` varchar(50) DEFAULT NULL,
  `state` varchar(50) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci ROW_FORMAT=COMPACT;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `usa_area_codes`
--

LOCK TABLES `usa_area_codes` WRITE;
/*!40000 ALTER TABLE `usa_area_codes` DISABLE KEYS */;
/*!40000 ALTER TABLE `usa_area_codes` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `user_audit_trails`
--

DROP TABLE IF EXISTS `user_audit_trails`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `user_audit_trails` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `event` enum('insert','update','delete') NOT NULL,
  `table_name` varchar(128) NOT NULL,
  `old_values` text DEFAULT NULL,
  `new_values` text NOT NULL,
  `url` varchar(255) NOT NULL,
  `name` varchar(128) NOT NULL,
  `ip_address` varchar(45) NOT NULL,
  `user_agent` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci ROW_FORMAT=COMPACT;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `user_audit_trails`
--

LOCK TABLES `user_audit_trails` WRITE;
/*!40000 ALTER TABLE `user_audit_trails` DISABLE KEYS */;
/*!40000 ALTER TABLE `user_audit_trails` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `user_type_permissions`
--

DROP TABLE IF EXISTS `user_type_permissions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `user_type_permissions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_type` varchar(50) NOT NULL,
  `permissions` text NOT NULL,
  `label` varchar(50) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci ROW_FORMAT=COMPACT;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `user_type_permissions`
--

LOCK TABLES `user_type_permissions` WRITE;
/*!40000 ALTER TABLE `user_type_permissions` DISABLE KEYS */;
INSERT INTO `user_type_permissions` VALUES
(1,'RESELLER','a:6:{s:8:\"reseller\";a:4:{i:0;s:4:\"view\";i:1;s:3:\"add\";i:2;s:4:\"edit\";i:3;s:6:\"delete\";}s:8:\"customer\";a:4:{i:0;s:4:\"view\";i:1;s:3:\"add\";i:2;s:4:\"edit\";i:3;s:6:\"delete\";}s:8:\"ratecard\";a:5:{i:0;s:4:\"view\";i:1;s:3:\"add\";i:2;s:4:\"edit\";i:3;s:6:\"delete\";i:4;s:6:\"upload\";}s:4:\"rate\";a:4:{i:0;s:4:\"view\";i:1;s:3:\"add\";i:2;s:4:\"edit\";i:3;s:6:\"delete\";}s:6:\"tariff\";a:4:{i:0;s:4:\"view\";i:1;s:3:\"add\";i:2;s:4:\"edit\";i:3;s:6:\"delete\";}s:7:\"reports\";a:9:{i:0;s:3:\"cdr\";i:1;s:10:\"fail_calls\";i:2;s:11:\"call_report\";i:3;s:12:\"report_topup\";i:4;s:20:\"report_topup_monthly\";i:5;s:22:\"customer_topup_summery\";i:6;s:18:\"report_daily_sales\";i:7;s:26:\"report_daily_sales_monthly\";i:8;s:22:\"customer_sales_summery\";}}',NULL),
(2,'SUBADMIN','a:11:{s:4:\"user\";a:2:{i:0;s:4:\"view\";i:1;s:3:\"add\";}s:8:\"reseller\";a:2:{i:0;s:4:\"view\";i:1;s:6:\"delete\";}s:7:\"carrier\";a:2:{i:0;s:4:\"view\";i:1;s:3:\"add\";}s:7:\"routing\";a:4:{i:0;s:4:\"view\";i:1;s:3:\"add\";i:2;s:4:\"edit\";i:3;s:6:\"delete\";}s:8:\"dialplan\";a:4:{i:0;s:4:\"view\";i:1;s:3:\"add\";i:2;s:4:\"edit\";i:3;s:6:\"delete\";}s:8:\"ratecard\";a:5:{i:0;s:4:\"view\";i:1;s:3:\"add\";i:2;s:4:\"edit\";i:3;s:6:\"delete\";i:4;s:6:\"upload\";}s:4:\"rate\";a:4:{i:0;s:4:\"view\";i:1;s:3:\"add\";i:2;s:4:\"edit\";i:3;s:6:\"delete\";}s:6:\"tariff\";a:4:{i:0;s:4:\"view\";i:1;s:3:\"add\";i:2;s:4:\"edit\";i:3;s:6:\"delete\";}s:8:\"provider\";a:3:{i:0;s:4:\"view\";i:1;s:3:\"add\";i:2;s:4:\"edit\";}s:7:\"service\";a:2:{i:0;s:4:\"view\";i:1;s:3:\"add\";}s:7:\"reports\";a:7:{i:0;s:3:\"cdr\";i:1;s:10:\"fail_calls\";i:2;s:4:\"live\";i:3;s:17:\"analytics_carrier\";i:4;s:18:\"accounting_billing\";i:5;s:7:\"summary\";i:6;s:11:\"call_report\";}}',NULL),
(3,'CUSTOMER','a:4:{s:8:\"customer\";a:2:{i:0;s:4:\"view\";i:1;s:7:\"cliedit\";}s:7:\"routing\";a:4:{i:0;s:4:\"view\";i:1;s:3:\"add\";i:2;s:4:\"edit\";i:3;s:6:\"delete\";}s:7:\"service\";a:1:{i:0;s:4:\"view\";}s:7:\"reports\";a:6:{i:0;s:3:\"cdr\";i:1;s:10:\"fail_calls\";i:2;s:4:\"live\";i:3;s:9:\"statement\";i:4;s:9:\"myinvoice\";i:5;s:16:\"report_statement\";}}',NULL),
(4,'ACCOUNTS','a:8:{s:8:\"reseller\";a:4:{i:0;s:4:\"view\";i:1;s:3:\"add\";i:2;s:4:\"edit\";i:3;s:6:\"delete\";}s:8:\"customer\";a:4:{i:0;s:4:\"view\";i:1;s:3:\"add\";i:2;s:4:\"edit\";i:3;s:6:\"delete\";}s:7:\"carrier\";a:1:{i:0;s:6:\"delete\";}s:8:\"ratecard\";a:5:{i:0;s:4:\"view\";i:1;s:3:\"add\";i:2;s:4:\"edit\";i:3;s:6:\"delete\";i:4;s:6:\"upload\";}s:4:\"rate\";a:4:{i:0;s:4:\"view\";i:1;s:3:\"add\";i:2;s:4:\"edit\";i:3;s:6:\"delete\";}s:6:\"tariff\";a:4:{i:0;s:4:\"view\";i:1;s:3:\"add\";i:2;s:4:\"edit\";i:3;s:6:\"delete\";}s:7:\"service\";a:4:{i:0;s:4:\"view\";i:1;s:3:\"add\";i:2;s:4:\"edit\";i:3;s:6:\"delete\";}s:7:\"reports\";a:15:{i:0;s:3:\"cdr\";i:1;s:10:\"fail_calls\";i:2;s:5:\"monin\";i:3;s:8:\"CustQOSR\";i:4;s:12:\"monitCarrier\";i:5;s:17:\"analytics_carrier\";i:6;s:18:\"accounting_billing\";i:7;s:7:\"summary\";i:8;s:11:\"call_report\";i:9;s:12:\"report_topup\";i:10;s:20:\"report_topup_monthly\";i:11;s:22:\"customer_topup_summery\";i:12;s:18:\"report_daily_sales\";i:13;s:26:\"report_daily_sales_monthly\";i:14;s:22:\"customer_sales_summery\";}}',NULL),
(6,'RESELLERACCOUNT','a:3:{s:5:\"admin\";a:1:{i:0;s:4:\"view\";}s:8:\"reseller\";a:1:{i:0;s:4:\"view\";}s:8:\"customer\";a:1:{i:0;s:3:\"add\";}}',NULL),
(7,'RESELLERADMIN','a:11:{s:4:\"user\";a:4:{i:0;s:4:\"view\";i:1;s:3:\"add\";i:2;s:4:\"edit\";i:3;s:5:\"login\";}s:8:\"reseller\";a:6:{i:0;s:4:\"view\";i:1;s:3:\"add\";i:2;s:4:\"edit\";i:3;s:6:\"delete\";i:4;s:7:\"cliedit\";i:5;s:5:\"login\";}s:8:\"customer\";a:6:{i:0;s:4:\"view\";i:1;s:3:\"add\";i:2;s:4:\"edit\";i:3;s:6:\"delete\";i:4;s:7:\"cliedit\";i:5;s:5:\"login\";}s:7:\"carrier\";a:4:{i:0;s:4:\"view\";i:1;s:3:\"add\";i:2;s:4:\"edit\";i:3;s:6:\"delete\";}s:7:\"routing\";a:3:{i:0;s:4:\"view\";i:1;s:3:\"add\";i:2;s:4:\"edit\";}s:8:\"dialplan\";a:4:{i:0;s:4:\"view\";i:1;s:3:\"add\";i:2;s:4:\"edit\";i:3;s:6:\"delete\";}s:8:\"ratecard\";a:5:{i:0;s:4:\"view\";i:1;s:3:\"add\";i:2;s:4:\"edit\";i:3;s:6:\"delete\";i:4;s:6:\"upload\";}s:4:\"rate\";a:4:{i:0;s:4:\"view\";i:1;s:3:\"add\";i:2;s:4:\"edit\";i:3;s:6:\"delete\";}s:6:\"tariff\";a:4:{i:0;s:4:\"view\";i:1;s:3:\"add\";i:2;s:4:\"edit\";i:3;s:6:\"delete\";}s:6:\"bundle\";a:4:{i:0;s:4:\"view\";i:1;s:3:\"add\";i:2;s:4:\"edit\";i:3;s:6:\"delete\";}s:15:\"payment_gateway\";a:1:{i:0;s:3:\"add\";}}',NULL),
(8,'ACCOUNT','a:15:{s:4:\"user\";a:4:{i:0;s:4:\"view\";i:1;s:3:\"add\";i:2;s:4:\"edit\";i:3;s:5:\"login\";}s:8:\"reseller\";a:6:{i:0;s:4:\"view\";i:1;s:3:\"add\";i:2;s:4:\"edit\";i:3;s:6:\"delete\";i:4;s:7:\"cliedit\";i:5;s:5:\"login\";}s:8:\"customer\";a:6:{i:0;s:4:\"view\";i:1;s:3:\"add\";i:2;s:4:\"edit\";i:3;s:6:\"delete\";i:4;s:7:\"cliedit\";i:5;s:5:\"login\";}s:7:\"carrier\";a:4:{i:0;s:4:\"view\";i:1;s:3:\"add\";i:2;s:4:\"edit\";i:3;s:6:\"delete\";}s:7:\"routing\";a:4:{i:0;s:4:\"view\";i:1;s:3:\"add\";i:2;s:4:\"edit\";i:3;s:6:\"delete\";}s:8:\"dialplan\";a:4:{i:0;s:4:\"view\";i:1;s:3:\"add\";i:2;s:4:\"edit\";i:3;s:6:\"delete\";}s:8:\"ratecard\";a:5:{i:0;s:4:\"view\";i:1;s:3:\"add\";i:2;s:4:\"edit\";i:3;s:6:\"delete\";i:4;s:6:\"upload\";}s:4:\"rate\";a:4:{i:0;s:4:\"view\";i:1;s:3:\"add\";i:2;s:4:\"edit\";i:3;s:6:\"delete\";}s:6:\"tariff\";a:4:{i:0;s:4:\"view\";i:1;s:3:\"add\";i:2;s:4:\"edit\";i:3;s:6:\"delete\";}s:6:\"bundle\";a:4:{i:0;s:4:\"view\";i:1;s:3:\"add\";i:2;s:4:\"edit\";i:3;s:6:\"delete\";}s:8:\"provider\";a:4:{i:0;s:4:\"view\";i:1;s:3:\"add\";i:2;s:4:\"edit\";i:3;s:6:\"delete\";}s:7:\"service\";a:4:{i:0;s:4:\"view\";i:1;s:3:\"add\";i:2;s:4:\"edit\";i:3;s:6:\"delete\";}s:6:\"system\";a:1:{i:0;s:11:\"system_load\";}s:15:\"payment_gateway\";a:1:{i:0;s:3:\"add\";}s:7:\"reports\";a:21:{i:0;s:3:\"cdr\";i:1;s:10:\"fail_calls\";i:2;s:4:\"live\";i:3;s:5:\"monin\";i:4;s:8:\"CustQOSR\";i:5;s:12:\"monitCarrier\";i:6;s:17:\"analytics_carrier\";i:7;s:18:\"accounting_billing\";i:8;s:7:\"summary\";i:9;s:11:\"call_report\";i:10;s:12:\"report_topup\";i:11;s:20:\"report_topup_monthly\";i:12;s:22:\"customer_topup_summery\";i:13;s:18:\"report_daily_sales\";i:14;s:26:\"report_daily_sales_monthly\";i:15;s:22:\"customer_sales_summery\";i:16;s:9:\"statement\";i:17;s:9:\"myinvoice\";i:18;s:16:\"report_statement\";i:19;s:10:\"ProfitLoss\";i:20;s:8:\"CarrQOSR\";}}',NULL),
(9,'CUSTOMERADMIN','a:1:{s:7:\"reports\";a:3:{i:0;s:10:\"fail_calls\";i:1;s:16:\"report_statement\";i:2;s:3:\"cdr\";}}',NULL);
/*!40000 ALTER TABLE `user_type_permissions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` varchar(30) NOT NULL,
  `account_id` varchar(30) NOT NULL,
  `user_type` varchar(30) NOT NULL,
  `username` varchar(30) NOT NULL,
  `secret` varchar(30) NOT NULL,
  `name` varchar(100) NOT NULL,
  `emailaddress` varchar(100) NOT NULL,
  `phone` varchar(50) NOT NULL,
  `address` varchar(256) NOT NULL,
  `country_id` smallint(6) NOT NULL,
  `status_id` int(11) NOT NULL DEFAULT 1,
  `gcode` varchar(100) DEFAULT NULL,
  `create_dt` timestamp NOT NULL DEFAULT current_timestamp(),
  `create_by` varchar(30) NOT NULL,
  `update_dt` datetime DEFAULT NULL,
  `update_by` varchar(30) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `user_id` (`user_id`) USING BTREE,
  KEY `account_id` (`account_id`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci ROW_FORMAT=COMPACT;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users`
--

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
INSERT INTO `users` VALUES
(1,'UA000003333','SYSTEM','ADMIN','admin','123456','admin','openvoips,help@gmail.com','','',195,1,NULL,'2025-08-09 07:22:25','ADMIN',NULL,''),
(2,'UA000345178','SYSTEM','ADMIN','openvoips','123456','Openvoips Support','openvoips@gmail.com','','',100,1,NULL,'2025-08-09 07:24:41','UA000003333',NULL,''),
(3,'UC000003333','OV500','CUSTOMERADMIN','ovcustomer','Ov@500demo','Openvoips Technologies','openvoips.help@gmail.com','','',0,1,NULL,'2025-10-02 13:49:07','',NULL,''),
(4,'Ue000004114','OV500','EXTENSION','Hu219R2h','#pt$FK12A@','OV Help','','','',0,1,NULL,'2025-10-03 14:26:10','UC000003333',NULL,'');
/*!40000 ALTER TABLE `users` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `vendors`
--

DROP TABLE IF EXISTS `vendors`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `vendors` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `vendor_id` varchar(30) DEFAULT NULL,
  `vendor_name` varchar(30) DEFAULT NULL,
  `vendor_address` varchar(200) DEFAULT NULL,
  `vendor_emailid` varchar(100) NOT NULL,
  `currency_id` varchar(10) DEFAULT 'USD',
  `account_id` varchar(30) DEFAULT NULL,
  `created_by` varchar(30) DEFAULT NULL,
  `updated_by` varchar(30) DEFAULT NULL,
  `updated_dt` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp(),
  `created_dt` datetime DEFAULT NULL,
  `modify_by` varchar(30) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci ROW_FORMAT=COMPACT;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `vendors`
--

LOCK TABLES `vendors` WRITE;
/*!40000 ALTER TABLE `vendors` DISABLE KEYS */;
/*!40000 ALTER TABLE `vendors` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `version`
--

DROP TABLE IF EXISTS `version`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `version` (
  `table_name` varchar(32) NOT NULL,
  `table_version` int(10) unsigned NOT NULL DEFAULT 0,
  UNIQUE KEY `table_name_idx` (`table_name`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci ROW_FORMAT=COMPACT;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `version`
--

LOCK TABLES `version` WRITE;
/*!40000 ALTER TABLE `version` DISABLE KEYS */;
INSERT INTO `version` VALUES
('customer_devices',7);
/*!40000 ALTER TABLE `version` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `voicemail`
--

DROP TABLE IF EXISTS `voicemail`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `voicemail` (
  `vm_id` int(11) NOT NULL AUTO_INCREMENT,
  `account_id` varchar(60) NOT NULL,
  `vm_name` varchar(30) DEFAULT NULL,
  `vm_no` int(11) DEFAULT NULL,
  `mailbox` varchar(30) DEFAULT NULL,
  `vm_password` varchar(30) DEFAULT NULL,
  `no_of_vm` int(11) DEFAULT NULL,
  `no_of_vm_len` int(11) DEFAULT NULL,
  `send_email` tinyint(1) NOT NULL DEFAULT 0,
  `email_address` text DEFAULT NULL,
  `email_attach_file` tinyint(1) NOT NULL DEFAULT 0,
  `greetings_id` varchar(50) DEFAULT NULL,
  `status_id` int(11) DEFAULT NULL,
  `created_by` char(36) DEFAULT NULL,
  `created_dt` datetime NOT NULL,
  `modified_by` char(36) DEFAULT NULL,
  `modified_dt` datetime DEFAULT NULL,
  PRIMARY KEY (`vm_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci ROW_FORMAT=COMPACT;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `voicemail`
--

LOCK TABLES `voicemail` WRITE;
/*!40000 ALTER TABLE `voicemail` DISABLE KEYS */;
/*!40000 ALTER TABLE `voicemail` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `voicemail_msgs`
--

DROP TABLE IF EXISTS `voicemail_msgs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `voicemail_msgs` (
  `vm_id` int(11) NOT NULL AUTO_INCREMENT,
  `created_epoch` int(11) DEFAULT NULL,
  `read_epoch` int(11) DEFAULT NULL,
  `username` varchar(255) DEFAULT NULL,
  `domain` varchar(255) DEFAULT NULL,
  `uuid` varchar(255) DEFAULT NULL,
  `cid_name` varchar(255) DEFAULT NULL,
  `cid_number` varchar(255) DEFAULT NULL,
  `in_folder` varchar(255) DEFAULT NULL,
  `file_path` varchar(255) DEFAULT NULL,
  `message_len` int(11) DEFAULT NULL,
  `flags` varchar(255) DEFAULT NULL,
  `read_flags` varchar(255) DEFAULT NULL,
  `forwarded_by` varchar(255) DEFAULT NULL,
  `created_by` char(36) NOT NULL,
  `created_dt` datetime NOT NULL,
  `modified_by` char(36) DEFAULT NULL,
  `modified_dt` datetime DEFAULT NULL,
  PRIMARY KEY (`vm_id`),
  KEY `voicemail_msgs_idx1` (`created_epoch`) USING BTREE,
  KEY `voicemail_msgs_idx2` (`username`) USING BTREE,
  KEY `voicemail_msgs_idx3` (`domain`) USING BTREE,
  KEY `voicemail_msgs_idx4` (`uuid`) USING BTREE,
  KEY `voicemail_msgs_idx5` (`in_folder`) USING BTREE,
  KEY `voicemail_msgs_idx6` (`read_flags`) USING BTREE,
  KEY `voicemail_msgs_idx7` (`forwarded_by`) USING BTREE,
  KEY `voicemail_msgs_idx8` (`read_epoch`) USING BTREE,
  KEY `voicemail_msgs_idx9` (`flags`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci ROW_FORMAT=COMPACT;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `voicemail_msgs`
--

LOCK TABLES `voicemail_msgs` WRITE;
/*!40000 ALTER TABLE `voicemail_msgs` DISABLE KEYS */;
/*!40000 ALTER TABLE `voicemail_msgs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `voicemail_prefs`
--

DROP TABLE IF EXISTS `voicemail_prefs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `voicemail_prefs` (
  `username` varchar(255) DEFAULT NULL,
  `domain` varchar(255) DEFAULT NULL,
  `name_path` varchar(255) DEFAULT NULL,
  `greeting_path` varchar(255) DEFAULT NULL,
  `password` varchar(255) DEFAULT NULL,
  `created_by` char(36) NOT NULL,
  `created_dt` datetime NOT NULL,
  `modified_by` char(36) DEFAULT NULL,
  `modified_dt` datetime DEFAULT NULL,
  KEY `voicemail_prefs_idx1` (`username`) USING BTREE,
  KEY `voicemail_prefs_idx2` (`domain`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci ROW_FORMAT=COMPACT;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `voicemail_prefs`
--

LOCK TABLES `voicemail_prefs` WRITE;
/*!40000 ALTER TABLE `voicemail_prefs` DISABLE KEYS */;
/*!40000 ALTER TABLE `voicemail_prefs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `voucher`
--

DROP TABLE IF EXISTS `voucher`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `voucher` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `lot_id` varchar(30) DEFAULT NULL,
  `voucher_sr` varchar(50) DEFAULT NULL,
  `voucher_number` varchar(50) DEFAULT NULL,
  `voucher_status` enum('1','2','0') DEFAULT '1' COMMENT '1 active,2 =used,0inactive',
  `card_can_use_from` date DEFAULT NULL,
  `used_by` varchar(30) DEFAULT NULL,
  `account_id` varchar(30) DEFAULT NULL,
  `used_date` date DEFAULT NULL,
  `used_mode` enum('WEB','APP') DEFAULT NULL,
  `created_by` varchar(30) DEFAULT NULL,
  `created_date` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `voucher_number` (`voucher_number`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci ROW_FORMAT=COMPACT;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `voucher`
--

LOCK TABLES `voucher` WRITE;
/*!40000 ALTER TABLE `voucher` DISABLE KEYS */;
/*!40000 ALTER TABLE `voucher` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `voucher_lot`
--

DROP TABLE IF EXISTS `voucher_lot`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `voucher_lot` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `lot_id` varchar(30) DEFAULT NULL,
  `lot_name` varchar(50) DEFAULT NULL,
  `amount` double(10,2) DEFAULT NULL,
  `no_ofcard` int(11) DEFAULT 1,
  `lot_status` enum('1','0') DEFAULT NULL,
  `currency_id` varchar(10) DEFAULT 'USD',
  `card_length` int(11) DEFAULT 10,
  `created_by` varchar(30) DEFAULT NULL,
  `expiry_date` date DEFAULT NULL,
  `account_id` varchar(30) DEFAULT NULL,
  `created_date` date DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci ROW_FORMAT=COMPACT;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `voucher_lot`
--

LOCK TABLES `voucher_lot` WRITE;
/*!40000 ALTER TABLE `voucher_lot` DISABLE KEYS */;
/*!40000 ALTER TABLE `voucher_lot` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `webaccess_log`
--

DROP TABLE IF EXISTS `webaccess_log`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `webaccess_log` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `event` enum('track','insert','update','delete') NOT NULL DEFAULT 'track',
  `session_id` varchar(100) NOT NULL,
  `user_name` varchar(128) NOT NULL,
  `account_id` varchar(30) NOT NULL,
  `ip_address` varchar(45) NOT NULL,
  `remote_address` varchar(255) NOT NULL,
  `page_url` varchar(255) NOT NULL,
  `referrer_url` varchar(255) NOT NULL,
  `user_agent` varchar(255) NOT NULL,
  `ci_class_method` varchar(255) NOT NULL,
  `created_dt` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1400 DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci ROW_FORMAT=COMPACT;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `webaccess_log`
--

LOCK TABLES `webaccess_log` WRITE;
/*!40000 ALTER TABLE `webaccess_log` DISABLE KEYS */;
INSERT INTO `webaccess_log` VALUES
(1,'track','9vegrr7m64j93ijgfb9gs5gaqiqj2top','','','122.169.33.222','122.169.33.222','/','','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','login/index','2025-10-02 02:07:22'),
(2,'track','gp0c8sqaiio495kc6vlgrho5dtid6s2b','','','79.124.58.198','79.124.58.198','/','','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/78.0.3904.108 Safari/537.36','login/index','2025-10-02 02:10:10'),
(3,'track','nnji59vf0c4sh1h47u6btsphk7ajpdcg','','','122.169.33.222','122.169.33.222','/login','https://app.24ithub.com/','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','login/index','2025-10-02 02:11:57'),
(4,'track','nnji59vf0c4sh1h47u6btsphk7ajpdcg','Openvoips Support','SYSTEM','122.169.33.222','122.169.33.222','/dashboard','https://app.24ithub.com/login','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','dashboard/index','2025-10-02 02:11:57'),
(5,'track','nnji59vf0c4sh1h47u6btsphk7ajpdcg','Openvoips Support','SYSTEM','122.169.33.222','122.169.33.222','/dashboard/cpu','https://app.24ithub.com/dashboard','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','dashboard/cpu','2025-10-02 02:12:01'),
(6,'track','nnji59vf0c4sh1h47u6btsphk7ajpdcg','Openvoips Support','SYSTEM','122.169.33.222','122.169.33.222','/dashboard/memory','https://app.24ithub.com/dashboard','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','dashboard/memory','2025-10-02 02:12:02'),
(7,'track','nnji59vf0c4sh1h47u6btsphk7ajpdcg','Openvoips Support','SYSTEM','122.169.33.222','122.169.33.222','/dashboard/disk','https://app.24ithub.com/dashboard','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','dashboard/disk','2025-10-02 02:12:02'),
(8,'track','nnji59vf0c4sh1h47u6btsphk7ajpdcg','Openvoips Support','SYSTEM','122.169.33.222','122.169.33.222','/dashboard/cpu','https://app.24ithub.com/dashboard','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','dashboard/cpu','2025-10-02 02:12:07'),
(9,'track','nnji59vf0c4sh1h47u6btsphk7ajpdcg','Openvoips Support','SYSTEM','122.169.33.222','122.169.33.222','/dashboard/disk','https://app.24ithub.com/dashboard','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','dashboard/disk','2025-10-02 02:12:08'),
(10,'track','nnji59vf0c4sh1h47u6btsphk7ajpdcg','Openvoips Support','SYSTEM','122.169.33.222','122.169.33.222','/reports/monin','https://app.24ithub.com/dashboard','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','reports/monin','2025-10-02 02:12:08'),
(11,'track','nnji59vf0c4sh1h47u6btsphk7ajpdcg','Openvoips Support','SYSTEM','122.169.33.222','122.169.33.222','/reports/customers_status','https://app.24ithub.com/reports/monin','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','reports/customers_status','2025-10-02 02:12:09'),
(12,'track','nnji59vf0c4sh1h47u6btsphk7ajpdcg','Openvoips Support','SYSTEM','122.169.33.222','122.169.33.222','/reports/monin_data/N/N/N/N/Y/Y/N/N/N/Y','https://app.24ithub.com/reports/monin','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','reports/monin_data','2025-10-02 02:12:09'),
(13,'track','nnji59vf0c4sh1h47u6btsphk7ajpdcg','Openvoips Support','SYSTEM','122.169.33.222','122.169.33.222','/reports/customers_status','https://app.24ithub.com/reports/monin','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','reports/customers_status','2025-10-02 02:12:09'),
(14,'track','nnji59vf0c4sh1h47u6btsphk7ajpdcg','Openvoips Support','SYSTEM','122.169.33.222','122.169.33.222','/reports/monin_data/Y/Y/Y/Y/N/N/Y','https://app.24ithub.com/reports/monin','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','reports/monin_data','2025-10-02 02:12:09'),
(15,'track','nnji59vf0c4sh1h47u6btsphk7ajpdcg','Openvoips Support','SYSTEM','122.169.33.222','122.169.33.222','/reports/customers_status','https://app.24ithub.com/reports/monin','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','reports/customers_status','2025-10-02 02:12:09'),
(16,'track','nnji59vf0c4sh1h47u6btsphk7ajpdcg','Openvoips Support','SYSTEM','122.169.33.222','122.169.33.222','/reports/monin_data/N/N/N/N/N/N/N/Y/Y','https://app.24ithub.com/reports/monin','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','reports/monin_data','2025-10-02 02:12:09'),
(17,'track','nnji59vf0c4sh1h47u6btsphk7ajpdcg','Openvoips Support','SYSTEM','122.169.33.222','122.169.33.222','/reports/usage','https://app.24ithub.com/reports/monin','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','reports/usage','2025-10-02 02:12:09'),
(18,'track','nnji59vf0c4sh1h47u6btsphk7ajpdcg','Openvoips Support','SYSTEM','122.169.33.222','122.169.33.222','/reports/customers_status','https://app.24ithub.com/reports/monin','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','reports/customers_status','2025-10-02 02:12:09'),
(19,'track','nnji59vf0c4sh1h47u6btsphk7ajpdcg','Openvoips Support','SYSTEM','122.169.33.222','122.169.33.222','/carriers','https://app.24ithub.com/reports/monin','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','carriers/index','2025-10-02 02:12:12'),
(20,'track','nnji59vf0c4sh1h47u6btsphk7ajpdcg','Openvoips Support','SYSTEM','122.169.33.222','122.169.33.222','/dialplans','https://app.24ithub.com/carriers','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','dialplans/index','2025-10-02 02:12:14'),
(21,'track','nnji59vf0c4sh1h47u6btsphk7ajpdcg','Openvoips Support','SYSTEM','122.169.33.222','122.169.33.222','/dids','https://app.24ithub.com/dialplans','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','dids/index','2025-10-02 02:12:15'),
(22,'track','nnji59vf0c4sh1h47u6btsphk7ajpdcg','Openvoips Support','SYSTEM','122.169.33.222','122.169.33.222','/crs','https://app.24ithub.com/dids','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','crs/index','2025-10-02 02:12:17'),
(23,'track','nnji59vf0c4sh1h47u6btsphk7ajpdcg','Openvoips Support','SYSTEM','122.169.33.222','122.169.33.222','/crs','https://app.24ithub.com/dids','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','crs/index','2025-10-02 02:16:10'),
(24,'track','nnji59vf0c4sh1h47u6btsphk7ajpdcg','Openvoips Support','SYSTEM','122.169.33.222','122.169.33.222','/crs','https://app.24ithub.com/dids','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','crs/index','2025-10-02 02:16:11'),
(25,'track','nnji59vf0c4sh1h47u6btsphk7ajpdcg','Openvoips Support','SYSTEM','122.169.33.222','122.169.33.222','/','https://app.24ithub.com/crs','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','login/index','2025-10-02 02:16:16'),
(26,'track','nnji59vf0c4sh1h47u6btsphk7ajpdcg','Openvoips Support','SYSTEM','122.169.33.222','122.169.33.222','/dashboard','https://app.24ithub.com/crs','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','dashboard/index','2025-10-02 02:16:16'),
(27,'track','nnji59vf0c4sh1h47u6btsphk7ajpdcg','Openvoips Support','SYSTEM','122.169.33.222','122.169.33.222','/dashboard/disk','https://app.24ithub.com/dashboard','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','dashboard/disk','2025-10-02 02:16:17'),
(28,'track','nnji59vf0c4sh1h47u6btsphk7ajpdcg','Openvoips Support','SYSTEM','122.169.33.222','122.169.33.222','/dashboard/cpu','https://app.24ithub.com/dashboard','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','dashboard/cpu','2025-10-02 02:16:17'),
(29,'track','nnji59vf0c4sh1h47u6btsphk7ajpdcg','Openvoips Support','SYSTEM','122.169.33.222','122.169.33.222','/dashboard/memory','https://app.24ithub.com/dashboard','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','dashboard/memory','2025-10-02 02:16:18'),
(30,'track','nnji59vf0c4sh1h47u6btsphk7ajpdcg','Openvoips Support','SYSTEM','122.169.33.222','122.169.33.222','/profile','https://app.24ithub.com/dashboard','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','users/profile','2025-10-02 02:16:21'),
(31,'track','nnji59vf0c4sh1h47u6btsphk7ajpdcg','Openvoips Support','SYSTEM','122.169.33.222','122.169.33.222','/profile','https://app.24ithub.com/dashboard','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','users/profile','2025-10-02 02:16:25'),
(32,'track','4qpdacdrs9aa2r2jhjng29n4rg6k1hjk','','','54.161.142.123','54.161.142.123','/','','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/130.0.0.0 Safari/537.36','login/index','2025-10-02 02:16:43'),
(33,'track','qva6fclljsi2o36vngampoetspu9pn11','','','51.81.46.212','51.81.46.212','/','','','login/index','2025-10-02 02:18:42'),
(34,'track','aadh7pviebonr8qp899qtue4c6kpb8gb','Openvoips Support','SYSTEM','122.169.33.222','122.169.33.222','/logout','https://app.24ithub.com/profile','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','logout/index','2025-10-02 02:18:53'),
(35,'track','hq1c1i7iekvglmp6a2qp87j117f7hu3k','','','122.169.33.222','122.169.33.222','/','https://app.24ithub.com/profile','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','login/index','2025-10-02 02:18:53'),
(36,'track','hq1c1i7iekvglmp6a2qp87j117f7hu3k','','','122.169.33.222','122.169.33.222','/login','https://app.24ithub.com/','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','login/index','2025-10-02 02:18:55'),
(37,'track','hq1c1i7iekvglmp6a2qp87j117f7hu3k','Openvoips Support','SYSTEM','122.169.33.222','122.169.33.222','/dashboard','https://app.24ithub.com/login','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','dashboard/index','2025-10-02 02:18:55'),
(38,'track','hq1c1i7iekvglmp6a2qp87j117f7hu3k','Openvoips Support','SYSTEM','122.169.33.222','122.169.33.222','/dashboard/cpu','https://app.24ithub.com/dashboard','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','dashboard/cpu','2025-10-02 02:18:55'),
(39,'track','hq1c1i7iekvglmp6a2qp87j117f7hu3k','Openvoips Support','SYSTEM','122.169.33.222','122.169.33.222','/dashboard/disk','https://app.24ithub.com/dashboard','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','dashboard/disk','2025-10-02 02:18:56'),
(40,'track','hq1c1i7iekvglmp6a2qp87j117f7hu3k','Openvoips Support','SYSTEM','122.169.33.222','122.169.33.222','/dashboard/memory','https://app.24ithub.com/dashboard','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','dashboard/memory','2025-10-02 02:18:56'),
(41,'track','hq1c1i7iekvglmp6a2qp87j117f7hu3k','Openvoips Support','SYSTEM','122.169.33.222','122.169.33.222','/Billing/customerinvoice','https://app.24ithub.com/dashboard','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','Billing/customerinvoice','2025-10-02 02:19:02'),
(42,'track','hq1c1i7iekvglmp6a2qp87j117f7hu3k','Openvoips Support','SYSTEM','122.169.33.222','122.169.33.222','/dashboard/cpu','https://app.24ithub.com/dashboard','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','dashboard/cpu','2025-10-02 02:19:02'),
(43,'track','hq1c1i7iekvglmp6a2qp87j117f7hu3k','Openvoips Support','SYSTEM','122.169.33.222','122.169.33.222','/Billing/inConfig','https://app.24ithub.com/Billing/customerinvoice','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','Billing/inConfig','2025-10-02 02:19:04'),
(44,'track','hq1c1i7iekvglmp6a2qp87j117f7hu3k','Openvoips Support','SYSTEM','122.169.33.222','122.169.33.222','/crs','https://app.24ithub.com/Billing/inConfig','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','crs/index','2025-10-02 02:19:13'),
(45,'track','hq1c1i7iekvglmp6a2qp87j117f7hu3k','Openvoips Support','SYSTEM','122.169.33.222','122.169.33.222','/dialplans','https://app.24ithub.com/crs','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','dialplans/index','2025-10-02 02:19:17'),
(46,'track','hq1c1i7iekvglmp6a2qp87j117f7hu3k','Openvoips Support','SYSTEM','122.169.33.222','122.169.33.222','/ratecard','https://app.24ithub.com/dialplans','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','ratecard/index','2025-10-02 02:19:20'),
(47,'track','hq1c1i7iekvglmp6a2qp87j117f7hu3k','Openvoips Support','SYSTEM','122.169.33.222','122.169.33.222','/reports/monin','https://app.24ithub.com/ratecard','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','reports/monin','2025-10-02 02:19:23'),
(48,'track','hq1c1i7iekvglmp6a2qp87j117f7hu3k','Openvoips Support','SYSTEM','122.169.33.222','122.169.33.222','/reports/customers_status','https://app.24ithub.com/reports/monin','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','reports/customers_status','2025-10-02 02:19:24'),
(49,'track','hq1c1i7iekvglmp6a2qp87j117f7hu3k','Openvoips Support','SYSTEM','122.169.33.222','122.169.33.222','/reports/customers_status','https://app.24ithub.com/reports/monin','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','reports/customers_status','2025-10-02 02:19:24'),
(50,'track','hq1c1i7iekvglmp6a2qp87j117f7hu3k','Openvoips Support','SYSTEM','122.169.33.222','122.169.33.222','/reports/monin_data/N/N/N/N/Y/Y/N/N/N/Y','https://app.24ithub.com/reports/monin','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','reports/monin_data','2025-10-02 02:19:24'),
(51,'track','hq1c1i7iekvglmp6a2qp87j117f7hu3k','Openvoips Support','SYSTEM','122.169.33.222','122.169.33.222','/reports/monin_data/Y/Y/Y/Y/N/N/Y','https://app.24ithub.com/reports/monin','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','reports/monin_data','2025-10-02 02:19:24'),
(52,'track','hq1c1i7iekvglmp6a2qp87j117f7hu3k','Openvoips Support','SYSTEM','122.169.33.222','122.169.33.222','/reports/usage','https://app.24ithub.com/reports/monin','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','reports/usage','2025-10-02 02:19:24'),
(53,'track','hq1c1i7iekvglmp6a2qp87j117f7hu3k','Openvoips Support','SYSTEM','122.169.33.222','122.169.33.222','/reports/customers_status','https://app.24ithub.com/reports/monin','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','reports/customers_status','2025-10-02 02:19:24'),
(54,'track','hq1c1i7iekvglmp6a2qp87j117f7hu3k','Openvoips Support','SYSTEM','122.169.33.222','122.169.33.222','/reports/customers_status','https://app.24ithub.com/reports/monin','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','reports/customers_status','2025-10-02 02:19:24'),
(55,'track','hq1c1i7iekvglmp6a2qp87j117f7hu3k','Openvoips Support','SYSTEM','122.169.33.222','122.169.33.222','/reports/monin_data/N/N/N/N/N/N/N/Y/Y','https://app.24ithub.com/reports/monin','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','reports/monin_data','2025-10-02 02:19:24'),
(56,'track','hq1c1i7iekvglmp6a2qp87j117f7hu3k','Openvoips Support','SYSTEM','122.169.33.222','122.169.33.222','/reports/CustQOSR','https://app.24ithub.com/reports/monin','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','reports/CustQOSR','2025-10-02 02:19:29'),
(57,'track','hq1c1i7iekvglmp6a2qp87j117f7hu3k','Openvoips Support','SYSTEM','122.169.33.222','122.169.33.222','/rates','https://app.24ithub.com/reports/CustQOSR','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','rates/index','2025-10-02 02:19:32'),
(58,'track','hq1c1i7iekvglmp6a2qp87j117f7hu3k','Openvoips Support','SYSTEM','122.169.33.222','122.169.33.222','/routes','https://app.24ithub.com/rates','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','routes/index','2025-10-02 02:19:35'),
(59,'track','hq1c1i7iekvglmp6a2qp87j117f7hu3k','Openvoips Support','SYSTEM','122.169.33.222','122.169.33.222','/routes','https://app.24ithub.com/rates','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','routes/index','2025-10-02 02:20:36'),
(60,'track','hq1c1i7iekvglmp6a2qp87j117f7hu3k','Openvoips Support','SYSTEM','122.169.33.222','122.169.33.222','/routes','https://app.24ithub.com/rates','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','routes/index','2025-10-02 02:20:38'),
(61,'track','hq1c1i7iekvglmp6a2qp87j117f7hu3k','Openvoips Support','SYSTEM','122.169.33.222','122.169.33.222','/routes','https://app.24ithub.com/rates','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','routes/index','2025-10-02 02:20:40'),
(62,'track','hq1c1i7iekvglmp6a2qp87j117f7hu3k','Openvoips Support','SYSTEM','122.169.33.222','122.169.33.222','/routes','https://app.24ithub.com/rates','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','routes/index','2025-10-02 02:20:42'),
(63,'track','hq1c1i7iekvglmp6a2qp87j117f7hu3k','Openvoips Support','SYSTEM','122.169.33.222','122.169.33.222','/routes','https://app.24ithub.com/rates','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','routes/index','2025-10-02 02:20:43'),
(64,'track','hq1c1i7iekvglmp6a2qp87j117f7hu3k','Openvoips Support','SYSTEM','122.169.33.222','122.169.33.222','/routes','https://app.24ithub.com/rates','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','routes/index','2025-10-02 02:20:45'),
(65,'track','hq1c1i7iekvglmp6a2qp87j117f7hu3k','Openvoips Support','SYSTEM','122.169.33.222','122.169.33.222','/logout','https://app.24ithub.com/routes','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','logout/index','2025-10-02 02:20:48'),
(66,'track','oo5o84jslq1gfp7ft9iiea07mjcq1s8n','','','122.169.33.222','122.169.33.222','/','https://app.24ithub.com/routes','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','login/index','2025-10-02 02:20:48'),
(67,'track','oo5o84jslq1gfp7ft9iiea07mjcq1s8n','','','122.169.33.222','122.169.33.222','/login','https://app.24ithub.com/','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','login/index','2025-10-02 02:20:49'),
(68,'track','oo5o84jslq1gfp7ft9iiea07mjcq1s8n','Openvoips Support','SYSTEM','122.169.33.222','122.169.33.222','/dashboard','https://app.24ithub.com/login','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','dashboard/index','2025-10-02 02:20:49'),
(69,'track','oo5o84jslq1gfp7ft9iiea07mjcq1s8n','Openvoips Support','SYSTEM','122.169.33.222','122.169.33.222','/dashboard/disk','https://app.24ithub.com/dashboard','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','dashboard/disk','2025-10-02 02:20:50'),
(70,'track','oo5o84jslq1gfp7ft9iiea07mjcq1s8n','Openvoips Support','SYSTEM','122.169.33.222','122.169.33.222','/dashboard/cpu','https://app.24ithub.com/dashboard','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','dashboard/cpu','2025-10-02 02:20:50'),
(71,'track','oo5o84jslq1gfp7ft9iiea07mjcq1s8n','Openvoips Support','SYSTEM','122.169.33.222','122.169.33.222','/dashboard/memory','https://app.24ithub.com/dashboard','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','dashboard/memory','2025-10-02 02:20:51'),
(72,'track','oo5o84jslq1gfp7ft9iiea07mjcq1s8n','Openvoips Support','SYSTEM','122.169.33.222','122.169.33.222','/crs/payment/trace','https://app.24ithub.com/dashboard','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','payment/trace','2025-10-02 02:20:53'),
(73,'track','oo5o84jslq1gfp7ft9iiea07mjcq1s8n','Openvoips Support','SYSTEM','122.169.33.222','122.169.33.222','/blocknumbers','https://app.24ithub.com/crs/payment/trace','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','blocknumbers/index','2025-10-02 02:20:56'),
(74,'track','oo5o84jslq1gfp7ft9iiea07mjcq1s8n','Openvoips Support','SYSTEM','122.169.33.222','122.169.33.222','/blockcli','https://app.24ithub.com/blocknumbers','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','blockcli/index','2025-10-02 02:20:59'),
(75,'track','oo5o84jslq1gfp7ft9iiea07mjcq1s8n','Openvoips Support','SYSTEM','122.169.33.222','122.169.33.222','/currency/exc','https://app.24ithub.com/blockcli','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','currency/exc','2025-10-02 02:21:01'),
(76,'track','k1q4ib6mj63agqp8phf2pqgj7jbjcojo','','','79.124.58.198','79.124.58.198','/','','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/78.0.3904.108 Safari/537.36','login/index','2025-10-02 02:21:19'),
(77,'track','oo5o84jslq1gfp7ft9iiea07mjcq1s8n','Openvoips Support','SYSTEM','122.169.33.222','122.169.33.222','/currency/exc','https://app.24ithub.com/blockcli','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','currency/exc','2025-10-02 02:21:36'),
(78,'track','oo5o84jslq1gfp7ft9iiea07mjcq1s8n','Openvoips Support','SYSTEM','122.169.33.222','122.169.33.222','/pbx/extensionplan','https://app.24ithub.com/currency/exc','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','/index','2025-10-02 02:21:40'),
(79,'track','oo5o84jslq1gfp7ft9iiea07mjcq1s8n','Openvoips Support','SYSTEM','122.169.33.222','122.169.33.222','/dashboard','https://app.24ithub.com/currency/exc','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','dashboard/index','2025-10-02 02:21:40'),
(80,'track','oo5o84jslq1gfp7ft9iiea07mjcq1s8n','Openvoips Support','SYSTEM','122.169.33.222','122.169.33.222','/dashboard/cpu','https://app.24ithub.com/dashboard','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','dashboard/cpu','2025-10-02 02:21:41'),
(81,'track','oo5o84jslq1gfp7ft9iiea07mjcq1s8n','Openvoips Support','SYSTEM','122.169.33.222','122.169.33.222','/dashboard/memory','https://app.24ithub.com/dashboard','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','dashboard/memory','2025-10-02 02:21:42'),
(82,'track','oo5o84jslq1gfp7ft9iiea07mjcq1s8n','Openvoips Support','SYSTEM','122.169.33.222','122.169.33.222','/dashboard/disk','https://app.24ithub.com/dashboard','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','dashboard/disk','2025-10-02 02:21:42'),
(83,'track','oo5o84jslq1gfp7ft9iiea07mjcq1s8n','Openvoips Support','SYSTEM','122.169.33.222','122.169.33.222','/pbx/bundle','https://app.24ithub.com/dashboard','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','/index','2025-10-02 02:21:46'),
(84,'track','oo5o84jslq1gfp7ft9iiea07mjcq1s8n','Openvoips Support','SYSTEM','122.169.33.222','122.169.33.222','/dashboard','https://app.24ithub.com/dashboard','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','dashboard/index','2025-10-02 02:21:47'),
(85,'track','oo5o84jslq1gfp7ft9iiea07mjcq1s8n','Openvoips Support','SYSTEM','122.169.33.222','122.169.33.222','/dashboard/cpu','https://app.24ithub.com/dashboard','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','dashboard/cpu','2025-10-02 02:21:47'),
(86,'track','oo5o84jslq1gfp7ft9iiea07mjcq1s8n','Openvoips Support','SYSTEM','122.169.33.222','122.169.33.222','/dashboard/memory','https://app.24ithub.com/dashboard','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','dashboard/memory','2025-10-02 02:21:48'),
(87,'track','oo5o84jslq1gfp7ft9iiea07mjcq1s8n','Openvoips Support','SYSTEM','122.169.33.222','122.169.33.222','/dashboard/disk','https://app.24ithub.com/dashboard','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','dashboard/disk','2025-10-02 02:21:48'),
(88,'track','oo5o84jslq1gfp7ft9iiea07mjcq1s8n','Openvoips Support','SYSTEM','122.169.33.222','122.169.33.222','/dashboard/cpu','https://app.24ithub.com/dashboard','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','dashboard/cpu','2025-10-02 02:21:54'),
(89,'track','oo5o84jslq1gfp7ft9iiea07mjcq1s8n','Openvoips Support','SYSTEM','122.169.33.222','122.169.33.222','/dashboard/disk','https://app.24ithub.com/dashboard','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','dashboard/disk','2025-10-02 02:21:55'),
(90,'track','oo5o84jslq1gfp7ft9iiea07mjcq1s8n','Openvoips Support','SYSTEM','122.169.33.222','122.169.33.222','/dashboard/memory','https://app.24ithub.com/dashboard','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','dashboard/memory','2025-10-02 02:21:55'),
(91,'track','oo5o84jslq1gfp7ft9iiea07mjcq1s8n','Openvoips Support','SYSTEM','122.169.33.222','122.169.33.222','/pbx/crs','https://app.24ithub.com/dashboard','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','/index','2025-10-02 02:22:00'),
(92,'track','oo5o84jslq1gfp7ft9iiea07mjcq1s8n','Openvoips Support','SYSTEM','122.169.33.222','122.169.33.222','/dashboard','https://app.24ithub.com/dashboard','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','dashboard/index','2025-10-02 02:22:00'),
(93,'track','oo5o84jslq1gfp7ft9iiea07mjcq1s8n','Openvoips Support','SYSTEM','122.169.33.222','122.169.33.222','/dashboard/cpu','https://app.24ithub.com/dashboard','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','dashboard/cpu','2025-10-02 02:22:01'),
(94,'track','oo5o84jslq1gfp7ft9iiea07mjcq1s8n','Openvoips Support','SYSTEM','122.169.33.222','122.169.33.222','/dashboard/memory','https://app.24ithub.com/dashboard','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','dashboard/memory','2025-10-02 02:22:02'),
(95,'track','oo5o84jslq1gfp7ft9iiea07mjcq1s8n','Openvoips Support','SYSTEM','122.169.33.222','122.169.33.222','/dashboard/disk','https://app.24ithub.com/dashboard','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','dashboard/disk','2025-10-02 02:22:02'),
(96,'track','oo5o84jslq1gfp7ft9iiea07mjcq1s8n','Openvoips Support','SYSTEM','122.169.33.222','122.169.33.222','/dashboard/memory','https://app.24ithub.com/dashboard','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','dashboard/memory','2025-10-02 02:22:10'),
(97,'track','oo5o84jslq1gfp7ft9iiea07mjcq1s8n','Openvoips Support','SYSTEM','122.169.33.222','122.169.33.222','/dashboard/disk','https://app.24ithub.com/dashboard','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','dashboard/disk','2025-10-02 02:22:10'),
(98,'track','oo5o84jslq1gfp7ft9iiea07mjcq1s8n','Openvoips Support','SYSTEM','122.169.33.222','122.169.33.222','/dashboard/cpu','https://app.24ithub.com/dashboard','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','dashboard/cpu','2025-10-02 02:22:10'),
(99,'track','oo5o84jslq1gfp7ft9iiea07mjcq1s8n','Openvoips Support','SYSTEM','122.169.33.222','122.169.33.222','/dashboard/disk','https://app.24ithub.com/dashboard','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','dashboard/disk','2025-10-02 02:22:16'),
(100,'track','oo5o84jslq1gfp7ft9iiea07mjcq1s8n','Openvoips Support','SYSTEM','122.169.33.222','122.169.33.222','/dashboard/cpu','https://app.24ithub.com/dashboard','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','dashboard/cpu','2025-10-02 02:22:16'),
(101,'track','oo5o84jslq1gfp7ft9iiea07mjcq1s8n','Openvoips Support','SYSTEM','122.169.33.222','122.169.33.222','/dashboard/memory','https://app.24ithub.com/dashboard','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','dashboard/memory','2025-10-02 02:22:17'),
(102,'track','oo5o84jslq1gfp7ft9iiea07mjcq1s8n','Openvoips Support','SYSTEM','122.169.33.222','122.169.33.222','/dashboard/disk','https://app.24ithub.com/dashboard','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','dashboard/disk','2025-10-02 02:22:23'),
(103,'track','oo5o84jslq1gfp7ft9iiea07mjcq1s8n','Openvoips Support','SYSTEM','122.169.33.222','122.169.33.222','/dashboard/cpu','https://app.24ithub.com/dashboard','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','dashboard/cpu','2025-10-02 02:22:23'),
(104,'track','oo5o84jslq1gfp7ft9iiea07mjcq1s8n','Openvoips Support','SYSTEM','122.169.33.222','122.169.33.222','/dashboard/memory','https://app.24ithub.com/dashboard','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','dashboard/memory','2025-10-02 02:22:26'),
(105,'track','oo5o84jslq1gfp7ft9iiea07mjcq1s8n','Openvoips Support','SYSTEM','122.169.33.222','122.169.33.222','/dashboard/disk','https://app.24ithub.com/dashboard','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','dashboard/disk','2025-10-02 02:22:30'),
(106,'track','oo5o84jslq1gfp7ft9iiea07mjcq1s8n','Openvoips Support','SYSTEM','122.169.33.222','122.169.33.222','/dashboard/cpu','https://app.24ithub.com/dashboard','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','dashboard/cpu','2025-10-02 02:22:31'),
(107,'track','oo5o84jslq1gfp7ft9iiea07mjcq1s8n','Openvoips Support','SYSTEM','122.169.33.222','122.169.33.222','/dashboard/memory','https://app.24ithub.com/dashboard','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','dashboard/memory','2025-10-02 02:22:34'),
(108,'track','oo5o84jslq1gfp7ft9iiea07mjcq1s8n','Openvoips Support','SYSTEM','122.169.33.222','122.169.33.222','/dashboard/disk','https://app.24ithub.com/dashboard','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','dashboard/disk','2025-10-02 02:22:37'),
(109,'track','oo5o84jslq1gfp7ft9iiea07mjcq1s8n','Openvoips Support','SYSTEM','122.169.33.222','122.169.33.222','/dashboard/cpu','https://app.24ithub.com/dashboard','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','dashboard/cpu','2025-10-02 02:22:38'),
(110,'track','oo5o84jslq1gfp7ft9iiea07mjcq1s8n','Openvoips Support','SYSTEM','122.169.33.222','122.169.33.222','/dashboard/memory','https://app.24ithub.com/dashboard','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','dashboard/memory','2025-10-02 02:22:42'),
(111,'track','oo5o84jslq1gfp7ft9iiea07mjcq1s8n','Openvoips Support','SYSTEM','122.169.33.222','122.169.33.222','/dashboard/disk','https://app.24ithub.com/dashboard','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','dashboard/disk','2025-10-02 02:22:44'),
(112,'track','oo5o84jslq1gfp7ft9iiea07mjcq1s8n','Openvoips Support','SYSTEM','122.169.33.222','122.169.33.222','/dashboard/cpu','https://app.24ithub.com/dashboard','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','dashboard/cpu','2025-10-02 02:22:45'),
(113,'track','oo5o84jslq1gfp7ft9iiea07mjcq1s8n','Openvoips Support','SYSTEM','122.169.33.222','122.169.33.222','/dashboard/memory','https://app.24ithub.com/dashboard','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','dashboard/memory','2025-10-02 02:22:50'),
(114,'track','oo5o84jslq1gfp7ft9iiea07mjcq1s8n','Openvoips Support','SYSTEM','122.169.33.222','122.169.33.222','/dashboard/disk','https://app.24ithub.com/dashboard','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','dashboard/disk','2025-10-02 02:22:51'),
(115,'track','oo5o84jslq1gfp7ft9iiea07mjcq1s8n','Openvoips Support','SYSTEM','122.169.33.222','122.169.33.222','/dashboard/cpu','https://app.24ithub.com/dashboard','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','dashboard/cpu','2025-10-02 02:22:52'),
(116,'track','oo5o84jslq1gfp7ft9iiea07mjcq1s8n','Openvoips Support','SYSTEM','122.169.33.222','122.169.33.222','/dashboard/disk','https://app.24ithub.com/dashboard','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','dashboard/disk','2025-10-02 02:22:58'),
(117,'track','oo5o84jslq1gfp7ft9iiea07mjcq1s8n','Openvoips Support','SYSTEM','122.169.33.222','122.169.33.222','/dashboard/memory','https://app.24ithub.com/dashboard','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','dashboard/memory','2025-10-02 02:22:58'),
(118,'track','oo5o84jslq1gfp7ft9iiea07mjcq1s8n','Openvoips Support','SYSTEM','122.169.33.222','122.169.33.222','/dashboard/cpu','https://app.24ithub.com/dashboard','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','dashboard/cpu','2025-10-02 02:22:59'),
(119,'track','oo5o84jslq1gfp7ft9iiea07mjcq1s8n','Openvoips Support','SYSTEM','122.169.33.222','122.169.33.222','/dashboard/disk','https://app.24ithub.com/dashboard','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','dashboard/disk','2025-10-02 02:23:04'),
(120,'track','oo5o84jslq1gfp7ft9iiea07mjcq1s8n','Openvoips Support','SYSTEM','122.169.33.222','122.169.33.222','/dashboard/cpu','https://app.24ithub.com/dashboard','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','dashboard/cpu','2025-10-02 02:23:05'),
(121,'track','oo5o84jslq1gfp7ft9iiea07mjcq1s8n','Openvoips Support','SYSTEM','122.169.33.222','122.169.33.222','/dashboard/memory','https://app.24ithub.com/dashboard','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','dashboard/memory','2025-10-02 02:23:06'),
(122,'track','oo5o84jslq1gfp7ft9iiea07mjcq1s8n','Openvoips Support','SYSTEM','122.169.33.222','122.169.33.222','/dashboard/disk','https://app.24ithub.com/dashboard','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','dashboard/disk','2025-10-02 02:23:10'),
(123,'track','oo5o84jslq1gfp7ft9iiea07mjcq1s8n','Openvoips Support','SYSTEM','122.169.33.222','122.169.33.222','/dashboard/cpu','https://app.24ithub.com/dashboard','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','dashboard/cpu','2025-10-02 02:23:21'),
(124,'track','oo5o84jslq1gfp7ft9iiea07mjcq1s8n','Openvoips Support','SYSTEM','122.169.33.222','122.169.33.222','/dashboard/memory','https://app.24ithub.com/dashboard','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','dashboard/memory','2025-10-02 02:23:22'),
(125,'track','oo5o84jslq1gfp7ft9iiea07mjcq1s8n','Openvoips Support','SYSTEM','122.169.33.222','122.169.33.222','/dashboard/disk','https://app.24ithub.com/dashboard','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','dashboard/disk','2025-10-02 02:23:22'),
(126,'track','oo5o84jslq1gfp7ft9iiea07mjcq1s8n','Openvoips Support','SYSTEM','122.169.33.222','122.169.33.222','/dashboard/cpu','https://app.24ithub.com/dashboard','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','dashboard/cpu','2025-10-02 02:23:28'),
(127,'track','oo5o84jslq1gfp7ft9iiea07mjcq1s8n','Openvoips Support','SYSTEM','122.169.33.222','122.169.33.222','/dashboard/disk','https://app.24ithub.com/dashboard','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','dashboard/disk','2025-10-02 02:23:30'),
(128,'track','oo5o84jslq1gfp7ft9iiea07mjcq1s8n','Openvoips Support','SYSTEM','122.169.33.222','122.169.33.222','/dashboard/memory','https://app.24ithub.com/dashboard','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','dashboard/memory','2025-10-02 02:23:30'),
(129,'track','oo5o84jslq1gfp7ft9iiea07mjcq1s8n','Openvoips Support','SYSTEM','122.169.33.222','122.169.33.222','/dashboard/cpu','https://app.24ithub.com/dashboard','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','dashboard/cpu','2025-10-02 02:23:35'),
(130,'track','oo5o84jslq1gfp7ft9iiea07mjcq1s8n','Openvoips Support','SYSTEM','122.169.33.222','122.169.33.222','/dashboard/disk','https://app.24ithub.com/dashboard','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','dashboard/disk','2025-10-02 02:23:36'),
(131,'track','oo5o84jslq1gfp7ft9iiea07mjcq1s8n','Openvoips Support','SYSTEM','122.169.33.222','122.169.33.222','/dashboard/memory','https://app.24ithub.com/dashboard','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','dashboard/memory','2025-10-02 02:23:37'),
(132,'track','oo5o84jslq1gfp7ft9iiea07mjcq1s8n','Openvoips Support','SYSTEM','122.169.33.222','122.169.33.222','/dashboard/cpu','https://app.24ithub.com/dashboard','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','dashboard/cpu','2025-10-02 02:23:42'),
(133,'track','oo5o84jslq1gfp7ft9iiea07mjcq1s8n','Openvoips Support','SYSTEM','122.169.33.222','122.169.33.222','/dashboard/disk','https://app.24ithub.com/dashboard','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','dashboard/disk','2025-10-02 02:23:43'),
(134,'track','oo5o84jslq1gfp7ft9iiea07mjcq1s8n','Openvoips Support','SYSTEM','122.169.33.222','122.169.33.222','/dashboard/memory','https://app.24ithub.com/dashboard','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','dashboard/memory','2025-10-02 02:23:44'),
(135,'track','oo5o84jslq1gfp7ft9iiea07mjcq1s8n','Openvoips Support','SYSTEM','122.169.33.222','122.169.33.222','/dashboard/cpu','https://app.24ithub.com/dashboard','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','dashboard/cpu','2025-10-02 02:23:48'),
(136,'track','oo5o84jslq1gfp7ft9iiea07mjcq1s8n','Openvoips Support','SYSTEM','122.169.33.222','122.169.33.222','/dashboard/disk','https://app.24ithub.com/dashboard','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','dashboard/disk','2025-10-02 02:23:49'),
(137,'track','oo5o84jslq1gfp7ft9iiea07mjcq1s8n','Openvoips Support','SYSTEM','122.169.33.222','122.169.33.222','/dashboard/memory','https://app.24ithub.com/dashboard','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','dashboard/memory','2025-10-02 02:23:54'),
(138,'track','oo5o84jslq1gfp7ft9iiea07mjcq1s8n','Openvoips Support','SYSTEM','122.169.33.222','122.169.33.222','/dashboard/cpu','https://app.24ithub.com/dashboard','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','dashboard/cpu','2025-10-02 02:23:58'),
(139,'track','oo5o84jslq1gfp7ft9iiea07mjcq1s8n','Openvoips Support','SYSTEM','122.169.33.222','122.169.33.222','/dashboard/disk','https://app.24ithub.com/dashboard','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','dashboard/disk','2025-10-02 02:23:59'),
(140,'track','oo5o84jslq1gfp7ft9iiea07mjcq1s8n','Openvoips Support','SYSTEM','122.169.33.222','122.169.33.222','/dashboard/cpu','https://app.24ithub.com/dashboard','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','dashboard/cpu','2025-10-02 02:24:04'),
(141,'track','oo5o84jslq1gfp7ft9iiea07mjcq1s8n','Openvoips Support','SYSTEM','122.169.33.222','122.169.33.222','/dashboard/memory','https://app.24ithub.com/dashboard','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','dashboard/memory','2025-10-02 02:24:05'),
(142,'track','oo5o84jslq1gfp7ft9iiea07mjcq1s8n','Openvoips Support','SYSTEM','122.169.33.222','122.169.33.222','/dashboard/disk','https://app.24ithub.com/dashboard','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','dashboard/disk','2025-10-02 02:24:05'),
(143,'track','oo5o84jslq1gfp7ft9iiea07mjcq1s8n','Openvoips Support','SYSTEM','122.169.33.222','122.169.33.222','/dashboard/cpu','https://app.24ithub.com/dashboard','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','dashboard/cpu','2025-10-02 02:24:11'),
(144,'track','oo5o84jslq1gfp7ft9iiea07mjcq1s8n','Openvoips Support','SYSTEM','122.169.33.222','122.169.33.222','/dashboard/disk','https://app.24ithub.com/dashboard','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','dashboard/disk','2025-10-02 02:24:12'),
(145,'track','oo5o84jslq1gfp7ft9iiea07mjcq1s8n','Openvoips Support','SYSTEM','122.169.33.222','122.169.33.222','/dashboard/memory','https://app.24ithub.com/dashboard','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','dashboard/memory','2025-10-02 02:24:13'),
(146,'track','oo5o84jslq1gfp7ft9iiea07mjcq1s8n','Openvoips Support','SYSTEM','122.169.33.222','122.169.33.222','/dashboard/cpu','https://app.24ithub.com/dashboard','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','dashboard/cpu','2025-10-02 02:24:17'),
(147,'track','oo5o84jslq1gfp7ft9iiea07mjcq1s8n','Openvoips Support','SYSTEM','122.169.33.222','122.169.33.222','/dashboard/disk','https://app.24ithub.com/dashboard','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','dashboard/disk','2025-10-02 02:24:19'),
(148,'track','oo5o84jslq1gfp7ft9iiea07mjcq1s8n','Openvoips Support','SYSTEM','122.169.33.222','122.169.33.222','/dashboard/memory','https://app.24ithub.com/dashboard','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','dashboard/memory','2025-10-02 02:24:20'),
(149,'track','oo5o84jslq1gfp7ft9iiea07mjcq1s8n','Openvoips Support','SYSTEM','122.169.33.222','122.169.33.222','/dashboard/cpu','https://app.24ithub.com/dashboard','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','dashboard/cpu','2025-10-02 02:24:24'),
(150,'track','oo5o84jslq1gfp7ft9iiea07mjcq1s8n','Openvoips Support','SYSTEM','122.169.33.222','122.169.33.222','/dashboard/disk','https://app.24ithub.com/dashboard','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','dashboard/disk','2025-10-02 02:24:25'),
(151,'track','oo5o84jslq1gfp7ft9iiea07mjcq1s8n','Openvoips Support','SYSTEM','122.169.33.222','122.169.33.222','/dashboard/memory','https://app.24ithub.com/dashboard','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','dashboard/memory','2025-10-02 02:24:27'),
(152,'track','oo5o84jslq1gfp7ft9iiea07mjcq1s8n','Openvoips Support','SYSTEM','122.169.33.222','122.169.33.222','/dashboard/cpu','https://app.24ithub.com/dashboard','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','dashboard/cpu','2025-10-02 02:24:30'),
(153,'track','c9mjp1aupdp9q9974l0kadgkdntf9oed','','','185.244.104.2','185.244.104.2','/','http://173.208.52.222:443/','-','login/index','2025-10-02 02:24:31'),
(154,'track','oo5o84jslq1gfp7ft9iiea07mjcq1s8n','Openvoips Support','SYSTEM','122.169.33.222','122.169.33.222','/dashboard/disk','https://app.24ithub.com/dashboard','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','dashboard/disk','2025-10-02 02:24:32'),
(155,'track','oo5o84jslq1gfp7ft9iiea07mjcq1s8n','Openvoips Support','SYSTEM','122.169.33.222','122.169.33.222','/dashboard/memory','https://app.24ithub.com/dashboard','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','dashboard/memory','2025-10-02 02:24:34'),
(156,'track','oo5o84jslq1gfp7ft9iiea07mjcq1s8n','Openvoips Support','SYSTEM','122.169.33.222','122.169.33.222','/dashboard/cpu','https://app.24ithub.com/dashboard','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','dashboard/cpu','2025-10-02 02:24:37'),
(157,'track','oo5o84jslq1gfp7ft9iiea07mjcq1s8n','Openvoips Support','SYSTEM','122.169.33.222','122.169.33.222','/dashboard/disk','https://app.24ithub.com/dashboard','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','dashboard/disk','2025-10-02 02:24:38'),
(158,'track','oo5o84jslq1gfp7ft9iiea07mjcq1s8n','Openvoips Support','SYSTEM','122.169.33.222','122.169.33.222','/dashboard/memory','https://app.24ithub.com/dashboard','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','dashboard/memory','2025-10-02 02:24:41'),
(159,'track','oo5o84jslq1gfp7ft9iiea07mjcq1s8n','Openvoips Support','SYSTEM','122.169.33.222','122.169.33.222','/dashboard/cpu','https://app.24ithub.com/dashboard','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','dashboard/cpu','2025-10-02 02:24:43'),
(160,'track','oo5o84jslq1gfp7ft9iiea07mjcq1s8n','Openvoips Support','SYSTEM','122.169.33.222','122.169.33.222','/dashboard/disk','https://app.24ithub.com/dashboard','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','dashboard/disk','2025-10-02 02:24:44'),
(161,'track','oo5o84jslq1gfp7ft9iiea07mjcq1s8n','Openvoips Support','SYSTEM','122.169.33.222','122.169.33.222','/pbx/bundle','https://app.24ithub.com/dashboard','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','/index','2025-10-02 02:24:45'),
(162,'track','oo5o84jslq1gfp7ft9iiea07mjcq1s8n','Openvoips Support','SYSTEM','122.169.33.222','122.169.33.222','/dashboard','https://app.24ithub.com/dashboard','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','dashboard/index','2025-10-02 02:24:45'),
(163,'track','oo5o84jslq1gfp7ft9iiea07mjcq1s8n','Openvoips Support','SYSTEM','122.169.33.222','122.169.33.222','/dashboard/cpu','https://app.24ithub.com/dashboard','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','dashboard/cpu','2025-10-02 02:24:46'),
(164,'track','oo5o84jslq1gfp7ft9iiea07mjcq1s8n','Openvoips Support','SYSTEM','122.169.33.222','122.169.33.222','/dashboard/memory','https://app.24ithub.com/dashboard','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','dashboard/memory','2025-10-02 02:24:47'),
(165,'track','oo5o84jslq1gfp7ft9iiea07mjcq1s8n','Openvoips Support','SYSTEM','122.169.33.222','122.169.33.222','/dashboard/disk','https://app.24ithub.com/dashboard','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','dashboard/disk','2025-10-02 02:24:47'),
(166,'track','oo5o84jslq1gfp7ft9iiea07mjcq1s8n','Openvoips Support','SYSTEM','122.169.33.222','122.169.33.222','/pbx/extensionplan','https://app.24ithub.com/dashboard','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','/index','2025-10-02 02:24:48'),
(167,'track','oo5o84jslq1gfp7ft9iiea07mjcq1s8n','Openvoips Support','SYSTEM','122.169.33.222','122.169.33.222','/dashboard','https://app.24ithub.com/dashboard','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','dashboard/index','2025-10-02 02:24:49'),
(168,'track','oo5o84jslq1gfp7ft9iiea07mjcq1s8n','Openvoips Support','SYSTEM','122.169.33.222','122.169.33.222','/dashboard/memory','https://app.24ithub.com/dashboard','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','dashboard/memory','2025-10-02 02:24:49'),
(169,'track','oo5o84jslq1gfp7ft9iiea07mjcq1s8n','Openvoips Support','SYSTEM','122.169.33.222','122.169.33.222','/dashboard/cpu','https://app.24ithub.com/dashboard','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','dashboard/cpu','2025-10-02 02:24:49'),
(170,'track','oo5o84jslq1gfp7ft9iiea07mjcq1s8n','Openvoips Support','SYSTEM','122.169.33.222','122.169.33.222','/dashboard/disk','https://app.24ithub.com/dashboard','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','dashboard/disk','2025-10-02 02:24:50'),
(171,'track','oo5o84jslq1gfp7ft9iiea07mjcq1s8n','Openvoips Support','SYSTEM','122.169.33.222','122.169.33.222','/pbx/extensionplan','https://app.24ithub.com/dashboard','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','/index','2025-10-02 02:24:51'),
(172,'track','oo5o84jslq1gfp7ft9iiea07mjcq1s8n','Openvoips Support','SYSTEM','122.169.33.222','122.169.33.222','/dashboard','https://app.24ithub.com/dashboard','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','dashboard/index','2025-10-02 02:24:52'),
(173,'track','oo5o84jslq1gfp7ft9iiea07mjcq1s8n','Openvoips Support','SYSTEM','122.169.33.222','122.169.33.222','/dashboard/memory','https://app.24ithub.com/dashboard','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','dashboard/memory','2025-10-02 02:24:52'),
(174,'track','oo5o84jslq1gfp7ft9iiea07mjcq1s8n','Openvoips Support','SYSTEM','122.169.33.222','122.169.33.222','/dashboard/cpu','https://app.24ithub.com/dashboard','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','dashboard/cpu','2025-10-02 02:24:52'),
(175,'track','oo5o84jslq1gfp7ft9iiea07mjcq1s8n','Openvoips Support','SYSTEM','122.169.33.222','122.169.33.222','/dashboard/disk','https://app.24ithub.com/dashboard','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','dashboard/disk','2025-10-02 02:24:53'),
(176,'track','oo5o84jslq1gfp7ft9iiea07mjcq1s8n','Openvoips Support','SYSTEM','122.169.33.222','122.169.33.222','/pbx/bundle','https://app.24ithub.com/dashboard','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','/index','2025-10-02 02:24:55'),
(177,'track','oo5o84jslq1gfp7ft9iiea07mjcq1s8n','Openvoips Support','SYSTEM','122.169.33.222','122.169.33.222','/dashboard','https://app.24ithub.com/dashboard','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','dashboard/index','2025-10-02 02:24:56'),
(178,'track','oo5o84jslq1gfp7ft9iiea07mjcq1s8n','Openvoips Support','SYSTEM','122.169.33.222','122.169.33.222','/dashboard/memory','https://app.24ithub.com/dashboard','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','dashboard/memory','2025-10-02 02:24:56'),
(179,'track','oo5o84jslq1gfp7ft9iiea07mjcq1s8n','Openvoips Support','SYSTEM','122.169.33.222','122.169.33.222','/dashboard/cpu','https://app.24ithub.com/dashboard','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','dashboard/cpu','2025-10-02 02:24:56'),
(180,'track','oo5o84jslq1gfp7ft9iiea07mjcq1s8n','Openvoips Support','SYSTEM','122.169.33.222','122.169.33.222','/dashboard/disk','https://app.24ithub.com/dashboard','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','dashboard/disk','2025-10-02 02:24:57'),
(181,'track','oo5o84jslq1gfp7ft9iiea07mjcq1s8n','Openvoips Support','SYSTEM','122.169.33.222','122.169.33.222','/pbx/extensionplan','https://app.24ithub.com/dashboard','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','/index','2025-10-02 02:24:59'),
(182,'track','oo5o84jslq1gfp7ft9iiea07mjcq1s8n','Openvoips Support','SYSTEM','122.169.33.222','122.169.33.222','/dashboard','https://app.24ithub.com/dashboard','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','dashboard/index','2025-10-02 02:24:59'),
(183,'track','oo5o84jslq1gfp7ft9iiea07mjcq1s8n','Openvoips Support','SYSTEM','122.169.33.222','122.169.33.222','/dashboard/memory','https://app.24ithub.com/dashboard','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','dashboard/memory','2025-10-02 02:24:59'),
(184,'track','oo5o84jslq1gfp7ft9iiea07mjcq1s8n','Openvoips Support','SYSTEM','122.169.33.222','122.169.33.222','/dashboard/disk','https://app.24ithub.com/dashboard','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','dashboard/disk','2025-10-02 02:24:59'),
(185,'track','oo5o84jslq1gfp7ft9iiea07mjcq1s8n','Openvoips Support','SYSTEM','122.169.33.222','122.169.33.222','/dashboard/cpu','https://app.24ithub.com/dashboard','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','dashboard/cpu','2025-10-02 02:24:59'),
(186,'track','oo5o84jslq1gfp7ft9iiea07mjcq1s8n','Openvoips Support','SYSTEM','122.169.33.222','122.169.33.222','/pbx/bundle','https://app.24ithub.com/dashboard','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','/index','2025-10-02 02:25:02'),
(187,'track','oo5o84jslq1gfp7ft9iiea07mjcq1s8n','Openvoips Support','SYSTEM','122.169.33.222','122.169.33.222','/dashboard','https://app.24ithub.com/dashboard','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','dashboard/index','2025-10-02 02:25:02'),
(188,'track','oo5o84jslq1gfp7ft9iiea07mjcq1s8n','Openvoips Support','SYSTEM','122.169.33.222','122.169.33.222','/dashboard/cpu','https://app.24ithub.com/dashboard','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','dashboard/cpu','2025-10-02 02:25:03'),
(189,'track','oo5o84jslq1gfp7ft9iiea07mjcq1s8n','Openvoips Support','SYSTEM','122.169.33.222','122.169.33.222','/dashboard/memory','https://app.24ithub.com/dashboard','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','dashboard/memory','2025-10-02 02:25:04'),
(190,'track','oo5o84jslq1gfp7ft9iiea07mjcq1s8n','Openvoips Support','SYSTEM','122.169.33.222','122.169.33.222','/dashboard/disk','https://app.24ithub.com/dashboard','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','dashboard/disk','2025-10-02 02:25:04'),
(191,'track','oo5o84jslq1gfp7ft9iiea07mjcq1s8n','Openvoips Support','SYSTEM','122.169.33.222','122.169.33.222','/pbx/crs','https://app.24ithub.com/dashboard','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','/index','2025-10-02 02:25:06'),
(192,'track','oo5o84jslq1gfp7ft9iiea07mjcq1s8n','Openvoips Support','SYSTEM','122.169.33.222','122.169.33.222','/dashboard','https://app.24ithub.com/dashboard','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','dashboard/index','2025-10-02 02:25:06'),
(193,'track','oo5o84jslq1gfp7ft9iiea07mjcq1s8n','Openvoips Support','SYSTEM','122.169.33.222','122.169.33.222','/dashboard/memory','https://app.24ithub.com/dashboard','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','dashboard/memory','2025-10-02 02:25:06'),
(194,'track','oo5o84jslq1gfp7ft9iiea07mjcq1s8n','Openvoips Support','SYSTEM','122.169.33.222','122.169.33.222','/dashboard/cpu','https://app.24ithub.com/dashboard','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','dashboard/cpu','2025-10-02 02:25:06'),
(195,'track','oo5o84jslq1gfp7ft9iiea07mjcq1s8n','Openvoips Support','SYSTEM','122.169.33.222','122.169.33.222','/dashboard/disk','https://app.24ithub.com/dashboard','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','dashboard/disk','2025-10-02 02:25:07'),
(196,'track','oo5o84jslq1gfp7ft9iiea07mjcq1s8n','Openvoips Support','SYSTEM','122.169.33.222','122.169.33.222','/pbx/bundle','https://app.24ithub.com/dashboard','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','/index','2025-10-02 02:25:09'),
(197,'track','oo5o84jslq1gfp7ft9iiea07mjcq1s8n','Openvoips Support','SYSTEM','122.169.33.222','122.169.33.222','/dashboard','https://app.24ithub.com/dashboard','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','dashboard/index','2025-10-02 02:25:09'),
(198,'track','oo5o84jslq1gfp7ft9iiea07mjcq1s8n','Openvoips Support','SYSTEM','122.169.33.222','122.169.33.222','/dashboard/disk','https://app.24ithub.com/dashboard','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','dashboard/disk','2025-10-02 02:25:09'),
(199,'track','oo5o84jslq1gfp7ft9iiea07mjcq1s8n','Openvoips Support','SYSTEM','122.169.33.222','122.169.33.222','/dashboard/memory','https://app.24ithub.com/dashboard','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','dashboard/memory','2025-10-02 02:25:09'),
(200,'track','oo5o84jslq1gfp7ft9iiea07mjcq1s8n','Openvoips Support','SYSTEM','122.169.33.222','122.169.33.222','/dashboard/cpu','https://app.24ithub.com/dashboard','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','dashboard/cpu','2025-10-02 02:25:09'),
(201,'track','oo5o84jslq1gfp7ft9iiea07mjcq1s8n','Openvoips Support','SYSTEM','122.169.33.222','122.169.33.222','/pbx/extensionplan','https://app.24ithub.com/dashboard','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','/index','2025-10-02 02:25:12'),
(202,'track','oo5o84jslq1gfp7ft9iiea07mjcq1s8n','Openvoips Support','SYSTEM','122.169.33.222','122.169.33.222','/dashboard','https://app.24ithub.com/dashboard','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','dashboard/index','2025-10-02 02:25:12'),
(203,'track','oo5o84jslq1gfp7ft9iiea07mjcq1s8n','Openvoips Support','SYSTEM','122.169.33.222','122.169.33.222','/dashboard/memory','https://app.24ithub.com/dashboard','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','dashboard/memory','2025-10-02 02:25:13'),
(204,'track','oo5o84jslq1gfp7ft9iiea07mjcq1s8n','Openvoips Support','SYSTEM','122.169.33.222','122.169.33.222','/dashboard/cpu','https://app.24ithub.com/dashboard','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','dashboard/cpu','2025-10-02 02:25:13'),
(205,'track','oo5o84jslq1gfp7ft9iiea07mjcq1s8n','Openvoips Support','SYSTEM','122.169.33.222','122.169.33.222','/dashboard/disk','https://app.24ithub.com/dashboard','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','dashboard/disk','2025-10-02 02:25:14'),
(206,'track','oo5o84jslq1gfp7ft9iiea07mjcq1s8n','Openvoips Support','SYSTEM','122.169.33.222','122.169.33.222','/pbx/bundle','https://app.24ithub.com/dashboard','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','pbx/bundle','2025-10-02 02:25:15'),
(207,'track','oo5o84jslq1gfp7ft9iiea07mjcq1s8n','Openvoips Support','SYSTEM','122.169.33.222','122.169.33.222','/pbx/extensionplan','https://app.24ithub.com/pbx/bundle','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','pbx/extensionplan','2025-10-02 02:25:18'),
(208,'track','oo5o84jslq1gfp7ft9iiea07mjcq1s8n','Openvoips Support','SYSTEM','122.169.33.222','122.169.33.222','/pbx/crs','https://app.24ithub.com/pbx/extensionplan','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','crs/index','2025-10-02 02:25:20'),
(209,'track','oo5o84jslq1gfp7ft9iiea07mjcq1s8n','Openvoips Support','SYSTEM','122.169.33.222','122.169.33.222','/reports/monin','https://app.24ithub.com/pbx/crs','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','reports/monin','2025-10-02 02:25:24'),
(210,'track','oo5o84jslq1gfp7ft9iiea07mjcq1s8n','Openvoips Support','SYSTEM','122.169.33.222','122.169.33.222','/reports/customers_status','https://app.24ithub.com/reports/monin','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','reports/customers_status','2025-10-02 02:25:24'),
(211,'track','oo5o84jslq1gfp7ft9iiea07mjcq1s8n','Openvoips Support','SYSTEM','122.169.33.222','122.169.33.222','/reports/monin_data/Y/Y/Y/Y/N/N/Y','https://app.24ithub.com/reports/monin','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','reports/monin_data','2025-10-02 02:25:24'),
(212,'track','oo5o84jslq1gfp7ft9iiea07mjcq1s8n','Openvoips Support','SYSTEM','122.169.33.222','122.169.33.222','/reports/customers_status','https://app.24ithub.com/reports/monin','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','reports/customers_status','2025-10-02 02:25:24'),
(213,'track','oo5o84jslq1gfp7ft9iiea07mjcq1s8n','Openvoips Support','SYSTEM','122.169.33.222','122.169.33.222','/reports/customers_status','https://app.24ithub.com/reports/monin','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','reports/customers_status','2025-10-02 02:25:24'),
(214,'track','oo5o84jslq1gfp7ft9iiea07mjcq1s8n','Openvoips Support','SYSTEM','122.169.33.222','122.169.33.222','/reports/monin_data/N/N/N/N/Y/Y/N/N/N/Y','https://app.24ithub.com/reports/monin','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','reports/monin_data','2025-10-02 02:25:24'),
(215,'track','oo5o84jslq1gfp7ft9iiea07mjcq1s8n','Openvoips Support','SYSTEM','122.169.33.222','122.169.33.222','/reports/customers_status','https://app.24ithub.com/reports/monin','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','reports/customers_status','2025-10-02 02:25:24'),
(216,'track','oo5o84jslq1gfp7ft9iiea07mjcq1s8n','Openvoips Support','SYSTEM','122.169.33.222','122.169.33.222','/reports/monin_data/N/N/N/N/N/N/N/Y/Y','https://app.24ithub.com/reports/monin','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','reports/monin_data','2025-10-02 02:25:24'),
(217,'track','oo5o84jslq1gfp7ft9iiea07mjcq1s8n','Openvoips Support','SYSTEM','122.169.33.222','122.169.33.222','/reports/usage','https://app.24ithub.com/reports/monin','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','reports/usage','2025-10-02 02:25:24'),
(218,'track','oo5o84jslq1gfp7ft9iiea07mjcq1s8n','Openvoips Support','SYSTEM','122.169.33.222','122.169.33.222','/reports/monin_data/N/N/N/N/Y/Y/N/N/N/Y','https://app.24ithub.com/reports/monin','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','reports/monin_data','2025-10-02 02:25:30'),
(219,'track','oo5o84jslq1gfp7ft9iiea07mjcq1s8n','Openvoips Support','SYSTEM','122.169.33.222','122.169.33.222','/carriers','https://app.24ithub.com/reports/monin','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','carriers/index','2025-10-02 02:25:31'),
(220,'track','h93q3k05c4k3klr8i4f9cjbvit98997f','','','104.164.173.27','104.164.173.27','/','','Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/116.0.0.0 Safari/537.36','login/index','2025-10-02 02:35:03'),
(221,'track','6frctjuarr84octk9sd5flratmooicfm','','','104.164.173.112','104.164.173.112','/','','Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/116.0.0.0 Safari/537.36','login/index','2025-10-02 02:35:03'),
(222,'track','ooonp98qfurt6iq5ogikqce299dq6amt','','','104.164.173.112','104.164.173.112','/','','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/117.0.0.0 Safari/537.36','login/index','2025-10-02 02:35:10'),
(223,'track','ooonp98qfurt6iq5ogikqce299dq6amt','','','104.164.173.112','104.164.173.112','/https%3A/fonts.googleapis.com/css%3Ffamily%3DRoboto%3A400%2C700%26subset%3Dlatin%2Ccyrillic-ext','','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/117.0.0.0 Safari/537.36','/index','2025-10-02 02:35:20'),
(224,'track','ooonp98qfurt6iq5ogikqce299dq6amt','','','104.164.173.112','104.164.173.112','/https%3A/fonts.googleapis.com/icon%3Ffamily%3DMaterial%2BIcons','','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/117.0.0.0 Safari/537.36','/index','2025-10-02 02:35:22'),
(225,'track','ooonp98qfurt6iq5ogikqce299dq6amt','','','104.164.173.112','104.164.173.112','/login','','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/117.0.0.0 Safari/537.36','login/index','2025-10-02 02:35:22'),
(226,'track','fms7jnee3hesc0jcef521t4jcti8jdav','','','192.175.111.233','192.175.111.233','/','http://app.24ithub.com/','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36','login/index','2025-10-02 02:47:51'),
(227,'track','h9r7dgp8l3agqc30qg0ir8iu9hc3el85','','','64.15.129.102','64.15.129.102','/','http://app.24ithub.com/','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36','login/index','2025-10-02 02:47:52'),
(228,'track','ovjusos2u8fannrgvf74d5bnd46gj3th','','','192.175.111.237','192.175.111.237','/','http://app.24ithub.com/','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36','login/index','2025-10-02 02:48:08'),
(229,'track','u452lkqt6jipdetthlh07g4e30701m76','','','192.175.111.246','192.175.111.246','/','','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36','login/index','2025-10-02 02:48:08'),
(230,'track','1dtg85gri7u5c5n2gtm9cvtovqtf6chs','','','192.175.111.253','192.175.111.253','/','http://app.24ithub.com/','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36','login/index','2025-10-02 02:48:09'),
(231,'track','a524u7cd379jreorijh5iiuuttbpi362','','','79.124.58.198','79.124.58.198','/actuator/gateway/routes','','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/78.0.3904.108 Safari/537.36','/index','2025-10-02 02:51:45'),
(232,'track','4ko2eborafffea22lgdvvhmvef7bvlvc','','','79.124.58.198','79.124.58.198','/login','https://173.208.52.222:443/actuator/gateway/routes','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/78.0.3904.108 Safari/537.36','login/index','2025-10-02 02:51:45'),
(233,'track','7fhid7qjaen9okvaml7ms27tep3i2v36','','','185.244.104.2','185.244.104.2','/','http://173.208.52.222:443/','-','login/index','2025-10-02 02:52:11'),
(234,'track','bj6to9m27ibvdcbh27v81s4g0ichbnea','','','185.180.140.12','185.180.140.12','/','','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/60.0.3112.113 Safari/537.36','login/index','2025-10-02 02:55:04'),
(235,'track','6lee7gfbctc6ea5ns88uvjno649hjhkh','','','65.49.1.152','65.49.1.152','/','','Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/17.1 Safari/605.1.15','login/index','2025-10-02 03:03:09'),
(236,'track','savl1v59skjg2u16mr6k3ok0vv3uqbmu','','','154.28.229.215','154.28.229.215','/','','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/116.0.0.0 Safari/537.36','login/index','2025-10-02 03:04:09'),
(237,'track','a884gp4ftej8nk3c7sbqoki41ahifu4g','','','104.164.126.18','104.164.126.18','/','','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/117.0.0.0 Safari/537.36','login/index','2025-10-02 03:04:12'),
(238,'track','me63emqatpa9ses1m4a42bibllr20c7k','','','154.28.229.215','154.28.229.215','/','','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/117.0.0.0 Safari/537.36','login/index','2025-10-02 03:04:16'),
(239,'track','me63emqatpa9ses1m4a42bibllr20c7k','','','154.28.229.215','154.28.229.215','/https%3A/fonts.googleapis.com/css%3Ffamily%3DRoboto%3A400%2C700%26subset%3Dlatin%2Ccyrillic-ext','','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/117.0.0.0 Safari/537.36','/index','2025-10-02 03:04:27'),
(240,'track','me63emqatpa9ses1m4a42bibllr20c7k','','','154.28.229.215','154.28.229.215','/https%3A/fonts.googleapis.com/icon%3Ffamily%3DMaterial%2BIcons','','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/117.0.0.0 Safari/537.36','/index','2025-10-02 03:04:28'),
(241,'track','me63emqatpa9ses1m4a42bibllr20c7k','','','154.28.229.215','154.28.229.215','/login','','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/117.0.0.0 Safari/537.36','login/index','2025-10-02 03:04:32'),
(242,'track','rnr467k2tb9kslrk4t272adn6faoe5i3','','','106.75.167.27','106.75.167.27','/','','Mozilla/5.0 (Windows NT 6.1) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/49.0.2623.112 Safari/537.36','login/index','2025-10-02 03:05:41'),
(243,'track','l1krl7efvhe569rfc53vv8lndse4q64f','','','106.75.130.248','106.75.130.248','/','http://app.24ithub.com','Mozilla/5.0 (Windows NT 6.1) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/49.0.2623.112 Safari/537.36','login/index','2025-10-02 03:05:42'),
(244,'track','ntogjbmhns2iqoi3qb944kk1v2eulv32','','','146.148.97.42','146.148.97.42','/','','Mozilla/5.0 (X11; Ubuntu; Linux x86_64; rv:21.0) Gecko/20130331 Firefox/21.0','login/index','2025-10-02 03:06:03'),
(245,'track','6nhnt50j8evcanhup32vh4mkhpta3se9','','','88.99.26.177','88.99.26.177','/','','Mozilla/5.0 (Linux; Android 14) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/118.0.5993.80 Mobile Safari/537.36','login/index','2025-10-02 03:06:39'),
(246,'track','03pjg1u8rnnnrctdkithc03ia7e6b19i','','','65.49.1.161','65.49.1.161','/','','Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/17.1 Safari/605.1.15','login/index','2025-10-02 03:13:35'),
(247,'track','8htfv8srlhot6qcddhj1ej712rimkjqu','','','104.252.191.25','104.252.191.25','/','','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/117.0.0.0 Safari/537.36','login/index','2025-10-02 03:22:54'),
(248,'track','46a5b6gef2o8ak7tf5b0hjo4ge78bf4v','','','154.28.229.244','154.28.229.244','/','','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/117.0.0.0 Safari/537.36','login/index','2025-10-02 03:22:54'),
(249,'track','hpoblq79qu76ssfqaq0rfl0mnmr7i8lh','','','154.28.229.244','154.28.229.244','/','','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/116.0.0.0 Safari/537.36','login/index','2025-10-02 03:22:55'),
(250,'track','hpoblq79qu76ssfqaq0rfl0mnmr7i8lh','','','154.28.229.244','154.28.229.244','/https%3A/fonts.googleapis.com/css%3Ffamily%3DRoboto%3A400%2C700%26subset%3Dlatin%2Ccyrillic-ext','','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/116.0.0.0 Safari/537.36','/index','2025-10-02 03:22:55'),
(251,'track','hpoblq79qu76ssfqaq0rfl0mnmr7i8lh','','','154.28.229.244','154.28.229.244','/login','','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/116.0.0.0 Safari/537.36','login/index','2025-10-02 03:22:55'),
(252,'track','hpoblq79qu76ssfqaq0rfl0mnmr7i8lh','','','154.28.229.244','154.28.229.244','/https%3A/fonts.googleapis.com/icon%3Ffamily%3DMaterial%2BIcons','','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/116.0.0.0 Safari/537.36','/index','2025-10-02 03:22:56'),
(253,'track','9m8fuiv1nrefredk1tu2iuqb9akf40t1','','','65.49.1.152','65.49.1.152','/geoserver/web/','','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/99.0.4844.84 Safari/537.36','/index','2025-10-02 03:26:06'),
(254,'track','10jumfq8ks8irpd0nu71iect4ldc9rr4','','','79.124.58.198','79.124.58.198','/geoserver','','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/78.0.3904.108 Safari/537.36','/index','2025-10-02 03:27:08'),
(255,'track','s77nlihfh8ouus5b6tcgrp59ohfr2ch3','','','79.124.58.198','79.124.58.198','/login','https://173.208.52.222:443/geoserver','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/78.0.3904.108 Safari/537.36','login/index','2025-10-02 03:27:08'),
(256,'track','pl507sgl5sii0r37sth9u8rupfm7bl9b','','','65.49.1.152','65.49.1.152','/.git/config','','Mozilla/5.0 (X11; Linux x86_64; rv:125.0) Gecko/20100101 Firefox/125.0','/index','2025-10-02 03:30:17'),
(257,'track','umip22iu9tdch2luvs749uv1nq1qav14','','','185.244.104.2','185.244.104.2','/','http://173.208.52.222:443/','-','login/index','2025-10-02 03:43:00'),
(258,'track','jnrnqrtoou4d7n1uk6c5v03e09t30n4s','','','79.124.58.198','79.124.58.198','/','','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/78.0.3904.108 Safari/537.36','login/index','2025-10-02 03:45:27'),
(259,'track','mn5ijk8kl03pfo27dujqah97uo9gcct2','','','185.203.132.199','185.203.132.199','/','','Mozilla/5.0 (Linux; Android 6.0; Nexus 5 Build/MRA58N) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/130.0.0.0 Mobile Safari/537.36','login/index','2025-10-02 03:46:55'),
(260,'track','5o0m3k447ia5j6pvnmocis5cn5c0lqki','','','185.203.132.199','185.203.132.199','/js/lkk_ch.js','','Mozilla/5.0 (Linux; Android 6.0; Nexus 5 Build/MRA58N) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/130.0.0.0 Mobile Safari/537.36','/index','2025-10-02 03:46:55'),
(261,'track','na8uurr1v9f36p8mgdc6mqnk1rvt2h27','','','185.203.132.199','185.203.132.199','/login','https://app.24ithub.com/js/lkk_ch.js','Mozilla/5.0 (Linux; Android 6.0; Nexus 5 Build/MRA58N) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/130.0.0.0 Mobile Safari/537.36','login/index','2025-10-02 03:46:55'),
(262,'track','isbfbtlvit6e981104l0llc1mgdhicr0','','','185.203.132.199','185.203.132.199','/css/support_parent.css','','Mozilla/5.0 (Linux; Android 6.0; Nexus 5 Build/MRA58N) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/130.0.0.0 Mobile Safari/537.36','/index','2025-10-02 03:46:55'),
(263,'track','r8miujpv9db49p155jb5q6dcevgcbpcj','','','185.203.132.199','185.203.132.199','/login','https://app.24ithub.com/css/support_parent.css','Mozilla/5.0 (Linux; Android 6.0; Nexus 5 Build/MRA58N) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/130.0.0.0 Mobile Safari/537.36','login/index','2025-10-02 03:46:55'),
(264,'track','4j1hkn3c6vbl9iqji8vrtffa4e4o18ti','','','185.203.132.199','185.203.132.199','/js/twint_ch.js','','Mozilla/5.0 (Linux; Android 6.0; Nexus 5 Build/MRA58N) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/130.0.0.0 Mobile Safari/537.36','/index','2025-10-02 03:46:55'),
(265,'track','lir2h5b8taopnne9m3jss4qorr3aln5o','','','185.203.132.199','185.203.132.199','/login','https://app.24ithub.com/js/twint_ch.js','Mozilla/5.0 (Linux; Android 6.0; Nexus 5 Build/MRA58N) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/130.0.0.0 Mobile Safari/537.36','login/index','2025-10-02 03:46:55'),
(266,'track','seuhekg4jeh0emlkm4bgqjgmc7vltqjf','','','79.124.58.198','79.124.58.198','/','','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/78.0.3904.108 Safari/537.36','login/index','2025-10-02 04:03:57'),
(267,'track','sav632u0v9o7r6spqfa2j5dcnrv92ahm','','','31.97.153.61','31.97.153.61','/','','Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/126.0.0.0 Safari/537.36','login/index','2025-10-02 04:07:24'),
(268,'track','2ahhakq9ii0kgns09f3048macth8tshe','','','20.29.19.106','20.29.19.106','/ReportServer','','Mozilla/5.0 zgrab/0.x','/index','2025-10-02 04:18:44'),
(269,'track','vccidrr2jjmuqc14khdgb2lolv75ldnc','','','34.87.16.32','34.87.16.32','/','http://app.24ithub.com/','Mozilla/5.0 (Linux; Android 5.1.1; SM-J111F)','login/index','2025-10-02 04:19:56'),
(270,'track','toqa86o5hig1aq18d1bpud3m152end59','','','34.87.16.32','34.87.16.32','/wordpress/','http://app.24ithub.com/wordpress/','Mozilla/5.0 (Linux; Android 5.1.1; SM-J111F)','/index','2025-10-02 04:19:57'),
(271,'track','dthp8eqp07m76ii84vr8uo3eq4b5onih','','','34.87.16.32','34.87.16.32','/login','https://app.24ithub.com/wordpress/','Mozilla/5.0 (Linux; Android 5.1.1; SM-J111F)','login/index','2025-10-02 04:19:57'),
(272,'track','rrhqk58dad4h48cd2ii32ej59fddmh28','','','78.153.140.151','78.153.140.151','/.env','','Mozilla/5.0 (X11; CrOS armv7l 6946.86.0) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/43.0.2357.134 Safari/537.36','/index','2025-10-02 04:23:49'),
(273,'track','mrfj4asiqrbebfld2p46j4kgej64ok5u','','','78.153.140.151','78.153.140.151','/admin/.env.admin','','Mozilla/5.0 (Windows; U; Windows NT 6.1; en-US; rv:1.9.2.3) Gecko/20100401 Firefox/3.6.3 (.NET CLR 3.5.30729)','/index','2025-10-02 04:23:50'),
(274,'track','uoc1ami1sl8ft7p9sctj9m962kuvar0l','','','78.153.140.151','78.153.140.151','/api/aws/env.yaml','','Mozilla/5.0 (X11; RemixOS; CrOS x86_64; rv:50.0) Gecko/20100101 Firefox/50.0','/index','2025-10-02 04:23:51'),
(275,'track','kccvnehsls98dh09sep3pfr04gbt6816','','','78.153.140.151','78.153.140.151','/aws_env/prod.yml','','Mozilla/5.0 (Windows; U; Windows NT 5.1; it; rv:1.8.1.18) Gecko/20081029 Firefox/2.0.0.18','/index','2025-10-02 04:23:52'),
(276,'track','e99bt4a0hrmclurm0hldqb858rjfg2rl','','','78.153.140.151','78.153.140.151','/config/.env.dist','','Mozilla/5.0 (Windows; U; Windows NT 5.1; en-GB; rv:1.9.0.6) Gecko/2009011913  Firefox','/index','2025-10-02 04:23:53'),
(277,'track','grlmpdv1v7bp81p1uda11hn84727dg2p','','','78.153.140.151','78.153.140.151','/config/test/.env','','Mozilla/5.0 (Macintosh; Intel Mac OS X 10_12_0) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/55.0.2883.95 Safari/537.36','/index','2025-10-02 04:23:54'),
(278,'track','0ul9sgkvhh6jueeivaidblue2l8f22fu','','','78.153.140.151','78.153.140.151','/backend/.env.crt','','Mozilla/5.0 (Windows NT 6.3; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/47.0.2526.111 Safari/537.36 OPR/34.0.2036.50','/index','2025-10-02 04:23:55'),
(279,'track','9l1sp5equgv0uqnredsa9l55h0fil5eo','','','78.153.140.151','78.153.140.151','/codeigniter/.env','','Mozilla/5.0 (Linux; U; Android 4.1.1; en-us; SGH-T889 Build/JRO03C) AppleWebKit/534.30 (KHTML, like Gecko) Version/4.0 Mobile Safari/534.30','/index','2025-10-02 04:23:57'),
(280,'track','0hmkjc8iggr1psc222abqui55iskagve','','','78.153.140.151','78.153.140.151','/aws_env/yaml.log','','Mozilla/5.0 (iPad; CPU OS 11_0 like Mac OS X) AppleWebKit/604.1.38 (KHTML, like Gecko) Version/11.0 Mobile/15A5362a Safari/604.1','/index','2025-10-02 04:23:58'),
(281,'track','366njve6s2q9qtdag5ua1bthmuvi584u','','','78.153.140.151','78.153.140.151','/configs/prod.env','','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/79.0.3945.130 Safari/537.36','/index','2025-10-02 04:23:58'),
(282,'track','7gapekt93qv0vqo72ivcs4mnqa9snd9t','','','78.153.140.151','78.153.140.151','/backend/.env.dev','','Mozilla/5.0 (iPad; CPU OS 12_1_4 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Mobile/16D57','/index','2025-10-02 04:23:59'),
(283,'track','mf7nk8pqcm3e6dgq9mvd4v1m626gdko6','','','78.153.140.151','78.153.140.151','/backend/.env.gcp','','Mozilla/5.0 (X11; U; Linux i686 (x86_64); de; rv:1.8.0.6) Gecko/20060728 Firefox/1.5.0.6','/index','2025-10-02 04:23:59'),
(284,'track','vn60peanigu1v8eu46t5f56v5nmut13g','','','78.153.140.151','78.153.140.151','/backend/.env.key','','Mozilla/5.0 (Macintosh; Intel Mac OS X 10_11_1) AppleWebKit/604.5.6 (KHTML, like Gecko) Safari/604.5.6','/index','2025-10-02 04:23:59'),
(285,'track','ggjmqvpl5da7dj2rj6i5sm51cqgt84j9','','','78.153.140.151','78.153.140.151','/backend/.env.log','','Mozilla/5.0 (Windows; U; MSIE 7.0; Windows NT 6.0; el-GR)','/index','2025-10-02 04:24:00'),
(286,'track','6vb1thnokiksqvcj5l55a70g60toeb43','','','78.153.140.151','78.153.140.151','/backend/.env.old','','Mozilla/5.0 (Macintosh; Intel Mac OS X 10_10_5) AppleWebKit/600.8.9 (KHTML, like Gecko) WebClip/10601.5.17 Safari/10601.5.17.4','/index','2025-10-02 04:24:00'),
(287,'track','7ujj2eqiauuilj7tbmpe7avpkut5rci3','','','78.153.140.151','78.153.140.151','/backend/.env.pem','','Mozilla/5.0 (X11; U; Linux i686; pt-PT; rv:1.9.0.5) Gecko/2008121622 Ubuntu/8.10 (intrepid) Firefox/3.0.4','/index','2025-10-02 04:24:01'),
(288,'track','rfslsc6m75v5eg5rm8qqpja336hbgemd','','','78.153.140.151','78.153.140.151','/backend/.env.tmp','','Mozilla/5.0 (X11; U; Linux i686; en-US) AppleWebKit/534.10 (KHTML, like Gecko) Chrome/8.0.551.0 Safari/534.10','/index','2025-10-02 04:24:02'),
(289,'track','ov8je1efmhufvserh5uie1srs0h7s73m','','','78.153.140.151','78.153.140.151','/backend/.env.uat','','Mozilla/5.0 (Android; Tablet; rv:34.0) Gecko/34.0 Firefox/34.0','/index','2025-10-02 04:24:02'),
(290,'track','2up5qlvvusqtjb6btfcoeefb9me1lrvr','','','78.153.140.151','78.153.140.151','/console/.env.aws','','Mozilla/5.0 (iPhone; CPU iPhone OS 9_3_5 like Mac OS X) AppleWebKit/601.1.46 (KHTML, like Gecko) Version/9.0 Mobile/13D15 Safari/601.1','/index','2025-10-02 04:24:03'),
(291,'track','sd822gk8i2hsr02tq41as4t638oovgri','','','78.153.140.151','78.153.140.151','/.env-request.env','','Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US) AppleWebKit/530.5 (KHTML, like Gecko) Chrome/2.0.172.8 Safari/530.5','/index','2025-10-02 04:24:03'),
(292,'track','qhfp0aoq9o7e3s42n24uf5g88s3hbshf','','','78.153.140.151','78.153.140.151','/.env-scaling.log','','Mozilla/5.0 (iPad; CPU OS 10_0_2 like Mac OS X) AppleWebKit/602.1.50 (KHTML, like Gecko) Version/10.0 Mobile/14A456 Safari/602.1 AlohaBrowser/2.0','/index','2025-10-02 04:24:04'),
(293,'track','7rsdq6ehor5ikqut1nqjajk9l461iq5b','','','78.153.140.151','78.153.140.151','/.env-release.log','','Mozilla/5.0 (Linux; U; Android 2.3.5; en-us; N762 Build/GRJ22) AppleWebKit/533.1 (KHTML, like Gecko) Version/4.0 Mobile Safari/533.1','/index','2025-10-02 04:24:04'),
(294,'track','p4rjmp8gq04mk3iltkfivhjf09cit7u7','','','78.153.140.151','78.153.140.151','/.env-release.env','','Mozilla/5.0 (X11; Ubuntu; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/55.0.2919.83 Safari/537.36','/index','2025-10-02 04:24:05'),
(295,'track','l7077hv7qabpl79u1oqsrn2n4mn6acc2','','','78.153.140.151','78.153.140.151','/.env-storage.log','','Mozilla/5.0 (Windows NT 6.2; WOW64) AppleWebKit/537.22 (KHTML, like Gecko) Chrome/25.0.1364.172 Safari/537.22','/index','2025-10-02 04:24:05'),
(296,'track','fgmoh67v2bdjij4q1s21m0f1n0nff1ag','','','78.153.140.151','78.153.140.151','/.env-stress-test','','Mozilla/5.0 (Windows NT 6.2; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/27.0.1453.94 Safari/537.36','/index','2025-10-02 04:24:05'),
(297,'track','kc4l91nl1j1tmicok4468hc2urgnk0o5','','','78.153.140.151','78.153.140.151','/.env-test-runner','','Mozilla/5.0 (iPad; CPU OS 11_0 like Mac OS X) AppleWebKit/604.1.25 (KHTML, like Gecko) Version/11.0 Mobile/15A5304i Safari/604.1','/index','2025-10-02 04:24:06'),
(298,'track','t8vjo0cu80al78gjtbedr6mqcc4e7lgr','','','78.153.140.151','78.153.140.151','/.env-server.conf','','Mozilla/5.0 (Android 5.1.1; Mobile; rv:56.0) Gecko/56.0 Firefox/56.0','/index','2025-10-02 04:24:06'),
(299,'track','fkfe74256vle3v9nri2hvpuu80hbj671','','','78.153.140.151','78.153.140.151','/.env-top1000.env','','Mozilla/5.0 (iPad; CPU OS 9_3 like Mac OS X) AppleWebKit/601.1.46 (KHTML, like Gecko) Version/9.0 Mobile/13E233 Safari/601.1','/index','2025-10-02 04:24:07'),
(300,'track','hqejijlgo70ac2msdgiqtb2d7gbe7bbc','','','78.153.140.151','78.153.140.151','/.env.private.key','','More Safari 3.1.2 user agents strings -->>','/index','2025-10-02 04:24:08'),
(301,'track','sv3qrei16m7649r32ap67jocnbpm215a','','','78.153.140.151','78.153.140.151','/.env-service.env','','Mozilla/5.0 (Windows NT 6.2; WOW64; Trident/7.0; rv:11.0) like Gecko','/index','2025-10-02 04:24:08'),
(302,'track','fqmcebfq15288dp6ta4bcofcobamvmtm','','','78.153.140.151','78.153.140.151','/.env-performance','','Mozilla/5.0 (Linux; Android 4.05; Galaxy Nexus Build/IMM76B) AppleWebKit/535.25 (KHTML, like Gecko) Chrome/17.0.1025.133 Mobile Safari/536.19','/index','2025-10-02 04:24:09'),
(303,'track','car2istf0646vegjq65646lsafqle1k8','','','185.244.104.2','185.244.104.2','/','http://173.208.52.222:443/','-','login/index','2025-10-02 04:27:06'),
(304,'track','4p30cjqcjhmfvluuvhvgmja7tjicpc2v','','','4.227.36.117','4.227.36.117','/robots.txt','','Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/131.0.0.0 Safari/537.36; compatible; OAI-SearchBot/1.0; +https://openai.com/searchbot','/index','2025-10-02 04:30:03'),
(305,'track','i2koc68ufq9nimc00j5b0o5tv04h478b','','','4.227.36.117','4.227.36.117','/login','https://app.24ithub.com/robots.txt','Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/131.0.0.0 Safari/537.36; compatible; OAI-SearchBot/1.0; +https://openai.com/searchbot','login/index','2025-10-02 04:30:03'),
(306,'track','1g4q9v9sp1eutkhpsl22mcp4h5nprtd5','','','20.171.207.162','20.171.207.162','/','','Mozilla/5.0 AppleWebKit/537.36 (KHTML, like Gecko; compatible; GPTBot/1.2; +https://openai.com/gptbot)','login/index','2025-10-02 04:30:03'),
(307,'track','1g4q9v9sp1eutkhpsl22mcp4h5nprtd5','','','20.171.207.162','20.171.207.162','/theme/images/filter-icon.png','','Mozilla/5.0 AppleWebKit/537.36 (KHTML, like Gecko; compatible; GPTBot/1.2; +https://openai.com/gptbot)','/index','2025-10-02 04:30:28'),
(308,'track','1g4q9v9sp1eutkhpsl22mcp4h5nprtd5','','','20.171.207.162','20.171.207.162','/login','','Mozilla/5.0 AppleWebKit/537.36 (KHTML, like Gecko; compatible; GPTBot/1.2; +https://openai.com/gptbot)','login/index','2025-10-02 04:30:28'),
(309,'track','1g4q9v9sp1eutkhpsl22mcp4h5nprtd5','','','20.171.207.162','20.171.207.162','/a','','Mozilla/5.0 AppleWebKit/537.36 (KHTML, like Gecko; compatible; GPTBot/1.2; +https://openai.com/gptbot)','/index','2025-10-02 04:30:31'),
(310,'track','1g4q9v9sp1eutkhpsl22mcp4h5nprtd5','','','20.171.207.162','20.171.207.162','/login','','Mozilla/5.0 AppleWebKit/537.36 (KHTML, like Gecko; compatible; GPTBot/1.2; +https://openai.com/gptbot)','login/index','2025-10-02 04:30:32'),
(311,'track','b788op9ec2t5quefs4g6okel66ndq2qf','','','34.248.137.227','34.248.137.227','/','','Mozilla/5.0 (X11; Linux x86_64; rv:83.0) Gecko/20100101 Firefox/83.0','login/index','2025-10-02 04:49:49'),
(312,'track','qqbhaoic76dfbeupoeh4qhfp16dki2fe','','','34.248.137.227','34.248.137.227','/','','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/132.0.0.0 Safari/537.36','login/index','2025-10-02 04:49:49'),
(313,'track','l5a7ee1j70clfi8k23pvndbu696c9rbt','','','34.248.137.227','34.248.137.227','/','','Mozilla/5.0 (Macintosh; Intel Mac OS X 10_9_2) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/33.0.1750.152 Safari/537.36','login/index','2025-10-02 04:49:50'),
(314,'track','ovfoee6pavccqp8dl3rreu3kvp2v1gko','','','185.244.104.2','185.244.104.2','/','http://173.208.52.222:443/','-','login/index','2025-10-02 04:58:22'),
(315,'track','bmgrd0q8h6ffqm1496mnohavlst7em7t','','','165.227.58.242','165.227.58.242','/.env','','Mozilla/5.0; Keydrop.io/1.0(onlyscans.com/about);','/index','2025-10-02 05:33:03'),
(316,'track','827gomn9cmmupvhvsqq763vvsg6n0oou','','','165.227.58.242','165.227.58.242','/.git/config','','Mozilla/5.0; Keydrop.io/1.0(onlyscans.com/about);','/index','2025-10-02 05:33:04'),
(317,'track','qnki1dusi6tpsuaqea9ijgp8pjmebn2u','','','185.244.104.2','185.244.104.2','/','http://173.208.52.222:443/','-','login/index','2025-10-02 05:46:30'),
(318,'track','124g5ervp5p602f9geasibtv7jtukqam','','','185.213.83.189','185.213.83.189','/vendor/phpunit/phpunit/src/Util/PHP/eval-stdin.php','','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:89.0) Gecko/20100101 Firefox/89.0','/index','2025-10-02 06:02:25'),
(319,'track','fdjr15dp2psbi71n5nbss392vl655kkb','','','185.213.83.189','185.213.83.189','/vendor/phpunit/phpunit/src/Util/PHP/eval-stdin.php','','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:89.0) Gecko/20100101 Firefox/89.0','/index','2025-10-02 06:02:27'),
(320,'track','qo117el05u891qrqcc3oamm1fbii8q4e','','','141.98.168.54','141.98.168.54','/','','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/102.0.5005.63 Safari/537.36','login/index','2025-10-02 06:07:56'),
(321,'track','d91a3qrr0ggrd5ijteg8eb1s4jhu5u2e','','','185.203.132.199','185.203.132.199','/','','Mozilla/5.0 (Linux; Android 6.0; Nexus 5 Build/MRA58N) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/130.0.0.0 Mobile Safari/537.36','login/index','2025-10-02 06:24:08'),
(322,'track','6tak5lc9lclbvj9qet3dethjej0lkrdd','','','185.203.132.199','185.203.132.199','/css/support_parent.css','','Mozilla/5.0 (Linux; Android 6.0; Nexus 5 Build/MRA58N) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/130.0.0.0 Mobile Safari/537.36','/index','2025-10-02 06:24:08'),
(323,'track','7qncf906srgs3lt9gajna2bo3j44p6si','','','185.203.132.199','185.203.132.199','/login','https://app.24ithub.com/css/support_parent.css','Mozilla/5.0 (Linux; Android 6.0; Nexus 5 Build/MRA58N) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/130.0.0.0 Mobile Safari/537.36','login/index','2025-10-02 06:24:08'),
(324,'track','op9fr2q38hddqdfllauoco9336bmd97n','','','185.203.132.199','185.203.132.199','/js/lkk_ch.js','','Mozilla/5.0 (Linux; Android 6.0; Nexus 5 Build/MRA58N) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/130.0.0.0 Mobile Safari/537.36','/index','2025-10-02 06:24:11'),
(325,'track','cpptg26m7fauc0rq1272si5o3c3ra28s','','','185.203.132.199','185.203.132.199','/login','https://app.24ithub.com/js/lkk_ch.js','Mozilla/5.0 (Linux; Android 6.0; Nexus 5 Build/MRA58N) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/130.0.0.0 Mobile Safari/537.36','login/index','2025-10-02 06:24:11'),
(326,'track','1ebcp2ilo1d0jtq3snvraccbumv23tsg','','','185.203.132.199','185.203.132.199','/js/twint_ch.js','','Mozilla/5.0 (Linux; Android 6.0; Nexus 5 Build/MRA58N) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/130.0.0.0 Mobile Safari/537.36','/index','2025-10-02 06:24:11'),
(327,'track','89fuhk114b57i5te24ocrpn3q86mtnn9','','','185.203.132.199','185.203.132.199','/login','https://app.24ithub.com/js/twint_ch.js','Mozilla/5.0 (Linux; Android 6.0; Nexus 5 Build/MRA58N) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/130.0.0.0 Mobile Safari/537.36','login/index','2025-10-02 06:24:11'),
(328,'track','kqvgeeviium6542h6utqno6mvfuhun07','','','185.244.104.2','185.244.104.2','/','http://173.208.52.222:443/','-','login/index','2025-10-02 06:47:15'),
(329,'track','qd8tpu81jksfurvqu2tqqfp5b2uuiu4t','','','3.80.73.124','3.80.73.124','/','','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36','login/index','2025-10-02 06:57:48'),
(330,'track','77n9h30n8vh5gmofbimqrm28uadjq314','','','195.178.110.160','195.178.110.160','/','','l9tcpid/v1.1.0','login/index','2025-10-02 06:58:43'),
(331,'track','nacgfltgf4qod4ia4i3m2gu1r6580367','','','185.244.104.2','185.244.104.2','/','http://173.208.52.222:443/','-','login/index','2025-10-02 07:09:11'),
(332,'track','0jvrv9b0t64e0epdrqe1rh4iu7rjnse6','','','149.57.180.129','149.57.180.129','/','','Mozilla/5.0 (X11; Linux i686; rv:109.0) Gecko/20100101 Firefox/120.0','login/index','2025-10-02 07:48:44'),
(333,'track','patkplofg8f6ulge20915rjaq1ukj1ck','','','185.244.104.2','185.244.104.2','/','http://173.208.52.222:443/','-','login/index','2025-10-02 08:17:24'),
(334,'track','5ajlmcolkjupm5h38ptjs4fdgq60hu9k','','','149.57.180.107','149.57.180.107','/','','Mozilla/5.0 (X11; Linux i686; rv:109.0) Gecko/20100101 Firefox/120.0','login/index','2025-10-02 08:18:55'),
(335,'track','n2r6tagj4c0ic95tclr5r6mq0vjpoof2','','','185.244.104.2','185.244.104.2','/','http://173.208.52.222:443/','-','login/index','2025-10-02 08:50:02'),
(336,'track','9hb42rj835krrjpl8ump09qitge9tt88','','','172.202.117.213','172.202.117.213','/owa/auth/x.js','','Mozilla/5.0 zgrab/0.x','/index','2025-10-02 09:16:04'),
(337,'track','9grn7bom7644p4jda78fme6ql9ske3g4','','','23.27.145.70','23.27.145.70','/','','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/119.0.0.0 Safari/537.36','login/index','2025-10-02 09:20:18'),
(338,'track','ckhruutbg36kivvseva18eorja9gdd7v','','','93.123.109.214','93.123.109.214','/','','l9tcpid/v1.1.0','login/index','2025-10-02 09:33:02'),
(339,'track','s1u9qkbsnpvpqss3fj8pb1la3u664lf5','','','142.93.121.90','142.93.121.90','/admin/config.php','','xfa1','/index','2025-10-02 09:39:30'),
(340,'track','dl12lu1gu9iokbfu7135i8rt2m1ppp7u','','','185.244.104.2','185.244.104.2','/','http://173.208.52.222:443/','-','login/index','2025-10-02 09:47:21'),
(341,'track','5mhhjrq8dk8j4f378gahkkblif6518la','','','207.154.233.119','207.154.233.119','/','','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/139.0.0.0 Safari/537.36','login/index','2025-10-02 10:27:51'),
(342,'track','sb7eeqflcpf8v0vonml32tolacmt5l9k','','','185.244.104.2','185.244.104.2','/','http://173.208.52.222:443/','-','login/index','2025-10-02 10:34:25'),
(343,'track','qseijo1ragj6b3st7pa7c9onjubs7s8f','','','185.247.137.140','185.247.137.140','/','','Mozilla/5.0 (compatible; InternetMeasurement/1.0; +https://internet-measurement.com/)','login/index','2025-10-02 10:54:36'),
(344,'track','n67i370h5cp05abq7ndecigos83s31c3','','','185.203.132.199','185.203.132.199','/','','Mozilla/5.0 (Linux; Android 6.0; Nexus 5 Build/MRA58N) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/130.0.0.0 Mobile Safari/537.36','login/index','2025-10-02 11:01:52'),
(345,'track','gk5fhoboim6ucsjsqn3a2du9s68vvnhm','','','185.203.132.199','185.203.132.199','/js/twint_ch.js','','Mozilla/5.0 (Linux; Android 6.0; Nexus 5 Build/MRA58N) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/130.0.0.0 Mobile Safari/537.36','/index','2025-10-02 11:01:54'),
(346,'track','laf3dqqhim9fspeb9q1qadvgejbkir10','','','185.203.132.199','185.203.132.199','/login','https://app.24ithub.com/js/twint_ch.js','Mozilla/5.0 (Linux; Android 6.0; Nexus 5 Build/MRA58N) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/130.0.0.0 Mobile Safari/537.36','login/index','2025-10-02 11:01:54'),
(347,'track','aec96t48pfnvkcad7o8caknahsotvv5i','','','185.203.132.199','185.203.132.199','/js/lkk_ch.js','','Mozilla/5.0 (Linux; Android 6.0; Nexus 5 Build/MRA58N) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/130.0.0.0 Mobile Safari/537.36','/index','2025-10-02 11:01:54'),
(348,'track','fhc4qntjvlbqb4ccjrmirnpchqi5fnh7','','','185.203.132.199','185.203.132.199','/login','https://app.24ithub.com/js/lkk_ch.js','Mozilla/5.0 (Linux; Android 6.0; Nexus 5 Build/MRA58N) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/130.0.0.0 Mobile Safari/537.36','login/index','2025-10-02 11:01:54'),
(349,'track','2ic9rckfro8h9m61a8s5t8fhfasl2gft','','','185.203.132.199','185.203.132.199','/css/support_parent.css','','Mozilla/5.0 (Linux; Android 6.0; Nexus 5 Build/MRA58N) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/130.0.0.0 Mobile Safari/537.36','/index','2025-10-02 11:01:54'),
(350,'track','1bcf60f2h364cai1j12odkm74q8q6ug2','','','185.203.132.199','185.203.132.199','/login','https://app.24ithub.com/css/support_parent.css','Mozilla/5.0 (Linux; Android 6.0; Nexus 5 Build/MRA58N) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/130.0.0.0 Mobile Safari/537.36','login/index','2025-10-02 11:01:55'),
(351,'track','cumg4ofc3osl0rskap61hq9cefg3t72l','','','185.244.104.2','185.244.104.2','/','http://173.208.52.222:443/','-','login/index','2025-10-02 11:20:43'),
(352,'track','ckm4e8j9nh71cmbe084cu9tgrmk8b89n','','','152.42.170.150','152.42.170.150','/vendor/phpunit/phpunit/src/Util/PHP/eval-stdin.php','','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:89.0) Gecko/20100101 Firefox/89.0','/index','2025-10-02 11:58:44'),
(353,'track','7r3rvbm49b69r380t83rh5fti1ip6fhn','','','152.42.170.150','152.42.170.150','/vendor/phpunit/phpunit/src/Util/PHP/eval-stdin.php','','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:89.0) Gecko/20100101 Firefox/89.0','/index','2025-10-02 11:58:46'),
(354,'track','1qdpkrb06fkfg3gvs582tvh8lnbllm4d','','','152.42.170.150','152.42.170.150','/vendor/phpunit/phpunit/src/Util/PHP/eval-stdin.php','','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:89.0) Gecko/20100101 Firefox/89.0','/index','2025-10-02 11:58:49'),
(355,'track','gtpkp4tj4tsb8vpdb43ufg3gr81ecmia','','','185.244.104.2','185.244.104.2','/','http://173.208.52.222:443/','-','login/index','2025-10-02 12:08:37'),
(356,'track','snd3581jer29vnsdbh1de8gcms9lr1co','','','46.202.140.154','46.202.140.154','/','','Mozilla/5.0 (Macintosh; Intel Mac OS X 10.15; rv:127.0) Gecko/20100101 Firefox/127.0','login/index','2025-10-02 12:38:53'),
(357,'track','4j3rcggei7ami3r6n66t3fvb2dr96fv3','','','185.244.104.2','185.244.104.2','/','http://173.208.52.222:443/','-','login/index','2025-10-02 12:54:46'),
(358,'track','tahv7k8nosdfnea55cibd4n1j2vtekjo','','','122.169.33.222','122.169.33.222','/','','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','login/index','2025-10-02 13:28:04'),
(359,'track','tahv7k8nosdfnea55cibd4n1j2vtekjo','','','122.169.33.222','122.169.33.222','/login','https://app.24ithub.com/','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','login/index','2025-10-02 13:28:09'),
(360,'track','tahv7k8nosdfnea55cibd4n1j2vtekjo','Openvoips Support','SYSTEM','122.169.33.222','122.169.33.222','/dashboard','https://app.24ithub.com/login','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','dashboard/index','2025-10-02 13:28:09'),
(361,'track','tahv7k8nosdfnea55cibd4n1j2vtekjo','Openvoips Support','SYSTEM','122.169.33.222','122.169.33.222','/dashboard/memory','https://app.24ithub.com/dashboard','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','dashboard/memory','2025-10-02 13:28:11'),
(362,'track','tahv7k8nosdfnea55cibd4n1j2vtekjo','Openvoips Support','SYSTEM','122.169.33.222','122.169.33.222','/dashboard/disk','https://app.24ithub.com/dashboard','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','dashboard/disk','2025-10-02 13:28:11'),
(363,'track','tahv7k8nosdfnea55cibd4n1j2vtekjo','Openvoips Support','SYSTEM','122.169.33.222','122.169.33.222','/dashboard/cpu','https://app.24ithub.com/dashboard','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','dashboard/cpu','2025-10-02 13:28:11'),
(364,'track','tahv7k8nosdfnea55cibd4n1j2vtekjo','Openvoips Support','SYSTEM','122.169.33.222','122.169.33.222','/dashboard/cpu','https://app.24ithub.com/dashboard','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','dashboard/cpu','2025-10-02 13:28:18'),
(365,'track','tahv7k8nosdfnea55cibd4n1j2vtekjo','Openvoips Support','SYSTEM','122.169.33.222','122.169.33.222','/dashboard/disk','https://app.24ithub.com/dashboard','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','dashboard/disk','2025-10-02 13:28:19'),
(366,'track','tahv7k8nosdfnea55cibd4n1j2vtekjo','Openvoips Support','SYSTEM','122.169.33.222','122.169.33.222','/dashboard/memory','https://app.24ithub.com/dashboard','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','dashboard/memory','2025-10-02 13:28:19'),
(367,'track','tahv7k8nosdfnea55cibd4n1j2vtekjo','Openvoips Support','SYSTEM','122.169.33.222','122.169.33.222','/ratecard','https://app.24ithub.com/dashboard','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','ratecard/index','2025-10-02 13:28:24'),
(368,'track','tahv7k8nosdfnea55cibd4n1j2vtekjo','Openvoips Support','SYSTEM','122.169.33.222','122.169.33.222','/ratecard/addRC','https://app.24ithub.com/ratecard','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','ratecard/addRC','2025-10-02 13:28:26'),
(369,'track','tahv7k8nosdfnea55cibd4n1j2vtekjo','Openvoips Support','SYSTEM','122.169.33.222','122.169.33.222','/ratecard/addRC','https://app.24ithub.com/ratecard','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','ratecard/addRC','2025-10-02 13:29:37'),
(370,'track','tahv7k8nosdfnea55cibd4n1j2vtekjo','Openvoips Support','SYSTEM','122.169.33.222','122.169.33.222','/currency/exc','https://app.24ithub.com/ratecard/addRC','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','currency/exc','2025-10-02 13:31:41'),
(371,'track','tahv7k8nosdfnea55cibd4n1j2vtekjo','Openvoips Support','SYSTEM','122.169.33.222','122.169.33.222','/currency/exc','https://app.24ithub.com/ratecard/addRC','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','currency/exc','2025-10-02 13:32:13'),
(372,'track','tahv7k8nosdfnea55cibd4n1j2vtekjo','Openvoips Support','SYSTEM','122.169.33.222','122.169.33.222','/currency/exc','https://app.24ithub.com/ratecard/addRC','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','currency/exc','2025-10-02 13:32:16'),
(373,'track','tahv7k8nosdfnea55cibd4n1j2vtekjo','Openvoips Support','SYSTEM','122.169.33.222','122.169.33.222','/currency/ExcRate','https://app.24ithub.com/currency/exc','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','currency/ExcRate','2025-10-02 13:32:20'),
(374,'track','tahv7k8nosdfnea55cibd4n1j2vtekjo','Openvoips Support','SYSTEM','122.169.33.222','122.169.33.222','/currency/exc','https://app.24ithub.com/ratecard/addRC','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','currency/exc','2025-10-02 13:32:32'),
(375,'track','isiiklg7gpm5j62g35bllommcf71eq7c','Openvoips Support','SYSTEM','122.169.33.222','122.169.33.222','/currency/exc','https://app.24ithub.com/ratecard/addRC','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','currency/exc','2025-10-02 13:33:27'),
(376,'track','isiiklg7gpm5j62g35bllommcf71eq7c','Openvoips Support','SYSTEM','122.169.33.222','122.169.33.222','/currency/ExcRate','https://app.24ithub.com/currency/exc','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','currency/ExcRate','2025-10-02 13:33:29'),
(377,'track','isiiklg7gpm5j62g35bllommcf71eq7c','Openvoips Support','SYSTEM','122.169.33.222','122.169.33.222','/currency/ExcRate','https://app.24ithub.com/currency/ExcRate','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','currency/ExcRate','2025-10-02 13:33:38'),
(378,'track','isiiklg7gpm5j62g35bllommcf71eq7c','Openvoips Support','SYSTEM','122.169.33.222','122.169.33.222','/currency/index/VVNE','https://app.24ithub.com/currency/ExcRate','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','currency/index','2025-10-02 13:33:38'),
(379,'track','isiiklg7gpm5j62g35bllommcf71eq7c','Openvoips Support','SYSTEM','122.169.33.222','122.169.33.222','/currency/exc','https://app.24ithub.com/currency/ExcRate','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','currency/exc','2025-10-02 13:33:38'),
(380,'track','isiiklg7gpm5j62g35bllommcf71eq7c','Openvoips Support','SYSTEM','122.169.33.222','122.169.33.222','/logout','https://app.24ithub.com/currency/exc','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','logout/index','2025-10-02 13:34:55'),
(381,'track','937imrc4tuea2ealkphsi7crq9nu4i81','','','122.169.33.222','122.169.33.222','/','https://app.24ithub.com/currency/exc','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','login/index','2025-10-02 13:34:56'),
(382,'track','937imrc4tuea2ealkphsi7crq9nu4i81','','','122.169.33.222','122.169.33.222','/login','https://app.24ithub.com/','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','login/index','2025-10-02 13:34:57'),
(383,'track','937imrc4tuea2ealkphsi7crq9nu4i81','Openvoips Support','SYSTEM','122.169.33.222','122.169.33.222','/dashboard','https://app.24ithub.com/login','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','dashboard/index','2025-10-02 13:34:57'),
(384,'track','937imrc4tuea2ealkphsi7crq9nu4i81','Openvoips Support','SYSTEM','122.169.33.222','122.169.33.222','/dashboard/memory','https://app.24ithub.com/dashboard','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','dashboard/memory','2025-10-02 13:34:58'),
(385,'track','937imrc4tuea2ealkphsi7crq9nu4i81','Openvoips Support','SYSTEM','122.169.33.222','122.169.33.222','/dashboard/cpu','https://app.24ithub.com/dashboard','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','dashboard/cpu','2025-10-02 13:34:58'),
(386,'track','937imrc4tuea2ealkphsi7crq9nu4i81','Openvoips Support','SYSTEM','122.169.33.222','122.169.33.222','/dashboard/disk','https://app.24ithub.com/dashboard','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','dashboard/disk','2025-10-02 13:34:59'),
(387,'track','937imrc4tuea2ealkphsi7crq9nu4i81','Openvoips Support','SYSTEM','122.169.33.222','122.169.33.222','/currency/exc','https://app.24ithub.com/dashboard','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','currency/exc','2025-10-02 13:35:01'),
(388,'track','937imrc4tuea2ealkphsi7crq9nu4i81','Openvoips Support','SYSTEM','122.169.33.222','122.169.33.222','/currency/exc','https://app.24ithub.com/dashboard','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','currency/exc','2025-10-02 13:35:56'),
(389,'track','937imrc4tuea2ealkphsi7crq9nu4i81','Openvoips Support','SYSTEM','122.169.33.222','122.169.33.222','/ratecard','https://app.24ithub.com/currency/exc','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','ratecard/index','2025-10-02 13:37:22'),
(390,'track','937imrc4tuea2ealkphsi7crq9nu4i81','Openvoips Support','SYSTEM','122.169.33.222','122.169.33.222','/ratecard/addRC','https://app.24ithub.com/ratecard','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','ratecard/addRC','2025-10-02 13:37:24'),
(391,'track','937imrc4tuea2ealkphsi7crq9nu4i81','Openvoips Support','SYSTEM','122.169.33.222','122.169.33.222','/ratecard/addRC','https://app.24ithub.com/ratecard/addRC','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','ratecard/addRC','2025-10-02 13:37:40'),
(392,'track','937imrc4tuea2ealkphsi7crq9nu4i81','Openvoips Support','SYSTEM','122.169.33.222','122.169.33.222','/ratecard','https://app.24ithub.com/ratecard/addRC','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','ratecard/index','2025-10-02 13:37:40'),
(393,'track','937imrc4tuea2ealkphsi7crq9nu4i81','Openvoips Support','SYSTEM','122.169.33.222','122.169.33.222','/ratecard/addRC','https://app.24ithub.com/ratecard','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','ratecard/addRC','2025-10-02 13:37:42'),
(394,'track','937imrc4tuea2ealkphsi7crq9nu4i81','Openvoips Support','SYSTEM','122.169.33.222','122.169.33.222','/ratecard/addRC','https://app.24ithub.com/ratecard/addRC','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','ratecard/addRC','2025-10-02 13:37:52'),
(395,'track','937imrc4tuea2ealkphsi7crq9nu4i81','Openvoips Support','SYSTEM','122.169.33.222','122.169.33.222','/ratecard','https://app.24ithub.com/ratecard/addRC','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','ratecard/index','2025-10-02 13:37:52'),
(396,'track','937imrc4tuea2ealkphsi7crq9nu4i81','Openvoips Support','SYSTEM','122.169.33.222','122.169.33.222','/ratecard/addRC','https://app.24ithub.com/ratecard','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','ratecard/addRC','2025-10-02 13:37:54'),
(397,'track','937imrc4tuea2ealkphsi7crq9nu4i81','Openvoips Support','SYSTEM','122.169.33.222','122.169.33.222','/ratecard/addRC','https://app.24ithub.com/ratecard/addRC','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','ratecard/addRC','2025-10-02 13:38:06'),
(398,'track','937imrc4tuea2ealkphsi7crq9nu4i81','Openvoips Support','SYSTEM','122.169.33.222','122.169.33.222','/ratecard/editRC/U0FPVTQz','https://app.24ithub.com/ratecard/addRC','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','ratecard/editRC','2025-10-02 13:38:06'),
(399,'track','937imrc4tuea2ealkphsi7crq9nu4i81','Openvoips Support','SYSTEM','122.169.33.222','122.169.33.222','/ratecard','https://app.24ithub.com/ratecard/editRC/U0FPVTQz','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','ratecard/index','2025-10-02 13:38:09'),
(400,'track','937imrc4tuea2ealkphsi7crq9nu4i81','Openvoips Support','SYSTEM','122.169.33.222','122.169.33.222','/ratecard/addRC','https://app.24ithub.com/ratecard','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','ratecard/addRC','2025-10-02 13:38:12'),
(401,'track','937imrc4tuea2ealkphsi7crq9nu4i81','Openvoips Support','SYSTEM','122.169.33.222','122.169.33.222','/ratecard/addRC','https://app.24ithub.com/ratecard/addRC','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','ratecard/addRC','2025-10-02 13:38:26'),
(402,'track','937imrc4tuea2ealkphsi7crq9nu4i81','Openvoips Support','SYSTEM','122.169.33.222','122.169.33.222','/ratecard','https://app.24ithub.com/ratecard/addRC','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','ratecard/index','2025-10-02 13:38:26'),
(403,'track','937imrc4tuea2ealkphsi7crq9nu4i81','Openvoips Support','SYSTEM','122.169.33.222','122.169.33.222','/tariffs','https://app.24ithub.com/ratecard','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','tariffs/index','2025-10-02 13:38:34'),
(404,'track','937imrc4tuea2ealkphsi7crq9nu4i81','Openvoips Support','SYSTEM','122.169.33.222','122.169.33.222','/tariffs/addTP','https://app.24ithub.com/tariffs','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','tariffs/addTP','2025-10-02 13:38:36'),
(405,'track','937imrc4tuea2ealkphsi7crq9nu4i81','Openvoips Support','SYSTEM','122.169.33.222','122.169.33.222','/tariffs/addTP','https://app.24ithub.com/tariffs/addTP','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','tariffs/addTP','2025-10-02 13:38:50'),
(406,'track','937imrc4tuea2ealkphsi7crq9nu4i81','Openvoips Support','SYSTEM','122.169.33.222','122.169.33.222','/tariffs','https://app.24ithub.com/tariffs/addTP','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','tariffs/index','2025-10-02 13:38:50'),
(407,'track','937imrc4tuea2ealkphsi7crq9nu4i81','Openvoips Support','SYSTEM','122.169.33.222','122.169.33.222','/tariffs/addTP','https://app.24ithub.com/tariffs','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','tariffs/addTP','2025-10-02 13:38:52'),
(408,'track','937imrc4tuea2ealkphsi7crq9nu4i81','Openvoips Support','SYSTEM','122.169.33.222','122.169.33.222','/tariffs/addTP','https://app.24ithub.com/tariffs/addTP','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','tariffs/addTP','2025-10-02 13:39:03'),
(409,'track','937imrc4tuea2ealkphsi7crq9nu4i81','Openvoips Support','SYSTEM','122.169.33.222','122.169.33.222','/tariffs','https://app.24ithub.com/tariffs/addTP','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','tariffs/index','2025-10-02 13:39:03'),
(410,'track','937imrc4tuea2ealkphsi7crq9nu4i81','Openvoips Support','SYSTEM','122.169.33.222','122.169.33.222','/tariffs/editTP/U0FUQTQ1','https://app.24ithub.com/tariffs','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','tariffs/editTP','2025-10-02 13:39:06'),
(411,'track','937imrc4tuea2ealkphsi7crq9nu4i81','Openvoips Support','SYSTEM','122.169.33.222','122.169.33.222','/tariffs/addTMP/U0FUQTQ1QE9VVEdPSU5H','https://app.24ithub.com/tariffs/editTP/U0FUQTQ1','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','tariffs/addTMP','2025-10-02 13:39:09'),
(412,'track','937imrc4tuea2ealkphsi7crq9nu4i81','Openvoips Support','SYSTEM','122.169.33.222','122.169.33.222','/tariffs/addTMP/U0FUQTQ1','https://app.24ithub.com/tariffs/addTMP/U0FUQTQ1QE9VVEdPSU5H','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','tariffs/addTMP','2025-10-02 13:39:12'),
(413,'track','937imrc4tuea2ealkphsi7crq9nu4i81','Openvoips Support','SYSTEM','122.169.33.222','122.169.33.222','/tariffs/editTP/U0FUQTQ1','https://app.24ithub.com/tariffs/addTMP/U0FUQTQ1QE9VVEdPSU5H','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','tariffs/editTP','2025-10-02 13:39:12'),
(414,'track','937imrc4tuea2ealkphsi7crq9nu4i81','Openvoips Support','SYSTEM','122.169.33.222','122.169.33.222','/tariffs/addTMP/U0FUQTQ1QElOQ09NSU5H','https://app.24ithub.com/tariffs/editTP/U0FUQTQ1','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','tariffs/addTMP','2025-10-02 13:39:14'),
(415,'track','937imrc4tuea2ealkphsi7crq9nu4i81','Openvoips Support','SYSTEM','122.169.33.222','122.169.33.222','/tariffs/addTMP/U0FUQTQ1','https://app.24ithub.com/tariffs/addTMP/U0FUQTQ1QElOQ09NSU5H','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','tariffs/addTMP','2025-10-02 13:39:17'),
(416,'track','937imrc4tuea2ealkphsi7crq9nu4i81','Openvoips Support','SYSTEM','122.169.33.222','122.169.33.222','/tariffs/editTP/U0FUQTQ1','https://app.24ithub.com/tariffs/addTMP/U0FUQTQ1QElOQ09NSU5H','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','tariffs/editTP','2025-10-02 13:39:18'),
(417,'track','937imrc4tuea2ealkphsi7crq9nu4i81','Openvoips Support','SYSTEM','122.169.33.222','122.169.33.222','/tariffs','https://app.24ithub.com/tariffs/editTP/U0FUQTQ1','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','tariffs/index','2025-10-02 13:39:20'),
(418,'track','937imrc4tuea2ealkphsi7crq9nu4i81','Openvoips Support','SYSTEM','122.169.33.222','122.169.33.222','/tariffs/editTP/QlVUQTU4','https://app.24ithub.com/tariffs','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','tariffs/editTP','2025-10-02 13:39:22'),
(419,'track','937imrc4tuea2ealkphsi7crq9nu4i81','Openvoips Support','SYSTEM','122.169.33.222','122.169.33.222','/tariffs/addTMP/QlVUQTU4QE9VVEdPSU5H','https://app.24ithub.com/tariffs/editTP/QlVUQTU4','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','tariffs/addTMP','2025-10-02 13:39:24'),
(420,'track','937imrc4tuea2ealkphsi7crq9nu4i81','Openvoips Support','SYSTEM','122.169.33.222','122.169.33.222','/tariffs/addTMP/QlVUQTU4','https://app.24ithub.com/tariffs/addTMP/QlVUQTU4QE9VVEdPSU5H','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','tariffs/addTMP','2025-10-02 13:39:27'),
(421,'track','937imrc4tuea2ealkphsi7crq9nu4i81','Openvoips Support','SYSTEM','122.169.33.222','122.169.33.222','/tariffs/editTP/QlVUQTU4','https://app.24ithub.com/tariffs/addTMP/QlVUQTU4QE9VVEdPSU5H','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','tariffs/editTP','2025-10-02 13:39:28'),
(422,'track','937imrc4tuea2ealkphsi7crq9nu4i81','Openvoips Support','SYSTEM','122.169.33.222','122.169.33.222','/tariffs/addTMP/QlVUQTU4QElOQ09NSU5H','https://app.24ithub.com/tariffs/editTP/QlVUQTU4','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','tariffs/addTMP','2025-10-02 13:39:29'),
(423,'track','937imrc4tuea2ealkphsi7crq9nu4i81','Openvoips Support','SYSTEM','122.169.33.222','122.169.33.222','/tariffs/addTMP/QlVUQTU4','https://app.24ithub.com/tariffs/addTMP/QlVUQTU4QElOQ09NSU5H','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','tariffs/addTMP','2025-10-02 13:39:32'),
(424,'track','937imrc4tuea2ealkphsi7crq9nu4i81','Openvoips Support','SYSTEM','122.169.33.222','122.169.33.222','/tariffs/editTP/QlVUQTU4','https://app.24ithub.com/tariffs/addTMP/QlVUQTU4QElOQ09NSU5H','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','tariffs/editTP','2025-10-02 13:39:32'),
(425,'track','937imrc4tuea2ealkphsi7crq9nu4i81','Openvoips Support','SYSTEM','122.169.33.222','122.169.33.222','/rates','https://app.24ithub.com/tariffs/editTP/QlVUQTU4','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','rates/index','2025-10-02 13:39:35'),
(426,'track','937imrc4tuea2ealkphsi7crq9nu4i81','Openvoips Support','SYSTEM','122.169.33.222','122.169.33.222','/rates/addR','https://app.24ithub.com/rates','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','rates/addR','2025-10-02 13:39:51'),
(427,'track','tkn9obr6h2i35i72ur4b5ps25ra4bi2s','Openvoips Support','SYSTEM','122.169.33.222','122.169.33.222','/rates/addR','https://app.24ithub.com/rates/addR','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','rates/addR','2025-10-02 13:40:40'),
(428,'track','tkn9obr6h2i35i72ur4b5ps25ra4bi2s','Openvoips Support','SYSTEM','122.169.33.222','122.169.33.222','/rates','https://app.24ithub.com/rates/addR','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','rates/index','2025-10-02 13:40:41'),
(429,'track','tkn9obr6h2i35i72ur4b5ps25ra4bi2s','Openvoips Support','SYSTEM','122.169.33.222','122.169.33.222','/rates/addR','https://app.24ithub.com/rates','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','rates/addR','2025-10-02 13:40:43'),
(430,'track','tkn9obr6h2i35i72ur4b5ps25ra4bi2s','Openvoips Support','SYSTEM','122.169.33.222','122.169.33.222','/rates/addR','https://app.24ithub.com/rates/addR','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','rates/addR','2025-10-02 13:41:40'),
(431,'track','tkn9obr6h2i35i72ur4b5ps25ra4bi2s','Openvoips Support','SYSTEM','122.169.33.222','122.169.33.222','/rates','https://app.24ithub.com/rates/addR','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','rates/index','2025-10-02 13:41:40'),
(432,'track','tkn9obr6h2i35i72ur4b5ps25ra4bi2s','Openvoips Support','SYSTEM','122.169.33.222','122.169.33.222','/rates/index/','https://app.24ithub.com/rates','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','rates/index','2025-10-02 13:41:42'),
(433,'track','tkn9obr6h2i35i72ur4b5ps25ra4bi2s','Openvoips Support','SYSTEM','122.169.33.222','122.169.33.222','/tariffs/apiTM/BUTA58?_=1759412502826','https://app.24ithub.com/rates/index/','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','tariffs/apiTM','2025-10-02 13:41:47'),
(434,'track','tkn9obr6h2i35i72ur4b5ps25ra4bi2s','Openvoips Support','SYSTEM','122.169.33.222','122.169.33.222','/rates/index/','https://app.24ithub.com/rates/index/','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','rates/index','2025-10-02 13:41:48'),
(435,'track','tkn9obr6h2i35i72ur4b5ps25ra4bi2s','Openvoips Support','SYSTEM','122.169.33.222','122.169.33.222','/ratecard','https://app.24ithub.com/rates/index/','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','ratecard/index','2025-10-02 13:41:54'),
(436,'track','tkn9obr6h2i35i72ur4b5ps25ra4bi2s','Openvoips Support','SYSTEM','122.169.33.222','122.169.33.222','/ratecard/editRC/Q0FSUjM4','https://app.24ithub.com/ratecard','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','ratecard/editRC','2025-10-02 13:42:03'),
(437,'track','tkn9obr6h2i35i72ur4b5ps25ra4bi2s','Openvoips Support','SYSTEM','122.169.33.222','122.169.33.222','/ratecard/editRC/Q0FSUjM4','https://app.24ithub.com/ratecard/editRC/Q0FSUjM4','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','ratecard/editRC','2025-10-02 13:42:11'),
(438,'track','tkn9obr6h2i35i72ur4b5ps25ra4bi2s','Openvoips Support','SYSTEM','122.169.33.222','122.169.33.222','/ratecard','https://app.24ithub.com/ratecard/editRC/Q0FSUjM4','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','ratecard/index','2025-10-02 13:42:13'),
(439,'track','tkn9obr6h2i35i72ur4b5ps25ra4bi2s','Openvoips Support','SYSTEM','122.169.33.222','122.169.33.222','/ratecard/editRC/Q0FSUjMw','https://app.24ithub.com/ratecard','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','ratecard/editRC','2025-10-02 13:42:19'),
(440,'track','tkn9obr6h2i35i72ur4b5ps25ra4bi2s','Openvoips Support','SYSTEM','122.169.33.222','122.169.33.222','/ratecard/editRC/Q0FSUjMw','https://app.24ithub.com/ratecard/editRC/Q0FSUjMw','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','ratecard/editRC','2025-10-02 13:42:35'),
(441,'track','tkn9obr6h2i35i72ur4b5ps25ra4bi2s','Openvoips Support','SYSTEM','122.169.33.222','122.169.33.222','/ratecard','https://app.24ithub.com/ratecard/editRC/Q0FSUjMw','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','ratecard/index','2025-10-02 13:42:35'),
(442,'track','tkn9obr6h2i35i72ur4b5ps25ra4bi2s','Openvoips Support','SYSTEM','122.169.33.222','122.169.33.222','/rates','https://app.24ithub.com/ratecard','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','rates/index','2025-10-02 13:42:38'),
(443,'track','tkn9obr6h2i35i72ur4b5ps25ra4bi2s','Openvoips Support','SYSTEM','122.169.33.222','122.169.33.222','/rates/addR','https://app.24ithub.com/rates','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','rates/addR','2025-10-02 13:42:45'),
(444,'track','tkn9obr6h2i35i72ur4b5ps25ra4bi2s','Openvoips Support','SYSTEM','122.169.33.222','122.169.33.222','/rates/addR','https://app.24ithub.com/rates/addR','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','rates/addR','2025-10-02 13:43:31'),
(445,'track','tkn9obr6h2i35i72ur4b5ps25ra4bi2s','Openvoips Support','SYSTEM','122.169.33.222','122.169.33.222','/rates','https://app.24ithub.com/rates/addR','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','rates/index','2025-10-02 13:43:31'),
(446,'track','tkn9obr6h2i35i72ur4b5ps25ra4bi2s','Openvoips Support','SYSTEM','122.169.33.222','122.169.33.222','/tariffs/apiTM/SATA45?_=1759412611567','https://app.24ithub.com/rates','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','tariffs/apiTM','2025-10-02 13:43:38'),
(447,'track','tkn9obr6h2i35i72ur4b5ps25ra4bi2s','Openvoips Support','SYSTEM','122.169.33.222','122.169.33.222','/rates/index/','https://app.24ithub.com/rates','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','rates/index','2025-10-02 13:43:39'),
(448,'track','tkn9obr6h2i35i72ur4b5ps25ra4bi2s','Openvoips Support','SYSTEM','122.169.33.222','122.169.33.222','/rates/addR','https://app.24ithub.com/rates/index/','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','rates/addR','2025-10-02 13:43:42'),
(449,'track','tkn9obr6h2i35i72ur4b5ps25ra4bi2s','Openvoips Support','SYSTEM','122.169.33.222','122.169.33.222','/rates/addR','https://app.24ithub.com/rates/addR','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','rates/addR','2025-10-02 13:44:05'),
(450,'track','tkn9obr6h2i35i72ur4b5ps25ra4bi2s','Openvoips Support','SYSTEM','122.169.33.222','122.169.33.222','/rates','https://app.24ithub.com/rates/addR','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','rates/index','2025-10-02 13:44:05'),
(451,'track','tkn9obr6h2i35i72ur4b5ps25ra4bi2s','Openvoips Support','SYSTEM','122.169.33.222','122.169.33.222','/tariffs','https://app.24ithub.com/rates','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','tariffs/index','2025-10-02 13:45:08'),
(452,'track','tkn9obr6h2i35i72ur4b5ps25ra4bi2s','Openvoips Support','SYSTEM','122.169.33.222','122.169.33.222','/routes','https://app.24ithub.com/tariffs','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','routes/index','2025-10-02 13:45:13'),
(453,'track','tkn9obr6h2i35i72ur4b5ps25ra4bi2s','Openvoips Support','SYSTEM','122.169.33.222','122.169.33.222','/carriers','https://app.24ithub.com/routes','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','carriers/index','2025-10-02 13:45:15'),
(454,'track','tkn9obr6h2i35i72ur4b5ps25ra4bi2s','Openvoips Support','SYSTEM','122.169.33.222','122.169.33.222','/carriers/addC','https://app.24ithub.com/carriers','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','carriers/addC','2025-10-02 13:45:16'),
(455,'track','tkn9obr6h2i35i72ur4b5ps25ra4bi2s','Openvoips Support','SYSTEM','122.169.33.222','122.169.33.222','/ajax/ajax_get_tariff','https://app.24ithub.com/carriers/addC','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','ajax/ajax_get_tariff','2025-10-02 13:45:33'),
(456,'track','oaag5kvv8rbo0ba81brhc7qcrahs1g6o','Openvoips Support','SYSTEM','122.169.33.222','122.169.33.222','/carriers/addC','https://app.24ithub.com/carriers/addC','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','carriers/addC','2025-10-02 13:45:47'),
(457,'track','oaag5kvv8rbo0ba81brhc7qcrahs1g6o','Openvoips Support','SYSTEM','122.169.33.222','122.169.33.222','/carriers/edit/T1ZEQzIz','https://app.24ithub.com/carriers/addC','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','carriers/edit','2025-10-02 13:45:47'),
(458,'track','oaag5kvv8rbo0ba81brhc7qcrahs1g6o','Openvoips Support','SYSTEM','122.169.33.222','122.169.33.222','/theme/default/css/tabs.css','https://app.24ithub.com/carriers/edit/T1ZEQzIz','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','/index','2025-10-02 13:45:47'),
(459,'track','oaag5kvv8rbo0ba81brhc7qcrahs1g6o','Openvoips Support','SYSTEM','122.169.33.222','122.169.33.222','/dashboard','https://app.24ithub.com/carriers/edit/T1ZEQzIz','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','dashboard/index','2025-10-02 13:45:47'),
(460,'track','oaag5kvv8rbo0ba81brhc7qcrahs1g6o','Openvoips Support','SYSTEM','122.169.33.222','122.169.33.222','/ajax/ajax_get_tariff','https://app.24ithub.com/carriers/edit/T1ZEQzIz','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','ajax/ajax_get_tariff','2025-10-02 13:45:47'),
(461,'track','oaag5kvv8rbo0ba81brhc7qcrahs1g6o','Openvoips Support','SYSTEM','122.169.33.222','122.169.33.222','/carriers/addG/T1ZEQzIz/3','https://app.24ithub.com/carriers/edit/T1ZEQzIz','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','carriers/addG','2025-10-02 13:45:53'),
(462,'track','oaag5kvv8rbo0ba81brhc7qcrahs1g6o','Openvoips Support','SYSTEM','122.169.33.222','122.169.33.222','/carriers/addG/T1ZEQzIz/3','https://app.24ithub.com/carriers/addG/T1ZEQzIz/3','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','carriers/addG','2025-10-02 13:46:04'),
(463,'track','oaag5kvv8rbo0ba81brhc7qcrahs1g6o','Openvoips Support','SYSTEM','122.169.33.222','122.169.33.222','/carriers/edit/T1ZEQzIz/3','https://app.24ithub.com/carriers/addG/T1ZEQzIz/3','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','carriers/edit','2025-10-02 13:46:04'),
(464,'track','oaag5kvv8rbo0ba81brhc7qcrahs1g6o','Openvoips Support','SYSTEM','122.169.33.222','122.169.33.222','/theme/default/css/tabs.css','https://app.24ithub.com/carriers/edit/T1ZEQzIz/3','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','/index','2025-10-02 13:46:04'),
(465,'track','oaag5kvv8rbo0ba81brhc7qcrahs1g6o','Openvoips Support','SYSTEM','122.169.33.222','122.169.33.222','/dashboard','https://app.24ithub.com/carriers/edit/T1ZEQzIz/3','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','dashboard/index','2025-10-02 13:46:04'),
(466,'track','oaag5kvv8rbo0ba81brhc7qcrahs1g6o','Openvoips Support','SYSTEM','122.169.33.222','122.169.33.222','/ajax/ajax_get_tariff','https://app.24ithub.com/carriers/edit/T1ZEQzIz/3','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','ajax/ajax_get_tariff','2025-10-02 13:46:05'),
(467,'track','oaag5kvv8rbo0ba81brhc7qcrahs1g6o','Openvoips Support','SYSTEM','122.169.33.222','122.169.33.222','/routes','https://app.24ithub.com/carriers/edit/T1ZEQzIz/3','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','routes/index','2025-10-02 13:46:21'),
(468,'track','oaag5kvv8rbo0ba81brhc7qcrahs1g6o','Openvoips Support','SYSTEM','122.169.33.222','122.169.33.222','/routes/addR','https://app.24ithub.com/routes','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','routes/addR','2025-10-02 13:46:29'),
(469,'track','oaag5kvv8rbo0ba81brhc7qcrahs1g6o','Openvoips Support','SYSTEM','122.169.33.222','122.169.33.222','/routes/addR','https://app.24ithub.com/routes/addR','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','routes/addR','2025-10-02 13:46:46'),
(470,'track','oaag5kvv8rbo0ba81brhc7qcrahs1g6o','Openvoips Support','SYSTEM','122.169.33.222','122.169.33.222','/routes','https://app.24ithub.com/routes/addR','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','routes/index','2025-10-02 13:46:46'),
(471,'track','oaag5kvv8rbo0ba81brhc7qcrahs1g6o','Openvoips Support','SYSTEM','122.169.33.222','122.169.33.222','/dialplans/index/','https://app.24ithub.com/routes','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','dialplans/index','2025-10-02 13:46:49'),
(472,'track','oaag5kvv8rbo0ba81brhc7qcrahs1g6o','Openvoips Support','SYSTEM','122.169.33.222','122.169.33.222','/dialplans/addD','https://app.24ithub.com/dialplans/index/','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','dialplans/addD','2025-10-02 13:46:51'),
(473,'track','oaag5kvv8rbo0ba81brhc7qcrahs1g6o','Openvoips Support','SYSTEM','122.169.33.222','122.169.33.222','/dialplans/addD','https://app.24ithub.com/dialplans/addD','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','dialplans/addD','2025-10-02 13:46:58'),
(474,'track','oaag5kvv8rbo0ba81brhc7qcrahs1g6o','Openvoips Support','SYSTEM','122.169.33.222','122.169.33.222','/dialplans/index/','https://app.24ithub.com/dialplans/addD','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','dialplans/index','2025-10-02 13:46:58'),
(475,'track','oaag5kvv8rbo0ba81brhc7qcrahs1g6o','Openvoips Support','SYSTEM','122.169.33.222','122.169.33.222','/crs','https://app.24ithub.com/dialplans/index/','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','crs/index','2025-10-02 13:47:09'),
(476,'track','oaag5kvv8rbo0ba81brhc7qcrahs1g6o','Openvoips Support','SYSTEM','122.169.33.222','122.169.33.222','/crs/customers/add','https://app.24ithub.com/crs','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','customers/add','2025-10-02 13:47:14'),
(477,'track','oaag5kvv8rbo0ba81brhc7qcrahs1g6o','Openvoips Support','SYSTEM','122.169.33.222','122.169.33.222','/crs/ajax/ajax_get_tariff','https://app.24ithub.com/crs/customers/add','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','ajax/ajax_get_tariff','2025-10-02 13:47:23'),
(478,'track','oaag5kvv8rbo0ba81brhc7qcrahs1g6o','Openvoips Support','SYSTEM','122.169.33.222','122.169.33.222','/crs/customers/add','https://app.24ithub.com/crs/customers/add','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','customers/add','2025-10-02 13:48:40'),
(479,'track','oaag5kvv8rbo0ba81brhc7qcrahs1g6o','Openvoips Support','SYSTEM','122.169.33.222','122.169.33.222','/crs/ajax/ajax_get_tariff','https://app.24ithub.com/crs/customers/add','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','ajax/ajax_get_tariff','2025-10-02 13:48:41'),
(480,'track','oaag5kvv8rbo0ba81brhc7qcrahs1g6o','Openvoips Support','SYSTEM','122.169.33.222','122.169.33.222','/crs/customers/add','https://app.24ithub.com/crs/customers/add','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','customers/add','2025-10-02 13:48:56'),
(481,'track','oaag5kvv8rbo0ba81brhc7qcrahs1g6o','Openvoips Support','SYSTEM','122.169.33.222','122.169.33.222','/crs/ajax/ajax_get_tariff','https://app.24ithub.com/crs/customers/add','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','ajax/ajax_get_tariff','2025-10-02 13:48:56'),
(482,'track','oaag5kvv8rbo0ba81brhc7qcrahs1g6o','Openvoips Support','SYSTEM','122.169.33.222','122.169.33.222','/crs/customers/add','https://app.24ithub.com/crs/customers/add','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','customers/add','2025-10-02 13:49:07'),
(483,'track','27c3s1hot8d0kh1str96fg3jfeidqs35','','','173.208.52.222','173.208.52.222','/billing/api?ACCOUNTID=OV500&REQUEST=OPENINGBALANCE&SERVICENUMBER=&CREATEDBY=OV500','','','billing/api','2025-10-02 13:49:07'),
(484,'track','oaag5kvv8rbo0ba81brhc7qcrahs1g6o','Openvoips Support','SYSTEM','122.169.33.222','122.169.33.222','/crs/customers','https://app.24ithub.com/crs/customers/add','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','customers/index','2025-10-02 13:49:07'),
(485,'track','oaag5kvv8rbo0ba81brhc7qcrahs1g6o','Openvoips Support','SYSTEM','122.169.33.222','122.169.33.222','/crs','https://app.24ithub.com/crs/customers','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','crs/index','2025-10-02 13:49:08'),
(486,'track','oaag5kvv8rbo0ba81brhc7qcrahs1g6o','Openvoips Support','SYSTEM','122.169.33.222','122.169.33.222','/crs/editvoip/T1Y1MDA_EQUALS_','https://app.24ithub.com/crs','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','crs/editvoip','2025-10-02 13:49:11'),
(487,'track','oaag5kvv8rbo0ba81brhc7qcrahs1g6o','Openvoips Support','SYSTEM','122.169.33.222','122.169.33.222','/theme/default/css/tabs.css','https://app.24ithub.com/crs/editvoip/T1Y1MDA_EQUALS_','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','/index','2025-10-02 13:49:11'),
(488,'track','oaag5kvv8rbo0ba81brhc7qcrahs1g6o','Openvoips Support','SYSTEM','122.169.33.222','122.169.33.222','/dashboard','https://app.24ithub.com/crs/editvoip/T1Y1MDA_EQUALS_','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','dashboard/index','2025-10-02 13:49:12'),
(489,'track','oaag5kvv8rbo0ba81brhc7qcrahs1g6o','Openvoips Support','SYSTEM','122.169.33.222','122.169.33.222','/crs/ajax/ajax_get_tariff','https://app.24ithub.com/crs/editvoip/T1Y1MDA_EQUALS_','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','ajax/ajax_get_tariff','2025-10-02 13:49:12'),
(490,'track','oaag5kvv8rbo0ba81brhc7qcrahs1g6o','Openvoips Support','SYSTEM','122.169.33.222','122.169.33.222','/crs/addvoip/T1Y1MDA_EQUALS_','https://app.24ithub.com/crs/editvoip/T1Y1MDA_EQUALS_','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','crs/addvoip','2025-10-02 13:49:16'),
(491,'track','85n9tohbttrf5rd2odaihrb5qth6fmf3','','','173.208.52.222','173.208.52.222','/billing/api?RULETYPE=OPENINGBALANCE&ACCOUNTID=OV500&QUANTITY=1&SERVICENUMBER=SATA45&REQUEST=TARIFFCHARGES','','','billing/api','2025-10-02 13:49:16'),
(492,'track','oaag5kvv8rbo0ba81brhc7qcrahs1g6o','Openvoips Support','SYSTEM','122.169.33.222','122.169.33.222','/crs/editvoip/T1Y1MDA_EQUALS_/2','https://app.24ithub.com/crs/editvoip/T1Y1MDA_EQUALS_','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','crs/editvoip','2025-10-02 13:49:16'),
(493,'track','oaag5kvv8rbo0ba81brhc7qcrahs1g6o','Openvoips Support','SYSTEM','122.169.33.222','122.169.33.222','/theme/default/css/tabs.css','https://app.24ithub.com/crs/editvoip/T1Y1MDA_EQUALS_/2','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','/index','2025-10-02 13:49:16'),
(494,'track','oaag5kvv8rbo0ba81brhc7qcrahs1g6o','Openvoips Support','SYSTEM','122.169.33.222','122.169.33.222','/dashboard','https://app.24ithub.com/crs/editvoip/T1Y1MDA_EQUALS_/2','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','dashboard/index','2025-10-02 13:49:16'),
(495,'track','oaag5kvv8rbo0ba81brhc7qcrahs1g6o','Openvoips Support','SYSTEM','122.169.33.222','122.169.33.222','/crs/ajax/ajax_get_tariff','https://app.24ithub.com/crs/editvoip/T1Y1MDA_EQUALS_/2','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','ajax/ajax_get_tariff','2025-10-02 13:49:16'),
(496,'track','oaag5kvv8rbo0ba81brhc7qcrahs1g6o','Openvoips Support','SYSTEM','122.169.33.222','122.169.33.222','/crs/editvoip/T1Y1MDA_EQUALS_/2','https://app.24ithub.com/crs/editvoip/T1Y1MDA_EQUALS_/2','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','crs/editvoip','2025-10-02 13:49:24'),
(497,'track','oaag5kvv8rbo0ba81brhc7qcrahs1g6o','Openvoips Support','SYSTEM','122.169.33.222','122.169.33.222','/crs/editvoip/T1Y1MDA_EQUALS_/4','https://app.24ithub.com/crs/editvoip/T1Y1MDA_EQUALS_/2','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','crs/editvoip','2025-10-02 13:49:24'),
(498,'track','oaag5kvv8rbo0ba81brhc7qcrahs1g6o','Openvoips Support','SYSTEM','122.169.33.222','122.169.33.222','/theme/default/css/tabs.css','https://app.24ithub.com/crs/editvoip/T1Y1MDA_EQUALS_/4','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','/index','2025-10-02 13:49:25'),
(499,'track','oaag5kvv8rbo0ba81brhc7qcrahs1g6o','Openvoips Support','SYSTEM','122.169.33.222','122.169.33.222','/dashboard','https://app.24ithub.com/crs/editvoip/T1Y1MDA_EQUALS_/4','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','dashboard/index','2025-10-02 13:49:25'),
(500,'track','oaag5kvv8rbo0ba81brhc7qcrahs1g6o','Openvoips Support','SYSTEM','122.169.33.222','122.169.33.222','/crs/ajax/ajax_get_tariff','https://app.24ithub.com/crs/editvoip/T1Y1MDA_EQUALS_/4','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','ajax/ajax_get_tariff','2025-10-02 13:49:25'),
(501,'track','oaag5kvv8rbo0ba81brhc7qcrahs1g6o','Openvoips Support','SYSTEM','122.169.33.222','122.169.33.222','/crs/editvoip/T1Y1MDA_EQUALS_/4','https://app.24ithub.com/crs/editvoip/T1Y1MDA_EQUALS_/4','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','crs/editvoip','2025-10-02 13:49:28'),
(502,'track','oaag5kvv8rbo0ba81brhc7qcrahs1g6o','Openvoips Support','SYSTEM','122.169.33.222','122.169.33.222','/crs/editvoip/T1Y1MDA_EQUALS_/5','https://app.24ithub.com/crs/editvoip/T1Y1MDA_EQUALS_/4','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','crs/editvoip','2025-10-02 13:49:28'),
(503,'track','oaag5kvv8rbo0ba81brhc7qcrahs1g6o','Openvoips Support','SYSTEM','122.169.33.222','122.169.33.222','/theme/default/css/tabs.css','https://app.24ithub.com/crs/editvoip/T1Y1MDA_EQUALS_/5','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','/index','2025-10-02 13:49:28'),
(504,'track','oaag5kvv8rbo0ba81brhc7qcrahs1g6o','Openvoips Support','SYSTEM','122.169.33.222','122.169.33.222','/dashboard','https://app.24ithub.com/crs/editvoip/T1Y1MDA_EQUALS_/5','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','dashboard/index','2025-10-02 13:49:28'),
(505,'track','oaag5kvv8rbo0ba81brhc7qcrahs1g6o','Openvoips Support','SYSTEM','122.169.33.222','122.169.33.222','/crs/ajax/ajax_get_tariff','https://app.24ithub.com/crs/editvoip/T1Y1MDA_EQUALS_/5','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','ajax/ajax_get_tariff','2025-10-02 13:49:28'),
(506,'track','oaag5kvv8rbo0ba81brhc7qcrahs1g6o','Openvoips Support','SYSTEM','122.169.33.222','122.169.33.222','/crs/sipAdd/T1Y1MDA_EQUALS_/7','https://app.24ithub.com/crs/editvoip/T1Y1MDA_EQUALS_/5','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','crs/sipAdd','2025-10-02 13:49:32'),
(507,'track','oaag5kvv8rbo0ba81brhc7qcrahs1g6o','Openvoips Support','SYSTEM','122.169.33.222','122.169.33.222','/crs/sipAdd/T1Y1MDA_EQUALS_/7','https://app.24ithub.com/crs/sipAdd/T1Y1MDA_EQUALS_/7','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','crs/sipAdd','2025-10-02 13:49:52'),
(508,'track','oaag5kvv8rbo0ba81brhc7qcrahs1g6o','Openvoips Support','SYSTEM','122.169.33.222','122.169.33.222','/crs/editvoip/T1Y1MDA_EQUALS_/7','https://app.24ithub.com/crs/sipAdd/T1Y1MDA_EQUALS_/7','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','crs/editvoip','2025-10-02 13:49:52'),
(509,'track','oaag5kvv8rbo0ba81brhc7qcrahs1g6o','Openvoips Support','SYSTEM','122.169.33.222','122.169.33.222','/theme/default/css/tabs.css','https://app.24ithub.com/crs/editvoip/T1Y1MDA_EQUALS_/7','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','/index','2025-10-02 13:49:52'),
(510,'track','oaag5kvv8rbo0ba81brhc7qcrahs1g6o','Openvoips Support','SYSTEM','122.169.33.222','122.169.33.222','/dashboard','https://app.24ithub.com/crs/editvoip/T1Y1MDA_EQUALS_/7','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','dashboard/index','2025-10-02 13:49:52'),
(511,'track','oaag5kvv8rbo0ba81brhc7qcrahs1g6o','Openvoips Support','SYSTEM','122.169.33.222','122.169.33.222','/crs/ajax/ajax_get_tariff','https://app.24ithub.com/crs/editvoip/T1Y1MDA_EQUALS_/7','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','ajax/ajax_get_tariff','2025-10-02 13:49:52'),
(512,'track','oaag5kvv8rbo0ba81brhc7qcrahs1g6o','Openvoips Support','SYSTEM','122.169.33.222','122.169.33.222','/crs/customers/edit/T1Y1MDA_EQUALS_/1','https://app.24ithub.com/crs/editvoip/T1Y1MDA_EQUALS_/7','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','customers/edit','2025-10-02 13:49:59'),
(513,'track','oaag5kvv8rbo0ba81brhc7qcrahs1g6o','Openvoips Support','SYSTEM','122.169.33.222','122.169.33.222','/theme/default/css/tabs.css','https://app.24ithub.com/crs/customers/edit/T1Y1MDA_EQUALS_/1','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','/index','2025-10-02 13:50:00'),
(514,'track','oaag5kvv8rbo0ba81brhc7qcrahs1g6o','Openvoips Support','SYSTEM','122.169.33.222','122.169.33.222','/dashboard','https://app.24ithub.com/crs/customers/edit/T1Y1MDA_EQUALS_/1','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','dashboard/index','2025-10-02 13:50:00'),
(515,'track','oaag5kvv8rbo0ba81brhc7qcrahs1g6o','Openvoips Support','SYSTEM','122.169.33.222','122.169.33.222','/crs/editvoip/T1Y1MDA_EQUALS_/7','https://app.24ithub.com/crs/customers/edit/T1Y1MDA_EQUALS_/1','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','crs/editvoip','2025-10-02 13:50:02'),
(516,'track','oaag5kvv8rbo0ba81brhc7qcrahs1g6o','Openvoips Support','SYSTEM','122.169.33.222','122.169.33.222','/theme/default/css/tabs.css','https://app.24ithub.com/crs/editvoip/T1Y1MDA_EQUALS_/7','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','/index','2025-10-02 13:50:03'),
(517,'track','oaag5kvv8rbo0ba81brhc7qcrahs1g6o','Openvoips Support','SYSTEM','122.169.33.222','122.169.33.222','/dashboard','https://app.24ithub.com/crs/editvoip/T1Y1MDA_EQUALS_/7','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','dashboard/index','2025-10-02 13:50:03'),
(518,'track','oaag5kvv8rbo0ba81brhc7qcrahs1g6o','Openvoips Support','SYSTEM','122.169.33.222','122.169.33.222','/crs/ajax/ajax_get_tariff','https://app.24ithub.com/crs/editvoip/T1Y1MDA_EQUALS_/7','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','ajax/ajax_get_tariff','2025-10-02 13:50:03'),
(519,'track','ggvqa6bpiqmeha8hnhse6hj5s7vqfj80','','','185.244.104.2','185.244.104.2','/','http://173.208.52.222:443/','-','login/index','2025-10-02 13:54:59'),
(520,'track','8jtt3t683kfnhl6ju229iqle2magpn01','Openvoips Support','SYSTEM','122.169.33.222','122.169.33.222','/crs/sipEdit/T1Y1MDA_EQUALS_/MQ_EQUALS__EQUALS_/7','https://app.24ithub.com/crs/editvoip/T1Y1MDA_EQUALS_/7','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','crs/sipEdit','2025-10-02 14:01:26'),
(521,'track','8jtt3t683kfnhl6ju229iqle2magpn01','Openvoips Support','SYSTEM','122.169.33.222','122.169.33.222','/crs/sipEdit/T1Y1MDA_EQUALS_/MQ_EQUALS__EQUALS_/7','https://app.24ithub.com/crs/sipEdit/T1Y1MDA_EQUALS_/MQ_EQUALS__EQUALS_/7','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','crs/sipEdit','2025-10-02 14:01:38'),
(522,'track','8jtt3t683kfnhl6ju229iqle2magpn01','Openvoips Support','SYSTEM','122.169.33.222','122.169.33.222','/crs/sipEdit/T1Y1MDA_EQUALS_/MQ_EQUALS__EQUALS_/7','https://app.24ithub.com/crs/sipEdit/T1Y1MDA_EQUALS_/MQ_EQUALS__EQUALS_/7','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','crs/sipEdit','2025-10-02 14:01:38'),
(523,'track','kf2hgoet3gilplv3jfn80q8pn9s4knid','','','104.252.191.225','104.252.191.225','/','','Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/116.0.0.0 Safari/537.36','login/index','2025-10-02 14:03:56'),
(524,'track','g25dmt74u623u1b063esp9i5682npf08','','','103.196.9.31','103.196.9.31','/','','Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/117.0.0.0 Safari/537.36','login/index','2025-10-02 14:03:56'),
(525,'track','vpmdianb98fs1qh57impagam7a22brmr','','','104.252.191.225','104.252.191.225','/','','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/117.0.0.0 Safari/537.36','login/index','2025-10-02 14:03:56'),
(526,'track','vpmdianb98fs1qh57impagam7a22brmr','','','104.252.191.225','104.252.191.225','/https%3A/fonts.googleapis.com/css%3Ffamily%3DRoboto%3A400%2C700%26subset%3Dlatin%2Ccyrillic-ext','','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/117.0.0.0 Safari/537.36','/index','2025-10-02 14:03:57'),
(527,'track','vpmdianb98fs1qh57impagam7a22brmr','','','104.252.191.225','104.252.191.225','/login','','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/117.0.0.0 Safari/537.36','login/index','2025-10-02 14:03:57'),
(528,'track','vpmdianb98fs1qh57impagam7a22brmr','','','104.252.191.225','104.252.191.225','/https%3A/fonts.googleapis.com/icon%3Ffamily%3DMaterial%2BIcons','','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/117.0.0.0 Safari/537.36','/index','2025-10-02 14:03:57'),
(529,'track','nrpa4t3ujfqtn30svgvvrbqiheu6n0s8','','','184.75.213.90','184.75.213.90','/admin/config.php','','xfa1','/index','2025-10-02 14:27:40'),
(530,'track','9edqpe833ehdrn58l97if2kugpdqi6l3','','','51.159.100.253','51.159.100.253','/','','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/132.0.0.0 Safari/537.3','login/index','2025-10-02 14:28:45'),
(531,'track','prc6unhu8m2q5nb66v595krehihj6qqg','','','133.242.174.119','133.242.174.119','/robots.txt','','Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Mobile Safari/537.36','/index','2025-10-02 14:35:27'),
(532,'track','prc6unhu8m2q5nb66v595krehihj6qqg','','','133.242.174.119','133.242.174.119','/login','','Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Mobile Safari/537.36','login/index','2025-10-02 14:35:28'),
(533,'track','prc6unhu8m2q5nb66v595krehihj6qqg','','','133.242.174.119','133.242.174.119','/','','Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Mobile Safari/537.36','login/index','2025-10-02 14:35:29'),
(534,'track','4l0ub9jaoib9jdntvvksepvc4ulrl4fk','','','185.244.104.2','185.244.104.2','/','http://173.208.52.222:443/','-','login/index','2025-10-02 14:40:39'),
(535,'track','08r1ktc47qcuvr0bqd4gmu5fre9m81d7','','','185.203.132.199','185.203.132.199','/','','Mozilla/5.0 (Linux; Android 6.0; Nexus 5 Build/MRA58N) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/130.0.0.0 Mobile Safari/537.36','login/index','2025-10-02 14:43:05'),
(536,'track','mji8hgahmvpfoihqecql0n43mgral7kg','','','185.203.132.199','185.203.132.199','/css/support_parent.css','','Mozilla/5.0 (Linux; Android 6.0; Nexus 5 Build/MRA58N) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/130.0.0.0 Mobile Safari/537.36','/index','2025-10-02 14:43:05'),
(537,'track','fg919pmuimlq7p99ao7hs38e6th8nk0g','','','185.203.132.199','185.203.132.199','/login','https://app.24ithub.com/css/support_parent.css','Mozilla/5.0 (Linux; Android 6.0; Nexus 5 Build/MRA58N) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/130.0.0.0 Mobile Safari/537.36','login/index','2025-10-02 14:43:05'),
(538,'track','jpqt2sasfhg846b38h49rojkc8mjgmnu','','','185.203.132.199','185.203.132.199','/js/lkk_ch.js','','Mozilla/5.0 (Linux; Android 6.0; Nexus 5 Build/MRA58N) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/130.0.0.0 Mobile Safari/537.36','/index','2025-10-02 14:43:08'),
(539,'track','4adiol1je2dvm963oum9jt2pu49rpc7d','','','185.203.132.199','185.203.132.199','/login','https://app.24ithub.com/js/lkk_ch.js','Mozilla/5.0 (Linux; Android 6.0; Nexus 5 Build/MRA58N) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/130.0.0.0 Mobile Safari/537.36','login/index','2025-10-02 14:43:08'),
(540,'track','rem46ip4vg1bo80mdh42e1ph21oidf1c','','','185.203.132.199','185.203.132.199','/js/twint_ch.js','','Mozilla/5.0 (Linux; Android 6.0; Nexus 5 Build/MRA58N) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/130.0.0.0 Mobile Safari/537.36','/index','2025-10-02 14:43:08'),
(541,'track','av2kfh58ic923b4lpgqkccs31dtqe2jc','','','185.203.132.199','185.203.132.199','/login','https://app.24ithub.com/js/twint_ch.js','Mozilla/5.0 (Linux; Android 6.0; Nexus 5 Build/MRA58N) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/130.0.0.0 Mobile Safari/537.36','login/index','2025-10-02 14:43:08'),
(542,'track','g044ri7eb83ni1rvq0fdh44p2simck46','','','34.38.83.65','34.38.83.65','/','','python-requests/2.32.5','login/index','2025-10-02 14:50:16'),
(543,'track','t3kh1ocb60l9p8524eab63hvvvgltkpg','','','185.244.104.2','185.244.104.2','/','http://173.208.52.222:443/','-','login/index','2025-10-02 15:09:15'),
(544,'track','3sr3il6j40k2l5cn5161f6eqeo7s5m8o','','','191.242.209.98','191.242.209.98','/admin/config.php','','xfa1','/index','2025-10-02 15:17:46'),
(545,'track','1c9l2rd82237jf7k4k80o25huh0s55ko','','','139.59.112.241','139.59.112.241','/','http://app.24ithub.com/','Mozilla/5.0 (Windows NT 6.1; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/45.0.2454.85 Safari/537.36','login/index','2025-10-02 15:26:31'),
(546,'track','bga89agki50f8bd5q2ek19giu6r596j7','','','185.244.104.2','185.244.104.2','/','http://173.208.52.222:443/','-','login/index','2025-10-02 16:08:20'),
(547,'track','0le4527n5v3g9pd72oj76l42fvjmji9k','','','185.244.104.2','185.244.104.2','/','http://173.208.52.222:443/','-','login/index','2025-10-02 16:44:09'),
(548,'track','3u5qfk4oun8aa43ji26o6tgablm2l0ri','','','51.159.100.254','51.159.100.254','/','','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/132.0.0.0 Safari/537.3','login/index','2025-10-02 16:53:19'),
(549,'track','idn1lru479fufjgo8q41a5bj0qag9s2i','','','51.159.100.254','51.159.100.254','/ads.txt','','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/132.0.0.0 Safari/537.3','/index','2025-10-02 16:53:31'),
(550,'track','idn1lru479fufjgo8q41a5bj0qag9s2i','','','51.159.100.254','51.159.100.254','/login','','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/132.0.0.0 Safari/537.3','login/index','2025-10-02 16:53:31'),
(551,'track','4973tuklgq2hik74752lcnmg1spfniia','','','51.159.100.254','51.159.100.254','/app-ads.txt','','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/132.0.0.0 Safari/537.3','/index','2025-10-02 16:53:31'),
(552,'track','4973tuklgq2hik74752lcnmg1spfniia','','','51.159.100.254','51.159.100.254','/login','','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/132.0.0.0 Safari/537.3','login/index','2025-10-02 16:53:31'),
(553,'track','m8esdavcmrtgu4bf475uucci6k0ga79b','','','51.159.100.254','51.159.100.254','/sellers.json','','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/132.0.0.0 Safari/537.3','/index','2025-10-02 16:53:31'),
(554,'track','m8esdavcmrtgu4bf475uucci6k0ga79b','','','51.159.100.254','51.159.100.254','/login','','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/132.0.0.0 Safari/537.3','login/index','2025-10-02 16:53:31'),
(555,'track','5l1u9sf9lk6cgdg8idaa12020aoirl4d','','','185.244.104.2','185.244.104.2','/','http://173.208.52.222:443/','-','login/index','2025-10-02 17:06:10'),
(556,'track','v5ctjubjtrve01jrp3jj6stsnofrfvof','','','144.126.197.42','144.126.197.42','/.env','','Mozilla/5.0; Keydrop.io/1.0(onlyscans.com/about);','/index','2025-10-02 17:07:58'),
(557,'track','ppa451ifhgcm63357btsj8g1n8iu36du','','','144.126.197.42','144.126.197.42','/.git/config','','Mozilla/5.0; Keydrop.io/1.0(onlyscans.com/about);','/index','2025-10-02 17:07:58'),
(558,'track','qlvak4jhv1jagfkola469trflnatntvk','','','93.123.109.214','93.123.109.214','/','','l9tcpid/v1.1.0','login/index','2025-10-02 17:34:39'),
(559,'track','o7tmlnv1spphqsbfsndh54dfajfpssf2','','','205.210.31.135','205.210.31.135','/','','Hello from Palo Alto Networks, find out more about our scans in https://docs-cortex.paloaltonetworks.com/r/1/Cortex-Xpanse/Scanning-activity','login/index','2025-10-02 17:39:03'),
(560,'track','o54vmpp1bmgjqpiqdu222btb03gqkq9f','','','20.102.100.198','20.102.100.198','/actuator/health','','Mozilla/5.0 zgrab/0.x','/index','2025-10-02 17:41:07'),
(561,'track','a85rltcgub19hsrl22lpbdq8ubkddpo6','','','34.121.183.29','34.121.183.29','/','http://app.24ithub.com','Mozilla/5.0 (compatible; CMS-Checker/1.0; +https://example.com)','login/index','2025-10-02 17:50:09'),
(562,'track','va1o7ffaetrv0j5fvmnhqc4v2ohhm0fr','','','185.244.104.2','185.244.104.2','/','http://173.208.52.222:443/','-','login/index','2025-10-02 18:13:12'),
(563,'track','folq9ejecmfm73fi6sa2n6ll13541k2r','','','206.168.34.46','206.168.34.46','/','','Mozilla/5.0 (compatible; CensysInspect/1.1; +https://about.censys.io/)','login/index','2025-10-02 18:25:04'),
(564,'track','4pn5nnumfqs4tpgd19q6jd6k5rb28qb5','','','206.168.34.46','206.168.34.46','/.well-known/security.txt','','Mozilla/5.0 (compatible; CensysInspect/1.1; +https://about.censys.io/)','/index','2025-10-02 18:25:24'),
(565,'track','ubb73122vmmjc6k101c224pvengiors9','','','93.123.109.214','93.123.109.214','/','','l9tcpid/v1.1.0','login/index','2025-10-02 18:30:59'),
(566,'track','s4jpi6t5r00jmb3e2312c30nhpottnge','','','3.137.73.221','3.137.73.221','/','','Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) Chrome/126.0.0.0 Safari/537.36','login/index','2025-10-02 18:48:39'),
(567,'track','5hhef81hrmp48nmmuvo65irjpk5mq55n','','','195.178.110.109','195.178.110.109','/','','l9tcpid/v1.1.0','login/index','2025-10-02 18:59:24'),
(568,'track','ntt539s2ebl18iojfq34111k45tfan6t','','','185.244.104.2','185.244.104.2','/','http://173.208.52.222:443/','-','login/index','2025-10-02 19:14:06'),
(569,'track','lb357d2unl28m7oosmdi92bok503e8te','','','185.244.104.2','185.244.104.2','/','http://173.208.52.222:443/','-','login/index','2025-10-02 19:35:31'),
(570,'track','jossmomh14tmmnjbdh5kpoqq61sed7ig','','','185.180.140.102','185.180.140.102','/','','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/60.0.3112.113 Safari/537.36','login/index','2025-10-02 19:46:19'),
(571,'track','76tpjf8f2ijdq6bt282te9qnb5hirb6f','','','195.178.110.109','195.178.110.109','/','','l9tcpid/v1.1.0','login/index','2025-10-02 20:41:18'),
(572,'track','k2n328m2gvf2d3sdi17nimav22uvm13n','','','185.244.104.2','185.244.104.2','/','http://173.208.52.222:443/','-','login/index','2025-10-02 20:48:10'),
(573,'track','r9b4b0ddld2ddvchb0rtv9r3trarb36h','','','198.44.136.122','198.44.136.122','/','','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36','login/index','2025-10-02 21:10:21'),
(574,'track','p3b28rn8efc5j909cblpdu7a8cihdm5q','','','185.244.104.2','185.244.104.2','/','http://173.208.52.222:443/','-','login/index','2025-10-02 21:24:42'),
(575,'track','3d84qd7kg6cno3b4t69qdfvit9dtr6cs','','','185.244.104.2','185.244.104.2','/','http://173.208.52.222:443/','-','login/index','2025-10-02 21:47:13'),
(576,'track','ibr183rm6a3buiuk756labh2ec3j1gpi','','','157.230.21.97','157.230.21.97','/','','','login/index','2025-10-02 22:15:34'),
(577,'track','n9ir7op6pvccvkvt0n9nfq641j54ee92','','','157.230.21.97','157.230.21.97','/odinhttpcall1759443334','','Mozilla/5.0 (compatible; Odin; https://docs.getodin.com/)','/index','2025-10-02 22:15:34'),
(578,'track','mvd2dmllqf3825jg34pc0n9715j9jog9','','','157.230.21.97','157.230.21.97','/sdk','','Mozilla/5.0 (compatible; Odin; https://docs.getodin.com/)','/index','2025-10-02 22:15:34'),
(579,'track','9fs8i294v4k3o4qdvd2s3o8s6rp0ffdk','','','157.230.21.97','157.230.21.97','/HNAP1','','Mozilla/5.0 (compatible; Odin; https://docs.getodin.com/)','/index','2025-10-02 22:15:34'),
(580,'track','839iccv5c4iifo2963f5u06k6lgtepss','','','157.230.21.97','157.230.21.97','/evox/about','','Mozilla/5.0 (compatible; Odin; https://docs.getodin.com/)','/index','2025-10-02 22:15:34'),
(581,'track','56j23l8d2lidgppbp3kqcl83oj7ls8tm','','','157.230.21.97','157.230.21.97','/login','','Mozilla/5.0 (compatible; Odin; https://docs.getodin.com/)','login/index','2025-10-02 22:15:34'),
(582,'track','g0q7glskhrfk4ostpupaa35s9o1108if','','','157.230.21.97','157.230.21.97','/','','','login/index','2025-10-02 22:15:34'),
(583,'track','691bdp348ctujp25290g9b62utvalrme','','','157.230.21.97','157.230.21.97','/','','','login/index','2025-10-02 22:15:35'),
(584,'track','7hkjpffcnm766sb0m05j0pocc4qnc13e','','','164.92.253.109','164.92.253.109','/','','Go-http-client/1.1','login/index','2025-10-02 22:15:35'),
(585,'track','n7s34bu0fvin7hdu8114r1dgjc9kogo7','','','164.92.253.109','164.92.253.109','/','','Mozilla/5.0 (compatible; Odin; https://docs.getodin.com/)','login/index','2025-10-02 22:15:35'),
(586,'track','2elkve9jaf0dq9stpl0tar308mjlf6pk','','','184.75.213.90','184.75.213.90','/admin/config.php','','xfa1','/index','2025-10-02 22:16:27'),
(587,'track','bdtvgvahd582i4ufjmmpa9vuf8d7f54f','','','195.178.110.160','195.178.110.160','/','','l9tcpid/v1.1.0','login/index','2025-10-02 23:05:18'),
(588,'track','3qidph61t72ehvbj2lv3pfmo6d0p4q3o','','','185.244.104.2','185.244.104.2','/','http://173.208.52.222:443/','-','login/index','2025-10-02 23:07:09'),
(589,'track','2g9mdit28k6krk16449uui59osc6lgmq','','','195.178.110.15','195.178.110.15','/','','l9tcpid/v1.1.0','login/index','2025-10-02 23:17:04'),
(590,'track','t4kl4dvf7dtu3l2rgq8voo2db0vi10u3','','','185.244.104.2','185.244.104.2','/','http://173.208.52.222:443/','-','login/index','2025-10-02 23:37:19'),
(591,'track','k95g7serbcfk6fokd568h3ob4irfonh5','','','64.62.156.222','64.62.156.222','/','','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:107.0) Gecko/20100101 Firefox/107.0','login/index','2025-10-03 00:03:19'),
(592,'track','f60p5cvueq60ko3ea77c9fp18md3istj','','','64.62.156.230','64.62.156.230','/','','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:107.0) Gecko/20100101 Firefox/107.0','login/index','2025-10-03 00:12:33'),
(593,'track','7ln3q9trdj3po69dq1v2hmhqj17e1ne7','','','79.124.58.198','79.124.58.198','/','','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/78.0.3904.108 Safari/537.36','login/index','2025-10-03 00:20:56'),
(594,'track','rl0ujk7c5cbh1sj0frpflo8qrsao67da','','','64.62.156.222','64.62.156.222','/geoserver/web/','','Mozilla/5.0 (X11; Linux x86_64; rv:109.0) Gecko/20100101 Firefox/109.0','/index','2025-10-03 00:25:53'),
(595,'track','lsukf2affmas202guu3f6v9283g0q4hp','','','79.124.58.198','79.124.58.198','/Autodiscover/Autodiscover.xml','','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/78.0.3904.108 Safari/537.36','/index','2025-10-03 00:25:54'),
(596,'track','93retv3hkkue7q2v6loded3rs3d4plej','','','79.124.58.198','79.124.58.198','/login','https://173.208.52.222:443/Autodiscover/Autodiscover.xml','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/78.0.3904.108 Safari/537.36','login/index','2025-10-03 00:25:54'),
(597,'track','92efo5dj1q5064ni4bdq178mmp00i1m2','','','64.62.156.222','64.62.156.222','/.git/config','','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:120.0) Gecko/20100101 Firefox/120.0','/index','2025-10-03 00:30:25'),
(598,'track','23gosb3kild91bnrdid8uq0ln16fkjl2','','','20.163.15.93','20.163.15.93','/','','Mozilla/5.0 zgrab/0.x','login/index','2025-10-03 00:32:49'),
(599,'track','8hbicdb61010erkgktju5ktd7mbmr685','','','185.244.104.2','185.244.104.2','/','http://173.208.52.222:443/','-','login/index','2025-10-03 00:34:52'),
(600,'track','strduut27jbc69tlva9dd41p1sgtmga7','','','196.251.70.214','196.251.70.214','/.env','','Mozilla/5.0 (iPad; CPU OS 12_3_1 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/12.1.1 Mobile/15E148 Safari/604.1','/index','2025-10-03 00:39:56'),
(601,'track','odtckf9a5hnohnvikpn7b7e1g9csucli','','','20.163.61.13','20.163.61.13','/login','','Mozilla/5.0 zgrab/0.x','login/index','2025-10-03 00:46:49'),
(602,'track','n7j2tkqripoe795rsbupuem99q4tkl5r','','','79.124.58.198','79.124.58.198','/vendor/phpunit/phpunit/src/Util/PHP/eval-stdin.php','','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/78.0.3904.108 Safari/537.36','/index','2025-10-03 00:56:40'),
(603,'track','241h2aajdu5g79c8qqorsv9cdk76fm7m','','','79.124.58.198','79.124.58.198','/login','https://173.208.52.222:443/vendor/phpunit/phpunit/src/Util/PHP/eval-stdin.php','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/78.0.3904.108 Safari/537.36','login/index','2025-10-03 00:56:40'),
(604,'track','pgfa3qk0govaolq54dfhj9466jcmf2kr','','','79.124.58.198','79.124.58.198','/vendor/phpunit/phpunit/src/Util/PHP/eval-stdin.php','','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/78.0.3904.108 Safari/537.36','/index','2025-10-03 01:06:30'),
(605,'track','j8roa7js6jpld7gug209icpegase7umn','','','79.124.58.198','79.124.58.198','/login','https://173.208.52.222:443/vendor/phpunit/phpunit/src/Util/PHP/eval-stdin.php','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/78.0.3904.108 Safari/537.36','login/index','2025-10-03 01:06:30'),
(606,'track','e2ra1lktmjo4g9lufp43ntdr29mqf0q0','','','185.244.104.2','185.244.104.2','/','http://173.208.52.222:443/','-','login/index','2025-10-03 01:14:34'),
(607,'track','8iioaj7a52ec21e79u7nd6snk31adi4p','','','134.209.192.233','134.209.192.233','/','','Mozilla/5.0 (X11; Linux x86_64; rv:139.0) Gecko/20100101 Firefox/139.0','login/index','2025-10-03 01:31:31'),
(608,'track','137ilsku1cgg2oudvg70703bvqolhoag','','','79.124.58.198','79.124.58.198','/?XDEBUG_SESSION_START=phpstorm','','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/78.0.3904.108 Safari/537.36','login/index','2025-10-03 02:08:32'),
(609,'track','qvc7ta4k4ho0c8vu50fgqq4ig9oeh95l','','','185.244.104.2','185.244.104.2','/','http://173.208.52.222:443/','-','login/index','2025-10-03 02:09:27'),
(610,'track','u9m85abocngfe3quk0tsbt7lq75aebt2','','','185.244.104.2','185.244.104.2','/','http://173.208.52.222:443/','-','login/index','2025-10-03 02:16:38'),
(611,'track','cstlaj7m9guknpkar7n31k8v9cphoj00','','','20.197.177.101','20.197.177.101','/','','Mozilla/5.0 (Macintosh; Intel Mac OS X 10_12_1) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/54.0.2840.98 Safari/537.36','login/index','2025-10-03 02:25:13'),
(612,'track','fq8gdc8hvs5h8fibhrq447mbbadenv3f','','','185.203.132.199','185.203.132.199','/','','Mozilla/5.0 (Linux; Android 6.0; Nexus 5 Build/MRA58N) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/130.0.0.0 Mobile Safari/537.36','login/index','2025-10-03 02:32:35'),
(613,'track','a18rtm134crq21dedubk33igfps4dhm0','','','185.203.132.199','185.203.132.199','/js/twint_ch.js','','Mozilla/5.0 (Linux; Android 6.0; Nexus 5 Build/MRA58N) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/130.0.0.0 Mobile Safari/537.36','/index','2025-10-03 02:32:35'),
(614,'track','vd0ptahlpdsug87d3em8psurg60f2j5l','','','185.203.132.199','185.203.132.199','/login','https://app.24ithub.com/js/twint_ch.js','Mozilla/5.0 (Linux; Android 6.0; Nexus 5 Build/MRA58N) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/130.0.0.0 Mobile Safari/537.36','login/index','2025-10-03 02:32:35'),
(615,'track','b5soflm6qntb93mi5686vn3tvu8iagc4','','','185.203.132.199','185.203.132.199','/css/support_parent.css','','Mozilla/5.0 (Linux; Android 6.0; Nexus 5 Build/MRA58N) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/130.0.0.0 Mobile Safari/537.36','/index','2025-10-03 02:32:37'),
(616,'track','lqlnieur8icdt7jldqv2g341na1ogoo4','','','185.203.132.199','185.203.132.199','/login','https://app.24ithub.com/css/support_parent.css','Mozilla/5.0 (Linux; Android 6.0; Nexus 5 Build/MRA58N) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/130.0.0.0 Mobile Safari/537.36','login/index','2025-10-03 02:32:37'),
(617,'track','4s78re1vb8mv5b0g9nrlm5tkspuivmg7','','','185.203.132.199','185.203.132.199','/js/lkk_ch.js','','Mozilla/5.0 (Linux; Android 6.0; Nexus 5 Build/MRA58N) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/130.0.0.0 Mobile Safari/537.36','/index','2025-10-03 02:32:37'),
(618,'track','dvtlqhg3tk2fp8qr7294gg8dl2l75ftl','','','185.203.132.199','185.203.132.199','/login','https://app.24ithub.com/js/lkk_ch.js','Mozilla/5.0 (Linux; Android 6.0; Nexus 5 Build/MRA58N) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/130.0.0.0 Mobile Safari/537.36','login/index','2025-10-03 02:32:37'),
(619,'track','1gb4jkr96ht064frg5sfn1nkfd88642n','','','79.124.58.198','79.124.58.198','/console/','','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/78.0.3904.108 Safari/537.36','/index','2025-10-03 02:35:05'),
(620,'track','h80si1dijthji452u702idjqbgaf2o9t','','','79.124.58.198','79.124.58.198','/login','https://173.208.52.222:443/console/','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/78.0.3904.108 Safari/537.36','login/index','2025-10-03 02:35:05'),
(621,'track','4f7l2okfmgse9ehs98sdm1sc275s3g7t','','','79.124.58.198','79.124.58.198','/_ignition/execute-solution','','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/78.0.3904.108 Safari/537.36','/index','2025-10-03 02:40:32'),
(622,'track','psurcukga0b51jpcpa6nvnlkup5bvnq1','','','79.124.58.198','79.124.58.198','/login','https://173.208.52.222:443/_ignition/execute-solution','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/78.0.3904.108 Safari/537.36','login/index','2025-10-03 02:40:32'),
(623,'track','hm7qcfk8nf431s5hvqa99q1rjl5rognh','','','162.216.149.126','162.216.149.126','/','','Hello from Palo Alto Networks, find out more about our scans in https://docs-cortex.paloaltonetworks.com/r/1/Cortex-Xpanse/Scanning-activity','login/index','2025-10-03 03:13:47'),
(624,'track','nt7da28krm5avf77cdp45tukshs4rd5h','','','185.244.104.2','185.244.104.2','/','http://173.208.52.222:443/','-','login/index','2025-10-03 03:21:48'),
(625,'track','dmlsb5lt76gah02mufs49ohgog5snddu','','','196.251.118.109','196.251.118.109','/.env','','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/81.0.4044.129 Safari/537.36','/index','2025-10-03 03:27:24'),
(626,'track','3hf35mrc7e47j8plj1kjhcru412u7ith','','','79.124.58.198','79.124.58.198','/actuator/gateway/routes','','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/78.0.3904.108 Safari/537.36','/index','2025-10-03 04:06:34'),
(627,'track','jmauguicihr36dtb2u7bhr9giq5m4uj0','','','79.124.58.198','79.124.58.198','/login','https://173.208.52.222:443/actuator/gateway/routes','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/78.0.3904.108 Safari/537.36','login/index','2025-10-03 04:06:34'),
(628,'track','7c995ip1npmgtdmh9hhlbfq4kote5v8k','','','185.244.104.2','185.244.104.2','/','http://173.208.52.222:443/','-','login/index','2025-10-03 04:11:17'),
(629,'track','lrjrafjbsspnree2aoif0pusaheq9h68','','','185.244.104.2','185.244.104.2','/','http://173.208.52.222:443/','-','login/index','2025-10-03 04:37:00'),
(630,'track','3l5ks1jdq9ih79a8769876mtb6jjm7ma','','','45.142.154.31','45.142.154.31','/','','','login/index','2025-10-03 05:18:12'),
(631,'track','uu3ifkkoj0g7aj34g5t02om64d9rh1eq','','','45.142.154.31','45.142.154.31','/','','Mozilla/5.0 (Windows NT 6.1) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/49.0.2623.112 Safari/537.36','login/index','2025-10-03 05:18:37'),
(632,'track','0rvdmmp6a4erg9p4nloevpmbn2l24o87','','','35.198.224.53','35.198.224.53','/','http://app.24ithub.com/','Mozilla/5.0 (Linux; Android 5.1.1; SM-J111F)','login/index','2025-10-03 05:27:44'),
(633,'track','4egvbkv3c53sk435o6gcav9ol7um0hil','','','35.198.224.53','35.198.224.53','/wordpress/','http://app.24ithub.com/wordpress/','Mozilla/5.0 (Linux; Android 5.1.1; SM-J111F)','/index','2025-10-03 05:27:45'),
(634,'track','pmmjb91gfumqckv7ntmbean340hs1muq','','','35.198.224.53','35.198.224.53','/login','https://app.24ithub.com/wordpress/','Mozilla/5.0 (Linux; Android 5.1.1; SM-J111F)','login/index','2025-10-03 05:27:45'),
(635,'track','7of0l61ebmm8sqfrao89n6gv10mfdkeh','','','185.244.104.2','185.244.104.2','/','http://173.208.52.222:443/','-','login/index','2025-10-03 05:41:19'),
(636,'track','rmadh0e4bad9u4crnt125h8r4dvlq3gu','','','45.156.128.37','45.156.128.37','/','','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/60.0.3112.113 Safari/537.36','login/index','2025-10-03 05:51:29'),
(637,'track','huq29l82q7odes91cui2ajv1rfcks6ev','','','185.244.104.2','185.244.104.2','/','http://173.208.52.222:443/','-','login/index','2025-10-03 06:06:48'),
(638,'track','3kn1uslvedpippa18ef5cppuphe9lvr6','','','191.242.209.98','191.242.209.98','/admin/config.php','','xfa1','/index','2025-10-03 06:07:46'),
(639,'track','h5vk32jkfbetli6cp8jkt0utpvgu6fti','','','62.60.131.18','62.60.131.18','/','','Mozilla/5.0 (Windows NT 10.0; rv:102.0) Gecko/20100101 Firefox/102.0','login/index','2025-10-03 06:16:16'),
(640,'track','v0iv2f57s4oc2lqjlr1lfro4juoldc3s','','','184.75.213.90','184.75.213.90','/admin/config.php','','xfa1','/index','2025-10-03 06:25:51'),
(641,'track','57viqq2id4tqck21fq27ftovf6i4u27c','','','172.237.126.37','172.237.126.37','/','','Mozilla/5.0 (Windows NT 6.1; WOW64; rv:8.0) Gecko/20100101 Firefox/8.0','login/index','2025-10-03 06:28:47'),
(642,'track','d0ea8h9ccd18k9hn6ctejtimcpqdgep8','','','185.244.104.2','185.244.104.2','/','http://173.208.52.222:443/','-','login/index','2025-10-03 07:05:28'),
(643,'track','qsh52i3ddcnu35o0snv4d8rhdn9g83g4','','','34.220.193.61','34.220.193.61','/','http://app.24ithub.com','Mozilla/5.0 (Linux; Android 8.0.0; SM-G965U Build/R16NW) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/63.0.3239.111 Mobile Safari/537.36','login/index','2025-10-03 07:10:16'),
(644,'track','1ifgq8ff9e5pqsch7715ee0pl3lsqppi','','','34.220.193.61','34.220.193.61','/','http://app.24ithub.com','Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/113.0.0.0 Safari/537.36','login/index','2025-10-03 07:10:16'),
(645,'track','ogirtn2bln3hap3a9483jrc97087o0ln','','','113.206.179.36','113.206.179.36','/','','','login/index','2025-10-03 07:22:48'),
(646,'track','85cksvb8ojd6lq1nr9kq3f06acq0i26c','','','195.178.110.160','195.178.110.160','/','','l9tcpid/v1.1.0','login/index','2025-10-03 07:32:50'),
(647,'track','1fco9b4apf32klnr12mjjofmatla3a2l','','','34.73.147.108','34.73.147.108','/','','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/88.0.4240.193 Safari/537.36','login/index','2025-10-03 07:33:29'),
(648,'track','1fco9b4apf32klnr12mjjofmatla3a2l','','','34.73.147.108','34.73.147.108','//wp-includes/wlwmanifest.xml','','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/88.0.4240.193 Safari/537.36','/index','2025-10-03 07:33:29'),
(649,'track','1fco9b4apf32klnr12mjjofmatla3a2l','','','34.73.147.108','34.73.147.108','//xmlrpc.php?rsd','','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/88.0.4240.193 Safari/537.36','/index','2025-10-03 07:33:29'),
(650,'track','1fco9b4apf32klnr12mjjofmatla3a2l','','','34.73.147.108','34.73.147.108','/','','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/88.0.4240.193 Safari/537.36','login/index','2025-10-03 07:33:29'),
(651,'track','1fco9b4apf32klnr12mjjofmatla3a2l','','','34.73.147.108','34.73.147.108','//blog/wp-includes/wlwmanifest.xml','','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/88.0.4240.193 Safari/537.36','/index','2025-10-03 07:33:29'),
(652,'track','1fco9b4apf32klnr12mjjofmatla3a2l','','','34.73.147.108','34.73.147.108','//web/wp-includes/wlwmanifest.xml','','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/88.0.4240.193 Safari/537.36','/index','2025-10-03 07:33:29'),
(653,'track','1fco9b4apf32klnr12mjjofmatla3a2l','','','34.73.147.108','34.73.147.108','//wordpress/wp-includes/wlwmanifest.xml','','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/88.0.4240.193 Safari/537.36','/index','2025-10-03 07:33:29'),
(654,'track','1fco9b4apf32klnr12mjjofmatla3a2l','','','34.73.147.108','34.73.147.108','//website/wp-includes/wlwmanifest.xml','','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/88.0.4240.193 Safari/537.36','/index','2025-10-03 07:33:30'),
(655,'track','1fco9b4apf32klnr12mjjofmatla3a2l','','','34.73.147.108','34.73.147.108','//wp/wp-includes/wlwmanifest.xml','','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/88.0.4240.193 Safari/537.36','/index','2025-10-03 07:33:30'),
(656,'track','1fco9b4apf32klnr12mjjofmatla3a2l','','','34.73.147.108','34.73.147.108','//news/wp-includes/wlwmanifest.xml','','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/88.0.4240.193 Safari/537.36','/index','2025-10-03 07:33:30'),
(657,'track','1fco9b4apf32klnr12mjjofmatla3a2l','','','34.73.147.108','34.73.147.108','//2020/wp-includes/wlwmanifest.xml','','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/88.0.4240.193 Safari/537.36','/index','2025-10-03 07:33:30'),
(658,'track','1fco9b4apf32klnr12mjjofmatla3a2l','','','34.73.147.108','34.73.147.108','//2019/wp-includes/wlwmanifest.xml','','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/88.0.4240.193 Safari/537.36','/index','2025-10-03 07:33:30'),
(659,'track','1fco9b4apf32klnr12mjjofmatla3a2l','','','34.73.147.108','34.73.147.108','//shop/wp-includes/wlwmanifest.xml','','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/88.0.4240.193 Safari/537.36','/index','2025-10-03 07:33:30'),
(660,'track','1fco9b4apf32klnr12mjjofmatla3a2l','','','34.73.147.108','34.73.147.108','//wp1/wp-includes/wlwmanifest.xml','','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/88.0.4240.193 Safari/537.36','/index','2025-10-03 07:33:30'),
(661,'track','1fco9b4apf32klnr12mjjofmatla3a2l','','','34.73.147.108','34.73.147.108','//test/wp-includes/wlwmanifest.xml','','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/88.0.4240.193 Safari/537.36','test/wp_includes','2025-10-03 07:33:32'),
(662,'track','1fco9b4apf32klnr12mjjofmatla3a2l','','','34.73.147.108','34.73.147.108','//wp2/wp-includes/wlwmanifest.xml','','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/88.0.4240.193 Safari/537.36','/index','2025-10-03 07:33:32'),
(663,'track','1fco9b4apf32klnr12mjjofmatla3a2l','','','34.73.147.108','34.73.147.108','//site/wp-includes/wlwmanifest.xml','','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/88.0.4240.193 Safari/537.36','/index','2025-10-03 07:33:34'),
(664,'track','1fco9b4apf32klnr12mjjofmatla3a2l','','','34.73.147.108','34.73.147.108','//cms/wp-includes/wlwmanifest.xml','','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/88.0.4240.193 Safari/537.36','/index','2025-10-03 07:33:34'),
(665,'track','1fco9b4apf32klnr12mjjofmatla3a2l','','','34.73.147.108','34.73.147.108','//sito/wp-includes/wlwmanifest.xml','','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/88.0.4240.193 Safari/537.36','/index','2025-10-03 07:33:35'),
(666,'track','ab64ltjmqqfp84i8l6261shtma8vt39n','','','45.144.212.235','45.144.212.235','/.git/config','','NokiaN70-1/5.0609.2.0.1 Series60/2.8 Profile/MIDP-2.0 Configuration/CLDC-1.1 UP.Link/6.3.1.13.0','/index','2025-10-03 07:42:33'),
(667,'track','cm45e4628oee43h7hlnke78tlfr8bpfg','','','3.82.247.226','3.82.247.226','/','http://app.24ithub.com/','Mozilla/5.0 (Macintosh; Intel Mac OS X 10_9_3) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/45.0.2454.85 Safari/537.36','login/index','2025-10-03 07:47:30'),
(668,'track','dtms1t9e1pnmfjc4j641jjid4eqfe7sc','','','152.32.235.85','152.32.235.85','/','','','login/index','2025-10-03 07:48:30'),
(669,'track','i2n764084j1r3247ntfgnm2ph72vfodg','','','152.32.235.85','152.32.235.85','/','','Mozilla/5.0 (Windows NT 6.1) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/49.0.2623.112 Safari/537.36','login/index','2025-10-03 07:48:39'),
(670,'track','1l5mo32hauootedinq5rj9904kkjp6dt','','','213.219.38.113','213.219.38.113','/','','','login/index','2025-10-03 07:58:07'),
(671,'track','eo0gciugfmg99q2ve0klcjukk31riacm','','','213.219.38.113','213.219.38.113','/','','Mozilla/5.0 (iPhone; CPU iPhone OS 15_5 like Mac OS X) AppleWebKit/605.1.15 (KHTML','login/index','2025-10-03 07:58:15'),
(672,'track','hh95v8tg45h84sr7933b9qei18a98cd2','','','213.219.38.113','213.219.38.113','/','','Mozilla/5.0 (iPhone; CPU iPhone OS 15_5 like Mac OS X) AppleWebKit/605.1.15 (KHTML','login/index','2025-10-03 07:58:15'),
(673,'track','bd4cssfoi9c8dh1tu0h3k584poncpb9v','','','213.219.38.113','213.219.38.113','/webui','','Mozilla/5.0 (iPhone; CPU iPhone OS 15_5 like Mac OS X) AppleWebKit/605.1.15 (KHTML','/index','2025-10-03 07:58:15'),
(674,'track','84cdubqj7t1d4nr1mosm74ri2l57dl3t','','','213.219.38.113','213.219.38.113','/login','','Mozilla/5.0 (iPhone; CPU iPhone OS 15_5 like Mac OS X) AppleWebKit/605.1.15 (KHTML','login/index','2025-10-03 07:58:15'),
(675,'track','n4enpkdv3v11i1ggc38lruhh2aame7k0','','','213.219.38.113','213.219.38.113','/','','Mozilla/5.0 (iPhone; CPU iPhone OS 15_5 like Mac OS X) AppleWebKit/605.1.15 (KHTML','login/index','2025-10-03 07:58:15'),
(676,'track','ssgec17qbuj227kgr58lpg8ddvltqc3j','','','213.219.38.113','213.219.38.113','/owa/','','Mozilla/5.0 (iPhone; CPU iPhone OS 15_5 like Mac OS X) AppleWebKit/605.1.15 (KHTML','/index','2025-10-03 07:58:16'),
(677,'track','gq2e913cd1r04o01u158ot8640mev4f6','','','213.219.38.113','213.219.38.113','/login','','Mozilla/5.0 (iPhone; CPU iPhone OS 15_5 like Mac OS X) AppleWebKit/605.1.15 (KHTML','login/index','2025-10-03 07:58:16'),
(678,'track','m1u97tmob65msepfn3hvhjkra5v26ibh','','','213.219.38.113','213.219.38.113','/owa/','','Mozilla/5.0 (iPhone; CPU iPhone OS 15_5 like Mac OS X) AppleWebKit/605.1.15 (KHTML','/index','2025-10-03 07:58:16'),
(679,'track','0bmtarm2ujqa7ef7sa5lqstul4s1nlgk','','','213.219.38.113','213.219.38.113','/login','','Mozilla/5.0 (iPhone; CPU iPhone OS 15_5 like Mac OS X) AppleWebKit/605.1.15 (KHTML','login/index','2025-10-03 07:58:16'),
(680,'track','cb6k1vpckip9un92o0fge8ifuc204l11','','','213.219.38.113','213.219.38.113','/','','','login/index','2025-10-03 07:58:35'),
(681,'track','3m1c0430ue1rrqcghq92n480dsrms0ul','','','213.219.38.113','213.219.38.113','/','','','login/index','2025-10-03 07:58:35'),
(682,'track','eglm7u37alarirspgeek6b2j52t5etsm','','','185.244.104.2','185.244.104.2','/','http://173.208.52.222:443/','-','login/index','2025-10-03 08:18:09'),
(683,'track','67ftrmuf1apa4b57ho4cb4212khh4468','','','122.169.33.222','122.169.33.222','/','','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','login/index','2025-10-03 08:20:24'),
(684,'track','67ftrmuf1apa4b57ho4cb4212khh4468','','','122.169.33.222','122.169.33.222','/login','https://app.24ithub.com/','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','login/index','2025-10-03 08:20:26'),
(685,'track','67ftrmuf1apa4b57ho4cb4212khh4468','Openvoips Support','SYSTEM','122.169.33.222','122.169.33.222','/dashboard','https://app.24ithub.com/login','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','dashboard/index','2025-10-03 08:20:27'),
(686,'track','67ftrmuf1apa4b57ho4cb4212khh4468','Openvoips Support','SYSTEM','122.169.33.222','122.169.33.222','/dashboard/memory','https://app.24ithub.com/dashboard','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','dashboard/memory','2025-10-03 08:20:29'),
(687,'track','67ftrmuf1apa4b57ho4cb4212khh4468','Openvoips Support','SYSTEM','122.169.33.222','122.169.33.222','/dashboard/disk','https://app.24ithub.com/dashboard','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','dashboard/disk','2025-10-03 08:20:29'),
(688,'track','67ftrmuf1apa4b57ho4cb4212khh4468','Openvoips Support','SYSTEM','122.169.33.222','122.169.33.222','/dashboard/cpu','https://app.24ithub.com/dashboard','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','dashboard/cpu','2025-10-03 08:20:29'),
(689,'track','67ftrmuf1apa4b57ho4cb4212khh4468','Openvoips Support','SYSTEM','122.169.33.222','122.169.33.222','/crs','https://app.24ithub.com/dashboard','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','crs/index','2025-10-03 08:20:32'),
(690,'track','67ftrmuf1apa4b57ho4cb4212khh4468','Openvoips Support','SYSTEM','122.169.33.222','122.169.33.222','/crs/payment/index/T1Y1MDA_EQUALS_','https://app.24ithub.com/crs','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','payment/index','2025-10-03 08:20:34'),
(691,'track','67ftrmuf1apa4b57ho4cb4212khh4468','Openvoips Support','SYSTEM','122.169.33.222','122.169.33.222','/crs/payment/addpayment/T1Y1MDA_EQUALS_','https://app.24ithub.com/crs/payment/index/T1Y1MDA_EQUALS_','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','payment/addpayment','2025-10-03 08:20:37'),
(692,'track','31k5f0mrm1bqr16tjgdd19vrjuf5bb9j','','','185.244.104.2','185.244.104.2','/','http://173.208.52.222:443/','-','login/index','2025-10-03 08:20:46'),
(693,'track','67ftrmuf1apa4b57ho4cb4212khh4468','Openvoips Support','SYSTEM','122.169.33.222','122.169.33.222','/crs/payment/addpayment/T1Y1MDA_EQUALS_','https://app.24ithub.com/crs/payment/addpayment/T1Y1MDA_EQUALS_','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','payment/addpayment','2025-10-03 08:20:57'),
(694,'track','pdk6mnlrrfdg8713b5fdsqstmsdoje1o','','','173.208.52.222','173.208.52.222','/billing/api?ACCOUNTID=OV500&SERVICENUMBER=Cash&COLLECTIONOPTION=Cash&AMMOUNT=10&PAIDON=2025-10-03+08%3A20%3A37&NOTES=for+test+by+OV+Team&CREATEDBY=UA000345178&REQUEST=ADDBALANCE','','','billing/api','2025-10-03 08:20:57'),
(695,'track','67ftrmuf1apa4b57ho4cb4212khh4468','Openvoips Support','SYSTEM','122.169.33.222','122.169.33.222','/crs/payment/index/T1Y1MDA_EQUALS_','https://app.24ithub.com/crs/payment/addpayment/T1Y1MDA_EQUALS_','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','payment/index','2025-10-03 08:20:57'),
(696,'track','67ftrmuf1apa4b57ho4cb4212khh4468','Openvoips Support','SYSTEM','122.169.33.222','122.169.33.222','/crs','https://app.24ithub.com/crs/payment/index/T1Y1MDA_EQUALS_','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','crs/index','2025-10-03 08:21:10'),
(697,'track','67ftrmuf1apa4b57ho4cb4212khh4468','Openvoips Support','SYSTEM','122.169.33.222','122.169.33.222','/crs/editvoip/T1Y1MDA_EQUALS_','https://app.24ithub.com/crs','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','crs/editvoip','2025-10-03 08:21:14'),
(698,'track','67ftrmuf1apa4b57ho4cb4212khh4468','Openvoips Support','SYSTEM','122.169.33.222','122.169.33.222','/theme/default/css/tabs.css','https://app.24ithub.com/crs/editvoip/T1Y1MDA_EQUALS_','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','/index','2025-10-03 08:21:14'),
(699,'track','67ftrmuf1apa4b57ho4cb4212khh4468','Openvoips Support','SYSTEM','122.169.33.222','122.169.33.222','/dashboard','https://app.24ithub.com/crs/editvoip/T1Y1MDA_EQUALS_','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','dashboard/index','2025-10-03 08:21:14'),
(700,'track','67ftrmuf1apa4b57ho4cb4212khh4468','Openvoips Support','SYSTEM','122.169.33.222','122.169.33.222','/crs/ajax/ajax_get_tariff','https://app.24ithub.com/crs/editvoip/T1Y1MDA_EQUALS_','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','ajax/ajax_get_tariff','2025-10-03 08:21:14'),
(701,'track','67ftrmuf1apa4b57ho4cb4212khh4468','Openvoips Support','SYSTEM','122.169.33.222','122.169.33.222','/crs','https://app.24ithub.com/crs/editvoip/T1Y1MDA_EQUALS_','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','crs/index','2025-10-03 08:21:19'),
(702,'track','ivnbrp1ki0k58css5t49s26sp317s118','','','162.142.125.120','162.142.125.120','/','','Mozilla/5.0 (compatible; CensysInspect/1.1; +https://about.censys.io/)','login/index','2025-10-03 08:21:32'),
(703,'track','67ftrmuf1apa4b57ho4cb4212khh4468','Openvoips Support','SYSTEM','122.169.33.222','122.169.33.222','/routes','https://app.24ithub.com/crs','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','routes/index','2025-10-03 08:21:34'),
(704,'track','67ftrmuf1apa4b57ho4cb4212khh4468','Openvoips Support','SYSTEM','122.169.33.222','122.169.33.222','/dialplans','https://app.24ithub.com/routes','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','dialplans/index','2025-10-03 08:21:37'),
(705,'track','67ftrmuf1apa4b57ho4cb4212khh4468','Openvoips Support','SYSTEM','122.169.33.222','122.169.33.222','/dialplans/editD/MQ_EQUALS__EQUALS_','https://app.24ithub.com/dialplans','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','dialplans/editD','2025-10-03 08:21:41'),
(706,'track','8j2g2nhnog7kpoksq6d8393hepgctnfk','','','162.142.125.120','162.142.125.120','/login','','Mozilla/5.0 (compatible; CensysInspect/1.1; +https://about.censys.io/)','login/index','2025-10-03 08:21:47'),
(707,'track','67ftrmuf1apa4b57ho4cb4212khh4468','Openvoips Support','SYSTEM','122.169.33.222','122.169.33.222','/dialplans/index/','https://app.24ithub.com/dialplans/editD/MQ_EQUALS__EQUALS_','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','dialplans/index','2025-10-03 08:21:49'),
(708,'track','67ftrmuf1apa4b57ho4cb4212khh4468','Openvoips Support','SYSTEM','122.169.33.222','122.169.33.222','/dialplans','https://app.24ithub.com/dialplans/index/','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','dialplans/index','2025-10-03 08:21:51'),
(709,'track','67ftrmuf1apa4b57ho4cb4212khh4468','Openvoips Support','SYSTEM','122.169.33.222','122.169.33.222','/dialplans/index/','https://app.24ithub.com/dialplans/index/','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','dialplans/index','2025-10-03 08:21:51'),
(710,'track','67ftrmuf1apa4b57ho4cb4212khh4468','Openvoips Support','SYSTEM','122.169.33.222','122.169.33.222','/dialplans/addD','https://app.24ithub.com/dialplans/index/','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','dialplans/addD','2025-10-03 08:21:53'),
(711,'track','67ftrmuf1apa4b57ho4cb4212khh4468','Openvoips Support','SYSTEM','122.169.33.222','122.169.33.222','/dialplans/addD','https://app.24ithub.com/dialplans/addD','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','dialplans/addD','2025-10-03 08:21:59'),
(712,'track','67ftrmuf1apa4b57ho4cb4212khh4468','Openvoips Support','SYSTEM','122.169.33.222','122.169.33.222','/dialplans/index/','https://app.24ithub.com/dialplans/addD','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','dialplans/index','2025-10-03 08:22:00'),
(713,'track','67ftrmuf1apa4b57ho4cb4212khh4468','Openvoips Support','SYSTEM','122.169.33.222','122.169.33.222','/pbx/extensionplan','https://app.24ithub.com/dialplans/index/','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','pbx/extensionplan','2025-10-03 08:22:26'),
(714,'track','67ftrmuf1apa4b57ho4cb4212khh4468','Openvoips Support','SYSTEM','122.169.33.222','122.169.33.222','/pbx/addBP','https://app.24ithub.com/pbx/extensionplan','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','pbx/addBP','2025-10-03 08:22:28'),
(715,'track','67ftrmuf1apa4b57ho4cb4212khh4468','Openvoips Support','SYSTEM','122.169.33.222','122.169.33.222','/pbx/addBP','https://app.24ithub.com/pbx/addBP','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','pbx/addBP','2025-10-03 08:22:55'),
(716,'track','67ftrmuf1apa4b57ho4cb4212khh4468','Openvoips Support','SYSTEM','122.169.33.222','122.169.33.222','/pbx/extensionplan','https://app.24ithub.com/pbx/addBP','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','pbx/extensionplan','2025-10-03 08:22:55'),
(717,'track','67ftrmuf1apa4b57ho4cb4212khh4468','Openvoips Support','SYSTEM','122.169.33.222','122.169.33.222','/pbx/crs','https://app.24ithub.com/pbx/extensionplan','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','crs/index','2025-10-03 08:22:57'),
(718,'track','67ftrmuf1apa4b57ho4cb4212khh4468','Openvoips Support','SYSTEM','122.169.33.222','122.169.33.222','/pbx/crs/details/T1Y1MDA_EQUALS_','https://app.24ithub.com/pbx/crs','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','crs/details','2025-10-03 08:23:00'),
(719,'track','67ftrmuf1apa4b57ho4cb4212khh4468','Openvoips Support','SYSTEM','122.169.33.222','122.169.33.222','/pbx/crs','https://app.24ithub.com/pbx/crs/details/T1Y1MDA_EQUALS_','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','crs/index','2025-10-03 08:23:02'),
(720,'track','67ftrmuf1apa4b57ho4cb4212khh4468','Openvoips Support','SYSTEM','122.169.33.222','122.169.33.222','/pbx/crs/details/T1Y1MDA_EQUALS_','https://app.24ithub.com/pbx/crs','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','crs/details','2025-10-03 08:23:08'),
(721,'track','67ftrmuf1apa4b57ho4cb4212khh4468','Openvoips Support','SYSTEM','122.169.33.222','122.169.33.222','/pbx/crs/details/T1Y1MDA_EQUALS_','https://app.24ithub.com/pbx/crs/details/T1Y1MDA_EQUALS_','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','crs/details','2025-10-03 08:23:12'),
(722,'track','67ftrmuf1apa4b57ho4cb4212khh4468','Openvoips Support','SYSTEM','122.169.33.222','122.169.33.222','/pbx/crs/details/T1Y1MDA_EQUALS_/2','https://app.24ithub.com/pbx/crs/details/T1Y1MDA_EQUALS_','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','crs/details','2025-10-03 08:23:12'),
(723,'track','67ftrmuf1apa4b57ho4cb4212khh4468','Openvoips Support','SYSTEM','122.169.33.222','122.169.33.222','/pbx/crs','https://app.24ithub.com/pbx/crs/details/T1Y1MDA_EQUALS_/2','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','crs/index','2025-10-03 08:23:14'),
(724,'track','67ftrmuf1apa4b57ho4cb4212khh4468','Openvoips Support','SYSTEM','122.169.33.222','122.169.33.222','/pbx/crs/permissions/T1Y1MDA_EQUALS_','https://app.24ithub.com/pbx/crs','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','crs/permissions','2025-10-03 08:23:18'),
(725,'track','67ftrmuf1apa4b57ho4cb4212khh4468','Openvoips Support','SYSTEM','122.169.33.222','122.169.33.222','/pbx/crs/permissions/T1Y1MDA_EQUALS_','https://app.24ithub.com/pbx/crs/permissions/T1Y1MDA_EQUALS_','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','crs/permissions','2025-10-03 08:23:31'),
(726,'track','67ftrmuf1apa4b57ho4cb4212khh4468','Openvoips Support','SYSTEM','122.169.33.222','122.169.33.222','/pbx/crs','https://app.24ithub.com/pbx/crs/permissions/T1Y1MDA_EQUALS_','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','crs/index','2025-10-03 08:23:31'),
(727,'track','67ftrmuf1apa4b57ho4cb4212khh4468','Openvoips Support','SYSTEM','122.169.33.222','122.169.33.222','/pbx/cli/index/T1Y1MDA_EQUALS_','https://app.24ithub.com/pbx/crs','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','cli/index','2025-10-03 08:23:36'),
(728,'track','67ftrmuf1apa4b57ho4cb4212khh4468','Openvoips Support','SYSTEM','122.169.33.222','122.169.33.222','/pbx/cli/addcli/T1Y1MDA_EQUALS_','https://app.24ithub.com/pbx/cli/index/T1Y1MDA_EQUALS_','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','cli/addcli','2025-10-03 08:23:38'),
(729,'track','67ftrmuf1apa4b57ho4cb4212khh4468','Openvoips Support','SYSTEM','122.169.33.222','122.169.33.222','/pbx/cli/addcli/T1Y1MDA_EQUALS_','https://app.24ithub.com/pbx/cli/addcli/T1Y1MDA_EQUALS_','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','cli/addcli','2025-10-03 08:23:56'),
(730,'track','67ftrmuf1apa4b57ho4cb4212khh4468','Openvoips Support','SYSTEM','122.169.33.222','122.169.33.222','/pbx/cli/index/T1Y1MDA_EQUALS_','https://app.24ithub.com/pbx/cli/addcli/T1Y1MDA_EQUALS_','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','cli/index','2025-10-03 08:23:56'),
(731,'track','67ftrmuf1apa4b57ho4cb4212khh4468','Openvoips Support','SYSTEM','122.169.33.222','122.169.33.222','/pbx/crs','https://app.24ithub.com/pbx/cli/index/T1Y1MDA_EQUALS_','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','crs/index','2025-10-03 08:23:58'),
(732,'track','67ftrmuf1apa4b57ho4cb4212khh4468','Openvoips Support','SYSTEM','122.169.33.222','122.169.33.222','/cuautologin/T1Y1MDA_EQUALS_','https://app.24ithub.com/pbx/crs','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','users/cuautologin','2025-10-03 08:24:01'),
(733,'track','67ftrmuf1apa4b57ho4cb4212khh4468','Openvoips Technologies','OV500','122.169.33.222','122.169.33.222','/dashboard','https://app.24ithub.com/cuautologin/T1Y1MDA_EQUALS_','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','dashboard/index','2025-10-03 08:24:01'),
(734,'track','67ftrmuf1apa4b57ho4cb4212khh4468','Openvoips Technologies','OV500','122.169.33.222','122.169.33.222','/dashboard/ajax_get_calls?month=Y&today=Y&active=Y','https://app.24ithub.com/dashboard','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','dashboard/ajax_get_calls','2025-10-03 08:24:02'),
(735,'track','67ftrmuf1apa4b57ho4cb4212khh4468','Openvoips Technologies','OV500','122.169.33.222','122.169.33.222','/pbx/extensions','https://app.24ithub.com/dashboard','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','extensions/index','2025-10-03 08:24:08'),
(736,'track','67ftrmuf1apa4b57ho4cb4212khh4468','Openvoips Technologies','OV500','122.169.33.222','122.169.33.222','/pbx/extensions/add','https://app.24ithub.com/pbx/extensions','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','extensions/add','2025-10-03 08:24:10'),
(737,'track','67ftrmuf1apa4b57ho4cb4212khh4468','Openvoips Technologies','OV500','122.169.33.222','122.169.33.222','/pbx/extensions/add','https://app.24ithub.com/pbx/extensions','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','extensions/add','2025-10-03 08:24:17'),
(738,'track','67ftrmuf1apa4b57ho4cb4212khh4468','Openvoips Technologies','OV500','122.169.33.222','122.169.33.222','/pbx/extensions/add','https://app.24ithub.com/pbx/extensions','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','extensions/add','2025-10-03 08:25:16'),
(739,'track','2tj2ules9ope09dcft6q0fvnisf4q90t','Openvoips Technologies','OV500','122.169.33.222','122.169.33.222','/pbx/extensions','https://app.24ithub.com/pbx/extensions/add','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','extensions/index','2025-10-03 08:25:34'),
(740,'track','2tj2ules9ope09dcft6q0fvnisf4q90t','Openvoips Technologies','OV500','122.169.33.222','122.169.33.222','/pbx/extensions/add','https://app.24ithub.com/pbx/extensions','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','extensions/add','2025-10-03 08:25:36'),
(741,'track','2tj2ules9ope09dcft6q0fvnisf4q90t','Openvoips Technologies','OV500','122.169.33.222','122.169.33.222','/pbx/extensions/add','https://app.24ithub.com/pbx/extensions/add','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','extensions/add','2025-10-03 08:26:10'),
(742,'track','tqanj9fkpuho30d31ltsi26iio4h2kug','','','173.208.52.222','173.208.52.222','/billing/api?REQUEST=EXTENSION&account_id=OV500&service_number=EOVHEY7900&extension_no=1001&service_id=DFE31P&account_type=CUSTOMER','','','billing/api','2025-10-03 08:26:12'),
(743,'track','2tj2ules9ope09dcft6q0fvnisf4q90t','Openvoips Technologies','OV500','122.169.33.222','122.169.33.222','/pbx/extensions','https://app.24ithub.com/pbx/extensions/add','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','extensions/index','2025-10-03 08:26:12'),
(744,'track','dp8unanupdg8muuoc00diddulgt72uo4','','','139.59.84.68','139.59.84.68','/.env','','Mozilla/5.0; Keydrop.io/1.0(onlyscans.com/about);','/index','2025-10-03 08:51:38'),
(745,'track','k8gni7pid19r4l4b8ofom5hn1et3j6nt','','','139.59.84.68','139.59.84.68','/.git/config','','Mozilla/5.0; Keydrop.io/1.0(onlyscans.com/about);','/index','2025-10-03 08:51:40'),
(746,'track','mgstll998vnmhcq1vduapbqs3gojd7n7','','','54.236.230.158','54.236.230.158','/','','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36','login/index','2025-10-03 08:56:04'),
(747,'track','9fc7pfgtf0ei9g57bem924mn8f611v02','','','185.244.104.2','185.244.104.2','/','http://173.208.52.222:443/','-','login/index','2025-10-03 09:05:27'),
(748,'track','l5ieeb7hd4150psf892f2dsp5vgjpqlm','','','23.27.145.100','23.27.145.100','/','','Mozilla/5.0 (X11; Linux i686; rv:109.0) Gecko/20100101 Firefox/120.0','login/index','2025-10-03 09:23:21'),
(749,'track','d69pqq0m820auf66afpi5dtam756ha2n','','','185.244.104.2','185.244.104.2','/','http://173.208.52.222:443/','-','login/index','2025-10-03 09:54:04'),
(750,'track','acam8m3cm22h8efeiq54ij3pi1d2uo2v','','','185.244.104.2','185.244.104.2','/','http://173.208.52.222:443/','-','login/index','2025-10-03 10:30:09'),
(751,'track','aaoq8176d6eoar6tkmn14gjp2dh17fok','','','213.209.157.244','213.209.157.244','/.git/config','','Mozilla/5.0 (iPhone; CPU iPhone OS 12_1 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) CriOS/75.0.3770.103 Mobile/15E148 Safari/605.1','/index','2025-10-03 11:03:34'),
(752,'track','v905gihn09fqogc5p85fc6se8h11th02','','','195.178.110.201','195.178.110.201','/.git/index','','Mozilla/5.0 (Macintosh; Intel Mac OS X 10_10_3) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/41.0.2272.105 Safari/537.36 Vivaldi/1.0.162.9','/index','2025-10-03 11:07:27'),
(753,'track','q1h35m1m971d6pcq7jcv1dk59nlfu9er','','','185.244.104.2','185.244.104.2','/','http://173.208.52.222:443/','-','login/index','2025-10-03 11:29:04'),
(754,'track','2002rionsslc8c0bicig7uldflbovtmb','','','35.199.148.168','35.199.148.168','/','http://app.24ithub.com','Mozilla/5.0 (compatible; CMS-Checker/1.0; +https://example.com)','login/index','2025-10-03 11:31:10'),
(755,'track','taa4kgtk02l2q5bvsa3pp3j1umri90cm','','','209.38.123.43','209.38.123.43','/','','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/139.0.0.0 Safari/537.36','login/index','2025-10-03 11:50:26'),
(756,'track','uo8sc3vdfveekbetn9c3i98b07ebahe4','','','185.244.104.2','185.244.104.2','/','http://173.208.52.222:443/','-','login/index','2025-10-03 12:01:29'),
(757,'track','cn6p79rgqr4d59vq80d5m9sp28k2udva','','','216.180.246.11','216.180.246.11','/','','\'Mozilla/5.0 (compatible; GenomeCrawlerd/1.0; +https://www.nokia.com/genomecrawler)\'','login/index','2025-10-03 12:03:37'),
(758,'track','6pc4me9p8hjsjbjcfgcm9ffgqmmp8qa1','','','34.235.156.184','34.235.156.184','/.git/config','','Mozilla/5.0 (X11; Linux i686; rv:25.0) Gecko/20100101 Firefox/25.0','/index','2025-10-03 12:04:20'),
(759,'track','3pmiag3cip5lc89jf1vh1bd7p5l4divs','','','196.251.72.177','196.251.72.177','/.env','','Mozilla/5.0 (Linux; Android 7.0; LG-H850) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/76.0.3809.89 Mobile Safari/537.36','/index','2025-10-03 12:17:12'),
(760,'track','2hk3m9op1r87meh1qu4dt0om2jtp6hhq','','','196.251.72.177','196.251.72.177','/.env','','Mozilla/5.0 (Linux; U; Android 0.5; en-us) AppleWebKit/522  (KHTML, like Gecko) Safari/419.3','/index','2025-10-03 12:18:38'),
(761,'track','b59rgc6euftrit6q0jsvm6elgjcffciv','','','185.244.104.2','185.244.104.2','/','http://173.208.52.222:443/','-','login/index','2025-10-03 13:09:54'),
(762,'track','iprqt8bfal89i7271d3k7g5ke5pgniv4','','','15.235.189.154','15.235.189.154','/','','Mozilla/5.0 (X11; Ubuntu; Linux x86_64; rv:134.0) Gecko/20100101 Firefox/134.0','login/index','2025-10-03 13:16:26'),
(763,'track','35hoq7d2k2f1cj4bad4voqbskcejpais','','','195.178.110.109','195.178.110.109','/','','l9tcpid/v1.1.0','login/index','2025-10-03 13:26:17'),
(764,'track','3b1c86fbphppfmfv6opgi9uj9ebg9d7q','','','185.244.104.2','185.244.104.2','/','http://173.208.52.222:443/','-','login/index','2025-10-03 13:28:00'),
(765,'track','g9go8b9qotbjuf9clq6sdtsdvcuf1jhl','','','122.169.33.222','122.169.33.222','/','https://app.24ithub.com/pbx/queue','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','login/index','2025-10-03 13:59:20'),
(766,'track','g9go8b9qotbjuf9clq6sdtsdvcuf1jhl','','','122.169.33.222','122.169.33.222','/login','https://app.24ithub.com/','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','login/index','2025-10-03 13:59:22'),
(767,'track','g9go8b9qotbjuf9clq6sdtsdvcuf1jhl','Openvoips Support','SYSTEM','122.169.33.222','122.169.33.222','/dashboard','https://app.24ithub.com/login','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','dashboard/index','2025-10-03 13:59:22'),
(768,'track','g9go8b9qotbjuf9clq6sdtsdvcuf1jhl','Openvoips Support','SYSTEM','122.169.33.222','122.169.33.222','/dashboard/memory','https://app.24ithub.com/dashboard','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','dashboard/memory','2025-10-03 13:59:24'),
(769,'track','g9go8b9qotbjuf9clq6sdtsdvcuf1jhl','Openvoips Support','SYSTEM','122.169.33.222','122.169.33.222','/dashboard/cpu','https://app.24ithub.com/dashboard','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','dashboard/cpu','2025-10-03 13:59:24'),
(770,'track','g9go8b9qotbjuf9clq6sdtsdvcuf1jhl','Openvoips Support','SYSTEM','122.169.33.222','122.169.33.222','/dashboard/disk','https://app.24ithub.com/dashboard','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','dashboard/disk','2025-10-03 13:59:25'),
(771,'track','g9go8b9qotbjuf9clq6sdtsdvcuf1jhl','Openvoips Support','SYSTEM','122.169.33.222','122.169.33.222','/pbx/crs','https://app.24ithub.com/dashboard','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','crs/index','2025-10-03 13:59:28'),
(772,'track','g9go8b9qotbjuf9clq6sdtsdvcuf1jhl','Openvoips Support','SYSTEM','122.169.33.222','122.169.33.222','/cuautologin/T1Y1MDA_EQUALS_','https://app.24ithub.com/pbx/crs','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','users/cuautologin','2025-10-03 13:59:30'),
(773,'track','g9go8b9qotbjuf9clq6sdtsdvcuf1jhl','Openvoips Technologies','OV500','122.169.33.222','122.169.33.222','/dashboard','https://app.24ithub.com/cuautologin/T1Y1MDA_EQUALS_','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','dashboard/index','2025-10-03 13:59:31'),
(774,'track','g9go8b9qotbjuf9clq6sdtsdvcuf1jhl','Openvoips Technologies','OV500','122.169.33.222','122.169.33.222','/dashboard/ajax_get_calls?month=Y&today=Y&active=Y','https://app.24ithub.com/dashboard','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','dashboard/ajax_get_calls','2025-10-03 13:59:31'),
(775,'track','g9go8b9qotbjuf9clq6sdtsdvcuf1jhl','Openvoips Technologies','OV500','122.169.33.222','122.169.33.222','/reports/Calls','https://app.24ithub.com/dashboard','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','reports/Calls','2025-10-03 13:59:40'),
(776,'track','g9go8b9qotbjuf9clq6sdtsdvcuf1jhl','Openvoips Technologies','OV500','122.169.33.222','122.169.33.222','/pbx/reports/livecalls','https://app.24ithub.com/reports/Calls','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','reports/livecalls','2025-10-03 13:59:43'),
(777,'track','g9go8b9qotbjuf9clq6sdtsdvcuf1jhl','Openvoips Technologies','OV500','122.169.33.222','122.169.33.222','/pbx/reports/api_livecall?action=search&call_flow=&max_rows=500&_=1759499983194','https://app.24ithub.com/pbx/reports/livecalls','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','reports/api_livecall','2025-10-03 13:59:43'),
(778,'track','g9go8b9qotbjuf9clq6sdtsdvcuf1jhl','Openvoips Technologies','OV500','122.169.33.222','122.169.33.222','/pbx/reports/api_livecall?action=search&call_flow=&max_rows=500&_=1759499983195','https://app.24ithub.com/pbx/reports/livecalls','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','reports/api_livecall','2025-10-03 13:59:49'),
(779,'track','g9go8b9qotbjuf9clq6sdtsdvcuf1jhl','Openvoips Technologies','OV500','122.169.33.222','122.169.33.222','/pbx/reports/api_livecall?action=search&call_flow=&max_rows=500&_=1759499983196','https://app.24ithub.com/pbx/reports/livecalls','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','reports/api_livecall','2025-10-03 13:59:54'),
(780,'track','g9go8b9qotbjuf9clq6sdtsdvcuf1jhl','Openvoips Technologies','OV500','122.169.33.222','122.169.33.222','/pbx/reports/api_livecall?action=search&call_flow=&max_rows=500&_=1759499983197','https://app.24ithub.com/pbx/reports/livecalls','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','reports/api_livecall','2025-10-03 13:59:59'),
(781,'track','g9go8b9qotbjuf9clq6sdtsdvcuf1jhl','Openvoips Technologies','OV500','122.169.33.222','122.169.33.222','/pbx/operatorpanal','https://app.24ithub.com/pbx/reports/livecalls','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','operatorpanal/index','2025-10-03 14:00:03'),
(782,'track','g9go8b9qotbjuf9clq6sdtsdvcuf1jhl','Openvoips Technologies','OV500','122.169.33.222','122.169.33.222','/pbx/operatorpanal/ajax_set_hangup/c15088f2-d93f-4180-ad87-c67f00932392','https://app.24ithub.com/pbx/operatorpanal','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','operatorpanal/ajax_set_hangup','2025-10-03 14:00:05'),
(783,'track','g9go8b9qotbjuf9clq6sdtsdvcuf1jhl','Openvoips Technologies','OV500','122.169.33.222','122.169.33.222','/pbx/operatorpanal/ajax_set_hangup/c15088f2-d93f-4180-ad87-c67f00932392','https://app.24ithub.com/pbx/operatorpanal','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','operatorpanal/ajax_set_hangup','2025-10-03 14:00:06'),
(784,'track','g9go8b9qotbjuf9clq6sdtsdvcuf1jhl','Openvoips Technologies','OV500','122.169.33.222','122.169.33.222','/pbx/operatorpanal/ajax_set_hangup/c15088f2-d93f-4180-ad87-c67f00932392','https://app.24ithub.com/pbx/operatorpanal','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','operatorpanal/ajax_set_hangup','2025-10-03 14:00:07'),
(785,'track','g9go8b9qotbjuf9clq6sdtsdvcuf1jhl','Openvoips Technologies','OV500','122.169.33.222','122.169.33.222','/pbx/operatorpanal/ajax_set_hangup/c15088f2-d93f-4180-ad87-c67f00932392','https://app.24ithub.com/pbx/operatorpanal','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','operatorpanal/ajax_set_hangup','2025-10-03 14:00:07'),
(786,'track','g9go8b9qotbjuf9clq6sdtsdvcuf1jhl','Openvoips Technologies','OV500','122.169.33.222','122.169.33.222','/pbx/operatorpanal/ajax_get_call_data','https://app.24ithub.com/pbx/operatorpanal','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','operatorpanal/ajax_get_call_data','2025-10-03 14:00:08'),
(787,'track','g9go8b9qotbjuf9clq6sdtsdvcuf1jhl','Openvoips Technologies','OV500','122.169.33.222','122.169.33.222','/pbx/operatorpanal/ajax_get_call_data','https://app.24ithub.com/pbx/operatorpanal','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','operatorpanal/ajax_get_call_data','2025-10-03 14:00:13'),
(788,'track','g9go8b9qotbjuf9clq6sdtsdvcuf1jhl','Openvoips Technologies','OV500','122.169.33.222','122.169.33.222','/pbx/operatorpanal/ajax_get_call_data','https://app.24ithub.com/pbx/operatorpanal','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','operatorpanal/ajax_get_call_data','2025-10-03 14:00:19'),
(789,'track','g9go8b9qotbjuf9clq6sdtsdvcuf1jhl','Openvoips Technologies','OV500','122.169.33.222','122.169.33.222','/pbx/operatorpanal/ajax_get_call_data','https://app.24ithub.com/pbx/operatorpanal','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','operatorpanal/ajax_get_call_data','2025-10-03 14:00:24'),
(790,'track','g9go8b9qotbjuf9clq6sdtsdvcuf1jhl','Openvoips Technologies','OV500','122.169.33.222','122.169.33.222','/pbx/operatorpanal/ajax_get_call_data','https://app.24ithub.com/pbx/operatorpanal','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','operatorpanal/ajax_get_call_data','2025-10-03 14:00:29'),
(791,'track','g9go8b9qotbjuf9clq6sdtsdvcuf1jhl','Openvoips Technologies','OV500','122.169.33.222','122.169.33.222','/pbx/operatorpanal/ajax_get_call_data','https://app.24ithub.com/pbx/operatorpanal','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','operatorpanal/ajax_get_call_data','2025-10-03 14:00:34'),
(792,'track','g9go8b9qotbjuf9clq6sdtsdvcuf1jhl','Openvoips Technologies','OV500','122.169.33.222','122.169.33.222','/pbx/operatorpanal/ajax_get_call_data','https://app.24ithub.com/pbx/operatorpanal','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','operatorpanal/ajax_get_call_data','2025-10-03 14:00:39'),
(793,'track','g9go8b9qotbjuf9clq6sdtsdvcuf1jhl','Openvoips Technologies','OV500','122.169.33.222','122.169.33.222','/pbx/operatorpanal/ajax_get_call_data','https://app.24ithub.com/pbx/operatorpanal','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','operatorpanal/ajax_get_call_data','2025-10-03 14:00:44'),
(794,'track','g9go8b9qotbjuf9clq6sdtsdvcuf1jhl','Openvoips Technologies','OV500','122.169.33.222','122.169.33.222','/pbx/operatorpanal/ajax_get_call_data','https://app.24ithub.com/pbx/operatorpanal','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','operatorpanal/ajax_get_call_data','2025-10-03 14:00:49'),
(795,'track','g9go8b9qotbjuf9clq6sdtsdvcuf1jhl','Openvoips Technologies','OV500','122.169.33.222','122.169.33.222','/pbx/operatorpanal/ajax_get_call_data','https://app.24ithub.com/pbx/operatorpanal','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','operatorpanal/ajax_get_call_data','2025-10-03 14:00:54'),
(796,'track','g9go8b9qotbjuf9clq6sdtsdvcuf1jhl','Openvoips Technologies','OV500','122.169.33.222','122.169.33.222','/pbx/operatorpanal/ajax_get_call_data','https://app.24ithub.com/pbx/operatorpanal','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','operatorpanal/ajax_get_call_data','2025-10-03 14:01:00'),
(797,'track','g9go8b9qotbjuf9clq6sdtsdvcuf1jhl','Openvoips Technologies','OV500','122.169.33.222','122.169.33.222','/pbx/operatorpanal/ajax_get_call_data','https://app.24ithub.com/pbx/operatorpanal','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','operatorpanal/ajax_get_call_data','2025-10-03 14:01:04'),
(798,'track','','','','','','','',' CLI','Billing/callmanage','2025-10-03 14:01:07'),
(799,'track','g9go8b9qotbjuf9clq6sdtsdvcuf1jhl','Openvoips Technologies','OV500','122.169.33.222','122.169.33.222','/pbx/operatorpanal/ajax_get_call_data','https://app.24ithub.com/pbx/operatorpanal','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','operatorpanal/ajax_get_call_data','2025-10-03 14:01:10'),
(800,'track','','','','','','','',' CLI','Billing/callmanage','2025-10-03 14:01:14'),
(801,'track','g9go8b9qotbjuf9clq6sdtsdvcuf1jhl','Openvoips Technologies','OV500','122.169.33.222','122.169.33.222','/pbx/operatorpanal/ajax_get_call_data','https://app.24ithub.com/pbx/operatorpanal','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','operatorpanal/ajax_get_call_data','2025-10-03 14:01:14'),
(802,'track','g9go8b9qotbjuf9clq6sdtsdvcuf1jhl','Openvoips Technologies','OV500','122.169.33.222','122.169.33.222','/pbx/operatorpanal/ajax_get_call_data','https://app.24ithub.com/pbx/operatorpanal','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','operatorpanal/ajax_get_call_data','2025-10-03 14:01:19'),
(803,'track','g9go8b9qotbjuf9clq6sdtsdvcuf1jhl','Openvoips Technologies','OV500','122.169.33.222','122.169.33.222','/pbx/reports/livecalls','https://app.24ithub.com/pbx/operatorpanal','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','reports/livecalls','2025-10-03 14:01:22'),
(804,'track','g9go8b9qotbjuf9clq6sdtsdvcuf1jhl','Openvoips Technologies','OV500','122.169.33.222','122.169.33.222','/pbx/reports/api_livecall?action=search&call_flow=&max_rows=500&_=1759500082724','https://app.24ithub.com/pbx/reports/livecalls','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','reports/api_livecall','2025-10-03 14:01:23'),
(805,'track','g9go8b9qotbjuf9clq6sdtsdvcuf1jhl','Openvoips Technologies','OV500','122.169.33.222','122.169.33.222','/pbx/reports/api_livecall?action=search&call_flow=&max_rows=500&_=1759500082725','https://app.24ithub.com/pbx/reports/livecalls','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','reports/api_livecall','2025-10-03 14:01:26'),
(806,'track','g9go8b9qotbjuf9clq6sdtsdvcuf1jhl','Openvoips Technologies','OV500','122.169.33.222','122.169.33.222','/pbx/reports/api_livecall?action=search&call_flow=&max_rows=500&_=1759500082726','https://app.24ithub.com/pbx/reports/livecalls','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','reports/api_livecall','2025-10-03 14:01:28'),
(807,'track','g9go8b9qotbjuf9clq6sdtsdvcuf1jhl','Openvoips Technologies','OV500','122.169.33.222','122.169.33.222','/pbx/reports/api_livecall?action=search&call_flow=&max_rows=500&_=1759500082727','https://app.24ithub.com/pbx/reports/livecalls','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','reports/api_livecall','2025-10-03 14:01:34'),
(808,'track','g9go8b9qotbjuf9clq6sdtsdvcuf1jhl','Openvoips Technologies','OV500','122.169.33.222','122.169.33.222','/pbx/reports/api_livecall?action=search&call_flow=&max_rows=500&_=1759500082728','https://app.24ithub.com/pbx/reports/livecalls','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','reports/api_livecall','2025-10-03 14:01:39'),
(809,'track','g9go8b9qotbjuf9clq6sdtsdvcuf1jhl','Openvoips Technologies','OV500','122.169.33.222','122.169.33.222','/pbx/reports/api_livecall?action=search&call_flow=&max_rows=500&_=1759500082729','https://app.24ithub.com/pbx/reports/livecalls','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','reports/api_livecall','2025-10-03 14:01:44'),
(810,'track','g9go8b9qotbjuf9clq6sdtsdvcuf1jhl','Openvoips Technologies','OV500','122.169.33.222','122.169.33.222','/pbx/reports/api_livecall?action=search&call_flow=&max_rows=500&_=1759500082730','https://app.24ithub.com/pbx/reports/livecalls','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','reports/api_livecall','2025-10-03 14:01:49'),
(811,'track','g9go8b9qotbjuf9clq6sdtsdvcuf1jhl','Openvoips Technologies','OV500','122.169.33.222','122.169.33.222','/pbx/reports/api_livecall?action=search&call_flow=&max_rows=500&_=1759500082731','https://app.24ithub.com/pbx/reports/livecalls','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','reports/api_livecall','2025-10-03 14:01:55'),
(812,'track','g9go8b9qotbjuf9clq6sdtsdvcuf1jhl','Openvoips Technologies','OV500','122.169.33.222','122.169.33.222','/pbx/reports/api_livecall?action=search&call_flow=&max_rows=500&_=1759500082732','https://app.24ithub.com/pbx/reports/livecalls','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','reports/api_livecall','2025-10-03 14:01:59'),
(813,'track','g9go8b9qotbjuf9clq6sdtsdvcuf1jhl','Openvoips Technologies','OV500','122.169.33.222','122.169.33.222','/pbx/reports/api_livecall?action=search&call_flow=&max_rows=500&_=1759500082733','https://app.24ithub.com/pbx/reports/livecalls','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','reports/api_livecall','2025-10-03 14:02:05'),
(814,'track','g9go8b9qotbjuf9clq6sdtsdvcuf1jhl','Openvoips Technologies','OV500','122.169.33.222','122.169.33.222','/pbx/reports/api_livecall?action=search&call_flow=&max_rows=500&_=1759500082734','https://app.24ithub.com/pbx/reports/livecalls','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','reports/api_livecall','2025-10-03 14:02:10'),
(815,'track','g9go8b9qotbjuf9clq6sdtsdvcuf1jhl','Openvoips Technologies','OV500','122.169.33.222','122.169.33.222','/pbx/reports/api_livecall?action=search&call_flow=&max_rows=500&_=1759500082735','https://app.24ithub.com/pbx/reports/livecalls','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','reports/api_livecall','2025-10-03 14:02:15'),
(816,'track','g9go8b9qotbjuf9clq6sdtsdvcuf1jhl','Openvoips Technologies','OV500','122.169.33.222','122.169.33.222','/pbx/reports/api_livecall?action=search&call_flow=&max_rows=500&_=1759500082736','https://app.24ithub.com/pbx/reports/livecalls','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','reports/api_livecall','2025-10-03 14:02:20'),
(817,'track','g9go8b9qotbjuf9clq6sdtsdvcuf1jhl','Openvoips Technologies','OV500','122.169.33.222','122.169.33.222','/pbx/reports/api_livecall?action=search&call_flow=&max_rows=500&_=1759500082737','https://app.24ithub.com/pbx/reports/livecalls','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','reports/api_livecall','2025-10-03 14:02:25'),
(818,'track','g9go8b9qotbjuf9clq6sdtsdvcuf1jhl','Openvoips Technologies','OV500','122.169.33.222','122.169.33.222','/pbx/reports/api_livecall?action=search&call_flow=&max_rows=500&_=1759500082738','https://app.24ithub.com/pbx/reports/livecalls','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','reports/api_livecall','2025-10-03 14:02:30'),
(819,'track','g9go8b9qotbjuf9clq6sdtsdvcuf1jhl','Openvoips Technologies','OV500','122.169.33.222','122.169.33.222','/pbx/reports/api_livecall?action=search&call_flow=&max_rows=500&_=1759500082739','https://app.24ithub.com/pbx/reports/livecalls','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','reports/api_livecall','2025-10-03 14:02:36'),
(820,'track','g9go8b9qotbjuf9clq6sdtsdvcuf1jhl','Openvoips Technologies','OV500','122.169.33.222','122.169.33.222','/pbx/reports/api_livecall?action=search&call_flow=&max_rows=500&_=1759500082740','https://app.24ithub.com/pbx/reports/livecalls','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','reports/api_livecall','2025-10-03 14:02:40'),
(821,'track','g9go8b9qotbjuf9clq6sdtsdvcuf1jhl','Openvoips Technologies','OV500','122.169.33.222','122.169.33.222','/pbx/reports/api_livecall?action=search&call_flow=&max_rows=500&_=1759500082741','https://app.24ithub.com/pbx/reports/livecalls','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','reports/api_livecall','2025-10-03 14:02:46'),
(822,'track','g9go8b9qotbjuf9clq6sdtsdvcuf1jhl','Openvoips Technologies','OV500','122.169.33.222','122.169.33.222','/pbx/reports/api_livecall?action=search&call_flow=&max_rows=500&_=1759500082742','https://app.24ithub.com/pbx/reports/livecalls','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','reports/api_livecall','2025-10-03 14:02:51'),
(823,'track','g9go8b9qotbjuf9clq6sdtsdvcuf1jhl','Openvoips Technologies','OV500','122.169.33.222','122.169.33.222','/pbx/reports/api_livecall?action=search&call_flow=&max_rows=500&_=1759500082743','https://app.24ithub.com/pbx/reports/livecalls','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','reports/api_livecall','2025-10-03 14:02:56'),
(824,'track','g9go8b9qotbjuf9clq6sdtsdvcuf1jhl','Openvoips Technologies','OV500','122.169.33.222','122.169.33.222','/pbx/reports/api_livecall?action=search&call_flow=&max_rows=500&_=1759500082744','https://app.24ithub.com/pbx/reports/livecalls','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','reports/api_livecall','2025-10-03 14:03:01'),
(825,'track','g9go8b9qotbjuf9clq6sdtsdvcuf1jhl','Openvoips Technologies','OV500','122.169.33.222','122.169.33.222','/pbx/reports/api_livecall?action=search&call_flow=&max_rows=500&_=1759500082745','https://app.24ithub.com/pbx/reports/livecalls','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','reports/api_livecall','2025-10-03 14:03:06'),
(826,'track','g9go8b9qotbjuf9clq6sdtsdvcuf1jhl','Openvoips Technologies','OV500','122.169.33.222','122.169.33.222','/pbx/reports/api_livecall?action=search&call_flow=&max_rows=500&_=1759500082746','https://app.24ithub.com/pbx/reports/livecalls','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','reports/api_livecall','2025-10-03 14:03:11'),
(827,'track','g9go8b9qotbjuf9clq6sdtsdvcuf1jhl','Openvoips Technologies','OV500','122.169.33.222','122.169.33.222','/pbx/reports/api_livecall?action=search&call_flow=&max_rows=500&_=1759500082747','https://app.24ithub.com/pbx/reports/livecalls','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','reports/api_livecall','2025-10-03 14:03:16'),
(828,'track','g9go8b9qotbjuf9clq6sdtsdvcuf1jhl','Openvoips Technologies','OV500','122.169.33.222','122.169.33.222','/pbx/reports/api_livecall?action=search&call_flow=&max_rows=500&_=1759500082748','https://app.24ithub.com/pbx/reports/livecalls','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','reports/api_livecall','2025-10-03 14:03:21'),
(829,'track','g9go8b9qotbjuf9clq6sdtsdvcuf1jhl','Openvoips Technologies','OV500','122.169.33.222','122.169.33.222','/pbx/reports/api_livecall?action=search&call_flow=&max_rows=500&_=1759500082749','https://app.24ithub.com/pbx/reports/livecalls','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','reports/api_livecall','2025-10-03 14:03:27'),
(830,'track','g9go8b9qotbjuf9clq6sdtsdvcuf1jhl','Openvoips Technologies','OV500','122.169.33.222','122.169.33.222','/pbx/reports/api_livecall?action=search&call_flow=&max_rows=500&_=1759500082750','https://app.24ithub.com/pbx/reports/livecalls','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','reports/api_livecall','2025-10-03 14:03:32'),
(831,'track','g9go8b9qotbjuf9clq6sdtsdvcuf1jhl','Openvoips Technologies','OV500','122.169.33.222','122.169.33.222','/pbx/reports/api_livecall?action=search&call_flow=&max_rows=500&_=1759500082751','https://app.24ithub.com/pbx/reports/livecalls','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','reports/api_livecall','2025-10-03 14:03:37'),
(832,'track','g9go8b9qotbjuf9clq6sdtsdvcuf1jhl','Openvoips Technologies','OV500','122.169.33.222','122.169.33.222','/pbx/reports/api_livecall?action=search&call_flow=&max_rows=500&_=1759500082752','https://app.24ithub.com/pbx/reports/livecalls','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','reports/api_livecall','2025-10-03 14:03:42'),
(833,'track','g9go8b9qotbjuf9clq6sdtsdvcuf1jhl','Openvoips Technologies','OV500','122.169.33.222','122.169.33.222','/pbx/reports/api_livecall?action=search&call_flow=&max_rows=500&_=1759500082753','https://app.24ithub.com/pbx/reports/livecalls','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','reports/api_livecall','2025-10-03 14:03:48'),
(834,'track','g9go8b9qotbjuf9clq6sdtsdvcuf1jhl','Openvoips Technologies','OV500','122.169.33.222','122.169.33.222','/pbx/reports/api_livecall?action=search&call_flow=&max_rows=500&_=1759500082754','https://app.24ithub.com/pbx/reports/livecalls','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','reports/api_livecall','2025-10-03 14:03:52'),
(835,'track','g9go8b9qotbjuf9clq6sdtsdvcuf1jhl','Openvoips Technologies','OV500','122.169.33.222','122.169.33.222','/pbx/reports/api_livecall?action=search&call_flow=&max_rows=500&_=1759500082755','https://app.24ithub.com/pbx/reports/livecalls','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','reports/api_livecall','2025-10-03 14:03:58'),
(836,'track','g9go8b9qotbjuf9clq6sdtsdvcuf1jhl','Openvoips Technologies','OV500','122.169.33.222','122.169.33.222','/pbx/reports/api_livecall?action=search&call_flow=&max_rows=500&_=1759500082756','https://app.24ithub.com/pbx/reports/livecalls','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','reports/api_livecall','2025-10-03 14:04:02'),
(837,'track','g9go8b9qotbjuf9clq6sdtsdvcuf1jhl','Openvoips Technologies','OV500','122.169.33.222','122.169.33.222','/pbx/reports/api_livecall?action=search&call_flow=&max_rows=500&_=1759500082757','https://app.24ithub.com/pbx/reports/livecalls','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','reports/api_livecall','2025-10-03 14:04:08'),
(838,'track','g9go8b9qotbjuf9clq6sdtsdvcuf1jhl','Openvoips Technologies','OV500','122.169.33.222','122.169.33.222','/pbx/reports/api_livecall?action=search&call_flow=&max_rows=500&_=1759500082758','https://app.24ithub.com/pbx/reports/livecalls','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','reports/api_livecall','2025-10-03 14:04:13'),
(839,'track','g9go8b9qotbjuf9clq6sdtsdvcuf1jhl','Openvoips Technologies','OV500','122.169.33.222','122.169.33.222','/pbx/reports/api_livecall?action=search&call_flow=&max_rows=500&_=1759500082759','https://app.24ithub.com/pbx/reports/livecalls','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','reports/api_livecall','2025-10-03 14:04:18'),
(840,'track','g9go8b9qotbjuf9clq6sdtsdvcuf1jhl','Openvoips Technologies','OV500','122.169.33.222','122.169.33.222','/pbx/reports/api_livecall?action=search&call_flow=&max_rows=500&_=1759500082760','https://app.24ithub.com/pbx/reports/livecalls','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','reports/api_livecall','2025-10-03 14:04:23'),
(841,'track','g9go8b9qotbjuf9clq6sdtsdvcuf1jhl','Openvoips Technologies','OV500','122.169.33.222','122.169.33.222','/pbx/reports/api_livecall?action=search&call_flow=&max_rows=500&_=1759500082761','https://app.24ithub.com/pbx/reports/livecalls','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','reports/api_livecall','2025-10-03 14:04:28'),
(842,'track','g9go8b9qotbjuf9clq6sdtsdvcuf1jhl','Openvoips Technologies','OV500','122.169.33.222','122.169.33.222','/pbx/reports/api_livecall?action=search&call_flow=&max_rows=500&_=1759500082762','https://app.24ithub.com/pbx/reports/livecalls','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','reports/api_livecall','2025-10-03 14:04:33'),
(843,'track','g9go8b9qotbjuf9clq6sdtsdvcuf1jhl','Openvoips Technologies','OV500','122.169.33.222','122.169.33.222','/pbx/reports/api_livecall?action=search&call_flow=&max_rows=500&_=1759500082763','https://app.24ithub.com/pbx/reports/livecalls','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','reports/api_livecall','2025-10-03 14:04:38'),
(844,'track','g9go8b9qotbjuf9clq6sdtsdvcuf1jhl','Openvoips Technologies','OV500','122.169.33.222','122.169.33.222','/pbx/reports/api_livecall?action=search&call_flow=&max_rows=500&_=1759500082764','https://app.24ithub.com/pbx/reports/livecalls','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','reports/api_livecall','2025-10-03 14:04:43'),
(845,'track','g9go8b9qotbjuf9clq6sdtsdvcuf1jhl','Openvoips Technologies','OV500','122.169.33.222','122.169.33.222','/pbx/reports/api_livecall?action=search&call_flow=&max_rows=500&_=1759500082765','https://app.24ithub.com/pbx/reports/livecalls','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','reports/api_livecall','2025-10-03 14:04:49'),
(846,'track','g9go8b9qotbjuf9clq6sdtsdvcuf1jhl','Openvoips Technologies','OV500','122.169.33.222','122.169.33.222','/pbx/reports/api_livecall?action=search&call_flow=&max_rows=500&_=1759500082766','https://app.24ithub.com/pbx/reports/livecalls','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','reports/api_livecall','2025-10-03 14:04:53'),
(847,'track','g9go8b9qotbjuf9clq6sdtsdvcuf1jhl','Openvoips Technologies','OV500','122.169.33.222','122.169.33.222','/pbx/reports/api_livecall?action=search&call_flow=&max_rows=500&_=1759500082767','https://app.24ithub.com/pbx/reports/livecalls','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','reports/api_livecall','2025-10-03 14:04:59'),
(848,'track','g9go8b9qotbjuf9clq6sdtsdvcuf1jhl','Openvoips Technologies','OV500','122.169.33.222','122.169.33.222','/pbx/reports/api_livecall?action=search&call_flow=&max_rows=500&_=1759500082768','https://app.24ithub.com/pbx/reports/livecalls','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','reports/api_livecall','2025-10-03 14:05:04'),
(849,'track','g9go8b9qotbjuf9clq6sdtsdvcuf1jhl','Openvoips Technologies','OV500','122.169.33.222','122.169.33.222','/pbx/reports/api_livecall?action=search&call_flow=&max_rows=500&_=1759500082769','https://app.24ithub.com/pbx/reports/livecalls','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','reports/api_livecall','2025-10-03 14:05:09'),
(850,'track','g9go8b9qotbjuf9clq6sdtsdvcuf1jhl','Openvoips Technologies','OV500','122.169.33.222','122.169.33.222','/pbx/reports/api_livecall?action=search&call_flow=&max_rows=500&_=1759500082770','https://app.24ithub.com/pbx/reports/livecalls','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','reports/api_livecall','2025-10-03 14:05:14'),
(851,'track','g9go8b9qotbjuf9clq6sdtsdvcuf1jhl','Openvoips Technologies','OV500','122.169.33.222','122.169.33.222','/pbx/reports/api_livecall?action=search&call_flow=&max_rows=500&_=1759500082771','https://app.24ithub.com/pbx/reports/livecalls','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','reports/api_livecall','2025-10-03 14:05:19'),
(852,'track','g9go8b9qotbjuf9clq6sdtsdvcuf1jhl','Openvoips Technologies','OV500','122.169.33.222','122.169.33.222','/pbx/reports/api_livecall?action=search&call_flow=&max_rows=500&_=1759500082772','https://app.24ithub.com/pbx/reports/livecalls','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','reports/api_livecall','2025-10-03 14:05:24'),
(853,'track','g9go8b9qotbjuf9clq6sdtsdvcuf1jhl','Openvoips Technologies','OV500','122.169.33.222','122.169.33.222','/pbx/reports/api_livecall?action=search&call_flow=&max_rows=500&_=1759500082773','https://app.24ithub.com/pbx/reports/livecalls','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','reports/api_livecall','2025-10-03 14:05:29'),
(854,'track','g9go8b9qotbjuf9clq6sdtsdvcuf1jhl','Openvoips Technologies','OV500','122.169.33.222','122.169.33.222','/pbx/reports/api_livecall?action=search&call_flow=&max_rows=500&_=1759500082774','https://app.24ithub.com/pbx/reports/livecalls','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','reports/api_livecall','2025-10-03 14:05:34'),
(855,'track','g9go8b9qotbjuf9clq6sdtsdvcuf1jhl','Openvoips Technologies','OV500','122.169.33.222','122.169.33.222','/pbx/reports/api_livecall?action=search&call_flow=&max_rows=500&_=1759500082775','https://app.24ithub.com/pbx/reports/livecalls','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','reports/api_livecall','2025-10-03 14:05:40'),
(856,'track','g9go8b9qotbjuf9clq6sdtsdvcuf1jhl','Openvoips Technologies','OV500','122.169.33.222','122.169.33.222','/pbx/reports/api_livecall?action=search&call_flow=&max_rows=500&_=1759500082776','https://app.24ithub.com/pbx/reports/livecalls','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','reports/api_livecall','2025-10-03 14:05:44'),
(857,'track','g9go8b9qotbjuf9clq6sdtsdvcuf1jhl','Openvoips Technologies','OV500','122.169.33.222','122.169.33.222','/pbx/reports/api_livecall?action=search&call_flow=&max_rows=500&_=1759500082777','https://app.24ithub.com/pbx/reports/livecalls','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','reports/api_livecall','2025-10-03 14:05:50'),
(858,'track','g9go8b9qotbjuf9clq6sdtsdvcuf1jhl','Openvoips Technologies','OV500','122.169.33.222','122.169.33.222','/pbx/reports/api_livecall?action=search&call_flow=&max_rows=500&_=1759500082778','https://app.24ithub.com/pbx/reports/livecalls','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','reports/api_livecall','2025-10-03 14:05:55'),
(859,'track','g9go8b9qotbjuf9clq6sdtsdvcuf1jhl','Openvoips Technologies','OV500','122.169.33.222','122.169.33.222','/pbx/reports/api_livecall?action=search&call_flow=&max_rows=500&_=1759500082779','https://app.24ithub.com/pbx/reports/livecalls','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','reports/api_livecall','2025-10-03 14:06:00'),
(860,'track','g9go8b9qotbjuf9clq6sdtsdvcuf1jhl','Openvoips Technologies','OV500','122.169.33.222','122.169.33.222','/pbx/reports/api_livecall?action=search&call_flow=&max_rows=500&_=1759500082780','https://app.24ithub.com/pbx/reports/livecalls','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','reports/api_livecall','2025-10-03 14:06:05'),
(861,'track','g9go8b9qotbjuf9clq6sdtsdvcuf1jhl','Openvoips Technologies','OV500','122.169.33.222','122.169.33.222','/pbx/reports/api_livecall?action=search&call_flow=&max_rows=500&_=1759500082781','https://app.24ithub.com/pbx/reports/livecalls','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','reports/api_livecall','2025-10-03 14:06:10'),
(862,'track','g9go8b9qotbjuf9clq6sdtsdvcuf1jhl','Openvoips Technologies','OV500','122.169.33.222','122.169.33.222','/pbx/reports/api_livecall?action=search&call_flow=&max_rows=500&_=1759500082782','https://app.24ithub.com/pbx/reports/livecalls','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','reports/api_livecall','2025-10-03 14:06:15'),
(863,'track','g9go8b9qotbjuf9clq6sdtsdvcuf1jhl','Openvoips Technologies','OV500','122.169.33.222','122.169.33.222','/pbx/reports/api_livecall?action=search&call_flow=&max_rows=500&_=1759500082783','https://app.24ithub.com/pbx/reports/livecalls','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','reports/api_livecall','2025-10-03 14:06:20'),
(864,'track','g9go8b9qotbjuf9clq6sdtsdvcuf1jhl','Openvoips Technologies','OV500','122.169.33.222','122.169.33.222','/pbx/reports/api_livecall?action=search&call_flow=&max_rows=500&_=1759500082784','https://app.24ithub.com/pbx/reports/livecalls','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','reports/api_livecall','2025-10-03 14:06:25'),
(865,'track','g9go8b9qotbjuf9clq6sdtsdvcuf1jhl','Openvoips Technologies','OV500','122.169.33.222','122.169.33.222','/pbx/reports/api_livecall?action=search&call_flow=&max_rows=500&_=1759500082785','https://app.24ithub.com/pbx/reports/livecalls','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','reports/api_livecall','2025-10-03 14:06:31'),
(866,'track','','','','','','','',' CLI','Billing/callmanage','2025-10-03 14:06:31'),
(867,'track','','','','','','','',' CLI','Billing/callmanage','2025-10-03 14:06:33'),
(868,'track','','','','','','','',' CLI','Billing/callmanage','2025-10-03 14:06:34'),
(869,'track','g9go8b9qotbjuf9clq6sdtsdvcuf1jhl','Openvoips Technologies','OV500','122.169.33.222','122.169.33.222','/pbx/reports/api_livecall?action=search&call_flow=&max_rows=500&_=1759500082786','https://app.24ithub.com/pbx/reports/livecalls','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','reports/api_livecall','2025-10-03 14:06:35'),
(870,'track','g9go8b9qotbjuf9clq6sdtsdvcuf1jhl','Openvoips Technologies','OV500','122.169.33.222','122.169.33.222','/pbx/reports/api_livecall?action=search&call_flow=&max_rows=500&_=1759500082787','https://app.24ithub.com/pbx/reports/livecalls','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','reports/api_livecall','2025-10-03 14:06:41'),
(871,'track','','','','','','','',' CLI','Billing/callmanage','2025-10-03 14:06:44'),
(872,'track','g9go8b9qotbjuf9clq6sdtsdvcuf1jhl','Openvoips Technologies','OV500','122.169.33.222','122.169.33.222','/pbx/reports/api_livecall?action=search&call_flow=&max_rows=500&_=1759500082788','https://app.24ithub.com/pbx/reports/livecalls','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','reports/api_livecall','2025-10-03 14:06:45'),
(873,'track','','','','','','','',' CLI','Billing/callmanage','2025-10-03 14:06:46'),
(874,'track','g9go8b9qotbjuf9clq6sdtsdvcuf1jhl','Openvoips Technologies','OV500','122.169.33.222','122.169.33.222','/pbx/reports/api_livecall?action=search&call_flow=&max_rows=500&_=1759500082789','https://app.24ithub.com/pbx/reports/livecalls','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','reports/api_livecall','2025-10-03 14:06:51'),
(875,'track','g9go8b9qotbjuf9clq6sdtsdvcuf1jhl','Openvoips Technologies','OV500','122.169.33.222','122.169.33.222','/pbx/reports/api_livecall?action=search&call_flow=&max_rows=500&_=1759500082790','https://app.24ithub.com/pbx/reports/livecalls','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','reports/api_livecall','2025-10-03 14:06:56'),
(876,'track','g9go8b9qotbjuf9clq6sdtsdvcuf1jhl','Openvoips Technologies','OV500','122.169.33.222','122.169.33.222','/pbx/reports/api_livecall?action=search&call_flow=&max_rows=500&_=1759500082791','https://app.24ithub.com/pbx/reports/livecalls','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','reports/api_livecall','2025-10-03 14:07:01'),
(877,'track','5strb7c26mmr3kmlu16fs61mpi8j89cq','Openvoips Technologies','OV500','122.169.33.222','122.169.33.222','/reports/Calls','https://app.24ithub.com/pbx/reports/livecalls','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','reports/Calls','2025-10-03 14:07:02'),
(878,'track','5strb7c26mmr3kmlu16fs61mpi8j89cq','Openvoips Technologies','OV500','122.169.33.222','122.169.33.222','/pbx/reports/livecalls','https://app.24ithub.com/reports/Calls','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','reports/livecalls','2025-10-03 14:07:04'),
(879,'track','5strb7c26mmr3kmlu16fs61mpi8j89cq','Openvoips Technologies','OV500','122.169.33.222','122.169.33.222','/pbx/reports/api_livecall?action=search&call_flow=&max_rows=500&_=1759500424654','https://app.24ithub.com/pbx/reports/livecalls','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','reports/api_livecall','2025-10-03 14:07:05'),
(880,'track','5strb7c26mmr3kmlu16fs61mpi8j89cq','Openvoips Technologies','OV500','122.169.33.222','122.169.33.222','/pbx/operatorpanal','https://app.24ithub.com/pbx/reports/livecalls','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','operatorpanal/index','2025-10-03 14:07:09'),
(881,'track','5strb7c26mmr3kmlu16fs61mpi8j89cq','Openvoips Technologies','OV500','122.169.33.222','122.169.33.222','/pbx/operatorpanal/ajax_set_hangup/','https://app.24ithub.com/pbx/operatorpanal','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','operatorpanal/ajax_set_hangup','2025-10-03 14:07:12'),
(882,'track','5strb7c26mmr3kmlu16fs61mpi8j89cq','Openvoips Technologies','OV500','122.169.33.222','122.169.33.222','/pbx/operatorpanal/ajax_get_call_data','https://app.24ithub.com/pbx/operatorpanal','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','operatorpanal/ajax_get_call_data','2025-10-03 14:07:14'),
(883,'track','5strb7c26mmr3kmlu16fs61mpi8j89cq','Openvoips Technologies','OV500','122.169.33.222','122.169.33.222','/pbx/operatorpanal','https://app.24ithub.com/pbx/reports/livecalls','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','operatorpanal/index','2025-10-03 14:07:18'),
(884,'track','5strb7c26mmr3kmlu16fs61mpi8j89cq','Openvoips Technologies','OV500','122.169.33.222','122.169.33.222','/pbx/operatorpanal/ajax_get_call_data','https://app.24ithub.com/pbx/operatorpanal','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','operatorpanal/ajax_get_call_data','2025-10-03 14:07:24'),
(885,'track','5strb7c26mmr3kmlu16fs61mpi8j89cq','Openvoips Technologies','OV500','122.169.33.222','122.169.33.222','/pbx/operatorpanal/ajax_get_call_data','https://app.24ithub.com/pbx/operatorpanal','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','operatorpanal/ajax_get_call_data','2025-10-03 14:07:29'),
(886,'track','5strb7c26mmr3kmlu16fs61mpi8j89cq','Openvoips Technologies','OV500','122.169.33.222','122.169.33.222','/pbx/operatorpanal','https://app.24ithub.com/pbx/reports/livecalls','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','operatorpanal/index','2025-10-03 14:07:30'),
(887,'track','5strb7c26mmr3kmlu16fs61mpi8j89cq','Openvoips Technologies','OV500','122.169.33.222','122.169.33.222','/pbx/operatorpanal','https://app.24ithub.com/pbx/reports/livecalls','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','operatorpanal/index','2025-10-03 14:07:33'),
(888,'track','5strb7c26mmr3kmlu16fs61mpi8j89cq','Openvoips Technologies','OV500','122.169.33.222','122.169.33.222','/pbx/operatorpanal','https://app.24ithub.com/pbx/reports/livecalls','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','operatorpanal/index','2025-10-03 14:07:36'),
(889,'track','5strb7c26mmr3kmlu16fs61mpi8j89cq','Openvoips Technologies','OV500','122.169.33.222','122.169.33.222','/pbx/operatorpanal/ajax_get_call_data','https://app.24ithub.com/pbx/operatorpanal','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','operatorpanal/ajax_get_call_data','2025-10-03 14:07:42'),
(890,'track','5strb7c26mmr3kmlu16fs61mpi8j89cq','Openvoips Technologies','OV500','122.169.33.222','122.169.33.222','/pbx/agents','https://app.24ithub.com/pbx/operatorpanal','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','agents/index','2025-10-03 14:07:43'),
(891,'track','5strb7c26mmr3kmlu16fs61mpi8j89cq','Openvoips Technologies','OV500','122.169.33.222','122.169.33.222','/pbx/agents/add','https://app.24ithub.com/pbx/agents','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','agents/add','2025-10-03 14:07:45'),
(892,'track','5strb7c26mmr3kmlu16fs61mpi8j89cq','Openvoips Technologies','OV500','122.169.33.222','122.169.33.222','/pbx/agents/add','https://app.24ithub.com/pbx/agents/add','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','agents/add','2025-10-03 14:08:06'),
(893,'track','5strb7c26mmr3kmlu16fs61mpi8j89cq','Openvoips Technologies','OV500','122.169.33.222','122.169.33.222','/pbx/agents/index','https://app.24ithub.com/pbx/agents/add','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','agents/index','2025-10-03 14:08:06'),
(894,'track','5strb7c26mmr3kmlu16fs61mpi8j89cq','Openvoips Technologies','OV500','122.169.33.222','122.169.33.222','/pbx/queue','https://app.24ithub.com/pbx/agents/index','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','queue/index','2025-10-03 14:08:08'),
(895,'track','5strb7c26mmr3kmlu16fs61mpi8j89cq','Openvoips Technologies','OV500','122.169.33.222','122.169.33.222','/pbx/queue/add','https://app.24ithub.com/pbx/queue','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','queue/add','2025-10-03 14:08:10'),
(896,'track','5strb7c26mmr3kmlu16fs61mpi8j89cq','Openvoips Technologies','OV500','122.169.33.222','122.169.33.222','/pbx/queue/add','https://app.24ithub.com/pbx/queue/add','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','queue/add','2025-10-03 14:08:29'),
(897,'track','5strb7c26mmr3kmlu16fs61mpi8j89cq','Openvoips Technologies','OV500','122.169.33.222','122.169.33.222','/pbx/queue/index','https://app.24ithub.com/pbx/queue/add','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','queue/index','2025-10-03 14:08:29'),
(898,'track','jeagmpajgcaqr3gr626ssm5nlqa9fluc','','','195.178.110.160','195.178.110.160','/','','l9tcpid/v1.1.0','login/index','2025-10-03 14:08:31'),
(899,'track','a0f0h9jdkd7aphmhflg3din47fun346h','','','195.178.110.160','195.178.110.160','/','','l9tcpid/v1.1.0','login/index','2025-10-03 14:08:31'),
(900,'track','mqhu5dt7en8bipj5q9kvelrl9588i5qh','','','185.244.104.2','185.244.104.2','/','http://173.208.52.222:443/','-','login/index','2025-10-03 14:48:44'),
(901,'track','10j7ck4nsi8a8kc0lui0ivu5dgio0sti','Openvoips Technologies','OV500','122.169.33.222','122.169.33.222','/pbx/operatorpanal','https://app.24ithub.com/pbx/queue/index','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','operatorpanal/index','2025-10-03 14:51:42'),
(902,'track','10j7ck4nsi8a8kc0lui0ivu5dgio0sti','Openvoips Technologies','OV500','122.169.33.222','122.169.33.222','/pbx/operatorpanal/ajax_get_call_data','https://app.24ithub.com/pbx/operatorpanal','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','operatorpanal/ajax_get_call_data','2025-10-03 14:51:47'),
(903,'track','10j7ck4nsi8a8kc0lui0ivu5dgio0sti','Openvoips Technologies','OV500','122.169.33.222','122.169.33.222','/pbx/reports/livecalls','https://app.24ithub.com/pbx/operatorpanal','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','reports/livecalls','2025-10-03 14:51:50'),
(904,'track','10j7ck4nsi8a8kc0lui0ivu5dgio0sti','Openvoips Technologies','OV500','122.169.33.222','122.169.33.222','/pbx/reports/api_livecall?action=search&call_flow=&max_rows=500&_=1759503110154','https://app.24ithub.com/pbx/reports/livecalls','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','reports/api_livecall','2025-10-03 14:51:50'),
(905,'track','10j7ck4nsi8a8kc0lui0ivu5dgio0sti','Openvoips Technologies','OV500','122.169.33.222','122.169.33.222','/pbx/reports/api_livecall?action=search&call_flow=&max_rows=500&_=1759503110155','https://app.24ithub.com/pbx/reports/livecalls','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','reports/api_livecall','2025-10-03 14:51:56'),
(906,'track','10j7ck4nsi8a8kc0lui0ivu5dgio0sti','Openvoips Technologies','OV500','122.169.33.222','122.169.33.222','/pbx/reports/api_livecall?action=search&call_flow=&max_rows=500&_=1759503110156','https://app.24ithub.com/pbx/reports/livecalls','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','reports/api_livecall','2025-10-03 14:52:00'),
(907,'track','10j7ck4nsi8a8kc0lui0ivu5dgio0sti','Openvoips Technologies','OV500','122.169.33.222','122.169.33.222','/pbx/reports/api_livecall?action=search&call_flow=&max_rows=500&_=1759503110157','https://app.24ithub.com/pbx/reports/livecalls','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','reports/api_livecall','2025-10-03 14:52:05'),
(908,'track','10j7ck4nsi8a8kc0lui0ivu5dgio0sti','Openvoips Technologies','OV500','122.169.33.222','122.169.33.222','/pbx/reports/api_livecall?action=search&call_flow=&max_rows=500&_=1759503110158','https://app.24ithub.com/pbx/reports/livecalls','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','reports/api_livecall','2025-10-03 14:52:11'),
(909,'track','10j7ck4nsi8a8kc0lui0ivu5dgio0sti','Openvoips Technologies','OV500','122.169.33.222','122.169.33.222','/pbx/reports/api_livecall?action=search&call_flow=&max_rows=500&_=1759503110159','https://app.24ithub.com/pbx/reports/livecalls','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','reports/api_livecall','2025-10-03 14:52:15'),
(910,'track','10j7ck4nsi8a8kc0lui0ivu5dgio0sti','Openvoips Technologies','OV500','122.169.33.222','122.169.33.222','/pbx/reports/api_livecall?action=search&call_flow=&max_rows=500&_=1759503110160','https://app.24ithub.com/pbx/reports/livecalls','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','reports/api_livecall','2025-10-03 14:52:21'),
(911,'track','10j7ck4nsi8a8kc0lui0ivu5dgio0sti','Openvoips Technologies','OV500','122.169.33.222','122.169.33.222','/pbx/reports/api_livecall?action=search&call_flow=&max_rows=500&_=1759503110161','https://app.24ithub.com/pbx/reports/livecalls','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','reports/api_livecall','2025-10-03 14:52:26'),
(912,'track','10j7ck4nsi8a8kc0lui0ivu5dgio0sti','Openvoips Technologies','OV500','122.169.33.222','122.169.33.222','/pbx/reports/api_livecall?action=search&call_flow=&max_rows=500&_=1759503110162','https://app.24ithub.com/pbx/reports/livecalls','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','reports/api_livecall','2025-10-03 14:52:31'),
(913,'track','10j7ck4nsi8a8kc0lui0ivu5dgio0sti','Openvoips Technologies','OV500','122.169.33.222','122.169.33.222','/pbx/reports/api_livecall?action=search&call_flow=&max_rows=500&_=1759503110163','https://app.24ithub.com/pbx/reports/livecalls','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','reports/api_livecall','2025-10-03 14:52:36'),
(914,'track','10j7ck4nsi8a8kc0lui0ivu5dgio0sti','Openvoips Technologies','OV500','122.169.33.222','122.169.33.222','/pbx/reports/api_livecall?action=search&call_flow=&max_rows=500&_=1759503110164','https://app.24ithub.com/pbx/reports/livecalls','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','reports/api_livecall','2025-10-03 14:52:41'),
(915,'track','10j7ck4nsi8a8kc0lui0ivu5dgio0sti','Openvoips Technologies','OV500','122.169.33.222','122.169.33.222','/pbx/reports/api_livecall?action=search&call_flow=&max_rows=500&_=1759503110165','https://app.24ithub.com/pbx/reports/livecalls','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','reports/api_livecall','2025-10-03 14:52:46'),
(916,'track','10j7ck4nsi8a8kc0lui0ivu5dgio0sti','Openvoips Technologies','OV500','122.169.33.222','122.169.33.222','/pbx/reports/api_livecall?action=search&call_flow=&max_rows=500&_=1759503110166','https://app.24ithub.com/pbx/reports/livecalls','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','reports/api_livecall','2025-10-03 14:52:52'),
(917,'track','10j7ck4nsi8a8kc0lui0ivu5dgio0sti','Openvoips Technologies','OV500','122.169.33.222','122.169.33.222','/pbx/reports/api_livecall?action=search&call_flow=&max_rows=500&_=1759503110167','https://app.24ithub.com/pbx/reports/livecalls','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','reports/api_livecall','2025-10-03 14:52:56'),
(918,'track','10j7ck4nsi8a8kc0lui0ivu5dgio0sti','Openvoips Technologies','OV500','122.169.33.222','122.169.33.222','/pbx/reports/api_livecall?action=search&call_flow=&max_rows=500&_=1759503110168','https://app.24ithub.com/pbx/reports/livecalls','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','reports/api_livecall','2025-10-03 14:53:02'),
(919,'track','10j7ck4nsi8a8kc0lui0ivu5dgio0sti','Openvoips Technologies','OV500','122.169.33.222','122.169.33.222','/pbx/reports/api_livecall?action=search&call_flow=&max_rows=500&_=1759503110169','https://app.24ithub.com/pbx/reports/livecalls','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','reports/api_livecall','2025-10-03 14:53:07'),
(920,'track','10j7ck4nsi8a8kc0lui0ivu5dgio0sti','Openvoips Technologies','OV500','122.169.33.222','122.169.33.222','/pbx/reports/api_livecall?action=search&call_flow=&max_rows=500&_=1759503110170','https://app.24ithub.com/pbx/reports/livecalls','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','reports/api_livecall','2025-10-03 14:53:12'),
(921,'track','10j7ck4nsi8a8kc0lui0ivu5dgio0sti','Openvoips Technologies','OV500','122.169.33.222','122.169.33.222','/pbx/reports/api_livecall?action=search&call_flow=&max_rows=500&_=1759503110171','https://app.24ithub.com/pbx/reports/livecalls','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','reports/api_livecall','2025-10-03 14:53:17'),
(922,'track','10j7ck4nsi8a8kc0lui0ivu5dgio0sti','Openvoips Technologies','OV500','122.169.33.222','122.169.33.222','/pbx/reports/api_livecall?action=search&call_flow=&max_rows=500&_=1759503110172','https://app.24ithub.com/pbx/reports/livecalls','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','reports/api_livecall','2025-10-03 14:53:23'),
(923,'track','10j7ck4nsi8a8kc0lui0ivu5dgio0sti','Openvoips Technologies','OV500','122.169.33.222','122.169.33.222','/pbx/reports/api_livecall?action=search&call_flow=&max_rows=500&_=1759503110173','https://app.24ithub.com/pbx/reports/livecalls','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','reports/api_livecall','2025-10-03 14:53:27'),
(924,'track','10j7ck4nsi8a8kc0lui0ivu5dgio0sti','Openvoips Technologies','OV500','122.169.33.222','122.169.33.222','/pbx/reports/api_livecall?action=search&call_flow=&max_rows=500&_=1759503110174','https://app.24ithub.com/pbx/reports/livecalls','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','reports/api_livecall','2025-10-03 14:53:33'),
(925,'track','10j7ck4nsi8a8kc0lui0ivu5dgio0sti','Openvoips Technologies','OV500','122.169.33.222','122.169.33.222','/pbx/reports/api_livecall?action=search&call_flow=&max_rows=500&_=1759503110175','https://app.24ithub.com/pbx/reports/livecalls','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','reports/api_livecall','2025-10-03 14:53:37'),
(926,'track','10j7ck4nsi8a8kc0lui0ivu5dgio0sti','Openvoips Technologies','OV500','122.169.33.222','122.169.33.222','/pbx/reports/api_livecall?action=search&call_flow=&max_rows=500&_=1759503110176','https://app.24ithub.com/pbx/reports/livecalls','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','reports/api_livecall','2025-10-03 14:53:43'),
(927,'track','10j7ck4nsi8a8kc0lui0ivu5dgio0sti','Openvoips Technologies','OV500','122.169.33.222','122.169.33.222','/pbx/reports/api_livecall?action=search&call_flow=&max_rows=500&_=1759503110177','https://app.24ithub.com/pbx/reports/livecalls','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','reports/api_livecall','2025-10-03 14:53:47'),
(928,'track','10j7ck4nsi8a8kc0lui0ivu5dgio0sti','Openvoips Technologies','OV500','122.169.33.222','122.169.33.222','/pbx/reports/api_livecall?action=search&call_flow=&max_rows=500&_=1759503110178','https://app.24ithub.com/pbx/reports/livecalls','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','reports/api_livecall','2025-10-03 14:53:53'),
(929,'track','10j7ck4nsi8a8kc0lui0ivu5dgio0sti','Openvoips Technologies','OV500','122.169.33.222','122.169.33.222','/pbx/reports/api_livecall?action=search&call_flow=&max_rows=500&_=1759503110179','https://app.24ithub.com/pbx/reports/livecalls','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','reports/api_livecall','2025-10-03 14:53:59'),
(930,'track','10j7ck4nsi8a8kc0lui0ivu5dgio0sti','Openvoips Technologies','OV500','122.169.33.222','122.169.33.222','/pbx/reports/api_livecall?action=search&call_flow=&max_rows=500&_=1759503110180','https://app.24ithub.com/pbx/reports/livecalls','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','reports/api_livecall','2025-10-03 14:54:03'),
(931,'track','10j7ck4nsi8a8kc0lui0ivu5dgio0sti','Openvoips Technologies','OV500','122.169.33.222','122.169.33.222','/pbx/reports/api_livecall?action=search&call_flow=&max_rows=500&_=1759503110181','https://app.24ithub.com/pbx/reports/livecalls','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','reports/api_livecall','2025-10-03 14:54:08'),
(932,'track','10j7ck4nsi8a8kc0lui0ivu5dgio0sti','Openvoips Technologies','OV500','122.169.33.222','122.169.33.222','/pbx/reports/api_livecall?action=search&call_flow=&max_rows=500&_=1759503110182','https://app.24ithub.com/pbx/reports/livecalls','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','reports/api_livecall','2025-10-03 14:54:13'),
(933,'track','10j7ck4nsi8a8kc0lui0ivu5dgio0sti','Openvoips Technologies','OV500','122.169.33.222','122.169.33.222','/pbx/reports/api_livecall?action=search&call_flow=&max_rows=500&_=1759503110183','https://app.24ithub.com/pbx/reports/livecalls','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','reports/api_livecall','2025-10-03 14:54:19'),
(934,'track','10j7ck4nsi8a8kc0lui0ivu5dgio0sti','Openvoips Technologies','OV500','122.169.33.222','122.169.33.222','/pbx/reports/api_livecall?action=search&call_flow=&max_rows=500&_=1759503110184','https://app.24ithub.com/pbx/reports/livecalls','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','reports/api_livecall','2025-10-03 14:54:23'),
(935,'track','10j7ck4nsi8a8kc0lui0ivu5dgio0sti','Openvoips Technologies','OV500','122.169.33.222','122.169.33.222','/pbx/reports/api_livecall?action=search&call_flow=&max_rows=500&_=1759503110185','https://app.24ithub.com/pbx/reports/livecalls','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','reports/api_livecall','2025-10-03 14:54:29'),
(936,'track','10j7ck4nsi8a8kc0lui0ivu5dgio0sti','Openvoips Technologies','OV500','122.169.33.222','122.169.33.222','/pbx/reports/api_livecall?action=search&call_flow=&max_rows=500&_=1759503110186','https://app.24ithub.com/pbx/reports/livecalls','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','reports/api_livecall','2025-10-03 14:54:34'),
(937,'track','10j7ck4nsi8a8kc0lui0ivu5dgio0sti','Openvoips Technologies','OV500','122.169.33.222','122.169.33.222','/pbx/reports/api_livecall?action=search&call_flow=&max_rows=500&_=1759503110187','https://app.24ithub.com/pbx/reports/livecalls','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','reports/api_livecall','2025-10-03 14:54:39'),
(938,'track','10j7ck4nsi8a8kc0lui0ivu5dgio0sti','Openvoips Technologies','OV500','122.169.33.222','122.169.33.222','/pbx/reports/api_livecall?action=search&call_flow=&max_rows=500&_=1759503110188','https://app.24ithub.com/pbx/reports/livecalls','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','reports/api_livecall','2025-10-03 14:54:44'),
(939,'track','10j7ck4nsi8a8kc0lui0ivu5dgio0sti','Openvoips Technologies','OV500','122.169.33.222','122.169.33.222','/pbx/reports/api_livecall?action=search&call_flow=&max_rows=500&_=1759503110189','https://app.24ithub.com/pbx/reports/livecalls','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','reports/api_livecall','2025-10-03 14:54:49'),
(940,'track','10j7ck4nsi8a8kc0lui0ivu5dgio0sti','Openvoips Technologies','OV500','122.169.33.222','122.169.33.222','/pbx/reports/api_livecall?action=search&call_flow=&max_rows=500&_=1759503110190','https://app.24ithub.com/pbx/reports/livecalls','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','reports/api_livecall','2025-10-03 14:54:54'),
(941,'track','10j7ck4nsi8a8kc0lui0ivu5dgio0sti','Openvoips Technologies','OV500','122.169.33.222','122.169.33.222','/pbx/reports/api_livecall?action=search&call_flow=&max_rows=500&_=1759503110191','https://app.24ithub.com/pbx/reports/livecalls','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','reports/api_livecall','2025-10-03 14:55:00'),
(942,'track','10j7ck4nsi8a8kc0lui0ivu5dgio0sti','Openvoips Technologies','OV500','122.169.33.222','122.169.33.222','/pbx/reports/api_livecall?action=search&call_flow=&max_rows=500&_=1759503110192','https://app.24ithub.com/pbx/reports/livecalls','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','reports/api_livecall','2025-10-03 14:55:04'),
(943,'track','10j7ck4nsi8a8kc0lui0ivu5dgio0sti','Openvoips Technologies','OV500','122.169.33.222','122.169.33.222','/pbx/reports/api_livecall?action=search&call_flow=&max_rows=500&_=1759503110193','https://app.24ithub.com/pbx/reports/livecalls','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','reports/api_livecall','2025-10-03 14:55:10'),
(944,'track','10j7ck4nsi8a8kc0lui0ivu5dgio0sti','Openvoips Technologies','OV500','122.169.33.222','122.169.33.222','/pbx/reports/api_livecall?action=search&call_flow=&max_rows=500&_=1759503110194','https://app.24ithub.com/pbx/reports/livecalls','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','reports/api_livecall','2025-10-03 14:55:15'),
(945,'track','10j7ck4nsi8a8kc0lui0ivu5dgio0sti','Openvoips Technologies','OV500','122.169.33.222','122.169.33.222','/pbx/reports/api_livecall?action=search&call_flow=&max_rows=500&_=1759503110195','https://app.24ithub.com/pbx/reports/livecalls','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','reports/api_livecall','2025-10-03 14:55:20'),
(946,'track','10j7ck4nsi8a8kc0lui0ivu5dgio0sti','Openvoips Technologies','OV500','122.169.33.222','122.169.33.222','/pbx/reports/api_livecall?action=search&call_flow=&max_rows=500&_=1759503110196','https://app.24ithub.com/pbx/reports/livecalls','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','reports/api_livecall','2025-10-03 14:55:25'),
(947,'track','10j7ck4nsi8a8kc0lui0ivu5dgio0sti','Openvoips Technologies','OV500','122.169.33.222','122.169.33.222','/pbx/reports/api_livecall?action=search&call_flow=&max_rows=500&_=1759503110197','https://app.24ithub.com/pbx/reports/livecalls','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','reports/api_livecall','2025-10-03 14:55:30'),
(948,'track','10j7ck4nsi8a8kc0lui0ivu5dgio0sti','Openvoips Technologies','OV500','122.169.33.222','122.169.33.222','/pbx/reports/api_livecall?action=search&call_flow=&max_rows=500&_=1759503110198','https://app.24ithub.com/pbx/reports/livecalls','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','reports/api_livecall','2025-10-03 14:55:35'),
(949,'track','10j7ck4nsi8a8kc0lui0ivu5dgio0sti','Openvoips Technologies','OV500','122.169.33.222','122.169.33.222','/pbx/reports/api_livecall?action=search&call_flow=&max_rows=500&_=1759503110199','https://app.24ithub.com/pbx/reports/livecalls','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','reports/api_livecall','2025-10-03 14:55:40'),
(950,'track','10j7ck4nsi8a8kc0lui0ivu5dgio0sti','Openvoips Technologies','OV500','122.169.33.222','122.169.33.222','/pbx/reports/api_livecall?action=search&call_flow=&max_rows=500&_=1759503110200','https://app.24ithub.com/pbx/reports/livecalls','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','reports/api_livecall','2025-10-03 14:55:45'),
(951,'track','10j7ck4nsi8a8kc0lui0ivu5dgio0sti','Openvoips Technologies','OV500','122.169.33.222','122.169.33.222','/pbx/reports/api_livecall?action=search&call_flow=&max_rows=500&_=1759503110201','https://app.24ithub.com/pbx/reports/livecalls','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','reports/api_livecall','2025-10-03 14:55:51'),
(952,'track','10j7ck4nsi8a8kc0lui0ivu5dgio0sti','Openvoips Technologies','OV500','122.169.33.222','122.169.33.222','/pbx/reports/api_livecall?action=search&call_flow=&max_rows=500&_=1759503110202','https://app.24ithub.com/pbx/reports/livecalls','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','reports/api_livecall','2025-10-03 14:55:55'),
(953,'track','10j7ck4nsi8a8kc0lui0ivu5dgio0sti','Openvoips Technologies','OV500','122.169.33.222','122.169.33.222','/pbx/reports/api_livecall?action=search&call_flow=&max_rows=500&_=1759503110203','https://app.24ithub.com/pbx/reports/livecalls','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','reports/api_livecall','2025-10-03 14:56:01'),
(954,'track','10j7ck4nsi8a8kc0lui0ivu5dgio0sti','Openvoips Technologies','OV500','122.169.33.222','122.169.33.222','/pbx/reports/api_livecall?action=search&call_flow=&max_rows=500&_=1759503110204','https://app.24ithub.com/pbx/reports/livecalls','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','reports/api_livecall','2025-10-03 14:56:05'),
(955,'track','10j7ck4nsi8a8kc0lui0ivu5dgio0sti','Openvoips Technologies','OV500','122.169.33.222','122.169.33.222','/pbx/reports/api_livecall?action=search&call_flow=&max_rows=500&_=1759503110205','https://app.24ithub.com/pbx/reports/livecalls','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','reports/api_livecall','2025-10-03 14:56:11'),
(956,'track','10j7ck4nsi8a8kc0lui0ivu5dgio0sti','Openvoips Technologies','OV500','122.169.33.222','122.169.33.222','/pbx/reports/api_livecall?action=search&call_flow=&max_rows=500&_=1759503110206','https://app.24ithub.com/pbx/reports/livecalls','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','reports/api_livecall','2025-10-03 14:56:15'),
(957,'track','10j7ck4nsi8a8kc0lui0ivu5dgio0sti','Openvoips Technologies','OV500','122.169.33.222','122.169.33.222','/pbx/reports/api_livecall?action=search&call_flow=&max_rows=500&_=1759503110207','https://app.24ithub.com/pbx/reports/livecalls','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','reports/api_livecall','2025-10-03 14:56:21'),
(958,'track','10j7ck4nsi8a8kc0lui0ivu5dgio0sti','Openvoips Technologies','OV500','122.169.33.222','122.169.33.222','/pbx/reports/api_livecall?action=search&call_flow=&max_rows=500&_=1759503110208','https://app.24ithub.com/pbx/reports/livecalls','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','reports/api_livecall','2025-10-03 14:56:26'),
(959,'track','10j7ck4nsi8a8kc0lui0ivu5dgio0sti','Openvoips Technologies','OV500','122.169.33.222','122.169.33.222','/pbx/reports/api_livecall?action=search&call_flow=&max_rows=500&_=1759503110209','https://app.24ithub.com/pbx/reports/livecalls','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','reports/api_livecall','2025-10-03 14:56:31'),
(960,'track','10j7ck4nsi8a8kc0lui0ivu5dgio0sti','Openvoips Technologies','OV500','122.169.33.222','122.169.33.222','/pbx/reports/api_livecall?action=search&call_flow=&max_rows=500&_=1759503110210','https://app.24ithub.com/pbx/reports/livecalls','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','reports/api_livecall','2025-10-03 14:56:36'),
(961,'track','10j7ck4nsi8a8kc0lui0ivu5dgio0sti','Openvoips Technologies','OV500','122.169.33.222','122.169.33.222','/pbx/reports/api_livecall?action=search&call_flow=&max_rows=500&_=1759503110211','https://app.24ithub.com/pbx/reports/livecalls','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','reports/api_livecall','2025-10-03 14:56:41'),
(962,'track','10j7ck4nsi8a8kc0lui0ivu5dgio0sti','Openvoips Technologies','OV500','122.169.33.222','122.169.33.222','/pbx/reports/api_livecall?action=search&call_flow=&max_rows=500&_=1759503110212','https://app.24ithub.com/pbx/reports/livecalls','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','reports/api_livecall','2025-10-03 14:56:46'),
(963,'track','10j7ck4nsi8a8kc0lui0ivu5dgio0sti','Openvoips Technologies','OV500','122.169.33.222','122.169.33.222','/pbx/reports/api_livecall?action=search&call_flow=&max_rows=500&_=1759503110213','https://app.24ithub.com/pbx/reports/livecalls','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','reports/api_livecall','2025-10-03 14:56:52'),
(964,'track','10j7ck4nsi8a8kc0lui0ivu5dgio0sti','Openvoips Technologies','OV500','122.169.33.222','122.169.33.222','/pbx/reports/api_livecall?action=search&call_flow=&max_rows=500&_=1759503110214','https://app.24ithub.com/pbx/reports/livecalls','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','reports/api_livecall','2025-10-03 14:56:56'),
(965,'track','10j7ck4nsi8a8kc0lui0ivu5dgio0sti','Openvoips Technologies','OV500','122.169.33.222','122.169.33.222','/pbx/reports/api_livecall?action=search&call_flow=&max_rows=500&_=1759503110215','https://app.24ithub.com/pbx/reports/livecalls','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','reports/api_livecall','2025-10-03 14:57:02'),
(966,'track','10j7ck4nsi8a8kc0lui0ivu5dgio0sti','Openvoips Technologies','OV500','122.169.33.222','122.169.33.222','/pbx/reports/api_livecall?action=search&call_flow=&max_rows=500&_=1759503110216','https://app.24ithub.com/pbx/reports/livecalls','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','reports/api_livecall','2025-10-03 14:57:07'),
(967,'track','10j7ck4nsi8a8kc0lui0ivu5dgio0sti','Openvoips Technologies','OV500','122.169.33.222','122.169.33.222','/pbx/reports/api_livecall?action=search&call_flow=&max_rows=500&_=1759503110217','https://app.24ithub.com/pbx/reports/livecalls','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','reports/api_livecall','2025-10-03 14:57:12'),
(968,'track','10j7ck4nsi8a8kc0lui0ivu5dgio0sti','Openvoips Technologies','OV500','122.169.33.222','122.169.33.222','/pbx/reports/api_livecall?action=search&call_flow=&max_rows=500&_=1759503110218','https://app.24ithub.com/pbx/reports/livecalls','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','reports/api_livecall','2025-10-03 14:57:17'),
(969,'track','10j7ck4nsi8a8kc0lui0ivu5dgio0sti','Openvoips Technologies','OV500','122.169.33.222','122.169.33.222','/pbx/reports/api_livecall?action=search&call_flow=&max_rows=500&_=1759503110219','https://app.24ithub.com/pbx/reports/livecalls','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','reports/api_livecall','2025-10-03 14:57:23'),
(970,'track','10j7ck4nsi8a8kc0lui0ivu5dgio0sti','Openvoips Technologies','OV500','122.169.33.222','122.169.33.222','/pbx/reports/api_livecall?action=search&call_flow=&max_rows=500&_=1759503110220','https://app.24ithub.com/pbx/reports/livecalls','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','reports/api_livecall','2025-10-03 14:57:27'),
(971,'track','10j7ck4nsi8a8kc0lui0ivu5dgio0sti','Openvoips Technologies','OV500','122.169.33.222','122.169.33.222','/pbx/reports/api_livecall?action=search&call_flow=&max_rows=500&_=1759503110221','https://app.24ithub.com/pbx/reports/livecalls','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','reports/api_livecall','2025-10-03 14:57:32'),
(972,'track','10j7ck4nsi8a8kc0lui0ivu5dgio0sti','Openvoips Technologies','OV500','122.169.33.222','122.169.33.222','/pbx/reports/api_livecall?action=search&call_flow=&max_rows=500&_=1759503110222','https://app.24ithub.com/pbx/reports/livecalls','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','reports/api_livecall','2025-10-03 14:57:37'),
(973,'track','10j7ck4nsi8a8kc0lui0ivu5dgio0sti','Openvoips Technologies','OV500','122.169.33.222','122.169.33.222','/pbx/reports/api_livecall?action=search&call_flow=&max_rows=500&_=1759503110223','https://app.24ithub.com/pbx/reports/livecalls','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','reports/api_livecall','2025-10-03 14:57:45'),
(974,'track','10j7ck4nsi8a8kc0lui0ivu5dgio0sti','Openvoips Technologies','OV500','122.169.33.222','122.169.33.222','/pbx/reports/api_livecall?action=search&call_flow=&max_rows=500&_=1759503110224','https://app.24ithub.com/pbx/reports/livecalls','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','reports/api_livecall','2025-10-03 14:57:48'),
(975,'track','10j7ck4nsi8a8kc0lui0ivu5dgio0sti','Openvoips Technologies','OV500','122.169.33.222','122.169.33.222','/pbx/reports/api_livecall?action=search&call_flow=&max_rows=500&_=1759503110225','https://app.24ithub.com/pbx/reports/livecalls','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','reports/api_livecall','2025-10-03 14:57:52'),
(976,'track','10j7ck4nsi8a8kc0lui0ivu5dgio0sti','Openvoips Technologies','OV500','122.169.33.222','122.169.33.222','/pbx/reports/api_livecall?action=search&call_flow=&max_rows=500&_=1759503110226','https://app.24ithub.com/pbx/reports/livecalls','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','reports/api_livecall','2025-10-03 14:57:58'),
(977,'track','10j7ck4nsi8a8kc0lui0ivu5dgio0sti','Openvoips Technologies','OV500','122.169.33.222','122.169.33.222','/pbx/reports/api_livecall?action=search&call_flow=&max_rows=500&_=1759503110227','https://app.24ithub.com/pbx/reports/livecalls','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','reports/api_livecall','2025-10-03 14:58:02'),
(978,'track','10j7ck4nsi8a8kc0lui0ivu5dgio0sti','Openvoips Technologies','OV500','122.169.33.222','122.169.33.222','/pbx/reports/api_livecall?action=search&call_flow=&max_rows=500&_=1759503110228','https://app.24ithub.com/pbx/reports/livecalls','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','reports/api_livecall','2025-10-03 14:58:08'),
(979,'track','10j7ck4nsi8a8kc0lui0ivu5dgio0sti','Openvoips Technologies','OV500','122.169.33.222','122.169.33.222','/pbx/reports/api_livecall?action=search&call_flow=&max_rows=500&_=1759503110229','https://app.24ithub.com/pbx/reports/livecalls','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','reports/api_livecall','2025-10-03 14:58:13'),
(980,'track','10j7ck4nsi8a8kc0lui0ivu5dgio0sti','Openvoips Technologies','OV500','122.169.33.222','122.169.33.222','/pbx/reports/api_livecall?action=search&call_flow=&max_rows=500&_=1759503110232','https://app.24ithub.com/pbx/reports/livecalls','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','reports/api_livecall','2025-10-03 14:58:41'),
(981,'track','10j7ck4nsi8a8kc0lui0ivu5dgio0sti','Openvoips Technologies','OV500','122.169.33.222','122.169.33.222','/pbx/reports/api_livecall?action=search&call_flow=&max_rows=500&_=1759503110230','https://app.24ithub.com/pbx/reports/livecalls','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','reports/api_livecall','2025-10-03 14:58:41'),
(982,'track','10j7ck4nsi8a8kc0lui0ivu5dgio0sti','Openvoips Technologies','OV500','122.169.33.222','122.169.33.222','/pbx/reports/api_livecall?action=search&call_flow=&max_rows=500&_=1759503110234','https://app.24ithub.com/pbx/reports/livecalls','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','reports/api_livecall','2025-10-03 14:58:41'),
(983,'track','10j7ck4nsi8a8kc0lui0ivu5dgio0sti','Openvoips Technologies','OV500','122.169.33.222','122.169.33.222','/pbx/reports/api_livecall?action=search&call_flow=&max_rows=500&_=1759503110233','https://app.24ithub.com/pbx/reports/livecalls','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','reports/api_livecall','2025-10-03 14:58:41'),
(984,'track','10j7ck4nsi8a8kc0lui0ivu5dgio0sti','Openvoips Technologies','OV500','122.169.33.222','122.169.33.222','/pbx/reports/api_livecall?action=search&call_flow=&max_rows=500&_=1759503110231','https://app.24ithub.com/pbx/reports/livecalls','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','reports/api_livecall','2025-10-03 14:58:41'),
(985,'track','10j7ck4nsi8a8kc0lui0ivu5dgio0sti','Openvoips Technologies','OV500','122.169.33.222','122.169.33.222','/pbx/reports/api_livecall?action=search&call_flow=&max_rows=500&_=1759503110235','https://app.24ithub.com/pbx/reports/livecalls','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','reports/api_livecall','2025-10-03 14:58:44'),
(986,'track','10j7ck4nsi8a8kc0lui0ivu5dgio0sti','Openvoips Technologies','OV500','122.169.33.222','122.169.33.222','/pbx/reports/api_livecall?action=search&call_flow=&max_rows=500&_=1759503110236','https://app.24ithub.com/pbx/reports/livecalls','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','reports/api_livecall','2025-10-03 14:58:50'),
(987,'track','10j7ck4nsi8a8kc0lui0ivu5dgio0sti','Openvoips Technologies','OV500','122.169.33.222','122.169.33.222','/pbx/reports/api_livecall?action=search&call_flow=&max_rows=500&_=1759503110237','https://app.24ithub.com/pbx/reports/livecalls','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','reports/api_livecall','2025-10-03 14:58:54'),
(988,'track','10j7ck4nsi8a8kc0lui0ivu5dgio0sti','Openvoips Technologies','OV500','122.169.33.222','122.169.33.222','/pbx/reports/api_livecall?action=search&call_flow=&max_rows=500&_=1759503110238','https://app.24ithub.com/pbx/reports/livecalls','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','reports/api_livecall','2025-10-03 14:59:02'),
(989,'track','10j7ck4nsi8a8kc0lui0ivu5dgio0sti','Openvoips Technologies','OV500','122.169.33.222','122.169.33.222','/pbx/reports/api_livecall?action=search&call_flow=&max_rows=500&_=1759503110239','https://app.24ithub.com/pbx/reports/livecalls','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','reports/api_livecall','2025-10-03 14:59:05'),
(990,'track','10j7ck4nsi8a8kc0lui0ivu5dgio0sti','Openvoips Technologies','OV500','122.169.33.222','122.169.33.222','/pbx/reports/api_livecall?action=search&call_flow=&max_rows=500&_=1759503110240','https://app.24ithub.com/pbx/reports/livecalls','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','reports/api_livecall','2025-10-03 14:59:11'),
(991,'track','10j7ck4nsi8a8kc0lui0ivu5dgio0sti','Openvoips Technologies','OV500','122.169.33.222','122.169.33.222','/pbx/reports/api_livecall?action=search&call_flow=&max_rows=500&_=1759503110241','https://app.24ithub.com/pbx/reports/livecalls','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','reports/api_livecall','2025-10-03 14:59:15'),
(992,'track','10j7ck4nsi8a8kc0lui0ivu5dgio0sti','Openvoips Technologies','OV500','122.169.33.222','122.169.33.222','/pbx/reports/api_livecall?action=search&call_flow=&max_rows=500&_=1759503110243','https://app.24ithub.com/pbx/reports/livecalls','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','reports/api_livecall','2025-10-03 14:59:26'),
(993,'track','10j7ck4nsi8a8kc0lui0ivu5dgio0sti','Openvoips Technologies','OV500','122.169.33.222','122.169.33.222','/pbx/reports/api_livecall?action=search&call_flow=&max_rows=500&_=1759503110242','https://app.24ithub.com/pbx/reports/livecalls','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','reports/api_livecall','2025-10-03 14:59:27'),
(994,'track','10j7ck4nsi8a8kc0lui0ivu5dgio0sti','Openvoips Technologies','OV500','122.169.33.222','122.169.33.222','/pbx/reports/api_livecall?action=search&call_flow=&max_rows=500&_=1759503110244','https://app.24ithub.com/pbx/reports/livecalls','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','reports/api_livecall','2025-10-03 14:59:30'),
(995,'track','10j7ck4nsi8a8kc0lui0ivu5dgio0sti','Openvoips Technologies','OV500','122.169.33.222','122.169.33.222','/pbx/reports/api_livecall?action=search&call_flow=&max_rows=500&_=1759503110245','https://app.24ithub.com/pbx/reports/livecalls','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','reports/api_livecall','2025-10-03 14:59:38'),
(996,'track','10j7ck4nsi8a8kc0lui0ivu5dgio0sti','Openvoips Technologies','OV500','122.169.33.222','122.169.33.222','/pbx/reports/api_livecall?action=search&call_flow=&max_rows=500&_=1759503110246','https://app.24ithub.com/pbx/reports/livecalls','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','reports/api_livecall','2025-10-03 14:59:40'),
(997,'track','10j7ck4nsi8a8kc0lui0ivu5dgio0sti','Openvoips Technologies','OV500','122.169.33.222','122.169.33.222','/pbx/reports/api_livecall?action=search&call_flow=&max_rows=500&_=1759503110247','https://app.24ithub.com/pbx/reports/livecalls','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','reports/api_livecall','2025-10-03 14:59:55'),
(998,'track','10j7ck4nsi8a8kc0lui0ivu5dgio0sti','Openvoips Technologies','OV500','122.169.33.222','122.169.33.222','/pbx/reports/api_livecall?action=search&call_flow=&max_rows=500&_=1759503110248','https://app.24ithub.com/pbx/reports/livecalls','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','reports/api_livecall','2025-10-03 14:59:57'),
(999,'track','10j7ck4nsi8a8kc0lui0ivu5dgio0sti','Openvoips Technologies','OV500','122.169.33.222','122.169.33.222','/pbx/reports/api_livecall?action=search&call_flow=&max_rows=500&_=1759503110249','https://app.24ithub.com/pbx/reports/livecalls','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','reports/api_livecall','2025-10-03 14:59:58'),
(1000,'track','10j7ck4nsi8a8kc0lui0ivu5dgio0sti','Openvoips Technologies','OV500','122.169.33.222','122.169.33.222','/pbx/reports/api_livecall?action=search&call_flow=&max_rows=500&_=1759503110250','https://app.24ithub.com/pbx/reports/livecalls','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','reports/api_livecall','2025-10-03 15:00:01'),
(1001,'track','10j7ck4nsi8a8kc0lui0ivu5dgio0sti','Openvoips Technologies','OV500','122.169.33.222','122.169.33.222','/pbx/reports/api_livecall?action=search&call_flow=&max_rows=500&_=1759503110251','https://app.24ithub.com/pbx/reports/livecalls','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','reports/api_livecall','2025-10-03 15:00:07'),
(1002,'track','10j7ck4nsi8a8kc0lui0ivu5dgio0sti','Openvoips Technologies','OV500','122.169.33.222','122.169.33.222','/pbx/reports/api_livecall?action=search&call_flow=&max_rows=500&_=1759503110252','https://app.24ithub.com/pbx/reports/livecalls','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','reports/api_livecall','2025-10-03 15:00:11'),
(1003,'track','10j7ck4nsi8a8kc0lui0ivu5dgio0sti','Openvoips Technologies','OV500','122.169.33.222','122.169.33.222','/pbx/reports/api_livecall?action=search&call_flow=&max_rows=500&_=1759503110253','https://app.24ithub.com/pbx/reports/livecalls','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','reports/api_livecall','2025-10-03 15:00:16'),
(1004,'track','10j7ck4nsi8a8kc0lui0ivu5dgio0sti','Openvoips Technologies','OV500','122.169.33.222','122.169.33.222','/pbx/reports/api_livecall?action=search&call_flow=&max_rows=500&_=1759503110254','https://app.24ithub.com/pbx/reports/livecalls','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','reports/api_livecall','2025-10-03 15:00:22'),
(1005,'track','10j7ck4nsi8a8kc0lui0ivu5dgio0sti','Openvoips Technologies','OV500','122.169.33.222','122.169.33.222','/pbx/reports/api_livecall?action=search&call_flow=&max_rows=500&_=1759503110255','https://app.24ithub.com/pbx/reports/livecalls','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','reports/api_livecall','2025-10-03 15:00:26'),
(1006,'track','10j7ck4nsi8a8kc0lui0ivu5dgio0sti','Openvoips Technologies','OV500','122.169.33.222','122.169.33.222','/pbx/reports/api_livecall?action=search&call_flow=&max_rows=500&_=1759503110256','https://app.24ithub.com/pbx/reports/livecalls','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','reports/api_livecall','2025-10-03 15:00:31'),
(1007,'track','10j7ck4nsi8a8kc0lui0ivu5dgio0sti','Openvoips Technologies','OV500','122.169.33.222','122.169.33.222','/pbx/reports/api_livecall?action=search&call_flow=&max_rows=500&_=1759503110257','https://app.24ithub.com/pbx/reports/livecalls','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','reports/api_livecall','2025-10-03 15:00:36'),
(1008,'track','10j7ck4nsi8a8kc0lui0ivu5dgio0sti','Openvoips Technologies','OV500','122.169.33.222','122.169.33.222','/pbx/reports/api_livecall?action=search&call_flow=&max_rows=500&_=1759503110258','https://app.24ithub.com/pbx/reports/livecalls','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','reports/api_livecall','2025-10-03 15:00:42'),
(1009,'track','10j7ck4nsi8a8kc0lui0ivu5dgio0sti','Openvoips Technologies','OV500','122.169.33.222','122.169.33.222','/pbx/reports/api_livecall?action=search&call_flow=&max_rows=500&_=1759503110259','https://app.24ithub.com/pbx/reports/livecalls','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','reports/api_livecall','2025-10-03 15:00:46'),
(1010,'track','10j7ck4nsi8a8kc0lui0ivu5dgio0sti','Openvoips Technologies','OV500','122.169.33.222','122.169.33.222','/pbx/reports/api_livecall?action=search&call_flow=&max_rows=500&_=1759503110260','https://app.24ithub.com/pbx/reports/livecalls','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','reports/api_livecall','2025-10-03 15:00:53'),
(1011,'track','10j7ck4nsi8a8kc0lui0ivu5dgio0sti','Openvoips Technologies','OV500','122.169.33.222','122.169.33.222','/pbx/reports/api_livecall?action=search&call_flow=&max_rows=500&_=1759503110261','https://app.24ithub.com/pbx/reports/livecalls','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','reports/api_livecall','2025-10-03 15:00:57'),
(1012,'track','10j7ck4nsi8a8kc0lui0ivu5dgio0sti','Openvoips Technologies','OV500','122.169.33.222','122.169.33.222','/pbx/reports/api_livecall?action=search&call_flow=&max_rows=500&_=1759503110262','https://app.24ithub.com/pbx/reports/livecalls','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','reports/api_livecall','2025-10-03 15:01:02'),
(1013,'track','10j7ck4nsi8a8kc0lui0ivu5dgio0sti','Openvoips Technologies','OV500','122.169.33.222','122.169.33.222','/pbx/reports/api_livecall?action=search&call_flow=&max_rows=500&_=1759503110263','https://app.24ithub.com/pbx/reports/livecalls','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','reports/api_livecall','2025-10-03 15:01:07'),
(1014,'track','10j7ck4nsi8a8kc0lui0ivu5dgio0sti','Openvoips Technologies','OV500','122.169.33.222','122.169.33.222','/pbx/reports/api_livecall?action=search&call_flow=&max_rows=500&_=1759503110264','https://app.24ithub.com/pbx/reports/livecalls','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','reports/api_livecall','2025-10-03 15:01:12'),
(1015,'track','10j7ck4nsi8a8kc0lui0ivu5dgio0sti','Openvoips Technologies','OV500','122.169.33.222','122.169.33.222','/pbx/reports/api_livecall?action=search&call_flow=&max_rows=500&_=1759503110265','https://app.24ithub.com/pbx/reports/livecalls','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','reports/api_livecall','2025-10-03 15:01:17'),
(1016,'track','10j7ck4nsi8a8kc0lui0ivu5dgio0sti','Openvoips Technologies','OV500','122.169.33.222','122.169.33.222','/pbx/reports/api_livecall?action=search&call_flow=&max_rows=500&_=1759503110266','https://app.24ithub.com/pbx/reports/livecalls','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','reports/api_livecall','2025-10-03 15:01:23'),
(1017,'track','10j7ck4nsi8a8kc0lui0ivu5dgio0sti','Openvoips Technologies','OV500','122.169.33.222','122.169.33.222','/pbx/reports/api_livecall?action=search&call_flow=&max_rows=500&_=1759503110267','https://app.24ithub.com/pbx/reports/livecalls','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','reports/api_livecall','2025-10-03 15:01:27'),
(1018,'track','10j7ck4nsi8a8kc0lui0ivu5dgio0sti','Openvoips Technologies','OV500','122.169.33.222','122.169.33.222','/pbx/reports/api_livecall?action=search&call_flow=&max_rows=500&_=1759503110268','https://app.24ithub.com/pbx/reports/livecalls','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','reports/api_livecall','2025-10-03 15:01:33'),
(1019,'track','10j7ck4nsi8a8kc0lui0ivu5dgio0sti','Openvoips Technologies','OV500','122.169.33.222','122.169.33.222','/pbx/reports/api_livecall?action=search&call_flow=&max_rows=500&_=1759503110269','https://app.24ithub.com/pbx/reports/livecalls','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','reports/api_livecall','2025-10-03 15:01:37'),
(1020,'track','10j7ck4nsi8a8kc0lui0ivu5dgio0sti','Openvoips Technologies','OV500','122.169.33.222','122.169.33.222','/pbx/reports/api_livecall?action=search&call_flow=&max_rows=500&_=1759503110270','https://app.24ithub.com/pbx/reports/livecalls','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','reports/api_livecall','2025-10-03 15:01:43'),
(1021,'track','10j7ck4nsi8a8kc0lui0ivu5dgio0sti','Openvoips Technologies','OV500','122.169.33.222','122.169.33.222','/pbx/reports/api_livecall?action=search&call_flow=&max_rows=500&_=1759503110271','https://app.24ithub.com/pbx/reports/livecalls','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','reports/api_livecall','2025-10-03 15:01:48'),
(1022,'track','10j7ck4nsi8a8kc0lui0ivu5dgio0sti','Openvoips Technologies','OV500','122.169.33.222','122.169.33.222','/pbx/reports/api_livecall?action=search&call_flow=&max_rows=500&_=1759503110272','https://app.24ithub.com/pbx/reports/livecalls','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','reports/api_livecall','2025-10-03 15:01:53'),
(1023,'track','10j7ck4nsi8a8kc0lui0ivu5dgio0sti','Openvoips Technologies','OV500','122.169.33.222','122.169.33.222','/pbx/reports/api_livecall?action=search&call_flow=&max_rows=500&_=1759503110273','https://app.24ithub.com/pbx/reports/livecalls','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','reports/api_livecall','2025-10-03 15:01:58'),
(1024,'track','10j7ck4nsi8a8kc0lui0ivu5dgio0sti','Openvoips Technologies','OV500','122.169.33.222','122.169.33.222','/pbx/reports/api_livecall?action=search&call_flow=&max_rows=500&_=1759503110274','https://app.24ithub.com/pbx/reports/livecalls','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','reports/api_livecall','2025-10-03 15:02:03'),
(1025,'track','10j7ck4nsi8a8kc0lui0ivu5dgio0sti','Openvoips Technologies','OV500','122.169.33.222','122.169.33.222','/pbx/reports/api_livecall?action=search&call_flow=&max_rows=500&_=1759503110275','https://app.24ithub.com/pbx/reports/livecalls','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','reports/api_livecall','2025-10-03 15:02:08'),
(1026,'track','10j7ck4nsi8a8kc0lui0ivu5dgio0sti','Openvoips Technologies','OV500','122.169.33.222','122.169.33.222','/pbx/reports/api_livecall?action=search&call_flow=&max_rows=500&_=1759503110276','https://app.24ithub.com/pbx/reports/livecalls','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','reports/api_livecall','2025-10-03 15:02:13'),
(1027,'track','10j7ck4nsi8a8kc0lui0ivu5dgio0sti','Openvoips Technologies','OV500','122.169.33.222','122.169.33.222','/pbx/reports/api_livecall?action=search&call_flow=&max_rows=500&_=1759503110277','https://app.24ithub.com/pbx/reports/livecalls','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','reports/api_livecall','2025-10-03 15:02:18'),
(1028,'track','10j7ck4nsi8a8kc0lui0ivu5dgio0sti','Openvoips Technologies','OV500','122.169.33.222','122.169.33.222','/pbx/reports/api_livecall?action=search&call_flow=&max_rows=500&_=1759503110278','https://app.24ithub.com/pbx/reports/livecalls','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','reports/api_livecall','2025-10-03 15:02:24'),
(1029,'track','10j7ck4nsi8a8kc0lui0ivu5dgio0sti','Openvoips Technologies','OV500','122.169.33.222','122.169.33.222','/pbx/reports/api_livecall?action=search&call_flow=&max_rows=500&_=1759503110279','https://app.24ithub.com/pbx/reports/livecalls','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','reports/api_livecall','2025-10-03 15:02:28'),
(1030,'track','10j7ck4nsi8a8kc0lui0ivu5dgio0sti','Openvoips Technologies','OV500','122.169.33.222','122.169.33.222','/pbx/reports/api_livecall?action=search&call_flow=&max_rows=500&_=1759503110280','https://app.24ithub.com/pbx/reports/livecalls','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','reports/api_livecall','2025-10-03 15:02:34'),
(1031,'track','10j7ck4nsi8a8kc0lui0ivu5dgio0sti','Openvoips Technologies','OV500','122.169.33.222','122.169.33.222','/pbx/reports/api_livecall?action=search&call_flow=&max_rows=500&_=1759503110281','https://app.24ithub.com/pbx/reports/livecalls','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','reports/api_livecall','2025-10-03 15:02:38'),
(1032,'track','10j7ck4nsi8a8kc0lui0ivu5dgio0sti','Openvoips Technologies','OV500','122.169.33.222','122.169.33.222','/pbx/reports/api_livecall?action=search&call_flow=&max_rows=500&_=1759503110282','https://app.24ithub.com/pbx/reports/livecalls','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','reports/api_livecall','2025-10-03 15:02:44'),
(1033,'track','10j7ck4nsi8a8kc0lui0ivu5dgio0sti','Openvoips Technologies','OV500','122.169.33.222','122.169.33.222','/pbx/reports/api_livecall?action=search&call_flow=&max_rows=500&_=1759503110283','https://app.24ithub.com/pbx/reports/livecalls','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','reports/api_livecall','2025-10-03 15:02:48'),
(1034,'track','10j7ck4nsi8a8kc0lui0ivu5dgio0sti','Openvoips Technologies','OV500','122.169.33.222','122.169.33.222','/pbx/reports/api_livecall?action=search&call_flow=&max_rows=500&_=1759503110284','https://app.24ithub.com/pbx/reports/livecalls','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','reports/api_livecall','2025-10-03 15:02:54'),
(1035,'track','10j7ck4nsi8a8kc0lui0ivu5dgio0sti','Openvoips Technologies','OV500','122.169.33.222','122.169.33.222','/pbx/reports/api_livecall?action=search&call_flow=&max_rows=500&_=1759503110285','https://app.24ithub.com/pbx/reports/livecalls','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','reports/api_livecall','2025-10-03 15:02:59'),
(1036,'track','10j7ck4nsi8a8kc0lui0ivu5dgio0sti','Openvoips Technologies','OV500','122.169.33.222','122.169.33.222','/pbx/reports/api_livecall?action=search&call_flow=&max_rows=500&_=1759503110286','https://app.24ithub.com/pbx/reports/livecalls','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','reports/api_livecall','2025-10-03 15:03:04'),
(1037,'track','10j7ck4nsi8a8kc0lui0ivu5dgio0sti','Openvoips Technologies','OV500','122.169.33.222','122.169.33.222','/pbx/reports/api_livecall?action=search&call_flow=&max_rows=500&_=1759503110287','https://app.24ithub.com/pbx/reports/livecalls','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','reports/api_livecall','2025-10-03 15:03:09'),
(1038,'track','10j7ck4nsi8a8kc0lui0ivu5dgio0sti','Openvoips Technologies','OV500','122.169.33.222','122.169.33.222','/pbx/reports/api_livecall?action=search&call_flow=&max_rows=500&_=1759503110288','https://app.24ithub.com/pbx/reports/livecalls','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','reports/api_livecall','2025-10-03 15:03:15'),
(1039,'track','10j7ck4nsi8a8kc0lui0ivu5dgio0sti','Openvoips Technologies','OV500','122.169.33.222','122.169.33.222','/pbx/reports/api_livecall?action=search&call_flow=&max_rows=500&_=1759503110289','https://app.24ithub.com/pbx/reports/livecalls','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','reports/api_livecall','2025-10-03 15:03:19'),
(1040,'track','10j7ck4nsi8a8kc0lui0ivu5dgio0sti','Openvoips Technologies','OV500','122.169.33.222','122.169.33.222','/pbx/reports/api_livecall?action=search&call_flow=&max_rows=500&_=1759503110290','https://app.24ithub.com/pbx/reports/livecalls','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','reports/api_livecall','2025-10-03 15:03:24'),
(1041,'track','10j7ck4nsi8a8kc0lui0ivu5dgio0sti','Openvoips Technologies','OV500','122.169.33.222','122.169.33.222','/pbx/reports/api_livecall?action=search&call_flow=&max_rows=500&_=1759503110291','https://app.24ithub.com/pbx/reports/livecalls','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','reports/api_livecall','2025-10-03 15:03:30'),
(1042,'track','10j7ck4nsi8a8kc0lui0ivu5dgio0sti','Openvoips Technologies','OV500','122.169.33.222','122.169.33.222','/pbx/reports/api_livecall?action=search&call_flow=&max_rows=500&_=1759503110292','https://app.24ithub.com/pbx/reports/livecalls','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','reports/api_livecall','2025-10-03 15:03:35'),
(1043,'track','10j7ck4nsi8a8kc0lui0ivu5dgio0sti','Openvoips Technologies','OV500','122.169.33.222','122.169.33.222','/pbx/reports/api_livecall?action=search&call_flow=&max_rows=500&_=1759503110293','https://app.24ithub.com/pbx/reports/livecalls','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','reports/api_livecall','2025-10-03 15:03:40'),
(1044,'track','10j7ck4nsi8a8kc0lui0ivu5dgio0sti','Openvoips Technologies','OV500','122.169.33.222','122.169.33.222','/pbx/reports/api_livecall?action=search&call_flow=&max_rows=500&_=1759503110294','https://app.24ithub.com/pbx/reports/livecalls','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','reports/api_livecall','2025-10-03 15:03:45'),
(1045,'track','10j7ck4nsi8a8kc0lui0ivu5dgio0sti','Openvoips Technologies','OV500','122.169.33.222','122.169.33.222','/pbx/reports/api_livecall?action=search&call_flow=&max_rows=500&_=1759503110295','https://app.24ithub.com/pbx/reports/livecalls','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','reports/api_livecall','2025-10-03 15:03:50'),
(1046,'track','10j7ck4nsi8a8kc0lui0ivu5dgio0sti','Openvoips Technologies','OV500','122.169.33.222','122.169.33.222','/pbx/reports/api_livecall?action=search&call_flow=&max_rows=500&_=1759503110296','https://app.24ithub.com/pbx/reports/livecalls','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','reports/api_livecall','2025-10-03 15:03:55'),
(1047,'track','10j7ck4nsi8a8kc0lui0ivu5dgio0sti','Openvoips Technologies','OV500','122.169.33.222','122.169.33.222','/pbx/reports/api_livecall?action=search&call_flow=&max_rows=500&_=1759503110297','https://app.24ithub.com/pbx/reports/livecalls','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','reports/api_livecall','2025-10-03 15:04:00'),
(1048,'track','10j7ck4nsi8a8kc0lui0ivu5dgio0sti','Openvoips Technologies','OV500','122.169.33.222','122.169.33.222','/pbx/reports/api_livecall?action=search&call_flow=&max_rows=500&_=1759503110298','https://app.24ithub.com/pbx/reports/livecalls','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','reports/api_livecall','2025-10-03 15:04:05'),
(1049,'track','10j7ck4nsi8a8kc0lui0ivu5dgio0sti','Openvoips Technologies','OV500','122.169.33.222','122.169.33.222','/pbx/reports/api_livecall?action=search&call_flow=&max_rows=500&_=1759503110299','https://app.24ithub.com/pbx/reports/livecalls','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','reports/api_livecall','2025-10-03 15:04:10'),
(1050,'track','10j7ck4nsi8a8kc0lui0ivu5dgio0sti','Openvoips Technologies','OV500','122.169.33.222','122.169.33.222','/pbx/reports/api_livecall?action=search&call_flow=&max_rows=500&_=1759503110300','https://app.24ithub.com/pbx/reports/livecalls','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','reports/api_livecall','2025-10-03 15:04:15'),
(1051,'track','10j7ck4nsi8a8kc0lui0ivu5dgio0sti','Openvoips Technologies','OV500','122.169.33.222','122.169.33.222','/pbx/reports/api_livecall?action=search&call_flow=&max_rows=500&_=1759503110301','https://app.24ithub.com/pbx/reports/livecalls','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','reports/api_livecall','2025-10-03 15:04:21'),
(1052,'track','10j7ck4nsi8a8kc0lui0ivu5dgio0sti','Openvoips Technologies','OV500','122.169.33.222','122.169.33.222','/pbx/reports/api_livecall?action=search&call_flow=&max_rows=500&_=1759503110302','https://app.24ithub.com/pbx/reports/livecalls','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','reports/api_livecall','2025-10-03 15:04:25'),
(1053,'track','10j7ck4nsi8a8kc0lui0ivu5dgio0sti','Openvoips Technologies','OV500','122.169.33.222','122.169.33.222','/pbx/reports/api_livecall?action=search&call_flow=&max_rows=500&_=1759503110303','https://app.24ithub.com/pbx/reports/livecalls','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','reports/api_livecall','2025-10-03 15:04:31'),
(1054,'track','10j7ck4nsi8a8kc0lui0ivu5dgio0sti','Openvoips Technologies','OV500','122.169.33.222','122.169.33.222','/pbx/reports/api_livecall?action=search&call_flow=&max_rows=500&_=1759503110304','https://app.24ithub.com/pbx/reports/livecalls','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','reports/api_livecall','2025-10-03 15:04:35'),
(1055,'track','10j7ck4nsi8a8kc0lui0ivu5dgio0sti','Openvoips Technologies','OV500','122.169.33.222','122.169.33.222','/pbx/reports/api_livecall?action=search&call_flow=&max_rows=500&_=1759503110305','https://app.24ithub.com/pbx/reports/livecalls','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','reports/api_livecall','2025-10-03 15:04:40'),
(1056,'track','10j7ck4nsi8a8kc0lui0ivu5dgio0sti','Openvoips Technologies','OV500','122.169.33.222','122.169.33.222','/pbx/reports/api_livecall?action=search&call_flow=&max_rows=500&_=1759503110306','https://app.24ithub.com/pbx/reports/livecalls','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','reports/api_livecall','2025-10-03 15:04:46'),
(1057,'track','10j7ck4nsi8a8kc0lui0ivu5dgio0sti','Openvoips Technologies','OV500','122.169.33.222','122.169.33.222','/pbx/reports/api_livecall?action=search&call_flow=&max_rows=500&_=1759503110307','https://app.24ithub.com/pbx/reports/livecalls','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','reports/api_livecall','2025-10-03 15:04:51'),
(1058,'track','10j7ck4nsi8a8kc0lui0ivu5dgio0sti','Openvoips Technologies','OV500','122.169.33.222','122.169.33.222','/pbx/reports/api_livecall?action=search&call_flow=&max_rows=500&_=1759503110308','https://app.24ithub.com/pbx/reports/livecalls','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','reports/api_livecall','2025-10-03 15:04:56'),
(1059,'track','10j7ck4nsi8a8kc0lui0ivu5dgio0sti','Openvoips Technologies','OV500','122.169.33.222','122.169.33.222','/pbx/reports/api_livecall?action=search&call_flow=&max_rows=500&_=1759503110309','https://app.24ithub.com/pbx/reports/livecalls','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','reports/api_livecall','2025-10-03 15:05:01'),
(1060,'track','10j7ck4nsi8a8kc0lui0ivu5dgio0sti','Openvoips Technologies','OV500','122.169.33.222','122.169.33.222','/pbx/reports/api_livecall?action=search&call_flow=&max_rows=500&_=1759503110310','https://app.24ithub.com/pbx/reports/livecalls','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','reports/api_livecall','2025-10-03 15:05:06'),
(1061,'track','10j7ck4nsi8a8kc0lui0ivu5dgio0sti','Openvoips Technologies','OV500','122.169.33.222','122.169.33.222','/pbx/reports/api_livecall?action=search&call_flow=&max_rows=500&_=1759503110311','https://app.24ithub.com/pbx/reports/livecalls','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','reports/api_livecall','2025-10-03 15:05:11'),
(1062,'track','10j7ck4nsi8a8kc0lui0ivu5dgio0sti','Openvoips Technologies','OV500','122.169.33.222','122.169.33.222','/pbx/reports/api_livecall?action=search&call_flow=&max_rows=500&_=1759503110312','https://app.24ithub.com/pbx/reports/livecalls','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','reports/api_livecall','2025-10-03 15:05:16'),
(1063,'track','10j7ck4nsi8a8kc0lui0ivu5dgio0sti','Openvoips Technologies','OV500','122.169.33.222','122.169.33.222','/pbx/reports/api_livecall?action=search&call_flow=&max_rows=500&_=1759503110313','https://app.24ithub.com/pbx/reports/livecalls','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','reports/api_livecall','2025-10-03 15:05:22'),
(1064,'track','10j7ck4nsi8a8kc0lui0ivu5dgio0sti','Openvoips Technologies','OV500','122.169.33.222','122.169.33.222','/pbx/reports/api_livecall?action=search&call_flow=&max_rows=500&_=1759503110314','https://app.24ithub.com/pbx/reports/livecalls','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','reports/api_livecall','2025-10-03 15:05:26'),
(1065,'track','10j7ck4nsi8a8kc0lui0ivu5dgio0sti','Openvoips Technologies','OV500','122.169.33.222','122.169.33.222','/pbx/reports/api_livecall?action=search&call_flow=&max_rows=500&_=1759503110315','https://app.24ithub.com/pbx/reports/livecalls','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','reports/api_livecall','2025-10-03 15:05:32'),
(1066,'track','10j7ck4nsi8a8kc0lui0ivu5dgio0sti','Openvoips Technologies','OV500','122.169.33.222','122.169.33.222','/pbx/reports/api_livecall?action=search&call_flow=&max_rows=500&_=1759503110316','https://app.24ithub.com/pbx/reports/livecalls','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','reports/api_livecall','2025-10-03 15:05:37'),
(1067,'track','10j7ck4nsi8a8kc0lui0ivu5dgio0sti','Openvoips Technologies','OV500','122.169.33.222','122.169.33.222','/pbx/reports/api_livecall?action=search&call_flow=&max_rows=500&_=1759503110317','https://app.24ithub.com/pbx/reports/livecalls','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','reports/api_livecall','2025-10-03 15:05:42'),
(1068,'track','10j7ck4nsi8a8kc0lui0ivu5dgio0sti','Openvoips Technologies','OV500','122.169.33.222','122.169.33.222','/pbx/reports/api_livecall?action=search&call_flow=&max_rows=500&_=1759503110318','https://app.24ithub.com/pbx/reports/livecalls','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','reports/api_livecall','2025-10-03 15:05:47'),
(1069,'track','10j7ck4nsi8a8kc0lui0ivu5dgio0sti','Openvoips Technologies','OV500','122.169.33.222','122.169.33.222','/pbx/reports/api_livecall?action=search&call_flow=&max_rows=500&_=1759503110319','https://app.24ithub.com/pbx/reports/livecalls','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','reports/api_livecall','2025-10-03 15:05:52'),
(1070,'track','10j7ck4nsi8a8kc0lui0ivu5dgio0sti','Openvoips Technologies','OV500','122.169.33.222','122.169.33.222','/pbx/reports/api_livecall?action=search&call_flow=&max_rows=500&_=1759503110320','https://app.24ithub.com/pbx/reports/livecalls','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','reports/api_livecall','2025-10-03 15:05:57'),
(1071,'track','10j7ck4nsi8a8kc0lui0ivu5dgio0sti','Openvoips Technologies','OV500','122.169.33.222','122.169.33.222','/pbx/reports/api_livecall?action=search&call_flow=&max_rows=500&_=1759503110321','https://app.24ithub.com/pbx/reports/livecalls','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','reports/api_livecall','2025-10-03 15:06:02'),
(1072,'track','10j7ck4nsi8a8kc0lui0ivu5dgio0sti','Openvoips Technologies','OV500','122.169.33.222','122.169.33.222','/pbx/reports/api_livecall?action=search&call_flow=&max_rows=500&_=1759503110322','https://app.24ithub.com/pbx/reports/livecalls','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','reports/api_livecall','2025-10-03 15:06:07'),
(1073,'track','10j7ck4nsi8a8kc0lui0ivu5dgio0sti','Openvoips Technologies','OV500','122.169.33.222','122.169.33.222','/pbx/reports/api_livecall?action=search&call_flow=&max_rows=500&_=1759503110323','https://app.24ithub.com/pbx/reports/livecalls','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','reports/api_livecall','2025-10-03 15:06:13'),
(1074,'track','10j7ck4nsi8a8kc0lui0ivu5dgio0sti','Openvoips Technologies','OV500','122.169.33.222','122.169.33.222','/pbx/reports/api_livecall?action=search&call_flow=&max_rows=500&_=1759503110324','https://app.24ithub.com/pbx/reports/livecalls','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','reports/api_livecall','2025-10-03 15:06:17'),
(1075,'track','10j7ck4nsi8a8kc0lui0ivu5dgio0sti','Openvoips Technologies','OV500','122.169.33.222','122.169.33.222','/pbx/reports/api_livecall?action=search&call_flow=&max_rows=500&_=1759503110325','https://app.24ithub.com/pbx/reports/livecalls','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','reports/api_livecall','2025-10-03 15:06:23'),
(1076,'track','10j7ck4nsi8a8kc0lui0ivu5dgio0sti','Openvoips Technologies','OV500','122.169.33.222','122.169.33.222','/pbx/reports/api_livecall?action=search&call_flow=&max_rows=500&_=1759503110326','https://app.24ithub.com/pbx/reports/livecalls','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','reports/api_livecall','2025-10-03 15:06:28'),
(1077,'track','10j7ck4nsi8a8kc0lui0ivu5dgio0sti','Openvoips Technologies','OV500','122.169.33.222','122.169.33.222','/pbx/reports/api_livecall?action=search&call_flow=&max_rows=500&_=1759503110327','https://app.24ithub.com/pbx/reports/livecalls','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','reports/api_livecall','2025-10-03 15:06:33'),
(1078,'track','10j7ck4nsi8a8kc0lui0ivu5dgio0sti','Openvoips Technologies','OV500','122.169.33.222','122.169.33.222','/pbx/reports/api_livecall?action=search&call_flow=&max_rows=500&_=1759503110328','https://app.24ithub.com/pbx/reports/livecalls','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','reports/api_livecall','2025-10-03 15:06:38'),
(1079,'track','10j7ck4nsi8a8kc0lui0ivu5dgio0sti','Openvoips Technologies','OV500','122.169.33.222','122.169.33.222','/pbx/reports/api_livecall?action=search&call_flow=&max_rows=500&_=1759503110329','https://app.24ithub.com/pbx/reports/livecalls','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','reports/api_livecall','2025-10-03 15:06:43'),
(1080,'track','10j7ck4nsi8a8kc0lui0ivu5dgio0sti','Openvoips Technologies','OV500','122.169.33.222','122.169.33.222','/pbx/reports/api_livecall?action=search&call_flow=&max_rows=500&_=1759503110330','https://app.24ithub.com/pbx/reports/livecalls','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','reports/api_livecall','2025-10-03 15:06:48'),
(1081,'track','10j7ck4nsi8a8kc0lui0ivu5dgio0sti','Openvoips Technologies','OV500','122.169.33.222','122.169.33.222','/pbx/reports/api_livecall?action=search&call_flow=&max_rows=500&_=1759503110331','https://app.24ithub.com/pbx/reports/livecalls','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','reports/api_livecall','2025-10-03 15:06:53'),
(1082,'track','10j7ck4nsi8a8kc0lui0ivu5dgio0sti','Openvoips Technologies','OV500','122.169.33.222','122.169.33.222','/pbx/reports/api_livecall?action=search&call_flow=&max_rows=500&_=1759503110332','https://app.24ithub.com/pbx/reports/livecalls','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','reports/api_livecall','2025-10-03 15:06:58'),
(1083,'track','10j7ck4nsi8a8kc0lui0ivu5dgio0sti','Openvoips Technologies','OV500','122.169.33.222','122.169.33.222','/pbx/reports/api_livecall?action=search&call_flow=&max_rows=500&_=1759503110333','https://app.24ithub.com/pbx/reports/livecalls','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','reports/api_livecall','2025-10-03 15:07:03'),
(1084,'track','10j7ck4nsi8a8kc0lui0ivu5dgio0sti','Openvoips Technologies','OV500','122.169.33.222','122.169.33.222','/pbx/reports/api_livecall?action=search&call_flow=&max_rows=500&_=1759503110334','https://app.24ithub.com/pbx/reports/livecalls','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','reports/api_livecall','2025-10-03 15:07:08'),
(1085,'track','10j7ck4nsi8a8kc0lui0ivu5dgio0sti','Openvoips Technologies','OV500','122.169.33.222','122.169.33.222','/pbx/reports/api_livecall?action=search&call_flow=&max_rows=500&_=1759503110335','https://app.24ithub.com/pbx/reports/livecalls','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','reports/api_livecall','2025-10-03 15:07:14'),
(1086,'track','10j7ck4nsi8a8kc0lui0ivu5dgio0sti','Openvoips Technologies','OV500','122.169.33.222','122.169.33.222','/pbx/reports/api_livecall?action=search&call_flow=&max_rows=500&_=1759503110336','https://app.24ithub.com/pbx/reports/livecalls','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','reports/api_livecall','2025-10-03 15:07:18'),
(1087,'track','10j7ck4nsi8a8kc0lui0ivu5dgio0sti','Openvoips Technologies','OV500','122.169.33.222','122.169.33.222','/pbx/reports/api_livecall?action=search&call_flow=&max_rows=500&_=1759503110337','https://app.24ithub.com/pbx/reports/livecalls','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','reports/api_livecall','2025-10-03 15:07:23'),
(1088,'track','10j7ck4nsi8a8kc0lui0ivu5dgio0sti','Openvoips Technologies','OV500','122.169.33.222','122.169.33.222','/pbx/reports/api_livecall?action=search&call_flow=&max_rows=500&_=1759503110338','https://app.24ithub.com/pbx/reports/livecalls','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','reports/api_livecall','2025-10-03 15:07:29'),
(1089,'track','10j7ck4nsi8a8kc0lui0ivu5dgio0sti','Openvoips Technologies','OV500','122.169.33.222','122.169.33.222','/pbx/reports/api_livecall?action=search&call_flow=&max_rows=500&_=1759503110339','https://app.24ithub.com/pbx/reports/livecalls','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','reports/api_livecall','2025-10-03 15:07:34'),
(1090,'track','pk62vtfprq60qhpvmoibjhr76ndpdfrc','','','34.79.78.134','34.79.78.134','/','','python-requests/2.32.5','login/index','2025-10-03 15:07:38'),
(1091,'track','10j7ck4nsi8a8kc0lui0ivu5dgio0sti','Openvoips Technologies','OV500','122.169.33.222','122.169.33.222','/pbx/reports/api_livecall?action=search&call_flow=&max_rows=500&_=1759503110340','https://app.24ithub.com/pbx/reports/livecalls','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','reports/api_livecall','2025-10-03 15:07:39'),
(1092,'track','10j7ck4nsi8a8kc0lui0ivu5dgio0sti','Openvoips Technologies','OV500','122.169.33.222','122.169.33.222','/pbx/reports/api_livecall?action=search&call_flow=&max_rows=500&_=1759503110341','https://app.24ithub.com/pbx/reports/livecalls','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','reports/api_livecall','2025-10-03 15:07:44'),
(1093,'track','10j7ck4nsi8a8kc0lui0ivu5dgio0sti','Openvoips Technologies','OV500','122.169.33.222','122.169.33.222','/pbx/reports/api_livecall?action=search&call_flow=&max_rows=500&_=1759503110342','https://app.24ithub.com/pbx/reports/livecalls','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','reports/api_livecall','2025-10-03 15:07:49'),
(1094,'track','10j7ck4nsi8a8kc0lui0ivu5dgio0sti','Openvoips Technologies','OV500','122.169.33.222','122.169.33.222','/pbx/reports/api_livecall?action=search&call_flow=&max_rows=500&_=1759503110343','https://app.24ithub.com/pbx/reports/livecalls','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','reports/api_livecall','2025-10-03 15:07:54'),
(1095,'track','10j7ck4nsi8a8kc0lui0ivu5dgio0sti','Openvoips Technologies','OV500','122.169.33.222','122.169.33.222','/pbx/reports/api_livecall?action=search&call_flow=&max_rows=500&_=1759503110344','https://app.24ithub.com/pbx/reports/livecalls','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','reports/api_livecall','2025-10-03 15:08:00'),
(1096,'track','10j7ck4nsi8a8kc0lui0ivu5dgio0sti','Openvoips Technologies','OV500','122.169.33.222','122.169.33.222','/pbx/reports/api_livecall?action=search&call_flow=&max_rows=500&_=1759503110345','https://app.24ithub.com/pbx/reports/livecalls','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','reports/api_livecall','2025-10-03 15:08:04'),
(1097,'track','10j7ck4nsi8a8kc0lui0ivu5dgio0sti','Openvoips Technologies','OV500','122.169.33.222','122.169.33.222','/pbx/reports/api_livecall?action=search&call_flow=&max_rows=500&_=1759503110346','https://app.24ithub.com/pbx/reports/livecalls','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','reports/api_livecall','2025-10-03 15:08:10'),
(1098,'track','10j7ck4nsi8a8kc0lui0ivu5dgio0sti','Openvoips Technologies','OV500','122.169.33.222','122.169.33.222','/pbx/reports/api_livecall?action=search&call_flow=&max_rows=500&_=1759503110347','https://app.24ithub.com/pbx/reports/livecalls','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','reports/api_livecall','2025-10-03 15:08:14'),
(1099,'track','10j7ck4nsi8a8kc0lui0ivu5dgio0sti','Openvoips Technologies','OV500','122.169.33.222','122.169.33.222','/pbx/reports/api_livecall?action=search&call_flow=&max_rows=500&_=1759503110348','https://app.24ithub.com/pbx/reports/livecalls','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','reports/api_livecall','2025-10-03 15:08:20'),
(1100,'track','10j7ck4nsi8a8kc0lui0ivu5dgio0sti','Openvoips Technologies','OV500','122.169.33.222','122.169.33.222','/pbx/reports/api_livecall?action=search&call_flow=&max_rows=500&_=1759503110349','https://app.24ithub.com/pbx/reports/livecalls','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','reports/api_livecall','2025-10-03 15:08:24'),
(1101,'track','10j7ck4nsi8a8kc0lui0ivu5dgio0sti','Openvoips Technologies','OV500','122.169.33.222','122.169.33.222','/pbx/reports/api_livecall?action=search&call_flow=&max_rows=500&_=1759503110350','https://app.24ithub.com/pbx/reports/livecalls','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','reports/api_livecall','2025-10-03 15:08:30'),
(1102,'track','10j7ck4nsi8a8kc0lui0ivu5dgio0sti','Openvoips Technologies','OV500','122.169.33.222','122.169.33.222','/pbx/reports/api_livecall?action=search&call_flow=&max_rows=500&_=1759503110351','https://app.24ithub.com/pbx/reports/livecalls','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','reports/api_livecall','2025-10-03 15:08:35'),
(1103,'track','10j7ck4nsi8a8kc0lui0ivu5dgio0sti','Openvoips Technologies','OV500','122.169.33.222','122.169.33.222','/pbx/reports/api_livecall?action=search&call_flow=&max_rows=500&_=1759503110352','https://app.24ithub.com/pbx/reports/livecalls','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','reports/api_livecall','2025-10-03 15:08:46'),
(1104,'track','10j7ck4nsi8a8kc0lui0ivu5dgio0sti','Openvoips Technologies','OV500','122.169.33.222','122.169.33.222','/pbx/reports/api_livecall?action=search&call_flow=&max_rows=500&_=1759503110353','https://app.24ithub.com/pbx/reports/livecalls','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','reports/api_livecall','2025-10-03 15:08:46'),
(1105,'track','10j7ck4nsi8a8kc0lui0ivu5dgio0sti','Openvoips Technologies','OV500','122.169.33.222','122.169.33.222','/pbx/reports/api_livecall?action=search&call_flow=&max_rows=500&_=1759503110354','https://app.24ithub.com/pbx/reports/livecalls','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','reports/api_livecall','2025-10-03 15:08:50'),
(1106,'track','10j7ck4nsi8a8kc0lui0ivu5dgio0sti','Openvoips Technologies','OV500','122.169.33.222','122.169.33.222','/pbx/reports/api_livecall?action=search&call_flow=&max_rows=500&_=1759503110355','https://app.24ithub.com/pbx/reports/livecalls','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','reports/api_livecall','2025-10-03 15:08:56'),
(1107,'track','10j7ck4nsi8a8kc0lui0ivu5dgio0sti','Openvoips Technologies','OV500','122.169.33.222','122.169.33.222','/pbx/reports/api_livecall?action=search&call_flow=&max_rows=500&_=1759503110356','https://app.24ithub.com/pbx/reports/livecalls','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','reports/api_livecall','2025-10-03 15:09:01'),
(1108,'track','10j7ck4nsi8a8kc0lui0ivu5dgio0sti','Openvoips Technologies','OV500','122.169.33.222','122.169.33.222','/pbx/reports/api_livecall?action=search&call_flow=&max_rows=500&_=1759503110357','https://app.24ithub.com/pbx/reports/livecalls','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','reports/api_livecall','2025-10-03 15:09:06'),
(1109,'track','10j7ck4nsi8a8kc0lui0ivu5dgio0sti','Openvoips Technologies','OV500','122.169.33.222','122.169.33.222','/pbx/reports/api_livecall?action=search&call_flow=&max_rows=500&_=1759503110358','https://app.24ithub.com/pbx/reports/livecalls','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','reports/api_livecall','2025-10-03 15:09:11'),
(1110,'track','10j7ck4nsi8a8kc0lui0ivu5dgio0sti','Openvoips Technologies','OV500','122.169.33.222','122.169.33.222','/pbx/reports/api_livecall?action=search&call_flow=&max_rows=500&_=1759503110359','https://app.24ithub.com/pbx/reports/livecalls','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','reports/api_livecall','2025-10-03 15:09:16'),
(1111,'track','10j7ck4nsi8a8kc0lui0ivu5dgio0sti','Openvoips Technologies','OV500','122.169.33.222','122.169.33.222','/pbx/reports/api_livecall?action=search&call_flow=&max_rows=500&_=1759503110360','https://app.24ithub.com/pbx/reports/livecalls','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','reports/api_livecall','2025-10-03 15:09:21'),
(1112,'track','10j7ck4nsi8a8kc0lui0ivu5dgio0sti','Openvoips Technologies','OV500','122.169.33.222','122.169.33.222','/pbx/reports/api_livecall?action=search&call_flow=&max_rows=500&_=1759503110361','https://app.24ithub.com/pbx/reports/livecalls','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','reports/api_livecall','2025-10-03 15:09:26'),
(1113,'track','10j7ck4nsi8a8kc0lui0ivu5dgio0sti','Openvoips Technologies','OV500','122.169.33.222','122.169.33.222','/pbx/reports/api_livecall?action=search&call_flow=&max_rows=500&_=1759503110362','https://app.24ithub.com/pbx/reports/livecalls','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','reports/api_livecall','2025-10-03 15:09:32'),
(1114,'track','10j7ck4nsi8a8kc0lui0ivu5dgio0sti','Openvoips Technologies','OV500','122.169.33.222','122.169.33.222','/pbx/reports/api_livecall?action=search&call_flow=&max_rows=500&_=1759503110363','https://app.24ithub.com/pbx/reports/livecalls','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','reports/api_livecall','2025-10-03 15:09:36'),
(1115,'track','10j7ck4nsi8a8kc0lui0ivu5dgio0sti','Openvoips Technologies','OV500','122.169.33.222','122.169.33.222','/pbx/reports/api_livecall?action=search&call_flow=&max_rows=500&_=1759503110364','https://app.24ithub.com/pbx/reports/livecalls','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','reports/api_livecall','2025-10-03 15:09:42'),
(1116,'track','10j7ck4nsi8a8kc0lui0ivu5dgio0sti','Openvoips Technologies','OV500','122.169.33.222','122.169.33.222','/pbx/reports/api_livecall?action=search&call_flow=&max_rows=500&_=1759503110365','https://app.24ithub.com/pbx/reports/livecalls','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','reports/api_livecall','2025-10-03 15:09:47'),
(1117,'track','10j7ck4nsi8a8kc0lui0ivu5dgio0sti','Openvoips Technologies','OV500','122.169.33.222','122.169.33.222','/pbx/reports/api_livecall?action=search&call_flow=&max_rows=500&_=1759503110366','https://app.24ithub.com/pbx/reports/livecalls','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','reports/api_livecall','2025-10-03 15:09:52'),
(1118,'track','10j7ck4nsi8a8kc0lui0ivu5dgio0sti','Openvoips Technologies','OV500','122.169.33.222','122.169.33.222','/pbx/reports/api_livecall?action=search&call_flow=&max_rows=500&_=1759503110367','https://app.24ithub.com/pbx/reports/livecalls','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','reports/api_livecall','2025-10-03 15:09:57'),
(1119,'track','10j7ck4nsi8a8kc0lui0ivu5dgio0sti','Openvoips Technologies','OV500','122.169.33.222','122.169.33.222','/pbx/reports/api_livecall?action=search&call_flow=&max_rows=500&_=1759503110368','https://app.24ithub.com/pbx/reports/livecalls','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','reports/api_livecall','2025-10-03 15:10:02'),
(1120,'track','10j7ck4nsi8a8kc0lui0ivu5dgio0sti','Openvoips Technologies','OV500','122.169.33.222','122.169.33.222','/pbx/reports/api_livecall?action=search&call_flow=&max_rows=500&_=1759503110369','https://app.24ithub.com/pbx/reports/livecalls','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','reports/api_livecall','2025-10-03 15:10:07'),
(1121,'track','10j7ck4nsi8a8kc0lui0ivu5dgio0sti','Openvoips Technologies','OV500','122.169.33.222','122.169.33.222','/pbx/reports/api_livecall?action=search&call_flow=&max_rows=500&_=1759503110370','https://app.24ithub.com/pbx/reports/livecalls','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','reports/api_livecall','2025-10-03 15:10:12'),
(1122,'track','10j7ck4nsi8a8kc0lui0ivu5dgio0sti','Openvoips Technologies','OV500','122.169.33.222','122.169.33.222','/pbx/reports/api_livecall?action=search&call_flow=&max_rows=500&_=1759503110371','https://app.24ithub.com/pbx/reports/livecalls','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','reports/api_livecall','2025-10-03 15:10:17'),
(1123,'track','10j7ck4nsi8a8kc0lui0ivu5dgio0sti','Openvoips Technologies','OV500','122.169.33.222','122.169.33.222','/pbx/reports/api_livecall?action=search&call_flow=&max_rows=500&_=1759503110372','https://app.24ithub.com/pbx/reports/livecalls','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','reports/api_livecall','2025-10-03 15:10:23'),
(1124,'track','10j7ck4nsi8a8kc0lui0ivu5dgio0sti','Openvoips Technologies','OV500','122.169.33.222','122.169.33.222','/pbx/reports/api_livecall?action=search&call_flow=&max_rows=500&_=1759503110373','https://app.24ithub.com/pbx/reports/livecalls','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','reports/api_livecall','2025-10-03 15:10:27'),
(1125,'track','10j7ck4nsi8a8kc0lui0ivu5dgio0sti','Openvoips Technologies','OV500','122.169.33.222','122.169.33.222','/pbx/reports/api_livecall?action=search&call_flow=&max_rows=500&_=1759503110374','https://app.24ithub.com/pbx/reports/livecalls','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','reports/api_livecall','2025-10-03 15:10:33'),
(1126,'track','10j7ck4nsi8a8kc0lui0ivu5dgio0sti','Openvoips Technologies','OV500','122.169.33.222','122.169.33.222','/pbx/reports/api_livecall?action=search&call_flow=&max_rows=500&_=1759503110375','https://app.24ithub.com/pbx/reports/livecalls','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','reports/api_livecall','2025-10-03 15:10:38'),
(1127,'track','10j7ck4nsi8a8kc0lui0ivu5dgio0sti','Openvoips Technologies','OV500','122.169.33.222','122.169.33.222','/pbx/reports/api_livecall?action=search&call_flow=&max_rows=500&_=1759503110376','https://app.24ithub.com/pbx/reports/livecalls','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','reports/api_livecall','2025-10-03 15:10:43'),
(1128,'track','10j7ck4nsi8a8kc0lui0ivu5dgio0sti','Openvoips Technologies','OV500','122.169.33.222','122.169.33.222','/pbx/reports/api_livecall?action=search&call_flow=&max_rows=500&_=1759503110377','https://app.24ithub.com/pbx/reports/livecalls','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','reports/api_livecall','2025-10-03 15:10:48'),
(1129,'track','10j7ck4nsi8a8kc0lui0ivu5dgio0sti','Openvoips Technologies','OV500','122.169.33.222','122.169.33.222','/pbx/reports/api_livecall?action=search&call_flow=&max_rows=500&_=1759503110378','https://app.24ithub.com/pbx/reports/livecalls','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','reports/api_livecall','2025-10-03 15:10:53'),
(1130,'track','10j7ck4nsi8a8kc0lui0ivu5dgio0sti','Openvoips Technologies','OV500','122.169.33.222','122.169.33.222','/pbx/reports/api_livecall?action=search&call_flow=&max_rows=500&_=1759503110379','https://app.24ithub.com/pbx/reports/livecalls','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','reports/api_livecall','2025-10-03 15:10:58'),
(1131,'track','10j7ck4nsi8a8kc0lui0ivu5dgio0sti','Openvoips Technologies','OV500','122.169.33.222','122.169.33.222','/pbx/reports/api_livecall?action=search&call_flow=&max_rows=500&_=1759503110380','https://app.24ithub.com/pbx/reports/livecalls','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','reports/api_livecall','2025-10-03 15:11:03'),
(1132,'track','10j7ck4nsi8a8kc0lui0ivu5dgio0sti','Openvoips Technologies','OV500','122.169.33.222','122.169.33.222','/pbx/reports/api_livecall?action=search&call_flow=&max_rows=500&_=1759503110381','https://app.24ithub.com/pbx/reports/livecalls','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','reports/api_livecall','2025-10-03 15:11:08'),
(1133,'track','10j7ck4nsi8a8kc0lui0ivu5dgio0sti','Openvoips Technologies','OV500','122.169.33.222','122.169.33.222','/pbx/reports/api_livecall?action=search&call_flow=&max_rows=500&_=1759503110382','https://app.24ithub.com/pbx/reports/livecalls','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','reports/api_livecall','2025-10-03 15:11:14'),
(1134,'track','10j7ck4nsi8a8kc0lui0ivu5dgio0sti','Openvoips Technologies','OV500','122.169.33.222','122.169.33.222','/pbx/reports/api_livecall?action=search&call_flow=&max_rows=500&_=1759503110383','https://app.24ithub.com/pbx/reports/livecalls','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','reports/api_livecall','2025-10-03 15:11:18'),
(1135,'track','10j7ck4nsi8a8kc0lui0ivu5dgio0sti','Openvoips Technologies','OV500','122.169.33.222','122.169.33.222','/pbx/reports/api_livecall?action=search&call_flow=&max_rows=500&_=1759503110384','https://app.24ithub.com/pbx/reports/livecalls','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','reports/api_livecall','2025-10-03 15:11:25'),
(1136,'track','10j7ck4nsi8a8kc0lui0ivu5dgio0sti','Openvoips Technologies','OV500','122.169.33.222','122.169.33.222','/pbx/reports/api_livecall?action=search&call_flow=&max_rows=500&_=1759503110385','https://app.24ithub.com/pbx/reports/livecalls','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','reports/api_livecall','2025-10-03 15:11:28'),
(1137,'track','10j7ck4nsi8a8kc0lui0ivu5dgio0sti','Openvoips Technologies','OV500','122.169.33.222','122.169.33.222','/pbx/reports/api_livecall?action=search&call_flow=&max_rows=500&_=1759503110386','https://app.24ithub.com/pbx/reports/livecalls','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','reports/api_livecall','2025-10-03 15:11:38'),
(1138,'track','10j7ck4nsi8a8kc0lui0ivu5dgio0sti','Openvoips Technologies','OV500','122.169.33.222','122.169.33.222','/pbx/reports/api_livecall?action=search&call_flow=&max_rows=500&_=1759503110387','https://app.24ithub.com/pbx/reports/livecalls','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','reports/api_livecall','2025-10-03 15:11:38'),
(1139,'track','10j7ck4nsi8a8kc0lui0ivu5dgio0sti','Openvoips Technologies','OV500','122.169.33.222','122.169.33.222','/pbx/reports/api_livecall?action=search&call_flow=&max_rows=500&_=1759503110388','https://app.24ithub.com/pbx/reports/livecalls','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','reports/api_livecall','2025-10-03 15:11:44'),
(1140,'track','10j7ck4nsi8a8kc0lui0ivu5dgio0sti','Openvoips Technologies','OV500','122.169.33.222','122.169.33.222','/pbx/reports/api_livecall?action=search&call_flow=&max_rows=500&_=1759503110389','https://app.24ithub.com/pbx/reports/livecalls','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','reports/api_livecall','2025-10-03 15:11:49'),
(1141,'track','10j7ck4nsi8a8kc0lui0ivu5dgio0sti','Openvoips Technologies','OV500','122.169.33.222','122.169.33.222','/pbx/reports/api_livecall?action=search&call_flow=&max_rows=500&_=1759503110390','https://app.24ithub.com/pbx/reports/livecalls','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','reports/api_livecall','2025-10-03 15:11:54'),
(1142,'track','10j7ck4nsi8a8kc0lui0ivu5dgio0sti','Openvoips Technologies','OV500','122.169.33.222','122.169.33.222','/pbx/reports/api_livecall?action=search&call_flow=&max_rows=500&_=1759503110391','https://app.24ithub.com/pbx/reports/livecalls','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','reports/api_livecall','2025-10-03 15:11:59'),
(1143,'track','10j7ck4nsi8a8kc0lui0ivu5dgio0sti','Openvoips Technologies','OV500','122.169.33.222','122.169.33.222','/pbx/reports/api_livecall?action=search&call_flow=&max_rows=500&_=1759503110392','https://app.24ithub.com/pbx/reports/livecalls','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','reports/api_livecall','2025-10-03 15:12:04'),
(1144,'track','10j7ck4nsi8a8kc0lui0ivu5dgio0sti','Openvoips Technologies','OV500','122.169.33.222','122.169.33.222','/pbx/reports/api_livecall?action=search&call_flow=&max_rows=500&_=1759503110393','https://app.24ithub.com/pbx/reports/livecalls','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','reports/api_livecall','2025-10-03 15:12:09'),
(1145,'track','10j7ck4nsi8a8kc0lui0ivu5dgio0sti','Openvoips Technologies','OV500','122.169.33.222','122.169.33.222','/pbx/reports/api_livecall?action=search&call_flow=&max_rows=500&_=1759503110394','https://app.24ithub.com/pbx/reports/livecalls','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','reports/api_livecall','2025-10-03 15:12:15'),
(1146,'track','10j7ck4nsi8a8kc0lui0ivu5dgio0sti','Openvoips Technologies','OV500','122.169.33.222','122.169.33.222','/pbx/reports/api_livecall?action=search&call_flow=&max_rows=500&_=1759503110395','https://app.24ithub.com/pbx/reports/livecalls','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','reports/api_livecall','2025-10-03 15:12:19'),
(1147,'track','10j7ck4nsi8a8kc0lui0ivu5dgio0sti','Openvoips Technologies','OV500','122.169.33.222','122.169.33.222','/pbx/reports/api_livecall?action=search&call_flow=&max_rows=500&_=1759503110396','https://app.24ithub.com/pbx/reports/livecalls','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','reports/api_livecall','2025-10-03 15:12:25'),
(1148,'track','10j7ck4nsi8a8kc0lui0ivu5dgio0sti','Openvoips Technologies','OV500','122.169.33.222','122.169.33.222','/pbx/reports/api_livecall?action=search&call_flow=&max_rows=500&_=1759503110397','https://app.24ithub.com/pbx/reports/livecalls','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','reports/api_livecall','2025-10-03 15:12:29'),
(1149,'track','10j7ck4nsi8a8kc0lui0ivu5dgio0sti','Openvoips Technologies','OV500','122.169.33.222','122.169.33.222','/pbx/reports/api_livecall?action=search&call_flow=&max_rows=500&_=1759503110398','https://app.24ithub.com/pbx/reports/livecalls','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','reports/api_livecall','2025-10-03 15:12:35'),
(1150,'track','10j7ck4nsi8a8kc0lui0ivu5dgio0sti','Openvoips Technologies','OV500','122.169.33.222','122.169.33.222','/pbx/reports/api_livecall?action=search&call_flow=&max_rows=500&_=1759503110399','https://app.24ithub.com/pbx/reports/livecalls','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','reports/api_livecall','2025-10-03 15:12:40'),
(1151,'track','10j7ck4nsi8a8kc0lui0ivu5dgio0sti','Openvoips Technologies','OV500','122.169.33.222','122.169.33.222','/pbx/reports/api_livecall?action=search&call_flow=&max_rows=500&_=1759503110400','https://app.24ithub.com/pbx/reports/livecalls','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','reports/api_livecall','2025-10-03 15:12:45'),
(1152,'track','10j7ck4nsi8a8kc0lui0ivu5dgio0sti','Openvoips Technologies','OV500','122.169.33.222','122.169.33.222','/pbx/reports/api_livecall?action=search&call_flow=&max_rows=500&_=1759503110401','https://app.24ithub.com/pbx/reports/livecalls','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','reports/api_livecall','2025-10-03 15:12:50'),
(1153,'track','10j7ck4nsi8a8kc0lui0ivu5dgio0sti','Openvoips Technologies','OV500','122.169.33.222','122.169.33.222','/pbx/reports/api_livecall?action=search&call_flow=&max_rows=500&_=1759503110402','https://app.24ithub.com/pbx/reports/livecalls','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','reports/api_livecall','2025-10-03 15:12:55'),
(1154,'track','10j7ck4nsi8a8kc0lui0ivu5dgio0sti','Openvoips Technologies','OV500','122.169.33.222','122.169.33.222','/pbx/reports/api_livecall?action=search&call_flow=&max_rows=500&_=1759503110403','https://app.24ithub.com/pbx/reports/livecalls','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','reports/api_livecall','2025-10-03 15:13:00'),
(1155,'track','10j7ck4nsi8a8kc0lui0ivu5dgio0sti','Openvoips Technologies','OV500','122.169.33.222','122.169.33.222','/pbx/reports/api_livecall?action=search&call_flow=&max_rows=500&_=1759503110404','https://app.24ithub.com/pbx/reports/livecalls','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','reports/api_livecall','2025-10-03 15:13:06'),
(1156,'track','10j7ck4nsi8a8kc0lui0ivu5dgio0sti','Openvoips Technologies','OV500','122.169.33.222','122.169.33.222','/pbx/reports/api_livecall?action=search&call_flow=&max_rows=500&_=1759503110405','https://app.24ithub.com/pbx/reports/livecalls','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','reports/api_livecall','2025-10-03 15:13:10'),
(1157,'track','10j7ck4nsi8a8kc0lui0ivu5dgio0sti','Openvoips Technologies','OV500','122.169.33.222','122.169.33.222','/pbx/reports/api_livecall?action=search&call_flow=&max_rows=500&_=1759503110406','https://app.24ithub.com/pbx/reports/livecalls','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','reports/api_livecall','2025-10-03 15:13:16'),
(1158,'track','10j7ck4nsi8a8kc0lui0ivu5dgio0sti','Openvoips Technologies','OV500','122.169.33.222','122.169.33.222','/pbx/reports/api_livecall?action=search&call_flow=&max_rows=500&_=1759503110407','https://app.24ithub.com/pbx/reports/livecalls','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','reports/api_livecall','2025-10-03 15:13:21'),
(1159,'track','10j7ck4nsi8a8kc0lui0ivu5dgio0sti','Openvoips Technologies','OV500','122.169.33.222','122.169.33.222','/pbx/reports/api_livecall?action=search&call_flow=&max_rows=500&_=1759503110408','https://app.24ithub.com/pbx/reports/livecalls','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','reports/api_livecall','2025-10-03 15:13:26'),
(1160,'track','10j7ck4nsi8a8kc0lui0ivu5dgio0sti','Openvoips Technologies','OV500','122.169.33.222','122.169.33.222','/pbx/reports/api_livecall?action=search&call_flow=&max_rows=500&_=1759503110409','https://app.24ithub.com/pbx/reports/livecalls','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','reports/api_livecall','2025-10-03 15:13:31'),
(1161,'track','10j7ck4nsi8a8kc0lui0ivu5dgio0sti','Openvoips Technologies','OV500','122.169.33.222','122.169.33.222','/pbx/reports/api_livecall?action=search&call_flow=&max_rows=500&_=1759503110410','https://app.24ithub.com/pbx/reports/livecalls','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','reports/api_livecall','2025-10-03 15:13:36'),
(1162,'track','10j7ck4nsi8a8kc0lui0ivu5dgio0sti','Openvoips Technologies','OV500','122.169.33.222','122.169.33.222','/pbx/reports/api_livecall?action=search&call_flow=&max_rows=500&_=1759503110411','https://app.24ithub.com/pbx/reports/livecalls','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','reports/api_livecall','2025-10-03 15:13:41'),
(1163,'track','10j7ck4nsi8a8kc0lui0ivu5dgio0sti','Openvoips Technologies','OV500','122.169.33.222','122.169.33.222','/pbx/reports/api_livecall?action=search&call_flow=&max_rows=500&_=1759503110412','https://app.24ithub.com/pbx/reports/livecalls','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','reports/api_livecall','2025-10-03 15:13:47'),
(1164,'track','10j7ck4nsi8a8kc0lui0ivu5dgio0sti','Openvoips Technologies','OV500','122.169.33.222','122.169.33.222','/pbx/reports/api_livecall?action=search&call_flow=&max_rows=500&_=1759503110413','https://app.24ithub.com/pbx/reports/livecalls','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','reports/api_livecall','2025-10-03 15:13:51'),
(1165,'track','10j7ck4nsi8a8kc0lui0ivu5dgio0sti','Openvoips Technologies','OV500','122.169.33.222','122.169.33.222','/pbx/reports/api_livecall?action=search&call_flow=&max_rows=500&_=1759503110414','https://app.24ithub.com/pbx/reports/livecalls','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','reports/api_livecall','2025-10-03 15:13:57'),
(1166,'track','10j7ck4nsi8a8kc0lui0ivu5dgio0sti','Openvoips Technologies','OV500','122.169.33.222','122.169.33.222','/pbx/reports/api_livecall?action=search&call_flow=&max_rows=500&_=1759503110415','https://app.24ithub.com/pbx/reports/livecalls','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','reports/api_livecall','2025-10-03 15:14:02'),
(1167,'track','10j7ck4nsi8a8kc0lui0ivu5dgio0sti','Openvoips Technologies','OV500','122.169.33.222','122.169.33.222','/pbx/reports/api_livecall?action=search&call_flow=&max_rows=500&_=1759503110416','https://app.24ithub.com/pbx/reports/livecalls','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','reports/api_livecall','2025-10-03 15:14:07'),
(1168,'track','10j7ck4nsi8a8kc0lui0ivu5dgio0sti','Openvoips Technologies','OV500','122.169.33.222','122.169.33.222','/pbx/reports/api_livecall?action=search&call_flow=&max_rows=500&_=1759503110417','https://app.24ithub.com/pbx/reports/livecalls','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','reports/api_livecall','2025-10-03 15:14:12'),
(1169,'track','10j7ck4nsi8a8kc0lui0ivu5dgio0sti','Openvoips Technologies','OV500','122.169.33.222','122.169.33.222','/pbx/reports/api_livecall?action=search&call_flow=&max_rows=500&_=1759503110418','https://app.24ithub.com/pbx/reports/livecalls','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','reports/api_livecall','2025-10-03 15:14:17'),
(1170,'track','10j7ck4nsi8a8kc0lui0ivu5dgio0sti','Openvoips Technologies','OV500','122.169.33.222','122.169.33.222','/pbx/reports/api_livecall?action=search&call_flow=&max_rows=500&_=1759503110419','https://app.24ithub.com/pbx/reports/livecalls','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','reports/api_livecall','2025-10-03 15:14:22'),
(1171,'track','10j7ck4nsi8a8kc0lui0ivu5dgio0sti','Openvoips Technologies','OV500','122.169.33.222','122.169.33.222','/pbx/reports/api_livecall?action=search&call_flow=&max_rows=500&_=1759503110420','https://app.24ithub.com/pbx/reports/livecalls','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','reports/api_livecall','2025-10-03 15:14:27'),
(1172,'track','10j7ck4nsi8a8kc0lui0ivu5dgio0sti','Openvoips Technologies','OV500','122.169.33.222','122.169.33.222','/pbx/reports/api_livecall?action=search&call_flow=&max_rows=500&_=1759503110421','https://app.24ithub.com/pbx/reports/livecalls','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','reports/api_livecall','2025-10-03 15:14:32'),
(1173,'track','10j7ck4nsi8a8kc0lui0ivu5dgio0sti','Openvoips Technologies','OV500','122.169.33.222','122.169.33.222','/pbx/reports/api_livecall?action=search&call_flow=&max_rows=500&_=1759503110422','https://app.24ithub.com/pbx/reports/livecalls','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','reports/api_livecall','2025-10-03 15:14:38'),
(1174,'track','10j7ck4nsi8a8kc0lui0ivu5dgio0sti','Openvoips Technologies','OV500','122.169.33.222','122.169.33.222','/pbx/reports/api_livecall?action=search&call_flow=&max_rows=500&_=1759503110423','https://app.24ithub.com/pbx/reports/livecalls','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','reports/api_livecall','2025-10-03 15:14:42'),
(1175,'track','10j7ck4nsi8a8kc0lui0ivu5dgio0sti','Openvoips Technologies','OV500','122.169.33.222','122.169.33.222','/pbx/reports/api_livecall?action=search&call_flow=&max_rows=500&_=1759503110424','https://app.24ithub.com/pbx/reports/livecalls','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','reports/api_livecall','2025-10-03 15:14:48'),
(1176,'track','10j7ck4nsi8a8kc0lui0ivu5dgio0sti','Openvoips Technologies','OV500','122.169.33.222','122.169.33.222','/pbx/reports/api_livecall?action=search&call_flow=&max_rows=500&_=1759503110425','https://app.24ithub.com/pbx/reports/livecalls','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','reports/api_livecall','2025-10-03 15:14:52'),
(1177,'track','10j7ck4nsi8a8kc0lui0ivu5dgio0sti','Openvoips Technologies','OV500','122.169.33.222','122.169.33.222','/pbx/reports/api_livecall?action=search&call_flow=&max_rows=500&_=1759503110426','https://app.24ithub.com/pbx/reports/livecalls','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','reports/api_livecall','2025-10-03 15:14:58'),
(1178,'track','10j7ck4nsi8a8kc0lui0ivu5dgio0sti','Openvoips Technologies','OV500','122.169.33.222','122.169.33.222','/pbx/reports/api_livecall?action=search&call_flow=&max_rows=500&_=1759503110427','https://app.24ithub.com/pbx/reports/livecalls','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','reports/api_livecall','2025-10-03 15:15:03'),
(1179,'track','10j7ck4nsi8a8kc0lui0ivu5dgio0sti','Openvoips Technologies','OV500','122.169.33.222','122.169.33.222','/pbx/reports/api_livecall?action=search&call_flow=&max_rows=500&_=1759503110428','https://app.24ithub.com/pbx/reports/livecalls','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','reports/api_livecall','2025-10-03 15:15:08'),
(1180,'track','10j7ck4nsi8a8kc0lui0ivu5dgio0sti','Openvoips Technologies','OV500','122.169.33.222','122.169.33.222','/pbx/reports/api_livecall?action=search&call_flow=&max_rows=500&_=1759503110429','https://app.24ithub.com/pbx/reports/livecalls','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','reports/api_livecall','2025-10-03 15:15:13'),
(1181,'track','10j7ck4nsi8a8kc0lui0ivu5dgio0sti','Openvoips Technologies','OV500','122.169.33.222','122.169.33.222','/pbx/reports/api_livecall?action=search&call_flow=&max_rows=500&_=1759503110430','https://app.24ithub.com/pbx/reports/livecalls','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','reports/api_livecall','2025-10-03 15:15:18'),
(1182,'track','10j7ck4nsi8a8kc0lui0ivu5dgio0sti','Openvoips Technologies','OV500','122.169.33.222','122.169.33.222','/pbx/reports/api_livecall?action=search&call_flow=&max_rows=500&_=1759503110431','https://app.24ithub.com/pbx/reports/livecalls','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','reports/api_livecall','2025-10-03 15:15:23'),
(1183,'track','10j7ck4nsi8a8kc0lui0ivu5dgio0sti','Openvoips Technologies','OV500','122.169.33.222','122.169.33.222','/pbx/reports/api_livecall?action=search&call_flow=&max_rows=500&_=1759503110432','https://app.24ithub.com/pbx/reports/livecalls','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','reports/api_livecall','2025-10-03 15:15:29'),
(1184,'track','10j7ck4nsi8a8kc0lui0ivu5dgio0sti','Openvoips Technologies','OV500','122.169.33.222','122.169.33.222','/pbx/reports/api_livecall?action=search&call_flow=&max_rows=500&_=1759503110433','https://app.24ithub.com/pbx/reports/livecalls','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','reports/api_livecall','2025-10-03 15:15:33'),
(1185,'track','10j7ck4nsi8a8kc0lui0ivu5dgio0sti','Openvoips Technologies','OV500','122.169.33.222','122.169.33.222','/pbx/reports/api_livecall?action=search&call_flow=&max_rows=500&_=1759503110434','https://app.24ithub.com/pbx/reports/livecalls','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','reports/api_livecall','2025-10-03 15:15:39'),
(1186,'track','10j7ck4nsi8a8kc0lui0ivu5dgio0sti','Openvoips Technologies','OV500','122.169.33.222','122.169.33.222','/pbx/reports/api_livecall?action=search&call_flow=&max_rows=500&_=1759503110435','https://app.24ithub.com/pbx/reports/livecalls','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','reports/api_livecall','2025-10-03 15:15:43'),
(1187,'track','10j7ck4nsi8a8kc0lui0ivu5dgio0sti','Openvoips Technologies','OV500','122.169.33.222','122.169.33.222','/pbx/reports/api_livecall?action=search&call_flow=&max_rows=500&_=1759503110436','https://app.24ithub.com/pbx/reports/livecalls','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','reports/api_livecall','2025-10-03 15:15:49'),
(1188,'track','10j7ck4nsi8a8kc0lui0ivu5dgio0sti','Openvoips Technologies','OV500','122.169.33.222','122.169.33.222','/pbx/reports/api_livecall?action=search&call_flow=&max_rows=500&_=1759503110437','https://app.24ithub.com/pbx/reports/livecalls','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','reports/api_livecall','2025-10-03 15:15:54'),
(1189,'track','10j7ck4nsi8a8kc0lui0ivu5dgio0sti','Openvoips Technologies','OV500','122.169.33.222','122.169.33.222','/pbx/reports/api_livecall?action=search&call_flow=&max_rows=500&_=1759503110438','https://app.24ithub.com/pbx/reports/livecalls','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','reports/api_livecall','2025-10-03 15:15:59'),
(1190,'track','10j7ck4nsi8a8kc0lui0ivu5dgio0sti','Openvoips Technologies','OV500','122.169.33.222','122.169.33.222','/pbx/reports/api_livecall?action=search&call_flow=&max_rows=500&_=1759503110439','https://app.24ithub.com/pbx/reports/livecalls','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','reports/api_livecall','2025-10-03 15:16:04'),
(1191,'track','10j7ck4nsi8a8kc0lui0ivu5dgio0sti','Openvoips Technologies','OV500','122.169.33.222','122.169.33.222','/pbx/reports/api_livecall?action=search&call_flow=&max_rows=500&_=1759503110440','https://app.24ithub.com/pbx/reports/livecalls','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','reports/api_livecall','2025-10-03 15:16:09'),
(1192,'track','10j7ck4nsi8a8kc0lui0ivu5dgio0sti','Openvoips Technologies','OV500','122.169.33.222','122.169.33.222','/pbx/reports/api_livecall?action=search&call_flow=&max_rows=500&_=1759503110441','https://app.24ithub.com/pbx/reports/livecalls','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','reports/api_livecall','2025-10-03 15:16:14'),
(1193,'track','10j7ck4nsi8a8kc0lui0ivu5dgio0sti','Openvoips Technologies','OV500','122.169.33.222','122.169.33.222','/pbx/reports/api_livecall?action=search&call_flow=&max_rows=500&_=1759503110442','https://app.24ithub.com/pbx/reports/livecalls','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','reports/api_livecall','2025-10-03 15:16:19'),
(1194,'track','10j7ck4nsi8a8kc0lui0ivu5dgio0sti','Openvoips Technologies','OV500','122.169.33.222','122.169.33.222','/pbx/reports/api_livecall?action=search&call_flow=&max_rows=500&_=1759503110443','https://app.24ithub.com/pbx/reports/livecalls','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','reports/api_livecall','2025-10-03 15:16:24'),
(1195,'track','10j7ck4nsi8a8kc0lui0ivu5dgio0sti','Openvoips Technologies','OV500','122.169.33.222','122.169.33.222','/pbx/reports/api_livecall?action=search&call_flow=&max_rows=500&_=1759503110444','https://app.24ithub.com/pbx/reports/livecalls','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','reports/api_livecall','2025-10-03 15:16:31'),
(1196,'track','10j7ck4nsi8a8kc0lui0ivu5dgio0sti','Openvoips Technologies','OV500','122.169.33.222','122.169.33.222','/pbx/reports/api_livecall?action=search&call_flow=&max_rows=500&_=1759503110445','https://app.24ithub.com/pbx/reports/livecalls','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','reports/api_livecall','2025-10-03 15:16:47'),
(1197,'track','10j7ck4nsi8a8kc0lui0ivu5dgio0sti','Openvoips Technologies','OV500','122.169.33.222','122.169.33.222','/pbx/reports/api_livecall?action=search&call_flow=&max_rows=500&_=1759503110447','https://app.24ithub.com/pbx/reports/livecalls','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','reports/api_livecall','2025-10-03 15:16:48'),
(1198,'track','10j7ck4nsi8a8kc0lui0ivu5dgio0sti','Openvoips Technologies','OV500','122.169.33.222','122.169.33.222','/pbx/reports/api_livecall?action=search&call_flow=&max_rows=500&_=1759503110446','https://app.24ithub.com/pbx/reports/livecalls','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','reports/api_livecall','2025-10-03 15:16:48'),
(1199,'track','10j7ck4nsi8a8kc0lui0ivu5dgio0sti','Openvoips Technologies','OV500','122.169.33.222','122.169.33.222','/pbx/reports/api_livecall?action=search&call_flow=&max_rows=500&_=1759503110448','https://app.24ithub.com/pbx/reports/livecalls','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','reports/api_livecall','2025-10-03 15:16:50'),
(1200,'track','10j7ck4nsi8a8kc0lui0ivu5dgio0sti','Openvoips Technologies','OV500','122.169.33.222','122.169.33.222','/pbx/reports/api_livecall?action=search&call_flow=&max_rows=500&_=1759503110449','https://app.24ithub.com/pbx/reports/livecalls','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','reports/api_livecall','2025-10-03 15:16:55'),
(1201,'track','10j7ck4nsi8a8kc0lui0ivu5dgio0sti','Openvoips Technologies','OV500','122.169.33.222','122.169.33.222','/pbx/reports/api_livecall?action=search&call_flow=&max_rows=500&_=1759503110450','https://app.24ithub.com/pbx/reports/livecalls','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','reports/api_livecall','2025-10-03 15:17:00'),
(1202,'track','10j7ck4nsi8a8kc0lui0ivu5dgio0sti','Openvoips Technologies','OV500','122.169.33.222','122.169.33.222','/pbx/reports/api_livecall?action=search&call_flow=&max_rows=500&_=1759503110451','https://app.24ithub.com/pbx/reports/livecalls','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','reports/api_livecall','2025-10-03 15:17:05'),
(1203,'track','10j7ck4nsi8a8kc0lui0ivu5dgio0sti','Openvoips Technologies','OV500','122.169.33.222','122.169.33.222','/pbx/reports/api_livecall?action=search&call_flow=&max_rows=500&_=1759503110452','https://app.24ithub.com/pbx/reports/livecalls','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','reports/api_livecall','2025-10-03 15:17:10'),
(1204,'track','10j7ck4nsi8a8kc0lui0ivu5dgio0sti','Openvoips Technologies','OV500','122.169.33.222','122.169.33.222','/pbx/reports/api_livecall?action=search&call_flow=&max_rows=500&_=1759503110453','https://app.24ithub.com/pbx/reports/livecalls','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','reports/api_livecall','2025-10-03 15:17:15'),
(1205,'track','10j7ck4nsi8a8kc0lui0ivu5dgio0sti','Openvoips Technologies','OV500','122.169.33.222','122.169.33.222','/pbx/reports/api_livecall?action=search&call_flow=&max_rows=500&_=1759503110454','https://app.24ithub.com/pbx/reports/livecalls','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','reports/api_livecall','2025-10-03 15:17:20'),
(1206,'track','10j7ck4nsi8a8kc0lui0ivu5dgio0sti','Openvoips Technologies','OV500','122.169.33.222','122.169.33.222','/pbx/reports/api_livecall?action=search&call_flow=&max_rows=500&_=1759503110455','https://app.24ithub.com/pbx/reports/livecalls','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','reports/api_livecall','2025-10-03 15:17:26'),
(1207,'track','10j7ck4nsi8a8kc0lui0ivu5dgio0sti','Openvoips Technologies','OV500','122.169.33.222','122.169.33.222','/pbx/reports/api_livecall?action=search&call_flow=&max_rows=500&_=1759503110456','https://app.24ithub.com/pbx/reports/livecalls','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','reports/api_livecall','2025-10-03 15:17:30'),
(1208,'track','10j7ck4nsi8a8kc0lui0ivu5dgio0sti','Openvoips Technologies','OV500','122.169.33.222','122.169.33.222','/pbx/reports/api_livecall?action=search&call_flow=&max_rows=500&_=1759503110457','https://app.24ithub.com/pbx/reports/livecalls','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','reports/api_livecall','2025-10-03 15:17:36'),
(1209,'track','10j7ck4nsi8a8kc0lui0ivu5dgio0sti','Openvoips Technologies','OV500','122.169.33.222','122.169.33.222','/pbx/reports/api_livecall?action=search&call_flow=&max_rows=500&_=1759503110458','https://app.24ithub.com/pbx/reports/livecalls','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','reports/api_livecall','2025-10-03 15:17:40'),
(1210,'track','10j7ck4nsi8a8kc0lui0ivu5dgio0sti','Openvoips Technologies','OV500','122.169.33.222','122.169.33.222','/pbx/reports/api_livecall?action=search&call_flow=&max_rows=500&_=1759503110459','https://app.24ithub.com/pbx/reports/livecalls','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','reports/api_livecall','2025-10-03 15:17:46'),
(1211,'track','10j7ck4nsi8a8kc0lui0ivu5dgio0sti','Openvoips Technologies','OV500','122.169.33.222','122.169.33.222','/pbx/reports/api_livecall?action=search&call_flow=&max_rows=500&_=1759503110460','https://app.24ithub.com/pbx/reports/livecalls','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','reports/api_livecall','2025-10-03 15:17:51'),
(1212,'track','10j7ck4nsi8a8kc0lui0ivu5dgio0sti','Openvoips Technologies','OV500','122.169.33.222','122.169.33.222','/pbx/reports/api_livecall?action=search&call_flow=&max_rows=500&_=1759503110461','https://app.24ithub.com/pbx/reports/livecalls','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','reports/api_livecall','2025-10-03 15:17:56'),
(1213,'track','10j7ck4nsi8a8kc0lui0ivu5dgio0sti','Openvoips Technologies','OV500','122.169.33.222','122.169.33.222','/pbx/reports/api_livecall?action=search&call_flow=&max_rows=500&_=1759503110462','https://app.24ithub.com/pbx/reports/livecalls','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','reports/api_livecall','2025-10-03 15:18:01'),
(1214,'track','10j7ck4nsi8a8kc0lui0ivu5dgio0sti','Openvoips Technologies','OV500','122.169.33.222','122.169.33.222','/pbx/reports/api_livecall?action=search&call_flow=&max_rows=500&_=1759503110463','https://app.24ithub.com/pbx/reports/livecalls','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','reports/api_livecall','2025-10-03 15:18:06'),
(1215,'track','10j7ck4nsi8a8kc0lui0ivu5dgio0sti','Openvoips Technologies','OV500','122.169.33.222','122.169.33.222','/pbx/reports/api_livecall?action=search&call_flow=&max_rows=500&_=1759503110464','https://app.24ithub.com/pbx/reports/livecalls','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','reports/api_livecall','2025-10-03 15:18:11'),
(1216,'track','10j7ck4nsi8a8kc0lui0ivu5dgio0sti','Openvoips Technologies','OV500','122.169.33.222','122.169.33.222','/pbx/reports/api_livecall?action=search&call_flow=&max_rows=500&_=1759503110465','https://app.24ithub.com/pbx/reports/livecalls','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','reports/api_livecall','2025-10-03 15:18:17'),
(1217,'track','10j7ck4nsi8a8kc0lui0ivu5dgio0sti','Openvoips Technologies','OV500','122.169.33.222','122.169.33.222','/pbx/reports/api_livecall?action=search&call_flow=&max_rows=500&_=1759503110466','https://app.24ithub.com/pbx/reports/livecalls','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','reports/api_livecall','2025-10-03 15:18:21'),
(1218,'track','10j7ck4nsi8a8kc0lui0ivu5dgio0sti','Openvoips Technologies','OV500','122.169.33.222','122.169.33.222','/pbx/reports/api_livecall?action=search&call_flow=&max_rows=500&_=1759503110467','https://app.24ithub.com/pbx/reports/livecalls','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','reports/api_livecall','2025-10-03 15:18:27'),
(1219,'track','10j7ck4nsi8a8kc0lui0ivu5dgio0sti','Openvoips Technologies','OV500','122.169.33.222','122.169.33.222','/pbx/reports/api_livecall?action=search&call_flow=&max_rows=500&_=1759503110468','https://app.24ithub.com/pbx/reports/livecalls','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','reports/api_livecall','2025-10-03 15:18:32'),
(1220,'track','10j7ck4nsi8a8kc0lui0ivu5dgio0sti','Openvoips Technologies','OV500','122.169.33.222','122.169.33.222','/pbx/reports/api_livecall?action=search&call_flow=&max_rows=500&_=1759503110469','https://app.24ithub.com/pbx/reports/livecalls','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','reports/api_livecall','2025-10-03 15:18:38'),
(1221,'track','10j7ck4nsi8a8kc0lui0ivu5dgio0sti','Openvoips Technologies','OV500','122.169.33.222','122.169.33.222','/pbx/reports/api_livecall?action=search&call_flow=&max_rows=500&_=1759503110470','https://app.24ithub.com/pbx/reports/livecalls','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','reports/api_livecall','2025-10-03 15:18:42'),
(1222,'track','10j7ck4nsi8a8kc0lui0ivu5dgio0sti','Openvoips Technologies','OV500','122.169.33.222','122.169.33.222','/pbx/reports/api_livecall?action=search&call_flow=&max_rows=500&_=1759503110471','https://app.24ithub.com/pbx/reports/livecalls','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','reports/api_livecall','2025-10-03 15:18:48'),
(1223,'track','10j7ck4nsi8a8kc0lui0ivu5dgio0sti','Openvoips Technologies','OV500','122.169.33.222','122.169.33.222','/pbx/reports/api_livecall?action=search&call_flow=&max_rows=500&_=1759503110472','https://app.24ithub.com/pbx/reports/livecalls','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','reports/api_livecall','2025-10-03 15:18:53'),
(1224,'track','10j7ck4nsi8a8kc0lui0ivu5dgio0sti','Openvoips Technologies','OV500','122.169.33.222','122.169.33.222','/pbx/reports/api_livecall?action=search&call_flow=&max_rows=500&_=1759503110473','https://app.24ithub.com/pbx/reports/livecalls','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','reports/api_livecall','2025-10-03 15:18:58'),
(1225,'track','10j7ck4nsi8a8kc0lui0ivu5dgio0sti','Openvoips Technologies','OV500','122.169.33.222','122.169.33.222','/pbx/reports/api_livecall?action=search&call_flow=&max_rows=500&_=1759503110474','https://app.24ithub.com/pbx/reports/livecalls','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','reports/api_livecall','2025-10-03 15:19:03'),
(1226,'track','10j7ck4nsi8a8kc0lui0ivu5dgio0sti','Openvoips Technologies','OV500','122.169.33.222','122.169.33.222','/pbx/reports/api_livecall?action=search&call_flow=&max_rows=500&_=1759503110475','https://app.24ithub.com/pbx/reports/livecalls','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','reports/api_livecall','2025-10-03 15:19:08'),
(1227,'track','10j7ck4nsi8a8kc0lui0ivu5dgio0sti','Openvoips Technologies','OV500','122.169.33.222','122.169.33.222','/pbx/reports/api_livecall?action=search&call_flow=&max_rows=500&_=1759503110476','https://app.24ithub.com/pbx/reports/livecalls','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','reports/api_livecall','2025-10-03 15:19:13'),
(1228,'track','10j7ck4nsi8a8kc0lui0ivu5dgio0sti','Openvoips Technologies','OV500','122.169.33.222','122.169.33.222','/pbx/reports/api_livecall?action=search&call_flow=&max_rows=500&_=1759503110477','https://app.24ithub.com/pbx/reports/livecalls','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','reports/api_livecall','2025-10-03 15:19:19'),
(1229,'track','10j7ck4nsi8a8kc0lui0ivu5dgio0sti','Openvoips Technologies','OV500','122.169.33.222','122.169.33.222','/pbx/reports/api_livecall?action=search&call_flow=&max_rows=500&_=1759503110478','https://app.24ithub.com/pbx/reports/livecalls','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','reports/api_livecall','2025-10-03 15:19:23'),
(1230,'track','10j7ck4nsi8a8kc0lui0ivu5dgio0sti','Openvoips Technologies','OV500','122.169.33.222','122.169.33.222','/pbx/reports/api_livecall?action=search&call_flow=&max_rows=500&_=1759503110479','https://app.24ithub.com/pbx/reports/livecalls','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','reports/api_livecall','2025-10-03 15:19:29'),
(1231,'track','10j7ck4nsi8a8kc0lui0ivu5dgio0sti','Openvoips Technologies','OV500','122.169.33.222','122.169.33.222','/pbx/reports/api_livecall?action=search&call_flow=&max_rows=500&_=1759503110480','https://app.24ithub.com/pbx/reports/livecalls','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','reports/api_livecall','2025-10-03 15:19:34'),
(1232,'track','10j7ck4nsi8a8kc0lui0ivu5dgio0sti','Openvoips Technologies','OV500','122.169.33.222','122.169.33.222','/pbx/reports/api_livecall?action=search&call_flow=&max_rows=500&_=1759503110481','https://app.24ithub.com/pbx/reports/livecalls','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','reports/api_livecall','2025-10-03 15:19:39'),
(1233,'track','10j7ck4nsi8a8kc0lui0ivu5dgio0sti','Openvoips Technologies','OV500','122.169.33.222','122.169.33.222','/pbx/reports/api_livecall?action=search&call_flow=&max_rows=500&_=1759503110482','https://app.24ithub.com/pbx/reports/livecalls','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','reports/api_livecall','2025-10-03 15:19:44'),
(1234,'track','10j7ck4nsi8a8kc0lui0ivu5dgio0sti','Openvoips Technologies','OV500','122.169.33.222','122.169.33.222','/pbx/reports/api_livecall?action=search&call_flow=&max_rows=500&_=1759503110483','https://app.24ithub.com/pbx/reports/livecalls','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','reports/api_livecall','2025-10-03 15:19:49'),
(1235,'track','10j7ck4nsi8a8kc0lui0ivu5dgio0sti','Openvoips Technologies','OV500','122.169.33.222','122.169.33.222','/pbx/reports/api_livecall?action=search&call_flow=&max_rows=500&_=1759503110484','https://app.24ithub.com/pbx/reports/livecalls','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','reports/api_livecall','2025-10-03 15:19:54'),
(1236,'track','10j7ck4nsi8a8kc0lui0ivu5dgio0sti','Openvoips Technologies','OV500','122.169.33.222','122.169.33.222','/pbx/reports/api_livecall?action=search&call_flow=&max_rows=500&_=1759503110485','https://app.24ithub.com/pbx/reports/livecalls','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','reports/api_livecall','2025-10-03 15:20:00'),
(1237,'track','10j7ck4nsi8a8kc0lui0ivu5dgio0sti','Openvoips Technologies','OV500','122.169.33.222','122.169.33.222','/pbx/reports/api_livecall?action=search&call_flow=&max_rows=500&_=1759503110486','https://app.24ithub.com/pbx/reports/livecalls','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','reports/api_livecall','2025-10-03 15:20:04'),
(1238,'track','10j7ck4nsi8a8kc0lui0ivu5dgio0sti','Openvoips Technologies','OV500','122.169.33.222','122.169.33.222','/pbx/reports/api_livecall?action=search&call_flow=&max_rows=500&_=1759503110487','https://app.24ithub.com/pbx/reports/livecalls','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','reports/api_livecall','2025-10-03 15:20:10'),
(1239,'track','10j7ck4nsi8a8kc0lui0ivu5dgio0sti','Openvoips Technologies','OV500','122.169.33.222','122.169.33.222','/pbx/reports/api_livecall?action=search&call_flow=&max_rows=500&_=1759503110488','https://app.24ithub.com/pbx/reports/livecalls','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','reports/api_livecall','2025-10-03 15:20:14'),
(1240,'track','10j7ck4nsi8a8kc0lui0ivu5dgio0sti','Openvoips Technologies','OV500','122.169.33.222','122.169.33.222','/pbx/reports/api_livecall?action=search&call_flow=&max_rows=500&_=1759503110489','https://app.24ithub.com/pbx/reports/livecalls','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','reports/api_livecall','2025-10-03 15:20:20'),
(1241,'track','10j7ck4nsi8a8kc0lui0ivu5dgio0sti','Openvoips Technologies','OV500','122.169.33.222','122.169.33.222','/pbx/reports/api_livecall?action=search&call_flow=&max_rows=500&_=1759503110490','https://app.24ithub.com/pbx/reports/livecalls','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','reports/api_livecall','2025-10-03 15:20:25'),
(1242,'track','10j7ck4nsi8a8kc0lui0ivu5dgio0sti','Openvoips Technologies','OV500','122.169.33.222','122.169.33.222','/pbx/reports/api_livecall?action=search&call_flow=&max_rows=500&_=1759503110491','https://app.24ithub.com/pbx/reports/livecalls','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','reports/api_livecall','2025-10-03 15:20:30'),
(1243,'track','10j7ck4nsi8a8kc0lui0ivu5dgio0sti','Openvoips Technologies','OV500','122.169.33.222','122.169.33.222','/pbx/reports/api_livecall?action=search&call_flow=&max_rows=500&_=1759503110492','https://app.24ithub.com/pbx/reports/livecalls','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','reports/api_livecall','2025-10-03 15:20:35'),
(1244,'track','10j7ck4nsi8a8kc0lui0ivu5dgio0sti','Openvoips Technologies','OV500','122.169.33.222','122.169.33.222','/pbx/reports/api_livecall?action=search&call_flow=&max_rows=500&_=1759503110493','https://app.24ithub.com/pbx/reports/livecalls','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','reports/api_livecall','2025-10-03 15:20:40'),
(1245,'track','10j7ck4nsi8a8kc0lui0ivu5dgio0sti','Openvoips Technologies','OV500','122.169.33.222','122.169.33.222','/pbx/reports/api_livecall?action=search&call_flow=&max_rows=500&_=1759503110494','https://app.24ithub.com/pbx/reports/livecalls','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','reports/api_livecall','2025-10-03 15:20:45'),
(1246,'track','10j7ck4nsi8a8kc0lui0ivu5dgio0sti','Openvoips Technologies','OV500','122.169.33.222','122.169.33.222','/pbx/reports/api_livecall?action=search&call_flow=&max_rows=500&_=1759503110495','https://app.24ithub.com/pbx/reports/livecalls','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','reports/api_livecall','2025-10-03 15:20:51'),
(1247,'track','10j7ck4nsi8a8kc0lui0ivu5dgio0sti','Openvoips Technologies','OV500','122.169.33.222','122.169.33.222','/pbx/reports/api_livecall?action=search&call_flow=&max_rows=500&_=1759503110496','https://app.24ithub.com/pbx/reports/livecalls','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','reports/api_livecall','2025-10-03 15:20:55'),
(1248,'track','10j7ck4nsi8a8kc0lui0ivu5dgio0sti','Openvoips Technologies','OV500','122.169.33.222','122.169.33.222','/pbx/reports/api_livecall?action=search&call_flow=&max_rows=500&_=1759503110497','https://app.24ithub.com/pbx/reports/livecalls','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','reports/api_livecall','2025-10-03 15:21:01'),
(1249,'track','10j7ck4nsi8a8kc0lui0ivu5dgio0sti','Openvoips Technologies','OV500','122.169.33.222','122.169.33.222','/pbx/reports/api_livecall?action=search&call_flow=&max_rows=500&_=1759503110498','https://app.24ithub.com/pbx/reports/livecalls','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','reports/api_livecall','2025-10-03 15:21:06'),
(1250,'track','10j7ck4nsi8a8kc0lui0ivu5dgio0sti','Openvoips Technologies','OV500','122.169.33.222','122.169.33.222','/pbx/reports/api_livecall?action=search&call_flow=&max_rows=500&_=1759503110499','https://app.24ithub.com/pbx/reports/livecalls','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','reports/api_livecall','2025-10-03 15:21:11'),
(1251,'track','10j7ck4nsi8a8kc0lui0ivu5dgio0sti','Openvoips Technologies','OV500','122.169.33.222','122.169.33.222','/pbx/reports/api_livecall?action=search&call_flow=&max_rows=500&_=1759503110500','https://app.24ithub.com/pbx/reports/livecalls','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','reports/api_livecall','2025-10-03 15:21:16'),
(1252,'track','10j7ck4nsi8a8kc0lui0ivu5dgio0sti','Openvoips Technologies','OV500','122.169.33.222','122.169.33.222','/pbx/reports/api_livecall?action=search&call_flow=&max_rows=500&_=1759503110501','https://app.24ithub.com/pbx/reports/livecalls','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','reports/api_livecall','2025-10-03 15:21:21'),
(1253,'track','10j7ck4nsi8a8kc0lui0ivu5dgio0sti','Openvoips Technologies','OV500','122.169.33.222','122.169.33.222','/pbx/reports/api_livecall?action=search&call_flow=&max_rows=500&_=1759503110502','https://app.24ithub.com/pbx/reports/livecalls','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','reports/api_livecall','2025-10-03 15:21:26'),
(1254,'track','10j7ck4nsi8a8kc0lui0ivu5dgio0sti','Openvoips Technologies','OV500','122.169.33.222','122.169.33.222','/pbx/reports/api_livecall?action=search&call_flow=&max_rows=500&_=1759503110503','https://app.24ithub.com/pbx/reports/livecalls','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','reports/api_livecall','2025-10-03 15:21:31'),
(1255,'track','10j7ck4nsi8a8kc0lui0ivu5dgio0sti','Openvoips Technologies','OV500','122.169.33.222','122.169.33.222','/pbx/reports/api_livecall?action=search&call_flow=&max_rows=500&_=1759503110504','https://app.24ithub.com/pbx/reports/livecalls','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','reports/api_livecall','2025-10-03 15:21:36'),
(1256,'track','10j7ck4nsi8a8kc0lui0ivu5dgio0sti','Openvoips Technologies','OV500','122.169.33.222','122.169.33.222','/pbx/reports/api_livecall?action=search&call_flow=&max_rows=500&_=1759503110505','https://app.24ithub.com/pbx/reports/livecalls','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','reports/api_livecall','2025-10-03 15:21:42'),
(1257,'track','10j7ck4nsi8a8kc0lui0ivu5dgio0sti','Openvoips Technologies','OV500','122.169.33.222','122.169.33.222','/pbx/reports/api_livecall?action=search&call_flow=&max_rows=500&_=1759503110506','https://app.24ithub.com/pbx/reports/livecalls','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','reports/api_livecall','2025-10-03 15:21:46'),
(1258,'track','10j7ck4nsi8a8kc0lui0ivu5dgio0sti','Openvoips Technologies','OV500','122.169.33.222','122.169.33.222','/pbx/reports/api_livecall?action=search&call_flow=&max_rows=500&_=1759503110507','https://app.24ithub.com/pbx/reports/livecalls','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','reports/api_livecall','2025-10-03 15:21:52'),
(1259,'track','10j7ck4nsi8a8kc0lui0ivu5dgio0sti','Openvoips Technologies','OV500','122.169.33.222','122.169.33.222','/pbx/reports/api_livecall?action=search&call_flow=&max_rows=500&_=1759503110508','https://app.24ithub.com/pbx/reports/livecalls','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','reports/api_livecall','2025-10-03 15:21:57'),
(1260,'track','10j7ck4nsi8a8kc0lui0ivu5dgio0sti','Openvoips Technologies','OV500','122.169.33.222','122.169.33.222','/pbx/reports/api_livecall?action=search&call_flow=&max_rows=500&_=1759503110509','https://app.24ithub.com/pbx/reports/livecalls','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','reports/api_livecall','2025-10-03 15:22:02'),
(1261,'track','10j7ck4nsi8a8kc0lui0ivu5dgio0sti','Openvoips Technologies','OV500','122.169.33.222','122.169.33.222','/pbx/reports/api_livecall?action=search&call_flow=&max_rows=500&_=1759503110510','https://app.24ithub.com/pbx/reports/livecalls','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','reports/api_livecall','2025-10-03 15:22:07'),
(1262,'track','10j7ck4nsi8a8kc0lui0ivu5dgio0sti','Openvoips Technologies','OV500','122.169.33.222','122.169.33.222','/pbx/reports/api_livecall?action=search&call_flow=&max_rows=500&_=1759503110511','https://app.24ithub.com/pbx/reports/livecalls','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','reports/api_livecall','2025-10-03 15:22:12'),
(1263,'track','10j7ck4nsi8a8kc0lui0ivu5dgio0sti','Openvoips Technologies','OV500','122.169.33.222','122.169.33.222','/pbx/reports/api_livecall?action=search&call_flow=&max_rows=500&_=1759503110512','https://app.24ithub.com/pbx/reports/livecalls','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','reports/api_livecall','2025-10-03 15:22:17'),
(1264,'track','10j7ck4nsi8a8kc0lui0ivu5dgio0sti','Openvoips Technologies','OV500','122.169.33.222','122.169.33.222','/pbx/reports/api_livecall?action=search&call_flow=&max_rows=500&_=1759503110513','https://app.24ithub.com/pbx/reports/livecalls','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','reports/api_livecall','2025-10-03 15:22:23'),
(1265,'track','10j7ck4nsi8a8kc0lui0ivu5dgio0sti','Openvoips Technologies','OV500','122.169.33.222','122.169.33.222','/pbx/reports/api_livecall?action=search&call_flow=&max_rows=500&_=1759503110514','https://app.24ithub.com/pbx/reports/livecalls','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','reports/api_livecall','2025-10-03 15:22:27'),
(1266,'track','10j7ck4nsi8a8kc0lui0ivu5dgio0sti','Openvoips Technologies','OV500','122.169.33.222','122.169.33.222','/pbx/reports/api_livecall?action=search&call_flow=&max_rows=500&_=1759503110516','https://app.24ithub.com/pbx/reports/livecalls','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','reports/api_livecall','2025-10-03 15:22:33'),
(1267,'track','10j7ck4nsi8a8kc0lui0ivu5dgio0sti','Openvoips Technologies','OV500','122.169.33.222','122.169.33.222','/pbx/reports/api_livecall?action=search&call_flow=&max_rows=500&_=1759503110515','https://app.24ithub.com/pbx/reports/livecalls','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','reports/api_livecall','2025-10-03 15:22:33'),
(1268,'track','10j7ck4nsi8a8kc0lui0ivu5dgio0sti','Openvoips Technologies','OV500','122.169.33.222','122.169.33.222','/pbx/reports/api_livecall?action=search&call_flow=&max_rows=500&_=1759503110517','https://app.24ithub.com/pbx/reports/livecalls','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','reports/api_livecall','2025-10-03 15:22:37'),
(1269,'track','10j7ck4nsi8a8kc0lui0ivu5dgio0sti','Openvoips Technologies','OV500','122.169.33.222','122.169.33.222','/pbx/reports/api_livecall?action=search&call_flow=&max_rows=500&_=1759503110518','https://app.24ithub.com/pbx/reports/livecalls','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','reports/api_livecall','2025-10-03 15:22:43'),
(1270,'track','10j7ck4nsi8a8kc0lui0ivu5dgio0sti','Openvoips Technologies','OV500','122.169.33.222','122.169.33.222','/pbx/reports/api_livecall?action=search&call_flow=&max_rows=500&_=1759503110519','https://app.24ithub.com/pbx/reports/livecalls','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','reports/api_livecall','2025-10-03 15:22:47'),
(1271,'track','ccdpvp6v96pk98pr369euhp47buk6eev','Openvoips Technologies','OV500','122.169.33.222','122.169.33.222','/reports/Calls','https://app.24ithub.com/pbx/reports/livecalls','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','reports/Calls','2025-10-03 15:22:53'),
(1272,'track','o1rq08isvci2r9p3n00fh90f9fgir4mn','','','185.244.104.2','185.244.104.2','/','http://173.208.52.222:443/','-','login/index','2025-10-03 15:28:42'),
(1273,'track','4kn1qt76um5e228oan1viuk4a0c6fajj','Openvoips Technologies','OV500','122.169.33.222','122.169.33.222','/pbx/featurecodes','https://app.24ithub.com/reports/Calls','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','featurecodes/index','2025-10-03 15:34:05'),
(1274,'track','4kn1qt76um5e228oan1viuk4a0c6fajj','Openvoips Technologies','OV500','122.169.33.222','122.169.33.222','/pbx/featurecodes/edit','https://app.24ithub.com/pbx/featurecodes','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','featurecodes/edit','2025-10-03 15:34:07'),
(1275,'track','4kn1qt76um5e228oan1viuk4a0c6fajj','Openvoips Technologies','OV500','122.169.33.222','122.169.33.222','/pbx/featurecodes','https://app.24ithub.com/pbx/featurecodes/edit','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','featurecodes/index','2025-10-03 15:34:10'),
(1276,'track','4kn1qt76um5e228oan1viuk4a0c6fajj','Openvoips Technologies','OV500','122.169.33.222','122.169.33.222','/pbx/featurecodes','https://app.24ithub.com/pbx/featurecodes/edit','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','featurecodes/index','2025-10-03 15:37:12'),
(1277,'track','4kn1qt76um5e228oan1viuk4a0c6fajj','Openvoips Technologies','OV500','122.169.33.222','122.169.33.222','/pbx/featurecodes/edit','https://app.24ithub.com/pbx/featurecodes','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','featurecodes/edit','2025-10-03 15:37:48'),
(1278,'track','4kn1qt76um5e228oan1viuk4a0c6fajj','Openvoips Technologies','OV500','122.169.33.222','122.169.33.222','/pbx/featurecodes/edit','https://app.24ithub.com/pbx/featurecodes/edit','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','featurecodes/edit','2025-10-03 15:37:52'),
(1279,'track','4kn1qt76um5e228oan1viuk4a0c6fajj','Openvoips Technologies','OV500','122.169.33.222','122.169.33.222','/pbx/featurecodes','https://app.24ithub.com/pbx/featurecodes/edit','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','featurecodes/index','2025-10-03 15:37:52'),
(1280,'track','gl0bd741tqjim7er5b5g572ie0tkbpmp','','','195.178.110.109','195.178.110.109','/','','l9tcpid/v1.1.0','login/index','2025-10-03 15:37:59'),
(1281,'track','n8aqk6tqmi4gq8co9t144fetblp9lr6c','','','196.251.118.110','196.251.118.110','/.env','','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/81.0.4044.129 Safari/537.36','/index','2025-10-03 15:43:38'),
(1282,'track','avkgpffbp47e3u1cdpvfvn8012t2q5n9','Openvoips Technologies','OV500','122.169.33.222','122.169.33.222','/pbx/featurecodes','https://app.24ithub.com/pbx/featurecodes/edit','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','featurecodes/index','2025-10-03 15:44:22'),
(1283,'track','avkgpffbp47e3u1cdpvfvn8012t2q5n9','Openvoips Technologies','OV500','122.169.33.222','122.169.33.222','/pbx/featurecodes/edit','https://app.24ithub.com/pbx/featurecodes','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','featurecodes/edit','2025-10-03 15:44:25'),
(1284,'track','avkgpffbp47e3u1cdpvfvn8012t2q5n9','Openvoips Technologies','OV500','122.169.33.222','122.169.33.222','/pbx/featurecodes/edit','https://app.24ithub.com/pbx/featurecodes/edit','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','featurecodes/edit','2025-10-03 15:44:27'),
(1285,'track','avkgpffbp47e3u1cdpvfvn8012t2q5n9','Openvoips Technologies','OV500','122.169.33.222','122.169.33.222','/pbx/featurecodes','https://app.24ithub.com/pbx/featurecodes/edit','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','featurecodes/index','2025-10-03 15:44:27'),
(1286,'track','avkgpffbp47e3u1cdpvfvn8012t2q5n9','Openvoips Technologies','OV500','122.169.33.222','122.169.33.222','/pbx/featurecodes','https://app.24ithub.com/pbx/featurecodes/edit','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','featurecodes/index','2025-10-03 15:45:42'),
(1287,'track','avkgpffbp47e3u1cdpvfvn8012t2q5n9','Openvoips Technologies','OV500','122.169.33.222','122.169.33.222','/pbx/featurecodes/edit','https://app.24ithub.com/pbx/featurecodes','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','featurecodes/edit','2025-10-03 15:46:57'),
(1288,'track','avkgpffbp47e3u1cdpvfvn8012t2q5n9','Openvoips Technologies','OV500','122.169.33.222','122.169.33.222','/pbx/featurecodes/edit','https://app.24ithub.com/pbx/featurecodes/edit','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','featurecodes/edit','2025-10-03 15:46:59'),
(1289,'track','avkgpffbp47e3u1cdpvfvn8012t2q5n9','Openvoips Technologies','OV500','122.169.33.222','122.169.33.222','/pbx/featurecodes','https://app.24ithub.com/pbx/featurecodes/edit','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','featurecodes/index','2025-10-03 15:47:00'),
(1290,'track','avkgpffbp47e3u1cdpvfvn8012t2q5n9','Openvoips Technologies','OV500','122.169.33.222','122.169.33.222','/pbx/queue','https://app.24ithub.com/pbx/featurecodes','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','queue/index','2025-10-03 15:47:03'),
(1291,'track','avkgpffbp47e3u1cdpvfvn8012t2q5n9','Openvoips Technologies','OV500','122.169.33.222','122.169.33.222','/pbx/agents','https://app.24ithub.com/pbx/queue','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','agents/index','2025-10-03 15:47:06'),
(1292,'track','avkgpffbp47e3u1cdpvfvn8012t2q5n9','Openvoips Technologies','OV500','122.169.33.222','122.169.33.222','/pbx/agents/edit/MQ_EQUALS__EQUALS_','https://app.24ithub.com/pbx/agents','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','agents/edit','2025-10-03 15:47:09'),
(1293,'track','avkgpffbp47e3u1cdpvfvn8012t2q5n9','Openvoips Technologies','OV500','122.169.33.222','122.169.33.222','/pbx/agents/edit/MQ_EQUALS__EQUALS_','https://app.24ithub.com/pbx/agents/edit/MQ_EQUALS__EQUALS_','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','agents/edit','2025-10-03 15:47:13'),
(1294,'track','avkgpffbp47e3u1cdpvfvn8012t2q5n9','Openvoips Technologies','OV500','122.169.33.222','122.169.33.222','/pbx/agents','https://app.24ithub.com/pbx/agents/edit/MQ_EQUALS__EQUALS_','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','agents/index','2025-10-03 15:47:13'),
(1295,'track','avkgpffbp47e3u1cdpvfvn8012t2q5n9','Openvoips Technologies','OV500','122.169.33.222','122.169.33.222','/pbx/queue','https://app.24ithub.com/pbx/agents','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','queue/index','2025-10-03 15:47:21'),
(1296,'track','avkgpffbp47e3u1cdpvfvn8012t2q5n9','Openvoips Technologies','OV500','122.169.33.222','122.169.33.222','/pbx/queue','https://app.24ithub.com/pbx/agents','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','queue/index','2025-10-03 15:48:07'),
(1297,'track','avkgpffbp47e3u1cdpvfvn8012t2q5n9','Openvoips Technologies','OV500','122.169.33.222','122.169.33.222','/pbx/agents','https://app.24ithub.com/pbx/queue','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','agents/index','2025-10-03 15:48:11'),
(1298,'track','avkgpffbp47e3u1cdpvfvn8012t2q5n9','Openvoips Technologies','OV500','122.169.33.222','122.169.33.222','/pbx/agents/edit/MQ_EQUALS__EQUALS_','https://app.24ithub.com/pbx/agents','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','agents/edit','2025-10-03 15:48:14'),
(1299,'track','avkgpffbp47e3u1cdpvfvn8012t2q5n9','Openvoips Technologies','OV500','122.169.33.222','122.169.33.222','/pbx/agents','https://app.24ithub.com/pbx/agents/edit/MQ_EQUALS__EQUALS_','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','agents/index','2025-10-03 15:48:19'),
(1300,'track','avkgpffbp47e3u1cdpvfvn8012t2q5n9','Openvoips Technologies','OV500','122.169.33.222','122.169.33.222','/pbx/agents','https://app.24ithub.com/pbx/agents','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','agents/index','2025-10-03 15:48:44'),
(1301,'track','avkgpffbp47e3u1cdpvfvn8012t2q5n9','Openvoips Technologies','OV500','122.169.33.222','122.169.33.222','/pbx/agents','https://app.24ithub.com/pbx/agents','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','agents/index','2025-10-03 15:48:46'),
(1302,'track','c2l3s7n4ma98c7ej0tocfs5nelnkcaal','Openvoips Technologies','OV500','122.169.33.222','122.169.33.222','/pbx/agents','https://app.24ithub.com/pbx/agents','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','agents/index','2025-10-03 15:49:39'),
(1303,'track','c2l3s7n4ma98c7ej0tocfs5nelnkcaal','Openvoips Technologies','OV500','122.169.33.222','122.169.33.222','/pbx/featurecodes','https://app.24ithub.com/pbx/agents','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','featurecodes/index','2025-10-03 15:49:41'),
(1304,'track','c2l3s7n4ma98c7ej0tocfs5nelnkcaal','Openvoips Technologies','OV500','122.169.33.222','122.169.33.222','/pbx/ringgroup','https://app.24ithub.com/pbx/featurecodes','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','ringgroup/index','2025-10-03 15:50:15'),
(1305,'track','c2l3s7n4ma98c7ej0tocfs5nelnkcaal','Openvoips Technologies','OV500','122.169.33.222','122.169.33.222','/pbx/extensions','https://app.24ithub.com/pbx/ringgroup','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','extensions/index','2025-10-03 15:50:17'),
(1306,'track','c2l3s7n4ma98c7ej0tocfs5nelnkcaal','Openvoips Technologies','OV500','122.169.33.222','122.169.33.222','/pbx/extensions/edit/RU9WSEVZNzkwMA_EQUALS__EQUALS_','https://app.24ithub.com/pbx/extensions','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','extensions/edit','2025-10-03 15:50:19'),
(1307,'track','c2l3s7n4ma98c7ej0tocfs5nelnkcaal','Openvoips Technologies','OV500','122.169.33.222','122.169.33.222','/pbx/extensions/edit/RU9WSEVZNzkwMA_EQUALS__EQUALS_','https://app.24ithub.com/pbx/extensions/edit/RU9WSEVZNzkwMA_EQUALS__EQUALS_','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','extensions/edit','2025-10-03 15:50:25'),
(1308,'track','c2l3s7n4ma98c7ej0tocfs5nelnkcaal','Openvoips Technologies','OV500','122.169.33.222','122.169.33.222','/pbx/extensions/edit/RU9WSEVZNzkwMA_EQUALS__EQUALS_','https://app.24ithub.com/pbx/extensions/edit/RU9WSEVZNzkwMA_EQUALS__EQUALS_','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','extensions/edit','2025-10-03 15:50:25'),
(1309,'track','c2l3s7n4ma98c7ej0tocfs5nelnkcaal','Openvoips Technologies','OV500','122.169.33.222','122.169.33.222','/pbx/queue','https://app.24ithub.com/pbx/extensions/edit/RU9WSEVZNzkwMA_EQUALS__EQUALS_','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','queue/index','2025-10-03 15:51:03'),
(1310,'track','c2l3s7n4ma98c7ej0tocfs5nelnkcaal','Openvoips Technologies','OV500','122.169.33.222','122.169.33.222','/pbx/queue/edit/MQ_EQUALS__EQUALS_','https://app.24ithub.com/pbx/queue','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','queue/edit','2025-10-03 15:51:16'),
(1311,'track','c2l3s7n4ma98c7ej0tocfs5nelnkcaal','Openvoips Technologies','OV500','122.169.33.222','122.169.33.222','/pbx/queue/edit/MQ_EQUALS__EQUALS_','https://app.24ithub.com/pbx/queue/edit/MQ_EQUALS__EQUALS_','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','queue/edit','2025-10-03 15:51:31'),
(1312,'track','c2l3s7n4ma98c7ej0tocfs5nelnkcaal','Openvoips Technologies','OV500','122.169.33.222','122.169.33.222','/pbx/queue/edit/MQ_EQUALS__EQUALS_','https://app.24ithub.com/pbx/queue/edit/MQ_EQUALS__EQUALS_','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','queue/edit','2025-10-03 15:51:31'),
(1313,'track','c2l3s7n4ma98c7ej0tocfs5nelnkcaal','Openvoips Technologies','OV500','122.169.33.222','122.169.33.222','/pbx/queue/edit/MQ_EQUALS__EQUALS_','https://app.24ithub.com/pbx/queue/edit/MQ_EQUALS__EQUALS_','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','queue/edit','2025-10-03 15:51:41'),
(1314,'track','c2l3s7n4ma98c7ej0tocfs5nelnkcaal','Openvoips Technologies','OV500','122.169.33.222','122.169.33.222','/pbx/queue/edit/MQ_EQUALS__EQUALS_','https://app.24ithub.com/pbx/queue/edit/MQ_EQUALS__EQUALS_','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','queue/edit','2025-10-03 15:51:41'),
(1315,'track','c2l3s7n4ma98c7ej0tocfs5nelnkcaal','Openvoips Technologies','OV500','122.169.33.222','122.169.33.222','/pbx/queue/edit/MQ_EQUALS__EQUALS_','https://app.24ithub.com/pbx/queue/edit/MQ_EQUALS__EQUALS_','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','queue/edit','2025-10-03 15:51:49'),
(1316,'track','c2l3s7n4ma98c7ej0tocfs5nelnkcaal','Openvoips Technologies','OV500','122.169.33.222','122.169.33.222','/pbx/queue','https://app.24ithub.com/pbx/queue/edit/MQ_EQUALS__EQUALS_','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','queue/index','2025-10-03 15:51:49'),
(1317,'track','m6082157go58lha938qh8q2v9u0ai96m','','','185.244.104.2','185.244.104.2','/','http://173.208.52.222:443/','-','login/index','2025-10-03 15:53:02'),
(1318,'track','ep10svci7a3fsr3hveg53h7ie7u01tbh','Openvoips Technologies','OV500','122.169.33.222','122.169.33.222','/pbx/featurecodes','https://app.24ithub.com/pbx/queue','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','featurecodes/index','2025-10-03 16:17:02'),
(1319,'track','ep10svci7a3fsr3hveg53h7ie7u01tbh','Openvoips Technologies','OV500','122.169.33.222','122.169.33.222','/pbx/queue','https://app.24ithub.com/pbx/featurecodes','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','queue/index','2025-10-03 16:17:32'),
(1320,'track','ep10svci7a3fsr3hveg53h7ie7u01tbh','Openvoips Technologies','OV500','122.169.33.222','122.169.33.222','/pbx/queue/edit/MQ_EQUALS__EQUALS_','https://app.24ithub.com/pbx/queue','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','queue/edit','2025-10-03 16:17:34'),
(1321,'track','ep10svci7a3fsr3hveg53h7ie7u01tbh','Openvoips Technologies','OV500','122.169.33.222','122.169.33.222','/pbx/agents','https://app.24ithub.com/pbx/queue/edit/MQ_EQUALS__EQUALS_','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','agents/index','2025-10-03 16:17:38'),
(1322,'track','ep10svci7a3fsr3hveg53h7ie7u01tbh','Openvoips Technologies','OV500','122.169.33.222','122.169.33.222','/pbx/agents/edit/MQ_EQUALS__EQUALS_','https://app.24ithub.com/pbx/agents','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','agents/edit','2025-10-03 16:17:40'),
(1323,'track','ep10svci7a3fsr3hveg53h7ie7u01tbh','Openvoips Technologies','OV500','122.169.33.222','122.169.33.222','/pbx/agents/edit/MQ_EQUALS__EQUALS_','https://app.24ithub.com/pbx/agents/edit/MQ_EQUALS__EQUALS_','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','agents/edit','2025-10-03 16:17:46'),
(1324,'track','ep10svci7a3fsr3hveg53h7ie7u01tbh','Openvoips Technologies','OV500','122.169.33.222','122.169.33.222','/pbx/agents/edit/MQ_EQUALS__EQUALS_','https://app.24ithub.com/pbx/agents/edit/MQ_EQUALS__EQUALS_','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','agents/edit','2025-10-03 16:17:46'),
(1325,'track','ep10svci7a3fsr3hveg53h7ie7u01tbh','Openvoips Technologies','OV500','122.169.33.222','122.169.33.222','/pbx/agents','https://app.24ithub.com/pbx/agents/edit/MQ_EQUALS__EQUALS_','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','agents/index','2025-10-03 16:18:06'),
(1326,'track','ep10svci7a3fsr3hveg53h7ie7u01tbh','Openvoips Technologies','OV500','122.169.33.222','122.169.33.222','/pbx/agents','https://app.24ithub.com/pbx/agents','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','agents/index','2025-10-03 16:18:44'),
(1327,'track','ep10svci7a3fsr3hveg53h7ie7u01tbh','Openvoips Technologies','OV500','122.169.33.222','122.169.33.222','/pbx/agents','https://app.24ithub.com/pbx/agents','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','agents/index','2025-10-03 16:18:45'),
(1328,'track','ep10svci7a3fsr3hveg53h7ie7u01tbh','Openvoips Technologies','OV500','122.169.33.222','122.169.33.222','/pbx/agents','https://app.24ithub.com/pbx/agents','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','agents/index','2025-10-03 16:18:47'),
(1329,'track','ep10svci7a3fsr3hveg53h7ie7u01tbh','Openvoips Technologies','OV500','122.169.33.222','122.169.33.222','/pbx/agents','https://app.24ithub.com/pbx/agents','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','agents/index','2025-10-03 16:19:04'),
(1330,'track','ep10svci7a3fsr3hveg53h7ie7u01tbh','Openvoips Technologies','OV500','122.169.33.222','122.169.33.222','/pbx/agents/edit/MQ_EQUALS__EQUALS_','https://app.24ithub.com/pbx/agents','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','agents/edit','2025-10-03 16:19:07'),
(1331,'track','ep10svci7a3fsr3hveg53h7ie7u01tbh','Openvoips Technologies','OV500','122.169.33.222','122.169.33.222','/pbx/agents','https://app.24ithub.com/pbx/agents/edit/MQ_EQUALS__EQUALS_','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','agents/index','2025-10-03 16:19:11'),
(1332,'track','ep10svci7a3fsr3hveg53h7ie7u01tbh','Openvoips Technologies','OV500','122.169.33.222','122.169.33.222','/pbx/agents','https://app.24ithub.com/pbx/agents','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','agents/index','2025-10-03 16:19:14'),
(1333,'track','ep10svci7a3fsr3hveg53h7ie7u01tbh','Openvoips Technologies','OV500','122.169.33.222','122.169.33.222','/pbx/agents/index/','https://app.24ithub.com/pbx/agents','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','agents/index','2025-10-03 16:19:14'),
(1334,'track','ep10svci7a3fsr3hveg53h7ie7u01tbh','Openvoips Technologies','OV500','122.169.33.222','122.169.33.222','/pbx/agents/add','https://app.24ithub.com/pbx/agents/index/','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','agents/add','2025-10-03 16:19:16'),
(1335,'track','ep10svci7a3fsr3hveg53h7ie7u01tbh','Openvoips Technologies','OV500','122.169.33.222','122.169.33.222','/pbx/agents/add','https://app.24ithub.com/pbx/agents/add','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','agents/add','2025-10-03 16:19:22'),
(1336,'track','ep10svci7a3fsr3hveg53h7ie7u01tbh','Openvoips Technologies','OV500','122.169.33.222','122.169.33.222','/pbx/agents/index','https://app.24ithub.com/pbx/agents/add','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','agents/index','2025-10-03 16:19:22'),
(1337,'track','ep10svci7a3fsr3hveg53h7ie7u01tbh','Openvoips Technologies','OV500','122.169.33.222','122.169.33.222','/pbx/queue','https://app.24ithub.com/pbx/agents/index','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','queue/index','2025-10-03 16:19:25'),
(1338,'track','ep10svci7a3fsr3hveg53h7ie7u01tbh','Openvoips Technologies','OV500','122.169.33.222','122.169.33.222','/pbx/queue/edit/MQ_EQUALS__EQUALS_','https://app.24ithub.com/pbx/queue','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','queue/edit','2025-10-03 16:19:27'),
(1339,'track','ep10svci7a3fsr3hveg53h7ie7u01tbh','Openvoips Technologies','OV500','122.169.33.222','122.169.33.222','/pbx/queue/edit/MQ_EQUALS__EQUALS_','https://app.24ithub.com/pbx/queue/edit/MQ_EQUALS__EQUALS_','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','queue/edit','2025-10-03 16:19:34'),
(1340,'track','ep10svci7a3fsr3hveg53h7ie7u01tbh','Openvoips Technologies','OV500','122.169.33.222','122.169.33.222','/pbx/queue','https://app.24ithub.com/pbx/queue/edit/MQ_EQUALS__EQUALS_','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','queue/index','2025-10-03 16:19:34'),
(1341,'track','ep10svci7a3fsr3hveg53h7ie7u01tbh','Openvoips Technologies','OV500','122.169.33.222','122.169.33.222','/pbx/queue/edit/MQ_EQUALS__EQUALS_','https://app.24ithub.com/pbx/queue','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','queue/edit','2025-10-03 16:19:37'),
(1342,'track','ep10svci7a3fsr3hveg53h7ie7u01tbh','Openvoips Technologies','OV500','122.169.33.222','122.169.33.222','/pbx/queue/edit/MQ_EQUALS__EQUALS_','https://app.24ithub.com/pbx/queue/edit/MQ_EQUALS__EQUALS_','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','queue/edit','2025-10-03 16:20:00'),
(1343,'track','ep10svci7a3fsr3hveg53h7ie7u01tbh','Openvoips Technologies','OV500','122.169.33.222','122.169.33.222','/pbx/queue/edit/MQ_EQUALS__EQUALS_','https://app.24ithub.com/pbx/queue/edit/MQ_EQUALS__EQUALS_','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','queue/edit','2025-10-03 16:20:00'),
(1344,'track','ep10svci7a3fsr3hveg53h7ie7u01tbh','Openvoips Technologies','OV500','122.169.33.222','122.169.33.222','/pbx/operatorpanal','https://app.24ithub.com/pbx/queue/edit/MQ_EQUALS__EQUALS_','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','operatorpanal/index','2025-10-03 16:20:18'),
(1345,'track','ep10svci7a3fsr3hveg53h7ie7u01tbh','Openvoips Technologies','OV500','122.169.33.222','122.169.33.222','/pbx/operatorpanal/ajax_set_hangup/8ab30bd2-bce6-479a-bdc5-cc5301a8fbce','https://app.24ithub.com/pbx/operatorpanal','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','operatorpanal/ajax_set_hangup','2025-10-03 16:20:23'),
(1346,'track','ep10svci7a3fsr3hveg53h7ie7u01tbh','Openvoips Technologies','OV500','122.169.33.222','122.169.33.222','/pbx/operatorpanal/ajax_get_call_data','https://app.24ithub.com/pbx/operatorpanal','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','operatorpanal/ajax_get_call_data','2025-10-03 16:20:23'),
(1347,'track','ep10svci7a3fsr3hveg53h7ie7u01tbh','Openvoips Technologies','OV500','122.169.33.222','122.169.33.222','/pbx/operatorpanal/ajax_get_call_data','https://app.24ithub.com/pbx/operatorpanal','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','operatorpanal/ajax_get_call_data','2025-10-03 16:20:29'),
(1348,'track','ep10svci7a3fsr3hveg53h7ie7u01tbh','Openvoips Technologies','OV500','122.169.33.222','122.169.33.222','/pbx/operatorpanal/ajax_get_call_data','https://app.24ithub.com/pbx/operatorpanal','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','operatorpanal/ajax_get_call_data','2025-10-03 16:20:34'),
(1349,'track','ep10svci7a3fsr3hveg53h7ie7u01tbh','Openvoips Technologies','OV500','122.169.33.222','122.169.33.222','/pbx/operatorpanal','https://app.24ithub.com/pbx/operatorpanal','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','operatorpanal/index','2025-10-03 16:20:37'),
(1350,'track','ep10svci7a3fsr3hveg53h7ie7u01tbh','Openvoips Technologies','OV500','122.169.33.222','122.169.33.222','/pbx/featurecodes','https://app.24ithub.com/pbx/operatorpanal','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','featurecodes/index','2025-10-03 16:20:43'),
(1351,'track','ep10svci7a3fsr3hveg53h7ie7u01tbh','Openvoips Technologies','OV500','122.169.33.222','122.169.33.222','/pbx/operatorpanal/ajax_get_call_data','https://app.24ithub.com/pbx/operatorpanal','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','operatorpanal/ajax_get_call_data','2025-10-03 16:20:43'),
(1352,'track','ep10svci7a3fsr3hveg53h7ie7u01tbh','Openvoips Technologies','OV500','122.169.33.222','122.169.33.222','/pbx/registration','https://app.24ithub.com/pbx/featurecodes','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','registration/index','2025-10-03 16:20:45'),
(1353,'track','ep10svci7a3fsr3hveg53h7ie7u01tbh','Openvoips Technologies','OV500','122.169.33.222','122.169.33.222','/pbx/registration/ajax_get_call_data','https://app.24ithub.com/pbx/registration','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','registration/ajax_get_call_data','2025-10-03 16:20:58'),
(1354,'track','ep10svci7a3fsr3hveg53h7ie7u01tbh','Openvoips Technologies','OV500','122.169.33.222','122.169.33.222','/pbx/registration/ajax_get_call_data','https://app.24ithub.com/pbx/registration','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','registration/ajax_get_call_data','2025-10-03 16:21:07'),
(1355,'track','ep10svci7a3fsr3hveg53h7ie7u01tbh','Openvoips Technologies','OV500','122.169.33.222','122.169.33.222','/pbx/registration/ajax_get_call_data','https://app.24ithub.com/pbx/registration','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','registration/ajax_get_call_data','2025-10-03 16:21:17'),
(1356,'track','ep10svci7a3fsr3hveg53h7ie7u01tbh','Openvoips Technologies','OV500','122.169.33.222','122.169.33.222','/pbx/agents','https://app.24ithub.com/pbx/registration','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','agents/index','2025-10-03 16:21:28'),
(1357,'track','ep10svci7a3fsr3hveg53h7ie7u01tbh','Openvoips Technologies','OV500','122.169.33.222','122.169.33.222','/pbx/registration/ajax_get_call_data','https://app.24ithub.com/pbx/registration','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','registration/ajax_get_call_data','2025-10-03 16:21:28'),
(1358,'track','ep10svci7a3fsr3hveg53h7ie7u01tbh','Openvoips Technologies','OV500','122.169.33.222','122.169.33.222','/pbx/agents/edit/Mg_EQUALS__EQUALS_','https://app.24ithub.com/pbx/agents','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','agents/edit','2025-10-03 16:21:36'),
(1359,'track','ep10svci7a3fsr3hveg53h7ie7u01tbh','Openvoips Technologies','OV500','122.169.33.222','122.169.33.222','/pbx/agents/edit/Mg_EQUALS__EQUALS_','https://app.24ithub.com/pbx/agents/edit/Mg_EQUALS__EQUALS_','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','agents/edit','2025-10-03 16:21:39'),
(1360,'track','ep10svci7a3fsr3hveg53h7ie7u01tbh','Openvoips Technologies','OV500','122.169.33.222','122.169.33.222','/pbx/agents','https://app.24ithub.com/pbx/agents/edit/Mg_EQUALS__EQUALS_','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','agents/index','2025-10-03 16:21:40'),
(1361,'track','plg6mtt0fmqvrelqms6890l524ldv2u2','Openvoips Technologies','OV500','122.169.33.222','122.169.33.222','/pbx/agents/edit/Mg_EQUALS__EQUALS_','https://app.24ithub.com/pbx/agents','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','agents/edit','2025-10-03 16:22:33'),
(1362,'track','plg6mtt0fmqvrelqms6890l524ldv2u2','Openvoips Technologies','OV500','122.169.33.222','122.169.33.222','/pbx/extensions','https://app.24ithub.com/pbx/agents/edit/Mg_EQUALS__EQUALS_','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','extensions/index','2025-10-03 16:22:51'),
(1363,'track','plg6mtt0fmqvrelqms6890l524ldv2u2','Openvoips Technologies','OV500','122.169.33.222','122.169.33.222','/pbx/featurecodes','https://app.24ithub.com/pbx/extensions','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','featurecodes/index','2025-10-03 16:22:57'),
(1364,'track','plg6mtt0fmqvrelqms6890l524ldv2u2','Openvoips Technologies','OV500','122.169.33.222','122.169.33.222','/pbx/agents/edit/Mg_EQUALS__EQUALS_','https://app.24ithub.com/pbx/agents','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','agents/edit','2025-10-03 16:23:35'),
(1365,'track','plg6mtt0fmqvrelqms6890l524ldv2u2','Openvoips Technologies','OV500','122.169.33.222','122.169.33.222','/pbx/agents/edit/Mg_EQUALS__EQUALS_','https://app.24ithub.com/pbx/agents','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','agents/edit','2025-10-03 16:24:04'),
(1366,'track','plg6mtt0fmqvrelqms6890l524ldv2u2','Openvoips Technologies','OV500','122.169.33.222','122.169.33.222','/pbx/reports/livecalls','https://app.24ithub.com/pbx/agents/edit/Mg_EQUALS__EQUALS_','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','reports/livecalls','2025-10-03 16:25:58'),
(1367,'track','plg6mtt0fmqvrelqms6890l524ldv2u2','Openvoips Technologies','OV500','122.169.33.222','122.169.33.222','/pbx/reports/api_livecall?action=search&call_flow=&max_rows=500&_=1759508758700','https://app.24ithub.com/pbx/reports/livecalls','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','reports/api_livecall','2025-10-03 16:25:59'),
(1368,'track','plg6mtt0fmqvrelqms6890l524ldv2u2','Openvoips Technologies','OV500','122.169.33.222','122.169.33.222','/pbx/reports/api_livecall?action=search&call_flow=&max_rows=500&_=1759508758701','https://app.24ithub.com/pbx/reports/livecalls','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','reports/api_livecall','2025-10-03 16:26:04'),
(1369,'track','plg6mtt0fmqvrelqms6890l524ldv2u2','Openvoips Technologies','OV500','122.169.33.222','122.169.33.222','/pbx/reports/api_livecall?action=search&call_flow=&max_rows=500&_=1759508758702','https://app.24ithub.com/pbx/reports/livecalls','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','reports/api_livecall','2025-10-03 16:26:12'),
(1370,'track','plg6mtt0fmqvrelqms6890l524ldv2u2','Openvoips Technologies','OV500','122.169.33.222','122.169.33.222','/pbx/reports/api_livecall?action=search&call_flow=&max_rows=500&_=1759508758703','https://app.24ithub.com/pbx/reports/livecalls','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','reports/api_livecall','2025-10-03 16:26:14'),
(1371,'track','plg6mtt0fmqvrelqms6890l524ldv2u2','Openvoips Technologies','OV500','122.169.33.222','122.169.33.222','/pbx/reports/api_livecall?action=search&call_flow=&max_rows=500&_=1759508758704','https://app.24ithub.com/pbx/reports/livecalls','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','reports/api_livecall','2025-10-03 16:26:19'),
(1372,'track','plg6mtt0fmqvrelqms6890l524ldv2u2','Openvoips Technologies','OV500','122.169.33.222','122.169.33.222','/pbx/reports/api_livecall?action=search&call_flow=&max_rows=500&_=1759508758705','https://app.24ithub.com/pbx/reports/livecalls','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','reports/api_livecall','2025-10-03 16:26:25'),
(1373,'track','plg6mtt0fmqvrelqms6890l524ldv2u2','Openvoips Technologies','OV500','122.169.33.222','122.169.33.222','/pbx/reports/api_livecall?action=search&call_flow=&max_rows=500&_=1759508758706','https://app.24ithub.com/pbx/reports/livecalls','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','reports/api_livecall','2025-10-03 16:26:29'),
(1374,'track','plg6mtt0fmqvrelqms6890l524ldv2u2','Openvoips Technologies','OV500','122.169.33.222','122.169.33.222','/pbx/reports/api_livecall?action=search&call_flow=&max_rows=500&_=1759508758707','https://app.24ithub.com/pbx/reports/livecalls','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','reports/api_livecall','2025-10-03 16:26:34'),
(1375,'track','plg6mtt0fmqvrelqms6890l524ldv2u2','Openvoips Technologies','OV500','122.169.33.222','122.169.33.222','/pbx/reports/api_livecall?action=search&call_flow=&max_rows=500&_=1759508758708','https://app.24ithub.com/pbx/reports/livecalls','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','reports/api_livecall','2025-10-03 16:26:39'),
(1376,'track','plg6mtt0fmqvrelqms6890l524ldv2u2','Openvoips Technologies','OV500','122.169.33.222','122.169.33.222','/pbx/reports/api_livecall?action=search&call_flow=&max_rows=500&_=1759508758709','https://app.24ithub.com/pbx/reports/livecalls','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','reports/api_livecall','2025-10-03 16:26:44'),
(1377,'track','plg6mtt0fmqvrelqms6890l524ldv2u2','Openvoips Technologies','OV500','122.169.33.222','122.169.33.222','/pbx/operatorpanal','https://app.24ithub.com/pbx/reports/livecalls','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','operatorpanal/index','2025-10-03 16:26:49'),
(1378,'track','plg6mtt0fmqvrelqms6890l524ldv2u2','Openvoips Technologies','OV500','122.169.33.222','122.169.33.222','/pbx/operatorpanal/ajax_get_call_data','https://app.24ithub.com/pbx/operatorpanal','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','operatorpanal/ajax_get_call_data','2025-10-03 16:26:55'),
(1379,'track','plg6mtt0fmqvrelqms6890l524ldv2u2','Openvoips Technologies','OV500','122.169.33.222','122.169.33.222','/pbx/operatorpanal/ajax_get_call_data','https://app.24ithub.com/pbx/operatorpanal','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','operatorpanal/ajax_get_call_data','2025-10-03 16:27:00'),
(1380,'track','plg6mtt0fmqvrelqms6890l524ldv2u2','Openvoips Technologies','OV500','122.169.33.222','122.169.33.222','/pbx/operatorpanal/ajax_get_call_data','https://app.24ithub.com/pbx/operatorpanal','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','operatorpanal/ajax_get_call_data','2025-10-03 16:27:06'),
(1381,'track','plg6mtt0fmqvrelqms6890l524ldv2u2','Openvoips Technologies','OV500','122.169.33.222','122.169.33.222','/pbx/operatorpanal/ajax_get_call_data','https://app.24ithub.com/pbx/operatorpanal','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','operatorpanal/ajax_get_call_data','2025-10-03 16:27:10'),
(1382,'track','plg6mtt0fmqvrelqms6890l524ldv2u2','Openvoips Technologies','OV500','122.169.33.222','122.169.33.222','/pbx/operatorpanal/ajax_get_call_data','https://app.24ithub.com/pbx/operatorpanal','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','operatorpanal/ajax_get_call_data','2025-10-03 16:27:16'),
(1383,'track','plg6mtt0fmqvrelqms6890l524ldv2u2','Openvoips Technologies','OV500','122.169.33.222','122.169.33.222','/pbx/operatorpanal/ajax_get_call_data','https://app.24ithub.com/pbx/operatorpanal','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','operatorpanal/ajax_get_call_data','2025-10-03 16:27:20'),
(1384,'track','plg6mtt0fmqvrelqms6890l524ldv2u2','Openvoips Technologies','OV500','122.169.33.222','122.169.33.222','/pbx/operatorpanal/ajax_get_call_data','https://app.24ithub.com/pbx/operatorpanal','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','operatorpanal/ajax_get_call_data','2025-10-03 16:27:26'),
(1385,'track','plg6mtt0fmqvrelqms6890l524ldv2u2','Openvoips Technologies','OV500','122.169.33.222','122.169.33.222','/pbx/operatorpanal/ajax_get_call_data','https://app.24ithub.com/pbx/operatorpanal','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','operatorpanal/ajax_get_call_data','2025-10-03 16:27:30'),
(1386,'track','plg6mtt0fmqvrelqms6890l524ldv2u2','Openvoips Technologies','OV500','122.169.33.222','122.169.33.222','/pbx/operatorpanal/ajax_get_call_data','https://app.24ithub.com/pbx/operatorpanal','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','operatorpanal/ajax_get_call_data','2025-10-03 16:27:36'),
(1387,'track','plg6mtt0fmqvrelqms6890l524ldv2u2','Openvoips Technologies','OV500','122.169.33.222','122.169.33.222','/pbx/operatorpanal/ajax_get_call_data','https://app.24ithub.com/pbx/operatorpanal','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','operatorpanal/ajax_get_call_data','2025-10-03 16:27:41'),
(1388,'track','plg6mtt0fmqvrelqms6890l524ldv2u2','Openvoips Technologies','OV500','122.169.33.222','122.169.33.222','/pbx/operatorpanal/ajax_get_call_data','https://app.24ithub.com/pbx/operatorpanal','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','operatorpanal/ajax_get_call_data','2025-10-03 16:27:46'),
(1389,'track','plg6mtt0fmqvrelqms6890l524ldv2u2','Openvoips Technologies','OV500','122.169.33.222','122.169.33.222','/pbx/operatorpanal/ajax_get_call_data','https://app.24ithub.com/pbx/operatorpanal','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','operatorpanal/ajax_get_call_data','2025-10-03 16:27:51'),
(1390,'track','plg6mtt0fmqvrelqms6890l524ldv2u2','Openvoips Technologies','OV500','122.169.33.222','122.169.33.222','/pbx/operatorpanal/ajax_get_call_data','https://app.24ithub.com/pbx/operatorpanal','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','operatorpanal/ajax_get_call_data','2025-10-03 16:27:56'),
(1391,'track','plg6mtt0fmqvrelqms6890l524ldv2u2','Openvoips Technologies','OV500','122.169.33.222','122.169.33.222','/pbx/operatorpanal/ajax_get_call_data','https://app.24ithub.com/pbx/operatorpanal','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','operatorpanal/ajax_get_call_data','2025-10-03 16:28:01'),
(1392,'track','plg6mtt0fmqvrelqms6890l524ldv2u2','Openvoips Technologies','OV500','122.169.33.222','122.169.33.222','/pbx/operatorpanal/ajax_get_call_data','https://app.24ithub.com/pbx/operatorpanal','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','operatorpanal/ajax_get_call_data','2025-10-03 16:28:06'),
(1393,'track','plg6mtt0fmqvrelqms6890l524ldv2u2','Openvoips Technologies','OV500','122.169.33.222','122.169.33.222','/pbx/operatorpanal/ajax_get_call_data','https://app.24ithub.com/pbx/operatorpanal','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','operatorpanal/ajax_get_call_data','2025-10-03 16:28:11'),
(1394,'track','plg6mtt0fmqvrelqms6890l524ldv2u2','Openvoips Technologies','OV500','122.169.33.222','122.169.33.222','/pbx/operatorpanal/ajax_get_call_data','https://app.24ithub.com/pbx/operatorpanal','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','operatorpanal/ajax_get_call_data','2025-10-03 16:28:16'),
(1395,'track','plg6mtt0fmqvrelqms6890l524ldv2u2','Openvoips Technologies','OV500','122.169.33.222','122.169.33.222','/pbx/operatorpanal/ajax_get_call_data','https://app.24ithub.com/pbx/operatorpanal','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','operatorpanal/ajax_get_call_data','2025-10-03 16:28:21'),
(1396,'track','plg6mtt0fmqvrelqms6890l524ldv2u2','Openvoips Technologies','OV500','122.169.33.222','122.169.33.222','/pbx/operatorpanal/ajax_get_call_data','https://app.24ithub.com/pbx/operatorpanal','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','operatorpanal/ajax_get_call_data','2025-10-03 16:28:27'),
(1397,'track','plg6mtt0fmqvrelqms6890l524ldv2u2','Openvoips Technologies','OV500','122.169.33.222','122.169.33.222','/pbx/operatorpanal/ajax_get_call_data','https://app.24ithub.com/pbx/operatorpanal','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','operatorpanal/ajax_get_call_data','2025-10-03 16:28:31'),
(1398,'track','plg6mtt0fmqvrelqms6890l524ldv2u2','Openvoips Technologies','OV500','122.169.33.222','122.169.33.222','/pbx/operatorpanal/ajax_get_call_data','https://app.24ithub.com/pbx/operatorpanal','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','operatorpanal/ajax_get_call_data','2025-10-03 16:28:37'),
(1399,'track','plg6mtt0fmqvrelqms6890l524ldv2u2','Openvoips Technologies','OV500','122.169.33.222','122.169.33.222','/pbx/operatorpanal/ajax_get_call_data','https://app.24ithub.com/pbx/operatorpanal','Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:143.0) Gecko/20100101 Firefox/143.0','operatorpanal/ajax_get_call_data','2025-10-03 16:28:42');
/*!40000 ALTER TABLE `webaccess_log` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `whitelisted_callerid`
--

DROP TABLE IF EXISTS `whitelisted_callerid`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `whitelisted_callerid` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `callerid` varchar(30) DEFAULT NULL,
  `account_id` varchar(30) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `callerid` (`callerid`,`account_id`) USING BTREE,
  KEY `account_id` (`account_id`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci ROW_FORMAT=COMPACT;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `whitelisted_callerid`
--

LOCK TABLES `whitelisted_callerid` WRITE;
/*!40000 ALTER TABLE `whitelisted_callerid` DISABLE KEYS */;
INSERT INTO `whitelisted_callerid` VALUES
(1,'12345678900','OV500');
/*!40000 ALTER TABLE `whitelisted_callerid` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping events for database 'switch'
--

--
-- Dumping routines for database 'switch'
--
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = '' */ ;
/*!50003 DROP PROCEDURE IF EXISTS `accountdelete` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb3 */ ;
/*!50003 SET character_set_results = utf8mb3 */ ;
/*!50003 SET collation_connection  = utf8mb3_general_ci */ ;
DELIMITER ;;
CREATE DEFINER=`root`@`localhost` PROCEDURE `accountdelete`(account varchar(30))
BEGIN
delete FROM users where account_id = account;
delete FROM account where account_id = account;
delete FROM customers where account_id = account;
delete FROM customer_voipminuts where account_id = account;
delete FROM customer_dialpattern where account_id = account;
delete FROM customer_callerid where account_id = account;
delete FROM customer_dialplan where account_id = account;
delete FROM customer_balance where account_id = account;
delete FROM customer_sip_account where account_id = account;
END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;

--
-- Final view structure for view `allnumbers`
--

/*!50001 DROP VIEW IF EXISTS `allnumbers`*/;
/*!50001 SET @saved_cs_client          = @@character_set_client */;
/*!50001 SET @saved_cs_results         = @@character_set_results */;
/*!50001 SET @saved_col_connection     = @@collation_connection */;
/*!50001 SET character_set_client      = utf8mb3 */;
/*!50001 SET character_set_results     = utf8mb3 */;
/*!50001 SET collation_connection      = utf8mb3_general_ci */;
/*!50001 CREATE ALGORITHM=UNDEFINED */
/*!50013 DEFINER=`root`@`localhost` SQL SECURITY DEFINER */
/*!50001 VIEW `allnumbers` AS select `audiofiles`.`audiofile_id` AS `username`,`audiofiles`.`account_id` AS `account_id`,`audiofiles`.`audio_name` AS `name`,`audiofiles`.`audiofile_id` AS `extension_id`,'' AS `extension_no`,'VOICEMESSAGE' AS `dst_type` from `audiofiles` union select `customer_devices`.`username` AS `username`,`customer_devices`.`account_id` AS `account_id`,`customer_devices`.`name` AS `name`,`customer_devices`.`extension_id` AS `extension_id`,`customer_devices`.`extension_no` AS `extension_no`,'EXTEN' AS `dst_type` from `customer_devices` where `customer_devices`.`user_type` = 'PBX' union select `ivrs`.`ivr_id` AS `ivr_id`,`ivrs`.`account_id` AS `account_id`,`ivrs`.`ivr_name` AS `ivr_name`,`ivrs`.`ivr_id` AS `ivr_id`,`ivrs`.`ivr_no` AS `ivr_no`,'IVR' AS `dst_type` from `ivrs` union select `ringgroup`.`ringgroup_id` AS `ringgroup_id`,`ringgroup`.`account_id` AS `account_id`,`ringgroup`.`ringgroup_name` AS `ringgroup_name`,`ringgroup`.`ringgroup_id` AS `ringgroup_id`,`ringgroup`.`ringgroup_number` AS `ringgroup_number`,'GROUP' AS `dst_type` from `ringgroup` union select `announcement`.`announcement_id` AS `announcement_id`,`announcement`.`account_id` AS `account_id`,`announcement`.`annumcement_name` AS `annumcement_name`,`announcement`.`announcement_id` AS `announcement_id`,`announcement`.`announcement_no` AS `announcement_no`,'ANNOUNCEMENT' AS `dst_type` from `announcement` union select `conferences`.`conference_id` AS `conference_id`,`conferences`.`account_id` AS `account_id`,`conferences`.`conference_name` AS `conference_name`,`conferences`.`conference_id` AS `conference_id`,`conferences`.`conference_no` AS `conference_no`,'CONF' AS `dst_type` from `conferences` union select `timeconditions`.`timeconditions_id` AS `timeconditions_id`,`timeconditions`.`account_id` AS `account_id`,`timeconditions`.`timeconditions_name` AS `timeconditions_name`,`timeconditions`.`timeconditions_id` AS `timeconditions_id`,`timeconditions`.`timeconditions_number` AS `timeconditions_number`,'TIMEROUTE' AS `dst_type` from `timeconditions` union select `voicemail`.`mailbox` AS `mailbox`,`voicemail`.`account_id` AS `account_id`,`voicemail`.`vm_name` AS `vm_name`,`voicemail`.`mailbox` AS `mailbox`,`voicemail`.`vm_no` AS `vm_no`,'VOICEMAIL' AS `dst_type` from `voicemail` union select `queue`.`queue_fs_name` AS `queue_fs_name`,`queue`.`account_id` AS `account_id`,`queue`.`queue_name` AS `queue_name`,`queue`.`queue_fs_name` AS `queue_fs_name`,`queue`.`queue_number` AS `queue_number`,'QUEUE' AS `dst_type` from `queue` union select `feature_code`.`dialcode` AS `dialcode`,`feature_code`.`account_id` AS `account_id`,`feature_code`.`feature_id` AS `feature_id`,`feature_code`.`feature_id` AS `feature_id`,`feature_code`.`dialcode` AS `dialcode`,'DIALCODE' AS `dst_type` from `feature_code` */;
/*!50001 SET character_set_client      = @saved_cs_client */;
/*!50001 SET character_set_results     = @saved_cs_results */;
/*!50001 SET collation_connection      = @saved_col_connection */;

--
-- Final view structure for view `allnumbersall`
--

/*!50001 DROP VIEW IF EXISTS `allnumbersall`*/;
/*!50001 SET @saved_cs_client          = @@character_set_client */;
/*!50001 SET @saved_cs_results         = @@character_set_results */;
/*!50001 SET @saved_col_connection     = @@collation_connection */;
/*!50001 SET character_set_client      = utf8mb3 */;
/*!50001 SET character_set_results     = utf8mb3 */;
/*!50001 SET collation_connection      = utf8mb3_general_ci */;
/*!50001 CREATE ALGORITHM=UNDEFINED */
/*!50013 DEFINER=`root`@`localhost` SQL SECURITY DEFINER */
/*!50001 VIEW `allnumbersall` AS select `customer_devices`.`username` AS `username`,`customer_devices`.`account_id` AS `account_id`,`customer_devices`.`name` AS `name`,`customer_devices`.`extension_id` AS `extension_id`,`customer_devices`.`extension_no` AS `extension_no`,'EXTEN' AS `dst_type` from `customer_devices` where `customer_devices`.`user_type` = 'PBX' union select `ivrs`.`ivr_id` AS `ivr_id`,`ivrs`.`account_id` AS `account_id`,`ivrs`.`ivr_name` AS `ivr_name`,`ivrs`.`ivr_id` AS `ivr_id`,`ivrs`.`ivr_no` AS `ivr_no`,'IVR' AS `dst_type` from `ivrs` union select `ringgroup`.`ringgroup_id` AS `ringgroup_id`,`ringgroup`.`account_id` AS `account_id`,`ringgroup`.`ringgroup_name` AS `ringgroup_name`,`ringgroup`.`ringgroup_id` AS `ringgroup_id`,`ringgroup`.`ringgroup_number` AS `ringgroup_number`,'GROUP' AS `dst_type` from `ringgroup` union select `announcement`.`announcement_id` AS `announcement_id`,`announcement`.`account_id` AS `account_id`,`announcement`.`annumcement_name` AS `annumcement_name`,`announcement`.`announcement_id` AS `announcement_id`,`announcement`.`announcement_no` AS `announcement_no`,'ANNOUNCEMENT' AS `dst_type` from `announcement` union select `conferences`.`conference_id` AS `conference_id`,`conferences`.`account_id` AS `account_id`,`conferences`.`conference_name` AS `conference_name`,`conferences`.`conference_id` AS `conference_id`,`conferences`.`conference_no` AS `conference_no`,'CONF' AS `dst_type` from `conferences` union select `timeconditions`.`timeconditions_id` AS `timeconditions_id`,`timeconditions`.`account_id` AS `account_id`,`timeconditions`.`timeconditions_name` AS `timeconditions_name`,`timeconditions`.`timeconditions_id` AS `timeconditions_id`,`timeconditions`.`timeconditions_number` AS `timeconditions_number`,'TIMEROUTE' AS `dst_type` from `timeconditions` union select `voicemail`.`mailbox` AS `mailbox`,`voicemail`.`account_id` AS `account_id`,`voicemail`.`vm_name` AS `vm_name`,`voicemail`.`mailbox` AS `mailbox`,`voicemail`.`vm_no` AS `vm_no`,'VOICEMAIL' AS `dst_type` from `voicemail` union select `queue`.`queue_fs_name` AS `queue_fs_name`,`queue`.`account_id` AS `account_id`,`queue`.`queue_name` AS `queue_name`,`queue`.`queue_fs_name` AS `queue_fs_name`,`queue`.`queue_number` AS `queue_number`,'QUEUE' AS `dst_type` from `queue` union select `feature_code`.`dialcode` AS `dialcode`,`feature_code`.`account_id` AS `account_id`,`feature_code`.`feature_id` AS `feature_id`,`feature_code`.`feature_id` AS `feature_id`,`feature_code`.`dialcode` AS `dialcode`,'DIALCODE' AS `dst_type` from `feature_code` union select `did`.`did_number` AS `did_number`,`did`.`account_id` AS `account_id`,`did`.`did_name` AS `did_name`,`did`.`did_number` AS `did_number`,`did`.`did_number` AS `did_number`,'DID' AS `dst_type` from `did` */;
/*!50001 SET character_set_client      = @saved_cs_client */;
/*!50001 SET character_set_results     = @saved_cs_results */;
/*!50001 SET collation_connection      = @saved_col_connection */;

--
-- Final view structure for view `new_did`
--

/*!50001 DROP VIEW IF EXISTS `new_did`*/;
/*!50001 SET @saved_cs_client          = @@character_set_client */;
/*!50001 SET @saved_cs_results         = @@character_set_results */;
/*!50001 SET @saved_col_connection     = @@collation_connection */;
/*!50001 SET character_set_client      = utf8mb3 */;
/*!50001 SET character_set_results     = utf8mb3 */;
/*!50001 SET collation_connection      = utf8mb3_general_ci */;
/*!50001 CREATE ALGORITHM=UNDEFINED */
/*!50013 DEFINER=`root`@`localhost` SQL SECURITY DEFINER */
/*!50001 VIEW `new_did` AS select `did`.`did_id` AS `did_id`,`did`.`did_number` AS `did_number`,`did`.`did_status` AS `did_status`,`did`.`carrier_id` AS `carrier_id`,`did`.`account_id` AS `account_id`,`did`.`assign_date` AS `assign_date`,`did`.`reseller1_account_id` AS `reseller1_account_id`,`did`.`reseller1_assign_date` AS `reseller1_assign_date`,`did`.`reseller2_account_id` AS `reseller2_account_id`,`did`.`reseller2_assign_date` AS `reseller2_assign_date`,`did`.`reseller3_account_id` AS `reseller3_account_id`,`did`.`reseller3_assign_date` AS `reseller3_assign_date`,`did`.`create_date` AS `create_date`,`did`.`channels` AS `channels`,`did`.`did_name` AS `did_name`,`did`.`number_type` AS `number_type`,`usa_area_codes`.`area_code` AS `area_code`,`usa_area_codes`.`city` AS `city`,`usa_area_codes`.`state` AS `state` from (`did` join `usa_area_codes`) where `did`.`did_number` like concat(`usa_area_codes`.`area_code`,'%') and `did`.`did_status` = 'NEW' group by `usa_area_codes`.`area_code` */;
/*!50001 SET character_set_client      = @saved_cs_client */;
/*!50001 SET character_set_results     = @saved_cs_results */;
/*!50001 SET collation_connection      = @saved_col_connection */;

--
-- Final view structure for view `openipaddress`
--

/*!50001 DROP VIEW IF EXISTS `openipaddress`*/;
/*!50001 SET @saved_cs_client          = @@character_set_client */;
/*!50001 SET @saved_cs_results         = @@character_set_results */;
/*!50001 SET @saved_col_connection     = @@collation_connection */;
/*!50001 SET character_set_client      = utf8mb3 */;
/*!50001 SET character_set_results     = utf8mb3 */;
/*!50001 SET collation_connection      = utf8mb3_general_ci */;
/*!50001 CREATE ALGORITHM=UNDEFINED */
/*!50013 DEFINER=`root`@`localhost` SQL SECURITY DEFINER */
/*!50001 VIEW `openipaddress` AS select `customer_ips`.`ipaddress` AS `ipaddress`,'customerips' AS `fromip` from `customer_ips` union select `carrier_ips`.`ipaddress` AS `ipaddress`,'carrierips' AS `fromip` from `carrier_ips` */;
/*!50001 SET character_set_client      = @saved_cs_client */;
/*!50001 SET character_set_results     = @saved_cs_results */;
/*!50001 SET collation_connection      = @saved_col_connection */;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2025-10-03 12:28:42
