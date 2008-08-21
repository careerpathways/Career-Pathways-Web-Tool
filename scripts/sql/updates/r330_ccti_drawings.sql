insert into `admin_module` (`id`,`name`,`friendly_name`,`page_title`,`active`,`order`) values (13,'ccti_drawings','CCTI Drawings','CCTI Drawings',1,15);
insert into `admin_level_module` (`module_id`,`level`) values (13,16);

CREATE TABLE `ccti_data` (                                
	`id` int(10) unsigned NOT NULL auto_increment,          
	`section_id` int(10) unsigned default NULL,             
	`row` int(11) NOT NULL default '0',                     
	`col` int(11) NOT NULL default '0',                     
	`rowspan` int(11) NOT NULL default '1',                 
	`colspan` int(11) NOT NULL default '1',                 
	`text` text NOT NULL,                                   
	PRIMARY KEY  (`id`)                                     
);

CREATE TABLE `ccti_drawings` (                           
	`id` int(10) unsigned NOT NULL auto_increment,         
	`school_id` int(11) default NULL,                      
	`name` varchar(255) default NULL,                      
	`code` varchar(255) default NULL,                      
	`date_created` datetime default NULL,                  
	`last_modified` datetime default NULL,                 
	`created_by` int(11) default NULL,                     
	`last_modified_by` int(11) default NULL,               
	`deleted` tinyint(4) NOT NULL default '0',             
	PRIMARY KEY  (`id`)                                    
)

CREATE TABLE `ccti_programs` (                           
	`id` int(10) unsigned NOT NULL auto_increment,         
	`drawing_id` int(10) unsigned default NULL,            
	`header` varchar(255) default NULL,                    
	`footer` varchar(255) default NULL,                    
	`school_id` int(11) NOT NULL default '0',              
	`completes_with` varchar(255) default NULL,            
	`num_columns` int(11) NOT NULL default '6',            
	`show_occ_titles` tinyint(1) NOT NULL default '1',     
	`index` int(11) NOT NULL default '0',                  
	PRIMARY KEY  (`id`)                                    
)

CREATE TABLE `ccti_section_labels` (                      
	`id` int(10) unsigned NOT NULL auto_increment,          
	`section_id` int(10) unsigned default NULL,             
	`axis` enum('x','y','xy') default NULL,                 
	`col` int(11) NOT NULL default '0',                     
	`row` int(11) NOT NULL default '0',                     
	`colspan` int(11) NOT NULL default '1',                 
	`rowspan` int(11) NOT NULL default '1',                 
	`text` varchar(255) default NULL,                       
	PRIMARY KEY  (`id`)                                     
)

CREATE TABLE `ccti_sections` (                           
	`id` int(10) unsigned NOT NULL auto_increment,         
	`program_id` int(10) unsigned default NULL,            
	`header` varchar(255) default NULL,                    
	`num_rows` int(11) NOT NULL default '1',               
	`index` int(11) NOT NULL default '0',                  
	PRIMARY KEY  (`id`)                                    
);

