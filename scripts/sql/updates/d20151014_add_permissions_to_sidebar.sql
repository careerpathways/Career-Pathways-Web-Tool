INSERT INTO `admin_level_module`
(`module_id`, `level`)
VALUES
((SELECT id FROM `admin_module` WHERE `name`='asset_manager'),12);
