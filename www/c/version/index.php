<?php
chdir("..");

$qs = $_SERVER['REDIRECT_QUERY_STRING'];
if( $qs != "" ) {

	preg_match("|(.+)/([0-9]+)\.([htx]{1,2}ml)|",$qs,$match);

	if( count($match) == 4 ) {
		$_REQUEST['d'] = $match[1];
		$_REQUEST['v'] = $match[2];
		$_REQUEST['format'] = $match[3];
		include("view.php");
	}
	
}

?>