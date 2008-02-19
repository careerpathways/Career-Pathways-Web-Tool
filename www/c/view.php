<?php
chdir("..");
include("inc.php");

if( KeyInRequest('d') ) {

	$drawing = $DB->SingleQuery("SELECT drawings.id AS id, 
			drawing_main.name, school_id, published, frozen
		FROM drawing_main, drawings
		WHERE code='".$DB->Safe($_REQUEST['d'])."'
		AND published=1
		AND parent_id=drawing_main.id");

	if( !is_array($drawing) ) {
		header("HTTP/1.0 404 Not Found");
		echo "Not found: ".$_REQUEST['d'];
		die();
	}

} else {

	$drawing = $DB->SingleQuery("SELECT * FROM drawing_main,drawings
		WHERE drawings.parent_id=drawing_main.id
			AND drawings.id=".intval($_REQUEST['id']));

	if( !is_array($drawing) ) {
		header("HTTP/1.0 404 Not Found");
		echo "Not found";
		die();
	}
}

$drawing_name = $drawing['name'];

// determine the format based on the request parameter
if (isset($_REQUEST['format'])) {
	$format = $_REQUEST['format'];
}
else {
	$format = 'html';
}

if ($format === 'xml') {
	require('view/xml.php');
}
else {
	require('view/html.php');
}