
alter table `email_text` add column `bcc` varchar (255)   NULL  after `recipient`;

insert into `email_text` (`id`,`description`,`sender`,`recipient`,`bcc`,`subject`,`emailbody`) values ('account_approved','Sent to the user when someone approves their account request. Contains a temporary password.','##WEBSITE_EMAIL##','##EMAIL##',NULL,'[Oregon CT Pathways] Account Request Approved','Welcome! Your account for the Career Pathways Web Tool has been activated. This email contains a temporary password you can use to log in to your account. Once logged in, you will need to change your password, as this link will work only once.\r\n\r\nYou can log in by clicking the link below, or you can visit http://oregon.ctepathways.org and log in using the password below.\r\n\r\n##LOGIN_LINK##\r\n\r\nEmail: ##EMAIL##\r\nPassword: ##PASSWORD##\r\n\r\nOnce logged in, we encourage you to visit our Getting Started Tutorial for complete step-by-step instructions and details for all Web Tool features. Within the tutorial you will also find a short video demonstration to orient you with some of the more dynamic drawing features.\r\n\r\nPlease do not hesitate to contact me for training and technical assistance as you get started.\r\n\r\nSincerely,\r\n\r\nEffie Siverts\r\nTraining & Development\r\nCareer Pathways Web Tool\r\n\r\nhelp@ctepathways.org\r\n');
insert into `email_text` (`id`,`description`,`sender`,`recipient`,`bcc`,`subject`,`emailbody`) values ('account_request','Sent to school admins when someone fills out the account request form.','##WEBSITE_EMAIL##','##RECIPIENTS##','','[Oregon CT Pathways] New Account Request','Hello,\r\n\r\nThis is an automated email sent to you because you are listed as a \"School Admin\" for your school on the Oregon Career Pathways website.\r\n\r\nThe person below has requested an account at your school. As a \"School Admin\" you can approve this person\'s request. Please do not approve the request unless you know this person, as they will have the ability to edit any drawing at your school. Review the details for this person, then click on the link below to create their account.\r\n\r\n##USER_INFO##\r\n\r\n##APPROVE_LINK##\r\n\r\nSincerely,\r\n\r\nOregon Career Pathways Web Tool\r\n\r\n');
insert into `email_text` (`id`,`description`,`sender`,`recipient`,`bcc`,`subject`,`emailbody`) values ('account_request_rt','Sent to RT when someone fills out the account request form.','aaron@parecki.com','help@ctepathways.org','','[Oregon CT Pathways] New Account Request','Hello,\r\n\r\nThe person below has requested an account. If this user has requested an account at an existing school, the school admins have already received this message. Review the details for this person, then click on the link below to create their account.\r\n\r\n##USER_INFO##\r\n\r\n##APPROVE_LINK##\r\n\r\nSincerely,\r\n\r\nOregon Career Pathways Web Tool\r\n\r\n');

insert into `email_variables` (`email_id`,`variable`,`description`) values ('all','WEBSITE_EMAIL','help@ctepathways.org');
insert into `email_variables` (`email_id`,`variable`,`description`) values ('account_request_rt','USER_INFO','Details about the user requesting an account.');
insert into `email_variables` (`email_id`,`variable`,`description`) values ('account_request','USER_INFO','Details about the user requesting an account.');
insert into `email_variables` (`email_id`,`variable`,`description`) values ('account_request','APPROVE_LINK','Click this link to review the user\'s application on the website.');
insert into `email_variables` (`email_id`,`variable`,`description`) values ('account_request','RECIPIENTS','A list of email addresses of any school admins at the school the user is requesting an account.');
insert into `email_variables` (`email_id`,`variable`,`description`) values ('account_request_rt','EMAIL','The email address of the user requesting an account.');
insert into `email_variables` (`email_id`,`variable`,`description`) values ('account_approved','EMAIL','The user\'s email address');
insert into `email_variables` (`email_id`,`variable`,`description`) values ('account_approved','PASSWORD','An automatically-generated password that can be used to log in once.');
insert into `email_variables` (`email_id`,`variable`,`description`) values ('account_approved','LOGIN_LINK','This will be a link they can click to log them in without typing in their email address or password.');


ALTER TABLE `users` 
	ADD COLUMN `new_user` tinyint (1)  DEFAULT '0' NOT NULL  AFTER `last_module`, 
	ADD COLUMN `referral` varchar (255)   NULL  AFTER `new_user`, 
	ADD COLUMN `other_school` varchar (255)   NULL  AFTER `referral`,
	ADD COLUMN `application_key` varchar (255)   NULL  AFTER `other_school`,
	ADD COLUMN `approved_by` int   NULL  AFTER `application_key`;

