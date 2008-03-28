alter table `users` 
	add column `job_title` varchar (255) NULL  after `email`, 
	add column `phone_number` varchar (50)   NULL  after `job_title`;
