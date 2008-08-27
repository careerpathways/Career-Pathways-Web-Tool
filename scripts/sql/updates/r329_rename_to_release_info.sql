UPDATE admin_module SET friendly_name="Edit Resources", page_title="Edit Resources" WHERE name="news";
ALTER TABLE `news` ADD COLUMN `category` VARCHAR(50) NULL after `date`;
UPDATE admin_module SET friendly_name="Organization Colors", page_title="Organization Colors" WHERE name="schoolcolors";
UPDATE admin_module SET friendly_name="Configure Organizations", page_title="Organizations" WHERE name="schools";
DELETE FROM admin_module WHERE name='sitenews';
DELETE FROM admin_module WHERE name='help';
INSERT INTO admin_module (id, name, friendly_name, page_title, active, order) VALUES (15, 'dashboard', 'Dashboard', 'Dashboard', 1, 1);
INSERT INTO admin_level_module (module_id, level) VALUES (15, 16);
