<?php

// include this script from the gif, gif_full or svg directories
// set $ext and $content_type before including



$id = substr($_SERVER['REDIRECT_QUERY_STRING'],0,strpos($_SERVER['REDIRECT_QUERY_STRING'],'.'));

if( is_numeric($id) ) {
	$_REQUEST['drawing_id'] = $id;
} else {

	$drawing = $DB->SingleQuery("SELECT drawings.id AS id, school_id, drawing_main.name, tagline_color_id
		FROM drawing_main, drawings
		WHERE code='".$DB->Safe($id)."'
		AND published=1
		AND parent_id=drawing_main.id");
	if( !is_array($drawing) ) {
		header("HTTP/1.0 404 Not Found");
		echo "Not found: ".$id."";
		die();
	}
	$_REQUEST['drawing_id'] = $drawing['id'];

}


$drawing = $DB->SingleQuery("SELECT * FROM drawings WHERE id=".$_REQUEST['drawing_id']." AND deleted=0");

if( is_array($drawing) ) {
	$filename = $_REQUEST['drawing_id'].'.'.md5($_REQUEST['drawing_id'].".".$drawing['last_modified']);
	$full_filename = "../cache/charts/".$filename.$ext;

	if( !file_exists($full_filename) ) {
		include("files/charts/generate.php");
	}

	header('Content-type: '.$content_type);
	readfile($full_filename);
} else {
	header("HTTP/1.0 404 Not Found");
}

?>