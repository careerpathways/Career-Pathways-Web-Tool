<?php
header("Content-type: text/html");

chdir("../..");
include("inc.php");


if( $_SERVER['REDIRECT_QUERY_STRING'] != "" ) {

	$code = $_SERVER['REDIRECT_QUERY_STRING'];

	$src = $DB->SingleQuery("SELECT rendered_html FROM drawing_main WHERE code='".$DB->Safe($code)."'");

	if( is_array($src) ) {
		echo '<style type="text/css">@import \'http://'.$_SERVER['SERVER_NAME'].'/c/chstyle.css\';</style>';
		echo "\n\n";
		echo str_replace(array("\r","\n"),"",$src['rendered_html']);
	} else {
		header("HTTP/1.0 404 Not Found");
	}
}


?>