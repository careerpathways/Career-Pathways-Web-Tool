

ALTER TABLE `external_links` ADD COLUMN `primary` TINYINT(4) NOT NULL DEFAULT '0' AFTER `url`;


