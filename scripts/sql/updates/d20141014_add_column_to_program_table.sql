/* LL t49 ***********BEGIN**************/
ALTER TABLE  `programs`
ADD  `use_for_roadmap_drawing` BOOLEAN NOT NULL COMMENT  'true if this program name is used by roadmap drawings' AFTER  `title` ,
ADD  `use_for_post_drawing` BOOLEAN NOT NULL COMMENT  'true if this program name is used by post drawings' AFTER  `use_for_roadmap_drawing` ,
ADD  `imported` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT  'timestamp that this row was imported at',
ADD INDEX (  `use_for_roadmap_drawing` ,  `use_for_post_drawing` );

UPDATE `programs` SET `use_for_post_drawing` = 1, `use_for_roadmap_drawing` = 1;
/* LL t49 ***********END**************/