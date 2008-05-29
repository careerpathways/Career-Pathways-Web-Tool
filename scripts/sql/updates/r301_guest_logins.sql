DROP TABLE IF EXISTS `guest_logins`;
create table `guest_logins` (    
	`id` int UNSIGNED   NOT NULL AUTO_INCREMENT ,  
	`date` datetime   NULL ,  
	`first_name` varchar (30)   NULL ,  
	`last_name` varchar (30)   NULL ,  
	`email` varchar (100)   NULL ,  
	`school` varchar (200)   NULL ,  
	`referral` varchar (200)   NULL ,  
	`ipaddr` varchar (20)   NULL  , 
	PRIMARY KEY ( `id` )  
');

