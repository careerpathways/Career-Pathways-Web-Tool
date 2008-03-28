insert into `email_text` (`id`,`description`,`sender`,`recipient`,`subject`,`emailbody`) values ('svn_update','This email is sent to the developers list when the dev server is updated.','svn@ctepathways.org','developers@ctepathways.org','dev server updated','##BODY##');
insert into `email_variables` (`email_id`,`variable`,`description`) values ('svn_update','BODY','The body of the message');
