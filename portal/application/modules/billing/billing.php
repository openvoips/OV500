<?php

/**
 * Plugin Name: Billing & Invoice Management
 * Plugin URI: http://openvoips.org/
 * Version: 1.0
 * Description: Billing & Invoice Related
 * Author: Seema Anand  openvoips@gmail.com
 * Author URI: http://openvoips.org/
 */
?>
<?php

function plugin_Billingt_install() {
    $ci = & get_instance();
    $sql1 = "CREATE TABLE  IF NOT EXISTS `bill_account_sdr` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `account_id` varchar(30) DEFAULT NULL,
            `rule_type` varchar(30) DEFAULT NULL,
            `service_number` varchar(30) DEFAULT NULL,
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
            PRIMARY KEY (`id`)
          ) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4 ROW_FORMAT=COMPACT;";

    $query1 = $ci->db->query($sql1);
    if (!$query1) {
        $error_array = $ci->db->error();
        return $error_array['message'];
    }
    $sql2 = "CREATE TABLE  IF NOT EXISTS
            `bill_billing_event` (
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
          ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 ROW_FORMAT=COMPACT; ";

    $query2 = $ci->db->query($sql2);
    if (!$query2) {
        $error_array = $ci->db->error();
        return $error_array['message'];
    }
    $sql3 = "CREATE TABLE `bill_carrier_sdr` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `carrier_id` varchar(30) DEFAULT NULL,
  `carrier_name` varchar(100) DEFAULT NULL,
  `account_id` varchar(30) DEFAULT NULL,
  `currency_id` int(11) DEFAULT NULL,
  `currency_name` varchar(20) DEFAULT NULL,
  `account_currency_id` int(11) DEFAULT NULL,
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
) ENGINE=InnoDB DEFAULT CHARSET=latin1 ROW_FORMAT=COMPACT;
";


    $query3 = $ci->db->query($sql3);
    if (!$query3) {
        $error_array = $ci->db->error();
        return $error_array['message'];
    }
    $sql4 = "CREATE TABLE `bill_customer_priceplan` (
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
  `updated_dt` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp(),
  `priceplan_id` varchar(30) DEFAULT NULL,
  `status_message` text DEFAULT NULL,
  `monthly_charges_day` smallint(6) DEFAULT 1,
  `billing_day` smallint(6) DEFAULT 1,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=latin1 ROW_FORMAT=COMPACT;";

    $query4 = $ci->db->query($sql4);
    if (!$query4) {
        $error_array = $ci->db->error();
        return $error_array['message'];
    }
    $sql5 = "CREATE TABLE `bill_customerpricelist` (
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
  `updated_dt` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `price_id` (`item_id`,`customer_account_id`,`record_type`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 ROW_FORMAT=COMPACT;
";

    $query5 = $ci->db->query($sql5);
    if (!$query5) {
        $error_array = $ci->db->error();
        return $error_array['message'];
    }
    $sql6 = "CREATE TABLE `bill_email_templates` (
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
) ENGINE=InnoDB DEFAULT CHARSET=latin1 ROW_FORMAT=COMPACT;";

    $query6 = $ci->db->query($sql6);
    if (!$query6) {
        $error_array = $ci->db->error();
        return $error_array['message'];
    }
    $sql7 = "CREATE TABLE `bill_invoice` (
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 ROW_FORMAT=COMPACT;";

    $query7 = $ci->db->query($sql7);
    if (!$query7) {
        $error_array = $ci->db->error();
        return $error_array['message'];
    }
    $sql8 = "CREATE TABLE `bill_invoice_config` (
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
) ENGINE=InnoDB DEFAULT CHARSET=latin1 ROW_FORMAT=COMPACT;
";



    $query8 = $ci->db->query($sql8);
    if (!$query8) {
        $error_array = $ci->db->error();
        return $error_array['message'];
    }
    $sql9 = "CREATE TABLE `bill_itemlist` (
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
) ENGINE=InnoDB DEFAULT CHARSET=latin1 ROW_FORMAT=COMPACT;
";

    $query9 = $ci->db->query($sql9);
    if (!$query9) {
        $error_array = $ci->db->error();
        return $error_array['message'];
    }

    $sql10 = "CREATE TABLE `bill_pricelist` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `price_id` varchar(30) DEFAULT NULL,
  `item_id` varchar(30) NOT NULL,
  `currency_id` varchar(30) DEFAULT '',
  `description` varchar(250) NOT NULL,
  `reguler_charges` enum('EMA','EME','NA') DEFAULT 'NA',
  `free_item` int(4) DEFAULT NULL,
  `charges` double(20,10) DEFAULT 0.0000000000,
  `additional_charges_as` enum('SE','NA') DEFAULT 'NA',
  `additional_charges` double(20,10) DEFAULT 0.0000000000,
  `account_id` varchar(30) NOT NULL,
  `created_by` varchar(30) NOT NULL,
  `updated_by` varchar(30) NOT NULL,
  `created_dt` datetime NOT NULL,
  `updated_dt` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `price_id` (`price_id`,`account_id`,`currency_id`,`item_id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 ROW_FORMAT=COMPACT;
";



    $query10 = $ci->db->query($sql0);
    if (!$query10) {
        $error_array = $ci->db->error();
        return $error_array['message'];
    }



    $sql11 = "  CREATE TABLE `bill_pricelist_customer` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `customer_account_id` varchar(30) DEFAULT NULL,
  `price_id` varchar(30) DEFAULT NULL,
  `item_id` varchar(30) NOT NULL,
  `currency_id` varchar(30) DEFAULT '',
  `description` varchar(250) NOT NULL,
  `reguler_charges` enum('EMA','EME','NA') DEFAULT 'NA',
  `free_item` int(4) DEFAULT NULL,
  `charges` double(20,10) DEFAULT 0.0000000000,
  `additional_charges_as` enum('SE','NA') DEFAULT 'NA',
  `additional_charges` double(20,10) DEFAULT 0.0000000000,
  `account_id` varchar(30) NOT NULL,
  `created_by` varchar(30) NOT NULL,
  `updated_by` varchar(30) NOT NULL,
  `created_dt` datetime NOT NULL,
  `updated_dt` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `price_id` (`price_id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 ROW_FORMAT=COMPACT;

    
    ";

    $query11 = $ci->db->query($sql11);
    if (!$query11) {
        $error_array = $ci->db->error();
        return $error_array['message'];
    }

    $sql12 = "   CREATE TABLE `bill_priceplan` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `priceplan_id` varchar(20) NOT NULL,
  `priceplan_name` varchar(250) NOT NULL,
  `account_id` varchar(30) NOT NULL,
  `created_by` varchar(30) NOT NULL,
  `updated_by` varchar(30) NOT NULL,
  `created_dt` datetime NOT NULL,
  `updated_dt` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `currency_id` varchar(30) DEFAULT NULL,
  `status` enum('0','1') DEFAULT '1',
  PRIMARY KEY (`id`),
  UNIQUE KEY `price_plan_id` (`priceplan_id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 ROW_FORMAT=COMPACT;
    ";

    $query12 = $ci->db->query($sql12);
    if (!$query12) {
        $error_array = $ci->db->error();
        return $error_array['message'];
    }


    $sql13 = "  CREATE TABLE `bill_priceplan_item` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `priceplan_item_id` varchar(20) NOT NULL,
  `priceplan_id` varchar(20) NOT NULL,
  `item_id` varchar(20) NOT NULL,
  `price_id` varchar(20) NOT NULL,
  `account_id` varchar(30) NOT NULL,
  `created_by` varchar(30) NOT NULL,
  `updated_by` varchar(30) NOT NULL,
  `created_dt` datetime NOT NULL,
  `updated_dt` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `price_plan_item_id` (`priceplan_item_id`) USING BTREE,
  UNIQUE KEY `itemplan_key` (`priceplan_id`,`item_id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 ROW_FORMAT=COMPACT;";

    $query13 = $ci->db->query($sql13);
    if (!$query13) {
        $error_array = $ci->db->error();
        return $error_array['message'];
    }


    $sql14 = "  CREATE TABLE `bill_smtp_config` (
  `id` bigint(11) NOT NULL AUTO_INCREMENT,
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
) ENGINE=InnoDB DEFAULT CHARSET=latin1 ROW_FORMAT=COMPACT;";

    $query14 = $ci->db->query($sql14);
    if (!$query14) {
        $error_array = $ci->db->error();
        return $error_array['message'];
    }



    $query20 = $ci->db->query($sql20);
    if (!$query10) {
        $error_array = $ci->db->error();
        return $error_array['message'];
    }
    $sql11 = "INSERT INTO bill_services VALUES ('1', 'VOICESERVICE', 'Voice services','','','','');
INSERT INTO bill_services VALUES ('2', 'INTERNET', 'Internet services','','','','');
INSERT INTO bill_services VALUES ('3', 'EQUIPMENT', 'Equipment','','','','');
INSERT INTO bill_services VALUES ('4', 'OTHERSERVICES', 'Other Services','','','','');
";

    $query11 = $ci->db->query($sql11);
    if (!$query11) {
        $error_array = $ci->db->error();
        return $error_array['message'];
    }
    $sql12 = "INSERT INTO bill_itemlist VALUES ('1', 'VOICESERVICE', 'EXTEN', 'Extension', 'Extension','','','','');
INSERT INTO bill_itemlist VALUES ('2', 'VOICESERVICE', 'VOICEMAIL', 'Voicemail', 'Voicemail','','','','');
INSERT INTO bill_itemlist VALUES ('3', 'VOICESERVICE', 'RINGGROUP', 'RingGroup', 'RingGroup','','','','');
INSERT INTO bill_itemlist VALUES ('4', 'VOICESERVICE', 'TIMECONDITION', 'Time Routing', 'Time Routing','','','','');
INSERT INTO bill_itemlist VALUES ('5', 'VOICESERVICE', 'IVR', 'IVR', 'IVR','','','','');
INSERT INTO bill_itemlist VALUES ('6', 'VOICESERVICE', 'ANNOUNCEMENT', 'Announcement', 'Announcement','','','','');
INSERT INTO bill_itemlist VALUES ('7', 'VOICESERVICE', 'QUEUE', 'Callcenter', 'Callcenter','','','','');
INSERT INTO bill_itemlist VALUES ('8', 'VOICESERVICE', 'CONFERENCEBRIDGE', 'Conference Bridge', 'Conference Bridge','','','','');
INSERT INTO bill_itemlist VALUES ('9', 'VOICESERVICE', 'QUEUEREPORT', 'CallCenter Wallboard', 'CallCenter Wallboard','','','','');
INSERT INTO bill_itemlist VALUES ('10', 'VOICESERVICE', 'MOBILEAPP', 'Mobile Application', 'Mobile Application','','','','');
INSERT INTO bill_itemlist VALUES ('11', 'VOICESERVICE', 'SPEEDDIAL', 'Speed Dial', 'Speed Dial','','','','');
INSERT INTO bill_itemlist VALUES ('12', 'VOICESERVICE', 'PINSERVICE', 'PIn Service', 'PIn Service','','','','');
INSERT INTO bill_itemlist VALUES ('13', 'VOICESERVICE', 'IPTRUNK', 'IP Trunk', 'IP Trunk','','','','');
INSERT INTO bill_itemlist VALUES ('14', 'INTERNET', 'FIBER', 'Fibre', 'Fibre','','','','');
INSERT INTO bill_itemlist VALUES ('15', 'INTERNET', 'INTERNET', 'Internet', 'Internet','','','','');
INSERT INTO bill_itemlist VALUES ('16', 'EQUIPMENT', 'IPPHONE', 'IP Phones', 'IP Phones','','','','');
INSERT INTO bill_itemlist VALUES ('17', 'EQUIPMENT', 'ROUTER', 'Router', 'Router','','','','');
INSERT INTO bill_itemlist VALUES ('18', 'EQUIPMENT', 'SWITCH', 'Switch', 'Switch','','','','');
INSERT INTO bill_itemlist VALUES ('19', 'EQUIPMENT', 'OTHEREQU', 'Other Equipent Charges', 'Other Equipent Charges','','','','');
INSERT INTO bill_itemlist VALUES ('20', 'EQUIPMENT', 'CABLE', 'Cabling', 'Cabling','','','','');
INSERT INTO bill_itemlist VALUES ('21', 'VOICESERVICE', 'OTHERVOICE', 'Voice Other Charges', 'Voice Other Charges','','','','');
INSERT INTO bill_itemlist VALUES ('22', 'OTHERSERVICES', 'CALOUTS', 'Call outs', 'Call outs','','','','');
INSERT INTO bill_itemlist VALUES ('23', 'OTHERSERVICES', 'TRAINING', 'Training', 'Training','','','','');
INSERT INTO bill_itemlist VALUES ('24', 'OTHERSERVICES', 'SUPPORT', 'Support SLA', 'Support SLA','','','','');
INSERT INTO bill_itemlist VALUES ('25', 'OTHERSERVICES', 'DIDPROTING', 'Number protability', 'Number protability','','','','');
INSERT INTO bill_itemlist VALUES ('26', 'VOICESERVICE', 'DID', 'DID number', 'DID number','','','','');
INSERT INTO bill_itemlist VALUES ('27', 'VOICESERVICE', 'OUT', 'Outbound Minute', 'Outbound Minute','','','','');
INSERT INTO bill_itemlist VALUES ('28', 'VOICESERVICE', 'IN', 'Inbound Minute', 'Inbound Minute','','','','');
";
    $query12 = $ci->db->query($sql12);
    if (!$query12) {
        $error_array = $ci->db->error();
        return $error_array['message'];
    }

    return true;
}

function plugin_Billing_uninstall() {
    return true;
}

?>