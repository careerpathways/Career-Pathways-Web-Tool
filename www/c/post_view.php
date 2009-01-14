<?php
chdir("..");
require_once("inc.php");
require_once("POSTChart.inc.php");

$drawing_id = 0;

if( Request('page') == 'published' ) {
	$drawing = $DB->SingleQuery('SELECT main.*, schools.school_abbr, d.id
		FROM ccti_drawing_main AS main, ccti_drawings AS d, schools 
		WHERE d.parent_id = main.id
			AND main.school_id = schools.id
			AND published = 1
			AND deleted = 0
			AND code="'.Request('d').'"');
	if( is_array($drawing) ) {
		$drawing_id = $drawing['id'];
	}
} elseif( Request('page') == 'version') {

	$drawing = $DB->SingleQuery('SELECT main.*, schools.school_abbr, d.id
		FROM ccti_drawing_main AS main, ccti_drawings AS d, schools
		WHERE d.parent_id = main.id
			AND main.school_id = schools.id
			AND version_num = '.Request('v').'
			AND deleted = 0
			AND code="'.Request('d').'"');
	if( is_array($drawing) ) {
		$drawing_id = $drawing['id'];
	}
}

if( $drawing_id == 0 ) {
	die();
}


$page_title = 'test';


$TEMPLATE->addl_styles[] = "/c/pstyle.css";

echo '<div id="post_title"><img src="/files/titles/'.base64_encode($drawing['school_abbr']).'/'.base64_encode($page_title).'.png" /></div>';

$post = POSTChart::Create($drawing_id);
$post->display();


?>