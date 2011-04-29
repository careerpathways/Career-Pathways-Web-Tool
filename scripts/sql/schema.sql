
CREATE TABLE `admin_level_module` (
  `module_id` int(11) NOT NULL,
  `level` int(11) NOT NULL,
  PRIMARY KEY  (`module_id`,`level`)
);

CREATE TABLE `admin_module` (
  `id` int(11) NOT NULL auto_increment,
  `name` varchar(50) default NULL,
  `friendly_name` varchar(50) default NULL,
  `page_title` varchar(50) default NULL,
  `active` tinyint(4) default NULL,
  `order` int(11) default NULL,
  PRIMARY KEY  (`id`)
);

CREATE TABLE `admin_user_levels` (
  `level` int(11) NOT NULL default '0',
  `name` varchar(50) default NULL,
  PRIMARY KEY  (`level`)
);

CREATE TABLE `color_schemes` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `school_id` INT(11) DEFAULT NULL,
  `hex` VARCHAR(6) DEFAULT NULL,
  `files_generated` TINYINT(1) NOT NULL DEFAULT '0',
  `num_roadmaps` INT(11) NOT NULL DEFAULT '0',
  PRIMARY KEY  (`id`)
);

CREATE TABLE `connections` (
  `id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `source_object_id` INT(10) UNSIGNED NOT NULL,
  `destination_object_id` INT(10) UNSIGNED NOT NULL,
  `num_segments` INT(2) UNSIGNED NOT NULL,
  `color` CHAR(6) NOT NULL,
  `created` DATETIME NOT NULL,
  `modified` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `source_axis` CHAR(1) NOT NULL,
  `source_side` CHAR(1) NOT NULL DEFAULT 'n',
  `source_position` TINYINT(3) UNSIGNED NOT NULL DEFAULT '50',
  `destination_side` CHAR(1) NOT NULL DEFAULT 'n',
  `destination_position` TINYINT(3) UNSIGNED NOT NULL DEFAULT '50',
  PRIMARY KEY  (`id`)
);

CREATE TABLE `counties` (
  `id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `county` VARCHAR(50) DEFAULT NULL,
  PRIMARY KEY  (`id`)
);

CREATE TABLE `drawing_main` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `school_id` INT(11) DEFAULT NULL,
  `skillset_id` INT(11) NOT NULL DEFAULT '0',
  `program_id` INT(11) NOT NULL DEFAULT '0',
  `name` VARCHAR(255) DEFAULT NULL,
  `tagline` VARCHAR(100) DEFAULT NULL,
  `tagline_color_id` VARCHAR(6) DEFAULT NULL,
  `code` VARCHAR(255) DEFAULT NULL,
  `date_created` DATETIME DEFAULT NULL,
  `last_modified` DATETIME DEFAULT NULL,
  `created_by` INT(11) DEFAULT NULL,
  `last_modified_by` INT(11) DEFAULT NULL,
  PRIMARY KEY  (`id`)
);

CREATE TABLE `drawings` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `parent_id` INT(11) DEFAULT NULL,
  `version_num` INT(11) DEFAULT NULL,
  `published` TINYINT(1) NOT NULL DEFAULT '0',
  `frozen` TINYINT(1) DEFAULT '0',
  `note` VARCHAR(255) DEFAULT NULL,
  `date_created` DATETIME DEFAULT NULL,
  `last_modified` DATETIME DEFAULT NULL,
  `created_by` INT(11) DEFAULT NULL,
  `last_modified_by` INT(11) DEFAULT NULL,
  `deleted` TINYINT(1) NOT NULL DEFAULT '0',
  PRIMARY KEY  (`id`)
);

CREATE TABLE `email_text` (
  `id` VARCHAR(50) NOT NULL DEFAULT '',
  `description` TEXT,
  `sender` VARCHAR(255) DEFAULT NULL,
  `recipient` VARCHAR(255) DEFAULT NULL,
  `bcc` VARCHAR(255) DEFAULT NULL,
  `subject` VARCHAR(255) DEFAULT NULL,
  `emailbody` TEXT,
  PRIMARY KEY  (`id`)
);

CREATE TABLE `email_variables` (
  `email_id` VARCHAR(50) DEFAULT NULL,
  `variable` VARCHAR(100) DEFAULT NULL,
  `description` TEXT
);

