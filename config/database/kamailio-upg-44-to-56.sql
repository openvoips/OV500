--UPGRADE FROM 4.4 to 5.0

--table: dialplan
ALTER TABLE dialplan CHANGE COLUMN repl_exp repl_exp VARCHAR(256) NOT NULL; -- was varchar(64) NOT NULL
 
--table: sca_subscriptions
ALTER TABLE sca_subscriptions ADD COLUMN server_id INT(11) NOT NULL DEFAULT '0';
ALTER TABLE sca_subscriptions DROP INDEX sca_expires_idx; # was INDEX (expires)
ALTER TABLE sca_subscriptions ADD INDEX sca_expires_idx (server_id,expires);
UPDATE version SET table_version=2 WHERE TABLE_NAME="sca_subscriptions";
 
--table: subscriber
ALTER TABLE subscriber CHANGE COLUMN rpid rpid VARCHAR(128) DEFAULT NULL; -- was varchar(64) DEFAULT NULL
ALTER TABLE subscriber CHANGE COLUMN email_address email_address VARCHAR(128) DEFAULT NULL; -- was varchar(64) NOT NULL DEFAULT ''
ALTER TABLE subscriber CHANGE COLUMN password password VARCHAR(64) NOT NULL DEFAULT ''; -- was varchar(25) NOT NULL DEFAULT ''
ALTER TABLE subscriber CHANGE COLUMN ha1 ha1 VARCHAR(128) NOT NULL DEFAULT ''; -- was varchar(64) NOT NULL DEFAULT ''
ALTER TABLE subscriber CHANGE COLUMN ha1b ha1b VARCHAR(128) NOT NULL DEFAULT ''; -- was varchar(64) NOT NULL DEFAULT ''
UPDATE version SET table_version=7 WHERE TABLE_NAME="subscriber";
 
 
--table: uacreg
ALTER TABLE uacreg CHANGE COLUMN auth_proxy auth_proxy VARCHAR(128) NOT NULL DEFAULT ''; -- was varchar(64) NOT NULL DEFAULT ''
ALTER TABLE uacreg CHANGE COLUMN l_domain l_domain VARCHAR(64) NOT NULL DEFAULT ''; -- was varchar(128) NOT NULL DEFAULT ''
ALTER TABLE uacreg CHANGE COLUMN r_domain r_domain VARCHAR(64) NOT NULL DEFAULT ''; -- was varchar(128) NOT NULL DEFAULT ''
ALTER TABLE uacreg ADD COLUMN auth_ha1 VARCHAR(128) NOT NULL DEFAULT '';
UPDATE version SET table_version=3 WHERE TABLE_NAME="uacreg";

--UPGRADE FROM 5.0 to 5.1

ALTER TABLE lcr_rule ADD COLUMN mt_tvalue VARCHAR(128) DEFAULT NULL AFTER request_uri;
UPDATE version SET table_version=3 WHERE TABLE_NAME='lcr_rule';
ALTER TABLE location MODIFY contact VARCHAR(512) NOT NULL DEFAULT '';
UPDATE version SET table_version=9 WHERE TABLE_NAME='location';
ALTER TABLE active_watchers CHANGE COLUMN reason reason VARCHAR(64) DEFAULT NULL; -- # was varchar(64) NOT NULL
ALTER TABLE domain_attrs DROP INDEX domain_attrs_idx; -- # was UNIQUE (did,name,value)
ALTER TABLE domain_attrs ADD INDEX domain_attrs_idx (did,name);
-- ALTER TABLE lcr_rule ADD COLUMN mt_tvalue varchar(128) DEFAULT NULL;
-- ALTER TABLE location CHANGE COLUMN contact contact varchar(512) NOT NULL DEFAULT ''; # was varchar(255) NOT NULL DEFAULT ''
ALTER TABLE topos_d ADD INDEX a_uuid_idx (a_uuid);
ALTER TABLE topos_d ADD INDEX b_uuid_idx (b_uuid);
ALTER TABLE topos_t ADD INDEX x_vbranch_idx (x_vbranch);
ALTER TABLE topos_t ADD INDEX a_uuid_idx (a_uuid);

--UPGRADE FROM 5.1 to 5.2

-- location table - optional update
ALTER TABLE location_attrs CHANGE COLUMN avalue avalue VARCHAR(512) NOT NULL DEFAULT '';
 
