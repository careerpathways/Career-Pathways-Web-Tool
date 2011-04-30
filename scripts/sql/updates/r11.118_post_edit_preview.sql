ALTER TABLE post_cell
ADD COLUMN `edit_txn` INT(10) UNSIGNED NOT NULL DEFAULT '0',
ADD COLUMN `edit_action` ENUM('add','delete');

ALTER TABLE post_col
ADD COLUMN `edit_txn` INT(10) UNSIGNED NOT NULL DEFAULT '0',
ADD COLUMN `edit_action` ENUM('add','delete');

ALTER TABLE post_row
ADD COLUMN `edit_txn` INT(10) UNSIGNED NOT NULL DEFAULT '0',
ADD COLUMN `edit_action` ENUM('add','delete');
