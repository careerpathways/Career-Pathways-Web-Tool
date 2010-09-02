
/*Data for the table `admin_module` */

INSERT INTO `admin_module` VALUES (1,'users','Users','Users',1,260);
INSERT INTO `admin_module` VALUES (2,'schools','Configure Organizations','Organizations',1,220);
INSERT INTO `admin_module` VALUES (3,'drawings','Roadmap Drawings','Roadmap Drawings',1,20);
INSERT INTO `admin_module` VALUES (8,'schoolcolors','Organization Colors','Organization Colors',1,210);
INSERT INTO `admin_module` VALUES (6,'news','Edit Resources','Edit Resources',1,250);
INSERT INTO `admin_module` VALUES (21,'permissions','Configure Permissions','Configure Permissions',1,232);
INSERT INTO `admin_module` VALUES (4,'sessions','Current Sessions','Current Sessions',0,1130);
INSERT INTO `admin_module` VALUES (9,'help','Help','Help',0,1200);
INSERT INTO `admin_module` VALUES (10,'emailcontents','Email Templates','Email Templates',1,240);
INSERT INTO `admin_module` VALUES (11,'guestlogins','Guest Logins','Guest Logins',1,1190);
INSERT INTO `admin_module` VALUES (13,'stats','Stats','Stats',1,1195);
INSERT INTO `admin_module` VALUES (20,'--','--','--',1,300);
INSERT INTO `admin_module` VALUES (15,'dashboard','Dashboard','Dashboard',1,10);
INSERT INTO `admin_module` VALUES (16,'post_drawings','POST Drawings','POST Drawings',1,30);
INSERT INTO `admin_module` VALUES (17,'hs_settings','High School Settings','High School Settings',1,205);
INSERT INTO `admin_module` VALUES (18,'configure_post_legend','Configure POST Legend','Configure POST Legend',1,230);
INSERT INTO `admin_module` VALUES (19,'--','--','--',1,200);
INSERT INTO `admin_module` VALUES (22,'post_views','POST Views','POST Views',1,35);
INSERT INTO `admin_module` VALUES (23,'hs_affiliations','Affiliations','Affiliations',1,220);

/*Data for the table `admin_user_levels` */

INSERT INTO `admin_user_levels` VALUES (127,'State Admin');
INSERT INTO `admin_user_levels` VALUES (64,'Org Admin');
INSERT INTO `admin_user_levels` VALUES (16,'Staff');
INSERT INTO `admin_user_levels` VALUES (32,'Webmaster');
INSERT INTO `admin_user_levels` VALUES (8,'HS Staff');
INSERT INTO `admin_user_levels` VALUES (12,'HS Admin');

/*Data for the table `admin_level_module` */

INSERT INTO `admin_level_module` VALUES (1,0);
INSERT INTO `admin_level_module` VALUES (2,127);
INSERT INTO `admin_level_module` VALUES (3,4);
INSERT INTO `admin_level_module` VALUES (6,127);
INSERT INTO `admin_level_module` VALUES (8,32);
INSERT INTO `admin_level_module` VALUES (9,16);
INSERT INTO `admin_level_module` VALUES (10,127);
INSERT INTO `admin_level_module` VALUES (11,127);
INSERT INTO `admin_level_module` VALUES (13,127);
INSERT INTO `admin_level_module` VALUES (15,4);
INSERT INTO `admin_level_module` VALUES (16,4);
INSERT INTO `admin_level_module` VALUES (17,12);
INSERT INTO `admin_level_module` VALUES (18,127);
INSERT INTO `admin_level_module` VALUES (19,0);
INSERT INTO `admin_level_module` VALUES (20,0);
INSERT INTO `admin_level_module` VALUES (21,127);
INSERT INTO `admin_level_module` VALUES (22,8);
INSERT INTO `admin_level_module` VALUES (23,32);

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

INSERT INTO `post_legend` VALUES (0,'legend_e.png','Elective');
INSERT INTO `post_legend` VALUES (0,'legend_g.png','Take For Grade (not P/NP)');
INSERT INTO `post_legend` VALUES (0,'legend_d.png','Dual Credit, College Now, Early College Credit');
INSERT INTO `post_legend` VALUES (0,'legend_2.png','2+2 Early College Credit, College Now, Dual Credit');
INSERT INTO `post_legend` VALUES (0,'legend_ct.png','Career Technical Education');
INSERT INTO `post_legend` VALUES (0,'legend_at.png','Academic Transfer (AAOT)');
INSERT INTO `post_legend` VALUES (0,'legend_ap.png','Advanced Placement');

INSERT INTO `oregon_skillsets` VALUES (1,'Agriculture, Food and Natural Resources');
INSERT INTO `oregon_skillsets` VALUES (2,'Arts, Information and Communications');
INSERT INTO `oregon_skillsets` VALUES (3,'Business and Management');
INSERT INTO `oregon_skillsets` VALUES (4,'Health Services');
INSERT INTO `oregon_skillsets` VALUES (5,'Human Resources');
INSERT INTO `oregon_skillsets` VALUES (6,'Industrial and Engineering Systems');