-- presentity table
ALTER TABLE presentity CHANGE COLUMN etag etag VARCHAR(128) NOT NULL;
ALTER TABLE presentity ADD COLUMN ruid VARCHAR(64);
CREATE UNIQUE INDEX ruid_idx ON presentity (ruid);
UPDATE version SET table_version=5 WHERE TABLE_NAME='presentity';
 
-- pua table - optional update
ALTER TABLE pua CHANGE COLUMN etag etag VARCHAR(128) NOT NULL;
 
-- subscriber table - optional update
ALTER TABLE subscriber DROP COLUMN rpid;
ALTER TABLE subscriber DROP COLUMN email_address;
 
-- xcap table - optional update
ALTER TABLE xcap CHANGE COLUMN etag etag VARCHAR(128) NOT NULL;

--UPGRADE FROM 5.2 to 5.3

-- acc table
ALTER TABLE acc CHANGE COLUMN to_tag to_tag VARCHAR(128) NOT NULL DEFAULT ''; -- # was varchar(64) NOT NULL DEFAULT ''
ALTER TABLE acc CHANGE COLUMN from_tag from_tag VARCHAR(128) NOT NULL DEFAULT ''; -- # was varchar(64) NOT NULL DEFAULT ''
 
-- active_watchers table
ALTER TABLE active_watchers CHANGE COLUMN contact contact VARCHAR(255) NOT NULL; -- # was varchar(128) NOT NULL
ALTER TABLE active_watchers CHANGE COLUMN from_tag from_tag VARCHAR(128) NOT NULL; -- # was varchar(64) NOT NULL
ALTER TABLE active_watchers CHANGE COLUMN to_tag to_tag VARCHAR(128) NOT NULL; -- # was varchar(64) NOT NULL
ALTER TABLE active_watchers CHANGE COLUMN presentity_uri presentity_uri VARCHAR(255) NOT NULL; -- # was varchar(128) NOT NULL
ALTER TABLE active_watchers CHANGE COLUMN local_contact local_contact VARCHAR(255) NOT NULL; -- # was varchar(128) NOT NULL
 
ALTER TABLE aliases CHANGE COLUMN received received VARCHAR(255) DEFAULT NULL; -- # was varchar(128) DEFAULT NULL
 
ALTER TABLE carrierfailureroute CHANGE COLUMN host_name host_name VARCHAR(255) NOT NULL DEFAULT ''; -- # was varchar(128) NOT NULL DEFAULT ''
ALTER TABLE carrierroute CHANGE COLUMN rewrite_host rewrite_host VARCHAR(255) NOT NULL DEFAULT ''; -- # was varchar(128) NOT NULL DEFAULT ''
 
ALTER TABLE dialog CHANGE COLUMN req_uri req_uri VARCHAR(255) NOT NULL; -- # was varchar(128) NOT NULL
ALTER TABLE dialog CHANGE COLUMN caller_contact caller_contact VARCHAR(255) NOT NULL; -- # was varchar(128) NOT NULL
ALTER TABLE dialog CHANGE COLUMN callee_contact callee_contact VARCHAR(255) NOT NULL; -- # was varchar(128) NOT NULL
ALTER TABLE dialog CHANGE COLUMN to_tag to_tag VARCHAR(128) NOT NULL; -- # was varchar(64) NOT NULL
ALTER TABLE dialog CHANGE COLUMN from_tag from_tag VARCHAR(128) NOT NULL; -- # was varchar(64) NOT NULL
ALTER TABLE dialog CHANGE COLUMN from_uri from_uri VARCHAR(255) NOT NULL; -- # was varchar(128) NOT NULL
ALTER TABLE dialog CHANGE COLUMN to_uri to_uri VARCHAR(255) NOT NULL; -- # was varchar(128) NOT NULL
 
ALTER TABLE missed_calls CHANGE COLUMN to_tag to_tag VARCHAR(128) NOT NULL DEFAULT ''; -- # was varchar(64) NOT NULL DEFAULT ''
ALTER TABLE missed_calls CHANGE COLUMN from_tag from_tag VARCHAR(128) NOT NULL DEFAULT ''; -- # was varchar(64) NOT NULL DEFAULT ''
 
ALTER TABLE pdt CHANGE COLUMN DOMAIN DOMAIN VARCHAR(255) NOT NULL DEFAULT ''; -- # was varchar(128) NOT NULL DEFAULT ''
ALTER TABLE pdt CHANGE COLUMN sdomain sdomain VARCHAR(255) NOT NULL; -- # was varchar(128) NOT NULL
 