CREATE TABLE `external_link_detail` (
  `id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `type` VARCHAR(30) NOT NULL,
  `date` DATETIME NOT NULL,
  `drawing_id` INT(11) NOT NULL,
  `url` VARCHAR(255) NOT NULL,
  `remote_addr` VARCHAR(20) DEFAULT NULL,
  `user_agent` VARCHAR(255) DEFAULT NULL,
  PRIMARY KEY  (`id`)
);

CREATE TABLE `external_link_errors` (
  `id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `date` DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00',
  `type` ENUM('roadmap','post','postview') NOT NULL,
  `request_uri` VARCHAR(255) NOT NULL DEFAULT '',
  `query_string` VARCHAR(255) NOT NULL DEFAULT '',
  `external_url` VARCHAR(255) NOT NULL DEFAULT '',
  `drawing_id` INT(11) NOT NULL DEFAULT '0',
  `version_id` INT(11) NOT NULL DEFAULT '0',
  `version` INT(11) NOT NULL DEFAULT '0',
  `drawing_code` VARCHAR(255) NOT NULL DEFAULT '',
  `counter` INT(10) UNSIGNED NOT NULL DEFAULT '0',
  PRIMARY KEY  (`id`)
);

CREATE TABLE `external_link_exclude` (
  `id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `pattern` VARCHAR(255) NOT NULL,
  PRIMARY KEY  (`id`)
);

CREATE TABLE `external_links` (
  `id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `type` VARCHAR(30) NOT NULL,
  `drawing_id` INT(11) NOT NULL,
  `url` VARCHAR(255) NOT NULL,
  `primary` TINYINT(4) NOT NULL DEFAULT '0',
  `last_seen` DATETIME NOT NULL,
  `counter` INT(10) UNSIGNED NOT NULL DEFAULT '0',
  PRIMARY KEY  (`id`)
);

CREATE TABLE `guest_logins` (
  `id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `date` DATETIME DEFAULT NULL,
  `first_name` VARCHAR(30) DEFAULT NULL,
  `last_name` VARCHAR(30) DEFAULT NULL,
  `email` VARCHAR(100) DEFAULT NULL,
  `school` VARCHAR(200) DEFAULT NULL,
  `referral` VARCHAR(200) DEFAULT NULL,
  `download` TINYINT(4) NOT NULL DEFAULT '0',
  `ipaddr` VARCHAR(20) DEFAULT NULL,
  PRIMARY KEY  (`id`)
);

CREATE TABLE `helprequests` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `date` DATETIME DEFAULT NULL,
  `user_id` INT(11) DEFAULT NULL,
  `subject` VARCHAR(255) DEFAULT NULL,
  `message` TEXT,
  PRIMARY KEY  (`id`)
);

CREATE TABLE `hs_affiliations` (                          
	`id` int(10) unsigned NOT NULL auto_increment,          
	`cc_id` int(10) unsigned NOT NULL default '0',          
	`hs_id` int(10) unsigned NOT NULL default '0',          
	PRIMARY KEY  (`id`)                                     
);

CREATE TABLE `login_history` (
  `id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `date` DATETIME DEFAULT NULL,
  `user_id` INT(11) DEFAULT NULL,
  `name` VARCHAR(50) DEFAULT NULL,
  `ip_address` VARCHAR(20) DEFAULT NULL,
  `user_agent` VARCHAR(255) DEFAULT NULL,
  PRIMARY KEY  (`id`)
);

CREATE TABLE `logs` (
  `id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `remote_addr` VARCHAR(20) DEFAULT NULL,
  `date` DATETIME DEFAULT NULL,
  `url` VARCHAR(255) DEFAULT NULL,
  `drawing_code` VARCHAR(255) DEFAULT NULL,
  `drawing_id` INT(11) DEFAULT NULL,
  `status_code` INT(11) DEFAULT NULL,
  `bytes_transferred` INT(11) DEFAULT NULL,
  `referer` VARCHAR(255) DEFAULT NULL,
  `user_agent` VARCHAR(255) DEFAULT NULL,
  PRIMARY KEY  (`id`)
);

CREATE TABLE `logs_processed` (
  `id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `filename` VARCHAR(50) DEFAULT NULL,
  `date_processed` DATETIME DEFAULT NULL,
  PRIMARY KEY  (`id`)
);

CREATE TABLE `news` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `date` DATE DEFAULT NULL,
  `category` VARCHAR(50) DEFAULT NULL,
  `user_id` INT(11) DEFAULT NULL,
  `caption` VARCHAR(255) DEFAULT NULL,
  `text` TEXT,
  `active` TINYINT(1) NOT NULL DEFAULT '1',
  `sort_index` INT(11) DEFAULT NULL,
  PRIMARY KEY  (`id`)
);

