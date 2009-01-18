alter table `pathways`.`schools` add column `school_county` varchar (100)  NULL  after `school_zip`;
insert into `admin_module`(`id`,`name`,`friendly_name`,`page_title`,`active`,`order`) values ( '17','hs_settings','High School Settings','High School Settings','1','20');
insert into `admin_user_levels`(`level`,`name`) values ( '8','High School');
insert into `admin_user_levels`(`level`,`name`) values ( '12','High School Admin');
insert into `admin_level_module`(`module_id`,`level`) values ( '16','8');
insert into `admin_level_module`(`module_id`,`level`) values ( '17','12');
