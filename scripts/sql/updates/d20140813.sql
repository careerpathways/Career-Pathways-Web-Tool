/* Relies on LL t49. Please run d20141014_add_column_to_program_table.sql before this file. */
/* LL t39 ***********BEGIN**************/
INSERT INTO admin_module
        (name,               friendly_name,       page_title,             active, `order`)
  VALUES('ap_name_settings', 'AP Name Settings', 'Edit AP Name Settings', TRUE,    1300);
INSERT INTO admin_level_module (module_id, `level`) VALUES(LAST_INSERT_ID(),127);
/* LL t39 ***********END**************/