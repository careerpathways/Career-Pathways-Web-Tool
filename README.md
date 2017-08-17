# 1. Install
After downloading the source code, you will need to do some initial configuration.

You can download the source code here: https://github.com/careerpathways/Career-Pathways-Web-Tool


## Configure Application
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
* You can update counties that the system uses by editing and executing https://github.com/careerpathways/Career-Pathways-Web-Tool/blob/master/scripts/sql/updates/r564_adds_counties_and_fixes_schools.sql after the Database has been constructed (below).


## Configure .htaccess file
Copy the `www/htaccess` file to `www/.htaccess` and modify it to point to your `www/include` and `common` folders.


## Set Up Database
Configure the database username and password in your `www/include/*.settings.php` file. If you can't create your own file, you will have to edit the `default.settings.php` file directly, just make sure not to commit that file back to the repository.

Install the database tables from the `scripts/sql/schema.sql` file provided. After the tables are created, run the `scripts/sql/initdata.sql` file to insert some default data.

Make sure to run updates in scripts/sql to get your database up to date.


## Install Dependencies
From the project root, run `composer install`



# 2. Start
## Development
### Requirements
* [Vagrant](https://www.vagrantup.com/)
* [Composer](https://getcomposer.org/)

Run `vagrant up` from `www/Vagrant/`, then visit http://localhost:8080 in the browser, and you should be presented with a login prompt.

The default user account is **admin@example.com** and a password of **1234**.

## Live Server
### Requirements
The Career Pathways Web Tool is best suited for a Linux web server running Apache, PHP, and MySQL. It can also run on a Windows web server, but has not yet been implemented in Windows on a production system. The following software is required for Linux and Windows installations:

* Apache 2.0 web server with mod_rewrite
* PHP 5.2 or later
* MySQL 5.0 or later
* [Composer](https://getcomposer.org/)
* There are several modules that should be installed. The easiest way to find these modules is by reviewing the Vagrant bootstrap.sh script that is available on github https://github.com/careerpathways/Career-Pathways-Web-Tool/blob/master/www/Vagrant/bootstrap.sh.
* The software requires write permission to several directories. The user running Apache will need write access to the "cache" folder and the "assets" folder.

*Note: It may be possible to run the software under a web server other than Apache, however this has not yet been attempted.*

### Live Server Additional Config
#### Apache
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


#### Filesystem
If it doesn't already exist, create a `cache` folder next to the `www` and `scripts` folders, and make it writable by apache.
If it doesn't already exist, create a `asset` folder next to the `www` and `scripts` folders, and make it writable by apache.

```
drwxr-xr-x  3 apache   apache    4096 2011-03-24 14:35 cache
drwxr-xr-x  3 apache   apache    4096 2011-03-24 14:35 asset
drwxr-xr-x  7 apache   apache    4096 2011-03-24 14:15 common
drwxr-xr-x  2 apache   apache    4096 2011-03-24 14:15 examples
drwxr-xr-x  3 apache   apache    4096 2011-03-24 14:15 scripts
drwxr-xr-x 12 apache   apache    4096 2011-03-24 14:29 www
```


# Additional Information
## PHP Includes
This project is set up to use php includes and modifies the include path. See `www/Vagrant/bootstrap.sh` where Apache virtual sites are defined for a line similar to `php_value include_path \".:/home/project/$projectName/www/include:/home/project/$projectName/common\"`

Notice that now, when you see `include('x.php');` in PHP, these paths will be checked as well.


# Release Process
* Update `(core)/common/version.php` with appropriate version number.
* *Add config and release notes to `(core)/CHANGELOG.md`*
* Tag the release, e.g. `4.3.0`
