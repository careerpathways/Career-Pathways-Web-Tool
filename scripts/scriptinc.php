<?php

$dir = explode("/",dirname(__FILE__));
array_pop($dir);
$basedir = implode("/", $dir);

chdir($basedir);
set_include_path('.:./www/inc:./common');
define("NOSESSION", true);
include('./www/inc.php');

?>