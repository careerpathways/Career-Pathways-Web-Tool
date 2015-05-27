ALTER TABLE  `drawing_main` ADD  `name_approved` BOOLEAN NOT NULL DEFAULT  '0'
COMMENT  'True: use program_id to get title from programs table. False: use name field in this table.' AFTER `name`
