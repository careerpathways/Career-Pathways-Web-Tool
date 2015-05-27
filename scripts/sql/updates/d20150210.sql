/* LL t49 ***********BEGIN**************/
ALTER TABLE  `programs`
ADD  `use_for_roadmap_drawing` BOOLEAN NOT NULL COMMENT  'true if this program name is used by roadmap drawings' AFTER  `title` ,
ADD  `use_for_post_drawing` BOOLEAN NOT NULL COMMENT  'true if this program name is used by post drawings' AFTER  `use_for_roadmap_drawing` ,
ADD  `imported` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT  'timestamp that this row was imported at',
ADD INDEX (  `use_for_roadmap_drawing` ,  `use_for_post_drawing` );

UPDATE `programs` SET `use_for_post_drawing` = 1, `use_for_roadmap_drawing` = 1;
/* LL t49 ***********END**************/

/* Relies on LL t49.*/
/* LL t39 ***********BEGIN**************/
INSERT INTO admin_module
        (name,               friendly_name,       page_title,             active, `order`, feature)
  VALUES('ap_name_settings', 'AP Name Settings', 'Edit AP Name Settings', TRUE,    1300,   'approved_program_name');
INSERT INTO admin_level_module (module_id, `level`) VALUES(LAST_INSERT_ID(),127);
/* LL t39 ***********END**************/

/* LL t75 ***********BEGIN*************/
ALTER TABLE  `programs`
ADD  `imported_uid` INT(10) COMMENT  'user that imported this row'
/* LL t75 ***********END*************/
