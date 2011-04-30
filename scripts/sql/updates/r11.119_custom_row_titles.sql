ALTER TABLE `post_row`
ADD COLUMN `title` VARCHAR(255) NOT NULL DEFAULT '' AFTER `drawing_id`;

CREATE TABLE `post_default_row` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `school_id` int(10) unsigned NOT NULL,
  `title` varchar(255) NOT NULL DEFAULT '',
  `row_type` enum('prereq','term','electives','unlabeled') DEFAULT NULL,
  `row_year` enum('1','2','3','4','5','6','7','8','9','10','11','12') DEFAULT NULL,
  `row_term` enum('M','F','W','S','U') DEFAULT NULL,
  `row_qtr` tinyint(4) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
);
