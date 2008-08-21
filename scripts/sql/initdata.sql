
/*Data for the table `admin_module` */

insert into `admin_module` (`id`,`name`,`friendly_name`,`page_title`,`active`,`order`) values (1,'users','Edit Users','Users',1,100);
insert into `admin_module` (`id`,`name`,`friendly_name`,`page_title`,`active`,`order`) values (2,'schools','Configure Schools','Schools',1,90);
insert into `admin_module` (`id`,`name`,`friendly_name`,`page_title`,`active`,`order`) values (3,'drawings','Drawings','Drawings',1,10);
insert into `admin_module` (`id`,`name`,`friendly_name`,`page_title`,`active`,`order`) values (6,'news','Edit Site News','Edit Site News',1,5);
insert into `admin_module` (`id`,`name`,`friendly_name`,`page_title`,`active`,`order`) values (7,'integrating','Embedding Instructions','Embedding Instructions',1,120);
insert into `admin_module` (`id`,`name`,`friendly_name`,`page_title`,`active`,`order`) values (8,'schoolcolors','School Colors','School Colors',1,80);
insert into `admin_module` (`id`,`name`,`friendly_name`,`page_title`,`active`,`order`) values (9,'help','Help','Help',1,200);
insert into `admin_module` (`id`,`name`,`friendly_name`,`page_title`,`active`,`order`) values (10,'emailcontents','Email Templates','Email Templates',1,105);
insert into `admin_module` (`id`,`name`,`friendly_name`,`page_title`,`active`,`order`) values (11,'guestlogins','Guest Logins','Guest Logins','1','190');
insert into `admin_module` (`id`,`name`,`friendly_name`,`page_title`,`active`,`order`) values (12,'sitenews','Site News','Site News',1,4);
insert into `admin_module` (`id`,`name`,`friendly_name`,`page_title`,`active`,`order`) values (13,'ccti_drawings','CCTI Drawings','CCTI Drawings',1,15);

/*Data for the table `admin_user_levels` */

insert into `admin_user_levels` (`level`,`name`) values (16,'Staff');
insert into `admin_user_levels` (`level`,`name`) values (32,'Webmaster');
insert into `admin_user_levels` (`level`,`name`) values (64,'School Admin');
insert into `admin_user_levels` (`level`,`name`) values (127,'State Admin');

/*Data for the table `admin_level_module` */

insert into `admin_level_module` (`module_id`,`level`) values (1,64);
insert into `admin_level_module` (`module_id`,`level`) values (2,127);
insert into `admin_level_module` (`module_id`,`level`) values (3,16);
insert into `admin_level_module` (`module_id`,`level`) values (6,127);
insert into `admin_level_module` (`module_id`,`level`) values (7,32);
insert into `admin_level_module` (`module_id`,`level`) values (8,64);
insert into `admin_level_module` (`module_id`,`level`) values (9,16);
insert into `admin_level_module` (`module_id`,`level`) values (11,127);
insert into `admin_level_module` (`module_id`,`level`) values (13,16);

/*Data for the table `email_text` */

