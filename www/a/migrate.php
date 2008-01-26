<?php
die();

chdir("..");
include("inc.php");

$objects = $DB->MultiQuery("SELECT * FROM objects");
foreach( $objects as $o ) {
	echo "moving: ".$o['id']." from: ".$obj['y'];
	$obj = unserialize($o['content']);
	$obj['y'] -= 100;
	echo " to: ".$obj['y']."<br>";
	$newcontent['content'] = serialize($obj);
	$DB->Update('objects',$newcontent,$o['id']);
}

echo "done";

?>