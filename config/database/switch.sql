/*
Navicat MySQL Data Transfer

Source Server         : 151.106.4.238 Dil
Source Server Version : 50568
Source Host           : localhost:3306
Source Database       : switch

Target Server Type    : MYSQL
Target Server Version : 50568
File Encoding         : 65001

Date: 2022-08-19 16:51:46
*/

SET FOREIGN_KEY_CHECKS=0;

-- ----------------------------
-- Table structure for `account`
-- ----------------------------
DROP TABLE IF EXISTS `account`;
CREATE TABLE `account` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `account_id` varchar(30) DEFAULT NULL,
  `parent_account_id` varchar(30) NOT NULL,
  `status_id` enum('1','0','-1','-2','-3','-4') DEFAULT '-1' COMMENT '0=inactive, -2=suspended',
  `account_type` enum('CUSTOMER','RESELLER') DEFAULT 'CUSTOMER',
  `account_level` smallint(6) unsigned DEFAULT NULL COMMENT '0',
  `dp` tinyint(1) DEFAULT '4',
  `currency_id` int(11) DEFAULT '1',
  `account_cc` int(11) DEFAULT '1',
  `account_cps` int(11) DEFAULT NULL,
  `tax_number` varchar(30) DEFAULT NULL,
  `tax_type` enum('inclusive','exclusive') DEFAULT 'exclusive',
  `vat_flag` enum('NONE','TAX','VAT') DEFAULT 'NONE',
  `tax1` double(8,4) DEFAULT NULL,
  `tax2` double(8,4) DEFAULT NULL,
  `tax3` double(8,4) DEFAULT NULL,
  `cli_check` int(11) DEFAULT '0',
  `dialpattern_check` enum('1','0') DEFAULT '0',
  `llr_check` enum('1','0') DEFAULT '0',
  `account_codecs` varchar(255) DEFAULT 'G729,PCMU,PCMA,G722',
  `media_transcoding` enum('1','0') DEFAULT '1',
  `media_rtpproxy` enum('1','0') DEFAULT '0',
  `force_dst_src_cli_prefix` enum('1','0') DEFAULT '0',
  `codecs_force` enum('1','0') DEFAULT '0',
  `max_callduration` int(11) DEFAULT '30',
  `round_logic` enum('CEIL','ROUND') DEFAULT NULL,
  `create_dt` datetime NOT NULL,
  `create_by` varchar(30) NOT NULL,
  `update_dt` timestamp NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  `update_by` varchar(30) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=latin1;

-- ----------------------------
-- Records of account
-- ----------------------------
INSERT INTO `account` VALUES ('2', 'STR100000', '', '1', 'RESELLER', '1', '2', '2', '2', '2', '1222', 'exclusive', 'TAX', '2.0000', '2.0000', '2.0000', '0', '0', '0', 'G729,PCMU,PCMA,G722', '0', '0', '0', '0', '30', null, '0000-00-00 00:00:00', '', '2021-07-31 13:05:29', '');
INSERT INTO `account` VALUES ('4', 'STC300000', '', '1', 'CUSTOMER', '1', '4', '4', '10', '1', '', 'exclusive', 'NONE', '0.0000', '0.0000', '0.0000', '0', '0', '0', 'G729,PCMU,PCMA,G722', '0', '0', '0', '0', '30', null, '2021-07-31 00:00:00', '', null, '');

