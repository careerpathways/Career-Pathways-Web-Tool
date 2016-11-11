#!/usr/bin/env bash

# https://github.com/Divi/VagrantBootstrap


# ------------------------------------------------
# Project Name, set in Vagrantfile
# ------------------------------------------------
projectName=$1


echo '***************************** Apt Update *****************************'
export DEBIAN_FRONTEND=noninteractive
apt-get update

echo '***************************** System settings *****************************'

# Terminal
echo "PS1='$projectName(\u):\w# ' " >> /root/.bashrc
echo "PS1='$projectName(\u):\w\$ ' " >> /home/vagrant/.bashrc


# ------------------------------------------------
# Apache
# ------------------------------------------------
echo '***************************** Installing and configuring Apache *****************************'
apt-get install -y apache2


# Add ServerName to httpd.conf for localhost
echo "ServerName localhost
User vagrant
Group vagrant
EnableSendFile off" > /etc/apache2/httpd.conf

echo "<VirtualHost *:80>
ServerAdmin webmaster@localhost
DocumentRoot /home/project/$projectName/www/

CustomLog \${APACHE_LOG_DIR}/access.log combined
ErrorLog \${APACHE_LOG_DIR}/error.log
LogLevel warn

<Directory /home/project/$projectName/www/>
    Options -Indexes +FollowSymLinks -MultiViews
    AllowOverride All
    Order allow,deny
    allow from all
</Directory>

<IfModule mod_php5.c>
  php_value include_path \".:/home/project/$projectName/www/include:/home/project/$projectName/common\"
</IfModule>

</VirtualHost>" > /etc/apache2/sites-available/default

echo "<IfModule mod_ssl.c>
<VirtualHost *:443>
ServerAdmin webmaster@localhost
DocumentRoot /home/project/$projectName/www/

CustomLog \${APACHE_LOG_DIR}/access.log combined
ErrorLog \${APACHE_LOG_DIR}/error.log
LogLevel warn

<Directory /home/project/$projectName/www/>
    Options -Indexes +FollowSymLinks -MultiViews
    AllowOverride All
    Order allow,deny
    allow from all
</Directory>

<IfModule mod_php5.c>
  php_value include_path \".:/home/project/$projectName/www/include:/home/project/$projectName/common\"
</IfModule>

SSLEngine on

SSLCertificateFile    /etc/ssl/certs/ssl-cert-snakeoil.pem
SSLCertificateKeyFile /etc/ssl/private/ssl-cert-snakeoil.key
<FilesMatch \"\.(cgi|shtml|phtml|php)$\">
        SSLOptions +StdEnvVars
</FilesMatch>
BrowserMatch \"MSIE [2-6]\" \
        nokeepalive ssl-unclean-shutdown \
        downgrade-1.0 force-response-1.0
# MSIE 7 and newer should be able to use keepalive
BrowserMatch \"MSIE [17-9]\" ssl-unclean-shutdown

</VirtualHost>
</IfModule>" > /etc/apache2/sites-available/default-ssl



echo '***************************** Add extra mod and generate testing certificate *****************************'
make-ssl-cert generate-default-snakeoil --force-overwrite
a2ensite default-ssl
a2enmod ssl
a2enmod rewrite


# ------------------------------------------------
# PHP 5.x
# ------------------------------------------------
echo '***************************** Installing PHP5 *****************************'
apt-get install -y php5 libapache2-mod-php5 php5-cli php5-ldap

echo '***************************** Installing PHP5 tools *****************************'
apt-get install -y php5-curl php5-mcrypt php5-gd php-pear php5-xdebug php5-intl php5-dev


echo '***************************** Writing php.ini *****************************'
echo '
engine = On
output_buffering = 4096
implicit_flush = Off
allow_call_time_pass_reference = Off
safe_mode = Off
disable_functions = pcntl_alarm,pcntl_fork,pcntl_waitpid,pcntl_wait,pcntl_wifexited,pcntl_wifstopped,pcntl_wifsignaled,pcntl_wexitstatus,pcntl_wtermsig,pcntl_wstopsig,pcntl_signal,pcntl_signal_dispatch,pcntl_get_last_error,pcntl_strerror,pcntl_sigprocmask,pcntl_sigwaitinfo,pcntl_sigtimedwait,pcntl_exec,pcntl_getpriority,pcntl_setpriority,
zend.enable_gc = On

max_execution_time = 30
max_input_time = 60
;max_input_nesting_level = 64
; max_input_vars = 1000
memory_limit = 128M
post_max_size = 8M

file_uploads = On
upload_max_filesize = 2M
max_file_uploads = 20

allow_url_fopen = On
allow_url_include = Off

error_reporting = E_ALL & ~E_DEPRECATED
display_errors = Off
display_startup_errors = On
log_errors = On
log_errors_max_len = 1024
ignore_repeated_errors = Off
ignore_repeated_source = Off
report_memleaks = On

html_errors = On
variables_order = "GPCS"
request_order = "GP"

register_globals = Off
register_long_arrays = Off
register_argc_argv = Off

auto_globals_jit = On
magic_quotes_gpc = Off
magic_quotes_runtime = Off
magic_quotes_sybase = Off
default_mimetype = "text/html"
enable_dl = Off

[Date]
date.timezone ="America/Los_Angeles"

[mail function]
; For Win32 only.
SMTP = localhost
smtp_port = 25

; For Win32 only.
;sendmail_from = me@example.com

; For Unix only.  You may supply arguments as well (default: "sendmail -t -i").
;sendmail_path =

