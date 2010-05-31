CREATE TABLE `external_link_exclude` (
  `id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `pattern` VARCHAR(255) NOT NULL,
  PRIMARY KEY (`id`)
);

INSERT INTO external_link_exclude VALUES
	(0, '/public.ctepathways.org/'),
	(0, '|file://|'),
	(0, '/\\bdev\\b/');
	