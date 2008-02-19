<?php
chdir("..");

$qs = $_SERVER['REDIRECT_QUERY_STRING'];
if( $qs != "" ) {
	
	$parts = explode(".",$qs);
	if( count($parts) == 2 ) {
		$_REQUEST['id'] = $parts[0];		
		$_REQUEST['format'] = $parts[1];
		include("view.php");
	}

}

?>