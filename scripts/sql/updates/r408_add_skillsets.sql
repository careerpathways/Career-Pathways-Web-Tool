CREATE TABLE `oregon_skillsets` (                        
                    `id` int(10) unsigned NOT NULL auto_increment,         
                    `title` varchar(255) NOT NULL default '',              
                    PRIMARY KEY  (`id`)                                    
                  );

ALTER TABLE `drawing_main` ADD COLUMN skillset_id INT NULL AFTER `school_id`;
ALTER TABLE `post_drawing_main` ADD COLUMN skillset_id INT NULL AFTER `school_id`;


insert  into `oregon_skillsets`(`id`,`title`) values
	(1,'Agriculture, Food and Natural Resources'),
	(2,'Arts, Information and Communications'),
	(3,'Business and Management'),
	(4,'Health Services'),
	(5,'Human Resources'),
	(6,'Industrial and Engineering Systems');

