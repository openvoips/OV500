SET FOREIGN_KEY_CHECKS=0;

-- ----------------------------
-- Table structure for `acc`
-- ----------------------------
DROP TABLE IF EXISTS `acc`;
CREATE TABLE `acc` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `method` varchar(16) NOT NULL DEFAULT '',
  `from_tag` varchar(64) NOT NULL DEFAULT '',
  `to_tag` varchar(64) NOT NULL DEFAULT '',
  `callid` varchar(255) NOT NULL DEFAULT '',
  `sip_code` varchar(3) NOT NULL DEFAULT '',
  `sip_reason` varchar(128) NOT NULL DEFAULT '',
  `time` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `callid_idx` (`callid`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- ----------------------------
-- Records of acc
-- ----------------------------

-- ----------------------------
-- Table structure for `acc_cdrs`
-- ----------------------------
DROP TABLE IF EXISTS `acc_cdrs`;
CREATE TABLE `acc_cdrs` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `start_time` datetime NOT NULL DEFAULT '2000-01-01 00:00:00',
  `end_time` datetime NOT NULL DEFAULT '2000-01-01 00:00:00',
  `duration` float(10,3) NOT NULL DEFAULT '0.000',
  PRIMARY KEY (`id`),
  KEY `start_time_idx` (`start_time`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- ----------------------------
-- Records of acc_cdrs
-- ----------------------------

-- ----------------------------
-- Table structure for `active_watchers`
-- ----------------------------
DROP TABLE IF EXISTS `active_watchers`;
CREATE TABLE `active_watchers` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `presentity_uri` varchar(128) NOT NULL,
  `watcher_username` varchar(64) NOT NULL,
  `watcher_domain` varchar(64) NOT NULL,
  `to_user` varchar(64) NOT NULL,
  `to_domain` varchar(64) NOT NULL,
  `event` varchar(64) NOT NULL DEFAULT 'presence',
  `event_id` varchar(64) DEFAULT NULL,
  `to_tag` varchar(64) NOT NULL,
  `from_tag` varchar(64) NOT NULL,
  `callid` varchar(255) NOT NULL,
  `local_cseq` int(11) NOT NULL,
  `remote_cseq` int(11) NOT NULL,
  `contact` varchar(128) NOT NULL,
  `record_route` text,
  `expires` int(11) NOT NULL,
  `status` int(11) NOT NULL DEFAULT '2',
  `reason` varchar(64) NOT NULL,
  `version` int(11) NOT NULL DEFAULT '0',
  `socket_info` varchar(64) NOT NULL,
  `local_contact` varchar(128) NOT NULL,
  `from_user` varchar(64) NOT NULL,
  `from_domain` varchar(64) NOT NULL,
  `updated` int(11) NOT NULL,
  `updated_winfo` int(11) NOT NULL,
  `flags` int(11) NOT NULL DEFAULT '0',
  `user_agent` varchar(255) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`),
  UNIQUE KEY `active_watchers_idx` (`callid`,`to_tag`,`from_tag`) USING BTREE,
  KEY `active_watchers_expires` (`expires`) USING BTREE,
  KEY `active_watchers_pres` (`presentity_uri`,`event`) USING BTREE,
  KEY `updated_idx` (`updated`) USING BTREE,
  KEY `updated_winfo_idx` (`updated_winfo`,`presentity_uri`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- ----------------------------
-- Records of active_watchers
-- ----------------------------

-- ----------------------------
-- Table structure for `address`
-- ----------------------------
DROP TABLE IF EXISTS `address`;
CREATE TABLE `address` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `grp` int(11) unsigned NOT NULL DEFAULT '1',
  `ip_addr` varchar(50) NOT NULL,
  `mask` int(11) NOT NULL DEFAULT '32',
  `port` smallint(5) unsigned NOT NULL DEFAULT '0',
  `tag` varchar(64) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- ----------------------------
-- Records of address
-- ----------------------------

-- ----------------------------
-- Table structure for `aliases`
-- ----------------------------
DROP TABLE IF EXISTS `aliases`;
CREATE TABLE `aliases` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `ruid` varchar(64) NOT NULL DEFAULT '',
  `username` varchar(64) NOT NULL DEFAULT '',
  `domain` varchar(64) DEFAULT NULL,
  `contact` varchar(255) NOT NULL DEFAULT '',
  `received` varchar(128) DEFAULT NULL,
  `path` varchar(512) DEFAULT NULL,
  `expires` datetime NOT NULL DEFAULT '2030-05-28 21:32:15',
  `q` float(10,2) NOT NULL DEFAULT '1.00',
  `callid` varchar(255) NOT NULL DEFAULT 'Default-Call-ID',
  `cseq` int(11) NOT NULL DEFAULT '1',
  `last_modified` datetime NOT NULL DEFAULT '2000-01-01 00:00:01',
  `flags` int(11) NOT NULL DEFAULT '0',
  `cflags` int(11) NOT NULL DEFAULT '0',
  `user_agent` varchar(255) NOT NULL DEFAULT '',
  `socket` varchar(64) DEFAULT NULL,
  `methods` int(11) DEFAULT NULL,
  `instance` varchar(255) DEFAULT NULL,
  `reg_id` int(11) NOT NULL DEFAULT '0',
  `server_id` int(11) NOT NULL DEFAULT '0',
  `connection_id` int(11) NOT NULL DEFAULT '0',
  `keepalive` int(11) NOT NULL DEFAULT '0',
  `partition` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `ruid_idx` (`ruid`) USING BTREE,
  KEY `account_contact_idx` (`username`,`domain`,`contact`) USING BTREE,
  KEY `expires_idx` (`expires`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- ----------------------------
-- Records of aliases
-- ----------------------------

-- ----------------------------
-- Table structure for `carrier_name`
-- ----------------------------
DROP TABLE IF EXISTS `carrier_name`;
CREATE TABLE `carrier_name` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `carrier` varchar(64) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- ----------------------------
-- Records of carrier_name
-- ----------------------------

-- ----------------------------
-- Table structure for `carrierfailureroute`
-- ----------------------------
DROP TABLE IF EXISTS `carrierfailureroute`;
CREATE TABLE `carrierfailureroute` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `carrier` int(10) unsigned NOT NULL DEFAULT '0',
  `domain` int(10) unsigned NOT NULL DEFAULT '0',
  `scan_prefix` varchar(64) NOT NULL DEFAULT '',
  `host_name` varchar(128) NOT NULL DEFAULT '',
  `reply_code` varchar(3) NOT NULL DEFAULT '',
  `flags` int(11) unsigned NOT NULL DEFAULT '0',
  `mask` int(11) unsigned NOT NULL DEFAULT '0',
  `next_domain` int(10) unsigned NOT NULL DEFAULT '0',
  `description` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- ----------------------------
-- Records of carrierfailureroute
-- ----------------------------

-- ----------------------------
-- Table structure for `carrierroute`
-- ----------------------------
DROP TABLE IF EXISTS `carrierroute`;
CREATE TABLE `carrierroute` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `carrier` int(10) unsigned NOT NULL DEFAULT '0',
  `domain` int(10) unsigned NOT NULL DEFAULT '0',
  `scan_prefix` varchar(64) NOT NULL DEFAULT '',
  `flags` int(11) unsigned NOT NULL DEFAULT '0',
  `mask` int(11) unsigned NOT NULL DEFAULT '0',
  `prob` float NOT NULL DEFAULT '0',
  `strip` int(11) unsigned NOT NULL DEFAULT '0',
  `rewrite_host` varchar(128) NOT NULL DEFAULT '',
  `rewrite_prefix` varchar(64) NOT NULL DEFAULT '',
  `rewrite_suffix` varchar(64) NOT NULL DEFAULT '',
  `description` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- ----------------------------
-- Records of carrierroute
-- ----------------------------

-- ----------------------------
-- Table structure for `cpl`
-- ----------------------------
DROP TABLE IF EXISTS `cpl`;
CREATE TABLE `cpl` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `username` varchar(64) NOT NULL,
  `domain` varchar(64) NOT NULL DEFAULT '',
  `cpl_xml` text,
  `cpl_bin` text,
  PRIMARY KEY (`id`),
  UNIQUE KEY `account_idx` (`username`,`domain`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- ----------------------------
-- Records of cpl
-- ----------------------------

-- ----------------------------
-- Table structure for `dbaliases`
-- ----------------------------
DROP TABLE IF EXISTS `dbaliases`;
CREATE TABLE `dbaliases` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `alias_username` varchar(64) NOT NULL DEFAULT '',
  `alias_domain` varchar(64) NOT NULL DEFAULT '',
  `username` varchar(64) NOT NULL DEFAULT '',
  `domain` varchar(64) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`),
  KEY `alias_user_idx` (`alias_username`) USING BTREE,
  KEY `alias_idx` (`alias_username`,`alias_domain`) USING BTREE,
  KEY `target_idx` (`username`,`domain`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- ----------------------------
-- Records of dbaliases
-- ----------------------------

-- ----------------------------
-- Table structure for `dialog`
-- ----------------------------
DROP TABLE IF EXISTS `dialog`;
CREATE TABLE `dialog` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `hash_entry` int(10) unsigned NOT NULL,
  `hash_id` int(10) unsigned NOT NULL,
  `callid` varchar(255) NOT NULL,
  `from_uri` varchar(128) NOT NULL,
  `from_tag` varchar(64) NOT NULL,
  `to_uri` varchar(128) NOT NULL,
  `to_tag` varchar(64) NOT NULL,
  `caller_cseq` varchar(20) NOT NULL,
  `callee_cseq` varchar(20) NOT NULL,
  `caller_route_set` varchar(512) DEFAULT NULL,
  `callee_route_set` varchar(512) DEFAULT NULL,
  `caller_contact` varchar(128) NOT NULL,
  `callee_contact` varchar(128) NOT NULL,
  `caller_sock` varchar(64) NOT NULL,
  `callee_sock` varchar(64) NOT NULL,
  `state` int(10) unsigned NOT NULL,
  `start_time` int(10) unsigned NOT NULL,
  `timeout` int(10) unsigned NOT NULL DEFAULT '0',
  `sflags` int(10) unsigned NOT NULL DEFAULT '0',
  `iflags` int(10) unsigned NOT NULL DEFAULT '0',
  `toroute_name` varchar(32) DEFAULT NULL,
  `req_uri` varchar(128) NOT NULL,
  `xdata` varchar(512) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `hash_idx` (`hash_entry`,`hash_id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- ----------------------------
-- Records of dialog
-- ----------------------------

-- ----------------------------
-- Table structure for `dialog_vars`
-- ----------------------------
DROP TABLE IF EXISTS `dialog_vars`;
CREATE TABLE `dialog_vars` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `hash_entry` int(10) unsigned NOT NULL,
  `hash_id` int(10) unsigned NOT NULL,
  `dialog_key` varchar(128) NOT NULL,
  `dialog_value` varchar(512) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `hash_idx` (`hash_entry`,`hash_id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- ----------------------------
-- Records of dialog_vars
-- ----------------------------

-- ----------------------------
-- Table structure for `dialplan`
-- ----------------------------
DROP TABLE IF EXISTS `dialplan`;
CREATE TABLE `dialplan` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `dpid` int(11) NOT NULL,
  `pr` int(11) NOT NULL,
  `match_op` int(11) NOT NULL,
  `match_exp` varchar(64) NOT NULL,
  `match_len` int(11) NOT NULL,
  `subst_exp` varchar(64) NOT NULL,
  `repl_exp` varchar(64) NOT NULL,
  `attrs` varchar(64) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- ----------------------------
-- Records of dialplan
-- ----------------------------

-- ----------------------------
-- Table structure for `dispatcher`
-- ----------------------------
DROP TABLE IF EXISTS `dispatcher`;
CREATE TABLE `dispatcher` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `setid` int(11) NOT NULL DEFAULT '0',
  `destination` varchar(192) NOT NULL DEFAULT '',
  `flags` int(11) NOT NULL DEFAULT '0',
  `priority` int(11) NOT NULL DEFAULT '0',
  `attrs` varchar(128) NOT NULL DEFAULT '',
  `description` varchar(64) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- ----------------------------
-- Records of dispatcher
-- ----------------------------

-- ----------------------------
-- Table structure for `domain`
-- ----------------------------
DROP TABLE IF EXISTS `domain`;
CREATE TABLE `domain` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `domain` varchar(64) NOT NULL,
  `did` varchar(64) DEFAULT NULL,
  `last_modified` datetime NOT NULL DEFAULT '2000-01-01 00:00:01',
  PRIMARY KEY (`id`),
  UNIQUE KEY `domain_idx` (`domain`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- ----------------------------
-- Records of domain
-- ----------------------------

-- ----------------------------
-- Table structure for `domain_attrs`
-- ----------------------------
DROP TABLE IF EXISTS `domain_attrs`;
CREATE TABLE `domain_attrs` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `did` varchar(64) NOT NULL,
  `name` varchar(32) NOT NULL,
  `type` int(10) unsigned NOT NULL,
  `value` varchar(255) NOT NULL,
  `last_modified` datetime NOT NULL DEFAULT '2000-01-01 00:00:01',
  PRIMARY KEY (`id`),
  UNIQUE KEY `domain_attrs_idx` (`did`,`name`,`value`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- ----------------------------
-- Records of domain_attrs
-- ----------------------------

-- ----------------------------
-- Table structure for `domain_name`
-- ----------------------------
DROP TABLE IF EXISTS `domain_name`;
CREATE TABLE `domain_name` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `domain` varchar(64) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- ----------------------------
-- Records of domain_name
-- ----------------------------

-- ----------------------------
-- Table structure for `domainpolicy`
-- ----------------------------
DROP TABLE IF EXISTS `domainpolicy`;
CREATE TABLE `domainpolicy` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `rule` varchar(255) NOT NULL,
  `type` varchar(255) NOT NULL,
  `att` varchar(255) DEFAULT NULL,
  `val` varchar(128) DEFAULT NULL,
  `description` varchar(255) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `rav_idx` (`rule`,`att`,`val`) USING BTREE,
  KEY `rule_idx` (`rule`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- ----------------------------
-- Records of domainpolicy
-- ----------------------------

-- ----------------------------
-- Table structure for `dr_gateways`
-- ----------------------------
DROP TABLE IF EXISTS `dr_gateways`;
CREATE TABLE `dr_gateways` (
  `gwid` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `type` int(11) unsigned NOT NULL DEFAULT '0',
  `address` varchar(128) NOT NULL,
  `strip` int(11) unsigned NOT NULL DEFAULT '0',
  `pri_prefix` varchar(64) DEFAULT NULL,
  `attrs` varchar(255) DEFAULT NULL,
  `description` varchar(128) NOT NULL DEFAULT '',
  PRIMARY KEY (`gwid`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- ----------------------------
-- Records of dr_gateways
-- ----------------------------

-- ----------------------------
-- Table structure for `dr_groups`
-- ----------------------------
DROP TABLE IF EXISTS `dr_groups`;
CREATE TABLE `dr_groups` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `username` varchar(64) NOT NULL,
  `domain` varchar(128) NOT NULL DEFAULT '',
  `groupid` int(11) unsigned NOT NULL DEFAULT '0',
  `description` varchar(128) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- ----------------------------
-- Records of dr_groups
-- ----------------------------

-- ----------------------------
-- Table structure for `dr_gw_lists`
-- ----------------------------
DROP TABLE IF EXISTS `dr_gw_lists`;
CREATE TABLE `dr_gw_lists` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `gwlist` varchar(255) NOT NULL,
  `description` varchar(128) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- ----------------------------
-- Records of dr_gw_lists
-- ----------------------------

-- ----------------------------
-- Table structure for `dr_rules`
-- ----------------------------
DROP TABLE IF EXISTS `dr_rules`;
CREATE TABLE `dr_rules` (
  `ruleid` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `groupid` varchar(255) NOT NULL,
  `prefix` varchar(64) NOT NULL,
  `timerec` varchar(255) NOT NULL,
  `priority` int(11) NOT NULL DEFAULT '0',
  `routeid` varchar(64) NOT NULL,
  `gwlist` varchar(255) NOT NULL,
  `description` varchar(128) NOT NULL DEFAULT '',
  PRIMARY KEY (`ruleid`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- ----------------------------
-- Records of dr_rules
-- ----------------------------

-- ----------------------------
-- Table structure for `globalblacklist`
-- ----------------------------
DROP TABLE IF EXISTS `globalblacklist`;
CREATE TABLE `globalblacklist` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `prefix` varchar(64) NOT NULL DEFAULT '',
  `whitelist` tinyint(1) NOT NULL DEFAULT '0',
  `description` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `globalblacklist_idx` (`prefix`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- ----------------------------
-- Records of globalblacklist
-- ----------------------------

-- ----------------------------
-- Table structure for `grp`
-- ----------------------------
DROP TABLE IF EXISTS `grp`;
CREATE TABLE `grp` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `username` varchar(64) NOT NULL DEFAULT '',
  `domain` varchar(64) NOT NULL DEFAULT '',
  `grp` varchar(64) NOT NULL DEFAULT '',
  `last_modified` datetime NOT NULL DEFAULT '2000-01-01 00:00:01',
  PRIMARY KEY (`id`),
  UNIQUE KEY `account_group_idx` (`username`,`domain`,`grp`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- ----------------------------
-- Records of grp
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
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- ----------------------------
-- Records of htable
-- ----------------------------

-- ----------------------------
-- Table structure for `imc_members`
-- ----------------------------
DROP TABLE IF EXISTS `imc_members`;
CREATE TABLE `imc_members` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `username` varchar(64) NOT NULL,
  `domain` varchar(64) NOT NULL,
  `room` varchar(64) NOT NULL,
  `flag` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `account_room_idx` (`username`,`domain`,`room`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- ----------------------------
-- Records of imc_members
-- ----------------------------

-- ----------------------------
-- Table structure for `imc_rooms`
-- ----------------------------
DROP TABLE IF EXISTS `imc_rooms`;
CREATE TABLE `imc_rooms` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(64) NOT NULL,
  `domain` varchar(64) NOT NULL,
  `flag` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `name_domain_idx` (`name`,`domain`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- ----------------------------
-- Records of imc_rooms
-- ----------------------------

-- ----------------------------
-- Table structure for `lcr_gw`
-- ----------------------------
DROP TABLE IF EXISTS `lcr_gw`;
CREATE TABLE `lcr_gw` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `lcr_id` smallint(5) unsigned NOT NULL,
  `gw_name` varchar(128) DEFAULT NULL,
  `ip_addr` varchar(50) DEFAULT NULL,
  `hostname` varchar(64) DEFAULT NULL,
  `port` smallint(5) unsigned DEFAULT NULL,
  `params` varchar(64) DEFAULT NULL,
  `uri_scheme` tinyint(3) unsigned DEFAULT NULL,
  `transport` tinyint(3) unsigned DEFAULT NULL,
  `strip` tinyint(3) unsigned DEFAULT NULL,
  `prefix` varchar(16) DEFAULT NULL,
  `tag` varchar(64) DEFAULT NULL,
  `flags` int(10) unsigned NOT NULL DEFAULT '0',
  `defunct` int(10) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `lcr_id_idx` (`lcr_id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- ----------------------------
-- Records of lcr_gw
-- ----------------------------

-- ----------------------------
-- Table structure for `lcr_rule`
-- ----------------------------
DROP TABLE IF EXISTS `lcr_rule`;
CREATE TABLE `lcr_rule` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `lcr_id` smallint(5) unsigned NOT NULL,
  `prefix` varchar(16) DEFAULT NULL,
  `from_uri` varchar(64) DEFAULT NULL,
  `request_uri` varchar(64) DEFAULT NULL,
  `stopper` int(10) unsigned NOT NULL DEFAULT '0',
  `enabled` int(10) unsigned NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`),
  UNIQUE KEY `lcr_id_prefix_from_uri_idx` (`lcr_id`,`prefix`,`from_uri`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- ----------------------------
-- Records of lcr_rule
-- ----------------------------

-- ----------------------------
-- Table structure for `lcr_rule_target`
-- ----------------------------
DROP TABLE IF EXISTS `lcr_rule_target`;
CREATE TABLE `lcr_rule_target` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `lcr_id` smallint(5) unsigned NOT NULL,
  `rule_id` int(10) unsigned NOT NULL,
  `gw_id` int(10) unsigned NOT NULL,
  `priority` tinyint(3) unsigned NOT NULL,
  `weight` int(10) unsigned NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`),
  UNIQUE KEY `rule_id_gw_id_idx` (`rule_id`,`gw_id`) USING BTREE,
  KEY `lcr_id_idx` (`lcr_id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- ----------------------------
-- Records of lcr_rule_target
-- ----------------------------

-- ----------------------------
-- Table structure for `location`
-- ----------------------------
DROP TABLE IF EXISTS `location`;
CREATE TABLE `location` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `ruid` varchar(64) NOT NULL DEFAULT '',
  `username` varchar(64) NOT NULL DEFAULT '',
  `domain` varchar(64) DEFAULT NULL,
  `contact` varchar(255) NOT NULL DEFAULT '',
  `received` varchar(128) DEFAULT NULL,
  `path` varchar(512) DEFAULT NULL,
  `expires` datetime NOT NULL DEFAULT '2030-05-28 21:32:15',
  `q` float(10,2) NOT NULL DEFAULT '1.00',
  `callid` varchar(255) NOT NULL DEFAULT 'Default-Call-ID',
  `cseq` int(11) NOT NULL DEFAULT '1',
  `last_modified` datetime NOT NULL DEFAULT '2000-01-01 00:00:01',
  `flags` int(11) NOT NULL DEFAULT '0',
  `cflags` int(11) NOT NULL DEFAULT '0',
  `user_agent` varchar(255) NOT NULL DEFAULT '',
  `socket` varchar(64) DEFAULT NULL,
  `methods` int(11) DEFAULT NULL,
  `instance` varchar(255) DEFAULT NULL,
  `reg_id` int(11) NOT NULL DEFAULT '0',
  `server_id` int(11) NOT NULL DEFAULT '0',
  `connection_id` int(11) NOT NULL DEFAULT '0',
  `keepalive` int(11) NOT NULL DEFAULT '0',
  `partition` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `ruid_idx` (`ruid`) USING BTREE,
  KEY `account_contact_idx` (`username`,`domain`,`contact`) USING BTREE,
  KEY `expires_idx` (`expires`) USING BTREE,
  KEY `connection_idx` (`server_id`,`connection_id`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=3391 DEFAULT CHARSET=latin1;


-- ----------------------------
-- Table structure for `location_attrs`
-- ----------------------------
DROP TABLE IF EXISTS `location_attrs`;
CREATE TABLE `location_attrs` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `ruid` varchar(64) NOT NULL DEFAULT '',
  `username` varchar(64) NOT NULL DEFAULT '',
  `domain` varchar(64) DEFAULT NULL,
  `aname` varchar(64) NOT NULL DEFAULT '',
  `atype` int(11) NOT NULL DEFAULT '0',
  `avalue` varchar(255) NOT NULL DEFAULT '',
  `last_modified` datetime NOT NULL DEFAULT '2000-01-01 00:00:01',
  PRIMARY KEY (`id`),
  KEY `account_record_idx` (`username`,`domain`,`ruid`) USING BTREE,
  KEY `last_modified_idx` (`last_modified`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- ----------------------------
-- Records of location_attrs
-- ----------------------------

-- ----------------------------
-- Table structure for `missed_calls`
-- ----------------------------
DROP TABLE IF EXISTS `missed_calls`;
CREATE TABLE `missed_calls` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `method` varchar(16) NOT NULL DEFAULT '',
  `from_tag` varchar(64) NOT NULL DEFAULT '',
  `to_tag` varchar(64) NOT NULL DEFAULT '',
  `callid` varchar(255) NOT NULL DEFAULT '',
  `sip_code` varchar(3) NOT NULL DEFAULT '',
  `sip_reason` varchar(128) NOT NULL DEFAULT '',
  `time` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `callid_idx` (`callid`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- ----------------------------
-- Records of missed_calls
-- ----------------------------

-- ----------------------------
-- Table structure for `mohqcalls`
-- ----------------------------
DROP TABLE IF EXISTS `mohqcalls`;
CREATE TABLE `mohqcalls` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `mohq_id` int(10) unsigned NOT NULL,
  `call_id` varchar(100) NOT NULL,
  `call_status` int(10) unsigned NOT NULL,
  `call_from` varchar(100) NOT NULL,
  `call_contact` varchar(100) DEFAULT NULL,
  `call_time` datetime NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `mohqcalls_idx` (`call_id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- ----------------------------
-- Records of mohqcalls
-- ----------------------------

-- ----------------------------
-- Table structure for `mohqueues`
-- ----------------------------
DROP TABLE IF EXISTS `mohqueues`;
CREATE TABLE `mohqueues` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(25) NOT NULL,
  `uri` varchar(100) NOT NULL,
  `mohdir` varchar(100) DEFAULT NULL,
  `mohfile` varchar(100) NOT NULL,
  `debug` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `mohqueue_uri_idx` (`uri`) USING BTREE,
  UNIQUE KEY `mohqueue_name_idx` (`name`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- ----------------------------
-- Records of mohqueues
-- ----------------------------

-- ----------------------------
-- Table structure for `mtree`
-- ----------------------------
DROP TABLE IF EXISTS `mtree`;
CREATE TABLE `mtree` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `tprefix` varchar(32) NOT NULL DEFAULT '',
  `tvalue` varchar(128) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`),
  UNIQUE KEY `tprefix_idx` (`tprefix`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- ----------------------------
-- Records of mtree
-- ----------------------------

-- ----------------------------
-- Table structure for `mtrees`
-- ----------------------------
DROP TABLE IF EXISTS `mtrees`;
CREATE TABLE `mtrees` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `tname` varchar(128) NOT NULL DEFAULT '',
  `tprefix` varchar(32) NOT NULL DEFAULT '',
  `tvalue` varchar(128) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`),
  UNIQUE KEY `tname_tprefix_tvalue_idx` (`tname`,`tprefix`,`tvalue`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- ----------------------------
-- Records of mtrees
-- ----------------------------

-- ----------------------------
-- Table structure for `pdt`
-- ----------------------------
DROP TABLE IF EXISTS `pdt`;
CREATE TABLE `pdt` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `sdomain` varchar(128) NOT NULL,
  `prefix` varchar(32) NOT NULL,
  `domain` varchar(128) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`),
  UNIQUE KEY `sdomain_prefix_idx` (`sdomain`,`prefix`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- ----------------------------
-- Records of pdt
-- ----------------------------

-- ----------------------------
-- Table structure for `pl_pipes`
-- ----------------------------
DROP TABLE IF EXISTS `pl_pipes`;
CREATE TABLE `pl_pipes` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `pipeid` varchar(64) NOT NULL DEFAULT '',
  `algorithm` varchar(32) NOT NULL DEFAULT '',
  `plimit` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- ----------------------------
-- Records of pl_pipes
-- ----------------------------

-- ----------------------------
-- Table structure for `presentity`
-- ----------------------------
DROP TABLE IF EXISTS `presentity`;
CREATE TABLE `presentity` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `username` varchar(64) NOT NULL,
  `domain` varchar(64) NOT NULL,
  `event` varchar(64) NOT NULL,
  `etag` varchar(64) NOT NULL,
  `expires` int(11) NOT NULL,
  `received_time` int(11) NOT NULL,
  `body` blob NOT NULL,
  `sender` varchar(128) NOT NULL,
  `priority` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `presentity_idx` (`username`,`domain`,`event`,`etag`) USING BTREE,
  KEY `presentity_expires` (`expires`) USING BTREE,
  KEY `account_idx` (`username`,`domain`,`event`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- ----------------------------
-- Records of presentity
-- ----------------------------

-- ----------------------------
-- Table structure for `pua`
-- ----------------------------
DROP TABLE IF EXISTS `pua`;
CREATE TABLE `pua` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `pres_uri` varchar(128) NOT NULL,
  `pres_id` varchar(255) NOT NULL,
  `event` int(11) NOT NULL,
  `expires` int(11) NOT NULL,
  `desired_expires` int(11) NOT NULL,
  `flag` int(11) NOT NULL,
  `etag` varchar(64) NOT NULL,
  `tuple_id` varchar(64) DEFAULT NULL,
  `watcher_uri` varchar(128) NOT NULL,
  `call_id` varchar(255) NOT NULL,
  `to_tag` varchar(64) NOT NULL,
  `from_tag` varchar(64) NOT NULL,
  `cseq` int(11) NOT NULL,
  `record_route` text,
  `contact` varchar(128) NOT NULL,
  `remote_contact` varchar(128) NOT NULL,
  `version` int(11) NOT NULL,
  `extra_headers` text NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `pua_idx` (`etag`,`tuple_id`,`call_id`,`from_tag`) USING BTREE,
  KEY `expires_idx` (`expires`) USING BTREE,
  KEY `dialog1_idx` (`pres_id`,`pres_uri`) USING BTREE,
  KEY `dialog2_idx` (`call_id`,`from_tag`) USING BTREE,
  KEY `record_idx` (`pres_id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- ----------------------------
-- Records of pua
-- ----------------------------

-- ----------------------------
-- Table structure for `purplemap`
-- ----------------------------
DROP TABLE IF EXISTS `purplemap`;
CREATE TABLE `purplemap` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `sip_user` varchar(128) NOT NULL,
  `ext_user` varchar(128) NOT NULL,
  `ext_prot` varchar(16) NOT NULL,
  `ext_pass` varchar(64) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- ----------------------------
-- Records of purplemap
-- ----------------------------

-- ----------------------------
-- Table structure for `re_grp`
-- ----------------------------
DROP TABLE IF EXISTS `re_grp`;
CREATE TABLE `re_grp` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `reg_exp` varchar(128) NOT NULL DEFAULT '',
  `group_id` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `group_idx` (`group_id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- ----------------------------
-- Records of re_grp
-- ----------------------------

-- ----------------------------
-- Table structure for `rls_presentity`
-- ----------------------------
DROP TABLE IF EXISTS `rls_presentity`;
CREATE TABLE `rls_presentity` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `rlsubs_did` varchar(255) NOT NULL,
  `resource_uri` varchar(128) NOT NULL,
  `content_type` varchar(255) NOT NULL,
  `presence_state` blob NOT NULL,
  `expires` int(11) NOT NULL,
  `updated` int(11) NOT NULL,
  `auth_state` int(11) NOT NULL,
  `reason` varchar(64) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `rls_presentity_idx` (`rlsubs_did`,`resource_uri`) USING BTREE,
  KEY `rlsubs_idx` (`rlsubs_did`) USING BTREE,
  KEY `updated_idx` (`updated`) USING BTREE,
  KEY `expires_idx` (`expires`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- ----------------------------
-- Records of rls_presentity
-- ----------------------------

-- ----------------------------
-- Table structure for `rls_watchers`
-- ----------------------------
DROP TABLE IF EXISTS `rls_watchers`;
CREATE TABLE `rls_watchers` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `presentity_uri` varchar(128) NOT NULL,
  `to_user` varchar(64) NOT NULL,
  `to_domain` varchar(64) NOT NULL,
  `watcher_username` varchar(64) NOT NULL,
  `watcher_domain` varchar(64) NOT NULL,
  `event` varchar(64) NOT NULL DEFAULT 'presence',
  `event_id` varchar(64) DEFAULT NULL,
  `to_tag` varchar(64) NOT NULL,
  `from_tag` varchar(64) NOT NULL,
  `callid` varchar(255) NOT NULL,
  `local_cseq` int(11) NOT NULL,
  `remote_cseq` int(11) NOT NULL,
  `contact` varchar(128) NOT NULL,
  `record_route` text,
  `expires` int(11) NOT NULL,
  `status` int(11) NOT NULL DEFAULT '2',
  `reason` varchar(64) NOT NULL,
  `version` int(11) NOT NULL DEFAULT '0',
  `socket_info` varchar(64) NOT NULL,
  `local_contact` varchar(128) NOT NULL,
  `from_user` varchar(64) NOT NULL,
  `from_domain` varchar(64) NOT NULL,
  `updated` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `rls_watcher_idx` (`callid`,`to_tag`,`from_tag`) USING BTREE,
  KEY `rls_watchers_update` (`watcher_username`,`watcher_domain`,`event`) USING BTREE,
  KEY `rls_watchers_expires` (`expires`) USING BTREE,
  KEY `updated_idx` (`updated`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- ----------------------------
-- Records of rls_watchers
-- ----------------------------

-- ----------------------------
-- Table structure for `rtpproxy`
-- ----------------------------
DROP TABLE IF EXISTS `rtpproxy`;
CREATE TABLE `rtpproxy` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `setid` varchar(32) NOT NULL DEFAULT '0',
  `url` varchar(64) NOT NULL DEFAULT '',
  `flags` int(11) NOT NULL DEFAULT '0',
  `weight` int(11) NOT NULL DEFAULT '1',
  `description` varchar(64) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- ----------------------------
-- Records of rtpproxy
-- ----------------------------

-- ----------------------------
-- Table structure for `sca_subscriptions`
-- ----------------------------
DROP TABLE IF EXISTS `sca_subscriptions`;
CREATE TABLE `sca_subscriptions` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `subscriber` varchar(255) NOT NULL,
  `aor` varchar(255) NOT NULL,
  `event` int(11) NOT NULL DEFAULT '0',
  `expires` int(11) NOT NULL DEFAULT '0',
  `state` int(11) NOT NULL DEFAULT '0',
  `app_idx` int(11) NOT NULL DEFAULT '0',
  `call_id` varchar(255) NOT NULL,
  `from_tag` varchar(64) NOT NULL,
  `to_tag` varchar(64) NOT NULL,
  `record_route` text,
  `notify_cseq` int(11) NOT NULL,
  `subscribe_cseq` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `sca_subscriptions_idx` (`subscriber`,`call_id`,`from_tag`,`to_tag`) USING BTREE,
  KEY `sca_expires_idx` (`expires`) USING BTREE,
  KEY `sca_subscribers_idx` (`subscriber`,`event`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- ----------------------------
-- Records of sca_subscriptions
-- ----------------------------

-- ----------------------------
-- Table structure for `silo`
-- ----------------------------
DROP TABLE IF EXISTS `silo`;
CREATE TABLE `silo` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `src_addr` varchar(128) NOT NULL DEFAULT '',
  `dst_addr` varchar(128) NOT NULL DEFAULT '',
  `username` varchar(64) NOT NULL DEFAULT '',
  `domain` varchar(64) NOT NULL DEFAULT '',
  `inc_time` int(11) NOT NULL DEFAULT '0',
  `exp_time` int(11) NOT NULL DEFAULT '0',
  `snd_time` int(11) NOT NULL DEFAULT '0',
  `ctype` varchar(32) NOT NULL DEFAULT 'text/plain',
  `body` blob,
  `extra_hdrs` text,
  `callid` varchar(128) NOT NULL DEFAULT '',
  `status` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `account_idx` (`username`,`domain`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- ----------------------------
-- Records of silo
-- ----------------------------

-- ----------------------------
-- Table structure for `sip_trace`
-- ----------------------------
DROP TABLE IF EXISTS `sip_trace`;
CREATE TABLE `sip_trace` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `time_stamp` datetime NOT NULL DEFAULT '1900-01-01 00:00:01',
  `time_us` int(10) unsigned NOT NULL DEFAULT '0',
  `callid` varchar(255) NOT NULL DEFAULT '',
  `traced_user` varchar(128) NOT NULL DEFAULT '',
  `msg` mediumtext NOT NULL,
  `method` varchar(50) NOT NULL DEFAULT '',
  `status` varchar(128) NOT NULL DEFAULT '',
  `fromip` varchar(50) NOT NULL DEFAULT '',
  `toip` varchar(50) NOT NULL DEFAULT '',
  `fromtag` varchar(64) NOT NULL DEFAULT '',
  `totag` varchar(64) NOT NULL DEFAULT '',
  `direction` varchar(4) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`),
  KEY `traced_user_idx` (`traced_user`) USING BTREE,
  KEY `date_idx` (`time_stamp`) USING BTREE,
  KEY `fromip_idx` (`fromip`) USING BTREE,
  KEY `callid_idx` (`callid`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- ----------------------------
-- Records of sip_trace
-- ----------------------------

-- ----------------------------
-- Table structure for `speed_dial`
-- ----------------------------
DROP TABLE IF EXISTS `speed_dial`;
CREATE TABLE `speed_dial` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `username` varchar(64) NOT NULL DEFAULT '',
  `domain` varchar(64) NOT NULL DEFAULT '',
  `sd_username` varchar(64) NOT NULL DEFAULT '',
  `sd_domain` varchar(64) NOT NULL DEFAULT '',
  `new_uri` varchar(128) NOT NULL DEFAULT '',
  `fname` varchar(64) NOT NULL DEFAULT '',
  `lname` varchar(64) NOT NULL DEFAULT '',
  `description` varchar(64) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`),
  UNIQUE KEY `speed_dial_idx` (`username`,`domain`,`sd_domain`,`sd_username`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- ----------------------------
-- Records of speed_dial
-- ----------------------------

-- ----------------------------
-- Table structure for `subscriber`
-- ----------------------------
DROP TABLE IF EXISTS `subscriber`;
CREATE TABLE `subscriber` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `username` varchar(64) NOT NULL DEFAULT '',
  `domain` varchar(64) NOT NULL DEFAULT '',
  `password` varchar(25) NOT NULL DEFAULT '',
  `email_address` varchar(64) NOT NULL DEFAULT '',
  `ha1` varchar(64) NOT NULL DEFAULT '',
  `ha1b` varchar(64) NOT NULL DEFAULT '',
  `rpid` varchar(64) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `account_idx` (`username`,`domain`) USING BTREE,
  KEY `username_idx` (`username`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- ----------------------------
-- Records of subscriber
-- ----------------------------

-- ----------------------------
-- Table structure for `topos_d`
-- ----------------------------
DROP TABLE IF EXISTS `topos_d`;
CREATE TABLE `topos_d` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `rectime` datetime NOT NULL,
  `s_method` varchar(64) NOT NULL DEFAULT '',
  `s_cseq` varchar(64) NOT NULL DEFAULT '',
  `a_callid` varchar(255) NOT NULL DEFAULT '',
  `a_uuid` varchar(255) NOT NULL DEFAULT '',
  `b_uuid` varchar(255) NOT NULL DEFAULT '',
  `a_contact` varchar(128) NOT NULL DEFAULT '',
  `b_contact` varchar(128) NOT NULL DEFAULT '',
  `as_contact` varchar(128) NOT NULL DEFAULT '',
  `bs_contact` varchar(128) NOT NULL DEFAULT '',
  `a_tag` varchar(255) NOT NULL DEFAULT '',
  `b_tag` varchar(255) NOT NULL DEFAULT '',
  `a_rr` mediumtext,
  `b_rr` mediumtext,
  `s_rr` mediumtext,
  `iflags` int(10) unsigned NOT NULL DEFAULT '0',
  `a_uri` varchar(128) NOT NULL DEFAULT '',
  `b_uri` varchar(128) NOT NULL DEFAULT '',
  `r_uri` varchar(128) NOT NULL DEFAULT '',
  `a_srcaddr` varchar(128) NOT NULL DEFAULT '',
  `b_srcaddr` varchar(128) NOT NULL DEFAULT '',
  `a_socket` varchar(128) NOT NULL DEFAULT '',
  `b_socket` varchar(128) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`),
  KEY `rectime_idx` (`rectime`) USING BTREE,
  KEY `a_callid_idx` (`a_callid`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- ----------------------------
-- Records of topos_d
-- ----------------------------

-- ----------------------------
-- Table structure for `topos_t`
-- ----------------------------
DROP TABLE IF EXISTS `topos_t`;
CREATE TABLE `topos_t` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `rectime` datetime NOT NULL,
  `s_method` varchar(64) NOT NULL DEFAULT '',
  `s_cseq` varchar(64) NOT NULL DEFAULT '',
  `a_callid` varchar(255) NOT NULL DEFAULT '',
  `a_uuid` varchar(255) NOT NULL DEFAULT '',
  `b_uuid` varchar(255) NOT NULL DEFAULT '',
  `direction` int(11) NOT NULL DEFAULT '0',
  `x_via` mediumtext,
  `x_vbranch` varchar(255) NOT NULL DEFAULT '',
  `x_rr` mediumtext,
  `y_rr` mediumtext,
  `s_rr` mediumtext,
  `x_uri` varchar(128) NOT NULL DEFAULT '',
  `a_contact` varchar(128) NOT NULL DEFAULT '',
  `b_contact` varchar(128) NOT NULL DEFAULT '',
  `as_contact` varchar(128) NOT NULL DEFAULT '',
  `bs_contact` varchar(128) NOT NULL DEFAULT '',
  `x_tag` varchar(255) NOT NULL DEFAULT '',
  `a_tag` varchar(255) NOT NULL DEFAULT '',
  `b_tag` varchar(255) NOT NULL DEFAULT '',
  `a_srcaddr` varchar(128) NOT NULL DEFAULT '',
  `b_srcaddr` varchar(128) NOT NULL DEFAULT '',
  `a_socket` varchar(128) NOT NULL DEFAULT '',
  `b_socket` varchar(128) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`),
  KEY `rectime_idx` (`rectime`) USING BTREE,
  KEY `a_callid_idx` (`a_callid`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- ----------------------------
-- Records of topos_t
-- ----------------------------

-- ----------------------------
-- Table structure for `trusted`
-- ----------------------------
DROP TABLE IF EXISTS `trusted`;
CREATE TABLE `trusted` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `src_ip` varchar(50) NOT NULL,
  `proto` varchar(4) NOT NULL,
  `from_pattern` varchar(64) DEFAULT NULL,
  `ruri_pattern` varchar(64) DEFAULT NULL,
  `tag` varchar(64) DEFAULT NULL,
  `priority` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `peer_idx` (`src_ip`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- ----------------------------
-- Records of trusted
-- ----------------------------

-- ----------------------------
-- Table structure for `uacreg`
-- ----------------------------
DROP TABLE IF EXISTS `uacreg`;
CREATE TABLE `uacreg` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `l_uuid` varchar(64) NOT NULL DEFAULT '',
  `l_username` varchar(64) NOT NULL DEFAULT '',
  `l_domain` varchar(128) NOT NULL DEFAULT '',
  `r_username` varchar(64) NOT NULL DEFAULT '',
  `r_domain` varchar(128) NOT NULL DEFAULT '',
  `realm` varchar(64) NOT NULL DEFAULT '',
  `auth_username` varchar(64) NOT NULL DEFAULT '',
  `auth_password` varchar(64) NOT NULL DEFAULT '',
  `auth_proxy` varchar(64) NOT NULL DEFAULT '',
  `expires` int(11) NOT NULL DEFAULT '0',
  `flags` int(11) NOT NULL DEFAULT '0',
  `reg_delay` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `l_uuid_idx` (`l_uuid`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- ----------------------------
-- Records of uacreg
-- ----------------------------

-- ----------------------------
-- Table structure for `uid_credentials`
-- ----------------------------
DROP TABLE IF EXISTS `uid_credentials`;
CREATE TABLE `uid_credentials` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `auth_username` varchar(64) NOT NULL,
  `did` varchar(64) NOT NULL DEFAULT '_default',
  `realm` varchar(64) NOT NULL,
  `password` varchar(28) NOT NULL DEFAULT '',
  `flags` int(11) NOT NULL DEFAULT '0',
  `ha1` varchar(32) NOT NULL,
  `ha1b` varchar(32) NOT NULL DEFAULT '',
  `uid` varchar(64) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `cred_idx` (`auth_username`,`did`) USING BTREE,
  KEY `uid` (`uid`) USING BTREE,
  KEY `did_idx` (`did`) USING BTREE,
  KEY `realm_idx` (`realm`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- ----------------------------
-- Records of uid_credentials
-- ----------------------------

-- ----------------------------
-- Table structure for `uid_domain`
-- ----------------------------
DROP TABLE IF EXISTS `uid_domain`;
CREATE TABLE `uid_domain` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `did` varchar(64) NOT NULL,
  `domain` varchar(64) NOT NULL,
  `flags` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `domain_idx` (`domain`) USING BTREE,
  KEY `did_idx` (`did`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- ----------------------------
-- Records of uid_domain
-- ----------------------------

-- ----------------------------
-- Table structure for `uid_domain_attrs`
-- ----------------------------
DROP TABLE IF EXISTS `uid_domain_attrs`;
CREATE TABLE `uid_domain_attrs` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `did` varchar(64) DEFAULT NULL,
  `name` varchar(32) NOT NULL,
  `type` int(11) NOT NULL DEFAULT '0',
  `value` varchar(128) DEFAULT NULL,
  `flags` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `domain_attr_idx` (`did`,`name`,`value`) USING BTREE,
  KEY `domain_did` (`did`,`flags`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- ----------------------------
-- Records of uid_domain_attrs
-- ----------------------------

-- ----------------------------
-- Table structure for `uid_global_attrs`
-- ----------------------------
DROP TABLE IF EXISTS `uid_global_attrs`;
CREATE TABLE `uid_global_attrs` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(32) NOT NULL,
  `type` int(11) NOT NULL DEFAULT '0',
  `value` varchar(128) DEFAULT NULL,
  `flags` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `global_attrs_idx` (`name`,`value`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- ----------------------------
-- Records of uid_global_attrs
-- ----------------------------

-- ----------------------------
-- Table structure for `uid_uri`
-- ----------------------------
DROP TABLE IF EXISTS `uid_uri`;
CREATE TABLE `uid_uri` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `uid` varchar(64) NOT NULL,
  `did` varchar(64) NOT NULL,
  `username` varchar(64) NOT NULL,
  `flags` int(10) unsigned NOT NULL DEFAULT '0',
  `scheme` varchar(8) NOT NULL DEFAULT 'sip',
  PRIMARY KEY (`id`),
  KEY `uri_idx1` (`username`,`did`,`scheme`) USING BTREE,
  KEY `uri_uid` (`uid`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- ----------------------------
-- Records of uid_uri
-- ----------------------------

-- ----------------------------
-- Table structure for `uid_uri_attrs`
-- ----------------------------
DROP TABLE IF EXISTS `uid_uri_attrs`;
CREATE TABLE `uid_uri_attrs` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `username` varchar(64) NOT NULL,
  `did` varchar(64) NOT NULL,
  `name` varchar(32) NOT NULL,
  `value` varchar(128) DEFAULT NULL,
  `type` int(11) NOT NULL DEFAULT '0',
  `flags` int(10) unsigned NOT NULL DEFAULT '0',
  `scheme` varchar(8) NOT NULL DEFAULT 'sip',
  PRIMARY KEY (`id`),
  UNIQUE KEY `uriattrs_idx` (`username`,`did`,`name`,`value`,`scheme`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- ----------------------------
-- Records of uid_uri_attrs
-- ----------------------------

-- ----------------------------
-- Table structure for `uid_user_attrs`
-- ----------------------------
DROP TABLE IF EXISTS `uid_user_attrs`;
CREATE TABLE `uid_user_attrs` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `uid` varchar(64) NOT NULL,
  `name` varchar(32) NOT NULL,
  `value` varchar(128) DEFAULT NULL,
  `type` int(11) NOT NULL DEFAULT '0',
  `flags` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `userattrs_idx` (`uid`,`name`,`value`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- ----------------------------
-- Records of uid_user_attrs
-- ----------------------------

-- ----------------------------
-- Table structure for `uri`
-- ----------------------------
DROP TABLE IF EXISTS `uri`;
CREATE TABLE `uri` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `username` varchar(64) NOT NULL DEFAULT '',
  `domain` varchar(64) NOT NULL DEFAULT '',
  `uri_user` varchar(64) NOT NULL DEFAULT '',
  `last_modified` datetime NOT NULL DEFAULT '2000-01-01 00:00:01',
  PRIMARY KEY (`id`),
  UNIQUE KEY `account_idx` (`username`,`domain`,`uri_user`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- ----------------------------
-- Records of uri
-- ----------------------------

-- ----------------------------
-- Table structure for `userblacklist`
-- ----------------------------
DROP TABLE IF EXISTS `userblacklist`;
CREATE TABLE `userblacklist` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `username` varchar(64) NOT NULL DEFAULT '',
  `domain` varchar(64) NOT NULL DEFAULT '',
  `prefix` varchar(64) NOT NULL DEFAULT '',
  `whitelist` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `userblacklist_idx` (`username`,`domain`,`prefix`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- ----------------------------
-- Records of userblacklist
-- ----------------------------

-- ----------------------------
-- Table structure for `usr_preferences`
-- ----------------------------
DROP TABLE IF EXISTS `usr_preferences`;
CREATE TABLE `usr_preferences` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `uuid` varchar(64) NOT NULL DEFAULT '',
  `username` varchar(128) NOT NULL DEFAULT '0',
  `domain` varchar(64) NOT NULL DEFAULT '',
  `attribute` varchar(32) NOT NULL DEFAULT '',
  `type` int(11) NOT NULL DEFAULT '0',
  `value` varchar(128) NOT NULL DEFAULT '',
  `last_modified` datetime NOT NULL DEFAULT '2000-01-01 00:00:01',
  PRIMARY KEY (`id`),
  KEY `ua_idx` (`uuid`,`attribute`) USING BTREE,
  KEY `uda_idx` (`username`,`domain`,`attribute`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- ----------------------------
-- Records of usr_preferences
-- ----------------------------

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
-- Table structure for `watchers`
-- ----------------------------
DROP TABLE IF EXISTS `watchers`;
CREATE TABLE `watchers` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `presentity_uri` varchar(128) NOT NULL,
  `watcher_username` varchar(64) NOT NULL,
  `watcher_domain` varchar(64) NOT NULL,
  `event` varchar(64) NOT NULL DEFAULT 'presence',
  `status` int(11) NOT NULL,
  `reason` varchar(64) DEFAULT NULL,
  `inserted_time` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `watcher_idx` (`presentity_uri`,`watcher_username`,`watcher_domain`,`event`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- ----------------------------
-- Records of watchers
-- ----------------------------

-- ----------------------------
-- Table structure for `xcap`
-- ----------------------------
DROP TABLE IF EXISTS `xcap`;
CREATE TABLE `xcap` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `username` varchar(64) NOT NULL,
  `domain` varchar(64) NOT NULL,
  `doc` mediumblob NOT NULL,
  `doc_type` int(11) NOT NULL,
  `etag` varchar(64) NOT NULL,
  `source` int(11) NOT NULL,
  `doc_uri` varchar(255) NOT NULL,
  `port` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `doc_uri_idx` (`doc_uri`) USING BTREE,
  KEY `account_doc_type_idx` (`username`,`domain`,`doc_type`) USING BTREE,
  KEY `account_doc_type_uri_idx` (`username`,`domain`,`doc_type`,`doc_uri`) USING BTREE,
  KEY `account_doc_uri_idx` (`username`,`domain`,`doc_uri`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- ----------------------------
-- Records of xcap
-- ----------------------------
