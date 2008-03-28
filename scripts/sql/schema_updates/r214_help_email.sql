insert into `admin_module` (`id`,`name`,`friendly_name`,`page_title`,`active`,`order`) values (9,'help','Help','Help',1,200);
insert into `admin_level_module` (`module_id`,`level`) values (9,16);
insert into `email_variables` (`email_id`,`variable`,`description`) values ('help_request','BODY','The body of the message the user entered into the form.');
insert into `email_variables` (`email_id`,`variable`,`description`) values ('help_request','EMAIL','The user\'s email address.');
insert into `email_variables` (`email_id`,`variable`,`description`) values ('help_request','SUBJECT','The user-provided subject line.');
insert into `email_text` (`id`,`description`,`sender`,`recipient`,`subject`,`emailbody`) values ('help_request','This email is sent to help@ctepathways.org when a user fills out the help form on the website.','##EMAIL##','help@ctepathways.org','[Oregon CT Pathways] ##SUBJECT##','##BODY##');
CREATE TABLE `helprequests` (            
	`id` int(11) NOT NULL auto_increment,  
	`date` datetime default NULL,          
	`user_id` int(11) default NULL,        
	`subject` varchar(255) default NULL,   
	`message` text,                        
	PRIMARY KEY  (`id`)                    
) ENGINE=InnoDB DEFAULT CHARSET=latin1

