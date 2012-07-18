#ALTER TABLE  `admin_module` ADD  `feature` VARCHAR(255);

INSERT INTO  `admin_module` (
  `id` ,
  `name` ,
  `friendly_name` ,
  `page_title` ,
  `active` ,
  feature,
  `order`)
 VALUES (
  NULL,
  'post_assurance',
  'POST Assurances',
  'POST Assurances',
  '1',
  'post_assurances',
  '40'
);

INSERT INTO `admin_level_module` (
  `module_id` ,
  `level`
  )
VALUES (LAST_INSERT_ID(), '64');