-- ----------------------------
-- Table structure for `account_am`
-- ----------------------------
DROP TABLE IF EXISTS `account_am`;
CREATE TABLE `account_am` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `customer_account_id` varchar(30) DEFAULT NULL,
  `account_manager` varchar(30) DEFAULT NULL,
  `account_id` varchar(30) DEFAULT NULL,
  `created_dt` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `account_id` (`account_id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=latin1;

-- ----------------------------
-- Records of account_am
-- ----------------------------
INSERT INTO `account_am` VALUES ('1', 'STR100000', 'UA000250803', 'SYSTEM', '2021-07-31 12:30:32');

-- ----------------------------
-- Table structure for `account_card_details`
-- ----------------------------
DROP TABLE IF EXISTS `account_card_details`;
CREATE TABLE `account_card_details` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `account_id` varchar(30) NOT NULL,
  `card_name` varchar(30) NOT NULL,
  `card_data` text NOT NULL,
  `dt_updated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- ----------------------------
-- Records of account_card_details
-- ----------------------------

-- ----------------------------
-- Table structure for `account_payment_credentials`
-- ----------------------------
DROP TABLE IF EXISTS `account_payment_credentials`;
CREATE TABLE `account_payment_credentials` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `account_id` varchar(30) NOT NULL,
  `payment_method` enum('paypal-client','paypal-sdk','ccavenue','secure-trading') NOT NULL,
  `credentials` text NOT NULL,
  `status` enum('Y','N') NOT NULL DEFAULT 'Y',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- ----------------------------
-- Records of account_payment_credentials
-- ----------------------------

-- ----------------------------
-- Table structure for `activity_api_log`
-- ----------------------------
DROP TABLE IF EXISTS `activity_api_log`;
CREATE TABLE `activity_api_log` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `request_data` text NOT NULL,
  `response_data` text NOT NULL,
  `function_return` text NOT NULL,
  `create_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- ----------------------------
-- Records of activity_api_log
-- ----------------------------

-- ----------------------------
-- Table structure for `activity_log`
-- ----------------------------
DROP TABLE IF EXISTS `activity_log`;
CREATE TABLE `activity_log` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `activity_id` int(11) DEFAULT NULL,
  `activity_type` varchar(20) DEFAULT NULL,
  `sql_table` varchar(50) DEFAULT NULL,
  `sql_key` varchar(255) DEFAULT NULL,
  `sql_query` text,
  `account_id` varchar(30) DEFAULT NULL,
  `dt_created` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=102 DEFAULT CHARSET=latin1;

-- ----------------------------
-- Records of activity_log
-- ----------------------------
INSERT INTO `activity_log` VALUES ('97', '1', 'insert', 'tariff_ratecard_map', '', 'INSERT INTO `tariff_ratecard_map` (`ratecard_id`, `tariff_id`, `start_day`, `start_time`, `end_day`, `end_time`, `priority`, `status`, `ratecard_for`) VALUES (\'CARRIER47\', \'CARRIER40\', \'0\', \'00:00:00\', \'6\', \'23:59:59\', \'1\', \'1\', \'OUTGOING\')', 'ADMIN', '2021-08-04 22:35:21');
INSERT INTO `activity_log` VALUES ('98', '2', 'insert', 'tariff_ratecard_map', '', 'INSERT INTO `tariff_ratecard_map` (`ratecard_id`, `tariff_id`, `start_day`, `start_time`, `end_day`, `end_time`, `priority`, `status`, `ratecard_for`) VALUES (\'DIDRATES41\', \'CARRIER40\', \'0\', \'00:00:00\', \'6\', \'23:59:59\', \'1\', \'1\', \'INCOMING\')', 'ADMIN', '2021-08-04 22:35:27');
INSERT INTO `activity_log` VALUES ('99', '3', 'insert', 'tariff_ratecard_map', '', 'INSERT INTO `tariff_ratecard_map` (`ratecard_id`, `tariff_id`, `start_day`, `start_time`, `end_day`, `end_time`, `priority`, `status`, `ratecard_for`) VALUES (\'CUSTOMER42\', \'CUSTOMER54\', \'0\', \'00:00:00\', \'6\', \'23:59:59\', \'1\', \'1\', \'OUTGOING\')', 'ADMIN', '2021-08-04 22:35:39');
INSERT INTO `activity_log` VALUES ('100', '4', 'insert', 'tariff_ratecard_map', '', 'INSERT INTO `tariff_ratecard_map` (`ratecard_id`, `tariff_id`, `start_day`, `start_time`, `end_day`, `end_time`, `priority`, `status`, `ratecard_for`) VALUES (\'DIDRATES16\', \'CUSTOMER54\', \'0\', \'00:00:00\', \'6\', \'23:59:59\', \'1\', \'1\', \'INCOMING\')', 'ADMIN', '2021-08-04 22:35:51');
INSERT INTO `activity_log` VALUES ('101', '5', 'update', 'users', ' user_id=\'ADMIN\'', 'UPDATE `users` SET `secret` = \'Ov500@786\', `user_type` = \'ADMIN\', `name` = \'Open Voips\', `emailaddress` = \'openvoips@gmail.com\', `address` = \'India\', `phone` = \'919949800228\', `country_id` = \'100\', `status_id` = \'1\', `update_dt` = \'\', `update_by` = \'ADMIN\'\nWHERE `user_id` = \'ADMIN\'', 'ADMIN', '2022-08-19 14:22:08');

-- ----------------------------
-- Table structure for `activity_site_log`
-- ----------------------------
DROP TABLE IF EXISTS `activity_site_log`;
CREATE TABLE `activity_site_log` (
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
  `created_dt` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=10686 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of activity_site_log
-- ----------------------------
INSERT INTO `activity_site_log` VALUES ('10444', 'track', 'ltc7mk78rrucl2knl6jcuun506evkm7t', '', '', '103.94.84.37', '103.94.84.37', '/portal/', '', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:103.0) Gecko/20100101 Firefox/103.0', 'login/index', '2022-08-19 13:16:52');
INSERT INTO `activity_site_log` VALUES ('10445', 'track', '8b4fp0hss097ci92fdc9bpvfrij0gmpm', '', '', '103.94.84.37', '103.94.84.37', '/portal/login', 'http://151.106.4.238/portal/', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:103.0) Gecko/20100101 Firefox/103.0', 'login/index', '2022-08-19 13:24:58');
INSERT INTO `activity_site_log` VALUES ('10446', 'track', '8b4fp0hss097ci92fdc9bpvfrij0gmpm', 'Open Voips', 'SYSTEM', '103.94.84.37', '103.94.84.37', '/portal/dashboard', '', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:103.0) Gecko/20100101 Firefox/103.0', 'dashboard/index', '2022-08-19 13:24:58');
INSERT INTO `activity_site_log` VALUES ('10447', 'track', '8b4fp0hss097ci92fdc9bpvfrij0gmpm', 'Open Voips', 'SYSTEM', '103.94.84.37', '103.94.84.37', '/portal/reports/monin', 'http://151.106.4.238/portal/dashboard', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:103.0) Gecko/20100101 Firefox/103.0', 'reports/monin', '2022-08-19 13:25:01');
INSERT INTO `activity_site_log` VALUES ('10448', 'track', '8b4fp0hss097ci92fdc9bpvfrij0gmpm', 'Open Voips', 'SYSTEM', '103.94.84.37', '103.94.84.37', '/portal/reports/monin_data/N/N/N/N/Y/Y/N/N/N/Y', 'http://151.106.4.238/portal/reports/monin', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:103.0) Gecko/20100101 Firefox/103.0', 'reports/monin_data', '2022-08-19 13:25:02');
INSERT INTO `activity_site_log` VALUES ('10449', 'track', '8b4fp0hss097ci92fdc9bpvfrij0gmpm', 'Open Voips', 'SYSTEM', '103.94.84.37', '103.94.84.37', '/portal/reports/monin_data/N/N/N/N/N/N/N/Y/Y', 'http://151.106.4.238/portal/reports/monin', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:103.0) Gecko/20100101 Firefox/103.0', 'reports/monin_data', '2022-08-19 13:25:02');
INSERT INTO `activity_site_log` VALUES ('10450', 'track', '8b4fp0hss097ci92fdc9bpvfrij0gmpm', 'Open Voips', 'SYSTEM', '103.94.84.37', '103.94.84.37', '/portal/reports/monin_data/Y/Y/Y/Y/N/N/Y', 'http://151.106.4.238/portal/reports/monin', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:103.0) Gecko/20100101 Firefox/103.0', 'reports/monin_data', '2022-08-19 13:25:02');
INSERT INTO `activity_site_log` VALUES ('10451', 'track', '8b4fp0hss097ci92fdc9bpvfrij0gmpm', 'Open Voips', 'SYSTEM', '103.94.84.37', '103.94.84.37', '/portal/ratecard', 'http://151.106.4.238/portal/reports/monin', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:103.0) Gecko/20100101 Firefox/103.0', 'ratecard/index', '2022-08-19 13:25:04');
INSERT INTO `activity_site_log` VALUES ('10452', 'track', '8b4fp0hss097ci92fdc9bpvfrij0gmpm', 'Open Voips', 'SYSTEM', '103.94.84.37', '103.94.84.37', '/portal/ratecard', 'http://151.106.4.238/portal/ratecard', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:103.0) Gecko/20100101 Firefox/103.0', 'ratecard/index', '2022-08-19 13:25:07');
INSERT INTO `activity_site_log` VALUES ('10453', 'track', '8b4fp0hss097ci92fdc9bpvfrij0gmpm', 'Open Voips', 'SYSTEM', '103.94.84.37', '103.94.84.37', '/portal/rates', 'http://151.106.4.238/portal/ratecard', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:103.0) Gecko/20100101 Firefox/103.0', 'rates/index', '2022-08-19 13:25:15');
INSERT INTO `activity_site_log` VALUES ('10454', 'track', '8b4fp0hss097ci92fdc9bpvfrij0gmpm', 'Open Voips', 'SYSTEM', '103.94.84.37', '103.94.84.37', '/portal/ratecard', 'http://151.106.4.238/portal/rates', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:103.0) Gecko/20100101 Firefox/103.0', 'ratecard/index', '2022-08-19 13:25:17');
INSERT INTO `activity_site_log` VALUES ('10455', 'track', '8b4fp0hss097ci92fdc9bpvfrij0gmpm', 'Open Voips', 'SYSTEM', '103.94.84.37', '103.94.84.37', '/portal/ratecard', 'http://151.106.4.238/portal/ratecard', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:103.0) Gecko/20100101 Firefox/103.0', 'ratecard/index', '2022-08-19 13:25:24');
INSERT INTO `activity_site_log` VALUES ('10456', 'track', '8b4fp0hss097ci92fdc9bpvfrij0gmpm', 'Open Voips', 'SYSTEM', '103.94.84.37', '103.94.84.37', '/portal/ratecard', 'http://151.106.4.238/portal/ratecard', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:103.0) Gecko/20100101 Firefox/103.0', 'ratecard/index', '2022-08-19 13:25:24');
INSERT INTO `activity_site_log` VALUES ('10457', 'track', '8b4fp0hss097ci92fdc9bpvfrij0gmpm', 'Open Voips', 'SYSTEM', '103.94.84.37', '103.94.84.37', '/portal/ratecard', 'http://151.106.4.238/portal/ratecard', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:103.0) Gecko/20100101 Firefox/103.0', 'ratecard/index', '2022-08-19 13:25:27');
INSERT INTO `activity_site_log` VALUES ('10458', 'track', '8b4fp0hss097ci92fdc9bpvfrij0gmpm', 'Open Voips', 'SYSTEM', '103.94.84.37', '103.94.84.37', '/portal/ratecard', 'http://151.106.4.238/portal/ratecard', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:103.0) Gecko/20100101 Firefox/103.0', 'ratecard/index', '2022-08-19 13:25:27');
INSERT INTO `activity_site_log` VALUES ('10459', 'track', '8b4fp0hss097ci92fdc9bpvfrij0gmpm', 'Open Voips', 'SYSTEM', '103.94.84.37', '103.94.84.37', '/portal/tariffs', 'http://151.106.4.238/portal/ratecard', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:103.0) Gecko/20100101 Firefox/103.0', 'tariffs/index', '2022-08-19 13:25:31');
INSERT INTO `activity_site_log` VALUES ('10460', 'track', '8b4fp0hss097ci92fdc9bpvfrij0gmpm', 'Open Voips', 'SYSTEM', '103.94.84.37', '103.94.84.37', '/portal/users', 'http://151.106.4.238/portal/tariffs', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:103.0) Gecko/20100101 Firefox/103.0', 'users/index', '2022-08-19 13:25:35');
INSERT INTO `activity_site_log` VALUES ('10461', 'track', '8b4fp0hss097ci92fdc9bpvfrij0gmpm', 'Open Voips', 'SYSTEM', '103.94.84.37', '103.94.84.37', '/portal/crs', 'http://151.106.4.238/portal/users', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:103.0) Gecko/20100101 Firefox/103.0', 'crs/index', '2022-08-19 13:25:36');
INSERT INTO `activity_site_log` VALUES ('10462', 'track', '8b4fp0hss097ci92fdc9bpvfrij0gmpm', 'Open Voips', 'SYSTEM', '103.94.84.37', '103.94.84.37', '/portal/users', 'http://151.106.4.238/portal/crs', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:103.0) Gecko/20100101 Firefox/103.0', 'users/index', '2022-08-19 13:25:38');
INSERT INTO `activity_site_log` VALUES ('10463', 'track', '8b4fp0hss097ci92fdc9bpvfrij0gmpm', 'Open Voips', 'SYSTEM', '103.94.84.37', '103.94.84.37', '/portal/reports/AnsCalls', 'http://151.106.4.238/portal/users', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:103.0) Gecko/20100101 Firefox/103.0', 'reports/AnsCalls', '2022-08-19 13:25:54');
INSERT INTO `activity_site_log` VALUES ('10464', 'track', '8b4fp0hss097ci92fdc9bpvfrij0gmpm', 'Open Voips', 'SYSTEM', '103.94.84.37', '103.94.84.37', '/portal/crs', 'http://151.106.4.238/portal/reports/AnsCalls', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:103.0) Gecko/20100101 Firefox/103.0', 'crs/index', '2022-08-19 13:25:58');
INSERT INTO `activity_site_log` VALUES ('10465', 'track', '8b4fp0hss097ci92fdc9bpvfrij0gmpm', 'Open Voips', 'SYSTEM', '103.94.84.37', '103.94.84.37', '/portal/reports/monin', 'http://151.106.4.238/portal/crs', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:103.0) Gecko/20100101 Firefox/103.0', 'reports/monin', '2022-08-19 13:26:14');
INSERT INTO `activity_site_log` VALUES ('10466', 'track', '8b4fp0hss097ci92fdc9bpvfrij0gmpm', 'Open Voips', 'SYSTEM', '103.94.84.37', '103.94.84.37', '/portal/reports/monin_data/N/N/N/N/Y/Y/N/N/N/Y', 'http://151.106.4.238/portal/reports/monin', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:103.0) Gecko/20100101 Firefox/103.0', 'reports/monin_data', '2022-08-19 13:26:14');
INSERT INTO `activity_site_log` VALUES ('10467', 'track', '8b4fp0hss097ci92fdc9bpvfrij0gmpm', 'Open Voips', 'SYSTEM', '103.94.84.37', '103.94.84.37', '/portal/reports/monin_data/N/N/N/N/N/N/N/Y/Y', 'http://151.106.4.238/portal/reports/monin', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:103.0) Gecko/20100101 Firefox/103.0', 'reports/monin_data', '2022-08-19 13:26:14');
INSERT INTO `activity_site_log` VALUES ('10468', 'track', '8b4fp0hss097ci92fdc9bpvfrij0gmpm', 'Open Voips', 'SYSTEM', '103.94.84.37', '103.94.84.37', '/portal/reports/monin_data/Y/Y/Y/Y/N/N/Y', 'http://151.106.4.238/portal/reports/monin', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:103.0) Gecko/20100101 Firefox/103.0', 'reports/monin_data', '2022-08-19 13:26:14');
INSERT INTO `activity_site_log` VALUES ('10469', 'track', '8b4fp0hss097ci92fdc9bpvfrij0gmpm', 'Open Voips', 'SYSTEM', '103.94.84.37', '103.94.84.37', '/portal/ratecard', 'http://151.106.4.238/portal/reports/monin', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:103.0) Gecko/20100101 Firefox/103.0', 'ratecard/index', '2022-08-19 13:26:17');
INSERT INTO `activity_site_log` VALUES ('10470', 'track', '8b4fp0hss097ci92fdc9bpvfrij0gmpm', 'Open Voips', 'SYSTEM', '103.94.84.37', '103.94.84.37', '/portal/rates', 'http://151.106.4.238/portal/ratecard', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:103.0) Gecko/20100101 Firefox/103.0', 'rates/index', '2022-08-19 13:26:23');
INSERT INTO `activity_site_log` VALUES ('10471', 'track', '8b4fp0hss097ci92fdc9bpvfrij0gmpm', 'Open Voips', 'SYSTEM', '103.94.84.37', '103.94.84.37', '/portal/tariffs/apiTM/CUSTOMER54?_=1660895786816', 'http://151.106.4.238/portal/rates', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:103.0) Gecko/20100101 Firefox/103.0', 'tariffs/apiTM', '2022-08-19 13:26:28');
INSERT INTO `activity_site_log` VALUES ('10472', 'track', '8b4fp0hss097ci92fdc9bpvfrij0gmpm', 'Open Voips', 'SYSTEM', '103.94.84.37', '103.94.84.37', '/portal/rates/index/', 'http://151.106.4.238/portal/rates', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:103.0) Gecko/20100101 Firefox/103.0', 'rates/index', '2022-08-19 13:26:29');
INSERT INTO `activity_site_log` VALUES ('10473', 'track', '8b4fp0hss097ci92fdc9bpvfrij0gmpm', 'Open Voips', 'SYSTEM', '103.94.84.37', '103.94.84.37', '/portal/tariffs/apiTM/CUSTOMER54?_=1660895792630', 'http://151.106.4.238/portal/rates/index/', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:103.0) Gecko/20100101 Firefox/103.0', 'tariffs/apiTM', '2022-08-19 13:26:33');
INSERT INTO `activity_site_log` VALUES ('10474', 'track', '8b4fp0hss097ci92fdc9bpvfrij0gmpm', 'Open Voips', 'SYSTEM', '103.94.84.37', '103.94.84.37', '/portal/rates/index/', 'http://151.106.4.238/portal/rates/index/', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:103.0) Gecko/20100101 Firefox/103.0', 'rates/index', '2022-08-19 13:26:35');
INSERT INTO `activity_site_log` VALUES ('10475', 'track', '8b4fp0hss097ci92fdc9bpvfrij0gmpm', 'Open Voips', 'SYSTEM', '103.94.84.37', '103.94.84.37', '/portal/rates/index/', 'http://151.106.4.238/portal/rates/index/', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:103.0) Gecko/20100101 Firefox/103.0', 'rates/index', '2022-08-19 13:26:41');
INSERT INTO `activity_site_log` VALUES ('10476', 'track', '8b4fp0hss097ci92fdc9bpvfrij0gmpm', 'Open Voips', 'SYSTEM', '103.94.84.37', '103.94.84.37', '/portal/rates/index/', 'http://151.106.4.238/portal/rates/index/', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:103.0) Gecko/20100101 Firefox/103.0', 'rates/index', '2022-08-19 13:26:47');
INSERT INTO `activity_site_log` VALUES ('10477', 'track', 's2d2hkq0r76c04bdne7k4o5imlj3gul2', 'Open Voips', 'SYSTEM', '103.94.84.37', '103.94.84.37', '/portal/crs', 'http://151.106.4.238/portal/rates/index/', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:103.0) Gecko/20100101 Firefox/103.0', 'crs/index', '2022-08-19 13:34:24');
INSERT INTO `activity_site_log` VALUES ('10478', 'track', 's2d2hkq0r76c04bdne7k4o5imlj3gul2', 'Open Voips', 'SYSTEM', '103.94.84.37', '103.94.84.37', '/portal/crs/editvoip/U1RDMzAwMDAw', 'http://151.106.4.238/portal/crs', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:103.0) Gecko/20100101 Firefox/103.0', 'crs/editvoip', '2022-08-19 13:34:28');
INSERT INTO `activity_site_log` VALUES ('10479', 'track', 's2d2hkq0r76c04bdne7k4o5imlj3gul2', 'Open Voips', 'SYSTEM', '103.94.84.37', '103.94.84.37', '/portal/%20theme/vendors/combo-box-typeahead/css/bootstrap-combobox.css', 'http://151.106.4.238/portal/crs/editvoip/U1RDMzAwMDAw', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:103.0) Gecko/20100101 Firefox/103.0', '/index', '2022-08-19 13:34:29');
INSERT INTO `activity_site_log` VALUES ('10480', 'track', 's2d2hkq0r76c04bdne7k4o5imlj3gul2', 'Open Voips', 'SYSTEM', '103.94.84.37', '103.94.84.37', '/portal/%20theme/vendors/combo-box-typeahead/css/bootstrap-combobox.css', 'http://151.106.4.238/portal/crs/editvoip/U1RDMzAwMDAw', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:103.0) Gecko/20100101 Firefox/103.0', '/index', '2022-08-19 13:34:29');
INSERT INTO `activity_site_log` VALUES ('10481', 'track', '774gr5fjvojllbshhd19d779fs9e2jpk', '', '', '::1', '::1', '/portal/api/cdr.php?uuid=a_31d12075-dba7-44a2-92bd-a38fc3d73909', '', 'freeswitch-xml/1.0', '/index', '2022-08-19 13:55:45');
INSERT INTO `activity_site_log` VALUES ('10482', 'track', 'sll7dupr83vr47dgrhkd7sf5pvobsdt0', '', '', '::1', '::1', '/portal/api/cdr.php?uuid=a_47329447-4c14-4228-a0fb-df2404722ce6', '', 'freeswitch-xml/1.0', '/index', '2022-08-19 13:57:22');
INSERT INTO `activity_site_log` VALUES ('10483', 'track', 'ma5u0b236f9dkl8mou410gqu6vqla447', '', '', '::1', '::1', '/portal/api/cdr.php?uuid=a_52335253-9966-4d38-acd1-3daa8cb5b307', '', 'freeswitch-xml/1.0', '/index', '2022-08-19 13:58:00');
INSERT INTO `activity_site_log` VALUES ('10484', 'track', 'kfir2nk0s7o0gatch72en8nhdj6eoulj', 'Open Voips', 'SYSTEM', '103.94.84.37', '103.94.84.37', '/portal/reports/FailCalls', 'http://151.106.4.238/portal/crs/editvoip/U1RDMzAwMDAw', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:103.0) Gecko/20100101 Firefox/103.0', 'reports/FailCalls', '2022-08-19 13:59:10');
INSERT INTO `activity_site_log` VALUES ('10485', 'track', 'kfir2nk0s7o0gatch72en8nhdj6eoulj', 'Open Voips', 'SYSTEM', '103.94.84.37', '103.94.84.37', '/portal/reports/FailCalls', 'http://151.106.4.238/portal/reports/FailCalls', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:103.0) Gecko/20100101 Firefox/103.0', 'reports/FailCalls', '2022-08-19 13:59:13');
INSERT INTO `activity_site_log` VALUES ('10486', 'track', 'kfir2nk0s7o0gatch72en8nhdj6eoulj', 'Open Voips', 'SYSTEM', '103.94.84.37', '103.94.84.37', '/portal/reports/FailCalls', 'http://151.106.4.238/portal/reports/FailCalls', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:103.0) Gecko/20100101 Firefox/103.0', 'reports/FailCalls', '2022-08-19 13:59:19');
INSERT INTO `activity_site_log` VALUES ('10487', 'track', 'kfir2nk0s7o0gatch72en8nhdj6eoulj', 'Open Voips', 'SYSTEM', '103.94.84.37', '103.94.84.37', '/portal/reports/FailCalls', 'http://151.106.4.238/portal/reports/FailCalls', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:103.0) Gecko/20100101 Firefox/103.0', 'reports/FailCalls', '2022-08-19 13:59:26');
INSERT INTO `activity_site_log` VALUES ('10488', 'track', 'kfir2nk0s7o0gatch72en8nhdj6eoulj', 'Open Voips', 'SYSTEM', '103.94.84.37', '103.94.84.37', '/portal/reports/FailCalls', 'http://151.106.4.238/portal/reports/FailCalls', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:103.0) Gecko/20100101 Firefox/103.0', 'reports/FailCalls', '2022-08-19 13:59:27');
INSERT INTO `activity_site_log` VALUES ('10489', 'track', '8cuvenfd90vocte3plrud8ug39eh70h8', '', '', '::1', '::1', '/portal/api/cdr.php?uuid=a_3d2e14da-8c1c-4c4b-b213-f0c1a5ae7223', '', 'freeswitch-xml/1.0', '/index', '2022-08-19 13:59:55');
INSERT INTO `activity_site_log` VALUES ('10490', 'track', 'jp0h7jsg0ll0iihvihgm7fde2k1grcps', '', '', '::1', '::1', '/portal/api/cdr.php?uuid=a_924a0550-06d8-4ea6-bf57-5c1ee67b3a9c', '', 'freeswitch-xml/1.0', '/index', '2022-08-19 14:00:40');
INSERT INTO `activity_site_log` VALUES ('10491', 'track', 'kfir2nk0s7o0gatch72en8nhdj6eoulj', 'Open Voips', 'SYSTEM', '103.94.84.37', '103.94.84.37', '/portal/reports/FailCalls', 'http://151.106.4.238/portal/reports/FailCalls', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:103.0) Gecko/20100101 Firefox/103.0', 'reports/FailCalls', '2022-08-19 14:00:46');
INSERT INTO `activity_site_log` VALUES ('10492', 'track', 'kfir2nk0s7o0gatch72en8nhdj6eoulj', 'Open Voips', 'SYSTEM', '103.94.84.37', '103.94.84.37', '/portal/reports/FailCalls', 'http://151.106.4.238/portal/reports/FailCalls', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:103.0) Gecko/20100101 Firefox/103.0', 'reports/FailCalls', '2022-08-19 14:00:47');
INSERT INTO `activity_site_log` VALUES ('10493', 'track', '4uqp89nda848f3rvl69aupuls02gl8b2', '', '', '::1', '::1', '/portal/api/cdr.php', '', 'Lynx/2.8.8dev.15 libwww-FM/2.14 SSL-MM/1.4.1 OpenSSL/1.0.1e-fips', '/index', '2022-08-19 14:01:58');
INSERT INTO `activity_site_log` VALUES ('10494', 'track', 'kfir2nk0s7o0gatch72en8nhdj6eoulj', 'Open Voips', 'SYSTEM', '103.94.84.37', '103.94.84.37', '/portal/reports/FailCalls', 'http://151.106.4.238/portal/reports/FailCalls', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:103.0) Gecko/20100101 Firefox/103.0', 'reports/FailCalls', '2022-08-19 14:03:14');
INSERT INTO `activity_site_log` VALUES ('10495', 'track', 'kfir2nk0s7o0gatch72en8nhdj6eoulj', 'Open Voips', 'SYSTEM', '103.94.84.37', '103.94.84.37', '/portal/reports/FailCalls', 'http://151.106.4.238/portal/reports/FailCalls', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:103.0) Gecko/20100101 Firefox/103.0', 'reports/FailCalls', '2022-08-19 14:03:15');
INSERT INTO `activity_site_log` VALUES ('10496', 'track', '318ankeje71alpqumgj5pottsrso21h5', '', '', '52.114.14.71', '52.114.14.71', '/portal/', '', 'Mozilla/5.0 (Windows NT 6.1; WOW64) SkypeUriPreview Preview/0.5 skype-url-preview@microsoft.com', 'login/index', '2022-08-19 14:03:34');
INSERT INTO `activity_site_log` VALUES ('10497', 'track', '160v85jq43eihgalfbjun5obd86agdub', '', '', '103.215.225.186', '103.215.225.186', '/portal/', '', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/104.0.0.0 Safari/537.36', 'login/index', '2022-08-19 14:16:26');
INSERT INTO `activity_site_log` VALUES ('10498', 'track', '160v85jq43eihgalfbjun5obd86agdub', '', '', '103.215.225.186', '103.215.225.186', '/portal/login', 'http://151.106.4.238/portal/', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/104.0.0.0 Safari/537.36', 'login/index', '2022-08-19 14:20:07');
INSERT INTO `activity_site_log` VALUES ('10499', 'track', '160v85jq43eihgalfbjun5obd86agdub', 'Open Voips', 'SYSTEM', '103.215.225.186', '103.215.225.186', '/portal/dashboard', 'http://151.106.4.238/portal/login', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/104.0.0.0 Safari/537.36', 'dashboard/index', '2022-08-19 14:20:08');
INSERT INTO `activity_site_log` VALUES ('10500', 'track', '160v85jq43eihgalfbjun5obd86agdub', 'Open Voips', 'SYSTEM', '103.215.225.186', '103.215.225.186', '/portal/users', 'http://151.106.4.238/portal/dashboard', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/104.0.0.0 Safari/537.36', 'users/index', '2022-08-19 14:20:45');
INSERT INTO `activity_site_log` VALUES ('10501', 'track', '160v85jq43eihgalfbjun5obd86agdub', 'Open Voips', 'SYSTEM', '103.215.225.186', '103.215.225.186', '/portal/users/editA/QURNSU4_EQUALS_', 'http://151.106.4.238/portal/users', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/104.0.0.0 Safari/537.36', 'users/editA', '2022-08-19 14:21:03');
INSERT INTO `activity_site_log` VALUES ('10502', 'track', '5g9vohppe6rmunmvp6k2h43fkvp9soap', 'Open Voips', 'SYSTEM', '103.215.225.186', '103.215.225.186', '/portal/users/editA/QURNSU4_EQUALS_', 'http://151.106.4.238/portal/users/editA/QURNSU4_EQUALS_', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/104.0.0.0 Safari/537.36', 'users/editA', '2022-08-19 14:22:08');
INSERT INTO `activity_site_log` VALUES ('10503', 'track', '5g9vohppe6rmunmvp6k2h43fkvp9soap', 'Open Voips', 'SYSTEM', '103.215.225.186', '103.215.225.186', '/portal/users/editA/QURNSU4_EQUALS_', 'http://151.106.4.238/portal/users/editA/QURNSU4_EQUALS_', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/104.0.0.0 Safari/537.36', 'users/editA', '2022-08-19 14:22:08');
INSERT INTO `activity_site_log` VALUES ('10504', 'track', 'tkf3quhtn0tf9e8l9kpvfc7ie1lae389', 'Open Voips', 'SYSTEM', '103.94.84.37', '103.94.84.37', '/portal/dashboard', 'http://151.106.4.238/portal/reports/FailCalls', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:103.0) Gecko/20100101 Firefox/103.0', 'dashboard/index', '2022-08-19 14:28:16');
INSERT INTO `activity_site_log` VALUES ('10505', 'track', 'tkf3quhtn0tf9e8l9kpvfc7ie1lae389', 'Open Voips', 'SYSTEM', '103.94.84.37', '103.94.84.37', '/portal/reports/monin', 'http://151.106.4.238/portal/dashboard', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:103.0) Gecko/20100101 Firefox/103.0', 'reports/monin', '2022-08-19 14:28:19');
INSERT INTO `activity_site_log` VALUES ('10506', 'track', 'tkf3quhtn0tf9e8l9kpvfc7ie1lae389', 'Open Voips', 'SYSTEM', '103.94.84.37', '103.94.84.37', '/portal/reports/monin_data/N/N/N/N/Y/Y/N/N/N/Y', 'http://151.106.4.238/portal/reports/monin', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:103.0) Gecko/20100101 Firefox/103.0', 'reports/monin_data', '2022-08-19 14:28:20');
INSERT INTO `activity_site_log` VALUES ('10507', 'track', 'tkf3quhtn0tf9e8l9kpvfc7ie1lae389', 'Open Voips', 'SYSTEM', '103.94.84.37', '103.94.84.37', '/portal/reports/monin_data/Y/Y/Y/Y/N/N/Y', 'http://151.106.4.238/portal/reports/monin', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:103.0) Gecko/20100101 Firefox/103.0', 'reports/monin_data', '2022-08-19 14:28:20');
INSERT INTO `activity_site_log` VALUES ('10508', 'track', 'tkf3quhtn0tf9e8l9kpvfc7ie1lae389', 'Open Voips', 'SYSTEM', '103.94.84.37', '103.94.84.37', '/portal/reports/monin_data/N/N/N/N/N/N/N/Y/Y', 'http://151.106.4.238/portal/reports/monin', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:103.0) Gecko/20100101 Firefox/103.0', 'reports/monin_data', '2022-08-19 14:28:20');
INSERT INTO `activity_site_log` VALUES ('10509', 'track', 'tkf3quhtn0tf9e8l9kpvfc7ie1lae389', 'Open Voips', 'SYSTEM', '103.94.84.37', '103.94.84.37', '/portal/reports/monin_data/N/N/N/N/Y/Y/N/N/N/Y', 'http://151.106.4.238/portal/reports/monin', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:103.0) Gecko/20100101 Firefox/103.0', 'reports/monin_data', '2022-08-19 14:28:26');
INSERT INTO `activity_site_log` VALUES ('10510', 'track', 'tkf3quhtn0tf9e8l9kpvfc7ie1lae389', 'Open Voips', 'SYSTEM', '103.94.84.37', '103.94.84.37', '/portal/reports/monin', 'http://151.106.4.238/portal/dashboard', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:103.0) Gecko/20100101 Firefox/103.0', 'reports/monin', '2022-08-19 14:28:31');
INSERT INTO `activity_site_log` VALUES ('10511', 'track', 'tkf3quhtn0tf9e8l9kpvfc7ie1lae389', 'Open Voips', 'SYSTEM', '103.94.84.37', '103.94.84.37', '/portal/reports/monin_data/N/N/N/N/Y/Y/N/N/N/Y', 'http://151.106.4.238/portal/reports/monin', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:103.0) Gecko/20100101 Firefox/103.0', 'reports/monin_data', '2022-08-19 14:28:32');
INSERT INTO `activity_site_log` VALUES ('10512', 'track', 'tkf3quhtn0tf9e8l9kpvfc7ie1lae389', 'Open Voips', 'SYSTEM', '103.94.84.37', '103.94.84.37', '/portal/reports/monin_data/Y/Y/Y/Y/N/N/Y', 'http://151.106.4.238/portal/reports/monin', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:103.0) Gecko/20100101 Firefox/103.0', 'reports/monin_data', '2022-08-19 14:28:32');
INSERT INTO `activity_site_log` VALUES ('10513', 'track', 'tkf3quhtn0tf9e8l9kpvfc7ie1lae389', 'Open Voips', 'SYSTEM', '103.94.84.37', '103.94.84.37', '/portal/reports/monin_data/N/N/N/N/N/N/N/Y/Y', 'http://151.106.4.238/portal/reports/monin', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:103.0) Gecko/20100101 Firefox/103.0', 'reports/monin_data', '2022-08-19 14:28:32');
INSERT INTO `activity_site_log` VALUES ('10514', 'track', 'tkf3quhtn0tf9e8l9kpvfc7ie1lae389', 'Open Voips', 'SYSTEM', '103.94.84.37', '103.94.84.37', '/portal/reports/monin_data/N/N/N/N/Y/Y/N/N/N/Y', 'http://151.106.4.238/portal/reports/monin', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:103.0) Gecko/20100101 Firefox/103.0', 'reports/monin_data', '2022-08-19 14:28:38');
INSERT INTO `activity_site_log` VALUES ('10515', 'track', 'tkf3quhtn0tf9e8l9kpvfc7ie1lae389', 'Open Voips', 'SYSTEM', '103.94.84.37', '103.94.84.37', '/portal/reports/monin_data/N/N/N/N/Y/Y/N/N/N/Y', 'http://151.106.4.238/portal/reports/monin', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:103.0) Gecko/20100101 Firefox/103.0', 'reports/monin_data', '2022-08-19 14:28:44');
INSERT INTO `activity_site_log` VALUES ('10516', 'track', 'tkf3quhtn0tf9e8l9kpvfc7ie1lae389', 'Open Voips', 'SYSTEM', '103.94.84.37', '103.94.84.37', '/portal/reports/monin_data/N/N/N/N/Y/Y/N/N/N/Y', 'http://151.106.4.238/portal/reports/monin', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:103.0) Gecko/20100101 Firefox/103.0', 'reports/monin_data', '2022-08-19 14:28:50');
INSERT INTO `activity_site_log` VALUES ('10517', 'track', 'tkf3quhtn0tf9e8l9kpvfc7ie1lae389', 'Open Voips', 'SYSTEM', '103.94.84.37', '103.94.84.37', '/portal/reports/monin_data/N/N/N/N/Y/Y/N/N/N/Y', 'http://151.106.4.238/portal/reports/monin', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:103.0) Gecko/20100101 Firefox/103.0', 'reports/monin_data', '2022-08-19 14:28:56');
INSERT INTO `activity_site_log` VALUES ('10518', 'track', 'tkf3quhtn0tf9e8l9kpvfc7ie1lae389', 'Open Voips', 'SYSTEM', '103.94.84.37', '103.94.84.37', '/portal/reports/monin_data/N/N/N/N/Y/Y/N/N/N/Y', 'http://151.106.4.238/portal/reports/monin', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:103.0) Gecko/20100101 Firefox/103.0', 'reports/monin_data', '2022-08-19 14:29:02');
INSERT INTO `activity_site_log` VALUES ('10519', 'track', 'tkf3quhtn0tf9e8l9kpvfc7ie1lae389', 'Open Voips', 'SYSTEM', '103.94.84.37', '103.94.84.37', '/portal/reports/monin_data/N/N/N/N/Y/Y/N/N/N/Y', 'http://151.106.4.238/portal/reports/monin', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:103.0) Gecko/20100101 Firefox/103.0', 'reports/monin_data', '2022-08-19 14:29:08');
INSERT INTO `activity_site_log` VALUES ('10520', 'track', 'tkf3quhtn0tf9e8l9kpvfc7ie1lae389', 'Open Voips', 'SYSTEM', '103.94.84.37', '103.94.84.37', '/portal/reports/monin_data/Y/Y/Y/Y/N/N/Y', 'http://151.106.4.238/portal/reports/monin', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:103.0) Gecko/20100101 Firefox/103.0', 'reports/monin_data', '2022-08-19 14:29:12');
INSERT INTO `activity_site_log` VALUES ('10521', 'track', 'tkf3quhtn0tf9e8l9kpvfc7ie1lae389', 'Open Voips', 'SYSTEM', '103.94.84.37', '103.94.84.37', '/portal/reports/monin_data/N/N/N/N/Y/Y/N/N/N/Y', 'http://151.106.4.238/portal/reports/monin', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:103.0) Gecko/20100101 Firefox/103.0', 'reports/monin_data', '2022-08-19 14:29:14');
INSERT INTO `activity_site_log` VALUES ('10522', 'track', 'tkf3quhtn0tf9e8l9kpvfc7ie1lae389', 'Open Voips', 'SYSTEM', '103.94.84.37', '103.94.84.37', '/portal/reports/monin_data/N/N/N/N/Y/Y/N/N/N/Y', 'http://151.106.4.238/portal/reports/monin', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:103.0) Gecko/20100101 Firefox/103.0', 'reports/monin_data', '2022-08-19 14:29:20');
INSERT INTO `activity_site_log` VALUES ('10523', 'track', 'tkf3quhtn0tf9e8l9kpvfc7ie1lae389', 'Open Voips', 'SYSTEM', '103.94.84.37', '103.94.84.37', '/portal/reports/monin_data/N/N/N/N/Y/Y/N/N/N/Y', 'http://151.106.4.238/portal/reports/monin', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:103.0) Gecko/20100101 Firefox/103.0', 'reports/monin_data', '2022-08-19 14:29:26');
INSERT INTO `activity_site_log` VALUES ('10524', 'track', 'tkf3quhtn0tf9e8l9kpvfc7ie1lae389', 'Open Voips', 'SYSTEM', '103.94.84.37', '103.94.84.37', '/portal/reports/monin_data/N/N/N/N/Y/Y/N/N/N/Y', 'http://151.106.4.238/portal/reports/monin', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:103.0) Gecko/20100101 Firefox/103.0', 'reports/monin_data', '2022-08-19 14:29:32');
INSERT INTO `activity_site_log` VALUES ('10525', 'track', 'tkf3quhtn0tf9e8l9kpvfc7ie1lae389', 'Open Voips', 'SYSTEM', '103.94.84.37', '103.94.84.37', '/portal/reports/monin_data/N/N/N/N/Y/Y/N/N/N/Y', 'http://151.106.4.238/portal/reports/monin', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:103.0) Gecko/20100101 Firefox/103.0', 'reports/monin_data', '2022-08-19 14:29:38');
INSERT INTO `activity_site_log` VALUES ('10526', 'track', 'tkf3quhtn0tf9e8l9kpvfc7ie1lae389', 'Open Voips', 'SYSTEM', '103.94.84.37', '103.94.84.37', '/portal/reports/monin_data/N/N/N/N/Y/Y/N/N/N/Y', 'http://151.106.4.238/portal/reports/monin', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:103.0) Gecko/20100101 Firefox/103.0', 'reports/monin_data', '2022-08-19 14:29:44');
INSERT INTO `activity_site_log` VALUES ('10527', 'track', 'tkf3quhtn0tf9e8l9kpvfc7ie1lae389', 'Open Voips', 'SYSTEM', '103.94.84.37', '103.94.84.37', '/portal/reports/monin_data/N/N/N/N/Y/Y/N/N/N/Y', 'http://151.106.4.238/portal/reports/monin', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:103.0) Gecko/20100101 Firefox/103.0', 'reports/monin_data', '2022-08-19 14:29:50');
INSERT INTO `activity_site_log` VALUES ('10528', 'track', 'tkf3quhtn0tf9e8l9kpvfc7ie1lae389', 'Open Voips', 'SYSTEM', '103.94.84.37', '103.94.84.37', '/portal/reports/monin_data/Y/Y/Y/Y/N/N/Y', 'http://151.106.4.238/portal/reports/monin', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:103.0) Gecko/20100101 Firefox/103.0', 'reports/monin_data', '2022-08-19 14:29:52');
INSERT INTO `activity_site_log` VALUES ('10529', 'track', 'tkf3quhtn0tf9e8l9kpvfc7ie1lae389', 'Open Voips', 'SYSTEM', '103.94.84.37', '103.94.84.37', '/portal/reports/monin_data/N/N/N/N/Y/Y/N/N/N/Y', 'http://151.106.4.238/portal/reports/monin', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:103.0) Gecko/20100101 Firefox/103.0', 'reports/monin_data', '2022-08-19 14:29:56');
INSERT INTO `activity_site_log` VALUES ('10530', 'track', 'tkf3quhtn0tf9e8l9kpvfc7ie1lae389', 'Open Voips', 'SYSTEM', '103.94.84.37', '103.94.84.37', '/portal/reports/monin_data/N/N/N/N/Y/Y/N/N/N/Y', 'http://151.106.4.238/portal/reports/monin', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:103.0) Gecko/20100101 Firefox/103.0', 'reports/monin_data', '2022-08-19 14:30:02');
INSERT INTO `activity_site_log` VALUES ('10531', 'track', 'tkf3quhtn0tf9e8l9kpvfc7ie1lae389', 'Open Voips', 'SYSTEM', '103.94.84.37', '103.94.84.37', '/portal/reports/monin_data/N/N/N/N/Y/Y/N/N/N/Y', 'http://151.106.4.238/portal/reports/monin', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:103.0) Gecko/20100101 Firefox/103.0', 'reports/monin_data', '2022-08-19 14:30:08');
INSERT INTO `activity_site_log` VALUES ('10532', 'track', 'tkf3quhtn0tf9e8l9kpvfc7ie1lae389', 'Open Voips', 'SYSTEM', '103.94.84.37', '103.94.84.37', '/portal/reports/monin_data/N/N/N/N/Y/Y/N/N/N/Y', 'http://151.106.4.238/portal/reports/monin', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:103.0) Gecko/20100101 Firefox/103.0', 'reports/monin_data', '2022-08-19 14:30:14');
INSERT INTO `activity_site_log` VALUES ('10533', 'track', 'tkf3quhtn0tf9e8l9kpvfc7ie1lae389', 'Open Voips', 'SYSTEM', '103.94.84.37', '103.94.84.37', '/portal/reports/monin_data/N/N/N/N/Y/Y/N/N/N/Y', 'http://151.106.4.238/portal/reports/monin', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:103.0) Gecko/20100101 Firefox/103.0', 'reports/monin_data', '2022-08-19 14:30:20');
INSERT INTO `activity_site_log` VALUES ('10534', 'track', 'tkf3quhtn0tf9e8l9kpvfc7ie1lae389', 'Open Voips', 'SYSTEM', '103.94.84.37', '103.94.84.37', '/portal/reports/monin_data/N/N/N/N/Y/Y/N/N/N/Y', 'http://151.106.4.238/portal/reports/monin', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:103.0) Gecko/20100101 Firefox/103.0', 'reports/monin_data', '2022-08-19 14:30:26');
INSERT INTO `activity_site_log` VALUES ('10535', 'track', 'tkf3quhtn0tf9e8l9kpvfc7ie1lae389', 'Open Voips', 'SYSTEM', '103.94.84.37', '103.94.84.37', '/portal/reports/monin', 'http://151.106.4.238/portal/dashboard', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:103.0) Gecko/20100101 Firefox/103.0', 'reports/monin', '2022-08-19 14:30:28');
INSERT INTO `activity_site_log` VALUES ('10536', 'track', 'tkf3quhtn0tf9e8l9kpvfc7ie1lae389', 'Open Voips', 'SYSTEM', '103.94.84.37', '103.94.84.37', '/portal/reports/monin_data/N/N/N/N/Y/Y/N/N/N/Y', 'http://151.106.4.238/portal/reports/monin', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:103.0) Gecko/20100101 Firefox/103.0', 'reports/monin_data', '2022-08-19 14:30:28');
INSERT INTO `activity_site_log` VALUES ('10537', 'track', 'tkf3quhtn0tf9e8l9kpvfc7ie1lae389', 'Open Voips', 'SYSTEM', '103.94.84.37', '103.94.84.37', '/portal/reports/monin_data/Y/Y/Y/Y/N/N/Y', 'http://151.106.4.238/portal/reports/monin', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:103.0) Gecko/20100101 Firefox/103.0', 'reports/monin_data', '2022-08-19 14:30:28');
INSERT INTO `activity_site_log` VALUES ('10538', 'track', 'tkf3quhtn0tf9e8l9kpvfc7ie1lae389', 'Open Voips', 'SYSTEM', '103.94.84.37', '103.94.84.37', '/portal/reports/monin_data/N/N/N/N/N/N/N/Y/Y', 'http://151.106.4.238/portal/reports/monin', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:103.0) Gecko/20100101 Firefox/103.0', 'reports/monin_data', '2022-08-19 14:30:28');
INSERT INTO `activity_site_log` VALUES ('10539', 'track', 'tkf3quhtn0tf9e8l9kpvfc7ie1lae389', 'Open Voips', 'SYSTEM', '103.94.84.37', '103.94.84.37', '/portal/reports/monin', 'http://151.106.4.238/portal/dashboard', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:103.0) Gecko/20100101 Firefox/103.0', 'reports/monin', '2022-08-19 14:30:30');
INSERT INTO `activity_site_log` VALUES ('10540', 'track', 'tkf3quhtn0tf9e8l9kpvfc7ie1lae389', 'Open Voips', 'SYSTEM', '103.94.84.37', '103.94.84.37', '/portal/reports/monin_data/N/N/N/N/Y/Y/N/N/N/Y', 'http://151.106.4.238/portal/reports/monin', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:103.0) Gecko/20100101 Firefox/103.0', 'reports/monin_data', '2022-08-19 14:30:31');
INSERT INTO `activity_site_log` VALUES ('10541', 'track', 'tkf3quhtn0tf9e8l9kpvfc7ie1lae389', 'Open Voips', 'SYSTEM', '103.94.84.37', '103.94.84.37', '/portal/reports/monin_data/Y/Y/Y/Y/N/N/Y', 'http://151.106.4.238/portal/reports/monin', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:103.0) Gecko/20100101 Firefox/103.0', 'reports/monin_data', '2022-08-19 14:30:31');
INSERT INTO `activity_site_log` VALUES ('10542', 'track', 'tkf3quhtn0tf9e8l9kpvfc7ie1lae389', 'Open Voips', 'SYSTEM', '103.94.84.37', '103.94.84.37', '/portal/reports/monin_data/N/N/N/N/N/N/N/Y/Y', 'http://151.106.4.238/portal/reports/monin', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:103.0) Gecko/20100101 Firefox/103.0', 'reports/monin_data', '2022-08-19 14:30:31');
INSERT INTO `activity_site_log` VALUES ('10543', 'track', 'tkf3quhtn0tf9e8l9kpvfc7ie1lae389', 'Open Voips', 'SYSTEM', '103.94.84.37', '103.94.84.37', '/portal/reports/monin', 'http://151.106.4.238/portal/dashboard', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:103.0) Gecko/20100101 Firefox/103.0', 'reports/monin', '2022-08-19 14:30:32');
INSERT INTO `activity_site_log` VALUES ('10544', 'track', 'tkf3quhtn0tf9e8l9kpvfc7ie1lae389', 'Open Voips', 'SYSTEM', '103.94.84.37', '103.94.84.37', '/portal/reports/monin_data/N/N/N/N/Y/Y/N/N/N/Y', 'http://151.106.4.238/portal/reports/monin', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:103.0) Gecko/20100101 Firefox/103.0', 'reports/monin_data', '2022-08-19 14:30:33');
INSERT INTO `activity_site_log` VALUES ('10545', 'track', 'tkf3quhtn0tf9e8l9kpvfc7ie1lae389', 'Open Voips', 'SYSTEM', '103.94.84.37', '103.94.84.37', '/portal/reports/monin_data/Y/Y/Y/Y/N/N/Y', 'http://151.106.4.238/portal/reports/monin', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:103.0) Gecko/20100101 Firefox/103.0', 'reports/monin_data', '2022-08-19 14:30:33');
INSERT INTO `activity_site_log` VALUES ('10546', 'track', 'tkf3quhtn0tf9e8l9kpvfc7ie1lae389', 'Open Voips', 'SYSTEM', '103.94.84.37', '103.94.84.37', '/portal/reports/monin_data/N/N/N/N/N/N/N/Y/Y', 'http://151.106.4.238/portal/reports/monin', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:103.0) Gecko/20100101 Firefox/103.0', 'reports/monin_data', '2022-08-19 14:30:33');
INSERT INTO `activity_site_log` VALUES ('10547', 'track', 'tkf3quhtn0tf9e8l9kpvfc7ie1lae389', 'Open Voips', 'SYSTEM', '103.94.84.37', '103.94.84.37', '/portal/reports/monin_data/N/N/N/N/Y/Y/N/N/N/Y', 'http://151.106.4.238/portal/reports/monin', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:103.0) Gecko/20100101 Firefox/103.0', 'reports/monin_data', '2022-08-19 14:30:39');
INSERT INTO `activity_site_log` VALUES ('10548', 'track', 'tkf3quhtn0tf9e8l9kpvfc7ie1lae389', 'Open Voips', 'SYSTEM', '103.94.84.37', '103.94.84.37', '/portal/reports/monin_data/N/N/N/N/Y/Y/N/N/N/Y', 'http://151.106.4.238/portal/reports/monin', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:103.0) Gecko/20100101 Firefox/103.0', 'reports/monin_data', '2022-08-19 14:30:45');
INSERT INTO `activity_site_log` VALUES ('10549', 'track', 'tkf3quhtn0tf9e8l9kpvfc7ie1lae389', 'Open Voips', 'SYSTEM', '103.94.84.37', '103.94.84.37', '/portal/reports/monin_data/N/N/N/N/Y/Y/N/N/N/Y', 'http://151.106.4.238/portal/reports/monin', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:103.0) Gecko/20100101 Firefox/103.0', 'reports/monin_data', '2022-08-19 14:30:51');
INSERT INTO `activity_site_log` VALUES ('10550', 'track', 'tkf3quhtn0tf9e8l9kpvfc7ie1lae389', 'Open Voips', 'SYSTEM', '103.94.84.37', '103.94.84.37', '/portal/reports/monin_data/N/N/N/N/Y/Y/N/N/N/Y', 'http://151.106.4.238/portal/reports/monin', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:103.0) Gecko/20100101 Firefox/103.0', 'reports/monin_data', '2022-08-19 14:30:57');
INSERT INTO `activity_site_log` VALUES ('10551', 'track', 'tkf3quhtn0tf9e8l9kpvfc7ie1lae389', 'Open Voips', 'SYSTEM', '103.94.84.37', '103.94.84.37', '/portal/reports/monin_data/N/N/N/N/Y/Y/N/N/N/Y', 'http://151.106.4.238/portal/reports/monin', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:103.0) Gecko/20100101 Firefox/103.0', 'reports/monin_data', '2022-08-19 14:31:03');
INSERT INTO `activity_site_log` VALUES ('10552', 'track', 'tkf3quhtn0tf9e8l9kpvfc7ie1lae389', 'Open Voips', 'SYSTEM', '103.94.84.37', '103.94.84.37', '/portal/reports/monin_data/N/N/N/N/Y/Y/N/N/N/Y', 'http://151.106.4.238/portal/reports/monin', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:103.0) Gecko/20100101 Firefox/103.0', 'reports/monin_data', '2022-08-19 14:31:09');
INSERT INTO `activity_site_log` VALUES ('10553', 'track', 'tkf3quhtn0tf9e8l9kpvfc7ie1lae389', 'Open Voips', 'SYSTEM', '103.94.84.37', '103.94.84.37', '/portal/reports/monin_data/Y/Y/Y/Y/N/N/Y', 'http://151.106.4.238/portal/reports/monin', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:103.0) Gecko/20100101 Firefox/103.0', 'reports/monin_data', '2022-08-19 14:31:13');
INSERT INTO `activity_site_log` VALUES ('10554', 'track', 'tkf3quhtn0tf9e8l9kpvfc7ie1lae389', 'Open Voips', 'SYSTEM', '103.94.84.37', '103.94.84.37', '/portal/reports/monin_data/N/N/N/N/Y/Y/N/N/N/Y', 'http://151.106.4.238/portal/reports/monin', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:103.0) Gecko/20100101 Firefox/103.0', 'reports/monin_data', '2022-08-19 14:31:15');
INSERT INTO `activity_site_log` VALUES ('10555', 'track', 'tkf3quhtn0tf9e8l9kpvfc7ie1lae389', 'Open Voips', 'SYSTEM', '103.94.84.37', '103.94.84.37', '/portal/reports/monin_data/N/N/N/N/Y/Y/N/N/N/Y', 'http://151.106.4.238/portal/reports/monin', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:103.0) Gecko/20100101 Firefox/103.0', 'reports/monin_data', '2022-08-19 14:31:21');
INSERT INTO `activity_site_log` VALUES ('10556', 'track', 'tkf3quhtn0tf9e8l9kpvfc7ie1lae389', 'Open Voips', 'SYSTEM', '103.94.84.37', '103.94.84.37', '/portal/reports/monin_data/N/N/N/N/Y/Y/N/N/N/Y', 'http://151.106.4.238/portal/reports/monin', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:103.0) Gecko/20100101 Firefox/103.0', 'reports/monin_data', '2022-08-19 14:31:27');
INSERT INTO `activity_site_log` VALUES ('10557', 'track', 'tkf3quhtn0tf9e8l9kpvfc7ie1lae389', 'Open Voips', 'SYSTEM', '103.94.84.37', '103.94.84.37', '/portal/reports/monin_data/N/N/N/N/Y/Y/N/N/N/Y', 'http://151.106.4.238/portal/reports/monin', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:103.0) Gecko/20100101 Firefox/103.0', 'reports/monin_data', '2022-08-19 14:31:33');
INSERT INTO `activity_site_log` VALUES ('10558', 'track', 'tkf3quhtn0tf9e8l9kpvfc7ie1lae389', 'Open Voips', 'SYSTEM', '103.94.84.37', '103.94.84.37', '/portal/reports/monin_data/N/N/N/N/Y/Y/N/N/N/Y', 'http://151.106.4.238/portal/reports/monin', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:103.0) Gecko/20100101 Firefox/103.0', 'reports/monin_data', '2022-08-19 14:31:39');
INSERT INTO `activity_site_log` VALUES ('10559', 'track', 'tkf3quhtn0tf9e8l9kpvfc7ie1lae389', 'Open Voips', 'SYSTEM', '103.94.84.37', '103.94.84.37', '/portal/reports/monin_data/N/N/N/N/Y/Y/N/N/N/Y', 'http://151.106.4.238/portal/reports/monin', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:103.0) Gecko/20100101 Firefox/103.0', 'reports/monin_data', '2022-08-19 14:31:45');
INSERT INTO `activity_site_log` VALUES ('10560', 'track', 'tkf3quhtn0tf9e8l9kpvfc7ie1lae389', 'Open Voips', 'SYSTEM', '103.94.84.37', '103.94.84.37', '/portal/reports/monin_data/N/N/N/N/Y/Y/N/N/N/Y', 'http://151.106.4.238/portal/reports/monin', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:103.0) Gecko/20100101 Firefox/103.0', 'reports/monin_data', '2022-08-19 14:31:51');
INSERT INTO `activity_site_log` VALUES ('10561', 'track', 'tkf3quhtn0tf9e8l9kpvfc7ie1lae389', 'Open Voips', 'SYSTEM', '103.94.84.37', '103.94.84.37', '/portal/reports/monin_data/Y/Y/Y/Y/N/N/Y', 'http://151.106.4.238/portal/reports/monin', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:103.0) Gecko/20100101 Firefox/103.0', 'reports/monin_data', '2022-08-19 14:31:53');
INSERT INTO `activity_site_log` VALUES ('10562', 'track', 'tkf3quhtn0tf9e8l9kpvfc7ie1lae389', 'Open Voips', 'SYSTEM', '103.94.84.37', '103.94.84.37', '/portal/reports/monin_data/N/N/N/N/Y/Y/N/N/N/Y', 'http://151.106.4.238/portal/reports/monin', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:103.0) Gecko/20100101 Firefox/103.0', 'reports/monin_data', '2022-08-19 14:31:57');
INSERT INTO `activity_site_log` VALUES ('10563', 'track', 'tkf3quhtn0tf9e8l9kpvfc7ie1lae389', 'Open Voips', 'SYSTEM', '103.94.84.37', '103.94.84.37', '/portal/reports/monin_data/N/N/N/N/Y/Y/N/N/N/Y', 'http://151.106.4.238/portal/reports/monin', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:103.0) Gecko/20100101 Firefox/103.0', 'reports/monin_data', '2022-08-19 14:32:03');
INSERT INTO `activity_site_log` VALUES ('10564', 'track', 'tkf3quhtn0tf9e8l9kpvfc7ie1lae389', 'Open Voips', 'SYSTEM', '103.94.84.37', '103.94.84.37', '/portal/reports/monin_data/N/N/N/N/Y/Y/N/N/N/Y', 'http://151.106.4.238/portal/reports/monin', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:103.0) Gecko/20100101 Firefox/103.0', 'reports/monin_data', '2022-08-19 14:32:09');
INSERT INTO `activity_site_log` VALUES ('10565', 'track', 'tkf3quhtn0tf9e8l9kpvfc7ie1lae389', 'Open Voips', 'SYSTEM', '103.94.84.37', '103.94.84.37', '/portal/reports/monin_data/N/N/N/N/Y/Y/N/N/N/Y', 'http://151.106.4.238/portal/reports/monin', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:103.0) Gecko/20100101 Firefox/103.0', 'reports/monin_data', '2022-08-19 14:32:15');
INSERT INTO `activity_site_log` VALUES ('10566', 'track', 'tkf3quhtn0tf9e8l9kpvfc7ie1lae389', 'Open Voips', 'SYSTEM', '103.94.84.37', '103.94.84.37', '/portal/reports/monin_data/N/N/N/N/Y/Y/N/N/N/Y', 'http://151.106.4.238/portal/reports/monin', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:103.0) Gecko/20100101 Firefox/103.0', 'reports/monin_data', '2022-08-19 14:32:21');
INSERT INTO `activity_site_log` VALUES ('10567', 'track', 'tkf3quhtn0tf9e8l9kpvfc7ie1lae389', 'Open Voips', 'SYSTEM', '103.94.84.37', '103.94.84.37', '/portal/reports/monin_data/N/N/N/N/Y/Y/N/N/N/Y', 'http://151.106.4.238/portal/reports/monin', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:103.0) Gecko/20100101 Firefox/103.0', 'reports/monin_data', '2022-08-19 14:32:28');
INSERT INTO `activity_site_log` VALUES ('10568', 'track', 'tkf3quhtn0tf9e8l9kpvfc7ie1lae389', 'Open Voips', 'SYSTEM', '103.94.84.37', '103.94.84.37', '/portal/reports/monin_data/N/N/N/N/N/N/N/Y/Y', 'http://151.106.4.238/portal/reports/monin', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:103.0) Gecko/20100101 Firefox/103.0', 'reports/monin_data', '2022-08-19 14:32:33');
INSERT INTO `activity_site_log` VALUES ('10569', 'track', 'tkf3quhtn0tf9e8l9kpvfc7ie1lae389', 'Open Voips', 'SYSTEM', '103.94.84.37', '103.94.84.37', '/portal/reports/monin_data/Y/Y/Y/Y/N/N/Y', 'http://151.106.4.238/portal/reports/monin', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:103.0) Gecko/20100101 Firefox/103.0', 'reports/monin_data', '2022-08-19 14:32:34');
INSERT INTO `activity_site_log` VALUES ('10570', 'track', 'tkf3quhtn0tf9e8l9kpvfc7ie1lae389', 'Open Voips', 'SYSTEM', '103.94.84.37', '103.94.84.37', '/portal/reports/monin_data/N/N/N/N/Y/Y/N/N/N/Y', 'http://151.106.4.238/portal/reports/monin', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:103.0) Gecko/20100101 Firefox/103.0', 'reports/monin_data', '2022-08-19 14:32:35');
INSERT INTO `activity_site_log` VALUES ('10571', 'track', 'tkf3quhtn0tf9e8l9kpvfc7ie1lae389', 'Open Voips', 'SYSTEM', '103.94.84.37', '103.94.84.37', '/portal/reports/monin_data/N/N/N/N/Y/Y/N/N/N/Y', 'http://151.106.4.238/portal/reports/monin', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:103.0) Gecko/20100101 Firefox/103.0', 'reports/monin_data', '2022-08-19 14:32:41');
INSERT INTO `activity_site_log` VALUES ('10572', 'track', 'tkf3quhtn0tf9e8l9kpvfc7ie1lae389', 'Open Voips', 'SYSTEM', '103.94.84.37', '103.94.84.37', '/portal/reports/monin_data/N/N/N/N/Y/Y/N/N/N/Y', 'http://151.106.4.238/portal/reports/monin', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:103.0) Gecko/20100101 Firefox/103.0', 'reports/monin_data', '2022-08-19 14:32:47');
INSERT INTO `activity_site_log` VALUES ('10573', 'track', 'tkf3quhtn0tf9e8l9kpvfc7ie1lae389', 'Open Voips', 'SYSTEM', '103.94.84.37', '103.94.84.37', '/portal/reports/monin_data/N/N/N/N/Y/Y/N/N/N/Y', 'http://151.106.4.238/portal/reports/monin', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:103.0) Gecko/20100101 Firefox/103.0', 'reports/monin_data', '2022-08-19 14:32:54');
INSERT INTO `activity_site_log` VALUES ('10574', 'track', 'tkf3quhtn0tf9e8l9kpvfc7ie1lae389', 'Open Voips', 'SYSTEM', '103.94.84.37', '103.94.84.37', '/portal/reports/monin_data/N/N/N/N/Y/Y/N/N/N/Y', 'http://151.106.4.238/portal/reports/monin', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:103.0) Gecko/20100101 Firefox/103.0', 'reports/monin_data', '2022-08-19 14:32:59');
INSERT INTO `activity_site_log` VALUES ('10575', 'track', 'tkf3quhtn0tf9e8l9kpvfc7ie1lae389', 'Open Voips', 'SYSTEM', '103.94.84.37', '103.94.84.37', '/portal/reports/monin_data/N/N/N/N/Y/Y/N/N/N/Y', 'http://151.106.4.238/portal/reports/monin', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:103.0) Gecko/20100101 Firefox/103.0', 'reports/monin_data', '2022-08-19 14:33:05');
INSERT INTO `activity_site_log` VALUES ('10576', 'track', 'tkf3quhtn0tf9e8l9kpvfc7ie1lae389', 'Open Voips', 'SYSTEM', '103.94.84.37', '103.94.84.37', '/portal/reports/monin_data/N/N/N/N/Y/Y/N/N/N/Y', 'http://151.106.4.238/portal/reports/monin', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:103.0) Gecko/20100101 Firefox/103.0', 'reports/monin_data', '2022-08-19 14:33:12');
INSERT INTO `activity_site_log` VALUES ('10577', 'track', 'tkf3quhtn0tf9e8l9kpvfc7ie1lae389', 'Open Voips', 'SYSTEM', '103.94.84.37', '103.94.84.37', '/portal/reports/monin_data/Y/Y/Y/Y/N/N/Y', 'http://151.106.4.238/portal/reports/monin', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:103.0) Gecko/20100101 Firefox/103.0', 'reports/monin_data', '2022-08-19 14:33:14');
INSERT INTO `activity_site_log` VALUES ('10578', 'track', 'tkf3quhtn0tf9e8l9kpvfc7ie1lae389', 'Open Voips', 'SYSTEM', '103.94.84.37', '103.94.84.37', '/portal/reports/monin_data/N/N/N/N/Y/Y/N/N/N/Y', 'http://151.106.4.238/portal/reports/monin', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:103.0) Gecko/20100101 Firefox/103.0', 'reports/monin_data', '2022-08-19 14:33:17');
INSERT INTO `activity_site_log` VALUES ('10579', 'track', 'tkf3quhtn0tf9e8l9kpvfc7ie1lae389', 'Open Voips', 'SYSTEM', '103.94.84.37', '103.94.84.37', '/portal/reports/monin_data/N/N/N/N/Y/Y/N/N/N/Y', 'http://151.106.4.238/portal/reports/monin', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:103.0) Gecko/20100101 Firefox/103.0', 'reports/monin_data', '2022-08-19 14:33:24');
INSERT INTO `activity_site_log` VALUES ('10580', 'track', 'tkf3quhtn0tf9e8l9kpvfc7ie1lae389', 'Open Voips', 'SYSTEM', '103.94.84.37', '103.94.84.37', '/portal/reports/monin_data/N/N/N/N/Y/Y/N/N/N/Y', 'http://151.106.4.238/portal/reports/monin', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:103.0) Gecko/20100101 Firefox/103.0', 'reports/monin_data', '2022-08-19 14:33:30');
INSERT INTO `activity_site_log` VALUES ('10581', 'track', 'tkf3quhtn0tf9e8l9kpvfc7ie1lae389', 'Open Voips', 'SYSTEM', '103.94.84.37', '103.94.84.37', '/portal/reports/monin_data/N/N/N/N/Y/Y/N/N/N/Y', 'http://151.106.4.238/portal/reports/monin', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:103.0) Gecko/20100101 Firefox/103.0', 'reports/monin_data', '2022-08-19 14:33:36');
INSERT INTO `activity_site_log` VALUES ('10582', 'track', 'tkf3quhtn0tf9e8l9kpvfc7ie1lae389', 'Open Voips', 'SYSTEM', '103.94.84.37', '103.94.84.37', '/portal/reports/monin_data/N/N/N/N/Y/Y/N/N/N/Y', 'http://151.106.4.238/portal/reports/monin', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:103.0) Gecko/20100101 Firefox/103.0', 'reports/monin_data', '2022-08-19 14:33:42');
INSERT INTO `activity_site_log` VALUES ('10583', 'track', 'tkf3quhtn0tf9e8l9kpvfc7ie1lae389', 'Open Voips', 'SYSTEM', '103.94.84.37', '103.94.84.37', '/portal/reports/monin_data/N/N/N/N/Y/Y/N/N/N/Y', 'http://151.106.4.238/portal/reports/monin', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:103.0) Gecko/20100101 Firefox/103.0', 'reports/monin_data', '2022-08-19 14:33:48');
INSERT INTO `activity_site_log` VALUES ('10584', 'track', 'tkf3quhtn0tf9e8l9kpvfc7ie1lae389', 'Open Voips', 'SYSTEM', '103.94.84.37', '103.94.84.37', '/portal/reports/monin_data/N/N/N/N/Y/Y/N/N/N/Y', 'http://151.106.4.238/portal/reports/monin', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:103.0) Gecko/20100101 Firefox/103.0', 'reports/monin_data', '2022-08-19 14:33:54');
INSERT INTO `activity_site_log` VALUES ('10585', 'track', 'tkf3quhtn0tf9e8l9kpvfc7ie1lae389', 'Open Voips', 'SYSTEM', '103.94.84.37', '103.94.84.37', '/portal/reports/monin_data/Y/Y/Y/Y/N/N/Y', 'http://151.106.4.238/portal/reports/monin', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:103.0) Gecko/20100101 Firefox/103.0', 'reports/monin_data', '2022-08-19 14:33:55');
INSERT INTO `activity_site_log` VALUES ('10586', 'track', 'tkf3quhtn0tf9e8l9kpvfc7ie1lae389', 'Open Voips', 'SYSTEM', '103.94.84.37', '103.94.84.37', '/portal/reports/monin_data/N/N/N/N/Y/Y/N/N/N/Y', 'http://151.106.4.238/portal/reports/monin', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:103.0) Gecko/20100101 Firefox/103.0', 'reports/monin_data', '2022-08-19 14:34:00');
INSERT INTO `activity_site_log` VALUES ('10587', 'track', 'tkf3quhtn0tf9e8l9kpvfc7ie1lae389', 'Open Voips', 'SYSTEM', '103.94.84.37', '103.94.84.37', '/portal/reports/monin_data/N/N/N/N/Y/Y/N/N/N/Y', 'http://151.106.4.238/portal/reports/monin', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:103.0) Gecko/20100101 Firefox/103.0', 'reports/monin_data', '2022-08-19 14:34:06');
INSERT INTO `activity_site_log` VALUES ('10588', 'track', 'tkf3quhtn0tf9e8l9kpvfc7ie1lae389', 'Open Voips', 'SYSTEM', '103.94.84.37', '103.94.84.37', '/portal/reports/monin_data/N/N/N/N/Y/Y/N/N/N/Y', 'http://151.106.4.238/portal/reports/monin', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:103.0) Gecko/20100101 Firefox/103.0', 'reports/monin_data', '2022-08-19 14:34:12');
INSERT INTO `activity_site_log` VALUES ('10589', 'track', 'tkf3quhtn0tf9e8l9kpvfc7ie1lae389', 'Open Voips', 'SYSTEM', '103.94.84.37', '103.94.84.37', '/portal/reports/monin_data/N/N/N/N/Y/Y/N/N/N/Y', 'http://151.106.4.238/portal/reports/monin', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:103.0) Gecko/20100101 Firefox/103.0', 'reports/monin_data', '2022-08-19 14:34:18');
INSERT INTO `activity_site_log` VALUES ('10590', 'track', 'tkf3quhtn0tf9e8l9kpvfc7ie1lae389', 'Open Voips', 'SYSTEM', '103.94.84.37', '103.94.84.37', '/portal/reports/monin_data/N/N/N/N/Y/Y/N/N/N/Y', 'http://151.106.4.238/portal/reports/monin', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:103.0) Gecko/20100101 Firefox/103.0', 'reports/monin_data', '2022-08-19 14:34:24');
INSERT INTO `activity_site_log` VALUES ('10591', 'track', 'tkf3quhtn0tf9e8l9kpvfc7ie1lae389', 'Open Voips', 'SYSTEM', '103.94.84.37', '103.94.84.37', '/portal/reports/monin_data/N/N/N/N/Y/Y/N/N/N/Y', 'http://151.106.4.238/portal/reports/monin', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:103.0) Gecko/20100101 Firefox/103.0', 'reports/monin_data', '2022-08-19 14:34:30');
INSERT INTO `activity_site_log` VALUES ('10592', 'track', 'tkf3quhtn0tf9e8l9kpvfc7ie1lae389', 'Open Voips', 'SYSTEM', '103.94.84.37', '103.94.84.37', '/portal/reports/monin_data/N/N/N/N/N/N/N/Y/Y', 'http://151.106.4.238/portal/reports/monin', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:103.0) Gecko/20100101 Firefox/103.0', 'reports/monin_data', '2022-08-19 14:34:33');
INSERT INTO `activity_site_log` VALUES ('10593', 'track', 'tkf3quhtn0tf9e8l9kpvfc7ie1lae389', 'Open Voips', 'SYSTEM', '103.94.84.37', '103.94.84.37', '/portal/reports/monin_data/Y/Y/Y/Y/N/N/Y', 'http://151.106.4.238/portal/reports/monin', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:103.0) Gecko/20100101 Firefox/103.0', 'reports/monin_data', '2022-08-19 14:34:35');
INSERT INTO `activity_site_log` VALUES ('10594', 'track', 'tkf3quhtn0tf9e8l9kpvfc7ie1lae389', 'Open Voips', 'SYSTEM', '103.94.84.37', '103.94.84.37', '/portal/reports/monin_data/N/N/N/N/Y/Y/N/N/N/Y', 'http://151.106.4.238/portal/reports/monin', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:103.0) Gecko/20100101 Firefox/103.0', 'reports/monin_data', '2022-08-19 14:34:36');
INSERT INTO `activity_site_log` VALUES ('10595', 'track', 'tkf3quhtn0tf9e8l9kpvfc7ie1lae389', 'Open Voips', 'SYSTEM', '103.94.84.37', '103.94.84.37', '/portal/reports/monin_data/N/N/N/N/Y/Y/N/N/N/Y', 'http://151.106.4.238/portal/reports/monin', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:103.0) Gecko/20100101 Firefox/103.0', 'reports/monin_data', '2022-08-19 14:34:42');
INSERT INTO `activity_site_log` VALUES ('10596', 'track', 'tkf3quhtn0tf9e8l9kpvfc7ie1lae389', 'Open Voips', 'SYSTEM', '103.94.84.37', '103.94.84.37', '/portal/reports/monin_data/N/N/N/N/Y/Y/N/N/N/Y', 'http://151.106.4.238/portal/reports/monin', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:103.0) Gecko/20100101 Firefox/103.0', 'reports/monin_data', '2022-08-19 14:34:48');
INSERT INTO `activity_site_log` VALUES ('10597', 'track', 'tkf3quhtn0tf9e8l9kpvfc7ie1lae389', 'Open Voips', 'SYSTEM', '103.94.84.37', '103.94.84.37', '/portal/reports/monin_data/N/N/N/N/Y/Y/N/N/N/Y', 'http://151.106.4.238/portal/reports/monin', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:103.0) Gecko/20100101 Firefox/103.0', 'reports/monin_data', '2022-08-19 14:34:54');
INSERT INTO `activity_site_log` VALUES ('10598', 'track', 'tkf3quhtn0tf9e8l9kpvfc7ie1lae389', 'Open Voips', 'SYSTEM', '103.94.84.37', '103.94.84.37', '/portal/reports/monin_data/N/N/N/N/Y/Y/N/N/N/Y', 'http://151.106.4.238/portal/reports/monin', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:103.0) Gecko/20100101 Firefox/103.0', 'reports/monin_data', '2022-08-19 14:35:01');
INSERT INTO `activity_site_log` VALUES ('10599', 'track', 'tkf3quhtn0tf9e8l9kpvfc7ie1lae389', 'Open Voips', 'SYSTEM', '103.94.84.37', '103.94.84.37', '/portal/reports/monin_data/N/N/N/N/Y/Y/N/N/N/Y', 'http://151.106.4.238/portal/reports/monin', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:103.0) Gecko/20100101 Firefox/103.0', 'reports/monin_data', '2022-08-19 14:35:07');
INSERT INTO `activity_site_log` VALUES ('10600', 'track', 'tkf3quhtn0tf9e8l9kpvfc7ie1lae389', 'Open Voips', 'SYSTEM', '103.94.84.37', '103.94.84.37', '/portal/reports/monin_data/N/N/N/N/Y/Y/N/N/N/Y', 'http://151.106.4.238/portal/reports/monin', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:103.0) Gecko/20100101 Firefox/103.0', 'reports/monin_data', '2022-08-19 14:35:13');
INSERT INTO `activity_site_log` VALUES ('10601', 'track', 'tkf3quhtn0tf9e8l9kpvfc7ie1lae389', 'Open Voips', 'SYSTEM', '103.94.84.37', '103.94.84.37', '/portal/reports/monin_data/Y/Y/Y/Y/N/N/Y', 'http://151.106.4.238/portal/reports/monin', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:103.0) Gecko/20100101 Firefox/103.0', 'reports/monin_data', '2022-08-19 14:35:15');
INSERT INTO `activity_site_log` VALUES ('10602', 'track', 'tkf3quhtn0tf9e8l9kpvfc7ie1lae389', 'Open Voips', 'SYSTEM', '103.94.84.37', '103.94.84.37', '/portal/reports/monin_data/N/N/N/N/Y/Y/N/N/N/Y', 'http://151.106.4.238/portal/reports/monin', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:103.0) Gecko/20100101 Firefox/103.0', 'reports/monin_data', '2022-08-19 14:35:18');
INSERT INTO `activity_site_log` VALUES ('10603', 'track', 'tkf3quhtn0tf9e8l9kpvfc7ie1lae389', 'Open Voips', 'SYSTEM', '103.94.84.37', '103.94.84.37', '/portal/reports/monin_data/N/N/N/N/Y/Y/N/N/N/Y', 'http://151.106.4.238/portal/reports/monin', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:103.0) Gecko/20100101 Firefox/103.0', 'reports/monin_data', '2022-08-19 14:35:25');
INSERT INTO `activity_site_log` VALUES ('10604', 'track', 'tkf3quhtn0tf9e8l9kpvfc7ie1lae389', 'Open Voips', 'SYSTEM', '103.94.84.37', '103.94.84.37', '/portal/reports/monin_data/N/N/N/N/Y/Y/N/N/N/Y', 'http://151.106.4.238/portal/reports/monin', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:103.0) Gecko/20100101 Firefox/103.0', 'reports/monin_data', '2022-08-19 14:35:31');
INSERT INTO `activity_site_log` VALUES ('10605', 'track', 'tkf3quhtn0tf9e8l9kpvfc7ie1lae389', 'Open Voips', 'SYSTEM', '103.94.84.37', '103.94.84.37', '/portal/reports/monin_data/N/N/N/N/Y/Y/N/N/N/Y', 'http://151.106.4.238/portal/reports/monin', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:103.0) Gecko/20100101 Firefox/103.0', 'reports/monin_data', '2022-08-19 14:35:37');
INSERT INTO `activity_site_log` VALUES ('10606', 'track', 'tkf3quhtn0tf9e8l9kpvfc7ie1lae389', 'Open Voips', 'SYSTEM', '103.94.84.37', '103.94.84.37', '/portal/reports/monin_data/N/N/N/N/Y/Y/N/N/N/Y', 'http://151.106.4.238/portal/reports/monin', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:103.0) Gecko/20100101 Firefox/103.0', 'reports/monin_data', '2022-08-19 14:35:43');
INSERT INTO `activity_site_log` VALUES ('10607', 'track', 'tkf3quhtn0tf9e8l9kpvfc7ie1lae389', 'Open Voips', 'SYSTEM', '103.94.84.37', '103.94.84.37', '/portal/reports/monin_data/Y/Y/Y/Y/N/N/Y', 'http://151.106.4.238/portal/reports/monin', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:103.0) Gecko/20100101 Firefox/103.0', 'reports/monin_data', '2022-08-19 14:35:56');
INSERT INTO `activity_site_log` VALUES ('10608', 'track', 'tkf3quhtn0tf9e8l9kpvfc7ie1lae389', 'Open Voips', 'SYSTEM', '103.94.84.37', '103.94.84.37', '/portal/reports/monin_data/N/N/N/N/Y/Y/N/N/N/Y', 'http://151.106.4.238/portal/reports/monin', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:103.0) Gecko/20100101 Firefox/103.0', 'reports/monin_data', '2022-08-19 14:36:13');
INSERT INTO `activity_site_log` VALUES ('10609', 'track', 'tkf3quhtn0tf9e8l9kpvfc7ie1lae389', 'Open Voips', 'SYSTEM', '103.94.84.37', '103.94.84.37', '/portal/reports/monin_data/N/N/N/N/Y/Y/N/N/N/Y', 'http://151.106.4.238/portal/reports/monin', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:103.0) Gecko/20100101 Firefox/103.0', 'reports/monin_data', '2022-08-19 14:36:19');
INSERT INTO `activity_site_log` VALUES ('10610', 'track', 'tkf3quhtn0tf9e8l9kpvfc7ie1lae389', 'Open Voips', 'SYSTEM', '103.94.84.37', '103.94.84.37', '/portal/reports/monin_data/N/N/N/N/Y/Y/N/N/N/Y', 'http://151.106.4.238/portal/reports/monin', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:103.0) Gecko/20100101 Firefox/103.0', 'reports/monin_data', '2022-08-19 14:36:25');
INSERT INTO `activity_site_log` VALUES ('10611', 'track', '5aujdruacsvp6tm8celj2vh893jpro2b', 'Open Voips', 'SYSTEM', '103.94.84.37', '103.94.84.37', '/portal/reports/monin', 'http://151.106.4.238/portal/dashboard', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:103.0) Gecko/20100101 Firefox/103.0', 'reports/monin', '2022-08-19 14:36:27');
INSERT INTO `activity_site_log` VALUES ('10612', 'track', '5aujdruacsvp6tm8celj2vh893jpro2b', 'Open Voips', 'SYSTEM', '103.94.84.37', '103.94.84.37', '/portal/reports/monin_data/N/N/N/N/N/N/N/Y/Y', 'http://151.106.4.238/portal/reports/monin', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:103.0) Gecko/20100101 Firefox/103.0', 'reports/monin_data', '2022-08-19 14:36:28');
INSERT INTO `activity_site_log` VALUES ('10613', 'track', '5aujdruacsvp6tm8celj2vh893jpro2b', 'Open Voips', 'SYSTEM', '103.94.84.37', '103.94.84.37', '/portal/reports/monin_data/N/N/N/N/Y/Y/N/N/N/Y', 'http://151.106.4.238/portal/reports/monin', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:103.0) Gecko/20100101 Firefox/103.0', 'reports/monin_data', '2022-08-19 14:36:28');
INSERT INTO `activity_site_log` VALUES ('10614', 'track', '5aujdruacsvp6tm8celj2vh893jpro2b', 'Open Voips', 'SYSTEM', '103.94.84.37', '103.94.84.37', '/portal/reports/monin_data/Y/Y/Y/Y/N/N/Y', 'http://151.106.4.238/portal/reports/monin', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:103.0) Gecko/20100101 Firefox/103.0', 'reports/monin_data', '2022-08-19 14:36:28');
INSERT INTO `activity_site_log` VALUES ('10615', 'track', '5aujdruacsvp6tm8celj2vh893jpro2b', 'Open Voips', 'SYSTEM', '103.94.84.37', '103.94.84.37', '/portal/reports/monin', 'http://151.106.4.238/portal/dashboard', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:103.0) Gecko/20100101 Firefox/103.0', 'reports/monin', '2022-08-19 14:36:29');
INSERT INTO `activity_site_log` VALUES ('10616', 'track', '5aujdruacsvp6tm8celj2vh893jpro2b', 'Open Voips', 'SYSTEM', '103.94.84.37', '103.94.84.37', '/portal/reports/monin_data/N/N/N/N/N/N/N/Y/Y', 'http://151.106.4.238/portal/reports/monin', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:103.0) Gecko/20100101 Firefox/103.0', 'reports/monin_data', '2022-08-19 14:36:30');
INSERT INTO `activity_site_log` VALUES ('10617', 'track', '5aujdruacsvp6tm8celj2vh893jpro2b', 'Open Voips', 'SYSTEM', '103.94.84.37', '103.94.84.37', '/portal/reports/monin_data/N/N/N/N/Y/Y/N/N/N/Y', 'http://151.106.4.238/portal/reports/monin', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:103.0) Gecko/20100101 Firefox/103.0', 'reports/monin_data', '2022-08-19 14:36:30');
INSERT INTO `activity_site_log` VALUES ('10618', 'track', '5aujdruacsvp6tm8celj2vh893jpro2b', 'Open Voips', 'SYSTEM', '103.94.84.37', '103.94.84.37', '/portal/reports/monin_data/Y/Y/Y/Y/N/N/Y', 'http://151.106.4.238/portal/reports/monin', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:103.0) Gecko/20100101 Firefox/103.0', 'reports/monin_data', '2022-08-19 14:36:30');
INSERT INTO `activity_site_log` VALUES ('10619', 'track', '5aujdruacsvp6tm8celj2vh893jpro2b', 'Open Voips', 'SYSTEM', '103.94.84.37', '103.94.84.37', '/portal/reports/monin', 'http://151.106.4.238/portal/dashboard', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:103.0) Gecko/20100101 Firefox/103.0', 'reports/monin', '2022-08-19 14:36:31');
INSERT INTO `activity_site_log` VALUES ('10620', 'track', '5aujdruacsvp6tm8celj2vh893jpro2b', 'Open Voips', 'SYSTEM', '103.94.84.37', '103.94.84.37', '/portal/reports/monin_data/N/N/N/N/Y/Y/N/N/N/Y', 'http://151.106.4.238/portal/reports/monin', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:103.0) Gecko/20100101 Firefox/103.0', 'reports/monin_data', '2022-08-19 14:36:32');
INSERT INTO `activity_site_log` VALUES ('10621', 'track', '5aujdruacsvp6tm8celj2vh893jpro2b', 'Open Voips', 'SYSTEM', '103.94.84.37', '103.94.84.37', '/portal/reports/monin_data/N/N/N/N/N/N/N/Y/Y', 'http://151.106.4.238/portal/reports/monin', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:103.0) Gecko/20100101 Firefox/103.0', 'reports/monin_data', '2022-08-19 14:36:32');
INSERT INTO `activity_site_log` VALUES ('10622', 'track', '5aujdruacsvp6tm8celj2vh893jpro2b', 'Open Voips', 'SYSTEM', '103.94.84.37', '103.94.84.37', '/portal/reports/monin_data/Y/Y/Y/Y/N/N/Y', 'http://151.106.4.238/portal/reports/monin', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:103.0) Gecko/20100101 Firefox/103.0', 'reports/monin_data', '2022-08-19 14:36:32');
INSERT INTO `activity_site_log` VALUES ('10623', 'track', '5aujdruacsvp6tm8celj2vh893jpro2b', 'Open Voips', 'SYSTEM', '103.94.84.37', '103.94.84.37', '/portal/reports/monin_data/N/N/N/N/Y/Y/N/N/N/Y', 'http://151.106.4.238/portal/reports/monin', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:103.0) Gecko/20100101 Firefox/103.0', 'reports/monin_data', '2022-08-19 14:36:38');
INSERT INTO `activity_site_log` VALUES ('10624', 'track', '5aujdruacsvp6tm8celj2vh893jpro2b', 'Open Voips', 'SYSTEM', '103.94.84.37', '103.94.84.37', '/portal/reports/monin', 'http://151.106.4.238/portal/dashboard', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:103.0) Gecko/20100101 Firefox/103.0', 'reports/monin', '2022-08-19 14:36:40');
INSERT INTO `activity_site_log` VALUES ('10625', 'track', '5aujdruacsvp6tm8celj2vh893jpro2b', 'Open Voips', 'SYSTEM', '103.94.84.37', '103.94.84.37', '/portal/reports/monin_data/N/N/N/N/Y/Y/N/N/N/Y', 'http://151.106.4.238/portal/reports/monin', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:103.0) Gecko/20100101 Firefox/103.0', 'reports/monin_data', '2022-08-19 14:36:41');
INSERT INTO `activity_site_log` VALUES ('10626', 'track', '5aujdruacsvp6tm8celj2vh893jpro2b', 'Open Voips', 'SYSTEM', '103.94.84.37', '103.94.84.37', '/portal/reports/monin_data/N/N/N/N/N/N/N/Y/Y', 'http://151.106.4.238/portal/reports/monin', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:103.0) Gecko/20100101 Firefox/103.0', 'reports/monin_data', '2022-08-19 14:36:41');
INSERT INTO `activity_site_log` VALUES ('10627', 'track', '5aujdruacsvp6tm8celj2vh893jpro2b', 'Open Voips', 'SYSTEM', '103.94.84.37', '103.94.84.37', '/portal/reports/monin_data/Y/Y/Y/Y/N/N/Y', 'http://151.106.4.238/portal/reports/monin', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:103.0) Gecko/20100101 Firefox/103.0', 'reports/monin_data', '2022-08-19 14:36:41');
INSERT INTO `activity_site_log` VALUES ('10628', 'track', '5aujdruacsvp6tm8celj2vh893jpro2b', 'Open Voips', 'SYSTEM', '103.94.84.37', '103.94.84.37', '/portal/reports/monin_data/N/N/N/N/Y/Y/N/N/N/Y', 'http://151.106.4.238/portal/reports/monin', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:103.0) Gecko/20100101 Firefox/103.0', 'reports/monin_data', '2022-08-19 14:36:48');
INSERT INTO `activity_site_log` VALUES ('10629', 'track', '5aujdruacsvp6tm8celj2vh893jpro2b', 'Open Voips', 'SYSTEM', '103.94.84.37', '103.94.84.37', '/portal/reports/monin_data/N/N/N/N/Y/Y/N/N/N/Y', 'http://151.106.4.238/portal/reports/monin', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:103.0) Gecko/20100101 Firefox/103.0', 'reports/monin_data', '2022-08-19 14:36:54');
INSERT INTO `activity_site_log` VALUES ('10630', 'track', '5aujdruacsvp6tm8celj2vh893jpro2b', 'Open Voips', 'SYSTEM', '103.94.84.37', '103.94.84.37', '/portal/reports/monin_data/N/N/N/N/Y/Y/N/N/N/Y', 'http://151.106.4.238/portal/reports/monin', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:103.0) Gecko/20100101 Firefox/103.0', 'reports/monin_data', '2022-08-19 14:37:00');
INSERT INTO `activity_site_log` VALUES ('10631', 'track', '5aujdruacsvp6tm8celj2vh893jpro2b', 'Open Voips', 'SYSTEM', '103.94.84.37', '103.94.84.37', '/portal/reports/monin_data/N/N/N/N/Y/Y/N/N/N/Y', 'http://151.106.4.238/portal/reports/monin', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:103.0) Gecko/20100101 Firefox/103.0', 'reports/monin_data', '2022-08-19 14:37:06');
INSERT INTO `activity_site_log` VALUES ('10632', 'track', '5aujdruacsvp6tm8celj2vh893jpro2b', 'Open Voips', 'SYSTEM', '103.94.84.37', '103.94.84.37', '/portal/reports/monin_data/N/N/N/N/Y/Y/N/N/N/Y', 'http://151.106.4.238/portal/reports/monin', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:103.0) Gecko/20100101 Firefox/103.0', 'reports/monin_data', '2022-08-19 14:37:12');
INSERT INTO `activity_site_log` VALUES ('10633', 'track', '5aujdruacsvp6tm8celj2vh893jpro2b', 'Open Voips', 'SYSTEM', '103.94.84.37', '103.94.84.37', '/portal/reports/monin_data/N/N/N/N/Y/Y/N/N/N/Y', 'http://151.106.4.238/portal/reports/monin', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:103.0) Gecko/20100101 Firefox/103.0', 'reports/monin_data', '2022-08-19 14:37:18');
INSERT INTO `activity_site_log` VALUES ('10634', 'track', '5aujdruacsvp6tm8celj2vh893jpro2b', 'Open Voips', 'SYSTEM', '103.94.84.37', '103.94.84.37', '/portal/reports/monin_data/Y/Y/Y/Y/N/N/Y', 'http://151.106.4.238/portal/reports/monin', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:103.0) Gecko/20100101 Firefox/103.0', 'reports/monin_data', '2022-08-19 14:37:22');
INSERT INTO `activity_site_log` VALUES ('10635', 'track', '5aujdruacsvp6tm8celj2vh893jpro2b', 'Open Voips', 'SYSTEM', '103.94.84.37', '103.94.84.37', '/portal/reports/monin_data/N/N/N/N/Y/Y/N/N/N/Y', 'http://151.106.4.238/portal/reports/monin', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:103.0) Gecko/20100101 Firefox/103.0', 'reports/monin_data', '2022-08-19 14:37:24');
INSERT INTO `activity_site_log` VALUES ('10636', 'track', '5aujdruacsvp6tm8celj2vh893jpro2b', 'Open Voips', 'SYSTEM', '103.94.84.37', '103.94.84.37', '/portal/reports/monin_data/N/N/N/N/Y/Y/N/N/N/Y', 'http://151.106.4.238/portal/reports/monin', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:103.0) Gecko/20100101 Firefox/103.0', 'reports/monin_data', '2022-08-19 14:37:31');
INSERT INTO `activity_site_log` VALUES ('10637', 'track', '5aujdruacsvp6tm8celj2vh893jpro2b', 'Open Voips', 'SYSTEM', '103.94.84.37', '103.94.84.37', '/portal/reports/monin_data/N/N/N/N/Y/Y/N/N/N/Y', 'http://151.106.4.238/portal/reports/monin', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:103.0) Gecko/20100101 Firefox/103.0', 'reports/monin_data', '2022-08-19 14:37:37');
INSERT INTO `activity_site_log` VALUES ('10638', 'track', '5aujdruacsvp6tm8celj2vh893jpro2b', 'Open Voips', 'SYSTEM', '103.94.84.37', '103.94.84.37', '/portal/reports/monin_data/N/N/N/N/Y/Y/N/N/N/Y', 'http://151.106.4.238/portal/reports/monin', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:103.0) Gecko/20100101 Firefox/103.0', 'reports/monin_data', '2022-08-19 14:37:43');
INSERT INTO `activity_site_log` VALUES ('10639', 'track', '5aujdruacsvp6tm8celj2vh893jpro2b', 'Open Voips', 'SYSTEM', '103.94.84.37', '103.94.84.37', '/portal/reports/monin_data/N/N/N/N/Y/Y/N/N/N/Y', 'http://151.106.4.238/portal/reports/monin', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:103.0) Gecko/20100101 Firefox/103.0', 'reports/monin_data', '2022-08-19 14:37:49');
INSERT INTO `activity_site_log` VALUES ('10640', 'track', '5aujdruacsvp6tm8celj2vh893jpro2b', 'Open Voips', 'SYSTEM', '103.94.84.37', '103.94.84.37', '/portal/reports/monin_data/N/N/N/N/Y/Y/N/N/N/Y', 'http://151.106.4.238/portal/reports/monin', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:103.0) Gecko/20100101 Firefox/103.0', 'reports/monin_data', '2022-08-19 14:37:55');
INSERT INTO `activity_site_log` VALUES ('10641', 'track', '5aujdruacsvp6tm8celj2vh893jpro2b', 'Open Voips', 'SYSTEM', '103.94.84.37', '103.94.84.37', '/portal/reports/monin_data/N/N/N/N/Y/Y/N/N/N/Y', 'http://151.106.4.238/portal/reports/monin', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:103.0) Gecko/20100101 Firefox/103.0', 'reports/monin_data', '2022-08-19 14:38:01');
INSERT INTO `activity_site_log` VALUES ('10642', 'track', '5aujdruacsvp6tm8celj2vh893jpro2b', 'Open Voips', 'SYSTEM', '103.94.84.37', '103.94.84.37', '/portal/reports/monin_data/Y/Y/Y/Y/N/N/Y', 'http://151.106.4.238/portal/reports/monin', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:103.0) Gecko/20100101 Firefox/103.0', 'reports/monin_data', '2022-08-19 14:38:03');
INSERT INTO `activity_site_log` VALUES ('10643', 'track', '5aujdruacsvp6tm8celj2vh893jpro2b', 'Open Voips', 'SYSTEM', '103.94.84.37', '103.94.84.37', '/portal/reports/monin_data/N/N/N/N/Y/Y/N/N/N/Y', 'http://151.106.4.238/portal/reports/monin', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:103.0) Gecko/20100101 Firefox/103.0', 'reports/monin_data', '2022-08-19 14:38:07');
INSERT INTO `activity_site_log` VALUES ('10644', 'track', '5aujdruacsvp6tm8celj2vh893jpro2b', 'Open Voips', 'SYSTEM', '103.94.84.37', '103.94.84.37', '/portal/reports/monin_data/N/N/N/N/Y/Y/N/N/N/Y', 'http://151.106.4.238/portal/reports/monin', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:103.0) Gecko/20100101 Firefox/103.0', 'reports/monin_data', '2022-08-19 14:38:13');
INSERT INTO `activity_site_log` VALUES ('10645', 'track', '5aujdruacsvp6tm8celj2vh893jpro2b', 'Open Voips', 'SYSTEM', '103.94.84.37', '103.94.84.37', '/portal/reports/monin_data/N/N/N/N/Y/Y/N/N/N/Y', 'http://151.106.4.238/portal/reports/monin', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:103.0) Gecko/20100101 Firefox/103.0', 'reports/monin_data', '2022-08-19 14:38:19');
INSERT INTO `activity_site_log` VALUES ('10646', 'track', '5aujdruacsvp6tm8celj2vh893jpro2b', 'Open Voips', 'SYSTEM', '103.94.84.37', '103.94.84.37', '/portal/reports/monin_data/N/N/N/N/Y/Y/N/N/N/Y', 'http://151.106.4.238/portal/reports/monin', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:103.0) Gecko/20100101 Firefox/103.0', 'reports/monin_data', '2022-08-19 14:38:25');
INSERT INTO `activity_site_log` VALUES ('10647', 'track', '5aujdruacsvp6tm8celj2vh893jpro2b', 'Open Voips', 'SYSTEM', '103.94.84.37', '103.94.84.37', '/portal/reports/monin_data/N/N/N/N/Y/Y/N/N/N/Y', 'http://151.106.4.238/portal/reports/monin', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:103.0) Gecko/20100101 Firefox/103.0', 'reports/monin_data', '2022-08-19 14:38:31');
INSERT INTO `activity_site_log` VALUES ('10648', 'track', '5aujdruacsvp6tm8celj2vh893jpro2b', 'Open Voips', 'SYSTEM', '103.94.84.37', '103.94.84.37', '/portal/reports/monin_data/N/N/N/N/Y/Y/N/N/N/Y', 'http://151.106.4.238/portal/reports/monin', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:103.0) Gecko/20100101 Firefox/103.0', 'reports/monin_data', '2022-08-19 14:38:37');
INSERT INTO `activity_site_log` VALUES ('10649', 'track', '5aujdruacsvp6tm8celj2vh893jpro2b', 'Open Voips', 'SYSTEM', '103.94.84.37', '103.94.84.37', '/portal/reports/monin_data/N/N/N/N/N/N/N/Y/Y', 'http://151.106.4.238/portal/reports/monin', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:103.0) Gecko/20100101 Firefox/103.0', 'reports/monin_data', '2022-08-19 14:38:42');
INSERT INTO `activity_site_log` VALUES ('10650', 'track', '5aujdruacsvp6tm8celj2vh893jpro2b', 'Open Voips', 'SYSTEM', '103.94.84.37', '103.94.84.37', '/portal/reports/monin_data/Y/Y/Y/Y/N/N/Y', 'http://151.106.4.238/portal/reports/monin', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:103.0) Gecko/20100101 Firefox/103.0', 'reports/monin_data', '2022-08-19 14:38:43');
INSERT INTO `activity_site_log` VALUES ('10651', 'track', '5aujdruacsvp6tm8celj2vh893jpro2b', 'Open Voips', 'SYSTEM', '103.94.84.37', '103.94.84.37', '/portal/reports/monin_data/N/N/N/N/Y/Y/N/N/N/Y', 'http://151.106.4.238/portal/reports/monin', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:103.0) Gecko/20100101 Firefox/103.0', 'reports/monin_data', '2022-08-19 14:38:43');
INSERT INTO `activity_site_log` VALUES ('10652', 'track', '5aujdruacsvp6tm8celj2vh893jpro2b', 'Open Voips', 'SYSTEM', '103.94.84.37', '103.94.84.37', '/portal/reports/monin_data/N/N/N/N/Y/Y/N/N/N/Y', 'http://151.106.4.238/portal/reports/monin', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:103.0) Gecko/20100101 Firefox/103.0', 'reports/monin_data', '2022-08-19 14:38:49');
INSERT INTO `activity_site_log` VALUES ('10653', 'track', '5aujdruacsvp6tm8celj2vh893jpro2b', 'Open Voips', 'SYSTEM', '103.94.84.37', '103.94.84.37', '/portal/reports/monin_data/N/N/N/N/Y/Y/N/N/N/Y', 'http://151.106.4.238/portal/reports/monin', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:103.0) Gecko/20100101 Firefox/103.0', 'reports/monin_data', '2022-08-19 14:38:56');
INSERT INTO `activity_site_log` VALUES ('10654', 'track', '5aujdruacsvp6tm8celj2vh893jpro2b', 'Open Voips', 'SYSTEM', '103.94.84.37', '103.94.84.37', '/portal/reports/monin_data/N/N/N/N/Y/Y/N/N/N/Y', 'http://151.106.4.238/portal/reports/monin', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:103.0) Gecko/20100101 Firefox/103.0', 'reports/monin_data', '2022-08-19 14:39:01');
INSERT INTO `activity_site_log` VALUES ('10655', 'track', '5aujdruacsvp6tm8celj2vh893jpro2b', 'Open Voips', 'SYSTEM', '103.94.84.37', '103.94.84.37', '/portal/reports/monin', 'http://151.106.4.238/portal/dashboard', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:103.0) Gecko/20100101 Firefox/103.0', 'reports/monin', '2022-08-19 14:39:07');
INSERT INTO `activity_site_log` VALUES ('10656', 'track', '5aujdruacsvp6tm8celj2vh893jpro2b', 'Open Voips', 'SYSTEM', '103.94.84.37', '103.94.84.37', '/portal/reports/monin_data/N/N/N/N/Y/Y/N/N/N/Y', 'http://151.106.4.238/portal/reports/monin', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:103.0) Gecko/20100101 Firefox/103.0', 'reports/monin_data', '2022-08-19 14:39:07');
INSERT INTO `activity_site_log` VALUES ('10657', 'track', '5aujdruacsvp6tm8celj2vh893jpro2b', 'Open Voips', 'SYSTEM', '103.94.84.37', '103.94.84.37', '/portal/reports/monin_data/Y/Y/Y/Y/N/N/Y', 'http://151.106.4.238/portal/reports/monin', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:103.0) Gecko/20100101 Firefox/103.0', 'reports/monin_data', '2022-08-19 14:39:08');
INSERT INTO `activity_site_log` VALUES ('10658', 'track', '5aujdruacsvp6tm8celj2vh893jpro2b', 'Open Voips', 'SYSTEM', '103.94.84.37', '103.94.84.37', '/portal/reports/monin_data/N/N/N/N/Y/Y/N/N/N/Y', 'http://151.106.4.238/portal/reports/monin', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:103.0) Gecko/20100101 Firefox/103.0', 'reports/monin_data', '2022-08-19 14:39:08');
INSERT INTO `activity_site_log` VALUES ('10659', 'track', '5aujdruacsvp6tm8celj2vh893jpro2b', 'Open Voips', 'SYSTEM', '103.94.84.37', '103.94.84.37', '/portal/reports/monin_data/N/N/N/N/N/N/N/Y/Y', 'http://151.106.4.238/portal/reports/monin', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:103.0) Gecko/20100101 Firefox/103.0', 'reports/monin_data', '2022-08-19 14:39:08');
INSERT INTO `activity_site_log` VALUES ('10660', 'track', '5aujdruacsvp6tm8celj2vh893jpro2b', 'Open Voips', 'SYSTEM', '103.94.84.37', '103.94.84.37', '/portal/reports/monin_data/N/N/N/N/Y/Y/N/N/N/Y', 'http://151.106.4.238/portal/reports/monin', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:103.0) Gecko/20100101 Firefox/103.0', 'reports/monin_data', '2022-08-19 14:39:14');
INSERT INTO `activity_site_log` VALUES ('10661', 'track', '5aujdruacsvp6tm8celj2vh893jpro2b', 'Open Voips', 'SYSTEM', '103.94.84.37', '103.94.84.37', '/portal/reports/monin_data/N/N/N/N/Y/Y/N/N/N/Y', 'http://151.106.4.238/portal/reports/monin', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:103.0) Gecko/20100101 Firefox/103.0', 'reports/monin_data', '2022-08-19 14:39:20');
INSERT INTO `activity_site_log` VALUES ('10662', 'track', '5aujdruacsvp6tm8celj2vh893jpro2b', 'Open Voips', 'SYSTEM', '103.94.84.37', '103.94.84.37', '/portal/ratecard', 'http://151.106.4.238/portal/reports/monin', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:103.0) Gecko/20100101 Firefox/103.0', 'ratecard/index', '2022-08-19 14:39:24');
INSERT INTO `activity_site_log` VALUES ('10663', 'track', '5aujdruacsvp6tm8celj2vh893jpro2b', 'Open Voips', 'SYSTEM', '103.94.84.37', '103.94.84.37', '/portal/providers', 'http://151.106.4.238/portal/ratecard', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:103.0) Gecko/20100101 Firefox/103.0', 'providers/index', '2022-08-19 14:39:28');
INSERT INTO `activity_site_log` VALUES ('10664', 'track', '5aujdruacsvp6tm8celj2vh893jpro2b', 'Open Voips', 'SYSTEM', '103.94.84.37', '103.94.84.37', '/portal/carriers', 'http://151.106.4.238/portal/providers', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:103.0) Gecko/20100101 Firefox/103.0', 'carriers/index', '2022-08-19 14:39:31');
INSERT INTO `activity_site_log` VALUES ('10665', 'track', '5aujdruacsvp6tm8celj2vh893jpro2b', 'Open Voips', 'SYSTEM', '103.94.84.37', '103.94.84.37', '/portal/dialplans', 'http://151.106.4.238/portal/carriers', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:103.0) Gecko/20100101 Firefox/103.0', 'dialplans/index', '2022-08-19 14:39:33');
INSERT INTO `activity_site_log` VALUES ('10666', 'track', 'j3vtfu8l76b73srea6jbdiijklipck97', '', '', '103.94.84.37', '103.94.84.37', '/portal/dialplans', 'http://151.106.4.238/portal/carriers', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:103.0) Gecko/20100101 Firefox/103.0', 'dialplans/index', '2022-08-19 16:49:19');
INSERT INTO `activity_site_log` VALUES ('10667', 'track', 'j3vtfu8l76b73srea6jbdiijklipck97', '', '', '103.94.84.37', '103.94.84.37', '/portal/login', 'http://151.106.4.238/portal/dialplans', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:103.0) Gecko/20100101 Firefox/103.0', 'login/index', '2022-08-19 16:49:33');
INSERT INTO `activity_site_log` VALUES ('10668', 'track', 'j3vtfu8l76b73srea6jbdiijklipck97', 'Open Voips', 'SYSTEM', '103.94.84.37', '103.94.84.37', '/portal/dashboard', '', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:103.0) Gecko/20100101 Firefox/103.0', 'dashboard/index', '2022-08-19 16:49:34');
INSERT INTO `activity_site_log` VALUES ('10669', 'track', 'j3vtfu8l76b73srea6jbdiijklipck97', 'Open Voips', 'SYSTEM', '103.94.84.37', '103.94.84.37', '/portal/reports/monin', 'http://151.106.4.238/portal/dashboard', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:103.0) Gecko/20100101 Firefox/103.0', 'reports/monin', '2022-08-19 16:49:37');
INSERT INTO `activity_site_log` VALUES ('10670', 'track', 'j3vtfu8l76b73srea6jbdiijklipck97', 'Open Voips', 'SYSTEM', '103.94.84.37', '103.94.84.37', '/portal/reports/monin_data/N/N/N/N/Y/Y/N/N/N/Y', 'http://151.106.4.238/portal/reports/monin', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:103.0) Gecko/20100101 Firefox/103.0', 'reports/monin_data', '2022-08-19 16:49:38');
INSERT INTO `activity_site_log` VALUES ('10671', 'track', 'j3vtfu8l76b73srea6jbdiijklipck97', 'Open Voips', 'SYSTEM', '103.94.84.37', '103.94.84.37', '/portal/reports/monin_data/Y/Y/Y/Y/N/N/Y', 'http://151.106.4.238/portal/reports/monin', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:103.0) Gecko/20100101 Firefox/103.0', 'reports/monin_data', '2022-08-19 16:49:38');
INSERT INTO `activity_site_log` VALUES ('10672', 'track', 'j3vtfu8l76b73srea6jbdiijklipck97', 'Open Voips', 'SYSTEM', '103.94.84.37', '103.94.84.37', '/portal/reports/monin_data/N/N/N/N/N/N/N/Y/Y', 'http://151.106.4.238/portal/reports/monin', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:103.0) Gecko/20100101 Firefox/103.0', 'reports/monin_data', '2022-08-19 16:49:38');
INSERT INTO `activity_site_log` VALUES ('10673', 'track', 'j3vtfu8l76b73srea6jbdiijklipck97', 'Open Voips', 'SYSTEM', '103.94.84.37', '103.94.84.37', '/portal/reports/monin_data/N/N/N/N/Y/Y/N/N/N/Y', 'http://151.106.4.238/portal/reports/monin', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:103.0) Gecko/20100101 Firefox/103.0', 'reports/monin_data', '2022-08-19 16:49:44');
INSERT INTO `activity_site_log` VALUES ('10674', 'track', 'j3vtfu8l76b73srea6jbdiijklipck97', 'Open Voips', 'SYSTEM', '103.94.84.37', '103.94.84.37', '/portal/users', 'http://151.106.4.238/portal/reports/monin', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:103.0) Gecko/20100101 Firefox/103.0', 'users/index', '2022-08-19 16:49:45');
INSERT INTO `activity_site_log` VALUES ('10675', 'track', 'j3vtfu8l76b73srea6jbdiijklipck97', 'Open Voips', 'SYSTEM', '103.94.84.37', '103.94.84.37', '/portal/crs', 'http://151.106.4.238/portal/users', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:103.0) Gecko/20100101 Firefox/103.0', 'crs/index', '2022-08-19 16:49:51');
INSERT INTO `activity_site_log` VALUES ('10676', 'track', 'j3vtfu8l76b73srea6jbdiijklipck97', 'Open Voips', 'SYSTEM', '103.94.84.37', '103.94.84.37', '/portal/users', 'http://151.106.4.238/portal/crs', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:103.0) Gecko/20100101 Firefox/103.0', 'users/index', '2022-08-19 16:51:08');
INSERT INTO `activity_site_log` VALUES ('10677', 'track', 'j3vtfu8l76b73srea6jbdiijklipck97', 'Open Voips', 'SYSTEM', '103.94.84.37', '103.94.84.37', '/portal/crs', 'http://151.106.4.238/portal/users', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:103.0) Gecko/20100101 Firefox/103.0', 'crs/index', '2022-08-19 16:51:10');
INSERT INTO `activity_site_log` VALUES ('10678', 'track', 'j3vtfu8l76b73srea6jbdiijklipck97', 'Open Voips', 'SYSTEM', '103.94.84.37', '103.94.84.37', '/portal/providers', 'http://151.106.4.238/portal/crs', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:103.0) Gecko/20100101 Firefox/103.0', 'providers/index', '2022-08-19 16:51:50');
INSERT INTO `activity_site_log` VALUES ('10679', 'track', 'j3vtfu8l76b73srea6jbdiijklipck97', 'Open Voips', 'SYSTEM', '103.94.84.37', '103.94.84.37', '/portal/carriers', 'http://151.106.4.238/portal/providers', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:103.0) Gecko/20100101 Firefox/103.0', 'carriers/index', '2022-08-19 16:51:52');
INSERT INTO `activity_site_log` VALUES ('10680', 'track', 'j3vtfu8l76b73srea6jbdiijklipck97', 'Open Voips', 'SYSTEM', '103.94.84.37', '103.94.84.37', '/portal/routes', 'http://151.106.4.238/portal/carriers', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:103.0) Gecko/20100101 Firefox/103.0', 'routes/index', '2022-08-19 16:51:54');
INSERT INTO `activity_site_log` VALUES ('10681', 'track', 'j3vtfu8l76b73srea6jbdiijklipck97', 'Open Voips', 'SYSTEM', '103.94.84.37', '103.94.84.37', '/portal/dialplans', 'http://151.106.4.238/portal/routes', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:103.0) Gecko/20100101 Firefox/103.0', 'dialplans/index', '2022-08-19 16:51:56');
INSERT INTO `activity_site_log` VALUES ('10682', 'track', 'j3vtfu8l76b73srea6jbdiijklipck97', 'Open Voips', 'SYSTEM', '103.94.84.37', '103.94.84.37', '/portal/tariffs', 'http://151.106.4.238/portal/dialplans', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:103.0) Gecko/20100101 Firefox/103.0', 'tariffs/index', '2022-08-19 16:51:59');
INSERT INTO `activity_site_log` VALUES ('10683', 'track', 'j3vtfu8l76b73srea6jbdiijklipck97', 'Open Voips', 'SYSTEM', '103.94.84.37', '103.94.84.37', '/portal/rates', 'http://151.106.4.238/portal/tariffs', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:103.0) Gecko/20100101 Firefox/103.0', 'rates/index', '2022-08-19 16:52:03');
INSERT INTO `activity_site_log` VALUES ('10684', 'track', 'j3vtfu8l76b73srea6jbdiijklipck97', 'Open Voips', 'SYSTEM', '103.94.84.37', '103.94.84.37', '/portal/ratecard', 'http://151.106.4.238/portal/rates', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:103.0) Gecko/20100101 Firefox/103.0', 'ratecard/index', '2022-08-19 16:52:04');
INSERT INTO `activity_site_log` VALUES ('10685', 'track', 'j3vtfu8l76b73srea6jbdiijklipck97', 'Open Voips', 'SYSTEM', '103.94.84.37', '103.94.84.37', '/portal/reports/ProfitLoss', 'http://151.106.4.238/portal/ratecard', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:103.0) Gecko/20100101 Firefox/103.0', 'reports/ProfitLoss', '2022-08-19 16:52:09');

-- ----------------------------
-- Table structure for `bill_account_sdr`
-- ----------------------------
DROP TABLE IF EXISTS `bill_account_sdr`;
CREATE TABLE `bill_account_sdr` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `account_id` varchar(30) DEFAULT NULL,
  `rule_type` varchar(30) DEFAULT NULL,
  `service_number` varchar(1000) DEFAULT '',
  `billing_date` date DEFAULT NULL,
  `unit` int(11) DEFAULT '0',
  `rate` double(20,10) DEFAULT '0.0000000000',
  `cost` double(20,10) DEFAULT '0.0000000000',
  `totalcost` double(20,10) DEFAULT '0.0000000000',
  `sallerunit` int(11) DEFAULT '0',
  `sallerrate` double(20,10) DEFAULT '0.0000000000',
  `sallercost` double(20,10) DEFAULT '0.0000000000',
  `totalsallercost` double(20,10) DEFAULT '0.0000000000',
  `startdate` date DEFAULT NULL,
  `enddate` date DEFAULT NULL,
  `createdate` datetime DEFAULT NULL,
  `invoice_id` varchar(50) DEFAULT NULL,
  `dategeneratedby` enum('service','api') DEFAULT 'service',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- ----------------------------
-- Records of bill_account_sdr
-- ----------------------------

-- ----------------------------
-- Table structure for `bill_billing_event`
-- ----------------------------
DROP TABLE IF EXISTS `bill_billing_event`;
CREATE TABLE `bill_billing_event` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `billingeventid` varchar(50) DEFAULT NULL,
  `account_id` varchar(30) DEFAULT NULL,
  `item_id` varchar(30) DEFAULT NULL,
  `price_id` varchar(30) DEFAULT NULL,
  `item_product_id` varchar(30) DEFAULT NULL,
  `quantity` int(11) DEFAULT '1',
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
  `event_delete_status` enum('0','1') DEFAULT '0',
  `child_billingeventid` varchar(50) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `account_id` (`billingeventid`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- ----------------------------
-- Records of bill_billing_event
-- ----------------------------

-- ----------------------------
-- Table structure for `bill_carrier_sdr`
-- ----------------------------
DROP TABLE IF EXISTS `bill_carrier_sdr`;
CREATE TABLE `bill_carrier_sdr` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `carrier_id` varchar(30) DEFAULT NULL,
  `carrier_name` varchar(100) DEFAULT NULL,
  `account_id` varchar(30) DEFAULT NULL,
  `currency_id` int(11) DEFAULT NULL,
  `currency_name` varchar(20) DEFAULT NULL,
  `account_currency_id` int(11) DEFAULT NULL,
  `currency_ratio` decimal(12,6) NOT NULL DEFAULT '1.000000',
  `rule_type` varchar(30) DEFAULT NULL,
  `prefix` varchar(30) DEFAULT NULL,
  `destination` varchar(150) DEFAULT NULL,
  `unit` int(11) DEFAULT '0',
  `rate` double(20,10) DEFAULT '0.0000000000',
  `carriercost` double(20,10) DEFAULT '0.0000000000',
  `carriercost_customer_currency` double(20,10) DEFAULT '0.0000000000',
  `calls_date` date DEFAULT NULL,
  `customer_cost` double(20,10) DEFAULT NULL,
  `customer_rate` double(20,10) DEFAULT NULL,
  `calls` int(11) DEFAULT '0',
  `billing_date` date DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- ----------------------------
-- Records of bill_carrier_sdr
-- ----------------------------

-- ----------------------------
-- Table structure for `bill_customer_priceplan`
-- ----------------------------
DROP TABLE IF EXISTS `bill_customer_priceplan`;
CREATE TABLE `bill_customer_priceplan` (
  `id` bigint(11) NOT NULL AUTO_INCREMENT,
  `account_id` varchar(30) DEFAULT NULL,
  `billing_cycle` enum('DAILY','WEEKLY','MONTHLY') DEFAULT NULL,
  `payment_terms` int(3) DEFAULT NULL,
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
  `updated_dt` timestamp NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  `priceplan_id` varchar(30) DEFAULT NULL,
  `status_message` text,
  `monthly_charges_day` smallint(6) DEFAULT '1',
  `billing_day` smallint(6) DEFAULT '1',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- ----------------------------
-- Records of bill_customer_priceplan
-- ----------------------------

-- ----------------------------
-- Table structure for `bill_customerpricelist`
-- ----------------------------
DROP TABLE IF EXISTS `bill_customerpricelist`;
CREATE TABLE `bill_customerpricelist` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `customer_account_id` varchar(30) DEFAULT NULL,
  `price_id` varchar(30) DEFAULT NULL,
  `item_id` varchar(30) NOT NULL,
  `quantity` int(11) DEFAULT NULL,
  `account_id` varchar(30) NOT NULL,
  `record_type` enum('rate','fixcharge') DEFAULT 'rate',
  `created_by` varchar(30) NOT NULL,
  `updated_by` varchar(30) NOT NULL,
  `created_dt` datetime NOT NULL,
  `updated_dt` timestamp NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `price_id` (`item_id`,`customer_account_id`,`record_type`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ----------------------------
-- Records of bill_customerpricelist
-- ----------------------------

-- ----------------------------
-- Table structure for `bill_itemlist`
-- ----------------------------
DROP TABLE IF EXISTS `bill_itemlist`;
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
  `updated_dt` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `item_id` (`item_id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- ----------------------------
-- Records of bill_itemlist
-- ----------------------------

-- ----------------------------
-- Table structure for `bill_pricelist`
-- ----------------------------
DROP TABLE IF EXISTS `bill_pricelist`;
CREATE TABLE `bill_pricelist` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `price_id` varchar(30) DEFAULT NULL,
  `item_id` varchar(30) NOT NULL,
  `currency_id` varchar(30) DEFAULT '',
  `description` varchar(250) NOT NULL,
  `reguler_charges` enum('EMA','EME','NA') DEFAULT 'NA',
  `free_item` int(4) DEFAULT NULL,
  `charges` double(20,10) DEFAULT '0.0000000000',
  `additional_charges_as` enum('SE','NA') DEFAULT 'NA',
  `additional_charges` double(20,10) DEFAULT '0.0000000000',
  `account_id` varchar(30) NOT NULL,
  `created_by` varchar(30) NOT NULL,
  `updated_by` varchar(30) NOT NULL,
  `created_dt` datetime NOT NULL,
  `updated_dt` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `price_id` (`price_id`,`account_id`,`currency_id`,`item_id`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=54 DEFAULT CHARSET=utf8mb4;

-- ----------------------------
-- Records of bill_pricelist
-- ----------------------------
INSERT INTO `bill_pricelist` VALUES ('14', 'PRI000001290', 'EXTEN', '1', 'Standard Cloud PBX Extension', 'EME', '0', '35.0000000000', 'NA', '0.0000000000', 'SYSTEM', 'ADMIN', 'ADMIN', '2021-02-25 12:53:44', '2021-02-25 17:24:24');
INSERT INTO `bill_pricelist` VALUES ('15', 'PRI000015957', 'NUMBERPORTABLINING', '1', 'Number Portability', 'EME', '0', '0.0000000000', 'SE', '100.0000000000', 'SYSTEM', 'ADMIN', '', '2021-02-25 12:58:09', '2021-02-25 19:28:09');
INSERT INTO `bill_pricelist` VALUES ('16', 'PRI000016967', 'MOBILEAPP', '1', 'Mobile App (Apple / Android)', 'EME', '0', '35.0000000000', 'SE', '150.0000000000', 'SYSTEM', 'ADMIN', '', '2021-02-25 12:58:52', '2021-02-25 19:28:52');
INSERT INTO `bill_pricelist` VALUES ('18', 'PRI000018263', 'EXTEN', '1', 'Wholesale - Extensions DocX', 'EME', '0', '25.0000000000', 'NA', '0.0000000000', 'SYSTEM', 'ADMIN', 'ADMIN', '2021-02-25 13:47:06', '2021-02-25 18:37:33');
INSERT INTO `bill_pricelist` VALUES ('19', 'PRI000019977', 'MOBILEAPP', '1', 'Wholesale - Mobile App (Apple / Android)', 'EME', '0', '17.5000000000', 'NA', '0.0000000000', 'SYSTEM', 'ADMIN', '', '2021-02-25 14:06:31', '2021-02-25 20:36:31');
INSERT INTO `bill_pricelist` VALUES ('20', 'PRI000020791', 'NUMBERPORTABLINING', '1', 'Wholesale - Number Porting', 'EME', '0', '0.0000000000', 'SE', '50.0000000000', 'SYSTEM', 'ADMIN', '', '2021-02-25 14:07:09', '2021-02-25 20:37:09');
INSERT INTO `bill_pricelist` VALUES ('21', 'PRI000021611', 'AWSSERVICES', '1', 'POPI Compliant Call Recordings (AWS S3)', 'EME', '0', '20.0000000000', 'NA', '0.0000000000', 'SYSTEM', 'ADMIN', '', '2021-02-25 17:34:21', '2021-02-26 00:04:21');
INSERT INTO `bill_pricelist` VALUES ('22', 'PRI000022292', 'EXTEN', '1', 'Cloud PBX Extension', 'EME', '0', '65.0000000000', 'NA', '0.0000000000', 'STR100000', 'UR000003359', '', '2021-02-25 20:25:23', '2021-02-26 02:55:23');
INSERT INTO `bill_pricelist` VALUES ('23', 'PRI000023977', 'MOBILEAPP', '1', 'Mobile Application', 'EME', '0', '65.0000000000', 'NA', '0.0000000000', 'STR100000', 'UR000003359', 'UR000003359', '2021-02-25 20:25:55', '2021-05-31 17:19:13');
INSERT INTO `bill_pricelist` VALUES ('24', 'PRI000024273', 'AGENT', '1', 'Hosted ViciDial Agents', 'EME', '0', '40.0000000000', 'NA', '0.0000000000', 'SYSTEM', 'ADMIN', '', '2021-03-02 18:06:17', '2021-03-03 00:36:17');
INSERT INTO `bill_pricelist` VALUES ('25', 'PRI000025748', 'BROADBANDFIBRE', '1', 'Broadband Fibre 100Mbps Uncapped - 24 months', 'EME', '0', '2999.0000000000', 'SE', '2999.0000000000', 'SYSTEM', 'ADMIN', 'ADMIN', '2021-03-02 18:06:58', '2021-03-02 23:44:37');
INSERT INTO `bill_pricelist` VALUES ('26', 'PRI000026166', 'LABOURCHARGES', '1', 'Labour for Installation', 'NA', '0', '0.0000000000', 'SE', '500.0000000000', 'SYSTEM', 'ADMIN', '', '2021-03-02 19:15:58', '2021-03-03 01:45:58');
INSERT INTO `bill_pricelist` VALUES ('27', 'PRI000027213', 'EXTEN', '1', 'DocX Reseller Extensions', 'EME', '0', '25.0000000000', 'NA', '0.0000000000', 'SYSTEM', 'ADMIN', '', '2021-04-26 17:57:59', '2021-04-26 23:27:59');
INSERT INTO `bill_pricelist` VALUES ('28', 'PRI000028980', 'MOBILEAPP', '1', 'Mobile App - Included in Extension', 'NA', '0', '0.0000000000', 'NA', '0.0000000000', 'STR200000', 'UR000021805', '', '2021-05-13 11:47:20', '2021-05-13 17:17:20');
INSERT INTO `bill_pricelist` VALUES ('29', 'PRI000029259', 'EXTEN', '1', 'Executive Extension', 'EME', '0', '70.0000000000', 'NA', '0.0000000000', 'STR200000', 'UR000021805', 'UR000021805', '2021-05-13 11:49:58', '2021-05-13 15:31:45');
INSERT INTO `bill_pricelist` VALUES ('30', 'PRI000030596', 'EXTERPRICEEXTEN', '1', 'Reseller Enterprise Extension', 'EME', '0', '25.0000000000', 'NA', '0.0000000000', 'SYSTEM', 'ADMIN', '', '2021-05-13 11:55:51', '2021-05-13 17:25:51');
INSERT INTO `bill_pricelist` VALUES ('31', 'PRI000031624', 'EXTEN', '1', 'Executive Extension', 'EME', '0', '80.0000000000', 'NA', '0.0000000000', 'STR200000', 'UR000021805', '', '2021-05-13 12:01:31', '2021-05-13 17:31:31');
INSERT INTO `bill_pricelist` VALUES ('32', 'PRI000032435', 'EXTEN', '1', 'Cloud PBX Extension', 'EME', '0', '115.0000000000', 'NA', '0.0000000000', 'STR200000', 'UR000021805', '', '2021-05-13 12:02:10', '2021-05-13 17:32:10');
INSERT INTO `bill_pricelist` VALUES ('33', 'PRI000033799', 'EXTERPRICEEXTEN', '1', 'Enterprise Cloud PBX Extension', 'EME', '0', '50.0000000000', 'NA', '0.0000000000', 'SYSTEM', 'ADMIN', '', '2021-05-13 12:15:42', '2021-05-13 17:45:42');
INSERT INTO `bill_pricelist` VALUES ('34', 'PRI000034639', 'FIXLINEALTERNATIVEFLA', '1', 'Fixed Line Alternative ARC', 'EME', '0', '500.0000000000', 'NA', '0.0000000000', 'SYSTEM', 'ADMIN', '', '2021-05-26 08:02:59', '2021-05-26 13:32:59');
INSERT INTO `bill_pricelist` VALUES ('35', 'PRI000035656', 'VOIPLINERENTAL', '1', 'SureTel - VoIP Line Rental (Afro Asian)', 'EME', '0', '143.8600000000', 'NA', '0.0000000000', 'SYSTEM', 'ADMIN', '', '2021-05-26 09:02:14', '2021-05-26 14:32:14');
INSERT INTO `bill_pricelist` VALUES ('36', 'PRI000036576', 'CLOUDPBXMONTHOLYFREE', '1', 'SureTel\'s - Cloud PBX Monthly Fees (Afro Asian)', 'EME', '0', '71.4300000000', 'NA', '0.0000000000', 'SYSTEM', 'ADMIN', '', '2021-05-26 09:18:10', '2021-05-26 14:48:10');
INSERT INTO `bill_pricelist` VALUES ('37', 'PRI000037817', 'ENTERPRISEFIBRE', '1', '20Mb/s FTTx with uncapped Internet (Afro Asian)', 'EME', '0', '3699.0000000000', 'NA', '0.0000000000', 'SYSTEM', 'ADMIN', '', '2021-05-26 09:20:51', '2021-05-26 14:50:51');
INSERT INTO `bill_pricelist` VALUES ('38', 'PRI000038275', 'ENTERPRISEFIBRE', '1', 'Enterprise Fibre 100Mb/s Uncapped + SLA - 24 Months', 'EME', '0', '6499.0000000000', 'NA', '0.0000000000', 'SYSTEM', 'ADMIN', '', '2021-05-26 09:42:38', '2021-05-26 15:12:38');
INSERT INTO `bill_pricelist` VALUES ('39', 'PRI000039296', 'VOIPGATEWAY', '1', 'PRI VoIP Gateway', 'EME', '0', '1500.0000000000', 'NA', '0.0000000000', 'SYSTEM', 'ADMIN', '', '2021-05-26 09:43:41', '2021-05-26 15:13:41');
INSERT INTO `bill_pricelist` VALUES ('40', 'PRI000040202', 'LICENSEDWIRELESS', '1', '100Mbps 1:10 Broadbank internet access (Comsol ISP) Licenced Microwave link 24 months', 'EME', '0', '5499.0000000000', 'NA', '0.0000000000', 'SYSTEM', 'ADMIN', '', '2021-05-26 09:44:39', '2021-05-26 15:14:39');
INSERT INTO `bill_pricelist` VALUES ('41', 'PRI000041729', 'VOIPLINERENTAL', '1', 'SureTel Voice Channels over Fibre', 'EME', '0', '670.0000000000', 'NA', '0.0000000000', 'SYSTEM', 'ADMIN', '', '2021-05-26 09:46:44', '2021-05-26 15:16:44');
INSERT INTO `bill_pricelist` VALUES ('42', 'PRI000042867', 'FIXLINEALTERNATIVEFLA', '1', 'Fixed Line Alternative', 'EME', '0', '200.0000000000', 'NA', '0.0000000000', 'SYSTEM', 'ADMIN', '', '2021-05-26 09:50:33', '2021-05-26 15:20:33');
INSERT INTO `bill_pricelist` VALUES ('43', 'PRI000043321', 'ENTERPRISEFIBRE', '1', '10Mb/s Premium Uncapped Fibre', 'EME', '0', '3455.0000000000', 'NA', '0.0000000000', 'SYSTEM', 'ADMIN', '', '2021-05-26 10:05:27', '2021-05-26 15:35:27');
INSERT INTO `bill_pricelist` VALUES ('44', 'PRI000044987', 'LTE', '1', '60GB + 60GB Telkom Fixed LTE Combo', 'EME', '0', '531.0000000000', 'NA', '0.0000000000', 'SYSTEM', 'ADMIN', '', '2021-05-26 17:09:44', '2021-05-26 22:39:44');
INSERT INTO `bill_pricelist` VALUES ('45', 'PRI000045556', 'VOIPLINERENTAL', '1', 'SureTel - VoIP Line Rental', 'EME', '0', '25.0000000000', 'NA', '0.0000000000', 'SYSTEM', 'ADMIN', '', '2021-05-26 17:20:28', '2021-05-26 22:50:28');
INSERT INTO `bill_pricelist` VALUES ('46', 'PRI000046353', 'VOIPLINERENTAL', '1', 'SureTel - VoIP Line Rental', 'EME', '0', '1020.0000000000', 'NA', '0.0000000000', 'SYSTEM', 'ADMIN', '', '2021-05-26 17:33:00', '2021-05-26 23:03:00');
INSERT INTO `bill_pricelist` VALUES ('47', 'PRI000047784', 'BUSINESSFIBRE', '1', 'Openserve 2000Mb/s FTTB - Business Uncapped', 'EME', '0', '2500.0000000000', 'NA', '0.0000000000', 'SYSTEM', 'ADMIN', '', '2021-05-26 17:33:45', '2021-05-26 23:03:45');
INSERT INTO `bill_pricelist` VALUES ('49', 'PRI000048937', 'VOICECHANNELS', '1', 'Voice channels on Openserve FTTB', 'EME', '0', '450.0000000000', 'NA', '0.0000000000', 'SYSTEM', 'ADMIN', '', '2021-05-26 18:17:58', '2021-05-26 23:47:58');
INSERT INTO `bill_pricelist` VALUES ('50', 'PRI000050203', 'ENTERPRISEFIBRE', '1', '100 Meg FTTB', 'EME', '0', '3398.0000000000', 'NA', '0.0000000000', 'STR100000', 'UR000003359', '', '2021-05-31 13:50:26', '2021-05-31 19:20:26');
INSERT INTO `bill_pricelist` VALUES ('51', 'PRI000051691', 'SIPTRUNK', '1', 'Line rental(physical numbers)', 'EME', '0', '30.0000000000', 'NA', '0.0000000000', 'STR200000', 'UR000021805', 'UR000021805', '2021-07-18 18:44:08', '2021-07-18 22:15:54');
INSERT INTO `bill_pricelist` VALUES ('52', 'PRI000052756', 'NUMBERPORTABLINING', '1', 'Number Porting', 'NA', '0', '0.0000000000', 'SE', '50.0000000000', 'STR200000', 'UR000021805', '', '2021-07-18 18:48:55', '2021-07-19 00:18:55');
INSERT INTO `bill_pricelist` VALUES ('53', 'PRI000053941', 'EXTEN', '1', 'Extension', 'EME', '0', '10.0000000000', 'NA', '0.0000000000', 'STR700000', 'UR000246857', '', '2021-07-21 16:03:24', '2021-07-21 21:33:24');

-- ----------------------------
-- Table structure for `bill_pricelist_customer`
-- ----------------------------
DROP TABLE IF EXISTS `bill_pricelist_customer`;
CREATE TABLE `bill_pricelist_customer` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `customer_account_id` varchar(30) DEFAULT NULL,
  `price_id` varchar(30) DEFAULT NULL,
  `item_id` varchar(30) NOT NULL,
  `currency_id` varchar(30) DEFAULT '',
  `description` varchar(250) NOT NULL,
  `reguler_charges` enum('EMA','EME','NA') DEFAULT 'NA',
  `free_item` int(4) DEFAULT NULL,
  `charges` double(20,10) DEFAULT '0.0000000000',
  `additional_charges_as` enum('SE','NA') DEFAULT 'NA',
  `additional_charges` double(20,10) DEFAULT '0.0000000000',
  `account_id` varchar(30) NOT NULL,
  `created_by` varchar(30) NOT NULL,
  `updated_by` varchar(30) NOT NULL,
  `created_dt` datetime NOT NULL,
  `updated_dt` timestamp NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `price_id` (`price_id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ----------------------------
-- Records of bill_pricelist_customer
-- ----------------------------

-- ----------------------------
-- Table structure for `bill_priceplan`
-- ----------------------------
DROP TABLE IF EXISTS `bill_priceplan`;
CREATE TABLE `bill_priceplan` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `priceplan_id` varchar(20) NOT NULL,
  `priceplan_name` varchar(250) NOT NULL,
  `account_id` varchar(30) NOT NULL,
  `created_by` varchar(30) NOT NULL,
  `updated_by` varchar(30) NOT NULL,
  `created_dt` datetime NOT NULL,
  `updated_dt` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `currency_id` varchar(30) DEFAULT NULL,
  `status` enum('0','1') DEFAULT '1',
  PRIMARY KEY (`id`),
  UNIQUE KEY `price_plan_id` (`priceplan_id`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4;

-- ----------------------------
-- Records of bill_priceplan
-- ----------------------------
INSERT INTO `bill_priceplan` VALUES ('1', 'PRP000001701', 'Default', 'SYSTEM', 'ADMIN', '', '2021-02-10 16:46:03', '2021-02-17 21:02:06', '1', '1');

-- ----------------------------
-- Table structure for `bill_priceplan_item`
-- ----------------------------
DROP TABLE IF EXISTS `bill_priceplan_item`;
CREATE TABLE `bill_priceplan_item` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `priceplan_item_id` varchar(20) NOT NULL,
  `priceplan_id` varchar(20) NOT NULL,
  `item_id` varchar(20) NOT NULL,
  `price_id` varchar(20) NOT NULL,
  `account_id` varchar(30) NOT NULL,
  `created_by` varchar(30) NOT NULL,
  `updated_by` varchar(30) NOT NULL,
  `created_dt` datetime NOT NULL,
  `updated_dt` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `price_plan_item_id` (`priceplan_item_id`) USING BTREE,
  UNIQUE KEY `itemplan_key` (`priceplan_id`,`item_id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ----------------------------
-- Records of bill_priceplan_item
-- ----------------------------

-- ----------------------------
-- Table structure for `bundle_account`
-- ----------------------------
DROP TABLE IF EXISTS `bundle_account`;
CREATE TABLE `bundle_account` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `bundle_package_id` varchar(30) DEFAULT NULL,
  `account_id` varchar(30) DEFAULT NULL,
  `assign_dt` date DEFAULT NULL,
  `account_bundle_key` varchar(30) DEFAULT NULL,
  `bundle_package_desc` varchar(50) DEFAULT NULL,
  `lastbill_execute_date` date DEFAULT NULL,
  `lastbilldate` date DEFAULT NULL,
  `created_by` varchar(30) DEFAULT NULL,
  `updated_by` varchar(30) DEFAULT NULL,
  `created_dt` datetime DEFAULT NULL,
  `updated_dt` timestamp NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `account_bundle_key` (`account_bundle_key`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- ----------------------------
-- Records of bundle_account
-- ----------------------------

-- ----------------------------
-- Table structure for `bundle_package`
-- ----------------------------
DROP TABLE IF EXISTS `bundle_package`;
CREATE TABLE `bundle_package` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `bundle_package_id` varchar(30) DEFAULT '',
  `bundle_package_name` varchar(30) DEFAULT '',
  `bundle_package_currency_id` int(11) DEFAULT '1',
  `bundle_package_status` enum('1','0') DEFAULT '1',
  `bundle_package_description` varchar(50) DEFAULT '',
  `created_by` varchar(30) NOT NULL,
  `create_dt` datetime DEFAULT NULL,
  `update_dt` timestamp NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  `package_option` enum('1','0') DEFAULT '0',
  `monthly_charges` double DEFAULT '0',
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
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- ----------------------------
-- Records of bundle_package
-- ----------------------------

-- ----------------------------
-- Table structure for `bundle_package_prefixes`
-- ----------------------------
DROP TABLE IF EXISTS `bundle_package_prefixes`;
CREATE TABLE `bundle_package_prefixes` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `bundle_package_id` varchar(30) NOT NULL,
  `bundle_id` enum('1','2','3') NOT NULL DEFAULT '1',
  `prefix` varchar(30) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `bundle_package_id` (`bundle_package_id`,`bundle_id`,`prefix`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- ----------------------------
-- Records of bundle_package_prefixes
-- ----------------------------

-- ----------------------------
-- Table structure for `carrier`
-- ----------------------------
DROP TABLE IF EXISTS `carrier`;
CREATE TABLE `carrier` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `carrier_id` varchar(30) DEFAULT NULL,
  `carrier_name` varchar(30) NOT NULL,
  `tariff_id` varchar(30) NOT NULL,
  `carrier_type` enum('INBOUND','OUTBOUND') DEFAULT 'OUTBOUND',
  `carrier_status` int(11) DEFAULT '1',
  `carrier_cps` int(11) DEFAULT '10',
  `carrier_cc` int(11) DEFAULT '10',
  `carrier_currency_id` int(11) DEFAULT '1',
  `provider_id` varchar(30) DEFAULT NULL,
  `carrier_progress_timeout` int(11) DEFAULT '5',
  `carrier_ring_timeout` int(11) DEFAULT '30',
  `cli_prefer` enum('rpid','pid','no') DEFAULT 'rpid',
  `carrier_codecs` varchar(50) DEFAULT 'G729,PCMU,PCMA',
  `gateway_withmedia` enum('1','0') DEFAULT '0',
  `tax1` float DEFAULT '0',
  `tax2` float DEFAULT '0',
  `tax3` float DEFAULT '0',
  `tax_type` enum('inclusive','exclusive') DEFAULT 'inclusive',
  `dp` int(11) DEFAULT '4',
  `vat_flag` enum('TAX','VAT','NONE','SZE','REVERSE') DEFAULT 'NONE',
  `tax_number` varchar(30) DEFAULT NULL,
  `account_id` varchar(30) DEFAULT NULL,
  `diversion_header_as_comingcli_db` enum('1','0') DEFAULT '1' COMMENT '1=as incoming  CLI; 0= from DB',
  `diversion_header_format` varchar(300) DEFAULT '<sip:${RDN}@${network_addr}>;reason=no-answer;counter=1;privacy=off',
  `diversion_header_option` enum('1','0') DEFAULT '1',
  `created_by` varchar(30) DEFAULT NULL,
  `updated_by` varchar(30) DEFAULT NULL,
  `created_dt` datetime DEFAULT NULL,
  `updated_dt` timestamp NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `key_carrier_id_name` (`carrier_id`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=latin1;

-- ----------------------------
-- Records of carrier
-- ----------------------------
INSERT INTO `carrier` VALUES ('1', 'DEFAULT38', 'Default', 'CARRIER40', 'OUTBOUND', '1', '1', '10', '4', 'DEFAULT414', '50', '60', 'pid', 'G729,PCMU,PCMA,G722', '0', '0', '0', '0', 'exclusive', '4', 'NONE', null, null, '1', '<sip:${RDN}@${network_addr}>;reason=no-answer;counter=1;privacy=off', '1', null, null, null, null);

-- ----------------------------
-- Table structure for `carrier_callerid`
-- ----------------------------
DROP TABLE IF EXISTS `carrier_callerid`;
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
  `updated_dt` timestamp NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `carrier_callerid_key` (`carrier_id`,`maching_string`,`route`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=latin1;

-- ----------------------------
-- Records of carrier_callerid
-- ----------------------------
INSERT INTO `carrier_callerid` VALUES ('1', '%', '', '%', 'DEFAULT38', '%=>%', '1', 'OUTBOUND', null, null, null, null, null);
INSERT INTO `carrier_callerid` VALUES ('2', '%', '', '%', 'DEFAULT38', '%=>%', '1', 'INBOUND', null, null, null, null, null);

-- ----------------------------
-- Table structure for `carrier_ips`
-- ----------------------------
DROP TABLE IF EXISTS `carrier_ips`;
CREATE TABLE `carrier_ips` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `carrier_ip_id` varchar(30) DEFAULT NULL,
  `carrier_id` varchar(30) DEFAULT NULL,
  `ipaddress_name` varchar(30) NOT NULL,
  `ipaddress` varchar(30) DEFAULT NULL,
  `load_share` int(11) NOT NULL DEFAULT '100',
  `priority` smallint(6) DEFAULT '1',
  `ip_status` enum('1','0') DEFAULT '1',
  `auth_type` enum('IP','CUSTOMER') DEFAULT 'IP',
  `username` varchar(50) DEFAULT NULL,
  `passwd` varchar(50) DEFAULT NULL,
  `account_id` varchar(30) DEFAULT NULL,
  `created_by` varchar(30) DEFAULT NULL,
  `updated_by` varchar(30) DEFAULT NULL,
  `created_dt` datetime DEFAULT NULL,
  `updated_dt` timestamp NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `carrier_id` (`carrier_id`,`ipaddress_name`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- ----------------------------
-- Records of carrier_ips
-- ----------------------------

-- ----------------------------
-- Table structure for `carrier_prefix`
-- ----------------------------
DROP TABLE IF EXISTS `carrier_prefix`;
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
  `updated_dt` timestamp NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `carrier_prefix_id_key` (`carrier_id`,`maching_string`,`route`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=latin1;

-- ----------------------------
-- Records of carrier_prefix
-- ----------------------------
INSERT INTO `carrier_prefix` VALUES ('1', 'DEFAULT38', '%', '', '%', '%=>%', 'OUTBOUND', null, null, null, null, null);
INSERT INTO `carrier_prefix` VALUES ('2', 'DEFAULT38', '%', '', '%', '%=>%', 'INBOUND', null, null, null, null, null);

-- ----------------------------
-- Table structure for `carrier_rates`
-- ----------------------------
DROP TABLE IF EXISTS `carrier_rates`;
CREATE TABLE `carrier_rates` (
  `rate_id` int(11) NOT NULL AUTO_INCREMENT,
  `ratecard_id` varchar(30) NOT NULL,
  `prefix` varchar(25) NOT NULL,
  `destination` varchar(150) NOT NULL,
  `setup_charge` double(12,6) NOT NULL DEFAULT '0.000000',
  `rental` double(12,6) NOT NULL DEFAULT '0.000000',
  `rate` double(12,6) NOT NULL DEFAULT '0.000000',
  `connection_charge` double DEFAULT '0',
  `minimal_time` int(11) NOT NULL DEFAULT '1',
  `resolution_time` int(11) DEFAULT '1',
  `grace_period` int(11) DEFAULT '0',
  `rate_multiplier` decimal(5,2) DEFAULT '1.00',
  `rate_addition` decimal(5,2) DEFAULT '0.00',
  `rates_status` enum('0','1') NOT NULL DEFAULT '1',
  `exclusive_per_channel_rental` double(12,6) DEFAULT '0.000000',
  `inclusive_channel` int(11) DEFAULT '1',
  `account_id` varchar(30) DEFAULT NULL,
  `minimal_charge` double(12,6) DEFAULT NULL,
  `ani_prefix` varchar(30) DEFAULT NULL,
  `updated_by` varchar(30) DEFAULT NULL,
  `created_by` varchar(30) DEFAULT NULL,
  `create_dt` timestamp NULL DEFAULT NULL,
  `update_dt` timestamp NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`rate_id`),
  UNIQUE KEY `pt` (`ratecard_id`,`prefix`) USING BTREE,
  KEY `prefix` (`prefix`) USING BTREE,
  KEY `tariff_id` (`ratecard_id`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=latin1;

-- ----------------------------
-- Records of carrier_rates
-- ----------------------------
INSERT INTO `carrier_rates` VALUES ('1', 'CARRIER47', '1', 'USA Route', '0.000000', '0.000000', '0.006000', '0', '1', '1', '0', '1.00', '0.00', '1', '0.000000', '1', null, null, null, null, null, '2021-07-31 15:50:07', '2021-07-31 15:50:07');

-- ----------------------------
-- Table structure for `ci_cookies`
-- ----------------------------
DROP TABLE IF EXISTS `ci_cookies`;
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
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- ----------------------------
-- Records of ci_cookies
-- ----------------------------

-- ----------------------------
-- Table structure for `ci_sessions`
-- ----------------------------
DROP TABLE IF EXISTS `ci_sessions`;
CREATE TABLE `ci_sessions` (
  `session_id` varchar(40) NOT NULL DEFAULT '0',
  `ip_address` varchar(45) NOT NULL DEFAULT '0',
  `user_agent` varchar(120) NOT NULL,
  `last_activity` int(10) unsigned NOT NULL DEFAULT '0',
  `user_data` text NOT NULL,
  PRIMARY KEY (`session_id`),
  KEY `last_activity_idx` (`last_activity`) USING BTREE
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- ----------------------------
-- Records of ci_sessions
-- ----------------------------

-- ----------------------------
-- Table structure for `credit_scheduler`
-- ----------------------------
DROP TABLE IF EXISTS `credit_scheduler`;
CREATE TABLE `credit_scheduler` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `account_id` varchar(30) NOT NULL,
  `credit_amount` double(12,6) NOT NULL,
  `execution_date` datetime NOT NULL,
  `is_emergency_credit` enum('Y','N') NOT NULL DEFAULT 'N',
  `status_id` enum('0','1','2') NOT NULL DEFAULT '0' COMMENT '0=acive,1=executed,2=cancelled',
  `created_by` varchar(30) NOT NULL,
  `create_date` datetime NOT NULL,
  `modify_date` timestamp NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=latin1;

-- ----------------------------
-- Records of credit_scheduler
-- ----------------------------
INSERT INTO `credit_scheduler` VALUES ('1', 'STC100000', '5.000000', '2021-08-01 09:50:49', 'N', '0', 'SYSTEM', '2021-07-31 09:50:49', null);

-- ----------------------------
-- Table structure for `customer_balance`
-- ----------------------------
DROP TABLE IF EXISTS `customer_balance`;
CREATE TABLE `customer_balance` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `credit_limit` double(12,6) DEFAULT '0.000000',
  `balance` double(12,6) DEFAULT '0.000000',
  `account_id` varchar(30) DEFAULT NULL,
  `maxcredit_limit` double(12,6) DEFAULT '0.000000',
  `update_dt` timestamp NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  `service_type` enum('SWITCH','PBX') DEFAULT 'SWITCH',
  PRIMARY KEY (`id`),
  UNIQUE KEY `account_id` (`account_id`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=latin1;

-- ----------------------------
-- Records of customer_balance
-- ----------------------------
INSERT INTO `customer_balance` VALUES ('2', '0.000000', '-22.000000', 'STR100000', null, '2021-07-31 13:22:08', 'SWITCH');
INSERT INTO `customer_balance` VALUES ('3', '0.000000', '-98.863000', 'STC300000', '0.000000', '2021-08-01 09:50:02', 'SWITCH');

-- ----------------------------
-- Table structure for `customer_bundle_sdr`
-- ----------------------------
DROP TABLE IF EXISTS `customer_bundle_sdr`;
CREATE TABLE `customer_bundle_sdr` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `account_id` varchar(30) DEFAULT NULL,
  `account_bundle_key` varchar(50) DEFAULT '',
  `bundle_package_id` varchar(30) DEFAULT '',
  `rule_type` varchar(30) DEFAULT NULL,
  `yearmonth` varchar(10) DEFAULT NULL,
  `bundle_package_name` varchar(150) DEFAULT '',
  `total_allowed` double(18,6) DEFAULT '0.000000',
  `bundle_type` varchar(300) DEFAULT '',
  `sdr_consumption` double(20,6) DEFAULT NULL,
  `service_startdate` date DEFAULT NULL,
  `service_stopdate` date DEFAULT NULL,
  `action_date` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `package_id` (`account_id`,`rule_type`,`yearmonth`,`account_bundle_key`,`bundle_package_id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- ----------------------------
-- Records of customer_bundle_sdr
-- ----------------------------

-- ----------------------------
-- Table structure for `customer_callerid`
-- ----------------------------
DROP TABLE IF EXISTS `customer_callerid`;
CREATE TABLE `customer_callerid` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `maching_string` varchar(30) DEFAULT NULL,
  `match_length` smallint(6) DEFAULT NULL,
  `remove_string` varchar(15) DEFAULT '%',
  `add_string` varchar(15) DEFAULT NULL,
  `account_id` varchar(30) DEFAULT NULL,
  `display_string` varchar(60) DEFAULT NULL,
  `action_type` enum('0','1') DEFAULT '1',
  `route` enum('INBOUND','OUTBOUND','DTSBASEDCLI') DEFAULT 'OUTBOUND',
  `created_by` varchar(30) DEFAULT NULL,
  `updated_by` varchar(30) DEFAULT NULL,
  `created_dt` datetime DEFAULT NULL,
  `updated_dt` timestamp NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  `service_type` enum('SWITCH','PBX') DEFAULT 'SWITCH',
  PRIMARY KEY (`id`),
  UNIQUE KEY `user_callerid_key` (`account_id`,`maching_string`,`route`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=latin1;

-- ----------------------------
-- Records of customer_callerid
-- ----------------------------
INSERT INTO `customer_callerid` VALUES ('7', '%', null, '', '%', 'STR100000', '%=>%', '1', 'INBOUND', 'ADMIN', null, '2021-07-31 09:46:29', null, 'SWITCH');
INSERT INTO `customer_callerid` VALUES ('8', '%', null, '', '%', 'STR100000', '%=>%', '1', 'OUTBOUND', 'ADMIN', null, '2021-07-31 09:46:29', null, 'SWITCH');
INSERT INTO `customer_callerid` VALUES ('9', '%', null, '', '%', 'STC300000', '%=>%', '1', 'INBOUND', 'ADMIN', null, '2021-07-31 12:33:18', null, 'SWITCH');
INSERT INTO `customer_callerid` VALUES ('10', '%', null, '', '%', 'STC300000', '%=>%', '1', 'OUTBOUND', 'ADMIN', null, '2021-07-31 12:33:18', null, 'SWITCH');

-- ----------------------------
-- Table structure for `customer_dialpattern`
-- ----------------------------
DROP TABLE IF EXISTS `customer_dialpattern`;
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
  `updated_dt` timestamp NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  `service_type` enum('SWITCH','PBX') DEFAULT 'SWITCH',
  PRIMARY KEY (`id`),
  UNIQUE KEY `user_dialplan_key` (`account_id`,`maching_string`,`route`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=latin1;

-- ----------------------------
-- Records of customer_dialpattern
-- ----------------------------
INSERT INTO `customer_dialpattern` VALUES ('3', 'STR100000', '%', null, '', '%', '%=>%', '1', 'OUTBOUND', null, null, null, null, 'SWITCH');
INSERT INTO `customer_dialpattern` VALUES ('4', 'STR100000', '%', null, '', '%', '%=>%', '1', 'INBOUND', 'ADMIN', null, '2021-07-31 09:46:29', null, 'SWITCH');
INSERT INTO `customer_dialpattern` VALUES ('5', 'STC300000', '%', null, '', '%', '%=>%', '1', 'OUTBOUND', 'ADMIN', null, '2021-07-31 12:33:18', null, 'SWITCH');
INSERT INTO `customer_dialpattern` VALUES ('6', 'STC300000', '%', null, '', '%', '%=>%', '1', 'INBOUND', 'ADMIN', null, '2021-07-31 12:33:18', null, 'SWITCH');

-- ----------------------------
-- Table structure for `customer_dialplan`
-- ----------------------------
DROP TABLE IF EXISTS `customer_dialplan`;
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
  `updated_dt` timestamp NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `user_carrier_dialplan_key` (`account_id`,`maching_string`) USING BTREE,
  KEY `maching_string_key` (`maching_string`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=latin1;

-- ----------------------------
-- Records of customer_dialplan
-- ----------------------------
INSERT INTO `customer_dialplan` VALUES ('3', 'STC300000', 'DEFAULT28', '%', '%=>DEFAULT28%', '', null, null, null, null, null);

-- ----------------------------
-- Table structure for `customer_ips`
-- ----------------------------
DROP TABLE IF EXISTS `customer_ips`;
CREATE TABLE `customer_ips` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `account_id` varchar(30) DEFAULT '',
  `ipaddress` varchar(30) DEFAULT NULL,
  `ip_status` enum('1','0') DEFAULT '1',
  `ip_cc` int(11) DEFAULT '10',
  `ip_cps` int(11) DEFAULT '1',
  `description` varchar(30) DEFAULT NULL,
  `dialprefix` varchar(30) DEFAULT NULL,
  `ipauthfrom` enum('SRC','FROM','NO') DEFAULT 'SRC',
  `billingcode` varchar(30) DEFAULT NULL,
  `created_by` varchar(30) DEFAULT NULL,
  `updated_by` varchar(30) DEFAULT NULL,
  `created_dt` datetime DEFAULT NULL,
  `updated_dt` timestamp NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `user_ips_ipaddress_key` (`ipaddress`,`dialprefix`,`billingcode`) USING BTREE,
  KEY `account_id` (`account_id`) USING BTREE,
  KEY `ipaddress` (`ipaddress`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=latin1;

-- ----------------------------
-- Records of customer_ips
-- ----------------------------
INSERT INTO `customer_ips` VALUES ('1', 'STC100000', '111111', '1', '1', '1', 'ddd', '%', 'SRC', '111', null, null, null, '2021-07-31 13:09:06');

-- ----------------------------
-- Table structure for `customer_notification`
-- ----------------------------
DROP TABLE IF EXISTS `customer_notification`;
CREATE TABLE `customer_notification` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `account_id` varchar(30) NOT NULL,
  `notify_name` enum('low-balance','daily-balance') NOT NULL,
  `notify_emails` varchar(255) NOT NULL,
  `notify_amount` varchar(50) NOT NULL,
  `status` enum('Y','N') NOT NULL,
  `email_status` enum('1','0') DEFAULT '0',
  `update_dt` timestamp NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `account_id` (`account_id`,`notify_name`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- ----------------------------
-- Records of customer_notification
-- ----------------------------

-- ----------------------------
-- Table structure for `customer_rates`
-- ----------------------------
DROP TABLE IF EXISTS `customer_rates`;
CREATE TABLE `customer_rates` (
  `rate_id` int(11) NOT NULL AUTO_INCREMENT,
  `ratecard_id` varchar(30) NOT NULL,
  `prefix` varchar(25) NOT NULL,
  `destination` varchar(150) NOT NULL,
  `setup_charge` double(12,6) NOT NULL DEFAULT '0.000000',
  `rental` double(12,6) NOT NULL DEFAULT '0.000000',
  `rate` double(12,6) NOT NULL DEFAULT '0.000000',
  `connection_charge` double DEFAULT '0',
  `minimal_time` int(11) NOT NULL DEFAULT '1',
  `resolution_time` int(11) DEFAULT '1',
  `grace_period` int(11) DEFAULT '0',
  `rate_multiplier` decimal(5,2) DEFAULT '1.00',
  `rate_addition` decimal(5,2) DEFAULT '0.00',
  `rates_status` enum('0','1') NOT NULL DEFAULT '1',
  `exclusive_per_channel_rental` double(12,6) DEFAULT '0.000000',
  `inclusive_channel` int(11) DEFAULT '1',
  `account_id` varchar(30) DEFAULT NULL,
  `minimal_charge` double(12,6) DEFAULT NULL,
  `ani_prefix` varchar(30) DEFAULT NULL,
  `updated_by` varchar(30) DEFAULT NULL,
  `created_by` varchar(30) DEFAULT NULL,
  `create_dt` datetime DEFAULT NULL,
  `update_dt` timestamp NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`rate_id`),
  UNIQUE KEY `pt` (`ratecard_id`,`prefix`) USING BTREE,
  KEY `prefix` (`prefix`) USING BTREE,
  KEY `tariff_id` (`ratecard_id`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=latin1;

-- ----------------------------
-- Records of customer_rates
-- ----------------------------
INSERT INTO `customer_rates` VALUES ('3', 'DIDRATES16', '1', 'USA DID', '1.000000', '1.000000', '0.006000', '0', '1', '1', '0', '1.00', '0.00', '1', '0.000000', '1', null, null, null, null, null, '2021-07-31 14:15:55', '2021-07-31 19:20:59');

-- ----------------------------
-- Table structure for `customer_sip_account`
-- ----------------------------
DROP TABLE IF EXISTS `customer_sip_account`;
CREATE TABLE `customer_sip_account` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(30) DEFAULT NULL,
  `secret` varchar(30) DEFAULT NULL,
  `ipaddress` varchar(30) DEFAULT NULL,
  `status` enum('1','0') DEFAULT '1',
  `account_id` varchar(30) DEFAULT NULL,
  `sip_cc` int(11) DEFAULT '1',
  `sip_cps` int(11) DEFAULT '1',
  `ipauthfrom` enum('FROM','SRC','NO') DEFAULT 'NO',
  `extension_no` int(11) DEFAULT NULL,
  `voicemail_enabled` enum('Y','N') DEFAULT 'N',
  `voicemail` varchar(30) DEFAULT NULL,
  `display_name` varchar(30) DEFAULT NULL,
  `caller_id` varchar(150) DEFAULT NULL,
  `cli_prefer` enum('rpid','pid','no') DEFAULT 'rpid',
  `codecs` varchar(50) DEFAULT 'G729,PCMU,PCMA',
  `moh_sound` varchar(255) NOT NULL DEFAULT 'default',
  `name` varchar(100) NOT NULL,
  `email_address` varchar(150) NOT NULL,
  `phone_number` varchar(20) NOT NULL,
  `ring_timeout` int(11) DEFAULT '30',
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
  `extension_id` varchar(30) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`) USING BTREE,
  UNIQUE KEY `account_exten` (`account_id`,`extension_no`) USING BTREE,
  KEY `account_id` (`account_id`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4;

-- ----------------------------
-- Records of customer_sip_account
-- ----------------------------
INSERT INTO `customer_sip_account` VALUES ('2', 'AnandKumar', 'Kanand@1!', '', '1', 'STC300000', '10', '1', 'NO', '100', 'Y', 'EXT000002710', null, null, 'rpid', 'G729,PCMU,PCMA', 'default', '', '', '', '30', 'N', 'HANGUP', null, 'N', 'HANGUP', null, 'N', 'HANGUP', null, null, '0', 'N', '', '', '', '0000-00-00 00:00:00', null, 'SWITCH', 'EXT000002710');

-- ----------------------------
-- Table structure for `customer_voipminuts`
-- ----------------------------
DROP TABLE IF EXISTS `customer_voipminuts`;
CREATE TABLE `customer_voipminuts` (
  `id` bigint(11) NOT NULL AUTO_INCREMENT,
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
  KEY `account_id` (`account_id`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=latin1;

-- ----------------------------
-- Records of customer_voipminuts
-- ----------------------------
INSERT INTO `customer_voipminuts` VALUES ('2', 'CVM000002475', 'STR100000', 'AAA', 'RESELLER', 'DDDDDD48', '1', 'ADMIN', '', '2021-07-31 09:46:29', null);
INSERT INTO `customer_voipminuts` VALUES ('3', 'CVM000003530', 'STC300000', '', 'CUSTOMER', 'CUSTOMER54', '1', 'ADMIN', '', '2021-07-31 12:33:18', null);

-- ----------------------------
-- Table structure for `customers`
-- ----------------------------
DROP TABLE IF EXISTS `customers`;
CREATE TABLE `customers` (
  `customer_id` int(11) NOT NULL AUTO_INCREMENT,
  `account_id` varchar(50) DEFAULT NULL,
  `company_name` varchar(50) NOT NULL,
  `contact_name` varchar(150) DEFAULT NULL,
  `name` varchar(30) DEFAULT NULL,
  `address` text,
  `country_id` int(11) DEFAULT NULL,
  `state_code_id` mediumint(9) DEFAULT NULL,
  `phone` varchar(30) DEFAULT NULL,
  `emailaddress` varchar(1000) DEFAULT NULL,
  `billing_type` enum('prepaid','postpaid','netoff') NOT NULL DEFAULT 'prepaid',
  `billing_cycle` enum('weekly','monthly') NOT NULL DEFAULT 'monthly',
  `payment_terms` int(11) NOT NULL DEFAULT '30',
  `next_billing_date` date DEFAULT NULL,
  `pincode` varchar(15) DEFAULT NULL,
  `view_ipdevices` enum('1','0') DEFAULT '1',
  `view_sipdevice` enum('1','0') DEFAULT '1',
  `view_src_out` enum('1','0') DEFAULT '1',
  `view_dst_out` enum('1','0') DEFAULT '1',
  `view_src_did` enum('1','0') DEFAULT '1',
  `view_dst_did` enum('1','0') DEFAULT '1',
  `created_by` varchar(30) DEFAULT NULL,
  `updated_by` varchar(30) DEFAULT NULL,
  `created_dt` datetime DEFAULT NULL,
  `updated_dt` timestamp NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`customer_id`),
  UNIQUE KEY `accountid` (`account_id`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=latin1;

-- ----------------------------
-- Records of customers
-- ----------------------------
INSERT INTO `customers` VALUES ('3', 'STC300000', 'Anand kumar', 'Anand kumar', null, '', '224', '0', '', 'kanand81@gmail.com', 'prepaid', 'monthly', '30', null, '', '1', '1', '1', '1', '1', '1', null, null, null, null);

-- ----------------------------
-- Table structure for `delete_history`
-- ----------------------------
DROP TABLE IF EXISTS `delete_history`;
CREATE TABLE `delete_history` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `delete_type` varchar(30) NOT NULL,
  `delete_status` varchar(30) NOT NULL,
  `delete_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `delete_code` varchar(30) NOT NULL,
  `deleted_by` varchar(30) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- ----------------------------
-- Records of delete_history
-- ----------------------------

-- ----------------------------
-- Table structure for `dialplan`
-- ----------------------------
DROP TABLE IF EXISTS `dialplan`;
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
  `update_dt` timestamp NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `dialplan_id_name` (`dialplan_id`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=latin1;

-- ----------------------------
-- Records of dialplan
-- ----------------------------
INSERT INTO `dialplan` VALUES ('1', null, 'DEFAULT28', 'default', '1', 'bbb', 'aaa', null, null, '2021-07-31 13:08:12', '2021-07-31 13:08:12');

-- ----------------------------
-- Table structure for `dialplan_prefix_list`
-- ----------------------------
DROP TABLE IF EXISTS `dialplan_prefix_list`;
CREATE TABLE `dialplan_prefix_list` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `account_id` varchar(30) DEFAULT NULL,
  `dialplan_id` varchar(30) CHARACTER SET latin1 DEFAULT NULL,
  `dial_prefix` varchar(30) CHARACTER SET latin1 NOT NULL,
  `priority` smallint(6) NOT NULL DEFAULT '1',
  `route_status` enum('0','1') CHARACTER SET latin1 NOT NULL DEFAULT '1',
  `carrier_id` varchar(30) CHARACTER SET latin1 NOT NULL,
  `start_day` smallint(6) DEFAULT '0',
  `start_time` varchar(8) CHARACTER SET latin1 DEFAULT '00:00:00',
  `end_day` smallint(6) DEFAULT '6',
  `end_time` varchar(8) CHARACTER SET latin1 DEFAULT '24:00:00',
  `load_share` int(11) DEFAULT '100',
  `created_by` varchar(30) DEFAULT NULL,
  `updated_by` varchar(30) DEFAULT NULL,
  `create_dt` datetime DEFAULT NULL,
  `update_dt` timestamp NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `dialplan_list_name` (`dial_prefix`,`carrier_id`,`dialplan_id`) USING BTREE,
  KEY `dialplan_id_name` (`dialplan_id`) USING BTREE,
  KEY `dial_prefix` (`dial_prefix`) USING BTREE,
  KEY `route_status` (`route_status`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=latin7;

-- ----------------------------
-- Records of dialplan_prefix_list
-- ----------------------------
INSERT INTO `dialplan_prefix_list` VALUES ('1', null, 'DEFAULT28', '1', '1', '1', 'DEFAULT38', '0', '00:00:00', '6', '23:59:59', '100', null, null, '2021-07-31 12:21:34', '2021-07-31 15:51:34');

-- ----------------------------
-- Table structure for `did`
-- ----------------------------
DROP TABLE IF EXISTS `did`;
CREATE TABLE `did` (
  `did_id` int(11) NOT NULL AUTO_INCREMENT,
  `did_number` varchar(30) DEFAULT NULL,
  `did_status` enum('NEW','USED','DEAD','BLOCKED') DEFAULT 'NEW',
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
  `channels` int(11) DEFAULT '1',
  `did_name` varchar(150) DEFAULT NULL,
  `number_type` enum('TFN','DID') DEFAULT 'DID',
  `lastbilldate` date DEFAULT NULL,
  `r1lastbilldate` date DEFAULT NULL,
  `r2lastbilldate` date DEFAULT NULL,
  `r3lastbilldate` date DEFAULT NULL,
  PRIMARY KEY (`did_id`),
  UNIQUE KEY `did_number` (`did_number`) USING BTREE,
  UNIQUE KEY `did_number_2` (`did_number`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- ----------------------------
-- Records of did
-- ----------------------------

-- ----------------------------
-- Table structure for `did_dst`
-- ----------------------------
DROP TABLE IF EXISTS `did_dst`;
CREATE TABLE `did_dst` (
  `did_dst_id` int(11) NOT NULL AUTO_INCREMENT,
  `did_number` varchar(30) DEFAULT NULL,
  `account_id` varchar(30) DEFAULT NULL,
  `dst_type` enum('IP','CUSTOMER','PSTN') DEFAULT 'IP',
  `dst_destination` varchar(30) DEFAULT NULL,
  `create_date` datetime DEFAULT NULL,
  `update_date` timestamp NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  `dst_type2` enum('IP','CUSTOMER','PSTN') DEFAULT 'IP',
  `dst_destination2` varchar(30) DEFAULT NULL,
  PRIMARY KEY (`did_dst_id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=latin1;

-- ----------------------------
-- Records of did_dst
-- ----------------------------
INSERT INTO `did_dst` VALUES ('1', '19496743649', 'STC300000', 'CUSTOMER', 'AnandKumar', '2021-07-31 00:00:00', null, 'CUSTOMER', '');

-- ----------------------------
-- Table structure for `emaillog`
-- ----------------------------
DROP TABLE IF EXISTS `emaillog`;
CREATE TABLE `emaillog` (
  `email_log_id` int(11) NOT NULL AUTO_INCREMENT,
  `account_id` varchar(30) DEFAULT NULL,
  `action_date` datetime DEFAULT NULL,
  `subject` varchar(300) DEFAULT NULL,
  `body` text,
  `attachement` blob,
  `actionfrom` varchar(500) DEFAULT NULL,
  `email_to` varchar(500) DEFAULT NULL,
  PRIMARY KEY (`email_log_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- ----------------------------
-- Records of emaillog
-- ----------------------------

-- ----------------------------
-- Table structure for `htable`
-- ----------------------------
DROP TABLE IF EXISTS `htable`;
CREATE TABLE `htable` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `key_name` varchar(64) NOT NULL DEFAULT '',
  `key_type` int(11) NOT NULL DEFAULT '0',
  `value_type` int(11) NOT NULL DEFAULT '0',
  `key_value` varchar(128) NOT NULL DEFAULT '',
  `expires` int(11) NOT NULL DEFAULT '0',
  `ip` varchar(30) DEFAULT NULL,
  `htime` datetime DEFAULT NULL,
  `custom_field` varchar(128) NOT NULL DEFAULT '',
  `email_status` int(11) DEFAULT '0',
  `serverid` int(11) DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `ov_htable_keyname_ind` (`key_name`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- ----------------------------
-- Records of htable
-- ----------------------------

-- ----------------------------
-- Table structure for `htabledump`
-- ----------------------------
DROP TABLE IF EXISTS `htabledump`;
CREATE TABLE `htabledump` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `key_name` varchar(64) NOT NULL DEFAULT '',
  `key_type` int(11) NOT NULL DEFAULT '0',
  `value_type` int(11) NOT NULL DEFAULT '0',
  `key_value` varchar(128) NOT NULL DEFAULT '',
  `expires` int(11) NOT NULL DEFAULT '0',
  `ip` varchar(30) DEFAULT NULL,
  `htime` datetime DEFAULT NULL,
  `custom_field` varchar(128) NOT NULL DEFAULT '',
  `email_status` int(11) DEFAULT '0',
  `serverid` int(11) DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `htable_keyname_ind` (`key_name`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- ----------------------------
-- Records of htabledump
-- ----------------------------

-- ----------------------------
-- Table structure for `ip_blocker`
-- ----------------------------
DROP TABLE IF EXISTS `ip_blocker`;
CREATE TABLE `ip_blocker` (
  `ip_id` int(11) NOT NULL AUTO_INCREMENT,
  `checking_type` enum('allow','disallow','inactive') NOT NULL,
  PRIMARY KEY (`ip_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of ip_blocker
-- ----------------------------

-- ----------------------------
-- Table structure for `ip_blocker_details`
-- ----------------------------
DROP TABLE IF EXISTS `ip_blocker_details`;
CREATE TABLE `ip_blocker_details` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `ip` varchar(50) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of ip_blocker_details
-- ----------------------------

-- ----------------------------
-- Table structure for `livecalls`
-- ----------------------------
DROP TABLE IF EXISTS `livecalls`;
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
  `carrier_currency_id` int(11) DEFAULT NULL,
  `carrier_src_caller` varchar(30) DEFAULT NULL,
  `carrier_src_callee` varchar(30) DEFAULT NULL,
  `carrier_dst_caller` varchar(30) DEFAULT NULL,
  `carrier_dst_callee` varchar(30) DEFAULT NULL,
  `dialplan_id` varchar(30) DEFAULT NULL,
  `customer_account_id` varchar(30) DEFAULT NULL,
  `customer_tariff_id` varchar(30) DEFAULT NULL,
  `customer_currency_id` int(11) DEFAULT NULL,
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
  `end_time` timestamp NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  `fscause` varchar(50) DEFAULT NULL,
  `Q850CODE` varchar(30) DEFAULT NULL,
  `SIPCODE` varchar(30) DEFAULT NULL,
  `caller_callid` varchar(150) DEFAULT NULL,
  `callee_callid` varchar(150) DEFAULT NULL,
  `common_uuid` varchar(150) DEFAULT NULL,
  `fs_host` varchar(30) DEFAULT NULL,
  `in_useragent` varchar(150) DEFAULT NULL,
  `callstatus` varchar(20) DEFAULT NULL,
  `notes` text,
  `customer_company` varchar(150) DEFAULT NULL,
  `loadbalancer` varchar(30) DEFAULT NULL,
  `call_flow` enum('PSTN','DID') DEFAULT 'PSTN',
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
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- ----------------------------
-- Records of livecalls
-- ----------------------------

-- ----------------------------
-- Table structure for `menus`
-- ----------------------------
DROP TABLE IF EXISTS `menus`;
CREATE TABLE `menus` (
  `menu_id` int(11) NOT NULL AUTO_INCREMENT,
  `account_id` varchar(50) NOT NULL,
  `menu` text NOT NULL,
  `update_by` varchar(50) NOT NULL,
  `update_dt` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`menu_id`),
  UNIQUE KEY `account_id` (`account_id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ----------------------------
-- Records of menus
-- ----------------------------

-- ----------------------------
-- Table structure for `payment_history`
-- ----------------------------
DROP TABLE IF EXISTS `payment_history`;
CREATE TABLE `payment_history` (
  `payment_id` int(11) NOT NULL AUTO_INCREMENT,
  `account_id` varchar(30) NOT NULL,
  `payment_option_id` varchar(30) NOT NULL,
  `payment_collection_id` varchar(30) DEFAULT NULL,
  `amount` decimal(12,6) NOT NULL,
  `paid_on` datetime NOT NULL,
  `notes` text,
  `transaction_id` varchar(50) NOT NULL,
  `file_name` varchar(20) NOT NULL,
  `other_data` text NOT NULL,
  `invoice_data` text NOT NULL,
  `created_by` varchar(30) NOT NULL,
  `create_dt` datetime NOT NULL,
  PRIMARY KEY (`payment_id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of payment_history
-- ----------------------------
INSERT INTO `payment_history` VALUES ('1', 'STC100000', 'ADDBALANCE', 'Cash', '10.000000', '2021-07-31 09:50:01', '', '', '', '', '', 'ADMIN', '2021-07-31 09:19:50');
INSERT INTO `payment_history` VALUES ('2', 'STC100000', 'ADDCREDIT', 'Temporary Credits', '5.000000', '2021-07-31 09:50:13', '', '', '', '', '', 'ADMIN', '2021-07-31 09:49:50');
INSERT INTO `payment_history` VALUES ('3', 'STC100000', 'REMOVEBALANCE', 'Cash Refund', '5.000000', '2021-07-31 09:50:51', '', '', '', '', '', 'ADMIN', '2021-07-31 09:09:51');
INSERT INTO `payment_history` VALUES ('4', 'STR100000', 'ADDBALANCE', 'Cash', '22.000000', '2021-07-31 09:51:48', '', '', '', '', '', 'ADMIN', '2021-07-31 09:08:52');
INSERT INTO `payment_history` VALUES ('5', 'STC300000', 'ADDBALANCE', 'Bank Transfer Payment', '100.000000', '2021-07-31 12:41:04', '', '', '', '', '', 'ADMIN', '2021-07-31 12:15:41');

-- ----------------------------
-- Table structure for `payment_tracking`
-- ----------------------------
DROP TABLE IF EXISTS `payment_tracking`;
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
  `order_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `attempt_check` enum('Y','N') NOT NULL DEFAULT 'Y',
  PRIMARY KEY (`payment_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- ----------------------------
-- Records of payment_tracking
-- ----------------------------

-- ----------------------------
-- Table structure for `plugins`
-- ----------------------------
DROP TABLE IF EXISTS `plugins`;
CREATE TABLE `plugins` (
  `plugin_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `plugin_system_name` varchar(255) NOT NULL,
  `plugin_name` varchar(255) NOT NULL,
  `plugin_uri` varchar(120) DEFAULT NULL,
  `plugin_version` varchar(30) NOT NULL,
  `plugin_description` text,
  `plugin_author` varchar(120) DEFAULT NULL,
  `plugin_author_uri` varchar(120) DEFAULT NULL,
  `plugin_data` longtext,
  PRIMARY KEY (`plugin_id`),
  UNIQUE KEY `plugin_index` (`plugin_system_name`) USING BTREE
) ENGINE=MyISAM AUTO_INCREMENT=134 DEFAULT CHARSET=latin1;

-- ----------------------------
-- Records of plugins
-- ----------------------------
INSERT INTO `plugins` VALUES ('124', 'ipblocker', 'IP Blocker', 'http://openvoips.org/', '1.0', 'Allow Or Block IP....<br>1. Set \'$config[\'enable_hooks\'] to TRUE in application\\config.php<br>2.	Put this code in application\\hooks.php <br> $hook[\'post_controller_constructor\'] = array(<br>	\'class\'    => \'Ipblockerhook\',<br>	\'function\' => \'post_controller_constructor\',<br>	\'filename\' => \'ipblocker-hook.php\',<br>	\'filepath\' => \'modules/ipblocker\',<br>	\'params\'   => \"\"<br>);', 'Anand Kumar & Sanjay', 'http://ov500.openvoips.org/', null);
INSERT INTO `plugins` VALUES ('122', 'activitylog', 'Activity Log', 'http://openvoips.org/', '1.0', 'Check Site Activity Log, Block IP....<br>1. Set \'$config[\'enable_hooks\'] to TRUE in application\\config.php<br>2.	Put this code in application\\hooks.php <br> $hook[\'post_controller_constructor\'] = array(<br>	\'class\'    => \'Activityloghook\',<br>	\'function\' => \'post_controller_constructor\',<br>	\'filename\' => \'activitylog-hook.php\',<br>	\'filepath\' => \'modules/activitylog\',<br>	\'params\'   => \"\"<br>);', 'Anand Kumar & Sanjay', 'http://ov500.openvoips.org/', null);
INSERT INTO `plugins` VALUES ('128', 'billing', 'Billing & Invoice Management', 'http://openvoips.org/', '1.0', 'Billing & Invoice Related 1....', 'Anand Kumar & Sanjay', 'http://ov500.openvoips.org/', null);
INSERT INTO `plugins` VALUES ('133', 'paypal', 'PayPal Payment Gateway', 'http://openvoips.org/', '1.0', 'PayPal Payment Gateway', 'Anand Kumar & Sanjay', 'http://ov500.openvoips.org/', null);
INSERT INTO `plugins` VALUES ('130', 'crs', 'CRS Module', 'http://openvoips.org/', '1.0', 'CRS Module', 'Anand Kumar & Sanjay', 'http://ov500.openvoips.org/', null);

-- ----------------------------
-- Table structure for `providers`
-- ----------------------------
DROP TABLE IF EXISTS `providers`;
CREATE TABLE `providers` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `provider_id` varchar(30) DEFAULT NULL,
  `provider_name` varchar(30) DEFAULT NULL,
  `provider_address` varchar(200) DEFAULT NULL,
  `provider_emailid` varchar(100) NOT NULL,
  `currency_id` int(4) DEFAULT NULL,
  `account_id` varchar(30) DEFAULT NULL,
  `created_by` varchar(30) DEFAULT NULL,
  `updated_by` varchar(30) DEFAULT NULL,
  `updated_dt` timestamp NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  `created_dt` datetime DEFAULT NULL,
  `modify_by` varchar(30) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=latin1;

-- ----------------------------
-- Records of providers
-- ----------------------------
INSERT INTO `providers` VALUES ('1', 'DEFAULT414', 'Default', '', '', '4', 'SYSTEM', 'ADMIN', null, null, '2021-07-31 12:20:30', null);

-- ----------------------------
-- Table structure for `ratecard`
-- ----------------------------
DROP TABLE IF EXISTS `ratecard`;
CREATE TABLE `ratecard` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `ratecard_id` varchar(30) DEFAULT NULL,
  `ratecard_name` varchar(30) DEFAULT NULL,
  `ratecard_type` enum('CARRIER','CUSTOMER') DEFAULT 'CARRIER',
  `account_id` varchar(30) NOT NULL,
  `ratecard_currency_id` int(11) DEFAULT NULL,
  `ratecard_for` enum('INCOMING','OUTGOING') DEFAULT 'OUTGOING',
  `created_by` varchar(30) DEFAULT NULL,
  `updated_by` varchar(30) DEFAULT NULL,
  `created_dt` datetime DEFAULT NULL,
  `updated_dt` timestamp NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `ratecard_id` (`ratecard_id`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=latin1;

-- ----------------------------
-- Records of ratecard
-- ----------------------------
INSERT INTO `ratecard` VALUES ('1', 'CARRIER47', 'Carrier', 'CARRIER', 'SYSTEM', '4', 'OUTGOING', 'ADMIN', null, null, null);
INSERT INTO `ratecard` VALUES ('2', 'CUSTOMER42', 'customer', 'CUSTOMER', 'SYSTEM', '4', 'OUTGOING', 'ADMIN', null, null, null);
INSERT INTO `ratecard` VALUES ('3', 'DIDRATES16', 'DID Rates', 'CUSTOMER', 'SYSTEM', '4', 'INCOMING', 'ADMIN', null, null, null);
INSERT INTO `ratecard` VALUES ('4', 'DIDRATES41', 'DID Rates Carrier', 'CARRIER', 'SYSTEM', '4', 'INCOMING', 'ADMIN', null, null, null);

-- ----------------------------
-- Table structure for `reseller_dialplan`
-- ----------------------------
DROP TABLE IF EXISTS `reseller_dialplan`;
CREATE TABLE `reseller_dialplan` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `account_id` varchar(30) DEFAULT NULL,
  `dialplan_id` varchar(30) DEFAULT NULL,
  `create_dt` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `reseller_dialplan_key` (`account_id`,`dialplan_id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- ----------------------------
-- Records of reseller_dialplan
-- ----------------------------

-- ----------------------------
-- Table structure for `resellers`
-- ----------------------------
DROP TABLE IF EXISTS `resellers`;
CREATE TABLE `resellers` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `account_id` varchar(30) NOT NULL,
  `company_name` varchar(50) NOT NULL,
  `contact_name` varchar(50) DEFAULT NULL,
  `address` text,
  `country_id` int(11) DEFAULT NULL,
  `state_code_id` mediumint(9) DEFAULT NULL,
  `phone` varchar(30) DEFAULT NULL,
  `emailaddress` varchar(1000) DEFAULT NULL,
  `pincode` varchar(15) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `account_id` (`account_id`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4;

-- ----------------------------
-- Records of resellers
-- ----------------------------
INSERT INTO `resellers` VALUES ('1', 'STR100000', 'test reseller', 'test reseller', '', '100', '37', '', 'tescustggr@mail.com', '6666');

-- ----------------------------
-- Table structure for `signup_plan`
-- ----------------------------
DROP TABLE IF EXISTS `signup_plan`;
CREATE TABLE `signup_plan` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `signplan_id` varchar(30) NOT NULL,
  `signplan_name` varchar(30) NOT NULL,
  `tariff_id` varchar(30) NOT NULL,
  `billing_type` varchar(30) DEFAULT 'PREPAID',
  `max_callduration` int(11) DEFAULT '120',
  `account_type` enum('CUSTOMER','RESELLER') DEFAULT 'CUSTOMER',
  `currency_id` int(11) NOT NULL DEFAULT '1',
  `dp` tinyint(1) DEFAULT '4',
  `account_cc` int(11) DEFAULT '10',
  `account_cps` int(11) DEFAULT '1',
  `tax1` double(6,2) DEFAULT '0.00',
  `tax2` double(6,2) DEFAULT '0.00',
  `tax3` double(6,2) DEFAULT '0.00',
  `tax_type` enum('inclusive','exclusive') DEFAULT 'exclusive',
  `account_codecs` varchar(150) DEFAULT 'G729,PCMU,PCMA,G722',
  `media_transcoding` enum('1','0') DEFAULT '1',
  `media_rtpproxy` enum('1','0') DEFAULT '1',
  `dialplan_id` varchar(100) NOT NULL,
  `account_level` int(11) DEFAULT '1',
  `create_dt` datetime DEFAULT NULL,
  `update_dt` timestamp NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  `created_by_user_id` varchar(30) DEFAULT NULL,
  `created_by_account_id` varchar(30) DEFAULT 'SYSTEM',
  PRIMARY KEY (`id`),
  UNIQUE KEY `signplan_id` (`signplan_id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- ----------------------------
-- Records of signup_plan
-- ----------------------------

-- ----------------------------
-- Table structure for `sys_countries`
-- ----------------------------
DROP TABLE IF EXISTS `sys_countries`;
CREATE TABLE `sys_countries` (
  `country_id` int(11) NOT NULL AUTO_INCREMENT,
  `country_abbr` char(3) NOT NULL,
  `country_iso` varchar(2) DEFAULT NULL,
  `country_prefix` int(10) NOT NULL,
  `country_name` varchar(100) NOT NULL,
  `status_id` int(10) unsigned NOT NULL DEFAULT '2',
  `display_sequence` smallint(6) NOT NULL DEFAULT '0',
  PRIMARY KEY (`country_id`)
) ENGINE=InnoDB AUTO_INCREMENT=249 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of sys_countries
-- ----------------------------
INSERT INTO `sys_countries` VALUES ('1', 'AFG', 'AF', '93', 'Afghanistan', '1', '0');
INSERT INTO `sys_countries` VALUES ('2', 'ALB', 'AL', '355', 'Albania', '1', '0');
INSERT INTO `sys_countries` VALUES ('3', 'DZA', 'DZ', '213', 'Algeria', '1', '0');
INSERT INTO `sys_countries` VALUES ('4', 'ASM', 'AS', '1684', 'American Samoa', '1', '0');
INSERT INTO `sys_countries` VALUES ('5', 'AND', 'AD', '376', 'Andorra', '1', '0');
INSERT INTO `sys_countries` VALUES ('6', 'AGO', 'AO', '244', 'Angola', '1', '0');
INSERT INTO `sys_countries` VALUES ('7', 'AIA', 'AI', '1264', 'Anguilla', '1', '0');
INSERT INTO `sys_countries` VALUES ('8', 'ATA', 'AQ', '672', 'Antarctica', '1', '0');
INSERT INTO `sys_countries` VALUES ('9', 'ATG', 'AG', '1268', 'Antigua & Barbuda', '1', '0');
INSERT INTO `sys_countries` VALUES ('10', 'ARG', 'AR', '54', 'Argentina', '1', '0');
INSERT INTO `sys_countries` VALUES ('11', 'ARM', 'AM', '374', 'Armenia', '1', '0');
INSERT INTO `sys_countries` VALUES ('12', 'ABW', 'AW', '297', 'Aruba', '1', '0');
INSERT INTO `sys_countries` VALUES ('13', 'AUS', 'AU', '61', 'Australia', '1', '0');
INSERT INTO `sys_countries` VALUES ('14', 'AUT', 'AT', '43', 'Austria', '1', '0');
INSERT INTO `sys_countries` VALUES ('15', 'AZE', 'AZ', '994', 'Azerbaijan', '1', '0');
INSERT INTO `sys_countries` VALUES ('16', 'BHS', 'BS', '1242', 'Bahamas', '1', '0');
INSERT INTO `sys_countries` VALUES ('17', 'BHR', 'BH', '973', 'Bahrain', '1', '0');
INSERT INTO `sys_countries` VALUES ('18', 'BGD', 'BD', '880', 'Bangladesh', '1', '0');
INSERT INTO `sys_countries` VALUES ('19', 'BRB', 'BB', '1246', 'Barbados', '1', '0');
INSERT INTO `sys_countries` VALUES ('20', 'BLR', 'BY', '375', 'Belarus', '1', '0');
INSERT INTO `sys_countries` VALUES ('21', 'BEL', 'BE', '32', 'Belgium', '1', '0');
INSERT INTO `sys_countries` VALUES ('22', 'BLZ', 'BZ', '501', 'Belize', '1', '0');
INSERT INTO `sys_countries` VALUES ('23', 'BEN', 'BJ', '229', 'Benin', '1', '0');
INSERT INTO `sys_countries` VALUES ('24', 'BMU', 'BM', '1441', 'Bermuda', '1', '0');
INSERT INTO `sys_countries` VALUES ('25', 'BTN', 'BT', '975', 'Bhutan', '1', '0');
INSERT INTO `sys_countries` VALUES ('26', 'BOL', 'BO', '591', 'Bolivia', '1', '0');
INSERT INTO `sys_countries` VALUES ('27', 'BIH', 'BA', '387', 'Bosnia & Herzegovina', '1', '0');
INSERT INTO `sys_countries` VALUES ('28', 'BWA', 'BW', '267', 'Botswana', '1', '0');
INSERT INTO `sys_countries` VALUES ('29', 'BVT', 'BV', '55', 'Bouvet island', '2', '0');
INSERT INTO `sys_countries` VALUES ('30', 'BRA', 'BR', '55', 'Brazil', '1', '0');
INSERT INTO `sys_countries` VALUES ('31', 'IOT', 'IO', '246', 'British Indian Ocean Territory', '2', '0');
INSERT INTO `sys_countries` VALUES ('32', 'BRN', 'BN', '673', 'Brunei', '1', '0');
INSERT INTO `sys_countries` VALUES ('33', 'BGR', 'BG', '359', 'Bulgaria', '1', '0');
INSERT INTO `sys_countries` VALUES ('34', 'BFA', 'BF', '226', 'Burkina Faso', '1', '0');
INSERT INTO `sys_countries` VALUES ('35', 'BDI', 'BI', '257', 'Burundi', '1', '0');
INSERT INTO `sys_countries` VALUES ('36', 'KHM', 'KH', '855', 'Cambodia', '1', '0');
INSERT INTO `sys_countries` VALUES ('37', 'CMR', 'CM', '237', 'Cameroon', '1', '0');
INSERT INTO `sys_countries` VALUES ('38', 'CAN', 'CA', '1', 'Canada', '1', '0');
INSERT INTO `sys_countries` VALUES ('39', 'CPV', 'CV', '238', 'Cape Verde', '1', '0');
INSERT INTO `sys_countries` VALUES ('40', 'CYM', 'KY', '1345', 'Cayman Islands', '1', '0');
INSERT INTO `sys_countries` VALUES ('41', 'CAF', 'CF', '236', 'Central African Republic', '1', '0');
INSERT INTO `sys_countries` VALUES ('42', 'TCD', 'TD', '235', 'Chad', '1', '0');
INSERT INTO `sys_countries` VALUES ('43', 'CHL', 'CL', '56', 'Chile', '1', '0');
INSERT INTO `sys_countries` VALUES ('44', 'CHN', 'CN', '86', 'China', '1', '0');
INSERT INTO `sys_countries` VALUES ('45', 'CXR', 'CX', '618916', 'Christmas Island', '2', '0');
INSERT INTO `sys_countries` VALUES ('46', 'CCK', 'CC', '61891', 'Cocos Islands', '2', '0');
INSERT INTO `sys_countries` VALUES ('47', 'COL', 'CO', '57', 'Colombia', '1', '0');
INSERT INTO `sys_countries` VALUES ('48', 'COM', 'KM', '269', 'Comoros', '1', '0');
INSERT INTO `sys_countries` VALUES ('49', 'COD', 'CD', '243', 'Democratic Republic of Congo', '1', '0');
INSERT INTO `sys_countries` VALUES ('50', 'COG', 'CG', '242', 'Republic of the Congo', '1', '0');
INSERT INTO `sys_countries` VALUES ('51', 'COK', 'CK', '682', 'Cook Islands', '1', '0');
INSERT INTO `sys_countries` VALUES ('52', 'CRI', 'CR', '506', 'Costa Rica', '1', '0');
INSERT INTO `sys_countries` VALUES ('53', 'CIV', 'CI', '225', 'Cote D\'ivoire', '1', '0');
INSERT INTO `sys_countries` VALUES ('54', 'HRV', 'HR', '385', 'Croatia', '1', '0');
INSERT INTO `sys_countries` VALUES ('55', 'CUB', 'CU', '53', 'Cuba', '1', '0');
INSERT INTO `sys_countries` VALUES ('56', 'CYP', 'CY', '357', 'Cyprus', '1', '0');
INSERT INTO `sys_countries` VALUES ('57', 'CZE', 'CZ', '420', 'Czech Republic', '1', '0');
INSERT INTO `sys_countries` VALUES ('58', 'DNK', 'DK', '45', 'Denmark', '1', '0');
INSERT INTO `sys_countries` VALUES ('59', 'DJI', 'DJ', '253', 'Djibouti', '1', '0');
INSERT INTO `sys_countries` VALUES ('60', 'DMA', 'DM', '1767', 'Dominica', '1', '0');
INSERT INTO `sys_countries` VALUES ('61', 'DOM', 'DO', '1809', 'Dominican Republic', '1', '0');
INSERT INTO `sys_countries` VALUES ('62', 'TLS', 'TP', '670', 'East Timor', '2', '0');
INSERT INTO `sys_countries` VALUES ('63', 'ECU', 'EC', '593', 'Ecuador', '1', '0');
INSERT INTO `sys_countries` VALUES ('64', 'EGY', 'EG', '20', 'Egypt', '1', '0');
INSERT INTO `sys_countries` VALUES ('65', 'SLV', 'SV', '503', 'El salvador', '1', '0');
INSERT INTO `sys_countries` VALUES ('66', 'GNQ', 'GQ', '240', 'Equatorial Guinea', '1', '0');
INSERT INTO `sys_countries` VALUES ('67', 'ERI', 'ER', '291', 'Eritrea', '1', '0');
INSERT INTO `sys_countries` VALUES ('68', 'EST', 'EE', '372', 'Estonia', '1', '0');
INSERT INTO `sys_countries` VALUES ('69', 'ETH', 'ET', '251', 'Ethiopia', '1', '0');
INSERT INTO `sys_countries` VALUES ('70', 'FLK', 'FK', '500', 'Falkland Islands', '1', '0');
INSERT INTO `sys_countries` VALUES ('71', 'FRO', 'FO', '298', 'Faeroe Islands', '1', '0');
INSERT INTO `sys_countries` VALUES ('72', 'FJI', 'FJ', '679', 'Fiji', '1', '0');
INSERT INTO `sys_countries` VALUES ('73', 'FIN', 'FI', '358', 'Finland', '1', '0');
INSERT INTO `sys_countries` VALUES ('74', 'FRA', 'FR', '33', 'France', '1', '0');
INSERT INTO `sys_countries` VALUES ('75', 'FXX', 'FX', '0', 'France Metropolitan', '2', '0');
INSERT INTO `sys_countries` VALUES ('76', 'GUF', 'GF', '594', 'French Guiana', '1', '0');
INSERT INTO `sys_countries` VALUES ('77', 'PYF', 'PF', '689', 'French Polynesia', '1', '0');
INSERT INTO `sys_countries` VALUES ('78', 'ATF', 'TF', '0', 'French Southern Territories', '2', '0');
INSERT INTO `sys_countries` VALUES ('79', 'GAB', 'GA', '241', 'Gabon', '1', '0');
INSERT INTO `sys_countries` VALUES ('80', 'GMB', 'GM', '220', 'Gambia', '1', '0');
INSERT INTO `sys_countries` VALUES ('81', 'GEO', 'GE', '995', 'Georgia', '1', '0');
INSERT INTO `sys_countries` VALUES ('82', 'DEU', 'DE', '49', 'Germany', '1', '0');
INSERT INTO `sys_countries` VALUES ('83', 'GHA', 'GH', '233', 'Ghana', '1', '0');
INSERT INTO `sys_countries` VALUES ('84', 'GIB', 'GI', '350', 'Gibraltar', '1', '0');
INSERT INTO `sys_countries` VALUES ('85', 'GRC', 'GR', '30', 'Greece', '1', '0');
INSERT INTO `sys_countries` VALUES ('86', 'GRL', 'GL', '299', 'Greenland', '1', '0');
INSERT INTO `sys_countries` VALUES ('87', 'GRD', 'GD', '1473', 'Grenada', '1', '0');
INSERT INTO `sys_countries` VALUES ('88', 'GLP', 'GP', '590', 'Guadeloupe', '1', '0');
INSERT INTO `sys_countries` VALUES ('89', 'GUM', 'GU', '1671', 'Guam', '1', '0');
INSERT INTO `sys_countries` VALUES ('90', 'GTM', 'GT', '502', 'Guatemala', '1', '0');
INSERT INTO `sys_countries` VALUES ('91', 'GIN', 'GN', '224', 'Guinea', '1', '0');
INSERT INTO `sys_countries` VALUES ('92', 'GNB', 'GW', '245', 'Guinea Bissau', '1', '0');
INSERT INTO `sys_countries` VALUES ('93', 'GUY', 'GY', '592', 'Guyana', '1', '0');
INSERT INTO `sys_countries` VALUES ('94', 'HTI', 'HT', '509', 'Haiti', '1', '0');
INSERT INTO `sys_countries` VALUES ('95', 'HMD', 'HM', '0', 'Heard & Mc Donald Islands', '2', '0');
INSERT INTO `sys_countries` VALUES ('96', 'HND', 'HN', '504', 'Honduras', '1', '0');
INSERT INTO `sys_countries` VALUES ('97', 'HKG', 'HK', '852', 'Hong kong', '1', '0');
INSERT INTO `sys_countries` VALUES ('98', 'HUN', 'HU', '36', 'Hungary', '1', '0');
INSERT INTO `sys_countries` VALUES ('99', 'ISL', 'IS', '354', 'Iceland', '1', '0');
INSERT INTO `sys_countries` VALUES ('100', 'IND', 'IN', '91', 'India', '1', '499');
INSERT INTO `sys_countries` VALUES ('101', 'IDN', 'ID', '62', 'Indonesia', '1', '0');
INSERT INTO `sys_countries` VALUES ('102', 'IRN', 'IR', '98', 'Iran', '1', '0');
INSERT INTO `sys_countries` VALUES ('103', 'IRQ', 'IQ', '964', 'Iraq', '1', '0');
INSERT INTO `sys_countries` VALUES ('104', 'IRL', 'IE', '353', 'Ireland', '1', '0');
INSERT INTO `sys_countries` VALUES ('105', 'ISR', 'IL', '972', 'Israel', '1', '0');
INSERT INTO `sys_countries` VALUES ('106', 'ITA', 'IT', '39', 'Italy', '1', '0');
INSERT INTO `sys_countries` VALUES ('107', 'JAM', 'JM', '1876', 'Jamaica', '1', '0');
INSERT INTO `sys_countries` VALUES ('108', 'JPN', 'JP', '81', 'Japan', '1', '0');
INSERT INTO `sys_countries` VALUES ('109', 'JOR', 'JO', '962', 'Jordan', '1', '0');
INSERT INTO `sys_countries` VALUES ('110', 'KAZ', 'KZ', '7', 'Kazakhstan', '1', '0');
INSERT INTO `sys_countries` VALUES ('111', 'KEN', 'KE', '254', 'Kenya', '1', '0');
INSERT INTO `sys_countries` VALUES ('112', 'KIR', 'KI', '686', 'Kiribati', '1', '0');
INSERT INTO `sys_countries` VALUES ('113', 'PRK', 'KP', '850', 'North Korea', '1', '0');
INSERT INTO `sys_countries` VALUES ('114', 'KOR', 'KR', '82', 'South Korea', '1', '0');
INSERT INTO `sys_countries` VALUES ('115', 'KWT', 'KW', '965', 'Kuwait', '1', '0');
INSERT INTO `sys_countries` VALUES ('116', 'KGZ', 'KG', '996', 'Kyrgyzstan', '1', '0');
INSERT INTO `sys_countries` VALUES ('117', 'LAO', 'LA', '856', 'Laos', '1', '0');
INSERT INTO `sys_countries` VALUES ('118', 'LVA', 'LV', '371', 'Latvia', '1', '0');
INSERT INTO `sys_countries` VALUES ('119', 'LBN', 'LB', '961', 'Lebanon', '1', '0');
INSERT INTO `sys_countries` VALUES ('120', 'LSO', 'LS', '266', 'Lesotho', '1', '0');
INSERT INTO `sys_countries` VALUES ('121', 'LBR', 'LR', '231', 'Liberia', '1', '0');
INSERT INTO `sys_countries` VALUES ('122', 'LBY', 'LY', '218', 'Libya', '1', '0');
INSERT INTO `sys_countries` VALUES ('123', 'LIE', 'LI', '423', 'Liechtenstein', '1', '0');
INSERT INTO `sys_countries` VALUES ('124', 'LTU', 'LT', '370', 'Lithuania', '1', '0');
INSERT INTO `sys_countries` VALUES ('125', 'LUX', 'LU', '352', 'Luxembourg', '1', '0');
INSERT INTO `sys_countries` VALUES ('126', 'MAC', 'MO', '853', 'Macau', '1', '0');
INSERT INTO `sys_countries` VALUES ('127', 'MKD', 'MK', '389', 'Macedonia', '1', '0');
INSERT INTO `sys_countries` VALUES ('128', 'MDG', 'MG', '261', 'Madagascar', '1', '0');
INSERT INTO `sys_countries` VALUES ('129', 'MWI', 'MW', '265', 'Malawi', '1', '0');
INSERT INTO `sys_countries` VALUES ('130', 'MYS', 'MY', '60', 'Malaysia', '1', '0');
INSERT INTO `sys_countries` VALUES ('131', 'MDV', 'MV', '960', 'Maldives', '1', '0');
INSERT INTO `sys_countries` VALUES ('132', 'MLI', 'ML', '223', 'Mali', '1', '0');
INSERT INTO `sys_countries` VALUES ('133', 'MLT', 'MT', '356', 'Malta', '1', '0');
INSERT INTO `sys_countries` VALUES ('134', 'MHL', 'MH', '692', 'Marshall Islands', '1', '0');
INSERT INTO `sys_countries` VALUES ('135', 'MTQ', 'MQ', '596', 'Martinique', '1', '0');
INSERT INTO `sys_countries` VALUES ('136', 'MRT', 'MR', '222', 'Mauritania', '1', '0');
INSERT INTO `sys_countries` VALUES ('137', 'MUS', 'MU', '230', 'Mauritius', '1', '0');
INSERT INTO `sys_countries` VALUES ('138', 'MYT', 'YT', '262', 'Mayotte', '1', '0');
INSERT INTO `sys_countries` VALUES ('139', 'MEX', 'MX', '52', 'Mexico', '1', '0');
INSERT INTO `sys_countries` VALUES ('140', 'FSM', 'FM', '691', 'Micronesia', '1', '0');
INSERT INTO `sys_countries` VALUES ('141', 'MDA', 'MD', '373', 'Moldova', '1', '0');
INSERT INTO `sys_countries` VALUES ('142', 'MCO', 'MC', '377', 'Monaco', '1', '0');
INSERT INTO `sys_countries` VALUES ('143', 'MNG', 'MN', '976', 'Mongolia', '1', '0');
INSERT INTO `sys_countries` VALUES ('144', 'MSR', 'MS', '1664', 'Montserrat', '1', '0');
INSERT INTO `sys_countries` VALUES ('145', 'MAR', 'MA', '212', 'Morocco', '1', '0');
INSERT INTO `sys_countries` VALUES ('146', 'MOZ', 'MZ', '258', 'Mozambique', '1', '0');
INSERT INTO `sys_countries` VALUES ('147', 'MMR', 'MM', '95', 'Myanmar', '1', '0');
INSERT INTO `sys_countries` VALUES ('148', 'NAM', 'NA', '264', 'Namibia', '1', '0');
INSERT INTO `sys_countries` VALUES ('149', 'NRU', 'NR', '674', 'Nauru', '1', '0');
INSERT INTO `sys_countries` VALUES ('150', 'NPL', 'NP', '977', 'Nepal', '1', '0');
INSERT INTO `sys_countries` VALUES ('151', 'NLD', 'NL', '31', 'Netherlands', '1', '0');
INSERT INTO `sys_countries` VALUES ('152', 'ANT', 'AN', '599', 'Netherlands Antilles', '1', '0');
INSERT INTO `sys_countries` VALUES ('153', 'NCL', 'NC', '687', 'New Caledonia', '1', '0');
INSERT INTO `sys_countries` VALUES ('154', 'NZL', 'NZ', '64', 'New Zealand', '1', '0');
INSERT INTO `sys_countries` VALUES ('155', 'NIC', 'NI', '505', 'Nicaragua', '1', '0');
INSERT INTO `sys_countries` VALUES ('156', 'NER', 'NE', '227', 'Niger', '1', '0');
INSERT INTO `sys_countries` VALUES ('157', 'NGA', 'NG', '234', 'Nigeria', '1', '0');
INSERT INTO `sys_countries` VALUES ('158', 'NIU', 'NU', '683', 'Niue', '1', '0');
INSERT INTO `sys_countries` VALUES ('159', 'NFK', 'NF', '672', 'Norfolk Islands', '1', '0');
INSERT INTO `sys_countries` VALUES ('160', 'MNP', 'MP', '1670', 'Mariana Islands', '1', '0');
INSERT INTO `sys_countries` VALUES ('161', 'NOR', 'NO', '47', 'Norway', '1', '0');
INSERT INTO `sys_countries` VALUES ('162', 'OMN', 'OM', '968', 'Oman', '1', '0');
INSERT INTO `sys_countries` VALUES ('163', 'PAK', 'PK', '92', 'Pakistan', '1', '0');
INSERT INTO `sys_countries` VALUES ('164', 'PLW', 'PW', '680', 'Palau', '1', '0');
INSERT INTO `sys_countries` VALUES ('165', 'PSE', 'PS', '970', 'Palestine', '1', '0');
INSERT INTO `sys_countries` VALUES ('166', 'PAN', 'PA', '507', 'Panama', '1', '0');
INSERT INTO `sys_countries` VALUES ('167', 'PNG', 'PG', '675', 'Papua New Guinea', '1', '0');
INSERT INTO `sys_countries` VALUES ('168', 'PRY', 'PY', '595', 'Paraguay', '1', '0');
INSERT INTO `sys_countries` VALUES ('169', 'PER', 'PE', '51', 'Peru', '1', '0');
INSERT INTO `sys_countries` VALUES ('170', 'PHL', 'PH', '63', 'Philippines', '1', '0');
INSERT INTO `sys_countries` VALUES ('171', 'PCN', 'PN', '870', 'Pitcairn', '1', '0');
INSERT INTO `sys_countries` VALUES ('172', 'POL', 'PL', '48', 'Poland', '1', '0');
INSERT INTO `sys_countries` VALUES ('173', 'PRT', 'PT', '351', 'Portugal', '1', '0');
INSERT INTO `sys_countries` VALUES ('174', 'PRI', 'PR', '1', 'Puerto Rico', '1', '0');
INSERT INTO `sys_countries` VALUES ('175', 'QAT', 'QA', '974', 'Qatar', '1', '0');
INSERT INTO `sys_countries` VALUES ('176', 'REU', 'RE', '262', 'Reunion Island', '1', '0');
INSERT INTO `sys_countries` VALUES ('177', 'ROU', 'RO', '40', 'Romania', '1', '0');
INSERT INTO `sys_countries` VALUES ('178', 'RUS', 'RU', '7', 'Russia', '1', '0');
INSERT INTO `sys_countries` VALUES ('179', 'RWA', 'RW', '250', 'Rwanda', '1', '0');
INSERT INTO `sys_countries` VALUES ('180', 'KNA', 'KN', '1869', 'St. Kitts', '1', '0');
INSERT INTO `sys_countries` VALUES ('181', 'LCA', 'LC', '1758', 'St. Lucia', '1', '0');
INSERT INTO `sys_countries` VALUES ('182', 'VCT', 'VC', '1784', 'St. Vincent', '1', '0');
INSERT INTO `sys_countries` VALUES ('183', 'WSM', 'WS', '685', 'Samoa', '1', '0');
INSERT INTO `sys_countries` VALUES ('184', 'SMR', 'SM', '378', 'San Marino', '1', '0');
INSERT INTO `sys_countries` VALUES ('185', 'STP', 'ST', '239', 'Sao Tome', '1', '0');
INSERT INTO `sys_countries` VALUES ('186', 'SAU', 'SA', '966', 'Saudi Arabia', '1', '0');
INSERT INTO `sys_countries` VALUES ('187', 'SEN', 'SN', '221', 'Senegal', '1', '0');
INSERT INTO `sys_countries` VALUES ('188', 'SYC', 'SC', '248', 'Seychelles', '1', '0');
INSERT INTO `sys_countries` VALUES ('189', 'SLE', 'SL', '232', 'Sierra Leone', '1', '0');
INSERT INTO `sys_countries` VALUES ('190', 'SGP', 'SG', '65', 'Singapore', '1', '0');
INSERT INTO `sys_countries` VALUES ('191', 'SVK', 'SK', '421', 'Slovakia', '1', '0');
INSERT INTO `sys_countries` VALUES ('192', 'SVN', 'SI', '386', 'Slovenia', '1', '0');
INSERT INTO `sys_countries` VALUES ('193', 'SLB', 'SB', '677', 'Solomon Islands', '1', '0');
INSERT INTO `sys_countries` VALUES ('194', 'SOM', 'SO', '252', 'Somalia', '1', '0');
INSERT INTO `sys_countries` VALUES ('195', 'ZAF', 'ZA', '27', 'South africa', '1', '0');
INSERT INTO `sys_countries` VALUES ('196', 'SGS', 'GS', '500', 'South Georgia and the South Sandwich Islands', '1', '0');
INSERT INTO `sys_countries` VALUES ('197', 'ESP', 'ES', '34', 'Spain', '1', '0');
INSERT INTO `sys_countries` VALUES ('198', 'LKA', 'LK', '94', 'Sri Lanka', '1', '0');
INSERT INTO `sys_countries` VALUES ('199', 'SHN', 'SH', '290', 'St. Helena', '1', '0');
INSERT INTO `sys_countries` VALUES ('200', 'SPM', 'PM', '508', 'St. Pierre & Miquelon', '1', '0');
INSERT INTO `sys_countries` VALUES ('201', 'SDN', 'SD', '249', 'Sudan', '1', '0');
INSERT INTO `sys_countries` VALUES ('202', 'SUR', 'SR', '597', 'Suriname', '1', '0');
INSERT INTO `sys_countries` VALUES ('203', 'SJM', 'SJ', '47', 'Svalbard and Jan Mayen Islands', '1', '0');
INSERT INTO `sys_countries` VALUES ('204', 'SWZ', 'SZ', '268', 'Swaziland', '1', '0');
INSERT INTO `sys_countries` VALUES ('205', 'SWE', 'SE', '46', 'Sweden', '1', '0');
INSERT INTO `sys_countries` VALUES ('206', 'CHE', 'CH', '41', 'Switzerland', '1', '0');
INSERT INTO `sys_countries` VALUES ('207', 'SYR', 'SY', '963', 'Syria', '1', '0');
INSERT INTO `sys_countries` VALUES ('208', 'TWN', 'TW', '886', 'Taiwan', '1', '0');
INSERT INTO `sys_countries` VALUES ('209', 'TJK', 'TJ', '992', 'Tajikistan', '1', '0');
INSERT INTO `sys_countries` VALUES ('210', 'TZA', 'TZ', '255', 'Tanzania', '1', '0');
INSERT INTO `sys_countries` VALUES ('211', 'THA', 'TH', '66', 'Thailand', '1', '0');
INSERT INTO `sys_countries` VALUES ('212', 'TGO', 'TG', '228', 'Togo', '1', '0');
INSERT INTO `sys_countries` VALUES ('213', 'TKL', 'TK', '690', 'Tokelau', '1', '0');
INSERT INTO `sys_countries` VALUES ('214', 'TON', 'TO', '676', 'Tonga', '1', '0');
INSERT INTO `sys_countries` VALUES ('215', 'TTO', 'TT', '1868', 'Trinidad & Tobago', '1', '0');
INSERT INTO `sys_countries` VALUES ('216', 'TUN', 'TN', '216', 'Tunisia', '1', '0');
INSERT INTO `sys_countries` VALUES ('217', 'TUR', 'TR', '90', 'Turkey', '1', '0');
INSERT INTO `sys_countries` VALUES ('218', 'TKM', 'TM', '993', 'Turkmenistan', '1', '0');
INSERT INTO `sys_countries` VALUES ('219', 'TCA', 'TC', '1649', 'Turks & Caicos Islands', '1', '0');
INSERT INTO `sys_countries` VALUES ('220', 'TUV', 'TV', '688', 'Tuvalu', '1', '0');
INSERT INTO `sys_countries` VALUES ('221', 'UGA', 'UG', '256', 'Uganda', '1', '0');
INSERT INTO `sys_countries` VALUES ('222', 'UKR', 'UA', '380', 'Ukraine', '1', '0');
INSERT INTO `sys_countries` VALUES ('223', 'ARE', 'AE', '971', 'United Arab Emirates', '1', '0');
INSERT INTO `sys_countries` VALUES ('224', 'GBR', 'GB', '44', 'United Kingdom', '1', '500');
INSERT INTO `sys_countries` VALUES ('225', 'USA', 'US', '1', 'United States of America', '1', '498');
INSERT INTO `sys_countries` VALUES ('226', 'UMI', 'UM', '581', 'United States Minor Outlying Islands', '1', '0');
INSERT INTO `sys_countries` VALUES ('227', 'URY', 'UY', '598', 'Uruguay', '1', '0');
INSERT INTO `sys_countries` VALUES ('228', 'UZB', 'UZ', '998', 'Uzbekistan', '1', '0');
INSERT INTO `sys_countries` VALUES ('229', 'VUT', 'VU', '678', 'Vanuatu', '1', '0');
INSERT INTO `sys_countries` VALUES ('230', 'VAT', 'VA', '39', 'Vatican', '1', '0');
INSERT INTO `sys_countries` VALUES ('231', 'VEN', 'VE', '58', 'Venezuela', '1', '0');
INSERT INTO `sys_countries` VALUES ('232', 'VNM', 'VN', '84', 'Viet nam', '1', '0');
INSERT INTO `sys_countries` VALUES ('233', 'VGB', 'VG', '1284', 'British Virgin Islands', '1', '0');
INSERT INTO `sys_countries` VALUES ('234', 'VIR', 'VI', '1340', 'US Virgin Islands', '1', '0');
INSERT INTO `sys_countries` VALUES ('235', 'WLF', 'WF', '681', 'Wallis & Futuna Islands', '1', '0');
INSERT INTO `sys_countries` VALUES ('236', 'ESH', 'EH', '212', 'Western Sahara', '1', '0');
INSERT INTO `sys_countries` VALUES ('237', 'YEM', 'YE', '967', 'Yemen', '1', '0');
INSERT INTO `sys_countries` VALUES ('238', 'YUG', 'YU', '891', 'Yugoslavia', '2', '0');
INSERT INTO `sys_countries` VALUES ('239', 'ZMB', 'ZM', '260', 'Zambia', '1', '0');
INSERT INTO `sys_countries` VALUES ('240', 'ZWE', 'ZW', '263', 'Zimbabwe', '1', '0');
INSERT INTO `sys_countries` VALUES ('241', 'SRB', 'RS', '381', 'Serbia', '1', '0');
INSERT INTO `sys_countries` VALUES ('242', 'MNE', 'ME', '382', 'Montenegro', '1', '0');
INSERT INTO `sys_countries` VALUES ('243', 'YAR', 'YE', '0', 'North Yemen', '2', '0');
INSERT INTO `sys_countries` VALUES ('244', 'SSD', 'SD', '211', 'South Sudan', '1', '0');
INSERT INTO `sys_countries` VALUES ('245', 'SCG', 'CS', '381', 'Kosovo', '1', '0');
INSERT INTO `sys_countries` VALUES ('246', 'MAF', 'MF', '1599', 'St. Martin', '1', '0');
INSERT INTO `sys_countries` VALUES ('247', 'ASC', 'AC', '247', 'Ascension Island', '2', '0');
INSERT INTO `sys_countries` VALUES ('248', 'ACT', '', '672', 'Australian Territories', '2', '0');

-- ----------------------------
-- Table structure for `sys_currencies`
-- ----------------------------
DROP TABLE IF EXISTS `sys_currencies`;
CREATE TABLE `sys_currencies` (
  `currency_id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(50) NOT NULL,
  `symbol` varchar(20) NOT NULL DEFAULT '',
  `detail_name` varchar(50) DEFAULT NULL,
  PRIMARY KEY (`currency_id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of sys_currencies
-- ----------------------------
INSERT INTO `sys_currencies` VALUES ('1', 'ZAR', 'R', 'South African Rand');
INSERT INTO `sys_currencies` VALUES ('2', 'GBP', '£', 'British Pound');
INSERT INTO `sys_currencies` VALUES ('3', 'INR', '₹', 'Indian Rupee');
INSERT INTO `sys_currencies` VALUES ('4', 'USD', '$', 'United States Dollar');
INSERT INTO `sys_currencies` VALUES ('5', 'EURO', '€', 'Euro');

-- ----------------------------
-- Table structure for `sys_currencies_conversions`
-- ----------------------------
DROP TABLE IF EXISTS `sys_currencies_conversions`;
CREATE TABLE `sys_currencies_conversions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `ratio` decimal(12,4) NOT NULL DEFAULT '1.0000',
  `currency_id` int(11) NOT NULL,
  `date` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `id_currency` (`currency_id`,`date`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of sys_currencies_conversions
-- ----------------------------
INSERT INTO `sys_currencies_conversions` VALUES ('1', '0.0490', '2', '2019-03-26 00:05:05');
INSERT INTO `sys_currencies_conversions` VALUES ('2', '1.0000', '1', '2019-03-26 00:05:05');
INSERT INTO `sys_currencies_conversions` VALUES ('3', '4.9600', '3', '2019-03-26 00:05:05');
INSERT INTO `sys_currencies_conversions` VALUES ('4', '0.0680', '4', '2019-03-26 00:05:05');
INSERT INTO `sys_currencies_conversions` VALUES ('5', '0.0570', '5', '2019-08-16 11:43:18');
INSERT INTO `sys_currencies_conversions` VALUES ('7', '0.0589', '4', '2021-02-27 01:47:34');
INSERT INTO `sys_currencies_conversions` VALUES ('8', '0.0500', '5', '2021-02-27 03:08:07');
INSERT INTO `sys_currencies_conversions` VALUES ('9', '0.0435', '2', '2021-02-27 03:10:27');
INSERT INTO `sys_currencies_conversions` VALUES ('10', '123.0000', '5', '2021-07-30 06:35:37');

-- ----------------------------
-- Table structure for `sys_payment_credentials`
-- ----------------------------
DROP TABLE IF EXISTS `sys_payment_credentials`;
CREATE TABLE `sys_payment_credentials` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `account_id` varchar(30) NOT NULL,
  `payment_method` varchar(30) DEFAULT NULL,
  `credentials` text,
  `status` enum('Y','N') DEFAULT 'Y',
  PRIMARY KEY (`id`),
  UNIQUE KEY `account_id` (`account_id`,`payment_method`,`status`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=46 DEFAULT CHARSET=latin1;

-- ----------------------------
-- Records of sys_payment_credentials
-- ----------------------------
INSERT INTO `sys_payment_credentials` VALUES ('43', 'SYSTEM', 'paypal', '{\"business\":\"openvoips@gmail.com\"}', 'Y');

-- ----------------------------
-- Table structure for `sys_rule_options`
-- ----------------------------
DROP TABLE IF EXISTS `sys_rule_options`;
CREATE TABLE `sys_rule_options` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `option_id` varchar(100) NOT NULL,
  `option_name` varchar(100) NOT NULL,
  `option_group` varchar(50) NOT NULL,
  `status_id` enum('1','0') NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=latin1;

-- ----------------------------
-- Records of sys_rule_options
-- ----------------------------
INSERT INTO `sys_rule_options` VALUES ('1', 'ADDBALANCE', 'Add Balance', 'payment', '1');
INSERT INTO `sys_rule_options` VALUES ('2', 'ADDCREDIT', 'Add Credit', 'payment', '1');
INSERT INTO `sys_rule_options` VALUES ('3', 'REMOVEBALANCE', 'Refund Balance', 'payment', '1');
INSERT INTO `sys_rule_options` VALUES ('4', 'REMOVECREDIT', 'Reduce Credit', 'payment', '1');
INSERT INTO `sys_rule_options` VALUES ('5', 'daily-balance', 'Daily Email', 'notification', '1');
INSERT INTO `sys_rule_options` VALUES ('6', 'low-balance', 'Low Balance', 'notification', '1');

-- ----------------------------
-- Table structure for `sys_sdr_terms`
-- ----------------------------
DROP TABLE IF EXISTS `sys_sdr_terms`;
CREATE TABLE `sys_sdr_terms` (
  `term_id` int(11) NOT NULL AUTO_INCREMENT,
  `term_group` varchar(30) NOT NULL,
  `term` varchar(30) NOT NULL,
  `display_text` varchar(255) NOT NULL,
  `cost_calculation_formula` varchar(10) NOT NULL,
  `service_id` varchar(30) DEFAULT NULL,
  PRIMARY KEY (`term_id`),
  UNIQUE KEY `term` (`term`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=124 DEFAULT CHARSET=latin1;

-- ----------------------------
-- Records of sys_sdr_terms
-- ----------------------------
INSERT INTO `sys_sdr_terms` VALUES ('1', 'balance', 'ADDBALANCE', 'Payment Received', '+', 'BALANCE');
INSERT INTO `sys_sdr_terms` VALUES ('2', 'balance', 'ADDCREDIT', 'Credit Added', '', 'BALANCE');
INSERT INTO `sys_sdr_terms` VALUES ('3', 'balance', 'REMOVEBALANCE', 'Payment Refund', '-', 'BALANCE');
INSERT INTO `sys_sdr_terms` VALUES ('4', 'balance', 'REMOVECREDIT', 'Credit Reduced', '', 'BALANCE');
INSERT INTO `sys_sdr_terms` VALUES ('11', 'opening', 'OPENINGBALANCE', 'Opening Balance', '+', 'BALANCE');
INSERT INTO `sys_sdr_terms` VALUES ('55', 'usage', 'GEONUMBER', 'Geographic Numbers', '-', 'DIDSERVICE');
INSERT INTO `sys_sdr_terms` VALUES ('56', 'usage', 'NONGEONUMBER', 'Non-Geographic Numbers', '-', 'DIDSERVICE');
INSERT INTO `sys_sdr_terms` VALUES ('57', 'usage', 'TELKOMPARNUMBER', 'Telkom PRA Numbers', '-', 'DIDSERVICE');
INSERT INTO `sys_sdr_terms` VALUES ('58', 'usage', 'IN', 'Inbound Calls', '-', 'VOICECALL');
INSERT INTO `sys_sdr_terms` VALUES ('59', 'usage', 'OUT', 'Outbond Calls', '-', 'VOICECALL');
INSERT INTO `sys_sdr_terms` VALUES ('60', 'usage', 'EXTEN', 'Standard Extension', '-', 'VOICESERVICE');
INSERT INTO `sys_sdr_terms` VALUES ('61', 'usage', 'EEXTEN', 'Enterprise Extension', '-', 'VOICESERVICE');
INSERT INTO `sys_sdr_terms` VALUES ('62', 'usage', 'CEXTEN', 'Contact Center Extension', '-', 'VOICESERVICE');
INSERT INTO `sys_sdr_terms` VALUES ('63', 'usage', 'AGENT', 'ViciDial Agent', '-', 'VOICESERVICE');
INSERT INTO `sys_sdr_terms` VALUES ('64', 'usage', 'MOBILEAPP', 'Mobile Application', '-', 'VOICESERVICE');
INSERT INTO `sys_sdr_terms` VALUES ('65', 'usage', 'RECORDING', 'Call Recorings', '-', 'VOICESERVICE');
INSERT INTO `sys_sdr_terms` VALUES ('66', 'usage', 'PINDIAL', 'PIN Dialling', '-', 'VOICESERVICE');
INSERT INTO `sys_sdr_terms` VALUES ('67', 'usage', 'SPEEDDIAL', 'Speed Dial', '-', 'VOICESERVICE');
INSERT INTO `sys_sdr_terms` VALUES ('68', 'usage', 'MOH', 'Music on Hold', '-', 'VOICESERVICE');
INSERT INTO `sys_sdr_terms` VALUES ('69', 'usage', 'IVR', 'IVR', '-', 'VOICESERVICE');
INSERT INTO `sys_sdr_terms` VALUES ('70', 'usage', 'QUEUE', 'Queue', '-', 'VOICESERVICE');
INSERT INTO `sys_sdr_terms` VALUES ('71', 'usage', 'TIMEROUTE', 'Time Routing', '-', 'VOICESERVICE');
INSERT INTO `sys_sdr_terms` VALUES ('72', 'usage', 'RINGGROUP', 'Ring Group', '-', 'VOICESERVICE');
INSERT INTO `sys_sdr_terms` VALUES ('73', 'usage', 'VOICEMAIL', 'Voicemail', '-', 'VOICESERVICE');
INSERT INTO `sys_sdr_terms` VALUES ('74', 'usage', 'WALLBOARD', 'Wallboard', '-', 'VOICESERVICE');
INSERT INTO `sys_sdr_terms` VALUES ('75', 'usage', 'SIPTRUNK', 'SIP Trunk', '-', 'VOICESERVICE');
INSERT INTO `sys_sdr_terms` VALUES ('76', 'usage', 'CFD', 'Call Forwarding', '-', 'VOICESERVICE');
INSERT INTO `sys_sdr_terms` VALUES ('77', 'usage', 'CONFERENCE', 'Conference Rooms', '-', 'VOICESERVICE');
INSERT INTO `sys_sdr_terms` VALUES ('78', 'usage', 'F2E', 'Fax2Email', '-', 'VOICESERVICE');
INSERT INTO `sys_sdr_terms` VALUES ('79', 'usage', 'E2F', 'Email2Fax', '-', 'VOICESERVICE');
INSERT INTO `sys_sdr_terms` VALUES ('80', 'usage', 'TMSR', 'TMS / Reporting', '-', 'VOICESERVICE');
INSERT INTO `sys_sdr_terms` VALUES ('81', 'usage', 'VOIPGATEWAY', 'VoIP Gateways', '-', 'EQUIPMENT');
INSERT INTO `sys_sdr_terms` VALUES ('82', 'usage', 'IPPHONE', 'IP Phones', '-', 'EQUIPMENT');
INSERT INTO `sys_sdr_terms` VALUES ('83', 'usage', 'CORDLESSIPPHONE', 'Cordless IP Phones', '-', 'EQUIPMENT');
INSERT INTO `sys_sdr_terms` VALUES ('84', 'usage', 'MULTICELLDECTIPPHONE', 'Multi Cell DECT IP Phones', '-', 'EQUIPMENT');
INSERT INTO `sys_sdr_terms` VALUES ('85', 'usage', 'HEADSETS', 'Headsets', '-', 'EQUIPMENT');
INSERT INTO `sys_sdr_terms` VALUES ('86', 'usage', 'IPCONFERENCEPHONE', 'IP Conference Phones', '-', 'EQUIPMENT');
INSERT INTO `sys_sdr_terms` VALUES ('87', 'usage', 'IPPHONEACCESSORIES', 'IP Phone Accessories', '-', 'EQUIPMENT');
INSERT INTO `sys_sdr_terms` VALUES ('88', 'usage', 'SWITCHES', 'Switches', '-', 'EQUIPMENT');
INSERT INTO `sys_sdr_terms` VALUES ('89', 'usage', 'ROUTES', 'Routers', '-', 'EQUIPMENT');
INSERT INTO `sys_sdr_terms` VALUES ('90', 'usage', 'FIREWALL', 'Firewalls', '-', 'EQUIPMENT');
INSERT INTO `sys_sdr_terms` VALUES ('91', 'usage', 'LTEROUTES', 'LTE Routers', '-', 'EQUIPMENT');
INSERT INTO `sys_sdr_terms` VALUES ('92', 'usage', 'WALLBOXES', 'Wallboxes', '-', 'EQUIPMENT');
INSERT INTO `sys_sdr_terms` VALUES ('93', 'usage', 'WALLBOXACCESSORIES', 'Wallbox Accessories', '-', 'EQUIPMENT');
INSERT INTO `sys_sdr_terms` VALUES ('94', 'usage', 'CABLING', 'Cabling', '-', 'EQUIPMENT');
INSERT INTO `sys_sdr_terms` VALUES ('95', 'usage', 'NUMBERPORTABLINING', 'Number Portability', '-', 'OTHERSERVICES');
INSERT INTO `sys_sdr_terms` VALUES ('96', 'usage', 'AWSSERVICES', 'AWS Services', '-', 'OTHERSERVICES');
INSERT INTO `sys_sdr_terms` VALUES ('97', 'usage', 'SUPPORTSLA', 'Support SLAs', '-', 'OTHERSERVICES');
INSERT INTO `sys_sdr_terms` VALUES ('98', 'usage', 'MANAGESERVICE', 'Managed Services', '-', 'OTHERSERVICES');
INSERT INTO `sys_sdr_terms` VALUES ('99', 'usage', 'TRAINING', 'Trainings', '-', 'OTHERSERVICES');
INSERT INTO `sys_sdr_terms` VALUES ('100', 'usage', 'CALLOUTS', 'Call Outs', '-', 'OTHERSERVICES');
INSERT INTO `sys_sdr_terms` VALUES ('101', 'usage', 'LABOURCHARGES', 'Labour Charges', '-', 'OTHERSERVICES');
INSERT INTO `sys_sdr_terms` VALUES ('102', 'usage', 'WHILELABELSERVICE', 'While Label Services', '-', 'OTHERSERVICES');
INSERT INTO `sys_sdr_terms` VALUES ('103', 'usage', 'LTE', 'LTE', '-', 'INTERNET');
INSERT INTO `sys_sdr_terms` VALUES ('104', 'usage', 'FTTH', 'FTTH', '-', 'INTERNET');
INSERT INTO `sys_sdr_terms` VALUES ('105', 'usage', 'ENTERPRISEFIBRE', 'Enterprise Fibre', '-', 'INTERNET');
INSERT INTO `sys_sdr_terms` VALUES ('106', 'usage', 'BUSINESSFIBRE', 'Business Fibre', '-', 'INTERNET');
INSERT INTO `sys_sdr_terms` VALUES ('107', 'usage', 'BROADBANDFIBRE', 'Broadband Fibre', '-', 'INTERNET');
INSERT INTO `sys_sdr_terms` VALUES ('108', 'usage', 'LICENSEDWIRELESS', 'Licensed Wireless', '-', 'INTERNET');
INSERT INTO `sys_sdr_terms` VALUES ('109', 'usage', 'UNLICENSEDWIRELESS', 'Unlicensed Wireless', '-', 'INTERNET');
INSERT INTO `sys_sdr_terms` VALUES ('110', 'usage', 'DIDEXTRACHRENTAL', 'Extra Channels in DID/Line Charge', '-', 'DIDSERVICE');
INSERT INTO `sys_sdr_terms` VALUES ('111', 'usage', 'DIDRENTAL', 'DID / Line Rental', '-', 'DIDSERVICE');
INSERT INTO `sys_sdr_terms` VALUES ('112', 'usage', 'DIDSETUP', 'DID / Line Rental Setup Charge', '-', 'DIDSERVICE');
INSERT INTO `sys_sdr_terms` VALUES ('113', 'usage', 'TARIFFCHARGES', 'Service Plan Charge', '-', 'VOICESERVICE');
INSERT INTO `sys_sdr_terms` VALUES ('114', 'usage', 'DIDCANCEL', 'DID / Line Cancellation', '-', 'DIDSERVICE');
INSERT INTO `sys_sdr_terms` VALUES ('115', 'usage', 'AGENTEXTEN', 'Callcenter Extension', '-', 'VOICESERVICE');
INSERT INTO `sys_sdr_terms` VALUES ('116', 'usage', 'EXTERPRICEEXTEN', 'Enterprise Extension', '-', 'VOICESERVICE');
INSERT INTO `sys_sdr_terms` VALUES ('117', 'usage', 'BUNDLECHARGES', 'Bundle Subscription', '-', 'VOICESERVICE');
INSERT INTO `sys_sdr_terms` VALUES ('118', 'usage', 'FIXLINEALTERNATIVEFLA', 'Fixed Line Alternative FLA', '-', 'VOICESERVICE');
INSERT INTO `sys_sdr_terms` VALUES ('119', 'usage', 'VOIPLINERENTAL', 'VoIP Line Rental', '-', 'VOICESERVICE');
INSERT INTO `sys_sdr_terms` VALUES ('120', 'usage', 'CLOUDPBXMONTHOLYFREE', 'Cloud PBX Monthly Fees', '-', 'VOICESERVICE');
INSERT INTO `sys_sdr_terms` VALUES ('121', 'usage', 'VOICECHANNELS', 'Voice Channels', '-', 'VOICESERVICE');
INSERT INTO `sys_sdr_terms` VALUES ('123', 'usage', 'LINERENTAL', 'Line Rental', '-', 'VOICESERVICE');

-- ----------------------------
-- Table structure for `sys_sitesetup`
-- ----------------------------
DROP TABLE IF EXISTS `sys_sitesetup`;
CREATE TABLE `sys_sitesetup` (
  `sitesetup_id` int(11) NOT NULL AUTO_INCREMENT,
  `payment_invoice_counter` bigint(20) NOT NULL,
  `prorata_billing` enum('1','0') NOT NULL DEFAULT '0',
  PRIMARY KEY (`sitesetup_id`)
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=latin1;

-- ----------------------------
-- Records of sys_sitesetup
-- ----------------------------
INSERT INTO `sys_sitesetup` VALUES ('1', '165', '1');

-- ----------------------------
-- Table structure for `sys_states`
-- ----------------------------
DROP TABLE IF EXISTS `sys_states`;
CREATE TABLE `sys_states` (
  `state_id` int(30) NOT NULL AUTO_INCREMENT,
  `state_name` varchar(60) DEFAULT NULL,
  `state_code_id` varchar(20) DEFAULT NULL,
  `country` varchar(50) DEFAULT 'INDIA',
  PRIMARY KEY (`state_id`)
) ENGINE=InnoDB AUTO_INCREMENT=38 DEFAULT CHARSET=latin1;

-- ----------------------------
-- Records of sys_states
-- ----------------------------
INSERT INTO `sys_states` VALUES ('1', 'JAMMU AND KASHMIR', '01', 'INDIA');
INSERT INTO `sys_states` VALUES ('2', 'HIMACHAL PRADESH', '02', 'INDIA');
INSERT INTO `sys_states` VALUES ('3', 'PUNJAB', '03', 'INDIA');
INSERT INTO `sys_states` VALUES ('4', 'CHANDIGARH', '04', 'INDIA');
INSERT INTO `sys_states` VALUES ('5', 'UTTARAKHAND', '05', 'INDIA');
INSERT INTO `sys_states` VALUES ('6', 'HARYANA', '06', 'INDIA');
INSERT INTO `sys_states` VALUES ('7', 'DELHI', '07', 'INDIA');
INSERT INTO `sys_states` VALUES ('8', 'RAJASTHAN', '08', 'INDIA');
INSERT INTO `sys_states` VALUES ('9', 'UTTAR  PRADESH', '09', 'INDIA');
INSERT INTO `sys_states` VALUES ('10', 'BIHAR', '10', 'INDIA');
INSERT INTO `sys_states` VALUES ('11', 'SIKKIM', '11', 'INDIA');
INSERT INTO `sys_states` VALUES ('12', 'ARUNACHAL PRADESH', '12', 'INDIA');
INSERT INTO `sys_states` VALUES ('13', 'NAGALAND', '13', 'INDIA');
INSERT INTO `sys_states` VALUES ('14', 'MANIPUR', '14', 'INDIA');
INSERT INTO `sys_states` VALUES ('15', 'MIZORAM', '15', 'INDIA');
INSERT INTO `sys_states` VALUES ('16', 'TRIPURA', '16', 'INDIA');
INSERT INTO `sys_states` VALUES ('17', 'MEGHLAYA', '17', 'INDIA');
INSERT INTO `sys_states` VALUES ('18', 'ASSAM', '18', 'INDIA');
INSERT INTO `sys_states` VALUES ('19', 'WEST BENGAL', '19', 'INDIA');
INSERT INTO `sys_states` VALUES ('20', 'JHARKHAND', '20', 'INDIA');
INSERT INTO `sys_states` VALUES ('21', 'ODISHA', '21', 'INDIA');
INSERT INTO `sys_states` VALUES ('22', 'CHATTISGARH', '22', 'INDIA');
INSERT INTO `sys_states` VALUES ('23', 'MADHYA PRADESH', '23', 'INDIA');
INSERT INTO `sys_states` VALUES ('24', 'GUJARAT', '24', 'INDIA');
INSERT INTO `sys_states` VALUES ('25', 'DAMAN AND DIU', '25', 'INDIA');
INSERT INTO `sys_states` VALUES ('26', 'DADRA AND NAGAR HAVELI', '26', 'INDIA');
INSERT INTO `sys_states` VALUES ('27', 'MAHARASHTRA', '27', 'INDIA');
INSERT INTO `sys_states` VALUES ('28', 'ANDHRA PRADESH(BEFORE DIVISION)', '28', 'INDIA');
INSERT INTO `sys_states` VALUES ('29', 'KARNATAKA', '29', 'INDIA');
INSERT INTO `sys_states` VALUES ('30', 'GOA', '30', 'INDIA');
INSERT INTO `sys_states` VALUES ('31', 'LAKSHWADEEP', '31', 'INDIA');
INSERT INTO `sys_states` VALUES ('32', 'KERALA', '32', 'INDIA');
INSERT INTO `sys_states` VALUES ('33', 'TAMIL NADU', '33', 'INDIA');
INSERT INTO `sys_states` VALUES ('34', 'PUDUCHERRY', '34', 'INDIA');
INSERT INTO `sys_states` VALUES ('35', 'ANDAMAN AND NICOBAR ISLANDS', '35', 'INDIA');
INSERT INTO `sys_states` VALUES ('36', 'TELANGANA', '36', 'INDIA');
INSERT INTO `sys_states` VALUES ('37', 'ANDHRA PRADESH (NEW)', '37', 'INDIA');

-- ----------------------------
-- Table structure for `tariff`
-- ----------------------------
DROP TABLE IF EXISTS `tariff`;
CREATE TABLE `tariff` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `tariff_id` varchar(30) DEFAULT NULL,
  `tariff_name` varchar(30) DEFAULT NULL,
  `tariff_currency_id` int(11) DEFAULT '1',
  `tariff_status` enum('1','0') DEFAULT '1',
  `tariff_description` varchar(50) DEFAULT NULL,
  `tariff_type` enum('CARRIER','CUSTOMER') NOT NULL DEFAULT 'CARRIER',
  `account_id` varchar(30) NOT NULL,
  `package_option` enum('1','0') DEFAULT '0',
  `monthly_charges` double DEFAULT '0',
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
  `update_dt` timestamp NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `tariff_id_name` (`tariff_id`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=latin1;

-- ----------------------------
-- Records of tariff
-- ----------------------------
INSERT INTO `tariff` VALUES ('2', 'CUSTOMER54', 'Customer Tariff', '4', '1', '', 'CUSTOMER', 'SYSTEM', '0', '0', '0', 'MINUTE', null, 'MINUTE', null, 'MINUTE', null, 'ADMIN', '', '2021-07-31 12:07:11', '2021-07-31 16:43:22');
INSERT INTO `tariff` VALUES ('3', 'CARRIER40', 'Carrier Tariff', '4', '1', '', 'CARRIER', 'SYSTEM', '0', '0', '0', 'MINUTE', null, 'MINUTE', null, 'MINUTE', null, 'ADMIN', '', '2021-07-31 12:07:23', '2021-07-31 16:43:09');

-- ----------------------------
-- Table structure for `tariff_ratecard_map`
-- ----------------------------
DROP TABLE IF EXISTS `tariff_ratecard_map`;
CREATE TABLE `tariff_ratecard_map` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `ratecard_id` varchar(30) DEFAULT NULL,
  `tariff_id` varchar(30) DEFAULT NULL,
  `start_day` int(11) DEFAULT NULL,
  `start_time` varchar(8) DEFAULT '00:00:00',
  `end_day` int(11) DEFAULT NULL,
  `end_time` varchar(8) DEFAULT '24:00:00',
  `priority` int(11) DEFAULT '1',
  `status` enum('1','0') DEFAULT '1',
  `ratecard_for` enum('INCOMING','OUTGOING') DEFAULT 'OUTGOING',
  `account_id` varchar(30) DEFAULT NULL,
  `created_by` varchar(30) DEFAULT NULL,
  `updated_by` varchar(30) DEFAULT NULL,
  `created_dt` datetime DEFAULT NULL,
  `updated_dt` timestamp NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `ratecard_id` (`ratecard_id`) USING BTREE,
  KEY `tariff_id` (`tariff_id`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=87 DEFAULT CHARSET=latin1;

-- ----------------------------
-- Records of tariff_ratecard_map
-- ----------------------------
INSERT INTO `tariff_ratecard_map` VALUES ('83', 'CARRIER47', 'CARRIER40', '0', '00:00:00', '6', '23:59:59', '1', '1', 'OUTGOING', null, null, null, null, null);
INSERT INTO `tariff_ratecard_map` VALUES ('84', 'DIDRATES41', 'CARRIER40', '0', '00:00:00', '6', '23:59:59', '1', '1', 'INCOMING', null, null, null, null, null);
INSERT INTO `tariff_ratecard_map` VALUES ('85', 'CUSTOMER42', 'CUSTOMER54', '0', '00:00:00', '6', '23:59:59', '1', '1', 'OUTGOING', null, null, null, null, null);
INSERT INTO `tariff_ratecard_map` VALUES ('86', 'DIDRATES16', 'CUSTOMER54', '0', '00:00:00', '6', '23:59:59', '1', '1', 'INCOMING', null, null, null, null, null);

-- ----------------------------
-- Table structure for `ticket_assigned_to`
-- ----------------------------
DROP TABLE IF EXISTS `ticket_assigned_to`;
CREATE TABLE `ticket_assigned_to` (
  `assigned_to_id` int(11) NOT NULL AUTO_INCREMENT,
  `assigned_to_name` varchar(50) NOT NULL,
  PRIMARY KEY (`assigned_to_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- ----------------------------
-- Records of ticket_assigned_to
-- ----------------------------

-- ----------------------------
-- Table structure for `ticket_attachments`
-- ----------------------------
DROP TABLE IF EXISTS `ticket_attachments`;
CREATE TABLE `ticket_attachments` (
  `attachment_id` int(11) NOT NULL AUTO_INCREMENT,
  `ticket_id` int(11) NOT NULL,
  `file_name` varchar(100) NOT NULL,
  `file_name_display` varchar(100) NOT NULL,
  PRIMARY KEY (`attachment_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- ----------------------------
-- Records of ticket_attachments
-- ----------------------------

-- ----------------------------
-- Table structure for `ticket_categories`
-- ----------------------------
DROP TABLE IF EXISTS `ticket_categories`;
CREATE TABLE `ticket_categories` (
  `category_id` int(11) NOT NULL AUTO_INCREMENT,
  `category_parent_id` int(11) NOT NULL,
  `category_name` varchar(50) NOT NULL,
  `status` enum('Y','N') NOT NULL,
  PRIMARY KEY (`category_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- ----------------------------
-- Records of ticket_categories
-- ----------------------------

-- ----------------------------
-- Table structure for `tickets`
-- ----------------------------
DROP TABLE IF EXISTS `tickets`;
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
  `create_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `close_date` datetime DEFAULT NULL,
  `author_name` varchar(30) NOT NULL,
  `author_email` varchar(50) NOT NULL,
  `author_email_subscribe` enum('Y','N') NOT NULL DEFAULT 'N',
  PRIMARY KEY (`ticket_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- ----------------------------
-- Records of tickets
-- ----------------------------

-- ----------------------------
-- Table structure for `user_audit_trails`
-- ----------------------------
DROP TABLE IF EXISTS `user_audit_trails`;
CREATE TABLE `user_audit_trails` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `event` enum('insert','update','delete') NOT NULL,
  `table_name` varchar(128) NOT NULL,
  `old_values` text,
  `new_values` text NOT NULL,
  `url` varchar(255) NOT NULL,
  `name` varchar(128) NOT NULL,
  `ip_address` varchar(45) NOT NULL,
  `user_agent` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of user_audit_trails
-- ----------------------------

-- ----------------------------
-- Table structure for `user_type_permissions`
-- ----------------------------
DROP TABLE IF EXISTS `user_type_permissions`;
CREATE TABLE `user_type_permissions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_type` varchar(50) NOT NULL,
  `permissions` text NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=latin1;

-- ----------------------------
-- Records of user_type_permissions
-- ----------------------------
INSERT INTO `user_type_permissions` VALUES ('1', 'RESELLER', 'a:6:{s:8:\"reseller\";a:4:{i:0;s:4:\"view\";i:1;s:3:\"add\";i:2;s:4:\"edit\";i:3;s:6:\"delete\";}s:8:\"customer\";a:4:{i:0;s:4:\"view\";i:1;s:3:\"add\";i:2;s:4:\"edit\";i:3;s:6:\"delete\";}s:8:\"ratecard\";a:5:{i:0;s:4:\"view\";i:1;s:3:\"add\";i:2;s:4:\"edit\";i:3;s:6:\"delete\";i:4;s:6:\"upload\";}s:4:\"rate\";a:4:{i:0;s:4:\"view\";i:1;s:3:\"add\";i:2;s:4:\"edit\";i:3;s:6:\"delete\";}s:6:\"tariff\";a:4:{i:0;s:4:\"view\";i:1;s:3:\"add\";i:2;s:4:\"edit\";i:3;s:6:\"delete\";}s:7:\"reports\";a:9:{i:0;s:3:\"cdr\";i:1;s:10:\"fail_calls\";i:2;s:11:\"call_report\";i:3;s:12:\"report_topup\";i:4;s:20:\"report_topup_monthly\";i:5;s:22:\"customer_topup_summery\";i:6;s:18:\"report_daily_sales\";i:7;s:26:\"report_daily_sales_monthly\";i:8;s:22:\"customer_sales_summery\";}}');
INSERT INTO `user_type_permissions` VALUES ('2', 'SUBADMIN', 'a:11:{s:4:\"user\";a:2:{i:0;s:4:\"view\";i:1;s:3:\"add\";}s:8:\"reseller\";a:2:{i:0;s:4:\"view\";i:1;s:6:\"delete\";}s:7:\"carrier\";a:2:{i:0;s:4:\"view\";i:1;s:3:\"add\";}s:7:\"routing\";a:4:{i:0;s:4:\"view\";i:1;s:3:\"add\";i:2;s:4:\"edit\";i:3;s:6:\"delete\";}s:8:\"dialplan\";a:4:{i:0;s:4:\"view\";i:1;s:3:\"add\";i:2;s:4:\"edit\";i:3;s:6:\"delete\";}s:8:\"ratecard\";a:5:{i:0;s:4:\"view\";i:1;s:3:\"add\";i:2;s:4:\"edit\";i:3;s:6:\"delete\";i:4;s:6:\"upload\";}s:4:\"rate\";a:4:{i:0;s:4:\"view\";i:1;s:3:\"add\";i:2;s:4:\"edit\";i:3;s:6:\"delete\";}s:6:\"tariff\";a:4:{i:0;s:4:\"view\";i:1;s:3:\"add\";i:2;s:4:\"edit\";i:3;s:6:\"delete\";}s:8:\"provider\";a:3:{i:0;s:4:\"view\";i:1;s:3:\"add\";i:2;s:4:\"edit\";}s:7:\"service\";a:2:{i:0;s:4:\"view\";i:1;s:3:\"add\";}s:7:\"reports\";a:7:{i:0;s:3:\"cdr\";i:1;s:10:\"fail_calls\";i:2;s:4:\"live\";i:3;s:17:\"analytics_carrier\";i:4;s:18:\"accounting_billing\";i:5;s:7:\"summary\";i:6;s:11:\"call_report\";}}');
INSERT INTO `user_type_permissions` VALUES ('3', 'CUSTOMER', 'a:4:{s:8:\"customer\";a:2:{i:0;s:4:\"view\";i:1;s:7:\"cliedit\";}s:7:\"routing\";a:4:{i:0;s:4:\"view\";i:1;s:3:\"add\";i:2;s:4:\"edit\";i:3;s:6:\"delete\";}s:7:\"service\";a:1:{i:0;s:4:\"view\";}s:7:\"reports\";a:6:{i:0;s:3:\"cdr\";i:1;s:10:\"fail_calls\";i:2;s:4:\"live\";i:3;s:9:\"statement\";i:4;s:9:\"myinvoice\";i:5;s:16:\"report_statement\";}}');
INSERT INTO `user_type_permissions` VALUES ('4', 'ACCOUNTS', 'a:8:{s:8:\"reseller\";a:4:{i:0;s:4:\"view\";i:1;s:3:\"add\";i:2;s:4:\"edit\";i:3;s:6:\"delete\";}s:8:\"customer\";a:4:{i:0;s:4:\"view\";i:1;s:3:\"add\";i:2;s:4:\"edit\";i:3;s:6:\"delete\";}s:7:\"carrier\";a:1:{i:0;s:6:\"delete\";}s:8:\"ratecard\";a:5:{i:0;s:4:\"view\";i:1;s:3:\"add\";i:2;s:4:\"edit\";i:3;s:6:\"delete\";i:4;s:6:\"upload\";}s:4:\"rate\";a:4:{i:0;s:4:\"view\";i:1;s:3:\"add\";i:2;s:4:\"edit\";i:3;s:6:\"delete\";}s:6:\"tariff\";a:4:{i:0;s:4:\"view\";i:1;s:3:\"add\";i:2;s:4:\"edit\";i:3;s:6:\"delete\";}s:7:\"service\";a:4:{i:0;s:4:\"view\";i:1;s:3:\"add\";i:2;s:4:\"edit\";i:3;s:6:\"delete\";}s:7:\"reports\";a:15:{i:0;s:3:\"cdr\";i:1;s:10:\"fail_calls\";i:2;s:5:\"monin\";i:3;s:8:\"CustQOSR\";i:4;s:12:\"monitCarrier\";i:5;s:17:\"analytics_carrier\";i:6;s:18:\"accounting_billing\";i:7;s:7:\"summary\";i:8;s:11:\"call_report\";i:9;s:12:\"report_topup\";i:10;s:20:\"report_topup_monthly\";i:11;s:22:\"customer_topup_summery\";i:12;s:18:\"report_daily_sales\";i:13;s:26:\"report_daily_sales_monthly\";i:14;s:22:\"customer_sales_summery\";}}');
INSERT INTO `user_type_permissions` VALUES ('6', 'RESELLERACCOUNT', 'a:3:{s:5:\"admin\";a:1:{i:0;s:4:\"view\";}s:8:\"reseller\";a:1:{i:0;s:4:\"view\";}s:8:\"customer\";a:1:{i:0;s:3:\"add\";}}');
INSERT INTO `user_type_permissions` VALUES ('7', 'RESELLERADMIN', 'a:11:{s:4:\"user\";a:4:{i:0;s:4:\"view\";i:1;s:3:\"add\";i:2;s:4:\"edit\";i:3;s:5:\"login\";}s:8:\"reseller\";a:6:{i:0;s:4:\"view\";i:1;s:3:\"add\";i:2;s:4:\"edit\";i:3;s:6:\"delete\";i:4;s:7:\"cliedit\";i:5;s:5:\"login\";}s:8:\"customer\";a:6:{i:0;s:4:\"view\";i:1;s:3:\"add\";i:2;s:4:\"edit\";i:3;s:6:\"delete\";i:4;s:7:\"cliedit\";i:5;s:5:\"login\";}s:7:\"carrier\";a:4:{i:0;s:4:\"view\";i:1;s:3:\"add\";i:2;s:4:\"edit\";i:3;s:6:\"delete\";}s:7:\"routing\";a:3:{i:0;s:4:\"view\";i:1;s:3:\"add\";i:2;s:4:\"edit\";}s:8:\"dialplan\";a:4:{i:0;s:4:\"view\";i:1;s:3:\"add\";i:2;s:4:\"edit\";i:3;s:6:\"delete\";}s:8:\"ratecard\";a:5:{i:0;s:4:\"view\";i:1;s:3:\"add\";i:2;s:4:\"edit\";i:3;s:6:\"delete\";i:4;s:6:\"upload\";}s:4:\"rate\";a:4:{i:0;s:4:\"view\";i:1;s:3:\"add\";i:2;s:4:\"edit\";i:3;s:6:\"delete\";}s:6:\"tariff\";a:4:{i:0;s:4:\"view\";i:1;s:3:\"add\";i:2;s:4:\"edit\";i:3;s:6:\"delete\";}s:6:\"bundle\";a:4:{i:0;s:4:\"view\";i:1;s:3:\"add\";i:2;s:4:\"edit\";i:3;s:6:\"delete\";}s:15:\"payment_gateway\";a:1:{i:0;s:3:\"add\";}}');
INSERT INTO `user_type_permissions` VALUES ('8', 'ACCOUNT', 'a:15:{s:4:\"user\";a:4:{i:0;s:4:\"view\";i:1;s:3:\"add\";i:2;s:4:\"edit\";i:3;s:5:\"login\";}s:8:\"reseller\";a:6:{i:0;s:4:\"view\";i:1;s:3:\"add\";i:2;s:4:\"edit\";i:3;s:6:\"delete\";i:4;s:7:\"cliedit\";i:5;s:5:\"login\";}s:8:\"customer\";a:6:{i:0;s:4:\"view\";i:1;s:3:\"add\";i:2;s:4:\"edit\";i:3;s:6:\"delete\";i:4;s:7:\"cliedit\";i:5;s:5:\"login\";}s:7:\"carrier\";a:4:{i:0;s:4:\"view\";i:1;s:3:\"add\";i:2;s:4:\"edit\";i:3;s:6:\"delete\";}s:7:\"routing\";a:4:{i:0;s:4:\"view\";i:1;s:3:\"add\";i:2;s:4:\"edit\";i:3;s:6:\"delete\";}s:8:\"dialplan\";a:4:{i:0;s:4:\"view\";i:1;s:3:\"add\";i:2;s:4:\"edit\";i:3;s:6:\"delete\";}s:8:\"ratecard\";a:5:{i:0;s:4:\"view\";i:1;s:3:\"add\";i:2;s:4:\"edit\";i:3;s:6:\"delete\";i:4;s:6:\"upload\";}s:4:\"rate\";a:4:{i:0;s:4:\"view\";i:1;s:3:\"add\";i:2;s:4:\"edit\";i:3;s:6:\"delete\";}s:6:\"tariff\";a:4:{i:0;s:4:\"view\";i:1;s:3:\"add\";i:2;s:4:\"edit\";i:3;s:6:\"delete\";}s:6:\"bundle\";a:4:{i:0;s:4:\"view\";i:1;s:3:\"add\";i:2;s:4:\"edit\";i:3;s:6:\"delete\";}s:8:\"provider\";a:4:{i:0;s:4:\"view\";i:1;s:3:\"add\";i:2;s:4:\"edit\";i:3;s:6:\"delete\";}s:7:\"service\";a:4:{i:0;s:4:\"view\";i:1;s:3:\"add\";i:2;s:4:\"edit\";i:3;s:6:\"delete\";}s:6:\"system\";a:1:{i:0;s:11:\"system_load\";}s:15:\"payment_gateway\";a:1:{i:0;s:3:\"add\";}s:7:\"reports\";a:21:{i:0;s:3:\"cdr\";i:1;s:10:\"fail_calls\";i:2;s:4:\"live\";i:3;s:5:\"monin\";i:4;s:8:\"CustQOSR\";i:5;s:12:\"monitCarrier\";i:6;s:17:\"analytics_carrier\";i:7;s:18:\"accounting_billing\";i:8;s:7:\"summary\";i:9;s:11:\"call_report\";i:10;s:12:\"report_topup\";i:11;s:20:\"report_topup_monthly\";i:12;s:22:\"customer_topup_summery\";i:13;s:18:\"report_daily_sales\";i:14;s:26:\"report_daily_sales_monthly\";i:15;s:22:\"customer_sales_summery\";i:16;s:9:\"statement\";i:17;s:9:\"myinvoice\";i:18;s:16:\"report_statement\";i:19;s:10:\"ProfitLoss\";i:20;s:8:\"CarrQOSR\";}}');
INSERT INTO `user_type_permissions` VALUES ('9', 'CUSTOMERADMIN', 'a:1:{s:7:\"reports\";a:3:{i:0;s:10:\"fail_calls\";i:1;s:16:\"report_statement\";i:2;s:3:\"cdr\";}}');

-- ----------------------------
-- Table structure for `users`
-- ----------------------------
DROP TABLE IF EXISTS `users`;
CREATE TABLE `users` (
  `id` int(1) NOT NULL AUTO_INCREMENT,
  `user_id` varchar(30) NOT NULL,
  `account_id` varchar(30) NOT NULL,
  `gcode` varchar(300) DEFAULT NULL,
  `user_type` varchar(30) NOT NULL,
  `username` varchar(30) NOT NULL,
  `secret` varchar(30) NOT NULL,
  `name` varchar(100) NOT NULL,
  `emailaddress` varchar(100) NOT NULL,
  `phone` varchar(50) NOT NULL,
  `address` varchar(256) NOT NULL,
  `country_id` smallint(6) NOT NULL,
  `status_id` int(11) NOT NULL DEFAULT '1',
  `create_dt` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `create_by` varchar(30) NOT NULL,
  `update_dt` datetime DEFAULT NULL,
  `update_by` varchar(30) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `user_id` (`user_id`) USING BTREE,
  KEY `account_id` (`account_id`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=257 DEFAULT CHARSET=latin1;

-- ----------------------------
-- Records of users
-- ----------------------------
INSERT INTO `users` VALUES ('2', 'ADMIN', 'SYSTEM', null, 'ADMIN', 'admin', 'admin', 'Open Voips', 'openvoips@gmail.com', '919949800228', 'India', '100', '1', '2021-01-22 20:26:31', '', '0000-00-00 00:00:00', 'ADMIN');
INSERT INTO `users` VALUES ('253', 'UR000253446', 'STR100000', null, 'RESELLERADMIN', 'testuserqqq', '1q2w#E$RRRR', 'test reseller', 'tesrrrrrgr@mail.com', '', '', '0', '1', '2021-07-31 12:32:11', '', null, '');
INSERT INTO `users` VALUES ('256', 'UC000256589', 'STC300000', null, 'CUSTOMERADMIN', 'kanand81', 'Kanand@81', 'Anand kumar', 'kanand81@gmail.com', '', '', '0', '1', '2021-07-31 15:55:12', '', null, '');

-- ----------------------------
-- Table structure for `version`
-- ----------------------------
DROP TABLE IF EXISTS `version`;
CREATE TABLE `version` (
  `table_name` varchar(32) NOT NULL,
  `table_version` int(10) unsigned NOT NULL DEFAULT '0',
  UNIQUE KEY `table_name_idx` (`table_name`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- ----------------------------
-- Records of version
-- ----------------------------
INSERT INTO `version` VALUES ('customer_sip_account', '7');

-- ----------------------------
-- Table structure for `voicemail`
-- ----------------------------
DROP TABLE IF EXISTS `voicemail`;
CREATE TABLE `voicemail` (
  `vm_id` int(11) NOT NULL AUTO_INCREMENT,
  `account_id` varchar(30) NOT NULL,
  `vm_name` varchar(30) NOT NULL,
  `vm_no` int(11) NOT NULL,
  `mailbox` varchar(30) DEFAULT NULL,
  `vm_password` varchar(30) DEFAULT NULL,
  `no_of_vm` int(11) DEFAULT NULL,
  `no_of_vm_len` int(11) DEFAULT NULL,
  `send_email` enum('0','1') DEFAULT '0',
  `email_address` text,
  `email_attach_file` enum('0','1') DEFAULT '0',
  `greetings_id` varchar(30) DEFAULT NULL,
  `group_id` varchar(30) NOT NULL,
  `status_id` enum('1','0') DEFAULT '1',
  `created_by` varchar(30) NOT NULL,
  `created_by_account_id` varchar(30) NOT NULL,
  `updated_by` varchar(30) NOT NULL,
  `created_dt` datetime NOT NULL,
  `updated_dt` datetime DEFAULT NULL,
  PRIMARY KEY (`vm_id`),
  UNIQUE KEY `mailbox` (`mailbox`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- ----------------------------
-- Records of voicemail
-- ----------------------------

-- ----------------------------
-- Table structure for `voicemail_msgs`
-- ----------------------------
DROP TABLE IF EXISTS `voicemail_msgs`;
CREATE TABLE `voicemail_msgs` (
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
  KEY `voicemail_msgs_idx1` (`created_epoch`) USING BTREE,
  KEY `voicemail_msgs_idx2` (`username`) USING BTREE,
  KEY `voicemail_msgs_idx3` (`domain`) USING BTREE,
  KEY `voicemail_msgs_idx4` (`uuid`) USING BTREE,
  KEY `voicemail_msgs_idx5` (`in_folder`) USING BTREE,
  KEY `voicemail_msgs_idx6` (`read_flags`) USING BTREE,
  KEY `voicemail_msgs_idx7` (`forwarded_by`) USING BTREE,
  KEY `voicemail_msgs_idx8` (`read_epoch`) USING BTREE,
  KEY `voicemail_msgs_idx9` (`flags`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- ----------------------------
-- Records of voicemail_msgs
-- ----------------------------

-- ----------------------------
-- Table structure for `voicemail_prefs`
-- ----------------------------
DROP TABLE IF EXISTS `voicemail_prefs`;
CREATE TABLE `voicemail_prefs` (
  `username` varchar(255) DEFAULT NULL,
  `domain` varchar(255) DEFAULT NULL,
  `name_path` varchar(255) DEFAULT NULL,
  `greeting_path` varchar(255) DEFAULT NULL,
  `password` varchar(255) DEFAULT NULL,
  KEY `voicemail_prefs_idx1` (`username`) USING BTREE,
  KEY `voicemail_prefs_idx2` (`domain`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- ----------------------------
-- Records of voicemail_prefs
-- ----------------------------
