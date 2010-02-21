ALTER TABLE color_schemes ADD COLUMN num_roadmaps INT(11) NOT NULL DEFAULT 0;
ALTER TABLE objects ADD COLUMN color VARCHAR(6) NOT NULL DEFAULT '333333';
DELETE FROM color_schemes WHERE `hex` = "ffffff";