CREATE TABLE `objects` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `drawing_id` INT(11) DEFAULT NULL,
  `content` MEDIUMTEXT,
  `date` DATETIME DEFAULT NULL,
  `color` VARCHAR(6) NOT NULL DEFAULT '333333',
  PRIMARY KEY  (`id`)
);

CREATE TABLE `olmis_codes` (
  `olmis_id` VARCHAR(20) NOT NULL,
  `job_title` VARCHAR(255) DEFAULT NULL,
  PRIMARY KEY  (`olmis_id`)
);

CREATE TABLE `olmis_links` (
  `id` INT(10) UNSIGNED NOT NULL DEFAULT '0',
  `drawing_id` INT(10) UNSIGNED DEFAULT NULL,
  `olmis_id` VARCHAR(20) DEFAULT NULL,
  `enabled` TINYINT(4) NOT NULL DEFAULT '0'
);

CREATE TABLE `oregon_skillsets` (
  `id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `title` VARCHAR(255) NOT NULL DEFAULT '',
  PRIMARY KEY  (`id`)
);

CREATE TABLE `post_cell` (
  `id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `drawing_id` INT(10) UNSIGNED DEFAULT NULL,
  `row_num` INT(11) DEFAULT NULL,
  `row_id` INT(11) DEFAULT NULL,
  `col_id` INT(11) DEFAULT NULL,
  `content` TEXT NOT NULL,
  `href` TEXT NOT NULL,
  `legend` TEXT,
  `course_credits` TINYINT(4) NOT NULL DEFAULT '0',
  `course_subject` VARCHAR(10) DEFAULT NULL,
  `course_number` VARCHAR(10) DEFAULT NULL,
  `course_title` VARCHAR(255) DEFAULT NULL,
  `course_description` TEXT,
  `course_description_cachedate` DATETIME DEFAULT NULL,
  `edit_txn` int(10) unsigned NOT NULL DEFAULT '0',
  `edit_action` enum('add','delete') DEFAULT NULL,
  PRIMARY KEY  (`id`)
);

CREATE TABLE `post_col` (
  `id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `drawing_id` INT(10) UNSIGNED DEFAULT NULL,
  `title` VARCHAR(255) DEFAULT NULL,
  `num` INT(11) DEFAULT NULL,
  `edit_txn` int(10) unsigned NOT NULL DEFAULT '0',
  `edit_action` enum('add','delete') DEFAULT NULL,
  PRIMARY KEY  (`id`)
);

CREATE TABLE `post_default_col` (
  `id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `school_id` INT(11) NOT NULL,
  `title` VARCHAR(255) NOT NULL,
  `num` INT(10) UNSIGNED NOT NULL,
  PRIMARY KEY  (`id`)
);

CREATE TABLE `post_drawing_main` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `school_id` INT(11) DEFAULT NULL,
  `skillset_id` INT(11) DEFAULT NULL,
  `program_id` INT(11) NOT NULL DEFAULT '0',
  `name` VARCHAR(255) DEFAULT NULL,
  `code` VARCHAR(255) DEFAULT NULL,
  `date_created` DATETIME DEFAULT NULL,
  `last_modified` DATETIME DEFAULT NULL,
  `created_by` INT(11) DEFAULT NULL,
  `last_modified_by` INT(11) DEFAULT NULL,
  `type` ENUM('HS','CC') DEFAULT NULL,
  PRIMARY KEY  (`id`)
);

CREATE TABLE `post_drawings` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `parent_id` INT(11) DEFAULT NULL,
  `version_num` INT(11) DEFAULT NULL,
  `published` TINYINT(1) NOT NULL DEFAULT '0',
  `frozen` TINYINT(1) DEFAULT '0',
  `note` VARCHAR(255) DEFAULT NULL,
  `footer_text` VARCHAR(255) DEFAULT NULL,
  `footer_link` VARCHAR(255) DEFAULT NULL,
  `sidebar_text_right` VARCHAR(255) DEFAULT NULL,
  `date_created` DATETIME DEFAULT NULL,
  `last_modified` DATETIME DEFAULT NULL,
  `created_by` INT(11) DEFAULT NULL,
  `last_modified_by` INT(11) DEFAULT NULL,
  `deleted` TINYINT(1) NOT NULL DEFAULT '0',
  `num_rows` INT(11) DEFAULT NULL,
  `num_extra_rows` INT(11) DEFAULT NULL,
  `header_text` TEXT,
  `header_link` TEXT,
  `header_state` TINYINT(1) NOT NULL DEFAULT '0',
  `footer_state` TINYINT(1) NOT NULL DEFAULT '1',
  PRIMARY KEY  (`id`)
);

