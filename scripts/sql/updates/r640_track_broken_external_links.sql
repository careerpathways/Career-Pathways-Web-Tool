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

