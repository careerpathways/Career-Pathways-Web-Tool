#!/bin/bash

logloc=/web/oregon.ctepathways.org/logs

# rotate the log file
mv $logloc/access_log $logloc/access_log.`date +%Y-%m-%d`
mv $logloc/error_log $logloc/error_log.`date +%Y-%m-%d`

# create a new empty log file
touch $logloc/access_log
touch $logloc/error_log

# send HUP signal to apache so that it doesn't keep the handle to the now moved log file
/bin/kill -HUP `cat /var/run/httpd.pid 2>/dev/null` 2> /dev/null


