<?php

$_SERVER['CONFIG_FILE'] = 'test.settings.php';

$dir = preg_split('~[/\\\]~', dirname(__FILE__));
array_pop($dir);
$basedir = implode("/", $dir);

chdir($basedir);
set_include_path('.' . PATH_SEPARATOR . './www/include' . PATH_SEPARATOR . './common');
define("NOSESSION", true);
include('inc.php');

set_time_limit(0);

?>
