<?php
chdir("..");
include("inc.php");

if( KeyInRequest('v') ) {

	$drawing = $DB->SingleQuery("SELECT drawings.id AS id, 
			drawing_main.name, school_id, published, frozen
		FROM drawing_main,drawings
		WHERE drawings.parent_id=drawing_main.id
			AND code='".$DB->Safe($_REQUEST['d'])."'
			AND drawings.version_num=".intval($_REQUEST['v']));

	if( !is_array($drawing) ) {
		header("HTTP/1.0 404 Not Found");
		echo "Not found";
		die();
	}

} else {

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
	$_REQUEST['id'] = $drawing['id'];
	require('view/xml.php');
}
else if ($format === 'js') {
	header("Content-type: text/javascript");
	require('view/chart_data_js.php');
}
else {
	require('view/html.php');
}