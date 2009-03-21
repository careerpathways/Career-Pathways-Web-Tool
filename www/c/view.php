<?php
chdir("..");
include("inc.php");

$_REQUEST['d'] = CleanDrawingCode($_REQUEST['d']);

if( KeyInRequest('v') ) {

	$drawing = $DB->SingleQuery("SELECT drawings.id AS id,
			drawing_main.name, school_id, published, frozen, sk.title AS skillset
		FROM drawing_main
		JOIN drawings ON drawings.parent_id=drawing_main.id
		LEFT JOIN oregon_skillsets AS sk ON drawing_main.skillset_id = sk.id
		WHERE code='".$DB->Safe($_REQUEST['d'])."'
			AND drawings.version_num=".intval($_REQUEST['v']));

	if( !is_array($drawing) ) {
		header("HTTP/1.0 404 Not Found");
		echo "Not found";
		die();
	}

} else if (KeyInRequest('id')) {
	$drawing = $DB->SingleQuery("SELECT drawing_main.*, sk.title AS skillset
		FROM drawing_main
		JOIN drawings ON drawings.parent_id=drawing_main.id
		LEFT JOIN oregon_skillsets AS sk ON drawing_main.skillset_id = sk.id
		WHERE drawings.id=".intval($_REQUEST['id']));

	if( !is_array($drawing) ) {
		header("HTTP/1.0 404 Not Found");
		echo "Not found: ".$_REQUEST['id'];
		die();
	}
} else {

	$drawing = $DB->SingleQuery("SELECT drawings.id AS id,
			drawing_main.name, school_id, published, frozen, sk.title AS skillset
		FROM drawing_main
		JOIN drawings ON drawings.parent_id=drawing_main.id
		LEFT JOIN oregon_skillsets AS sk ON drawing_main.skillset_id = sk.id
		WHERE code='".$DB->Safe($_REQUEST['d'])."'
		AND published=1");

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

if( $_REQUEST['page'] == 'text' ) {
	if( Request('v') ) {
		$_REQUEST['xml'] = 'http://'.$_SERVER['SERVER_NAME'].'/c/version/'.$_REQUEST['d'].'/'.$_REQUEST['v'].'.xml';
	} else {
		$_REQUEST['xml'] = 'http://'.$_SERVER['SERVER_NAME'].'/c/published/'.$_REQUEST['d'].'.xml';
	}
	require('view/text.php');
} else {
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
}

?>