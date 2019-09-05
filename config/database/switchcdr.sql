SET FOREIGN_KEY_CHECKS=0;

-- ----------------------------
-- Table structure for `quick_notes`
-- ----------------------------
DROP TABLE IF EXISTS `quick_notes`;
CREATE TABLE `quick_notes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `note` text NOT NULL,
  `account_id` varchar(30) NOT NULL,
  `account_name` varchar(100) NOT NULL,
  `dt_created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `created_by` varchar(30) DEFAULT NULL,
  `created_by_name` varchar(50) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=156 DEFAULT CHARSET=latin1;

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
) ENGINE=InnoDB AUTO_INCREMENT=28 DEFAULT CHARSET=latin1;

-- ----------------------------
-- Records of ticket_categories
-- ----------------------------
INSERT INTO `ticket_categories` VALUES ('1', '0', 'Network & Operations', 'Y');
INSERT INTO `ticket_categories` VALUES ('2', '0', 'Accounts', 'Y');
INSERT INTO `ticket_categories` VALUES ('4', '0', 'R&D', 'Y');
INSERT INTO `ticket_categories` VALUES ('5', '0', 'Other', 'Y');
INSERT INTO `ticket_categories` VALUES ('6', '1', 'Calling Problems', 'Y');
INSERT INTO `ticket_categories` VALUES ('7', '1', 'Call Records', 'Y');
INSERT INTO `ticket_categories` VALUES ('9', '2', 'Billing', 'Y');
INSERT INTO `ticket_categories` VALUES ('10', '1', 'Low ASR', 'Y');
INSERT INTO `ticket_categories` VALUES ('11', '1', 'Low ACD', 'Y');
INSERT INTO `ticket_categories` VALUES ('12', '1', 'DID Fault', 'Y');
INSERT INTO `ticket_categories` VALUES ('13', '1', 'Incorrect CLI Display', 'Y');
INSERT INTO `ticket_categories` VALUES ('14', '1', 'Voice Break', 'Y');
INSERT INTO `ticket_categories` VALUES ('15', '1', 'One-way Audio', 'Y');
INSERT INTO `ticket_categories` VALUES ('18', '1', 'Capacity', 'Y');
INSERT INTO `ticket_categories` VALUES ('19', '1', 'Change Request', 'Y');
INSERT INTO `ticket_categories` VALUES ('21', '1', 'Rating', 'Y');
INSERT INTO `ticket_categories` VALUES ('22', '1', 'Reliability', 'Y');
INSERT INTO `ticket_categories` VALUES ('23', '1', 'Security', 'Y');
INSERT INTO `ticket_categories` VALUES ('24', '2', 'Payment', 'Y');
INSERT INTO `ticket_categories` VALUES ('25', '3', 'New Order Enquiry', 'Y');

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
  `status` enum('open','closed') NOT NULL DEFAULT 'open',
  `hide_from_customer` enum('Y','N') NOT NULL DEFAULT 'N',
  `created_by` varchar(30) NOT NULL,
  `created_by_name` varchar(30) NOT NULL,
  `create_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`ticket_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- ----------------------------
-- Records of tickets
-- ----------------------------