ALTER TABLE presentity CHANGE COLUMN sender sender VARCHAR(255) NOT NULL; -- # was varchar(128) NOT NULL
 
ALTER TABLE pua CHANGE COLUMN remote_contact remote_contact VARCHAR(255) NOT NULL; -- # was varchar(128) NOT NULL
ALTER TABLE pua CHANGE COLUMN watcher_uri watcher_uri VARCHAR(255) NOT NULL; -- # was varchar(128) NOT NULL
ALTER TABLE pua CHANGE COLUMN contact contact VARCHAR(255) NOT NULL; -- # was varchar(128) NOT NULL
ALTER TABLE pua CHANGE COLUMN to_tag to_tag VARCHAR(128) NOT NULL; -- # was varchar(64) NOT NULL
ALTER TABLE pua CHANGE COLUMN from_tag from_tag VARCHAR(128) NOT NULL; -- # was varchar(64) NOT NULL
ALTER TABLE pua CHANGE COLUMN pres_uri pres_uri VARCHAR(255) NOT NULL; -- # was varchar(128) NOT NULL
 
ALTER TABLE purplemap CHANGE COLUMN sip_user sip_user VARCHAR(255) NOT NULL; -- # was varchar(128) NOT NULL
ALTER TABLE purplemap CHANGE COLUMN ext_user ext_user VARCHAR(255) NOT NULL; -- # was varchar(128) NOT NULL
 
ALTER TABLE rls_presentity CHANGE COLUMN resource_uri resource_uri VARCHAR(255) NOT NULL; -- # was varchar(128) NOT NULL
 
ALTER TABLE rls_watchers CHANGE COLUMN to_tag to_tag VARCHAR(128) NOT NULL; -- # was varchar(64) NOT NULL
ALTER TABLE rls_watchers CHANGE COLUMN from_tag from_tag VARCHAR(128) NOT NULL; -- # was varchar(64) NOT NULL
ALTER TABLE rls_watchers CHANGE COLUMN presentity_uri presentity_uri VARCHAR(255) NOT NULL; -- # was varchar(128) NOT NULL
ALTER TABLE rls_watchers CHANGE COLUMN local_contact local_contact VARCHAR(255) NOT NULL; -- # was varchar(128) NOT NULL
ALTER TABLE rls_watchers CHANGE COLUMN contact contact VARCHAR(255) NOT NULL; -- # was varchar(128) NOT NULL
 
ALTER TABLE sca_subscriptions CHANGE COLUMN to_tag to_tag VARCHAR(128) NOT NULL; -- # was varchar(64) NOT NULL
ALTER TABLE sca_subscriptions CHANGE COLUMN from_tag from_tag VARCHAR(128) NOT NULL; -- # was varchar(64) NOT NULL
 
ALTER TABLE silo CHANGE COLUMN dst_addr dst_addr VARCHAR(255) NOT NULL DEFAULT ''; -- # was varchar(128) NOT NULL DEFAULT ''
ALTER TABLE silo CHANGE COLUMN src_addr src_addr VARCHAR(255) NOT NULL DEFAULT ''; -- # was varchar(128) NOT NULL DEFAULT ''
 
ALTER TABLE sip_trace CHANGE COLUMN fromtag fromtag VARCHAR(128) NOT NULL DEFAULT ''; -- # was varchar(64) NOT NULL DEFAULT ''
ALTER TABLE sip_trace CHANGE COLUMN STATUS STATUS VARCHAR(255) NOT NULL DEFAULT ''; -- # was varchar(128) NOT NULL DEFAULT ''
ALTER TABLE sip_trace CHANGE COLUMN traced_user traced_user VARCHAR(255) NOT NULL DEFAULT ''; -- # was varchar(128) NOT NULL DEFAULT ''
ALTER TABLE sip_trace CHANGE COLUMN totag totag VARCHAR(128) NOT NULL DEFAULT ''; -- # was varchar(64) NOT NULL DEFAULT ''
 
ALTER TABLE speed_dial CHANGE COLUMN new_uri new_uri VARCHAR(255) NOT NULL DEFAULT ''; -- # was varchar(128) NOT NULL DEFAULT ''
 
