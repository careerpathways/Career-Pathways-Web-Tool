ALTER TABLE  `connections` ADD  `thickness` TEXT NOT NULL AFTER  `destination_position`;

# Default thickness value is 5
UPDATE `connections` SET `thickness` = '5';
