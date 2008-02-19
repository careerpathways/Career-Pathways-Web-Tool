<?php
chdir("..");

if( $_SERVER['REDIRECT_QUERY_STRING'] != "" ) {
	$_REQUEST['d'] = $_SERVER['REDIRECT_QUERY_STRING'];
	include("view.php");
}

?>