CREATE TABLE `post_legend` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `graphic` VARCHAR(255) NOT NULL,
  `text` VARCHAR(255) NOT NULL,
  PRIMARY KEY  (`id`)
);

CREATE TABLE `post_row` (
  `id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `drawing_id` INT(10) UNSIGNED NOT NULL,
  `title` VARCHAR(255) DEFAULT NULL,
  `row_type` ENUM('prereq','term','electives','unlabeled') DEFAULT NULL,
  `row_year` ENUM('1','2','3','4','5','6','7','8','9','10','11','12') DEFAULT NULL,
  `row_term` ENUM('M','F','W','S','U') DEFAULT NULL,
  `row_qtr` TINYINT(4) NOT NULL DEFAULT '0',
  `edit_txn` int(10) unsigned NOT NULL DEFAULT '0',
  `edit_action` enum('add','delete') DEFAULT NULL,
  PRIMARY KEY  (`id`)
);

CREATE TABLE `post_sidebar_options` (
  `id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `type` ENUM('HS','CC') NOT NULL,
  `text` VARCHAR(255) NOT NULL,
  PRIMARY KEY  (`id`)
);

CREATE TABLE `programs` (
  `id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `skillset_id` INT(10) UNSIGNED NOT NULL,
  `title` VARCHAR(200) DEFAULT NULL,
  PRIMARY KEY  (`id`)
);

CREATE TABLE `school_options` (
  `school_id` INT(11) NOT NULL,
  `show_banner` TINYINT(1) NOT NULL DEFAULT '1',
  PRIMARY KEY  (`school_id`)
);

CREATE TABLE `schools` (
  `id` int(11) NOT NULL auto_increment,
  `school_name` varchar(100) default NULL,
  `school_abbr` varchar(50) default NULL,
  `school_website` varchar(255) default NULL,
  `school_addr` varchar(255) default NULL,
  `school_city` varchar(100) default NULL,
  `school_state` varchar(2) default NULL,
  `school_zip` varchar(12) default NULL,
  `school_county` varchar(100) default NULL,
  `school_phone` varchar(20) default NULL,
  `organization_type` enum('HS','CC','Other') default NULL,
  `date_created` datetime default NULL,
  PRIMARY KEY  (`id`)
);

CREATE TABLE `users` (
  `id` int(11) NOT NULL auto_increment,
  `school_id` int(11) default NULL,
  `first_name` varchar(50) default NULL,
  `last_name` varchar(50) default NULL,
  `email` varchar(255) default NULL,
  `job_title` varchar(255) default NULL,     
  `phone_number` varchar(50) default NULL,   
  `password` varchar(50) default NULL,
  `temp_password` varchar(50) default NULL,
  `last_logon` datetime default NULL,
  `last_logon_ip` varchar(20) default NULL,
  `user_active` tinyint(4) default NULL,
  `user_level` tinyint(4) default NULL,
  `last_module` varchar(50) default NULL,
  `new_user` tinyint(1) NOT NULL default '0',  
  `referral` varchar(255) default NULL,        
  `other_school` varchar(255) default NULL,    
  `application_key` varchar(255) default NULL,  
  `approved_by` int(11) default NULL,
  `date_created` datetime default NULL,
  PRIMARY KEY  (`id`)
);

CREATE TABLE `vpost_links` (
  `id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `vid` INT(10) UNSIGNED DEFAULT NULL,
  `post_id` INT(10) UNSIGNED DEFAULT NULL,
  `tab_name` VARCHAR(100) DEFAULT NULL,
  `sort` INT(10) UNSIGNED NOT NULL DEFAULT '0',
  PRIMARY KEY  (`id`)
);

CREATE TABLE `vpost_views` (
  `id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `school_id` INT(11) DEFAULT NULL,
  `code` VARCHAR(40) DEFAULT NULL,
  `name` VARCHAR(200) DEFAULT NULL,
  `date_created` DATETIME DEFAULT NULL,
  `last_modified` DATETIME DEFAULT NULL,
  `created_by` INT(11) DEFAULT NULL,
  `last_modified_by` INT(11) DEFAULT NULL,
  PRIMARY KEY  (`id`)
);


