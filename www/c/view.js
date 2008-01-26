<?php
header("Content-type: text/javascript");

chdir("..");
include("inc.php");

if( KeyInRequest('d') ) {
	
	$src = $DB->SingleQuery("SELECT rendered_html FROM drawing_main WHERE code='".$DB->Safe($_REQUEST['d'])."'");

	echo 'document.write("<style type=\"text/css\">@import \'http://'.$_SERVER['SERVER_NAME'].'/c/chstyle.css\';</style>");';
	
	echo 'document.write("<div class=\"ctepathways\" style=\"position:relative\">';
	echo addslashes(str_replace(array("\n","\r"),"",$src['rendered_html']));
	echo '</div>");';

}


?>