ALTER TABLE topos_d CHANGE COLUMN a_contact a_contact VARCHAR(512) NOT NULL DEFAULT ''; -- # was varchar(128) NOT NULL DEFAULT ''
ALTER TABLE topos_d CHANGE COLUMN b_contact b_contact VARCHAR(512) NOT NULL DEFAULT ''; -- # was varchar(128) NOT NULL DEFAULT ''
ALTER TABLE topos_d CHANGE COLUMN b_uri b_uri VARCHAR(255) NOT NULL DEFAULT ''; -- # was varchar(128) NOT NULL DEFAULT ''
ALTER TABLE topos_d CHANGE COLUMN a_uri a_uri VARCHAR(255) NOT NULL DEFAULT ''; -- # was varchar(128) NOT NULL DEFAULT ''
ALTER TABLE topos_d CHANGE COLUMN bs_contact bs_contact VARCHAR(512) NOT NULL DEFAULT ''; -- # was varchar(128) NOT NULL DEFAULT ''
ALTER TABLE topos_d CHANGE COLUMN as_contact as_contact VARCHAR(512) NOT NULL DEFAULT ''; -- # was varchar(128) NOT NULL DEFAULT ''
ALTER TABLE topos_d CHANGE COLUMN r_uri r_uri VARCHAR(255) NOT NULL DEFAULT ''; -- # was varchar(128) NOT NULL DEFAULT ''
 
ALTER TABLE topos_t CHANGE COLUMN x_uri x_uri VARCHAR(255) NOT NULL DEFAULT ''; -- # was varchar(128) NOT NULL DEFAULT ''
ALTER TABLE topos_t CHANGE COLUMN a_contact a_contact VARCHAR(512) NOT NULL DEFAULT ''; -- # was varchar(128) NOT NULL DEFAULT ''
ALTER TABLE topos_t CHANGE COLUMN b_contact b_contact VARCHAR(512) NOT NULL DEFAULT ''; -- # was varchar(128) NOT NULL DEFAULT ''
ALTER TABLE topos_t CHANGE COLUMN bs_contact bs_contact VARCHAR(512) NOT NULL DEFAULT ''; -- # was varchar(128) NOT NULL DEFAULT ''
ALTER TABLE topos_t CHANGE COLUMN b_srcaddr b_srcaddr VARCHAR(255) NOT NULL DEFAULT ''; -- # was varchar(128) NOT NULL DEFAULT ''
ALTER TABLE topos_t CHANGE COLUMN as_contact as_contact VARCHAR(512) NOT NULL DEFAULT ''; -- # was varchar(128) NOT NULL DEFAULT ''
ALTER TABLE topos_t CHANGE COLUMN a_srcaddr a_srcaddr VARCHAR(255) NOT NULL DEFAULT ''; -- # was varchar(128) NOT NULL DEFAULT ''
 
-- uacreg table
ALTER TABLE uacreg CHANGE COLUMN auth_proxy auth_proxy VARCHAR(255) NOT NULL DEFAULT ''; -- # was varchar(128) NOT NULL DEFAULT ''
ALTER TABLE uacreg ADD COLUMN socket VARCHAR(128) NOT NULL DEFAULT '';
UPDATE version SET table_version=4 WHERE TABLE_NAME='uacreg';
 
-- usr_preferences table
ALTER TABLE usr_preferences CHANGE COLUMN username username VARCHAR(255) NOT NULL DEFAULT '0'; -- # was varchar(128) NOT NULL DEFAULT '0'
  
-- watchers table
ALTER TABLE watchers CHANGE COLUMN presentity_uri presentity_uri VARCHAR(255) NOT NULL; -- # was varchar(128) NOT NULL

-- UPGRADE FROM 5.3 TO 5.4

-- version table - added id column to facilitate records management with external tools
-- * the column is not used by Kamailio, thus is optional to be create
 
ALTER TABLE `version` ADD COLUMN `id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT, ADD PRIMARY KEY (`id`);

-- UPGRADE FROM 5.4 TO 5.5

-- topos_d table
ALTER TABLE topos_d ADD COLUMN x_context VARCHAR(64) NOT NULL DEFAULT '';
UPDATE version SET table_version=2 WHERE TABLE_NAME='topos_d';
 
-- topos_t table
ALTER TABLE topos_t ADD COLUMN x_context VARCHAR(64) NOT NULL DEFAULT '';
UPDATE version SET table_version=2 WHERE TABLE_NAME='topos_t';
 
-- uacreg table
ALTER TABLE uacreg ADD COLUMN contact_addr VARCHAR(255) NOT NULL DEFAULT '';
UPDATE version SET table_version=5 WHERE TABLE_NAME='uacreg';
 
-- IMPORTANT: see also the notes about the userblocklist module
