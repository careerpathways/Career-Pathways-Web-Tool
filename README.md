After downloading the source code, you will need to do some initial configuration.


Server Requirements
---
The Career Pathways Web Tool is best suited for a Linux web server running Apache, PHP, and MySQL. It can also run on a Windows web server, but has not yet been implemented in Windows on a production system. The following software is required for Linux and Windows installations:

* Apache 2.0 web server with mod_rewrite
* PHP 5.2 or later
* MySQL 5.0 or later
* There are several modules that should be installed. The easiest way to find these modules is by reviewing the Vagrant bootstrap.sh script that is available on github https://github.com/careerpathways/Career-Pathways-Web-Tool/blob/master/www/Vagrant/bootstrap.sh.
* The software requires write permission to several directories. The user running Apache will need write access to the "cache" folder and the "assets" folder.

''Note: It may be possible to run the software under a web server other than Apache, however this has not yet been attempted.''

You can download the source code here: http://oregon.ctepathways.org/p/licensing

Configuration
---
* Most site configuration is performed in https://github.com/careerpathways/Career-Pathways-Web-Tool/tree/master/www/include the include folder on the site including:
*  Database connection settings
*  Site features
*  Site name
*  Site email settings
*  Language files 
*  Site cache and asset folders
*  Other environment variables
*  Recaptcha keys
* Make sure to update email templates by visiting (your domain)/a/emailcontents.php
* You can update counties that the system uses by editing and executing https://github.com/careerpathways/Career-Pathways-Web-Tool/blob/master/scripts/sql/updates/r564_adds_counties_and_fixes_schools.sql.


Configure Apache
---

Configure apache to serve files from the "www" folder in the project. This is usually accomplished with a VirtualHost definition like the following.

```
<VirtualHost *:80>
	ServerName test.ctepathways.org
	
	DocumentRoot /web/test.ctepathways.org/www
	<Directory /web/test.ctepathways.org/www>
                Options +FollowSymLinks
                AllowOverride All
                allow from all
	</Directory>
</Virtualhost>
```

If you have the SetEnv module installed, add this line inside the Directory block:

    SetEnv CONFIG_FILE my.settings.php

Change the filename to whatever you want, and copy www/include/default.settings.php to the filename you set here.

Filesystem
---

If it doesn't already exist, create a `cache` folder next to the `www` and `scripts` folders, and make it writable by apache.
If it doesn't already exist, create a `asset` folder next to the `www` and `scripts` folders, and make it writable by apache.

```
drwxr-xr-x  3 apache   apache    4096 2011-03-24 14:35 cache
drwxr-xr-x  3 apache   apache    4096 2011-03-24 14:35 asset
drwxr-xr-x  7 pathways pathways  4096 2011-03-24 14:15 common
drwxr-xr-x  2 pathways pathways  4096 2011-03-24 14:15 examples
drwxr-xr-x  3 pathways pathways  4096 2011-03-24 14:15 scripts
drwxr-xr-x 12 pathways pathways  4096 2011-03-24 14:29 www
```

Database
---

Configure the database username and password in your `*.settings.php` file. If you can't create your own file, you will have to edit the `default.settings.php` file directly, just make sure not to commit that file back to the repository.

Install the database tables from the `scripts/sql/schema.sql` file provided. After the tables are created, run the `scripts/sql/initdata.sql` file to insert some default data.

Make sure to run updates in scripts/sql to get your database up to date.

.htaccess file
---

Copy the `www/htaccess` file to `www/.htaccess` and modify it to point to your `www/include` and `common` folders.

Try it out
---

Visit your install in the browser, and you should be presented with a login prompt. The default user account is admin@example.com and a password of 1234. 
