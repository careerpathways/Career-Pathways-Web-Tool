--
-- Table structure for table `admin_level_module`
--

DROP TABLE IF EXISTS `admin_level_module`;
CREATE TABLE `admin_level_module` (
  `module_id` int(11) NOT NULL,
  `level` int(11) NOT NULL,
  PRIMARY KEY  (`module_id`,`level`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Table structure for table `admin_module`
--

DROP TABLE IF EXISTS `admin_module`;
CREATE TABLE `admin_module` (
  `id` int(11) NOT NULL auto_increment,
  `name` varchar(50) default NULL,
  `friendly_name` varchar(50) default NULL,
  `page_title` varchar(50) default NULL,
  `active` tinyint(4) default NULL,
  `order` int(11) default NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=7 DEFAULT CHARSET=latin1;

--
-- Table structure for table `admin_user_levels`
--

DROP TABLE IF EXISTS `admin_user_levels`;
CREATE TABLE `admin_user_levels` (
  `level` int(11) NOT NULL default '0',
  `name` varchar(50) default NULL,
  PRIMARY KEY  (`level`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Table structure for table `color_schemes`
--

DROP TABLE IF EXISTS `color_schemes`;
CREATE TABLE `color_schemes` (
  `id` int(11) NOT NULL auto_increment,
  `school_id` int(11) default NULL,
  `hex` varchar(6) default NULL,
  `files_generated` tinyint(1) NOT NULL default '0',
  PRIMARY KEY  (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=141 DEFAULT CHARSET=latin1;

--
-- Table structure for table `connections`
--

DROP TABLE IF EXISTS `connections`;
 CREATE TABLE `connections` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `source_object_id` int(10) unsigned NOT NULL,
  `destination_object_id` int(10) unsigned NOT NULL,
  `num_segments` int(2) unsigned NOT NULL default '3',
  `color` char(6) NOT NULL,
  `source_axis` char(1) NOT NULL default 'x',
  `source_side` char(1) NOT NULL default 'n',
  `source_position` tinyint(3) unsigned NOT NULL default '50',
  `destination_side` char(1) NOT NULL default 'n',
  `destination_position` tinyint(3) unsigned NOT NULL default '50',
  `created` datetime NOT NULL,
  `modified` timestamp NOT NULL default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=32 DEFAULT CHARSET=latin1;

--
-- Table structure for table `drawing_main`
--

DROP TABLE IF EXISTS `drawing_main`;
CREATE TABLE `drawing_main` (
  `id` int(11) NOT NULL auto_increment,
  `school_id` int(11) default NULL,
  `name` varchar(255) default NULL,
  `tagline` varchar(100) default NULL,
  `tagline_color_id` varchar(6) default NULL,
  `code` varchar(30) default NULL,
  `rendered_html` text,
  `date_created` datetime default NULL,
  `last_modified` datetime default NULL,
  `created_by` int(11) default NULL,
  `last_modified_by` int(11) default NULL,
  PRIMARY KEY  (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=195 DEFAULT CHARSET=latin1;

--
-- Table structure for table `drawings`
--

DROP TABLE IF EXISTS `drawings`;
CREATE TABLE `drawings` (
  `id` int(11) NOT NULL auto_increment,
  `parent_id` int(11) default NULL,
  `version_num` int(11) default NULL,
  `published` tinyint(1) NOT NULL default '0',
  `frozen` tinyint(1) default '0',
  `note` varchar(255) default NULL,
  `date_created` datetime default NULL,
  `last_modified` datetime default NULL,
  `created_by` int(11) default NULL,
  `last_modified_by` int(11) default NULL,
  `deleted` tinyint(1) NOT NULL default '0',
  PRIMARY KEY  (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=345 DEFAULT CHARSET=latin1;

--
-- Table structure for table `email_text`
--

DROP TABLE IF EXISTS `email_text`;
CREATE TABLE `email_text` (
  `id` varchar(50) NOT NULL default '',
  `description` text,
  `sender` varchar(255) default NULL,
  `recipient` varchar(255) default NULL,
  `subject` varchar(255) default NULL,
  `emailbody` text,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Table structure for table `email_variables`
--

DROP TABLE IF EXISTS `email_variables`;
CREATE TABLE `email_variables` (
  `email_id` varchar(50) default NULL,
  `variable` varchar(100) default NULL,
  `description` text
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Table structure for table `news`
--

DROP TABLE IF EXISTS `news`;
CREATE TABLE `news` (
  `id` int(11) NOT NULL auto_increment,
  `date` datetime default NULL,
  `user_id` int(11) default NULL,
  `caption` varchar(255) default NULL,
  `text` text,
  `active` tinyint(1) NOT NULL default '1',
  PRIMARY KEY  (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=latin1;

--
-- Table structure for table `objects`
--

DROP TABLE IF EXISTS `objects`;
CREATE TABLE `objects` (
  `id` int(11) NOT NULL auto_increment,
  `drawing_id` int(11) default NULL,
  `content` text,
  `date` datetime default NULL,
  PRIMARY KEY  (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=5168 DEFAULT CHARSET=latin1;

--
-- Table structure for table `schools`
--

DROP TABLE IF EXISTS `schools`;
CREATE TABLE `schools` (
  `id` int(11) NOT NULL auto_increment,
  `school_name` varchar(100) default NULL,
  `school_abbr` varchar(50) default NULL,
  `school_website` varchar(255) default NULL,
  `school_logo` varchar(20) default NULL,
  PRIMARY KEY  (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=22 DEFAULT CHARSET=latin1;

--
-- Table structure for table `school_options`
--

DROP TABLE IF EXISTS `school_options`;
CREATE TABLE `school_options` (
	`school_id` int(11) NOT NULL,
	`show_banner` tinyint(1) NOT NULL default '1',
	PRIMARY KEY  (`school_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
CREATE TABLE `users` (
  `id` int(11) NOT NULL auto_increment,
  `school_id` int(11) default NULL,
  `first_name` varchar(50) default NULL,
  `last_name` varchar(50) default NULL,
  `email` varchar(255) default NULL,
  `password` varchar(50) default NULL,
  `temp_password` varchar(50) default NULL,
  `last_logon` datetime default NULL,
  `last_logon_ip` varchar(20) default NULL,
  `user_active` tinyint(4) default NULL,
  `user_level` tinyint(4) default NULL,
  `last_module` varchar(50) default NULL,
  PRIMARY KEY  (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=111 DEFAULT CHARSET=latin1;

--
-- Table structure for table `object_type`
--

DROP TABLE IF EXISTS `object_type`;
CREATE TABLE `object_type` (
  `id` int(11) NOT NULL auto_increment,
  `object_id` int(11) NOT NULL,
  `type_id` int(11) NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=5 DEFAULT CHARSET=latin1;

--
-- Table structure for table `types`
--

DROP TABLE IF EXISTS `types`;
CREATE TABLE `types` (
  `id` int(11) NOT NULL auto_increment,
  `description` varchar(50) NOT NULL,
  `family` varchar(25) NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=5 DEFAULT CHARSET=latin1;

