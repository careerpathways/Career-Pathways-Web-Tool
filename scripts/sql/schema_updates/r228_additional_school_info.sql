alter table `schools` 
	add column `school_city` varchar (100)   NULL  after `school_addr`, 
	add column `school_state` varchar (2)   NULL  after `school_city`, 
	add column `school_zip` varchar (12)   NULL  after `school_state`;

