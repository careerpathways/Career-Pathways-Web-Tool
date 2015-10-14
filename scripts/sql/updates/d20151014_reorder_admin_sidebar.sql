UPDATE admin_module
SET `order`=201
WHERE `friendly_name`="Affiliations";
UPDATE admin_module
SET `order`=202
WHERE `friendly_name`="Configure POST Legend";
UPDATE admin_module
SET `order`=203
WHERE `friendly_name`="POST Settings";
UPDATE admin_module
SET `order`=204
WHERE `friendly_name`="Edit Assurance Criteria";



UPDATE admin_module
SET `order`=301,`friendly_name`="Image Library"
WHERE `name`="asset_manager";
UPDATE admin_module
SET `order`=302
WHERE `friendly_name`="Configure Organizations";
UPDATE admin_module
SET `order`=303
WHERE `friendly_name`="Organization Colors";
UPDATE admin_module
SET `order`=304
WHERE `friendly_name`="Users";

INSERT INTO admin_module
(`name`,`friendly_name`,`page_title`,`active`,`order`)
VALUES
('--','--','--',1,400);

UPDATE admin_module
SET `order`=401
WHERE `friendly_name`="Edit Resources";
UPDATE admin_module
SET `order`=402
WHERE `friendly_name`="Email Templates";
UPDATE admin_module
SET `order`=403,`friendly_name`="Update APN Lists",`page_title`="Update APN Lists"
WHERE `name`="ap_name_settings";
UPDATE admin_module
SET `order`=404
WHERE `friendly_name`="Guest Logins";
UPDATE admin_module
SET `order`=405
WHERE `friendly_name`="Stats";
UPDATE admin_module
SET `active`=0,`order`=499
WHERE `friendly_name`="Configure Permissions";