; Add X-PHP-Originating-Script: that will include uid of the script followed by the filename
mail.add_x_header = On


[SQL]
sql.safe_mode = Off

[MySQL]
mysql.allow_local_infile = On
mysql.allow_persistent = On
mysql.cache_size = 2000
mysql.max_persistent = -1
mysql.max_links = -1
mysql.connect_timeout = 60
mysql.trace_mode = Off

[MySQLi]
mysqli.max_persistent = -1
mysqli.allow_persistent = On
mysqli.max_links = -1
mysqli.cache_size = 2000
mysqli.default_port = 3306
mysqli.reconnect = Off

' > /etc/php5/apache2/php.ini


echo "***************************** Installing Packages needed to build under PECL *****************************"
apt-get install -y build-essential git curl g++ libssl-dev apache2-utils



echo "***************************** Installing MySQL *****************************"
# Install MySQL without prompt
export DEBIAN_FRONTEND=noninteractive
debconf-set-selections <<< 'mysql-server mysql-server/root_password password devsu'
debconf-set-selections <<< 'mysql-server mysql-server/root_password_again password devsu'
apt-get install -y mysql-server 2> /dev/null
apt-get install -y mysql-client 2> /dev/null
apt-get install -y php5-mysql 2> /dev/null
# create the database based on the projectName
mysqladmin -pdevsu create $projectName
# populate the database based on the projectName
mysql -u root -pdevsu $projectName < /home/project/dump/$projectName.sql


echo '***************************** Installing phpmyadmin *****************************'
apt-get install -y phpmyadmin
echo "Include /etc/phpmyadmin/apache.conf" >> /etc/apache2/httpd.conf





/home/project/$projectName/www/
echo '***************************** Project-specific settings *****************************'
# -------------------
# Install wkhtmltopdf and its dependencies
# -------------------
apt-get install -y libjpeg8
apt-get install -y fontconfig
apt-get install -y libxrender1

# wkhtmltopdf is incomplete via apt-get, so we wget the .deb and dpkg it instead
wget --output-document 'wkhtmltox.deb' 'http://downloads.sourceforge.net/project/wkhtmltopdf/0.12.2.1/wkhtmltox-0.12.2.1_linux-precise-amd64.deb'
chown vagrant 'wkhtmltox.deb'
dpkg -i 'wkhtmltox.deb'

# codebase looks for wkhtmltopdf in a different spot, set up a sym link to avoid error
ln -s /usr/local/bin/wkhtmltopdf /usr/bin/wkhtmltopdf-i386

# the application stores pdf's here
mkdir -p '/web2/oregon.ctepathways.org/cache/pdf/'
chown vagant '/web/oregon.ctepathways.org/cache/pdf/'


#install pspell for tinymce spell-checker support
apt-get install libpspell-dev
apt-get install php5-pspell
apt-get install aspell-en


# -------------------
# Create cache folder and make sure it's writable
# -------------------
# Make user the vagant user can write to parent folder
#TODO update paths for washington:
#mkdir -p /home/wwwcaree/public_html/cache/pdf/
#chmod -R 777 /home/wwwcaree/public_html/

echo '***************************** Installing Composer and Dependencies *****************************'
curl -sS https://getcomposer.org/installer | sudo php -- --install-dir=/usr/local/bin --filename=composer
composer install --working-dir=/home/project/cpwt_oregon_template/core


echo '***************************** Installing PHPUnit *****************************'
wget https://phar.phpunit.de/phpunit-4.8.0.phar
chmod +x phpunit-4.8.0.phar
mv phpunit-4.8.0.phar /usr/bin/phpunit

# Install Pear requirements
pear install Text_Wiki

# Install Amazon SES Mailer
git clone https://github.com/geoloqi/Amazon-SES-Mailer-PHP.git Amazon-SES-Mailer-PHP
pear -D auto_discover=1 install pear.amazonwebservices.com/sdk


#Default-settings.php and settings.php are both used.  So they need to setup and the same.
sed -i 's#^php_value.*$#php_value include_path  ".:/home/project/$projectName/www/include/:/home/project/$projectName/common/"#g' /home/project/$projectName/www/.htaccess

sed -i 's#helpdesk@careermaphumboldt.com#michael.calabrese+lccpost@lunarlogic.com#g' /home/project/$projectName/www/include/default.settings.php
#sed -i "s#DBname\s\?=\s\?'\w\+'#DBname = 'pathways_pierce'#g" /home/project/$projectName/www/include/default.settings.php
sed -i "s#DBuser\s\?=\s\?'\w\+'#DBuser = 'root'#g" /home/project/$projectName/www/include/default.settings.php
sed -i "s#DBpass\s\?=\s\?'[^']\+'#DBpass = 'devsu'#g" /home/project/$projectName/www/include/default.settings.php
ln -s /home/project/$projectName/www/include/default.settings.php /home/project/$projectName/www/include/settings.php


#TODO change:
#public $lang_file = 'humboldt';
#to:
#public $lang_file = 'pierce';
# in default.settings.php

# TODO sed this:
# In general.inc.php change:
#  require_once('Amazon-SES-Mailer-PHP/AmazonSESMailer.php');
# to:
#  class PHPMailerLite {};
#  //require_once('Amazon-SES-Mailer-PHP/AmazonSESMailer.php');


# ------------------------------------------------
# Finish up
# ------------------------------------------------
# restart apache
service apache2 restart

echo '***************************** Completed bootstrap.sh *****************************'
echo '
** Helpful Vagrant commands **
vagrant up
vagrant suspend
vagrant halt
vagrant ssh
vagrant global-status --prune
'
