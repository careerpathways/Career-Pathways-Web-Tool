#!/usr/bin/env bash

# ----------------------------------------
# https://github.com/Divi/VagrantBootstrap
# ----------------------------------------

# Include parameteres file
# ------------------------------------------------
source /vagrant/bootstrap_parameters.sh

# Update the box release repositories
# ------------------------------------------------
apt-get update

# Make user the vagant user can use tmp
sudo chmod 777 /tmp

# APACHE
# ------------------------------------------------
apt-get install -y apache2
# Add ServerName to httpd.conf for localhost
echo "ServerName localhost
EnableSendFile off
User vagrant
Group vagrant" >> /etc/apache2/httpd.conf
sed -i '/AllowOverride None/c AllowOverride All' /etc/apache2/sites-available/default
sed -i '/AllowOverride None/c AllowOverride All' /etc/apache2/sites-available/default-ssl
sed -i 's#_default_#\*#g' /etc/apache2/sites-available/default-ssl
sed -i 's#/var/www#/home/webroot/cpwt#' /etc/apache2/sites-available/default
sed -i 's#DocumentRoot "/var/www/"#DocumentRoot "/home/webroot/cpwt/"#' /etc/apache2/sites-available/default-ssl
make-ssl-cert generate-default-snakeoil --force-overwrite
a2ensite default-ssl
a2enmod ssl
# Enable "mod_rewrite"
a2enmod rewrite



# PHP 5.x
# ------------------------------------------------
apt-get install -y php5 libapache2-mod-php5

# Install "add-apt-repository" binaries
apt-get install -y python-software-properties

# Drivers
apt-get install -y php5-cli

# Tools
apt-get install -y php5-curl php5-mcrypt php5-gd php-pear php5-xdebug php5-intl php5-dev

# php.ini
# Setting the timezone
PHP_TIMEZONE="America/Los_Angeles"
sed -i 's#;date.timezone\([[:space:]]*\)=\([[:space:]]*\)*#date.timezone\1=\2\"'"$PHP_TIMEZONE"'\"#g' /etc/php5/apache2/php.ini
sed -i 's#;date.timezone\([[:space:]]*\)=\([[:space:]]*\)*#date.timezone\1=\2\"'"$PHP_TIMEZONE"'\"#g' /etc/php5/cli/php.ini

# Showing error messages
#sed -i 's#display_errors = Off#display_errors = On#g' /etc/php5/apache2/php.ini > /etc/php5/apache2/php.ini.tmp
sed -i 's#display_startup_errors = Off#display_startup_errors = On#g' /etc/php5/apache2/php.ini
sed -i 's#error_reporting = E_ALL & ~E_DEPRECATED & ~E_STRICT#error_reporting = E_ALL#g' /etc/php5/apache2/php.ini

# Install Pear requirements
pear install Text_Wiki

# ------------------
# Essential packages
# ------------------
apt-get install -y build-essential git curl g++ libssl-dev apache2-utils



# ------------------
# MySQL
# ------------------
export DEBIAN_FRONTEND=noninteractive
# Install MySQL without prompt
debconf-set-selections <<< 'mysql-server mysql-server/root_password password devsu'
debconf-set-selections <<< 'mysql-server mysql-server/root_password_again password devsu'
apt-get install -y mysql-server 2> /dev/null
apt-get install -y mysql-client 2> /dev/null
#sudo apt-get install -y php5-mysqlnd 2> /dev/null
apt-get install -y php5-mysql 2> /dev/null
apt-get install -y php5-pspell 2> /dev/null
apt-get install -y php5-imagick 2> /dev/null

mysqladmin -pdevsu create pathways_pierce
mysql -pdevsu pathways_pierce < /var/sql_dump/pathways_pierce.sql
#sed -i 's#mysql://root:devsu@localhost/lunar_bakershoe#mysql://root:dev@localhost/lunar_bakershoe#g' /home/webroot/guaranty_drupal/sites/default/settings.php
#echo "use mysql; UPDATE user SET Host = '%' WHERE User = 'root' AND Host = '::1'" | mysql

apt-get install -y phpmyadmin
echo "Include /etc/phpmyadmin/apache.conf" >> /etc/apache2/httpd.conf

# ------------------
# setup pimp my log
# ------------------
git clone https://github.com/potsky/PimpMyLog.git /home/webroot/pimpmylog
chmod 777 /home/webroot/pimpmylog

echo "# phpMyAdmin default Apache configuration

Alias /pml /home/webroot/pimpmylog

<Directory /home/webroot/pimpmylog>
        Options FollowSymLinks
        DirectoryIndex index.php
</Directory>
" > /etc/apache2/conf.d/pimpmylog.conf

# ------------------
# Finally, restart apache
# ------------------
service apache2 restart

# ------------------
#Update the configurations for this setup.
# ------------------
sed -i 's#^php_value.*$#php_value include_path  ".:/home/webroot/cpwt/include/:/home/webroot/cpwt/common/"#g' /home/webroot/cpwt/.htaccess

#Default-settings.php and settings.php are both used.  So they need to setup and the same.
sed -i 's#helpdesk@careermaphumboldt.com#michael.calabrese+lccpost@lunarlogic.com#g' /home/webroot/cpwt/include/default.settings.php
sed -i "s#DBname\s\?=\s\?'\w\+'#DBname = 'pathways_pierce'#g" /home/webroot/cpwt/include/default.settings.php
sed -i "s#DBuser\s\?=\s\?'\w\+'#DBuser = 'root'#g" /home/webroot/cpwt/include/default.settings.php
sed -i "s#DBpass\s\?=\s\?'[^']\+'#DBpass = 'devsu'#g" /home/webroot/cpwt/include/default.settings.php
ln -s /home/webroot/cpwt/include/default.settings.php /home/webroot/cpwt/include/settings.php


