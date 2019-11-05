/*
Source Server         : localhost
Source Server Version : 50560
Source Host           : localhost
Source Database       : switch
Date: 2019-08-20 09:58:53
*/

SET FOREIGN_KEY_CHECKS=0;

-- ----------------------------
-- Table structure for `account`
-- ----------------------------
DROP TABLE IF EXISTS `account`;
CREATE TABLE `account` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `account_id` varchar(30) DEFAULT NULL,
  `account_status` enum('1','0','-1','-2','-3','-4') DEFAULT '-1' COMMENT '-1=not approved, 0=inactive, -2=suspended,-3=Stop Billing; -4=Account Closed',
  `account_type` enum('CUSTOMER','RESELLER') DEFAULT 'CUSTOMER',
  `account_level` smallint(6) unsigned DEFAULT NULL COMMENT '0',
  `parent_account_id` varchar(30) DEFAULT '',
  `dp` tinyint(1) DEFAULT '4',
  `tariff_id` varchar(30) DEFAULT '0',
  `account_cc` int(11) DEFAULT '10',
  `account_cps` int(11) DEFAULT '1',
  `tax_number` varchar(30) DEFAULT NULL,
  `tax1` double(6,2) DEFAULT '0.00',
  `tax2` double(6,2) DEFAULT '0.00',
  `tax3` double(6,2) DEFAULT '0.00',
  `tax_type` enum('inclusive','exclusive') DEFAULT 'exclusive',
  `vat_flag` enum('NONE','TAX','VAT') NOT NULL DEFAULT 'NONE',
  `currency_id` int(11) DEFAULT '1',
  `cli_check` enum('1','0') DEFAULT '1',
  `dialpattern_check` enum('1','0') DEFAULT '1',
  `llr_check` enum('1','0') DEFAULT '1',
  `account_codecs` varchar(150) DEFAULT 'G729,PCMU,PCMA,G722',
  `media_transcoding` enum('1','0') DEFAULT '0',
  `media_rtpproxy` enum('1','0') DEFAULT '0',
  `force_dst_src_cli_prefix` enum('1','0') DEFAULT '0',
  `codecs_force` enum('0','1') DEFAULT '1',
  `max_callduration` int(11) DEFAULT '120',
  `round_logic` enum('CEIL','ROUND') DEFAULT 'CEIL',
  `update_dt` timestamp NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `key_account_id` (`account_id`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=latin1;

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

DROP TABLE IF EXISTS `account_payment_credentials`;
CREATE TABLE `account_payment_credentials` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `account_id` varchar(30) NOT NULL,
  `payment_method` enum('paypal-client','paypal-sdk','ccavenue','secure-trading') NOT NULL,
  `credentials` text NOT NULL,
  `status` enum('Y','N') NOT NULL DEFAULT 'Y',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=latin1;
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
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=latin1;

-- ----------------------------
-- Table structure for `activity_log`
-- ----------------------------
DROP TABLE IF EXISTS `activity_log`;
CREATE TABLE `activity_log` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `activity_id` int(11) NOT NULL,
  `activity_type` varchar(20) NOT NULL,
  `sql_table` varchar(50) NOT NULL,
  `sql_key` varchar(255) DEFAULT NULL,
  `sql_query` text NOT NULL,
  `account_id` varchar(30) NOT NULL,
  `dt_created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=latin1;

-- ----------------------------
-- Table structure for `autosystem_report_tmp`
-- ----------------------------
DROP TABLE IF EXISTS `autosystem_report_tmp`;
CREATE TABLE `autosystem_report_tmp` (
  `autosystem_report_id` int(11) NOT NULL AUTO_INCREMENT,
  `report_type` varchar(30) DEFAULT NULL,
  `from_email` varchar(50) DEFAULT NULL,
  `from_name` varchar(50) DEFAULT NULL,
  `to_emails` varchar(500) DEFAULT NULL,
  `subject` varchar(150) DEFAULT NULL,
  `body_text` text,
  `bcc` varchar(500) DEFAULT NULL,
  PRIMARY KEY (`autosystem_report_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- ----------------------------
-- Records of autosystem_report_tmp
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
  PRIMARY KEY (`id`),
  UNIQUE KEY `key_carrier_id_name` (`carrier_id`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=latin1;




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
  PRIMARY KEY (`id`),
  UNIQUE KEY `carrier_callerid_key` (`carrier_id`,`maching_string`,`route`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=latin1;

-- ----------------------------
-- Table structure for `carrier_daily_usage`
-- ----------------------------
DROP TABLE IF EXISTS `carrier_daily_usage`;
CREATE TABLE `carrier_daily_usage` (
  `carrier_daily_usage_id` int(11) NOT NULL AUTO_INCREMENT,
  `carrier_account` varchar(30) DEFAULT NULL,
  `carrier_name` varchar(100) DEFAULT NULL,
  `asr` double(6,2) DEFAULT '0.00',
  `acd` int(11) DEFAULT '0',
  `answercalls` int(11) DEFAULT '0',
  `totalcalls` int(11) DEFAULT '0',
  `prefix` varchar(15) DEFAULT NULL,
  `destination` varchar(150) DEFAULT NULL,
  `carrier_currency_id` int(11) DEFAULT NULL,
  `currency_name` varchar(20) DEFAULT NULL,
  `carriercost` double(20,6) DEFAULT NULL,
  `out_minute` double(20,6) DEFAULT NULL,
  `calls_date` date DEFAULT NULL,
  `code401` int(11) DEFAULT '0',
  `code402` int(11) DEFAULT '0',
  `code403` int(11) DEFAULT '0',
  `code404` int(11) DEFAULT '0',
  `code407` int(11) DEFAULT '0',
  `code500` int(11) DEFAULT '0',
  `code503` int(11) DEFAULT '0',
  `code487` int(11) DEFAULT '0',
  `code488` int(11) DEFAULT '0',
  `code501` int(11) DEFAULT '0',
  `code483` int(11) DEFAULT '0',
  `code410` int(11) DEFAULT '0',
  `code515` int(11) DEFAULT '0',
  `code486` int(11) DEFAULT '0',
  `code480` int(11) DEFAULT '0',
  PRIMARY KEY (`carrier_daily_usage_id`),
  UNIQUE KEY `carrier_account` (`carrier_account`,`prefix`,`calls_date`,`destination`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=latin1;


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
  PRIMARY KEY (`id`),
  UNIQUE KEY `carrier_id` (`carrier_id`,`ipaddress_name`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=latin1;


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
  PRIMARY KEY (`id`),
  UNIQUE KEY `carrier_prefix_id_key` (`carrier_id`,`maching_string`,`route`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=latin1;


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
  `create_dt` timestamp NULL DEFAULT NULL,
  `update_dt` timestamp NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  `inclusive_channel` int(11) DEFAULT '1',
  `exclusive_per_channel_rental` double(12,6) DEFAULT '0.000000',
  PRIMARY KEY (`rate_id`),
  UNIQUE KEY `pt` (`ratecard_id`,`prefix`) USING BTREE,
  KEY `prefix` (`prefix`) USING BTREE,
  KEY `tariff_id` (`ratecard_id`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=latin1;


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
-- Table structure for `connected_calls`
-- ----------------------------
DROP TABLE IF EXISTS `connected_calls`;
CREATE TABLE `connected_calls` (
  `connected_call_id` int(11) NOT NULL AUTO_INCREMENT,
  `call_date` date NOT NULL,
  `db_source` varchar(1) NOT NULL,
  `carrier_id` varchar(30) NOT NULL,
  `carrier_name` varchar(50) NOT NULL,
  `client_id` varchar(30) NOT NULL,
  `account_name` varchar(50) NOT NULL,
  `account_type` varchar(20) NOT NULL,
  `reseller_id` varchar(30) NOT NULL,
  `reseller_name` varchar(50) DEFAULT NULL,
  `destination` varchar(255) NOT NULL,
  `currency_conversion_factor` decimal(7,4) NOT NULL,
  `call_count` int(11) NOT NULL,
  `call_duration` bigint(20) NOT NULL,
  `carrier_duration` bigint(20) DEFAULT '0',
  `account_cost` decimal(10,4) NOT NULL,
  `carrier_cost` decimal(10,4) NOT NULL,
  `reseller1_cost` decimal(10,4) NOT NULL,
  `reseller2_cost` decimal(10,4) NOT NULL,
  `reseller3_cost` decimal(10,4) NOT NULL,
  `acd` decimal(6,2) DEFAULT NULL,
  `carrier_callcost_total` decimal(12,6) DEFAULT NULL,
  `user_user_currency_id` int(11) DEFAULT NULL,
  `user_rate` decimal(12,6) DEFAULT '0.000000',
  `calltype` varchar(10) DEFAULT NULL,
  PRIMARY KEY (`connected_call_id`),
  UNIQUE KEY `call_date` (`call_date`,`carrier_id`,`client_id`,`destination`,`user_rate`) USING BTREE,
  KEY `connected_calls_calldate_idx` (`call_date`) USING BTREE,
  KEY `connected_calls_client_idx` (`client_id`) USING BTREE,
  KEY `vs_account_links_ind2` (`client_id`,`db_source`,`carrier_id`) USING BTREE,
  KEY `carrier_id` (`carrier_id`) USING BTREE,
  KEY `destination` (`destination`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=latin1;


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
  `modify_date` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00' ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=latin1;



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
  PRIMARY KEY (`id`),
  UNIQUE KEY `user_id_account_id` (`account_id`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=latin1;


-- ----------------------------
-- Table structure for `customer_bundle_sdr`
-- ----------------------------
DROP TABLE IF EXISTS `customer_bundle_sdr`;
CREATE TABLE `customer_bundle_sdr` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `account_id` varchar(30) DEFAULT NULL,
  `rule_type` varchar(30) DEFAULT NULL,
  `yearmonth` varchar(10) DEFAULT NULL,
  `service_number` varchar(150) DEFAULT NULL,
  `service_charges` double(12,6) DEFAULT NULL,
  `tax1` double(10,4) DEFAULT NULL,
  `tax1_cost` double(12,6) DEFAULT '0.000000',
  `tax2` double(10,4) DEFAULT NULL,
  `tax2_cost` double(12,6) DEFAULT '0.000000',
  `tax3` double(10,4) DEFAULT NULL,
  `tax3_cost` double(12,6) DEFAULT '0.000000',
  `total_tax` double(12,6) DEFAULT NULL,
  `cost` double(12,6) DEFAULT '0.000000',
  `total_cost` double(20,6) DEFAULT '0.000000',
  `detail` text,
  `otherdata` varchar(300) DEFAULT NULL,
  `sdr_consumption` double(20,6) DEFAULT NULL,
  `service_startdate` date DEFAULT NULL,
  `service_stopdate` date DEFAULT NULL,
  `action_date` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `usersdr_id` (`account_id`,`rule_type`,`yearmonth`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=latin1;


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
  PRIMARY KEY (`id`),
  UNIQUE KEY `user_callerid_key` (`account_id`,`maching_string`,`route`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=latin1;

-- ----------------------------
-- Table structure for `customer_card_details`
-- ----------------------------
DROP TABLE IF EXISTS `customer_card_details`;
CREATE TABLE `customer_card_details` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `account_id` varchar(30) NOT NULL,
  `card_name` varchar(30) NOT NULL,
  `card_data` text NOT NULL,
  `dt_updated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- ----------------------------
-- Records of customer_card_details
-- ----------------------------

-- ----------------------------
-- Table structure for `customer_daily_usages`
-- ----------------------------
DROP TABLE IF EXISTS `customer_daily_usages`;
CREATE TABLE `customer_daily_usages` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `account_id` varchar(30) DEFAULT NULL,
  `action_date` date DEFAULT NULL,
  `user_type` varchar(30) DEFAULT NULL,
  `user_level` varchar(30) DEFAULT NULL,
  `parent_account_id` varchar(30) DEFAULT NULL,
  `customer_currency_id_name` varchar(30) DEFAULT NULL,
  `customer_currency_id` varchar(30) DEFAULT NULL,
  `account_type` varchar(30) DEFAULT NULL,
  `company_name` varchar(150) DEFAULT NULL,
  `account_manager` varchar(150) DEFAULT NULL,
  `introducer` int(11) DEFAULT '0',
  `managername` varchar(150) DEFAULT NULL,
  `totalcalls` double(16,6) DEFAULT '0.000000',
  `answeredcalls` double(16,6) DEFAULT '0.000000',
  `account_duration` double(16,6) DEFAULT '0.000000',
  `account_cost` double(16,6) DEFAULT '0.000000',
  `carrier_callcost` double(16,6) DEFAULT '0.000000',
  `carrier_duration` double(16,6) DEFAULT '0.000000',
  `asr` double(16,6) DEFAULT '0.000000',
  `acd` double(16,6) DEFAULT '0.000000',
  `asr_in` double(16,6) DEFAULT '0.000000',
  `acd_in` double(16,6) DEFAULT '0.000000',
  `totalcalls_in` double(16,6) DEFAULT '0.000000',
  `answeredcalls_in` double(16,6) DEFAULT '0.000000',
  `account_duration_in` double(16,6) DEFAULT '0.000000',
  `account_cost_in` double(16,6) DEFAULT '0.000000',
  `carrier_callcost_in` double(16,6) DEFAULT '0.000000',
  `carrier_duration_in` double(16,6) DEFAULT '0.000000',
  `payment` double(16,6) DEFAULT '0.000000',
  `credit` double(16,6) DEFAULT '0.000000',
  `netoff` double(16,6) DEFAULT '0.000000',
  `testbalance` double(16,6) DEFAULT '0.000000',
  `balancetransfer` double(16,6) DEFAULT '0.000000',
  `balancetransfer_remove` double(16,6) DEFAULT '0.000000',
  `hlrcost_net` double(16,6) DEFAULT '0.000000',
  `hlrcost_gross` double(16,6) DEFAULT '0.000000',
  `hlrcost_net_carrier` double(16,6) DEFAULT '0.000000',
  `hlrcost_gross_carrier` double(16,6) DEFAULT '0.000000',
  `hlrcost_usage_carrier` double(16,6) DEFAULT '0.000000',
  `hlrcost_usage` double(16,6) DEFAULT '0.000000',
  `usaclicost_net` double(16,6) DEFAULT '0.000000',
  `usaclicost_gross` double(16,6) DEFAULT '0.000000',
  `usaclicost_net_carrier` double(16,6) DEFAULT '0.000000',
  `usaclicost_gross_carrier` double(16,6) DEFAULT '0.000000',
  `creditnotes` double(16,6) DEFAULT '0.000000',
  `callcost_net` double(16,6) DEFAULT '0.000000',
  `callcost_gross` double(16,6) DEFAULT '0.000000',
  `callcost_net_carrier` double(16,6) DEFAULT '0.000000',
  `callcost_gross_carrier` double(16,6) DEFAULT '0.000000',
  `carrier_usage` double(16,6) DEFAULT '0.000000',
  `user_usage` double(16,6) DEFAULT '0.000000',
  `callcost_net_in` double(16,6) DEFAULT '0.000000',
  `callcost_gross_in` double(16,6) DEFAULT '0.000000',
  `callcost_net_carrier_in` double(16,6) DEFAULT '0.000000',
  `callcost_gross_carrier_in` double(16,6) DEFAULT '0.000000',
  `carrier_usage_in` double(16,6) DEFAULT '0.000000',
  `user_usage_in` double(16,6) DEFAULT '0.000000',
  `debitnotes` double(16,6) DEFAULT '0.000000',
  `did_extra_channel_cost_net` double(16,6) DEFAULT '0.000000',
  `did_extra_channel_cost_gross` double(16,6) DEFAULT '0.000000',
  `did_extra_channel_cost_net_carrier` double(16,6) DEFAULT '0.000000',
  `did_extra_channel_cost_gross_carrier` double(16,6) DEFAULT '0.000000',
  `did_rental_cost_net` double(16,6) DEFAULT '0.000000',
  `did_rental_cost_gross` double(16,6) DEFAULT '0.000000',
  `did_rental_cost_net_carrier` double(16,6) DEFAULT '0.000000',
  `did_rental_cost_gross_carrier` double(16,6) DEFAULT '0.000000',
  `did_setup_cost_net` double(16,6) DEFAULT '0.000000',
  `did_setup_cost_gross` double(16,6) DEFAULT '0.000000',
  `did_setup_cost_net_carrier` double(16,6) DEFAULT '0.000000',
  `did_setup_cost_gross_carrier` double(16,6) DEFAULT '0.000000',
  `hosteddialer_cost_net` double(16,6) DEFAULT '0.000000',
  `hosteddialer_cost_gross` double(16,6) DEFAULT '0.000000',
  `hosteddialer_cost_net_carrier` double(16,6) DEFAULT '0.000000',
  `hosteddialer_cost_gross_carrier` double(16,6) DEFAULT '0.000000',
  `payment_remove` double(16,6) DEFAULT '0.000000',
  `credit_remove` double(16,6) DEFAULT '0.000000',
  `netoff_remove` double(16,6) DEFAULT '0.000000',
  `testbalance_remove` double(16,6) DEFAULT '0.000000',
  `tariff_net_cost` double(16,6) DEFAULT '0.000000',
  `tariff_gross_cost` double(16,6) DEFAULT '0.000000',
  `ukclicost_net` double(16,6) DEFAULT '0.000000',
  `ukclicost_gross` double(16,6) DEFAULT '0.000000',
  `ukclicost_net_carrier` double(16,6) DEFAULT '0.000000',
  `ulclicost_gross_carrier` double(16,6) DEFAULT '0.000000',
  `introducertax` double(16,6) DEFAULT '0.000000',
  `profit_from_outcalls_gross` double(16,6) DEFAULT '0.000000',
  `profit_from_incalls_gross` double(16,6) DEFAULT '0.000000',
  `user_tax_amount` double(16,6) DEFAULT '0.000000',
  `carrier_tax_amount` double(16,6) DEFAULT '0.000000',
  `license_fees` double(16,6) DEFAULT '0.000000',
  `profit_gross` double(16,6) DEFAULT '0.000000',
  `profit_net` double(16,6) DEFAULT '0.000000',
  `balance` double(16,6) DEFAULT '0.000000',
  `pbx_setup_cost` double(16,6) DEFAULT NULL,
  `pbx_monthly_cost` double(16,6) DEFAULT '0.000000',
  `extension_monthy_cost` double(16,6) DEFAULT '0.000000',
  `extension_setup_cost` double(16,6) DEFAULT '0.000000',
  `recording_monthly_cost` double(16,6) DEFAULT '0.000000',
  `recording_additional_cost` double(16,6) DEFAULT '0.000000',
  `ivr_setup_cost` double(16,6) DEFAULT '0.000000',
  `ivr_monthly_cost` double(16,6) DEFAULT '0.000000',
  `group_setup_cost` double(16,6) DEFAULT '0.000000',
  `group_monthly_cost` double(16,6) DEFAULT '0.000000',
  `no_ivr` int(11) DEFAULT '0',
  `no_extensions` int(11) DEFAULT '0',
  `no_group` int(11) DEFAULT '0',
  `no_dids` int(11) DEFAULT '0',
  `trunk_monthly_cost` double(16,6) DEFAULT '0.000000',
  `trunk_setup_cost` double(16,6) DEFAULT '0.000000',
  `no_trunk` int(11) DEFAULT '0',
  `button_setup_cost` double(16,6) DEFAULT '0.000000',
  `button_monthly_cost` double(16,6) DEFAULT '0.000000',
  `no_button` int(11) DEFAULT '0',
  `app_setup_cost` double(16,6) DEFAULT '0.000000',
  `app_monthly_cost` double(16,6) DEFAULT '0.000000',
  `no_app` int(11) DEFAULT '0',
  `ill_installation_cost` double(16,6) DEFAULT '0.000000',
  `ill_montholy_cost` double(16,6) DEFAULT '0.000000',
  `user_currency_id_name` varchar(10) DEFAULT NULL,
  `opening_balance` double(16,6) DEFAULT '0.000000',
  `entry_type` int(11) DEFAULT '1',
  PRIMARY KEY (`id`),
  UNIQUE KEY `account_id` (`account_id`,`action_date`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=latin1;

-- ----------------------------
-- Table structure for `customer_default_permissions`
-- ----------------------------
DROP TABLE IF EXISTS `customer_default_permissions`;
CREATE TABLE `customer_default_permissions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `account_type` enum('RESELLER','CUSTOMER','ADMIN','SUBADMIN','ACCOUNTS') NOT NULL,
  `permissions` text NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=latin1;

-- ----------------------------
-- Records of customer_default_permissions
-- ----------------------------
INSERT INTO `customer_default_permissions` VALUES ('1', 'RESELLER', 'a:6:{s:8:\"reseller\";a:4:{i:0;s:4:\"view\";i:1;s:3:\"add\";i:2;s:4:\"edit\";i:3;s:6:\"delete\";}s:8:\"customer\";a:4:{i:0;s:4:\"view\";i:1;s:3:\"add\";i:2;s:4:\"edit\";i:3;s:6:\"delete\";}s:8:\"ratecard\";a:5:{i:0;s:4:\"view\";i:1;s:3:\"add\";i:2;s:4:\"edit\";i:3;s:6:\"delete\";i:4;s:6:\"upload\";}s:4:\"rate\";a:4:{i:0;s:4:\"view\";i:1;s:3:\"add\";i:2;s:4:\"edit\";i:3;s:6:\"delete\";}s:6:\"tariff\";a:4:{i:0;s:4:\"view\";i:1;s:3:\"add\";i:2;s:4:\"edit\";i:3;s:6:\"delete\";}s:7:\"reports\";a:9:{i:0;s:3:\"cdr\";i:1;s:10:\"fail_calls\";i:2;s:11:\"call_report\";i:3;s:12:\"report_topup\";i:4;s:20:\"report_topup_monthly\";i:5;s:22:\"customer_topup_summery\";i:6;s:18:\"report_daily_sales\";i:7;s:26:\"report_daily_sales_monthly\";i:8;s:22:\"customer_sales_summery\";}}');
INSERT INTO `customer_default_permissions` VALUES ('2', 'SUBADMIN', 'a:10:{s:5:\"admin\";a:3:{i:0;s:4:\"view\";i:1;s:3:\"add\";i:2;s:4:\"edit\";}s:8:\"reseller\";a:4:{i:0;s:4:\"view\";i:1;s:3:\"add\";i:2;s:4:\"edit\";i:3;s:6:\"delete\";}s:7:\"carrier\";a:4:{i:0;s:4:\"view\";i:1;s:3:\"add\";i:2;s:4:\"edit\";i:3;s:6:\"delete\";}s:7:\"routing\";a:4:{i:0;s:4:\"view\";i:1;s:3:\"add\";i:2;s:4:\"edit\";i:3;s:6:\"delete\";}s:8:\"dialplan\";a:4:{i:0;s:4:\"view\";i:1;s:3:\"add\";i:2;s:4:\"edit\";i:3;s:6:\"delete\";}s:8:\"ratecard\";a:5:{i:0;s:4:\"view\";i:1;s:3:\"add\";i:2;s:4:\"edit\";i:3;s:6:\"delete\";i:4;s:6:\"upload\";}s:4:\"rate\";a:4:{i:0;s:4:\"view\";i:1;s:3:\"add\";i:2;s:4:\"edit\";i:3;s:6:\"delete\";}s:6:\"tariff\";a:4:{i:0;s:4:\"view\";i:1;s:3:\"add\";i:2;s:4:\"edit\";i:3;s:6:\"delete\";}s:7:\"service\";a:3:{i:0;s:4:\"view\";i:1;s:3:\"add\";i:2;s:4:\"edit\";}s:7:\"reports\";a:7:{i:0;s:3:\"cdr\";i:1;s:10:\"fail_calls\";i:2;s:4:\"live\";i:3;s:17:\"analytics_carrier\";i:4;s:18:\"accounting_billing\";i:5;s:7:\"summary\";i:6;s:11:\"call_report\";}}');
INSERT INTO `customer_default_permissions` VALUES ('3', 'CUSTOMER', 'a:4:{s:8:\"customer\";a:2:{i:0;s:4:\"view\";i:1;s:7:\"cliedit\";}s:7:\"routing\";a:4:{i:0;s:4:\"view\";i:1;s:3:\"add\";i:2;s:4:\"edit\";i:3;s:6:\"delete\";}s:7:\"service\";a:1:{i:0;s:4:\"view\";}s:7:\"reports\";a:6:{i:0;s:3:\"cdr\";i:1;s:10:\"fail_calls\";i:2;s:4:\"live\";i:3;s:9:\"statement\";i:4;s:9:\"myinvoice\";i:5;s:16:\"report_statement\";}}');
INSERT INTO `customer_default_permissions` VALUES ('4', 'ACCOUNTS', 'a:8:{s:8:\"reseller\";a:4:{i:0;s:4:\"view\";i:1;s:3:\"add\";i:2;s:4:\"edit\";i:3;s:6:\"delete\";}s:8:\"customer\";a:4:{i:0;s:4:\"view\";i:1;s:3:\"add\";i:2;s:4:\"edit\";i:3;s:6:\"delete\";}s:7:\"carrier\";a:1:{i:0;s:6:\"delete\";}s:8:\"ratecard\";a:5:{i:0;s:4:\"view\";i:1;s:3:\"add\";i:2;s:4:\"edit\";i:3;s:6:\"delete\";i:4;s:6:\"upload\";}s:4:\"rate\";a:4:{i:0;s:4:\"view\";i:1;s:3:\"add\";i:2;s:4:\"edit\";i:3;s:6:\"delete\";}s:6:\"tariff\";a:4:{i:0;s:4:\"view\";i:1;s:3:\"add\";i:2;s:4:\"edit\";i:3;s:6:\"delete\";}s:7:\"service\";a:4:{i:0;s:4:\"view\";i:1;s:3:\"add\";i:2;s:4:\"edit\";i:3;s:6:\"delete\";}s:7:\"reports\";a:15:{i:0;s:3:\"cdr\";i:1;s:10:\"fail_calls\";i:2;s:5:\"monin\";i:3;s:8:\"CustQOSR\";i:4;s:12:\"monitCarrier\";i:5;s:17:\"analytics_carrier\";i:6;s:18:\"accounting_billing\";i:7;s:7:\"summary\";i:8;s:11:\"call_report\";i:9;s:12:\"report_topup\";i:10;s:20:\"report_topup_monthly\";i:11;s:22:\"customer_topup_summery\";i:12;s:18:\"report_daily_sales\";i:13;s:26:\"report_daily_sales_monthly\";i:14;s:22:\"customer_sales_summery\";}}');

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
  PRIMARY KEY (`id`),
  UNIQUE KEY `user_dialplan_key` (`account_id`,`maching_string`,`route`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=latin1;

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
  PRIMARY KEY (`id`),
  UNIQUE KEY `user_carrier_dialplan_key` (`account_id`,`maching_string`) USING BTREE,
  KEY `maching_string_key` (`maching_string`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=latin1;

-- ----------------------------
-- Table structure for `customer_ips`
-- ----------------------------
DROP TABLE IF EXISTS `customer_ips`;
CREATE TABLE `customer_ips` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `account_id` varchar(30) DEFAULT '0',
  `ipaddress` varchar(30) DEFAULT NULL,
  `ip_status` enum('1','0') DEFAULT '1',
  `ip_cc` int(11) DEFAULT '10',
  `ip_cps` int(11) DEFAULT '1',
  `description` varchar(30) DEFAULT NULL,
  `dialprefix` varchar(30) DEFAULT NULL,
  `ipauthfrom` enum('SRC','FROM','NO') DEFAULT 'NO',
  PRIMARY KEY (`id`),
  UNIQUE KEY `user_ips_ipaddress_key` (`ipaddress`,`dialprefix`) USING BTREE,
  KEY `account_id` (`account_id`) USING BTREE,
  KEY `ipaddress` (`ipaddress`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=latin1;


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
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=latin1;

-- ----------------------------
-- Table structure for `customer_payment_credentials`
-- ----------------------------
DROP TABLE IF EXISTS `customer_payment_credentials`;
CREATE TABLE `customer_payment_credentials` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `account_id` varchar(30) NOT NULL,
  `payment_method` enum('paypal-client','paypal-sdk','ccavenue','secure-trading') NOT NULL,
  `credentials` text NOT NULL,
  `status` enum('Y','N') NOT NULL DEFAULT 'Y',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- ----------------------------
-- Records of customer_payment_credentials
-- ----------------------------

-- ----------------------------
-- Table structure for `customer_permissions`
-- ----------------------------
DROP TABLE IF EXISTS `customer_permissions`;
CREATE TABLE `customer_permissions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `account_id` varchar(30) NOT NULL,
  `permissions` text NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `account_id` (`account_id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- ----------------------------
-- Records of customer_permissions
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
  `create_dt` timestamp NULL DEFAULT NULL,
  `update_dt` timestamp NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  `inclusive_channel` int(11) DEFAULT '1',
  `exclusive_per_channel_rental` double(12,6) DEFAULT '0.000000',
  PRIMARY KEY (`rate_id`),
  UNIQUE KEY `pt` (`ratecard_id`,`prefix`) USING BTREE,
  KEY `prefix` (`prefix`) USING BTREE,
  KEY `tariff_id` (`ratecard_id`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=743 DEFAULT CHARSET=latin1;


-- ----------------------------
-- Table structure for `customer_sdr`
-- ----------------------------
DROP TABLE IF EXISTS `customer_sdr`;
CREATE TABLE `customer_sdr` (
  `user_sdr_id` int(11) NOT NULL AUTO_INCREMENT,
  `manualentry` enum('0','1') DEFAULT '0',
  `account_id` varchar(30) DEFAULT NULL,
  `rule_type` varchar(30) DEFAULT NULL,
  `yearmonth` varchar(10) DEFAULT NULL,
  `action_date` datetime DEFAULT NULL,
  `service_number` varchar(150) DEFAULT NULL,
  `service_charges` double(12,6) DEFAULT NULL,
  `tax1` double(10,4) DEFAULT NULL,
  `tax1_cost` double(12,6) DEFAULT '0.000000',
  `tax2` double(10,4) DEFAULT NULL,
  `tax2_cost` double(12,6) DEFAULT '0.000000',
  `tax3` double(10,4) DEFAULT NULL,
  `tax3_cost` double(12,6) DEFAULT '0.000000',
  `total_tax` double(12,6) DEFAULT NULL,
  `cost` double(12,6) DEFAULT '0.000000',
  `total_cost` double(20,6) DEFAULT '0.000000',
  `detail` text,
  `otherdata` varchar(300) DEFAULT NULL,
  `sdr_consumption` double(20,6) DEFAULT NULL,
  `service_startdate` date DEFAULT NULL,
  `service_stopdate` date DEFAULT NULL,
  `seller_tax1` double(12,6) DEFAULT '0.000000',
  `seller_tax2` double(12,6) DEFAULT '0.000000',
  `seller_tax3` double(12,6) DEFAULT '0.000000',
  `seller_tax1_cost` double(12,6) DEFAULT '0.000000',
  `seller_tax2_cost` double(12,6) DEFAULT '0.000000',
  `seller_tax3_cost` double(12,6) DEFAULT '0.000000',
  `seller_cost` double(12,6) DEFAULT '0.000000',
  `total_seller_cost` double(12,6) DEFAULT '0.000000',
  `carrier_tax1` double(12,6) DEFAULT '0.000000',
  `carrier_tax2` double(12,6) DEFAULT '0.000000',
  `carrier_tax3` double(12,6) DEFAULT '0.000000',
  `carrier_tax1_cost` double(12,6) DEFAULT '0.000000',
  `carrier_tax2_cost` double(12,6) DEFAULT '0.000000',
  `carrier_tax3_cost` double(12,6) DEFAULT '0.000000',
  `carrier_cost` double(12,6) DEFAULT '0.000000',
  `total_carrier_cost` double(12,6) DEFAULT '0.000000',
  `user_usage` double(15,2) DEFAULT '0.00',
  `seller_usage` double(15,2) DEFAULT '0.00',
  `carrier_usage` double(15,2) DEFAULT '0.00',
  `actiondate` date DEFAULT NULL,
  PRIMARY KEY (`user_sdr_id`),
  KEY `usersdr_id` (`account_id`,`rule_type`,`yearmonth`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=latin1;

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
  `voicemail` enum('1','0') DEFAULT '0',
  `voicemail_email` varchar(30) DEFAULT NULL,
  `display_name` varchar(30) DEFAULT NULL,
  `caller_id` varchar(150) DEFAULT NULL,
  `cli_prefer` enum('rpid','pid','no') DEFAULT 'rpid',
  `codecs` varchar(50) DEFAULT 'G729,PCMU,PCMA',
  `callingcard_pin` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`) USING BTREE,
  UNIQUE KEY `account_id_2` (`account_id`,`extension_no`) USING BTREE,
  UNIQUE KEY `callingcard_pin` (`callingcard_pin`) USING BTREE,
  KEY `account_id` (`account_id`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=latin1;


-- ----------------------------
-- Table structure for `customer_type_permissions`
-- ----------------------------
DROP TABLE IF EXISTS `customer_type_permissions`;
CREATE TABLE `customer_type_permissions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_type` enum('RESELLER','CUSTOMER','ADMIN','SUBADMIN','NOC','CARRIER','ACCOUNTMANAGER','CREDITCONTROL','SALESMANAGER') NOT NULL,
  `permissions` text NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=latin1;

-- ----------------------------
-- Records of customer_type_permissions
-- ----------------------------
INSERT INTO `customer_type_permissions` VALUES ('1', 'ACCOUNTMANAGER', 'a:10:{s:8:\"reseller\";a:4:{i:0;s:4:\"view\";i:1;s:3:\"add\";i:2;s:4:\"edit\";i:3;s:6:\"delete\";}s:7:\"enduser\";a:3:{i:0;s:4:\"view\";i:1;s:3:\"add\";i:2;s:4:\"edit\";}s:7:\"carrier\";a:1:{i:0;s:4:\"view\";}s:7:\"routing\";a:2:{i:0;s:4:\"view\";i:1;s:3:\"add\";}s:8:\"dialplan\";a:1:{i:0;s:4:\"view\";}s:8:\"ratecard\";a:1:{i:0;s:4:\"view\";}s:4:\"rate\";a:1:{i:0;s:4:\"view\";}s:6:\"tariff\";a:1:{i:0;s:4:\"view\";}s:7:\"service\";a:4:{i:0;s:4:\"view\";i:1;s:3:\"add\";i:2;s:4:\"edit\";i:3;s:6:\"delete\";}s:7:\"reports\";a:9:{i:0;s:3:\"cdr\";i:1;s:10:\"fail_calls\";i:2;s:4:\"live\";i:3;s:16:\"analytics_system\";i:4;s:18:\"analytics_customer\";i:5;s:17:\"analytics_carrier\";i:6;s:18:\"accounting_billing\";i:7;s:7:\"summary\";i:8;s:11:\"call_report\";}}');
INSERT INTO `customer_type_permissions` VALUES ('2', 'RESELLER', 'a:6:{s:8:\"reseller\";a:4:{i:0;s:4:\"view\";i:1;s:3:\"add\";i:2;s:4:\"edit\";i:3;s:6:\"delete\";}s:7:\"enduser\";a:4:{i:0;s:4:\"view\";i:1;s:3:\"add\";i:2;s:4:\"edit\";i:3;s:6:\"delete\";}s:8:\"ratecard\";a:5:{i:0;s:4:\"view\";i:1;s:3:\"add\";i:2;s:4:\"edit\";i:3;s:6:\"delete\";i:4;s:6:\"upload\";}s:4:\"rate\";a:4:{i:0;s:4:\"view\";i:1;s:3:\"add\";i:2;s:4:\"edit\";i:3;s:6:\"delete\";}s:6:\"tariff\";a:4:{i:0;s:4:\"view\";i:1;s:3:\"add\";i:2;s:4:\"edit\";i:3;s:6:\"delete\";}s:7:\"reports\";a:2:{i:0;s:3:\"cdr\";i:1;s:10:\"fail_calls\";}}');
INSERT INTO `customer_type_permissions` VALUES ('3', 'SUBADMIN', 'a:11:{s:5:\"admin\";a:3:{i:0;s:4:\"view\";i:1;s:3:\"add\";i:2;s:4:\"edit\";}s:8:\"reseller\";a:4:{i:0;s:4:\"view\";i:1;s:3:\"add\";i:2;s:4:\"edit\";i:3;s:6:\"delete\";}s:7:\"enduser\";a:4:{i:0;s:4:\"view\";i:1;s:3:\"add\";i:2;s:4:\"edit\";i:3;s:6:\"delete\";}s:7:\"carrier\";a:4:{i:0;s:4:\"view\";i:1;s:3:\"add\";i:2;s:4:\"edit\";i:3;s:6:\"delete\";}s:7:\"routing\";a:4:{i:0;s:4:\"view\";i:1;s:3:\"add\";i:2;s:4:\"edit\";i:3;s:6:\"delete\";}s:8:\"dialplan\";a:4:{i:0;s:4:\"view\";i:1;s:3:\"add\";i:2;s:4:\"edit\";i:3;s:6:\"delete\";}s:8:\"ratecard\";a:5:{i:0;s:4:\"view\";i:1;s:3:\"add\";i:2;s:4:\"edit\";i:3;s:6:\"delete\";i:4;s:6:\"upload\";}s:4:\"rate\";a:4:{i:0;s:4:\"view\";i:1;s:3:\"add\";i:2;s:4:\"edit\";i:3;s:6:\"delete\";}s:6:\"tariff\";a:4:{i:0;s:4:\"view\";i:1;s:3:\"add\";i:2;s:4:\"edit\";i:3;s:6:\"delete\";}s:7:\"service\";a:3:{i:0;s:4:\"view\";i:1;s:3:\"add\";i:2;s:4:\"edit\";}s:7:\"reports\";a:9:{i:0;s:3:\"cdr\";i:1;s:10:\"fail_calls\";i:2;s:4:\"live\";i:3;s:16:\"analytics_system\";i:4;s:18:\"analytics_customer\";i:5;s:17:\"analytics_carrier\";i:6;s:18:\"accounting_billing\";i:7;s:7:\"summary\";i:8;s:11:\"call_report\";}}');
INSERT INTO `customer_type_permissions` VALUES ('4', 'NOC', 'a:11:{s:5:\"admin\";a:3:{i:0;s:4:\"view\";i:1;s:3:\"add\";i:2;s:4:\"edit\";}s:8:\"reseller\";a:3:{i:0;s:4:\"view\";i:1;s:3:\"add\";i:2;s:4:\"edit\";}s:7:\"enduser\";a:3:{i:0;s:4:\"view\";i:1;s:3:\"add\";i:2;s:4:\"edit\";}s:7:\"carrier\";a:3:{i:0;s:4:\"view\";i:1;s:3:\"add\";i:2;s:4:\"edit\";}s:7:\"routing\";a:4:{i:0;s:4:\"view\";i:1;s:3:\"add\";i:2;s:4:\"edit\";i:3;s:6:\"delete\";}s:8:\"dialplan\";a:4:{i:0;s:4:\"view\";i:1;s:3:\"add\";i:2;s:4:\"edit\";i:3;s:6:\"delete\";}s:8:\"ratecard\";a:5:{i:0;s:4:\"view\";i:1;s:3:\"add\";i:2;s:4:\"edit\";i:3;s:6:\"delete\";i:4;s:6:\"upload\";}s:4:\"rate\";a:4:{i:0;s:4:\"view\";i:1;s:3:\"add\";i:2;s:4:\"edit\";i:3;s:6:\"delete\";}s:6:\"tariff\";a:4:{i:0;s:4:\"view\";i:1;s:3:\"add\";i:2;s:4:\"edit\";i:3;s:6:\"delete\";}s:7:\"service\";a:4:{i:0;s:4:\"view\";i:1;s:3:\"add\";i:2;s:4:\"edit\";i:3;s:6:\"delete\";}s:7:\"reports\";a:9:{i:0;s:3:\"cdr\";i:1;s:10:\"fail_calls\";i:2;s:4:\"live\";i:3;s:16:\"analytics_system\";i:4;s:18:\"analytics_customer\";i:5;s:17:\"analytics_carrier\";i:6;s:18:\"accounting_billing\";i:7;s:7:\"summary\";i:8;s:11:\"call_report\";}}');
INSERT INTO `customer_type_permissions` VALUES ('5', 'CUSTOMER', 'a:1:{s:7:\"reports\";a:1:{i:0;s:3:\"cdr\";}}');
INSERT INTO `customer_type_permissions` VALUES ('6', 'CREDITCONTROL', 'a:7:{s:8:\"reseller\";a:2:{i:0;s:4:\"view\";i:1;s:4:\"edit\";}s:7:\"enduser\";a:2:{i:0;s:4:\"view\";i:1;s:4:\"edit\";}s:8:\"ratecard\";a:2:{i:0;s:4:\"view\";i:1;s:4:\"edit\";}s:4:\"rate\";a:2:{i:0;s:4:\"view\";i:1;s:4:\"edit\";}s:6:\"tariff\";a:1:{i:0;s:4:\"view\";}s:7:\"service\";a:3:{i:0;s:4:\"view\";i:1;s:4:\"edit\";i:2;s:6:\"delete\";}s:7:\"reports\";a:2:{i:0;s:18:\"analytics_customer\";i:1;s:17:\"analytics_carrier\";}}');
INSERT INTO `customer_type_permissions` VALUES ('7', 'SALESMANAGER', 'a:5:{s:5:\"admin\";a:1:{i:0;s:4:\"view\";}s:7:\"enduser\";a:2:{i:0;s:4:\"view\";i:1;s:4:\"edit\";}s:4:\"rate\";a:1:{i:0;s:4:\"view\";}s:7:\"service\";a:1:{i:0;s:4:\"view\";}s:7:\"reports\";a:9:{i:0;s:3:\"cdr\";i:1;s:10:\"fail_calls\";i:2;s:4:\"live\";i:3;s:16:\"analytics_system\";i:4;s:18:\"analytics_customer\";i:5;s:17:\"analytics_carrier\";i:6;s:18:\"accounting_billing\";i:7;s:7:\"summary\";i:8;s:11:\"call_report\";}}');
INSERT INTO `customer_type_permissions` VALUES ('8', 'CARRIER', 'a:0:{}');

-- ----------------------------
-- Table structure for `customers`
-- ----------------------------
DROP TABLE IF EXISTS `customers`;
CREATE TABLE `customers` (
  `customer_id` int(11) NOT NULL AUTO_INCREMENT,
  `account_id` varchar(50) DEFAULT NULL,
  `company_name` varchar(50) NOT NULL,
  `name` varchar(30) DEFAULT NULL,
  `address` text,
  `country_id` int(11) DEFAULT NULL,
  `state_code_id` mediumint(9) DEFAULT NULL,
  `phone` varchar(30) DEFAULT NULL,
  `emailaddress` varchar(50) DEFAULT NULL,
  `account_type` varchar(30) DEFAULT NULL,
  `state` varchar(30) DEFAULT NULL,
  `state_code` int(11) DEFAULT NULL,
  `billing_type` enum('prepaid','postpaid','netoff') NOT NULL DEFAULT 'prepaid',
  `billing_cycle` enum('weekly','monthly') NOT NULL DEFAULT 'monthly',
  `payment_terms` int(11) NOT NULL DEFAULT '30',
  `next_billing_date` date DEFAULT NULL,
  `pincode` varchar(15) DEFAULT NULL,
  `account_status` enum('1','0','-1','-2') DEFAULT '1',
  `current_status` enum('LIVE','DEMO') DEFAULT 'LIVE',
  `create_dt` datetime DEFAULT NULL,
  `update_dt` timestamp NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  `created_by` varchar(30) DEFAULT NULL,
  PRIMARY KEY (`customer_id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=latin1;

-- ----------------------------
-- Records of customers
-- ----------------------------
INSERT INTO `customers` VALUES ('1', 'ADSW000001', '', 'Admin', 'Kolkata', '100', null, '125478963669', 'openvoips@gmail.com', 'ADMIN', null, null, 'prepaid', 'monthly', '30', null, null, '1', 'LIVE', null, '2019-05-07 13:03:31', null);

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
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=latin1;


-- ----------------------------
-- Table structure for `dialplan`
-- ----------------------------
DROP TABLE IF EXISTS `dialplan`;
CREATE TABLE `dialplan` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `dialplan_id` varchar(30) DEFAULT NULL,
  `dialplan_name` varchar(20) DEFAULT NULL,
  `dialplan_status` enum('1','0') DEFAULT '1',
  `failover_sipcause_list` varchar(300) DEFAULT 'NO_ROUTE_DESTINATION,CHANNEL_UNACCEPTABLE,410,483,503,488,501,504,401,402,403,404',
  `dialplan_description` varchar(50) DEFAULT NULL,
  `create_dt` timestamp NULL DEFAULT NULL,
  `update_dt` timestamp NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `dialplan_id_name` (`dialplan_id`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=latin1;


-- ----------------------------
-- Table structure for `dialplan_prefix_list`
-- ----------------------------
DROP TABLE IF EXISTS `dialplan_prefix_list`;
CREATE TABLE `dialplan_prefix_list` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
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
  `create_dt` timestamp NULL DEFAULT NULL,
  `update_dt` timestamp NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `dialplan_list_name` (`dial_prefix`,`carrier_id`,`dialplan_id`) USING BTREE,
  KEY `dialplan_id_name` (`dialplan_id`) USING BTREE,
  KEY `dial_prefix` (`dial_prefix`) USING BTREE,
  KEY `route_status` (`route_status`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=latin7;



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
  PRIMARY KEY (`did_id`),
  UNIQUE KEY `did_number` (`did_number`) USING BTREE,
  UNIQUE KEY `did_number_2` (`did_number`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=latin1;

-- ----------------------------
-- Table structure for `did_dst`
-- ----------------------------
DROP TABLE IF EXISTS `did_dst`;
CREATE TABLE `did_dst` (
  `did_dst_id` int(11) NOT NULL AUTO_INCREMENT,
  `did_number` varchar(30) DEFAULT NULL,
  `account_id` varchar(30) DEFAULT NULL,
  `dst_type` enum('IP','USER','PSTN') DEFAULT 'USER',
  `dst_destination` varchar(30) DEFAULT NULL,
  `create_date` datetime DEFAULT NULL,
  `update_date` timestamp NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  `dst_type2` enum('IP','USER','PSTN') DEFAULT 'IP',
  `dst_destination2` varchar(30) DEFAULT NULL,
  PRIMARY KEY (`did_dst_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- ----------------------------
-- Records of did_dst
-- ----------------------------

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
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=latin1;


-- ----------------------------
-- Table structure for `failed_calls`
-- ----------------------------
DROP TABLE IF EXISTS `failed_calls`;
CREATE TABLE `failed_calls` (
  `failed_call_id` int(11) NOT NULL AUTO_INCREMENT,
  `call_date` date NOT NULL,
  `db_source` varchar(10) NOT NULL,
  `carrier_id` varchar(30) NOT NULL,
  `carrier_name` varchar(50) DEFAULT NULL,
  `client_id` varchar(30) NOT NULL,
  `account_name` varchar(50) NOT NULL,
  `account_type` varchar(20) NOT NULL,
  `reseller_id` varchar(30) NOT NULL,
  `reseller_name` varchar(50) DEFAULT NULL,
  `destination` varchar(255) NOT NULL,
  `call_count` int(11) NOT NULL,
  `failed_200` int(11) NOT NULL,
  `failed_204` int(11) NOT NULL,
  `failed_400` int(11) NOT NULL,
  `failed_403` int(11) DEFAULT NULL,
  `failed_404` int(11) NOT NULL,
  `failed_480` int(11) NOT NULL,
  `failed_484` int(11) NOT NULL,
  `failed_486` int(11) NOT NULL,
  `failed_500` int(11) DEFAULT NULL,
  `failed_501` int(11) NOT NULL,
  `failed_503` int(11) NOT NULL,
  `failed_515` int(11) NOT NULL,
  `failed_others` int(11) NOT NULL,
  PRIMARY KEY (`failed_call_id`),
  UNIQUE KEY `call_date` (`call_date`,`carrier_id`,`client_id`,`reseller_id`,`destination`) USING BTREE,
  KEY `failed_calls_calldate_idx` (`call_date`) USING BTREE,
  KEY `failed_calls_client_idx` (`call_date`,`db_source`,`client_id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- ----------------------------
-- Records of failed_calls
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
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=latin1;

-- ----------------------------
-- Records of livecalls
-- ----------------------------

-- ----------------------------
-- Table structure for `payment_blocking`
-- ----------------------------
DROP TABLE IF EXISTS `payment_blocking`;
CREATE TABLE `payment_blocking` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `block_text` varchar(50) NOT NULL,
  `reason` text NOT NULL,
  `dt_updated` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00' ON UPDATE CURRENT_TIMESTAMP,
  `updated_by` varchar(30) NOT NULL DEFAULT 'System',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- ----------------------------
-- Records of payment_blocking
-- ----------------------------

-- ----------------------------
-- Table structure for `payment_history`
-- ----------------------------
DROP TABLE IF EXISTS `payment_history`;
CREATE TABLE `payment_history` (
  `payment_id` int(11) NOT NULL AUTO_INCREMENT,
  `account_id` varchar(30) NOT NULL,
  `payment_option_id` varchar(30) NOT NULL,
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
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;


-- ----------------------------
-- Table structure for `payment_options_del`
-- ----------------------------
DROP TABLE IF EXISTS `payment_options_del`;
CREATE TABLE `payment_options_del` (
  `option_id` int(11) NOT NULL AUTO_INCREMENT,
  `option_name` varchar(40) NOT NULL,
  PRIMARY KEY (`option_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- ----------------------------
-- Records of payment_options_del
-- ----------------------------

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
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=latin1;


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
  `created_by` varchar(30) DEFAULT NULL,
  `create_date` datetime DEFAULT NULL,
  `modify_by` varchar(30) NOT NULL,
  `modify_date` timestamp NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=latin1;


-- ----------------------------
-- Table structure for `ratecard`
-- ----------------------------
DROP TABLE IF EXISTS `ratecard`;
CREATE TABLE `ratecard` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `ratecard_id` varchar(30) DEFAULT NULL,
  `ratecard_name` varchar(30) DEFAULT NULL,
  `ratecard_type` enum('CARRIER','CUSTOMER') DEFAULT 'CARRIER',
  `created_by` varchar(30) DEFAULT NULL,
  `ratecard_currency_id` int(11) DEFAULT NULL,
  `ratecard_for` enum('INCOMING','OUTGOING') DEFAULT 'OUTGOING',
  PRIMARY KEY (`id`),
  UNIQUE KEY `ratecard_id` (`ratecard_id`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=latin1;


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
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=latin1;



-- ----------------------------
-- Table structure for `switch_daily_usage`
-- ----------------------------
DROP TABLE IF EXISTS `switch_daily_usage`;
CREATE TABLE `switch_daily_usage` (
  `daily_usagedata_id` int(11) NOT NULL AUTO_INCREMENT,
  `account_id` varchar(30) DEFAULT NULL,
  `company_name` varchar(150) DEFAULT '',
  `username` varchar(150) DEFAULT '',
  `record_date` date DEFAULT NULL,
  `currency` varchar(30) DEFAULT NULL,
  `currency_id` int(11) DEFAULT '0',
  `mins_out` int(11) DEFAULT '0',
  `calls_out` int(11) DEFAULT '0',
  `acd_out` int(11) DEFAULT '0',
  `asr_out` double(6,2) DEFAULT '0.00',
  `usercost_out` double(15,6) DEFAULT '0.000000',
  `carriercost_out` double(15,6) DEFAULT '0.000000',
  `profit_out` double(15,6) DEFAULT '0.000000',
  `calls_in` int(11) DEFAULT '0',
  `mins_in` int(11) DEFAULT '0',
  `usercost_in` double(15,6) DEFAULT '0.000000',
  `carriercost_in` double(15,6) DEFAULT '0.000000',
  `did_rental_user` double(15,6) DEFAULT '0.000000',
  `did_setup_user` double(15,6) DEFAULT '0.000000',
  `didrental_carrier` double(15,6) DEFAULT '0.000000',
  `didsetup_carrier` double(15,6) DEFAULT '0.000000',
  `other_services` double(15,6) DEFAULT '0.000000',
  `profit_in` double(15,6) DEFAULT '0.000000',
  `total_profit` double(15,6) DEFAULT '0.000000',
  `payment` double(15,6) DEFAULT '0.000000',
  `reimburse` double(15,6) DEFAULT '0.000000',
  `credit_added` double(15,6) DEFAULT '0.000000',
  `credit_remove` double(15,6) DEFAULT '0.000000',
  PRIMARY KEY (`daily_usagedata_id`),
  UNIQUE KEY `account_id` (`account_id`,`record_date`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- ----------------------------
-- Table structure for `switch_delete_history`
-- ----------------------------
DROP TABLE IF EXISTS `switch_delete_history`;
CREATE TABLE `switch_delete_history` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `delete_type` varchar(30) NOT NULL,
  `delete_status` varchar(30) NOT NULL,
  `delete_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `delete_code` varchar(30) NOT NULL,
  `deleted_by` varchar(30) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;


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
  `symbol` varchar(5) NOT NULL DEFAULT '',
  `detail_name` varchar(50) DEFAULT NULL,
  PRIMARY KEY (`currency_id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of sys_currencies
-- ----------------------------
INSERT INTO `sys_currencies` VALUES ('1', 'USD', '$', 'United States Dollar');
INSERT INTO `sys_currencies` VALUES ('2', 'GBP', '£', 'British Pound');
INSERT INTO `sys_currencies` VALUES ('3', 'INR', '₹', 'Indian Rupee');
INSERT INTO `sys_currencies` VALUES ('4', 'SGD', 'S$', 'Singapore Dollar');
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
) ENGINE=InnoDB AUTO_INCREMENT=454 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of sys_currencies_conversions
-- ----------------------------
INSERT INTO `sys_currencies_conversions` VALUES ('1', '0.7571', '2', '2019-03-26 00:05:05');
INSERT INTO `sys_currencies_conversions` VALUES ('2', '1.0000', '1', '2019-03-26 00:05:05');
INSERT INTO `sys_currencies_conversions` VALUES ('3', '0.8837', '3', '2019-03-26 00:05:05');
INSERT INTO `sys_currencies_conversions` VALUES ('4', '68.9334', '4', '2019-03-26 00:05:05');
INSERT INTO `sys_currencies_conversions` VALUES ('5', '1.3600', '5', '2019-08-16 11:43:18');

-- ----------------------------
-- Table structure for `sys_invoice_config`
-- ----------------------------
DROP TABLE IF EXISTS `sys_invoice_config`;
CREATE TABLE `sys_invoice_config` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `account_id` varchar(30) DEFAULT NULL,
  `logo` varchar(300) DEFAULT NULL,
  `company_name` varchar(300) DEFAULT NULL,
  `address` text,
  `bank_detail` text,
  `footer_text` text,
  `support_text` text,
  `create_dt` datetime DEFAULT NULL,
  `update_dt` timestamp NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `account_id` (`account_id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=latin1;


-- ----------------------------
-- Table structure for `sys_payment_credentials`
-- ----------------------------
DROP TABLE IF EXISTS `sys_payment_credentials`;
CREATE TABLE `sys_payment_credentials` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `account_id` varchar(30) NOT NULL,
  `payment_method` enum('paypal-client','paypal-sdk','ccavenue','secure-trading') NOT NULL,
  `credentials` text NOT NULL,
  `status` enum('Y','N') NOT NULL DEFAULT 'Y',
  PRIMARY KEY (`id`),
  UNIQUE KEY `account_id` (`account_id`,`payment_method`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=latin1;

-- ----------------------------
-- Records of sys_payment_credentials
-- ----------------------------

INSERT INTO `sys_payment_credentials` VALUES ('30', 'ADMIN', 'paypal-sdk', '{\"business\":\"openvoips@gmail.com\",\"pdt_identity_token\":\"sjahgsjahsjahshajshahsjkahsjajshalkshajks\"}', 'Y');

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
) ENGINE=InnoDB AUTO_INCREMENT=37 DEFAULT CHARSET=latin1;

-- ----------------------------
-- Records of sys_rule_options
-- ----------------------------
INSERT INTO `sys_rule_options` VALUES ('1', 'ADDBALANCE', 'Add Balance', 'payment', '1');
INSERT INTO `sys_rule_options` VALUES ('2', 'ADDCREDIT', 'Add Credit', 'payment', '1');
INSERT INTO `sys_rule_options` VALUES ('3', 'REMOVEBALANCE', 'Refund Balance', 'payment', '1');
INSERT INTO `sys_rule_options` VALUES ('4', 'REMOVECREDIT', 'Reduce Credit', 'payment', '1');
INSERT INTO `sys_rule_options` VALUES ('5', 'daily-balance', 'Daily Email', 'notification', '1');
INSERT INTO `sys_rule_options` VALUES ('6', 'low-balance', 'Low Balance', 'notification', '1');
INSERT INTO `sys_rule_options` VALUES ('11', 'biz-certificate-incorporation-company', 'Certificate of incorporation of company', 'kyc-biz-customer', '1');
INSERT INTO `sys_rule_options` VALUES ('12', 'biz-board-resolution-com-letterhead', 'Board resolution on company letterhead', 'kyc-biz-customer', '1');
INSERT INTO `sys_rule_options` VALUES ('13', 'biz-latest-list-directors', 'Latest List of Directors', 'kyc-biz-customer', '1');
INSERT INTO `sys_rule_options` VALUES ('14', 'biz-trade-license', 'Trade License', 'kyc-biz-customer', '1');
INSERT INTO `sys_rule_options` VALUES ('15', 'biz-pan-card', 'Pan Card', 'kyc-biz-customer', '1');
INSERT INTO `sys_rule_options` VALUES ('16', 'biz-signatory-aadhar-card', 'Authorised Signatory\'s Aadhar Card/Voter Card', 'kyc-biz-customer', '1');
INSERT INTO `sys_rule_options` VALUES ('17', 'biz-signatory-pan-card', 'Authorised Signatory\'s Pan Card', 'kyc-biz-customer', '1');
INSERT INTO `sys_rule_options` VALUES ('18', 'biz-signatory-photo', 'Authorised Signatory\'s Photo', 'kyc-biz-customer', '1');
INSERT INTO `sys_rule_options` VALUES ('19', 'biz-gstin-ertificate', 'GSTIN Certificate (Optional)', 'kyc-biz-customer', '1');
INSERT INTO `sys_rule_options` VALUES ('20', 'biz-dot-license', 'DOT License', 'kyc-biz-customer', '1');
INSERT INTO `sys_rule_options` VALUES ('21', 'resident-subscriber-aadhar', 'Subscriber\'s Aadhar card/Voter ID', 'kyc-resident-customer', '1');
INSERT INTO `sys_rule_options` VALUES ('22', 'resident-subscriber-pan-card', 'Subscriber\'s PAN Card', 'kyc-resident-customer', '1');
INSERT INTO `sys_rule_options` VALUES ('23', 'resident-subscriber-photo-order-form', 'Subscriber\'s Photo on Order form', 'kyc-resident-customer', '1');
INSERT INTO `sys_rule_options` VALUES ('24', 'resident-dot-license', 'DOT License(For International Minute Services)', 'kyc-resident-customer', '1');
INSERT INTO `sys_rule_options` VALUES ('25', 'biz-customer-order-form', 'Customer Order Form', 'kyc-biz-customer', '1');
INSERT INTO `sys_rule_options` VALUES ('26', 'biz-network-diagram', 'Network Diagram ', 'kyc-biz-customer', '1');
INSERT INTO `sys_rule_options` VALUES ('29', 'CREDITNOTES', 'Credit Notes', 'payment', '1');
INSERT INTO `sys_rule_options` VALUES ('30', 'DEBITNOTES', 'Debit Notes', 'payment', '1');


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
  PRIMARY KEY (`term_id`)
) ENGINE=InnoDB AUTO_INCREMENT=28 DEFAULT CHARSET=latin1;

-- ----------------------------
-- Records of sys_sdr_terms
-- ----------------------------
INSERT INTO `sys_sdr_terms` VALUES ('1', 'balance', 'ADDBALANCE', 'Payment Received', '+');
INSERT INTO `sys_sdr_terms` VALUES ('2', 'balance', 'ADDCREDIT', 'Credit Added', '');
INSERT INTO `sys_sdr_terms` VALUES ('3', 'balance', 'REMOVEBALANCE', 'Payment Refund', '-');
INSERT INTO `sys_sdr_terms` VALUES ('4', 'balance', 'REMOVECREDIT', 'Credit Reduced', '');
INSERT INTO `sys_sdr_terms` VALUES ('5', 'usage', 'DIDEXTRACHRENTAL', 'Extra Channels in DID/Line Charge', '-');
INSERT INTO `sys_sdr_terms` VALUES ('6', 'usage', 'DIDRENTAL', 'DID / Line Rental', '-');
INSERT INTO `sys_sdr_terms` VALUES ('7', 'usage', 'DIDSETUP', 'DID / Line Rental Setup Charge', '-');
INSERT INTO `sys_sdr_terms` VALUES ('8', 'usage', 'TARIFFCHARGES', 'Service Plan Charge', '-');
INSERT INTO `sys_sdr_terms` VALUES ('9', 'usage', 'DAILYUSAGE', 'Outbound Calls Call usages', '-');
INSERT INTO `sys_sdr_terms` VALUES ('10', 'usage', 'DAILYUSAGEIN', 'Inbound Calls Call usages', '-');
INSERT INTO `sys_sdr_terms` VALUES ('11', 'opening', 'OPENINGBALANCE', 'Opening Balance', '+');
INSERT INTO `sys_sdr_terms` VALUES ('14', 'usage', 'DIDCANCEL', 'DID / Line Cancellation', '-');
INSERT INTO `sys_sdr_terms` VALUES ('18', 'balance', 'ADDTESTBALANCE', 'Test Balance', '+');
INSERT INTO `sys_sdr_terms` VALUES ('19', 'balance', 'REMOVETESTBALANCE', 'Reduce Test Balance', '-');
INSERT INTO `sys_sdr_terms` VALUES ('20', 'balance', 'ADDNETOFFBALANCE', 'Add Net-Off transaction', '+');
INSERT INTO `sys_sdr_terms` VALUES ('21', 'balance', 'REMOVENETOFFBALANCE', 'Refund Net-Off transaction', '-');
INSERT INTO `sys_sdr_terms` VALUES ('22', 'balance', 'CREDITNOTES', 'Credit Notes', '+');
INSERT INTO `sys_sdr_terms` VALUES ('23', 'balance', 'DEBITNOTES', 'Debit Notes', '-');
-- ----------------------------
-- Table structure for `sys_sitesetup`
-- ----------------------------
DROP TABLE IF EXISTS `sys_sitesetup`;
CREATE TABLE `sys_sitesetup` (
  `sitesetup_id` int(11) NOT NULL AUTO_INCREMENT,
  `payment_invoice_counter` bigint(20) NOT NULL,
  PRIMARY KEY (`sitesetup_id`)
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=latin1;

-- ----------------------------
-- Records of sys_sitesetup
-- ----------------------------
INSERT INTO `sys_sitesetup` VALUES ('1', '165');

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
INSERT INTO `sys_states` VALUES ('9', 'UTTAR  PRADESH', '09', 'INDIA');
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
  `created_by` varchar(30) NOT NULL,
  `create_dt` timestamp NULL DEFAULT NULL,
  `update_dt` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00' ON UPDATE CURRENT_TIMESTAMP,
  `package_option` enum('1','0') DEFAULT '0',
  `monthly_charges` double DEFAULT '0',
  `bundle_option` enum('1','0') DEFAULT '0',
  `bundle1_type` enum('MINUTE','COST') DEFAULT 'MINUTE',
  `bundle1_value` double(12,6) DEFAULT NULL,
  `bundle2_type` enum('MINUTE','COST') DEFAULT 'MINUTE',
  `bundle2_value` double(12,6) DEFAULT NULL,
  `bundle3_type` enum('MINUTE','COST') DEFAULT 'MINUTE',
  `bundle3_value` double(12,6) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `tariff_id_name` (`tariff_id`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=latin1;


-- ----------------------------
-- Table structure for `tariff_bundle_prefixes`
-- ----------------------------
DROP TABLE IF EXISTS `tariff_bundle_prefixes`;
CREATE TABLE `tariff_bundle_prefixes` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `tariff_id` varchar(30) NOT NULL,
  `bundle_id` enum('1','2','3') NOT NULL DEFAULT '1',
  `prefix` varchar(30) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `tariff_id_bundle` (`tariff_id`,`bundle_id`,`prefix`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=169 DEFAULT CHARSET=latin1;


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
  PRIMARY KEY (`id`),
  KEY `ratecard_id` (`ratecard_id`) USING BTREE,
  KEY `tariff_id` (`tariff_id`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=latin1;

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
INSERT INTO `version` VALUES ('acc', '5');
INSERT INTO `version` VALUES ('acc_cdrs', '2');
INSERT INTO `version` VALUES ('active_watchers', '12');
INSERT INTO `version` VALUES ('address', '6');
INSERT INTO `version` VALUES ('aliases', '8');
INSERT INTO `version` VALUES ('carrierfailureroute', '2');
INSERT INTO `version` VALUES ('carrierroute', '3');
INSERT INTO `version` VALUES ('carrier_name', '1');
INSERT INTO `version` VALUES ('cpl', '1');
INSERT INTO `version` VALUES ('customer_sip_account', '6');
INSERT INTO `version` VALUES ('dbaliases', '1');
INSERT INTO `version` VALUES ('dialog', '7');
INSERT INTO `version` VALUES ('dialog_vars', '1');
INSERT INTO `version` VALUES ('dialplan', '2');
INSERT INTO `version` VALUES ('dispatcher', '4');
INSERT INTO `version` VALUES ('domain', '2');
INSERT INTO `version` VALUES ('domainpolicy', '2');
INSERT INTO `version` VALUES ('domain_attrs', '1');
INSERT INTO `version` VALUES ('domain_name', '1');
INSERT INTO `version` VALUES ('dr_gateways', '3');
INSERT INTO `version` VALUES ('dr_groups', '2');
INSERT INTO `version` VALUES ('dr_gw_lists', '1');
INSERT INTO `version` VALUES ('dr_rules', '3');
INSERT INTO `version` VALUES ('globalblacklist', '1');
INSERT INTO `version` VALUES ('grp', '2');
INSERT INTO `version` VALUES ('htable', '2');
INSERT INTO `version` VALUES ('imc_members', '1');
INSERT INTO `version` VALUES ('imc_rooms', '1');
INSERT INTO `version` VALUES ('lcr_gw', '3');
INSERT INTO `version` VALUES ('lcr_rule', '2');
INSERT INTO `version` VALUES ('lcr_rule_target', '1');
INSERT INTO `version` VALUES ('location', '8');
INSERT INTO `version` VALUES ('location_attrs', '1');
INSERT INTO `version` VALUES ('missed_calls', '4');
INSERT INTO `version` VALUES ('mohqcalls', '1');
INSERT INTO `version` VALUES ('mohqueues', '1');
INSERT INTO `version` VALUES ('mtree', '1');
INSERT INTO `version` VALUES ('mtrees', '2');
INSERT INTO `version` VALUES ('pdt', '1');
INSERT INTO `version` VALUES ('pl_pipes', '1');
INSERT INTO `version` VALUES ('presentity', '4');
INSERT INTO `version` VALUES ('pua', '7');
INSERT INTO `version` VALUES ('purplemap', '1');
INSERT INTO `version` VALUES ('re_grp', '1');
INSERT INTO `version` VALUES ('rls_presentity', '1');
INSERT INTO `version` VALUES ('rls_watchers', '3');
INSERT INTO `version` VALUES ('rtpproxy', '1');
INSERT INTO `version` VALUES ('sca_subscriptions', '1');
INSERT INTO `version` VALUES ('silo', '8');
INSERT INTO `version` VALUES ('sip_trace', '4');
INSERT INTO `version` VALUES ('speed_dial', '2');
INSERT INTO `version` VALUES ('subscriber', '6');
INSERT INTO `version` VALUES ('switch_user_sip', '6');
INSERT INTO `version` VALUES ('topos_d', '1');
INSERT INTO `version` VALUES ('topos_t', '1');
INSERT INTO `version` VALUES ('trusted', '6');
INSERT INTO `version` VALUES ('uacreg', '2');
INSERT INTO `version` VALUES ('uid_credentials', '7');
INSERT INTO `version` VALUES ('uid_domain', '2');
INSERT INTO `version` VALUES ('uid_domain_attrs', '1');
INSERT INTO `version` VALUES ('uid_global_attrs', '1');
INSERT INTO `version` VALUES ('uid_uri', '3');
INSERT INTO `version` VALUES ('uid_uri_attrs', '2');
INSERT INTO `version` VALUES ('uid_user_attrs', '3');
INSERT INTO `version` VALUES ('uri', '1');
INSERT INTO `version` VALUES ('userblacklist', '1');
INSERT INTO `version` VALUES ('usr_preferences', '2');
INSERT INTO `version` VALUES ('version', '1');
INSERT INTO `version` VALUES ('watchers', '3');
INSERT INTO `version` VALUES ('xcap', '4');

-- ----------------------------
-- Table structure for `web_access`
-- ----------------------------
DROP TABLE IF EXISTS `web_access`;
CREATE TABLE `web_access` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(30) DEFAULT NULL,
  `secret` varchar(30) DEFAULT NULL,
  `customer_id` int(11) DEFAULT NULL,
  `create_dt` datetime DEFAULT NULL,
  `update_dt` timestamp NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=70 DEFAULT CHARSET=latin1;

-- ----------------------------
-- Records of web_access
-- ----------------------------
INSERT INTO `web_access` VALUES ('1', 'admin', '123456', '1', '2019-05-03 20:30:42', '2019-06-28 04:22:00');
