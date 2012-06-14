CREATE TABLE `signature_categories` (
  `id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(255) NOT NULL UNIQUE,
  PRIMARY KEY  (`id`)
);

CREATE TABLE `signature_categories_users` (
  `id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `category_id` INT(10) UNSIGNED NOT NULL,
  `user_id` INT(10) UNSIGNED NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY (`category_id`, `user_id`)
);

INSERT INTO `signature_categories` (name) VALUES ('Secondary Institution');
INSERT INTO `signature_categories` (name) VALUES ('Post-secondary Institution');
INSERT INTO `signature_categories` (name) VALUES ('CTE Director');
INSERT INTO `signature_categories` (name) VALUES ('Workforce Dean');
INSERT INTO `signature_categories` (name) VALUES ('Local Tech Prep Facilitator');
INSERT INTO `signature_categories` (name) VALUES ('Out-of-District Tech Prep Facilitator');

-- ALTER TABLE login_history **change id column to auto_increment**

CREATE TABLE `signatures` (
  `id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `category_id` INT(10) UNSIGNED NOT NULL,
  `vpost_view_id` INT(10) UNSIGNED NOT NULL,
  `user_id` INT(10) UNSIGNED NOT NULL,
  `date_signed` TIMESTAMP NOT NULL,

  PRIMARY KEY (`id`)
);