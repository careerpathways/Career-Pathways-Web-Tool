
/*Data for the table `admin_module` */

insert into `admin_module` (`id`,`name`,`friendly_name`,`page_title`,`active`,`order`) values (1,'users','Edit Users','Users',1,100);
insert into `admin_module` (`id`,`name`,`friendly_name`,`page_title`,`active`,`order`) values (2,'schools','Configure Schools','Schools',1,90);
insert into `admin_module` (`id`,`name`,`friendly_name`,`page_title`,`active`,`order`) values (3,'drawings','Drawings','Drawings',1,10);
insert into `admin_module` (`id`,`name`,`friendly_name`,`page_title`,`active`,`order`) values (6,'news','Site News','Site News',1,110);
insert into `admin_module` (`id`,`name`,`friendly_name`,`page_title`,`active`,`order`) values (7,'integrating','Embedding Instructions','Embedding Instructions',1,120);
insert into `admin_module` (`id`,`name`,`friendly_name`,`page_title`,`active`,`order`) values (8,'schoolcolors','School Colors','School Colors',1,80);
insert into `admin_module` (`id`,`name`,`friendly_name`,`page_title`,`active`,`order`) values (9,'help','Help','Help',1,200);

/*Data for the table `admin_user_levels` */

insert into `admin_user_levels` (`level`,`name`) values (16,'Staff');
insert into `admin_user_levels` (`level`,`name`) values (64,'School Admin');
insert into `admin_user_levels` (`level`,`name`) values (96,'Webmaster');
insert into `admin_user_levels` (`level`,`name`) values (127,'State Admin');

/*Data for the table `admin_level_module` */

insert into `admin_level_module` (`module_id`,`level`) values (1,64);
insert into `admin_level_module` (`module_id`,`level`) values (2,127);
insert into `admin_level_module` (`module_id`,`level`) values (3,16);
insert into `admin_level_module` (`module_id`,`level`) values (6,127);
insert into `admin_level_module` (`module_id`,`level`) values (7,96);
insert into `admin_level_module` (`module_id`,`level`) values (8,64);
insert into `admin_level_module` (`module_id`,`level`) values (9,16);

/*Data for the table `email_text` */

insert into `email_text` (`id`,`description`,`sender`,`recipient`,`subject`,`emailbody`) values ('forgot_password','This email gets sent to people when they fill out the \"Forgot Password\" link on the admin site.','##WEBSITE_EMAIL##','##EMAIL##','[Oregon CTE Pathways] Forgot Password','Click on the link below to log into your account temporarily. You will need to change your password as soon as you log in, as this link will only work once.\r\n\r\nThis email was sent because you or someone else filled out the Forgot Password form on the Oregon CT Pathways website. If you have received this email in error, please ignore it. Your current password will still be valid.\r\n\r\n##LOGIN_LINK##\r\n\r\nIf you cannot click the link above for any reason, you can also log into the site using the temporary password below.\r\n\r\nhttp://oregon.ctepathways.org\r\n##EMAIL##\r\n##PASSWORD##\r\n\r\nSincerely,\r\n\r\nAaron Parecki\r\n');
insert into `email_text` (`id`,`description`,`sender`,`recipient`,`subject`,`emailbody`) values ('help_request','This email is sent to help@ctepathways.org when a user fills out the help form on the website.','##EMAIL##','help@ctepathways.org','[Oregon CT Pathways] ##SUBJECT##','##BODY##');
insert into `email_text` (`id`,`description`,`sender`,`recipient`,`subject`,`emailbody`) values ( 'svn_update','This email is sent to the developers list when the dev server is updated.','svn@ctepathways.org','developers@ctepathways.org','dev server updated','##BODY##');

/*Data for the table `email_variables` */

insert into `email_variables` (`email_id`,`variable`,`description`) values ('forgot_password','LOGIN_LINK','This will be a link they can click to log them in without typing in their email address or password.');
insert into `email_variables` (`email_id`,`variable`,`description`) values ('forgot_password','EMAIL','The user\'s email address they use to log in to their admin account.');
insert into `email_variables` (`email_id`,`variable`,`description`) values ('forgot_password','PASSWORD','An automatically-generated password that can be used to log in once.');
insert into `email_variables` (`email_id`,`variable`,`description`) values ('help_request','BODY','The body of the message the user entered into the form.');
insert into `email_variables` (`email_id`,`variable`,`description`) values ('help_request','EMAIL','The user\'s email address.');
insert into `email_variables` (`email_id`,`variable`,`description`) values ('help_request','SUBJECT','The user-provided subject line.');
insert into `email_variables` (`email_id`,`variable`,`description`) values ('svn_update','BODY','The body of the message');

/*Data for the table `types` */

insert into `types` (`id`,`description`,`family`) values (1,'Nursing','program');
insert into `types` (`id`,`description`,`family`) values (2,'Vocational','program');
insert into `types` (`id`,`description`,`family`) values (3,'Associates','program');
insert into `types` (`id`,`description`,`family`) values (4,'High School','program');
insert into `types` (`id`,`description`,`family`) values (5,'Transfer','program');
