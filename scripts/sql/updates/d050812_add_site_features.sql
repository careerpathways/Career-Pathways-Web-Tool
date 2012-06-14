ALTER TABLE  `admin_module` ADD  `feature` VARCHAR(255);
UPDATE  `pathways`.`admin_module` SET  `feature` =  'post_assurances' WHERE  `admin_module`.`id` =25;