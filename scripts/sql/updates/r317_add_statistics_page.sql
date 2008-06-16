insert into `admin_module` (`id`,`name`,`friendly_name`,`page_title`,`active`,`order`) values ( NULL,'stats','Stats','Stats','1','195');
insert into `admin_level_module` (`module_id`,`level`) values ( '13','127');
CREATE TABLE `logs` (                                       
          `id` int(10) unsigned NOT NULL auto_increment,            
          `remote_addr` varchar(20) default NULL,                   
          `date` datetime default NULL,                             
          `url` varchar(255) default NULL,                          
          `drawing_code` varchar(255) default NULL,                 
          `drawing_id` int(11) default NULL,                        
          `status_code` int(11) default NULL,                       
          `bytes_transferred` int(11) default NULL,                 
          `referer` varchar(255) default NULL,                      
          `user_agent` varchar(255) default NULL,                   
          PRIMARY KEY  (`id`)                                       
        ) ENGINE=MyISAM;
CREATE TABLE `logs_processed` (                            
                  `id` int(10) unsigned NOT NULL auto_increment,           
                  `filename` varchar(50) default NULL,                     
                  `date_processed` datetime default NULL,                  
                  PRIMARY KEY  (`id`)                                      
                );
