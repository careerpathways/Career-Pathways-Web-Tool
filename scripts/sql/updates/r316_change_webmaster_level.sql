update `admin_user_levels` set `level`='32' where `level`='96';
UPDATE users SET user_level=32 WHERE user_level=96;
UPDATE admin_level_module SET level=32 WHERE module_id=7;