insert into `email_text` (`id`,`description`,`sender`,`recipient`,`bcc`,`subject`,`emailbody`) values ('account_approved','Sent to the user when someone approves their account request. Contains a temporary password.','##WEBSITE_EMAIL##','##EMAIL##',NULL,'[Oregon CT Pathways] Account Request Approved','Welcome! Your account for the Career Pathways Web Tool has been activated. This email contains a temporary password you can use to log in to your account. Once logged in, you will need to change your password, as this link will work only once.\r\n\r\nYou can log in by clicking the link below, or you can visit http://oregon.ctepathways.org and log in using the password below.\r\n\r\n##LOGIN_LINK##\r\n\r\nEmail: ##EMAIL##\r\nPassword: ##PASSWORD##\r\n\r\nOnce logged in, we encourage you to visit our Getting Started Tutorial for complete step-by-step instructions and details for all Web Tool features. Within the tutorial you will also find a short video demonstration to orient you with some of the more dynamic drawing features.\r\n\r\nPlease do not hesitate to contact me for training and technical assistance as you get started.\r\n\r\nSincerely,\r\n\r\nEffie Siverts\r\nTraining & Development\r\nCareer Pathways Web Tool\r\n\r\nhelp@ctepathways.org\r\n');
insert into `email_text` (`id`,`description`,`sender`,`recipient`,`bcc`,`subject`,`emailbody`) values ('account_request','Sent to school admins when someone fills out the account request form.','##WEBSITE_EMAIL##','##RECIPIENTS##','','[Oregon CT Pathways] New Account Request','Hello,\r\n\r\nThis is an automated email sent to you because you are listed as a \"School Admin\" for your school on the Oregon Career Pathways website.\r\n\r\nThe person below has requested an account at your school. As a \"School Admin\" you can approve this person\'s request. Please do not approve the request unless you know this person, as they will have the ability to edit any drawing at your school. Review the details for this person, then click on the link below to create their account.\r\n\r\n##USER_INFO##\r\n\r\n##APPROVE_LINK##\r\n\r\nSincerely,\r\n\r\nOregon Career Pathways Web Tool\r\n\r\n');
insert into `email_text` (`id`,`description`,`sender`,`recipient`,`bcc`,`subject`,`emailbody`) values ('account_request_rt','Sent to RT when someone fills out the account request form.','aaron@parecki.com','help@ctepathways.org','','[Oregon CT Pathways] New Account Request','Hello,\r\n\r\nThe person below has requested an account. If this user has requested an account at an existing school, the school admins have already received this message. Review the details for this person, then click on the link below to create their account.\r\n\r\n##USER_INFO##\r\n\r\n##APPROVE_LINK##\r\n\r\nSincerely,\r\n\r\nOregon Career Pathways Web Tool\r\n\r\n');
insert into `email_text` (`id`,`description`,`sender`,`recipient`,`bcc`,`subject`,`emailbody`) values ('temporary_password','This email gets sent to people when they request a temporary password from the site.','##WEBSITE_EMAIL##','##EMAIL##',NULL,'[Oregon CT Pathways] Temporary Password','Click on the link below to log into your account temporarily. You will need to change your password as soon as you log in, as this link will only work once.\r\n\r\nThis email was sent because you or someone else requested a temporary password from the Oregon CT Pathways website. If you have received this email in error, please ignore it. Your current password will still be valid.\r\n\r\n##LOGIN_LINK##\r\n\r\nIf you cannot click the link above for any reason, you can also log into the site using the temporary password below.\r\n\r\nhttp://oregon.ctepathways.org\r\n##EMAIL##\r\n##PASSWORD##\r\n\r\nSincerely,\r\n\r\Effie Siverts\r\n');
insert into `email_text` (`id`,`description`,`sender`,`recipient`,`bcc`,`subject`,`emailbody`) values ('help_request','This email is sent to help@ctepathways.org when a user fills out the help form on the website.','##EMAIL##','help@ctepathways.org',NULL,'[Oregon CT Pathways] ##SUBJECT##','##BODY##');
insert into `email_text` (`id`,`description`,`sender`,`recipient`,`bcc`,`subject`,`emailbody`) values ('svn_update','This email is sent to the developers list when the dev server is updated.','svn@ctepathways.org','developers@ctepathways.org','dev server updated','##BODY##');

/*Data for the table `email_variables` */

insert into `email_variables` (`email_id`,`variable`,`description`) values ('all','WEBSITE_EMAIL','help@ctepathways.org');
insert into `email_variables` (`email_id`,`variable`,`description`) values ('temporary_password','LOGIN_LINK','This will be a link they can click to log them in without typing in their email address or password.');
insert into `email_variables` (`email_id`,`variable`,`description`) values ('temporary_password','EMAIL','The user\'s email address they use to log in to their account.');
insert into `email_variables` (`email_id`,`variable`,`description`) values ('temporary_password','PASSWORD','An automatically-generated password that can be used to log in once.');
insert into `email_variables` (`email_id`,`variable`,`description`) values ('account_request_rt','EMAIL','The email address of the user requesting an account.');
insert into `email_variables` (`email_id`,`variable`,`description`) values ('account_request_rt','APPROVE_LINK','Click this link to review the user\'s application on the website.');
insert into `email_variables` (`email_id`,`variable`,`description`) values ('account_request_rt','USER_INFO','Details about the user requesting an account.');
insert into `email_variables` (`email_id`,`variable`,`description`) values ('account_request','USER_INFO','Details about the user requesting an account.');
insert into `email_variables` (`email_id`,`variable`,`description`) values ('account_request','APPROVE_LINK','Click this link to review the user\'s application on the website.');
insert into `email_variables` (`email_id`,`variable`,`description`) values ('account_request','RECIPIENTS','A list of email addresses of any school admins at the school the user is requesting an account.');
insert into `email_variables` (`email_id`,`variable`,`description`) values ('help_request','BODY','The body of the message the user entered into the form.');
insert into `email_variables` (`email_id`,`variable`,`description`) values ('help_request','EMAIL','The user\'s email address.');
insert into `email_variables` (`email_id`,`variable`,`description`) values ('help_request','SUBJECT','The user-provided subject line.');
insert into `email_variables` (`email_id`,`variable`,`description`) values ('account_approved','EMAIL','The user\'s email address');
insert into `email_variables` (`email_id`,`variable`,`description`) values ('account_approved','PASSWORD','An automatically-generated password that can be used to log in once.');
insert into `email_variables` (`email_id`,`variable`,`description`) values ('account_approved','LOGIN_LINK','This will be a link they can click to log them in without typing in their email address or password.');
insert into `email_variables` (`email_id`,`variable`,`description`) values ('svn_update','BODY','The body of the message');

/*Data for the table `types` */

insert into `types` (`id`,`description`,`family`) values (1,'Nursing','program');
insert into `types` (`id`,`description`,`family`) values (2,'Vocational','program');
insert into `types` (`id`,`description`,`family`) values (3,'Associates','program');
insert into `types` (`id`,`description`,`family`) values (4,'High School','program');
insert into `types` (`id`,`description`,`family`) values (5,'Transfer','program');
