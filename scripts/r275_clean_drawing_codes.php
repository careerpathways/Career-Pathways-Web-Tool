<?php
define('NOSESSION',true);
chdir("../www");
include("inc.php");


$drawings = $DB->MultiQuery("SELECT id, code FROM drawing_main");
foreach( $drawings as $d ) {
	echo $d['code']."\n";

	$clean_code = CleanDrawingCode($d['code']);
	echo $clean_code."\n";

	$DB->Update('drawing_main',array('code'=>$clean_code),$d['id']);
	echo "----\n";
}



?>