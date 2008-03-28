alter table `schools` 
	drop column `school_logo`,
	add column `school_addr` varchar (255) NULL  after `school_website`;
