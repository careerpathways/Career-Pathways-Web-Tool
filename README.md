After downloading the source code, you will need to do some initial configuration.

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
```
drwxr-xr-x  3 apache   apache    4096 2011-03-24 14:35 cache
drwxr-xr-x  7 pathways pathways  4096 2011-03-24 14:15 common
drwxr-xr-x  2 pathways pathways  4096 2011-03-24 14:15 examples
drwxr-xr-x  3 pathways pathways  4096 2011-03-24 14:15 scripts
drwxr-xr-x 12 pathways pathways  4096 2011-03-24 14:29 www
```

Database
---

Configure the database username and password in your `*.settings.php` file. If you can't create your own file, you will have to edit the `default.settings.php` file directly, just make sure not to commit that file back to the repository.

Install the database tables from the `scripts/sql/schema.sql` file provided. After the tables are created, run the `scripts/sql/initdata.sql` file to insert some default data.

.htaccess file
---

Copy the `www/htaccess` file to `www/.htaccess` and modify it to point to your `www/include` and `common` folders.

Try it out
---

Visit your install in the browser, and you should be presented with a login prompt. The default user account is admin@example.com and a password of 1234. 
