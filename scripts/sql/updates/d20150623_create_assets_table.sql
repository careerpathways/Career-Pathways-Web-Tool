CREATE TABLE `assets` (
  `id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `file_name` VARCHAR(255) NOT NULL,
  `date_created` DATETIME DEFAULT NULL,
  `created_by` INT(11) DEFAULT NULL,
  PRIMARY KEY  (`id`)
);

CREATE TABLE `assets_school_ids` (
  `asset_id` INT(10) UNSIGNED NOT NULL,
  `school_id` INT(10) UNSIGNED NOT NULL,
  PRIMARY KEY  (`asset_id`, `school_id`)
);
